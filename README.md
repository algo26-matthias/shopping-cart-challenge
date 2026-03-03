# Symfony Cart API
## What is it about?

This synthetic project aims at showcasing how to build a simple cart API using Symfony.

It is **not** intended to be self-sufficient or complete.

## Tech Stack

We are using these basic elements:
- Docker
- Symfony
- MariaDB

## Run this project

You'll need Docker and Docker Compose to run the project locally. For easier interaction use make (see below).

### Start the container
```
make up
```
After the dust has settled you can interact with the API via `http://localhost:8080/

### API Contract

After starting the container, the OpenAPI documentation is available here:
http://localhost:8080/api/doc
