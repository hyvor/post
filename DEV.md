## Code Quality

```bash
# php tests (use --filter to run specific tests)
docker compose run --rm backend vendor/bin/phpunit

# phpstan
docker compose run --rm backend vendor/bin/phpstan --memory-limit=1G

# prettier
docker compose run --rm frontend npm run format
```