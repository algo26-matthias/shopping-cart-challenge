# Symfony Cart API
## What is it about?

This synthetic project aims at showcasing how to build a simple cart API using Symfony.

## Scope

This implementation focuses purely on structural cart management:
- Create cart
- Add item
- Update quantity
- Remove item
- Retrieve cart

Not included:
- Authentication
- Pricing
- Inventory validation
- Expiration handling
- HATEOAS

Out of scope of the coding challenge: Removing an entire cart. A real-life API would of course include this operation!

## Tech Stack

We are using these basic elements:
- Docker
- Symfony
- MariaDB

## Architecture

The application follows a layered architecture:

- Controllers (HTTP layer)
- ApiRequestGuard (input validation & content negotiation)
- Application layer (CartService)
- Doctrine repositories (persistence)
- RFC7807 exception subscriber
- OpenAPI documentation via NelmioApiDocBundle

## ADRs

- [ADR-0001 – Auth & Cart Identification](./docs/adr/0001-auth-ids-lifetime.md)
- [ADR-0002 – API Design](./docs/adr/0002-api-design.md)

## Quality Assurance

- PHPUnit functional tests
- Service-layer tests
- RFC7807 error handling covered
- 100 % method coverage (excluding structural OpenAPI DTOs)

## Run this project

You'll need Docker and Docker Compose to run the project locally. For easier interaction use make (see below).

### Start the container
```
make up
```
After the dust has settled you can interact with the API via `http://localhost:8080/`

### Tests and Code Coverage
Run the test suite by executing
```
make test
```

For a short coverage report, run
```
make coverage
```

A detailed HTML coverage report can be acquired by running
```
make coverage-html
```
The outcome will be put in `var/coverage/`. Open `var/coverage/index.html` in your browser to access it.

### API Contract

After starting the container, the OpenAPI documentation is available here:
http://localhost:8080/api/doc
