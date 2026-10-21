<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Inserve\DSDContractManagementAPI\Client\APIClient;
use Inserve\DSDContractManagementAPI\Exception\DSDContractManagementAPIException;
use Inserve\DSDContractManagementAPI\Models\Account;
use Inserve\DSDContractManagementAPI\Models\Contract;
use Inserve\DSDContractManagementAPI\Models\ProductCatalogue;
use Inserve\DSDContractManagementAPI\Serialization\SerializerFactory;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SerializationTest extends TestCase
{
    /**
     * @param DenormalizerInterface|null $denormalizer
     *
     * @return APIClient
     * @throws Exception
     */
    private function transport(?DenormalizerInterface $denormalizer = null): APIClient
    {
        return new APIClient(
            client: $this->createStub(ClientInterface::class),
            requestFactory: new HttpFactory(),
            accessToken: 'token',
            denormalizer: $denormalizer ?? SerializerFactory::create(),
        );
    }

    /**
     * @return array
     * @throws JsonException
     */
    private static function contract(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/Fixtures/contract.json'), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return void
     * @throws JsonException
     * @throws Exception
     */
    public function testMissingOptionalFieldsAndAdditionalApiFieldsAreSupported(): void
    {
        $data = self::contract();
        unset($data['client'], $data['saleReference'], $data['renewalDate'], $data['updated']);
        $data['futureField'] = ['anything' => true];
        $data['account']['futureField'] = 'ignored';
        $contract = $this->transport()->denormalize($data, Contract::class);

        self::assertNull($contract->getClient());
        self::assertNull($contract->getSaleReference());
        self::assertNull($contract->getRenewalDate());
        self::assertNull($contract->getUpdated());
        self::assertSame(1, $contract->getAccount()->getId());
    }

    /**
     * @return void
     * @throws JsonException
     * @throws Exception
     */
    public function testJsonWholeNumbersAreAcceptedForPrices(): void
    {
        $data = json_decode(file_get_contents(__DIR__ . '/Fixtures/product.json'), true, 512, JSON_THROW_ON_ERROR);
        $data['prices'][0]['price'] = 10;
        $data['prices'][0]['discountPercentage'] = 0;
        $product = $this->transport()->denormalize($data, ProductCatalogue::class);

        self::assertSame(10.0, $product->getPrices()[0]->getPrice());
        self::assertSame(0.0, $product->getPrices()[0]->getDiscountPercentage());
    }

    /**
     * @return iterable
     * @throws JsonException
     */
    public static function malformedContracts(): iterable
    {
        $data = self::contract();
        $data['account']['isSubaccount'] = 'false';
        yield 'string boolean' => [$data];

        $data = self::contract();
        $data['id'] = '1';
        yield 'numeric string ID' => [$data];

        $data = self::contract();
        $data['account'] = null;
        yield 'null required object' => [$data];

        $data = self::contract();
        unset($data['items'][0]['product']['name']);
        yield 'missing nested constructor argument' => [$data];

        $data = self::contract();
        $data['items'] = ['invalid'];
        yield 'scalar collection element' => [$data];

        $data = self::contract();
        $data['items'][0]['product']['prices'][0]['price'] = '10.50';
        yield 'numeric string price' => [$data];

        $data = self::contract();
        $data['items'][0]['product']['prices'][0]['quantity'] = '3';
        yield 'numeric string quantity' => [$data];

        $data = self::contract();
        $data['items'][0]['product']['prices'] = 'invalid';
        yield 'scalar nested collection' => [$data];
    }

    /**
     * @param array $data
     *
     * @return void
     * @throws Exception
     */
    #[DataProvider('malformedContracts')]
    public function testNestedMappingFailuresPreserveTheirCause(array $data): void
    {
        try {
            $this->transport()->denormalize($data, Contract::class);
            self::fail('Expected API exception.');
        } catch (DSDContractManagementAPIException $exception) {
            self::assertNotNull($exception->getPrevious());
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCustomDenormalizerReceivesDecodedFieldsAndJsonFormat(): void
    {
        $fields = ['id' => 7, 'username' => 'custom', 'isSubaccount' => false];
        $expected = new Account(7, 'custom', false);
        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer->expects(self::once())->method('denormalize')
            ->with($fields, Account::class, 'json')->willReturn($expected);

        self::assertSame($expected, $this->transport($denormalizer)->denormalize($fields, Account::class));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCustomDenormalizerFailureIsWrappedWithOriginalException(): void
    {
        $cause = new NotNormalizableValueException('Custom mapping failed.');
        $denormalizer = $this->createStub(DenormalizerInterface::class);
        $denormalizer->method('denormalize')->willThrowException($cause);

        try {
            $this->transport($denormalizer)->denormalize([], Account::class);
            self::fail('Expected API exception.');
        } catch (DSDContractManagementAPIException $exception) {
            self::assertSame($cause, $exception->getPrevious());
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCustomDenormalizerMustReturnTheRequestedModel(): void
    {
        $denormalizer = $this->createStub(DenormalizerInterface::class);
        $denormalizer->method('denormalize')->willReturn(new \stdClass());
        $this->expectException(DSDContractManagementAPIException::class);

        $this->transport($denormalizer)->denormalize([], Account::class);
    }
}
