# Shop Postcode Service (UK)

### This project leverages the capabilities of both the Laravel console and backend API to download, parse, store, and interact with postcode and location data.

1. Console command to download and import UK postcodes (data source:
   http://parlvid.mysociety.org/os/) into a MySQL database
2. A controller action to add a new shop to the database with the following details:
   1. Name
   2. Geo coordinates
   3. Store type (takeaway, shop, restaurant)
   4. ax delivery distance.
3. Controller action to return stores near to a postcode (latitude, longitude) as a JSON
   API response
4. Controller action to return stores can deliver to a certain postcode as a JSON API
   response - **TODO**

## Dependencies
1. PHP 8.2
2. Composer
3. Docker Desktop (make sure this is started and running)

## Within the project folder run:

```
composer install
```

### Make a copy of .env.example file and rename it to .env and run:

```
php artisan key:generate
```
#### This should generate a new APP_KEY value in your .env file.

# Database

### Update the following .env variables
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```
#### Note: If you run into DB connection issues with the host name (DB_HOST) set as mysql you can substitute the host name (DB_HOST) for 0.0.0.0 which is the default docker network bridge IP.

# Starting the application
#### Open a new terminal to set up and start both the database and local web server. Run:

```
sail up -d
php artisan migrate
php artisan serve
```

# Run console command
#### This command will download and process postcode data retrieved as archived files (similar to browser based downloads) from http://parlvid.mysociety.org/os/.

```
php artisan postcode:download
```
#### This operation performs a number of operations which including downloading and un-archiving the downloaded file from the internet, processing data stored in a CSV file and storing the information in the database. It will take under a minute to run. 

# API documentation (Scribe)
#### For the full API documentation with example requests, responses etc. Make sure the server is running and navigate to http://localhost/docs

#### To regenerate the scribe documentation run the following command in the terminal within the projects' folder:
```
php artisan scribe:generate
```


# A Note on adding new stores
#### Adding new stores requires you to add geo coordinates (latitude, longitude). Using the available data downloaded and stored in the postcodes table you can add the correct geo coordinates for a store. Run the following SQL statement to fetch all valid postcodes saved along with their coordinates.

```
SELECT postcode, ST_Y(geo_coordinates) as latitude, ST_X(geo_coordinates) as longitude from postcodes;
```

# Improvements
1. Move longer running command execution to queued jobs
2. Clean up processed archived files
3. Certain tools like SimpleExcelReader could be moved into a service and resolved. This can help with flexibility when switching between similar packages and adding tests.
4. Check feasibility of using the Laravel HTTPClient instead of the copy function to download archived files. Laravel HTTP Client provides more testing features.
5. Add some additional CSV data sanitization before saving to the database
6. PHPStan for static analysis (code quality)
7. Laravel Sanctum to create a simple authentication layer (if needed)
8. Centralised error handling
9. Add additional MySQL database to docker-compose.yml for feature tests since SQLite (which is a quick and easy alternative to add test db's) may not support certain MYSQL functions like POINT to process geography columns.
