# ADR-0001 – Auth & Cart Identification

Since this is a simple coding challenge, we don't want to go overboard in terms of complexity.

This means

# KISS
I am going to keep things simple. 

# No Auth
A real-life application will need to impose some sort of authentication layer.  
It depends on the context of the real application, what kind of authentication would be used.
This is outside the scope of this demonstration.

# Identifying a cart
For this application I decided to go with a no-deterministic approach by generating a UUIDv4 when creating a new cart.
This serves as the unique ID of each cart.
It allows to handle multiple carts at the same time and can easily be done without complex calculations.

# Lifetime unlimited
For the sake of simplicity we do not care about the age of carts. Real-life applications will of course 
expire carts older than a specific duration. Perhaps something like 24 or 48 hours.


