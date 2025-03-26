# Challenge API by [Coodesh](https://coodesh.com/)

## About

This API is a challenge taught by Coodesh. Basically it is an import using an external API, which is executed via cron, and a crud to complement it.

## Target

-   https://coodesh-challenge-api.mjsolutions.space/api

# Postman

- adicionar

## Stack summary

-   [PHP](https://www.php.net/docs.php)
-   [Laravel](https://laravel.com/)
-   [Pest](https://pestphp.com/docs)
-   [MySQL](https://dev.mysql.com/doc/)
-   [Algolia](https://www.algolia.com/pt-br/)

### Third-party

-   [Open Food Facts](https://br.openfoodfacts.org/data)

## Instalation

### Before install application:

1.  Check if your PHP version is equal to or greater than 8.2

### Create a copy of your .env.example file

    cp .env.example .env

### After above step install project dependencies:

    composer install

### After the above step, up the migrations.:

    php artisar migrate

### Finally, to ensure that there will be no problem with the key, the application executes the command to recreate it:

    php artisan key:generate

### Now, run server:

    php artisan serve

Generally, the application is started in [http://localhost:8000](http://localhost:8000) (check terminal info)

### To run the tests use:

    ./vendor/bin/pest

### The cron is configured to run every day at midnight. But you can also run it at any time using the command:
    php artisan import-products


### To create your api keys, you can use this command:
    php artisan api-key:generate --name="API Externa" --expires=365 --permissions=products.read --permissions=products.write

### For more information access:

## https://laravel.com/

This is a challenge by [Coodesh](https://coodesh.com/).

### Presentation Link:

- https://www.loom.com/share/74a822f004da4db78efdb92d5b10921c
