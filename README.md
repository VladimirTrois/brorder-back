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
//For HashPassword
make sf c="make:state-processor"

make sf c=make:user
make sf c=make:test


make composer c="require gesdinet/jwt-refresh-token-bundle"

```

To run tests

```
make test //runs all test
make test c="tests/ProductTest.php" //run only Product test
```


For certificates on dev with FEDORA
```
sudo dnf install ca-certificates
sudo update-ca-trust
docker cp $(docker compose ps -q php):/data/caddy/pki/authorities/local/root.crt /etc/pki/ca-trust/source/root.crt && sudo update-ca-trust
```


## For Prod :
```
CADDY_MERCURE_JWT_SECRET=ChangeThisMercureHubJWTSecretKey \
docker compose -f compose.yaml -f compose.prod.yaml up -d --wait
```

```
make composer c='req symfony/orm-pack'
make composer c='require api'
make composer c='require lexik/jwt-authentication-bundle'
make composer c='require symfony/serializer-pack'
make composer c='require gesdinet/jwt-refresh-token-bundle'
```
 
## Checklist:
```
##Step 1: Check Running Containers
docker ps

##Step 2: Check Service Health
docker inspect --format='{{json .State.Health}}' $(docker ps -q --filter name=php)

##Step 3: Test HTTP Connectivity
curl -I SERVER_NAME

##Step 4: Check Symfony Status
docker compose exec -it php bin/console about
# If symfony down
docker compose exec -it php bin/console cache:clear

##Step 5: Verify Database Connection
docker compose exec -it php bin/console doctrine:query:sql "SELECT 1"

##Step 6: Check Caddy & Mercure
docker compose logs php | grep caddy
```
 
api.brorder.campingdesplages.com

curl -X 'GET' \
  'http://localhost:8000/api/products?page=1&itemsPerPage=30&orderBy%5Brank%5D=asc&orderBy%5BisAvailable%5D=asc' \
  -H 'accept: application/ld+json'