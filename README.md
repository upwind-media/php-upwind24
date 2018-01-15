Lightweight PHP wrapper for Upwind24 WebAPI. That's the easiest way to use Upwind24 API in your PHP application.


Quickstart
----------

To download this wrapper and integrate it inside your PHP application, you can use [Composer](https://getcomposer.org).

Quick integration with the following command:

    composer require upwind24/php-upwind24

Or add the repository in your **composer.json** file or, if you don't already have
this file, create it at the root of your project with this content:

```json
{
    "name": "Example Application",
    "description": "This is an example of Upwind24 WebAPI wrapper usage.",
    "require": {
        "upwind24/php-upwind24": "dev-master"
    }
}

```

Then, you can install Upwind24 WebAPI wrapper and dependencies with:

    php composer.phar install

This will install ``upwind24/php-upwind24`` to ``./vendor``, along with other dependencies
including ``autoload.php``.

Upwind24 cookbook
------------

Do you want to use Upwind24 WebAPI? Let's start with [example part](examples/README.md) of this repository!


How I can get my API credentials?
------------

New Upwind24 WebAPI credential keys can be generated in the [user panel](http://www.upwind24.com/panel/api-access).