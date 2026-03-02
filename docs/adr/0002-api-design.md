# ADR-0002 – API Design

## RESTful
This API follows REST principles and models domain concepts as resources.
See the sections below for the concrete design decisions.

## JSON only
All request and response bodies will use JSON exclusively.

Requests with a body (e.g. `POST`, `PATCH`) must set
`Content-Type: application/json`.

Clients must send
`Accept: application/json`.

If the `Accept` header does not allow application/json, the API responds with **406 Not Acceptable**.  
If a request body is sent with an unsupported Content-Type, the API responds with **415 Unsupported Media Type**.

## Base URL
All endpoints are prefixed with `/api`.

### Versioning
For the sake of simplicity, no explicit API versioning is introduced at this point.

Versioning could be introduced later, for example by prefixing routes with `/api/v1/`. 

## HTTP Status Codes
All responses will use the proper HTTP status codes, allowing to easily determine
the outcome of the operation without need to parse the response body.

Examples:

- `POST /api/carts` → **201 Created** (with Location header pointing to the created resource)
- `GET /api/carts/{cartId}` → **200 OK** or **404 Not Found**
- `POST /api/carts/{cartId}/items` → **201 Created** or **404 Not Found**
- `PATCH /api/carts/{cartId}/items/{itemId}` → **200 OK**, **400 Bad Request** or **404 Not Found**
- `DELETE /api/carts/{cartId}/items/{itemId}` → **204 No Content** or **404 Not Found**

Validation errors (e.g. missing fields, invalid UUID format, quantity < 1) result in **400 Bad Request**.

## Entities and relations
There will be two basic entities: 
- `Cart` 
- `CartItem`  

A `Cart` can contain multiple `CartItem`s.  
Each `CartItem` belongs to exactly one `Cart`.

## Domain Scope
This implementation focuses purely on structural cart handling.  
No pricing, totals, tax calculations, currency handling or inventory validation are part of this API scope.

### Identifiers
Each `Cart` and `CartItem` is identified by a UUIDv4.  
UUIDs are treated as opaque identifiers.
They are not sequential and are not derived from internal database identifiers. 

## Endpoints
These endpoints will be offered by the API:

- `POST /api/carts`
- `GET /api/carts/{cartId}`
- `POST /api/carts/{cartId}/items`
- `PATCH /api/carts/{cartId}/items/{itemId}`
- `DELETE /api/carts/{cartId}/items/{itemId}`

## Idempotency
`POST` requests are not idempotent.
Calling `POST /api/carts` twice results in two distinct Cart resources.

`PATCH` and `DELETE` operations are designed to be idempotent.

`PATCH` uses set semantics:
Updating the quantity of a `CartItem` to a specific value and repeating the 
same request results in the same final state.

`DELETE` may be safely called multiple times.
If the resource no longer exists, the API responds with **404 Not Found**.

## Business Rule for Adding Items

When adding an item with a `productId` that already exists within the same 
`Cart`, the API increases the quantity of the existing `CartItem` instead of 
creating a duplicate entry.

## Errors
This API uses **RFC 7807 (Problem Details for HTTP APIs)** with the 
media type `application/problem+json` for error responses.

Example
```json
{
  "type": "https://example.com/errors/not-found",
  "title": "Resource not found",
  "status": 404,
  "detail": "Cart not found"
}
```

## No HATEOAS
HATEOAS is deliberately omitted to keep the scope of this demonstration focused on core resource handling.

