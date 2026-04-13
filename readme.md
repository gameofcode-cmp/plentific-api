# Plentific API Challenge

Framework-agnostic PHP package for retrieving and creating users via the DummyJSON API.

## What This Package Provides

- `getUserById(int $id): User`
- `getUsers(int $limit = 30, int $skip = 0): UserPagination`
- `createUser(CreateUserRequest $request): int`

Returned data is mapped to typed DTOs that implement `JsonSerializable` and provide `toArray()`:

- `User`
- `UserPagination`
- `CreateUserRequest`

## Design Decisions

- Uses PSR standards (`psr/http-client`, `psr/http-factory`, `psr/http-message`) so the package is portable across Laravel, Drupal, WordPress, and plain PHP projects.
- Keeps API-specific response handling in a single client (`DummyJsonUserService`) and mapping concerns in `UserMapper`.
- Converts remote/API failures into domain exceptions so callers can distinguish network failures, invalid payloads, and expected API errors.

## Error Handling Strategy

The service does not expose raw HTTP client exceptions directly.

The following exceptions are thrown:

- `NetworkException` for transport-level failures
- `InvalidApiResponseException` for malformed payloads
- `UserNotFoundException` for `404` user lookups
- `UserCreationException` for create-user failures
- `ApiException` for unexpected non-2xx responses


This keeps consumer code explicit and easier to reason about in higher layers.

## Testing Strategy

- **Unit tests** validate mapping, request/response handling, and exception behavior with mocked HTTP clients so work if API is offline.
- **Integration tests** call the real DummyJSON API to validate the package against real remote behavior.

```
composer test #runs just local mocked tests
composer test:integration #runs against real api
```

## Development Setup

DDEV is used for local orchestration.

Common commands:

```bash
ddev composer validate
ddev composer test
ddev composer stan
```

