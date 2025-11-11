This project contains a means of tracking vehicle entries into a parking lot and querying that data:

- by plate number to see how long the vehicle has been parked
- by a datetime range to see which vehicles were parked within that date time

## Database

Docker container running mariadb and phpMyAdmin.

To start:

```
docker-compose up -d
```

Then open your browser to `http://localhost:8080` and log in with (defined in the `docker-compose.yml` file):

- Email: admin@admin.com
- Password: admin123 

To stop and remove the containers (preserving the data in the volume):

```
docker-compose down
```

To remove the database:

```
docker-compose down -v
```

To just stop (to restart later):

```
docker-compose stop
```

## Symfony

The Symfony project was initially configured to build the minimal framework via `symfony new my_project_directory --version="7.3.x"`.

### Modeling

Doctrine was added [following the directions](https://symfony.com/doc/current/doctrine.html) and an Entity class for Vehicles was setup in the database. As an MVP it contains fields for `id`, `license_plate` (string), and `time_in` (immutable datetime)

#### Fixture

For sample data the [DoctrineFixturesBundle](https://symfony.com/bundles/DoctrineFixturesBundle/current/index.html) package was added and a fixture was setup to mock some entries