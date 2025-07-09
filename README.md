# Brorder Back

[BASE PROJECT](https://github.com/dunglas/symfony-docker)

## Informations

Partie back d'une application de gestion de commande. 
Cette application est réalisé pour un camping afin que les clients puissent commander du pain la veille pour le lendemain.
Le camping doit ensuite pouvoir gérer les produits et les commandes.

Le back utilise Docker et API platform sur Symfony.

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `make build` to build fresh images
3. Run `make up` to set up and start
4. Open SERVER_NAME address
5. Run `make down` to stop the Docker containers.

### On first time run
```
make sf c='lexik:jwt:generate-keypair'
make sf c="make:state-processor"
```

For ssl certificates on dev with FEDORA
```
sudo dnf install ca-certificates
sudo update-ca-trust
docker cp $(docker compose ps -q php):/data/caddy/pki/authorities/local/root.crt /etc/pki/ca-trust/source/root.crt && sudo update-ca-trust
```

## For Production

### To build
```
APP_ENV=prod \
APP_SECRET=ChangeMe \
CADDY_MERCURE_JWT_SECRET=ChangeThisMercureHubJWTSecretKey \
docker compose -f compose.yaml -f compose.prod.yaml build --no-cache
```

### To run
```
APP_ENV=prod \
APP_SECRET=ChangeMe \
CADDY_MERCURE_JWT_SECRET=ChangeThisMercureHubJWTSecretKey \
docker compose -f compose.yaml -f compose.prod.yaml up -d --wait
```

## Checklist
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

## To debug

### Php container
#### Connect to php container
```
make bash 
```
#### On the php container 
```
##Verify env variables
env

##Verify if app works inside container
curl -X GET 'SERVER_NAME:PORT/api/products' -H 'accept: application/ld+json'
```

### Database
#### Connect to DB 
```
Docker compose exec -it database bash
```
```
psql -U app -d brorder

SELECT * FROM product LIMIT 10;
SELECT * FROM user LIMIT 10;

## List users
\du 

```

### From the server
```
curl -X 'GET' 'SERVER_NAME:PORT/api/products' \
  -H 'accept: application/ld+json'

curl -X GET 'SERVER_NAME:PORT/api/products' -H 'accept: application/ld+json'
```

### Users
```
curl -X 'POST' \
  'SERVER_NAME:PORT/api/users' \
  -H 'accept: application/ld+json' \
  -H 'Authorization: Bearer token' \
  -H 'Content-Type: application/ld+json' \
  -d '{
  "username": "test",
  "roles": [
    "USER"
  ],
  "password": "password"
}'

curl -X 'DELETE' \
  'SERVER_NAME:PORT/api/users/{id}' \
  -H 'accept: */*' \
  -H 'Authorization: Bearer token'
```

## What was used :

### All the different packages
```
make composer c='require symfony/orm-pack'
make composer c='require api'
make composer c='require lexik/jwt-authentication-bundle'
make composer c="require symfony/serializer-pack"
make composer c="require gesdinet/jwt-refresh-token-bundle"
make composer c="require doctrine/doctrine-migrations-bundle"

make composer c='require --dev symfony/maker-bundle'
make composer c="require --dev foundry orm-fixtures"
make composer c="require --dev symfony/test-pack symfony/http-client"
make composer c="require --dev dama/doctrine-test-bundle"
make composer c="require --dev justinrainbow/json-schema"
make composer c="require --dev symfony/profiler-pack"
```