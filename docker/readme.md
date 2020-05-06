# Getting Started using Docker

Navigate to docker folder
```
cd docker
```

#### Build and run docker container
```
docker-compose up -d --build
```

#### Install dependancies
```
docker exec txt-php composer install
```

#### Run quick CSV class unit tests
```
docker exec txt-php composer test-unit
```

#### Run Big Data test
```
docker exec txt-php composer test-big-data
```