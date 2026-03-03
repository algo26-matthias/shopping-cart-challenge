# ADR-0001 – Auth & Cart Identification

Given the scope of this coding challenge, the design deliberately avoids unnecessary complexity.

## KISS
I am going to keep things simple. 

## No Auth
A real-life application will need to impose some sort of authentication layer.  
It depends on the context of the real application, what kind of authentication would be used.
This is outside the scope of this demonstration.

## Identifying a cart
For this application I decided to go with a non-deterministic identifier generation using UUIDv4 when creating a new cart.
This serves as the unique ID of each cart.
It allows to handle multiple carts at the same time and can easily be done without complex calculations.

## Lifetime unlimited
For the sake of simplicity we do not care about the age of carts. Real-life applications will of course 
expire carts older than a specific duration. Perhaps something like 24 or 48 hours.


