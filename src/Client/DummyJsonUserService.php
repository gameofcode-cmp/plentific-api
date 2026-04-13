<?php
declare(strict_types=1);

namespace Christo\PlentificApi\Client;

use Christo\PlentificApi\Contract\UserServiceInterface;
use Christo\PlentificApi\Dto\CreateUserRequest;
use Christo\PlentificApi\Dto\User;
use Christo\PlentificApi\Dto\UserPagination;
use Christo\PlentificApi\Exception\ApiException;
use Christo\PlentificApi\Exception\InvalidApiResponseException;
use Christo\PlentificApi\Exception\NetworkException;
use Christo\PlentificApi\Exception\UserCreationException;
use Christo\PlentificApi\Exception\UserNotFoundException;
use Christo\PlentificApi\Mapper\UserMapper;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use JsonException;

final class DummyJsonUserService implements UserServiceInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private UserMapper $userMapper = new UserMapper(),
        private string $baseUri = 'https://dummyjson.com',
    ) {
    }

    public function getUserById(int $id): User
    {
        $response = $this->sendRequest('GET', '/users/' . $id);

        if ($response->getStatusCode() === 404) {
            throw new UserNotFoundException(sprintf('User with ID %d was not found.', $id));
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new ApiException(sprintf(
                'Unexpected status code %d when retrieving user %d.',
                $response->getStatusCode(),
                $id,
            ));
        }

        $data = $this->decodeJsonResponse($response);

        return $this->userMapper->mapUser($data);
    }

    public function getUsers(int $limit = 30, int $skip = 0): UserPagination
    {
        $query = http_build_query([
            'limit' => $limit,
            'skip' => $skip,
            'select' => 'id,firstName,lastName,email',
        ]);

        $response = $this->sendRequest('GET', '/users?' . $query);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new ApiException(sprintf(
                'Unexpected status code %d when retrieving users.',
                $response->getStatusCode(),
            ));
        }

        
        $data = $this->decodeJsonResponse($response);

        if (
            !array_key_exists('users', $data) || !is_array($data['users']) ||
            !array_key_exists('total', $data) || !is_int($data['total']) ||
            !array_key_exists('skip', $data) || !is_int($data['skip']) ||
            !array_key_exists('limit', $data) || !is_int($data['limit'])
        ) {
            throw new InvalidApiResponseException('Paginated users payload is invalid.');
        }

        $users = [];

        foreach ($data['users'] as $userData) {
            if (!is_array($userData)) {
                throw new InvalidApiResponseException('A user in the paginated payload was not an array.');
            }

            /** @var array<string, mixed> $userData */
            $users[] = $this->userMapper->mapUser($userData);
        }

        return new UserPagination(
            users: $users,
            total: $data['total'],
            skip: $data['skip'],
            limit: $data['limit'],
        );
    }

    public function createUser(CreateUserRequest $request): int
    {
        $payload = $this->encodeJson([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
        ]);

        $response = $this->sendRequest(
            method: 'POST',
            path: '/users/add',
            body: $payload,
            headers: [
                'Content-Type' => 'application/json',
            ],
        );

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new UserCreationException(sprintf(
                'Unexpected status code %d when creating user.',
                $response->getStatusCode(),
            ));
        }

        $data = $this->decodeJsonResponse($response);

        if (!array_key_exists('id', $data) || !is_int($data['id'])) {
            throw new UserCreationException('Create user response did not contain a valid ID.');
        }

        return $data['id'];
    }

    /**
     * @param array<string, string> $headers
     */
    private function sendRequest(
        string $method,
        string $path,
        ?string $body = null,
        array $headers = [],
    ): ResponseInterface {
        $request = $this->requestFactory->createRequest(
            $method,
            rtrim($this->baseUri, '/') . $path,
        );

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ($body !== null) {
            $request = $request->withBody($this->streamFactory->createStream($body));
        }

        try {
            return $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new NetworkException(
                'Failed to communicate with the remote user API.',
                0,
                $exception,
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonResponse(ResponseInterface $response): array
    {
        $contents = (string) $response->getBody();

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidApiResponseException(
                'The API returned invalid JSON.',
                0,
                $exception,
            );
        }

        if (!is_array($decoded) || array_is_list($decoded)) {
            throw new InvalidApiResponseException('The API response was not a JSON object.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @param array<string, string> $data
     */
    private function encodeJson(array $data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UserCreationException(
                'Failed to encode the create user payload as JSON.',
                0,
                $exception,
            );
        }
    }
}