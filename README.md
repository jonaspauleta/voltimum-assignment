# Voltimum Assignment
Create a small Laravel project with base authentication for the User in the frontend, a Filament panel for the backoffice, a few models (see below), a database seeder for them and a single feature (choose one of the following):

1. Search engine
Manufacturer
Product (belongs to Manufacturer)
Distributor
Item (belongs to both product and distributor)
Typesense index and search engine for products with faceted search

2. Scraper
Product
Distributor
Item (belongs to both product and distributor)
Python scraper to go to a distributor website (any live e-commerce) and scrape its products to create items in the db if a product with that sku or ean exists (price and availability as pivot fields)

3. AI Agent
Product
API integration with any AI, that goes through all the products in the db and returns statistics showing clear data, enriched with some inference; simple data visualization using any chart tool library

## Tools

PHPStan: check static analysis

Rector: code upgrades and refactors

Pint: code format

Pulse

Telescope

Filament

Missing Tests
