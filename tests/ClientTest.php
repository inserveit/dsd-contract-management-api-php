<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\HttpFactory;
use Inserve\DSDContractManagementAPI\Client\APIClient;
use Inserve\DSDContractManagementAPI\Serialization\SerializerFactory;
use Inserve\DSDContractManagementAPI\Contracts\ContractsInterface;
use Inserve\DSDContractManagementAPI\Contracts\ProductsInterface;
use GuzzleHttp\Psr7\Response;
use Inserve\DSDContractManagementAPI\DSDContractManagementAPIClient;
use Inserve\DSDContractManagementAPI\Exception\DSDContractManagementAPIException;
use Inserve\DSDContractManagementAPI\Options\ContractFilters;
use Inserve\DSDContractManagementAPI\Options\EndClientFilters;
use Inserve\DSDContractManagementAPI\Contracts\EndClientsInterface;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class ClientTest extends TestCase
{
    private array $history = [];

    /**
     * @param array               $responses
     * @param AbstractLogger|null $logger
     *
     * @return DSDContractManagementAPIClient
     */
    private function client(array $responses, ?AbstractLogger $logger = null): DSDContractManagementAPIClient
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new DSDContractManagementAPIClient(new APIClient(
            client: new Client(['handler' => $stack]),
            requestFactory: new HttpFactory(),
            accessToken: 'test-token',
            denormalizer: SerializerFactory::create(),
            logger: $logger,
        ));
    }

    /**
     * @param string $name
     *
     * @return array
     * @throws JsonException
     */
    private function fixture(string $name): array
    {
        return json_decode(file_get_contents(__DIR__ . '/Fixtures/' . $name . '.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param array $data
     *
     * @return Response
     * @throws JsonException
     */
    private function response(array $data): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, JSON_THROW_ON_ERROR));
    }

    /**
     * @param array $items
     * @param int   $offset
     * @param int   $total
     *
     * @return Response
     * @throws JsonException
     */
    private function page(array $items, int $offset, int $total): Response
    {
        return $this->response(['result' => $items, 'pagination' => [
            'offset' => $offset, 'resultCount' => count($items), 'totalCount' => $total,
        ]]);
    }

    /**
     * @return void
     */
    public function testDefaultFactoryCreatesUsableResourceInterfacesWithoutNetworkAccess(): void
    {
        $client = DSDContractManagementAPIClient::create('token');

        self::assertInstanceOf(ContractsInterface::class, $client->contracts());
        self::assertInstanceOf(ProductsInterface::class, $client->products());
        self::assertInstanceOf(EndClientsInterface::class, $client->endClients());
    }

    /**
     * @return void
     * @throws JsonException
     * @throws Exception
     */
    public function testInjectedPsrClientAndFactoryReceiveAbsoluteRequests(): void
    {
        $http = $this->createMock(ClientInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);
        $url = 'https://dsd.example:8443/v1/contracts?offset=0&limit=20&ownUse=false&saleReference=REF%20%26%201';
        $factory->expects($this->once())->method('createRequest')->with('GET', $url)
            ->willReturn(new Request('GET', $url));
        $http->expects($this->once())->method('sendRequest')
            ->with(self::callback(static fn (RequestInterface $request): bool =>
                (string) $request->getUri() === $url &&
                $request->getHeaderLine('Authorization') === 'Bearer custom-token' &&
                $request->getHeaderLine('Accept') === 'application/json'))
            ->willReturn($this->page([], 0, 0));
        $client = new DSDContractManagementAPIClient(new APIClient(
            client: $http,
            requestFactory: $factory,
            accessToken: 'custom-token',
            denormalizer: SerializerFactory::create(),
            baseUri: 'https://dsd.example:8443/',
        ));

        self::assertSame([], $client->contracts()->list(filters: new ContractFilters(
            ownUse: false,
            saleReference: 'REF & 1',
        ))->getResult());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPsrNetworkExceptionsAreWrappedWithoutDependingOnGuzzle(): void
    {
        $http = $this->createMock(ClientInterface::class);
        $cause = new class extends \RuntimeException implements NetworkExceptionInterface {
            public function getRequest(): RequestInterface
            {
                return new Request('GET', 'https://dsd.example/v1/products');
            }
        };
        $http->expects($this->once())->method('sendRequest')->willThrowException($cause);
        $client = new DSDContractManagementAPIClient(new APIClient(
            $http,
            new HttpFactory(),
            'token',
            SerializerFactory::create()
        ));

        try {
            $client->products()->list();
            self::fail('Expected API exception.');
        } catch (DSDContractManagementAPIException $exception) {
            self::assertSame($cause, $exception->getPrevious());
            self::assertSame(0, $exception->getStatusCode());
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testTokenUpdatesReachAllExistingResources(): void
    {
        $client = $this->client([$this->page([], 0, 0), $this->page([], 0, 0), $this->page([], 0, 0)]);
        $contracts = $client->contracts();
        $products = $client->products();
        $endClients = $client->endClients();
        $client->setAccessToken('new-token');
        $contracts->list();
        $products->list();
        $endClients->list();

        foreach ($this->history as $transaction) {
            self::assertSame('Bearer new-token', $transaction['request']->getHeaderLine('Authorization'));
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testClientsKeepTheirAuthenticationSeparate(): void
    {
        $first = $this->client([$this->page([], 0, 0)]);
        $second = $this->client([$this->page([], 0, 0)]);
        $first->setAccessToken('first-account');
        $second->setAccessToken('second-account');
        $first->contracts()->list();
        $second->contracts()->list();

        self::assertSame('Bearer first-account', $this->history[0]['request']->getHeaderLine('Authorization'));
        self::assertSame('Bearer second-account', $this->history[1]['request']->getHeaderLine('Authorization'));
    }

    /**
     * @return void
     */
    public function testRedirectResponseIsReturnedAsAnErrorWithoutFollowingIt(): void
    {
        $client = $this->client([new Response(302, ['Location' => 'https://other.example'])]);
        try {
            $client->products()->list();
            self::fail('Expected API exception.');
        } catch (DSDContractManagementAPIException $exception) {
            self::assertSame(302, $exception->getStatusCode());
            self::assertCount(1, $this->history);
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testContractDetailsHydrateNestedModelsAndAuthenticate(): void
    {
        $client = $this->client([$this->response(['result' => $this->fixture('contract')])]);
        $contract = $client->contracts()->get(1);

        self::assertSame($client->contracts(), $client->contracts());
        self::assertSame(1, $contract->getId());
        self::assertSame('Example BV', $contract->getClient()->getCompany());
        self::assertTrue($contract->getAccount()->getIsSubaccount());
        self::assertSame(3, $contract->getItems()[0]->getProduct()->getPrices()[0]->getQuantity());
        self::assertSame(10.5, $contract->getItems()[0]->getProduct()->getPrices()[0]->getPrice());
        self::assertNull($contract->getRenewalDate());
        self::assertSame('EUR', $contract->getTotals()->getCurrency());
        $request = $this->history[0]['request'];
        self::assertSame('/v1/contracts/1', $request->getUri()->getPath());
        self::assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testProductDetailsEncodeSkuAndUseCataloguePrices(): void
    {
        $client = $this->client([$this->response(['result' => $this->fixture('product')])]);
        $client->setAccessToken('replacement');
        $product = $client->products()->get('SKU/part ?#');

        self::assertSame('one_year', $product->getPrices()[0]->getCommitmentTerm());
        self::assertSame('one_month', $product->getPrices()[0]->getInvoicingPeriod());
        self::assertNull($product->getPrices()[0]->getMin());
        self::assertSame('/v1/products/SKU%2Fpart%20%3F%23', $this->history[0]['request']->getUri()->getPath());
        self::assertSame('Bearer replacement', $this->history[0]['request']->getHeaderLine('Authorization'));
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testContractListPreservesAllFiltersAndPagination(): void
    {
        $client = $this->client([$this->page([$this->fixture('contract')], 10, 30)]);
        $filters = new ContractFilters(
            status: 'active',
            clientId: 7,
            ownUse: false,
            saleReference: 'REF & 1',
            renewalFrom: '2026-01-01',
            renewalTo: '2026-12-31',
            createdFrom: '2026-01-01T00:00:00Z',
            createdTo: '2026-02-01T00:00:00Z',
            updatedFrom: '2026-03-01',
            updatedTo: '2026-04-01',
            productName: 'Microsoft 365',
            productBrand: 'Microsoft',
            sku: 'SKU/1',
        );
        $page = $client->contracts()->list(offset: 10, limit: 5, filters: $filters);
        parse_str($this->history[0]['request']->getUri()->getQuery(), $query);

        self::assertSame('10', $query['offset']);
        self::assertSame('5', $query['limit']);
        foreach ($filters->toQuery() as $key => $value) {
            self::assertSame((string) $value, $query[$key]);
        }
        self::assertSame('/v1/contracts', $this->history[0]['request']->getUri()->getPath());
        self::assertSame(1, $page->getResult()[0]->getId());
        self::assertSame(10, $page->getPagination()->getOffset());
        self::assertSame(1, $page->getPagination()->getResultCount());
        self::assertSame(30, $page->getPagination()->getTotalCount());
        self::assertSame(['ownUse' => 'true'], (new ContractFilters(ownUse: true))->toQuery());
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testContractIteratorFetchesLazilyAndKeepsFilters(): void
    {
        $first = $this->fixture('contract');
        $second = array_replace($first, ['id' => 2]);
        $client = $this->client([$this->page([$first], 0, 2), $this->page([$second], 1, 2)]);
        $iterator = $client->contracts()->all(new ContractFilters(clientId: 7), limit: 1);
        self::assertCount(0, $this->history);
        self::assertSame([1, 2], array_map(fn ($c) => $c->getId(), iterator_to_array($iterator)));
        foreach ($this->history as $index => $transaction) {
            parse_str($transaction['request']->getUri()->getQuery(), $query);
            self::assertSame((string) $index, $query['offset']);
            self::assertSame('7', $query['clientId']);
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testProductIteratorUsesActualPageSizeAndStopsAtTotal(): void
    {
        $first = $this->fixture('product');
        $second = array_replace($first, ['sku' => 'SECOND']);
        $client = $this->client([$this->page([$first], 0, 2), $this->page([$second], 1, 2)]);
        $products = iterator_to_array($client->products()->all());

        self::assertCount(2, $products);
        self::assertSame('SECOND', $products[1]->getSku());
        self::assertCount(2, $this->history);
        self::assertStringContainsString('offset=1', $this->history[1]['request']->getUri()->getQuery());
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testEmptyPageStopsEvenIfTotalIsStale(): void
    {
        $client = $this->client([$this->page([], 0, 100)]);
        self::assertSame([], iterator_to_array($client->products()->all()));
        self::assertCount(1, $this->history);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testEndClientDetailsUseSeparateOriginAndDirectResponse(): void
    {
        $data = $this->fixture('end-client');
        $client = $this->client([$this->response($data), $this->page([], 0, 0)]);
        $endClient = $client->endClients()->get(7);
        $client->contracts()->list();

        self::assertSame($client->endClients(), $client->endClients());
        self::assertSame(7, $endClient->getId());
        self::assertSame(7, $endClient->getUserId());
        self::assertFalse($endClient->getActive());
        self::assertNull($endClient->getFirstName());
        self::assertSame('Example BV', $endClient->getCompany());
        self::assertSame($data['numberCoc'], $endClient->getNumberCoc());
        self::assertSame($data['updated'], $endClient->getUpdated());
        $request = $this->history[0]['request'];
        self::assertSame('https://end-client-api.dsdeurope.com/v1/end-clients/7', (string) $request->getUri());
        self::assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertSame('contract-management-api.dsdeurope.com', $this->history[1]['request']->getUri()->getHost());
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testEndClientListHydratesModelsAndPreservesEveryFilter(): void
    {
        $client = $this->client([$this->page([$this->fixture('end-client')], 10, 30)]);
        $filters = new EndClientFilters(
            active: false,
            name: 'Client & Co',
            firstName: 'John',
            lastName: 'Doe',
            company: 'Example BV',
            postalCode: '1234 AB',
            city: 'Amsterdam',
            country: 'NL',
            phone: '+31 123456789',
            email: 'john+dsd@example.com',
            numberCoc: '12345678',
            numberVat: 'NL123456789B01',
        );
        $page = $client->endClients()->list(offset: 10, limit: 5, filters: $filters);
        parse_str($this->history[0]['request']->getUri()->getQuery(), $query);

        self::assertSame(['offset' => '10', 'limit' => '5'] + $filters->toQuery(), $query);
        self::assertSame('end-client-api.dsdeurope.com', $this->history[0]['request']->getUri()->getHost());
        self::assertSame('/v1/end-clients', $this->history[0]['request']->getUri()->getPath());
        self::assertSame(7, $page->getResult()[0]->getId());
        self::assertSame(10, $page->getPagination()->getOffset());
        self::assertSame(1, $page->getPagination()->getResultCount());
        self::assertSame(30, $page->getPagination()->getTotalCount());
        self::assertSame([], (new EndClientFilters())->toQuery());
        self::assertSame(['active' => 'true'], (new EndClientFilters(active: true))->toQuery());
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testEndClientIteratorIsLazyAndKeepsFiltersAcrossPages(): void
    {
        $first = $this->fixture('end-client');
        $second = array_replace($first, ['id' => 8]);
        $client = $this->client([$this->page([$first], 0, 2), $this->page([$second], 1, 2)]);
        $iterator = $client->endClients()->all(new EndClientFilters(active: false), limit: 1);
        self::assertCount(0, $this->history);
        self::assertSame([7, 8], array_map(fn ($c) => $c->getId(), iterator_to_array($iterator)));
        foreach ($this->history as $index => $transaction) {
            parse_str($transaction['request']->getUri()->getQuery(), $query);
            self::assertSame((string) $index, $query['offset']);
            self::assertSame('1', $query['limit']);
            self::assertSame('false', $query['active']);
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testEndClientEmptyPageStopsIterator(): void
    {
        $client = $this->client([$this->page([], 0, 100)]);
        self::assertSame([], iterator_to_array($client->endClients()->all()));
        self::assertCount(1, $this->history);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testEndClientOriginCanBeConfiguredIndependently(): void
    {
        $stack = HandlerStack::create(new MockHandler([
            $this->page([], 0, 0), $this->page([], 0, 0), $this->page([], 0, 0),
        ]));
        $stack->push(Middleware::history($this->history));
        $client = new DSDContractManagementAPIClient(new APIClient(
            client: new Client(['handler' => $stack]),
            requestFactory: new HttpFactory(),
            accessToken: 'token',
            denormalizer: SerializerFactory::create(),
            baseUri: 'https://contracts.example:8443/',
            endClientBaseUri: 'https://clients.example:9443/',
        ));
        $client->contracts()->list();
        $client->products()->list();
        $client->endClients()->list();

        self::assertSame(
            'https://contracts.example:8443/v1/contracts?offset=0&limit=20',
            (string) $this->history[0]['request']->getUri()
        );
        self::assertSame(
            'https://contracts.example:8443/v1/products?offset=0&limit=20',
            (string) $this->history[1]['request']->getUri()
        );
        self::assertSame(
            'https://clients.example:9443/v1/end-clients?offset=0&limit=20',
            (string) $this->history[2]['request']->getUri()
        );
    }

    /**
     * @return void
     */
    public function testEndClientNotFoundUsesExistingErrorHandling(): void
    {
        $client = $this->client([new Response(404, [], json_encode([
            'title' => 'Client not found', 'status' => 404, 'detail' => 'Unknown client ID',
        ]))]);
        try {
            $client->endClients()->get(7);
            self::fail('Expected API exception.');
        } catch (DSDContractManagementAPIException $exception) {
            self::assertSame(404, $exception->getStatusCode());
            self::assertSame('Unknown client ID', $exception->getErrorResponse()->getDetail());
        }
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testMalformedEndClientDetailsThrowPackageException(): void
    {
        $client = $this->client([$this->response(['id' => 7])]);
        $this->expectException(DSDContractManagementAPIException::class);
        $client->endClients()->get(7);
    }

    /**
     * @return iterable
     */
    public static function errorResponses(): iterable
    {
        foreach ([400, 401, 403, 404, 406, 429, 500] as $status) {
            yield (string) $status => [$status];
        }
    }

    /**
     * @param int $status
     *
     * @return void
     */
    #[DataProvider('errorResponses')]
    public function testApiErrorsRetainHttpStatusAndDetails(int $status): void
    {
        $client = $this->client([new Response($status, [], json_encode([
            'title' => 'Request failed', 'status' => $status, 'detail' => 'Details', 'errors' => ['limit' => 'Invalid'],
        ]))]);
        try {
            $client->contracts()->list();
            self::fail('Expected API exception.');
        } catch (DSDContractManagementAPIException $exception) {
            self::assertSame($status, $exception->getStatusCode());
            self::assertSame(['limit' => 'Invalid'], $exception->getErrorResponse()->getErrors());
            self::assertStringContainsString('Details', $exception->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testHtmlErrorDoesNotMaskHttpStatus(): void
    {
        $client = $this->client([new Response(502, [], '<html>Bad gateway</html>')]);
        try {
            $client->products()->list();
            self::fail('Expected API exception.');
        } catch (DSDContractManagementAPIException $exception) {
            self::assertSame(502, $exception->getStatusCode());
            self::assertNull($exception->getErrorResponse());
        }
    }

    /**
     * @return iterable
     */
    public static function malformedResponses(): iterable
    {
        yield 'invalid JSON' => ['{'];
        yield 'null JSON' => ['null'];
        yield 'list JSON' => ['[1,2]'];
        yield 'missing result' => ['{}'];
        yield 'scalar result' => ['{"result":"wrong"}'];
        yield 'missing fields' => ['{"result":{"id":1}}'];
        yield 'wrong ID type' => [json_encode(['result' => ['id' => 'one']])];
    }

    /**
     * @param string $body
     *
     * @return void
     */
    #[DataProvider('malformedResponses')]
    public function testMalformedSuccessesThrowPackageException(string $body): void
    {
        $client = $this->client([new Response(200, [], $body)]);
        $this->expectException(DSDContractManagementAPIException::class);
        $client->contracts()->get(1);
    }

    /**
     * @return void
     */
    public function testTransportFailurePreservesCauseAndLogsWithoutToken(): void
    {
        $cause = new ConnectException('Connection failed', new Request('GET', 'https://example.test'));
        $logger = new class extends AbstractLogger {
            public array $records = [];
            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [$message, $context];
            }
        };
        $client = $this->client([$cause], $logger);
        try {
            $client->products()->list();
            self::fail('Expected API exception.');
        } catch (DSDContractManagementAPIException $exception) {
            self::assertSame($cause, $exception->getPrevious());
            self::assertSame(0, $exception->getStatusCode());
            self::assertCount(1, $logger->records);
            self::assertStringNotContainsString('test-token', json_encode($logger->records));
        }
    }

    /**
     * @return iterable
     */
    public static function invalidArguments(): iterable
    {
        yield 'blank token' => [static fn () => DSDContractManagementAPIClient::create('')];
        yield 'header injection' => [
            static fn () => DSDContractManagementAPIClient::create("token\r\nInjected: value"),
        ];
        yield 'timeout' => [static fn () => DSDContractManagementAPIClient::create('token', timeout: 0)];
        yield 'infinite timeout' => [static fn () => DSDContractManagementAPIClient::create('token', timeout: INF)];
        yield 'relative base URI' => [static fn () => DSDContractManagementAPIClient::create('token', baseUri: '/api')];
        yield 'base URI path' => [static fn () => DSDContractManagementAPIClient::create(
            'token',
            baseUri: 'https://example.test/v1'
        )];
        yield 'base URI query' => [static fn () => DSDContractManagementAPIClient::create(
            'token',
            baseUri: 'https://example.test?key=value'
        )];
        yield 'base URI credentials' => [static fn () => DSDContractManagementAPIClient::create(
            'token',
            baseUri: 'https://user:pass@example.test'
        )];
        yield 'conflicting filters' => [static fn () => new ContractFilters(clientId: 1, ownUse: true)];
        yield 'client ID' => [static fn () => new ContractFilters(clientId: 0)];
        yield 'status' => [static fn () => new ContractFilters(status: 'unknown')];
        yield 'negative offset' => [static fn ($c) => $c->contracts()->list(offset: -1)];
        yield 'low limit' => [static fn ($c) => $c->products()->list(limit: 0)];
        yield 'high limit' => [static fn ($c) => $c->contracts()->list(limit: 101)];
        yield 'contract ID' => [static fn ($c) => $c->contracts()->get(0)];
        yield 'end client ID' => [static fn ($c) => $c->endClients()->get(0)];
        yield 'end client offset' => [static fn ($c) => $c->endClients()->list(offset: -1)];
        yield 'end client low limit' => [static fn ($c) => $c->endClients()->list(limit: 0)];
        yield 'end client high limit' => [static fn ($c) => $c->endClients()->list(limit: 101)];
        yield 'end client base URI path' => [static fn () => DSDContractManagementAPIClient::create(
            'token',
            endClientBaseUri: 'https://example.test/v1'
        )];
        yield 'blank SKU' => [static fn ($c) => $c->products()->get('')];
    }

    /**
     * @param \Closure $action
     *
     * @return void
     */
    #[DataProvider('invalidArguments')]
    public function testInvalidArgumentsAreRejectedBeforeHttpRequest(\Closure $action): void
    {
        $client = $this->client([]);
        $this->expectException(\InvalidArgumentException::class);
        $action($client);
    }
}
