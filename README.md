# Router - PedroQuezado

[![Maintainer](http://img.shields.io/badge/maintainer-@pedroquezado-blue.svg?style=flat-square)](https://github.com/pedroquezado)
[![Source Code](http://img.shields.io/badge/source-pedroquezado/router-blue.svg?style=flat-square)](https://github.com/pedroquezado/router)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/pedroquezado/router.svg?style=flat-square)](https://packagist.org/packages/pedroquezado/router)
[![Latest Version](https://img.shields.io/github/release/pedroquezado/router.svg?style=flat-square)](https://github.com/pedroquezado/router/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build](https://img.shields.io/scrutinizer/build/g/pedroquezado/router.svg?style=flat-square)](https://scrutinizer-ci.com/g/pedroquezado/router)
[![Quality Score](https://img.shields.io/scrutinizer/g/pedroquezado/router.svg?style=flat-square)](https://scrutinizer-ci.com/g/pedroquezado/router)
[![Total Downloads](https://img.shields.io/packagist/dt/pedroquezado/router.svg?style=flat-square)](https://packagist.org/packages/pedroquezado/router)

## About PedroQuezado

PedroQuezado/Router is a PHP router component designed for routing requests in your application or API. It provides a simple and intuitive way to define routes and handle HTTP verbs (GET, POST, PUT, PATCH, DELETE) using the MVC pattern. The router works independently, ensuring isolation and seamless integration into your projects.

## Installation

You can install the PedroQuezado Router via Composer. Run the following command:

```bash
composer require pedroquezado/router
```

## Router Class
###### The Router class is the main entry point for routing requests. It provides methods to define routes, handle different HTTP verbs, and run the router to match and execute the appropriate route handlers.

## Usage
To use the Router class in your application, follow these steps:

- Create an **index.php** file in your project's root directory.
- Use Composer's autoloader to load the required files.
- Instantiate the **Router** class and define your routes.
- Dispatch the routes.

Here's an example of how to use the **Router** class:

```php
<?php

require 'vendor/autoload.php';

use PedroQuezado\Code\Router\Router;

$router = new Router('example.com');
$router->get('/', function () {
    echo 'Hello, world!';
});
$router->post('/user', 'UserController::create');
$router->put('/user/{id}', 'UserController::update');
$router->delete('/user/{id}', function ($id) {
    echo "Deleting user with ID: $id";
});

$router->run();
```

In the example above, we instantiate the **Router** class, define different routes using the **get()**, **post()**, **put()**, and **delete()** methods, and finally call the run() method to dispatch the routes.

## Methods
### get($path, $callback)
Defines a route for HTTP GET requests.

- $path: The path of the route.
- $callback: The callback function or class method to execute when the route matches.
### post($path, $callback)
Defines a route for HTTP POST requests.

- $path: The path of the route.
- $callback: The callback function or class method to execute when the route matches.

### put($path, $callback)
Defines a route for HTTP PUT requests.

- $path: The path of the route.
- $callback: The callback function or class method to execute when the route matches.

### patch($path, $callback)
Defines a route for HTTP PATCH requests.

- $path: The path of the route.
- $callback: The callback function or class method to execute when the route matches.

### delete($path, $callback)
Defines a route for HTTP DELETE requests.

- $path: The path of the route.
- $callback: The callback function or class method to execute when the route matches.

**Note**: The **$path** parameter can include placeholders enclosed in curly braces **{}** to capture dynamic segments of the URL. These placeholders can be accessed in the callback function or class method as parameters.

### Constructor
The **Router** class constructor accepts the base URL as its parameter. This base URL is used for generating the correct route URLs in your application. Here's an example of creating a new Router instance:
```php 
$router = new Router("https://www.yourdomain.com"); 
```

### Defining Routes
Here's an example of defining a **GET** route that maps to a closure function:
```php 
$router->get("/home", function () {
    // Handle the GET /home request
});
```

Class method callback:
```php 
$router->post('/user', 'UserController::create');
```

***In the class method callback format, the class and method names are separated by the double-colon (::) syntax.***

### Namespaces
The **namespace()** method allows you to define a namespace for the class or classes that will be referenced in the callbacks. This is useful for organizing classes into different directories or namespaces. Here's an example of how it can be used:
```php 
$router->namespace('App\Controllers');
$router->get('/users', 'UserController::index');
$router->get('/products', 'ProductController::index');

$router->namespace('App\Forms');
$router->get('/users', 'UserForms::index');
$router->get('/products', 'ProductForms::index');
```
In this example, the **UserController** and **ProductController** classes are located in the **App\Controllers** namespace.

#### Middleware
middleware($callback): Define um middleware para ser executado antes de uma rota.
#### Group
group($options, $callback): Define um grupo de rotas para compartilhar configurações comuns.

### Dispatching Routes
Once you have defined your routes, you can call the run() method to dispatch the routes and handle incoming requests.
```php
$router->run();
```
The **run()** method will match the current request URL and HTTP method to the defined routes and execute the corresponding callback function or class method.

## Contributing
Contributions are welcome! If you would like to contribute to the **PedroQuezado Router**, feel free to open an issue or submit a pull request. We appreciate your feedback and contributions to make this project even better.

License
This project is licensed under the MIT License.
