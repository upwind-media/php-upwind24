Upwind24 WebAPI - PHP examples
--------------------
Below you can find real use cases of the Upwind24 PHP Wrapper.

### Get list of regattas
```php
<?php
/**
 * Get your API credentials at https://www.upwind24.pl/panel/integration
 */
require __DIR__ . '/vendor/autoload.php';
use \Upwind24\Api;

$api = new Api($clientId, $secretId);

var_dump(
    $api->get('/regatta', [
        'name' => 'puchar',
        'limit' => 10,
        'offset' => 0,
    ])
);
?>
```

### Get single regatta details
```php
<?php
/**
 * Get your API credentials at https://www.upwind24.pl/panel/integration
 */
require __DIR__ . '/vendor/autoload.php';
use \Upwind24\Api;

$api = new Api($clientId, $secretId);

var_dump(
    $api->get('/regatta/xxiv-miedzynarodowe-mistrzostwa-polski-jachtow-kabinowych-2017')
);
?>
```
