# Shop Postcode Service (UK)

### This project combines the power of both the Laravel console and backend API functionality to download, parse, save and interact with saved postcode information

1. Console command to download and import UK postcodes (e.g.
   http://parlvid.mysociety.org/os/) into some kind of database
2. A controller action to add a new store/shop to the database with:
   1. Name
   2. Geo coordinates
   3. store type (takeaway, shop, restaurant)
   4. a max delivery distance.
3. Controller action to return stores near to a postcode (latitude, longitude) as a JSON
   API response
4. Controller action to return stores can deliver to a certain postcode as a JSON API
   response - TODO

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

### Open a new terminal to create, start and seed the database server. Also, the following commands will create and start a local mail server and serve the application.

```
sail up -d
php artisan migrate
php artisan serve
```

# Run Console Command
#### This command will download specific archived postcode data from http://parlvid.mysociety.org/os/ as a compressed zip file.
#### And extract postcode information from a CSV file which is then added to the database.

```
php artisan postcode:download
```

# API Documentation
#### For the full API documentation with example requests, responses etc. Make sure the server is running and navigate to http://localhost/docs

#### To regenerate the scribe documentation run the following command in the terminal within the projects' folder:
```
php artisan scribe:generate
```


# A Note on Adding New Stores
#### Adding new stores requires you to add geo coordinates (lat, long). With the available information downloaded and stored in the postcodes table you can add the correct geo coordinate for a store using this information. Run the following SQL statement to fetch all valid postcodes saved along with their coordinates.

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
9. Add additional MYSQl database for feature tests since SQLite (which is a quick and easy alternative) may not support certain MYSQL functions like POINT to process geography columns.
