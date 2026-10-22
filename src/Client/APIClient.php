<?php

declare(strict_types=1);

namespace Inserve\DSDContractManagementAPI\Client;

use Inserve\DSDContractManagementAPI\Exception\DSDContractManagementAPIException;
use Inserve\DSDContractManagementAPI\Models\ApiError;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Log\LoggerInterface;
use SensitiveParameter;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use UnexpectedValueException;

final class APIClient
{
    public const string DEFAULT_BASE_URI = 'https://contract-management-api.dsdeurope.com';

    public const string DEFAULT_END_CLIENT_BASE_URI = 'https://end-client-api.dsdeurope.com';

    private readonly string $baseUri;
    private readonly string $endClientBaseUri;

    /**
     * @param ClientInterface         $client
     * @param RequestFactoryInterface $requestFactory
     * @param string                  $accessToken
     * @param DenormalizerInterface   $denormalizer
     * @param LoggerInterface|null    $logger
     * @param string                  $baseUri
     * @param string                  $endClientBaseUri
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
        #[SensitiveParameter] private string $accessToken,
        private readonly DenormalizerInterface $denormalizer,
        private readonly ?LoggerInterface $logger = null,
        string $baseUri = self::DEFAULT_BASE_URI,
        string $endClientBaseUri = self::DEFAULT_END_CLIENT_BASE_URI,
    ) {
        $this->setAccessToken($accessToken);
        $this->baseUri = $this->validateBaseUri($baseUri);
        $this->endClientBaseUri = $this->validateBaseUri($endClientBaseUri);
    }

    /**
     * @param string $baseUri
     *
     * @return string
     */
    private function validateBaseUri(string $baseUri): string
    {
        $parts = parse_url($baseUri);
        if ($parts === false || !isset($parts['host']) ||
            !in_array($parts['scheme'] ?? '', ['http', 'https'], true) ||
            !in_array($parts['path'] ?? '', ['', '/'], true) ||
            isset($parts['query']) || isset($parts['fragment']) || isset($parts['user']) || isset($parts['pass'])) {
            throw new \InvalidArgumentException(
                'Base URI must be an HTTP(S) origin without a path, credentials, query, or fragment.'
            );
        }
        return rtrim($baseUri, '/');
    }

    /**
     * @param string $accessToken
     *
     * @return void
     */
    public function setAccessToken(#[SensitiveParameter] string $accessToken): void
    {
        if (trim($accessToken) === '' || preg_match('/[\r\n]/', $accessToken)) {
            throw new \InvalidArgumentException('A non-empty bearer token without line breaks is required.');
        }

        $this->accessToken = $accessToken;
    }

    /**
     * @param string $path
     * @param array  $query
     *
     * @return array
     */
    public function get(string $path, array $query = []): array
    {
        return $this->getFromBaseUri($this->baseUri, $path, $query);
    }

    /**
     * @param string $path
     * @param array  $query
     *
     * @return array
     */
    public function getEndClients(string $path, array $query = []): array
    {
        return $this->getFromBaseUri($this->endClientBaseUri, $path, $query);
    }

    /**
     * @param string $baseUri
     * @param string $path
     * @param array  $query
     *
     * @return array
     */
    private function getFromBaseUri(string $baseUri, string $path, array $query): array
    {
        $url = $baseUri . '/v1/' . ltrim($path, '/');
        if ($query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }
        $request = $this->requestFactory->createRequest('GET', $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer ' . $this->accessToken);

        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            $this->logger?->error('DSD API transport failure.', ['path' => $path]);
            throw new DSDContractManagementAPIException('Could not connect to the DSD API.', previous: $exception);
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status < 200 || $status >= 300) {
            $error = null;
            try {
                $error = $this->denormalize($this->decodeObject($body), ApiError::class);
            } catch (JsonException|UnexpectedValueException|DSDContractManagementAPIException) {
                //
            }

            $this->logger?->error('DSD API request failed.', ['path' => $path, 'status' => $status]);
            throw new DSDContractManagementAPIException(
                sprintf(
                    'DSD API returned HTTP %d: %s',
                    $status,
                    $error?->getDetail() ?? $error?->getTitle() ?? 'Request failed.'
                ),
                $status,
                $error,
            );
        }

        try {
            return $this->decodeObject($body);
        } catch (JsonException|UnexpectedValueException $exception) {
            throw new DSDContractManagementAPIException(
                'DSD API returned an invalid JSON object.',
                $status,
                previous: $exception
            );
        }
    }

    /**
     * @template T of object
     * @param mixed $data
     * @param class-string<T> $class
     * @return T
     */
    public function denormalize(mixed $data, string $class): object
    {
        try {
            if (!is_array($data) || ($data !== [] && array_is_list($data))) {
                throw new UnexpectedValueException('Expected response object fields.');
            }
            $model = $this->denormalizer->denormalize($data, $class, 'json');
            if (!$model instanceof $class) {
                throw new UnexpectedValueException('Denormalizer returned an unexpected model type.');
            }

            return $model;
        } catch (ExceptionInterface|UnexpectedValueException|\TypeError $exception) {
            throw new DSDContractManagementAPIException(
                'DSD API returned an unexpected response structure.',
                previous: $exception
            );
        }
    }

    /**
     * @return array<string, mixed>
     * @throws JsonException|UnexpectedValueException
     */
    private function decodeObject(string $body): array
    {
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !str_starts_with(ltrim($body), '{')) {
            throw new UnexpectedValueException('Expected a JSON object.');
        }

        return $data;
    }
}
