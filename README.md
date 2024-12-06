# Symfony Docker

[BASE PROJECT](https://github.com/dunglas/symfony-docker)

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## What was done :

```
make composer c='req symfony/orm-pack'
make composer c='require api'
make composer c='require symfony/maker-bundle --dev'
make composer c='require lexik/jwt-authentication-bundle'
make sf c='lexik:jwt:generate-keypair'
make composer c="require --dev foundry orm-fixtures"
make composer c="require --dev symfony/test-pack symfony/http-client"
make composer c="require --dev dama/doctrine-test-bundle"
make composer c="require --dev justinrainbow/json-schema"
make composer c="require symfony/serializer-pack"

make sf c=make:user
make sf c=make:test

//For HashPassword
make sf c="make:state-processor"
```

To run tests

```
make test //runs all test
make test c="tests/ProductTest.php" //run only Product test
```
