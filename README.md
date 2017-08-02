# PHPRouter
PHPRouter is a lightweight module that adds REST-like routing functionality to your project. It's inspired by ReactRouter and PHP Falcon framework.

## Quickstart

1. Include the following directive into your `.htaccess` file or the root config.
```
<IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^((?s).*)$ index.php [QSA, L]
</IfModule>
```
2. Initialize `Router` and map a route to `GET` request. Try accessing `/library/orwell/1984`.
```php
require_once('PHPRouter/Router.php');
require_once('PHPRouter/RouteParser.php');

$router = new Router(new RouteParser());
$router->addGet('/library/{author}/{book}', function($author, $book) {
    echo "You've requested '$book' by $author. Enjoy reading!";
});

$router->resolve();
```
## Documentation

### Basic approach
1. Initialize a `Router` with a `RouteParser` (make your own parser or choose one of preset)
2. Set up `Route`s to map paths and requests to your custom callback.
3. Call `resolve()` method to make `Router` resolve incoming requests.

### Features
1. `PHProuter` maps a *route schema* to a custom callback. A route schema is a string, which describes a route structure. (i.e. `/blog/articles`).
2. Parametrized routes by default (i.e. `/user/{id}`). All parameters are retreived from matched paths and passed as the callback arguments.
3. Easily extendable. `Router` class fully relies on interfaces, so one can easily add new functionality by either extending one of supplied parsers, or writing a completely new one.

### Preset parsers

1. `DefaultRouteParser`
A naive parser implementation, which doesn't support route parameters and simply makes bijective schema-path mapping.

2. `RouteParser`
A more advanced parser, which supports all listed features.

### Interfaces
PHPRouter exposes three interfaces to the programmer: `IRoute`, `IRouter`, and `IRouteParser`. 

**IRoute**
```php
  /*
    Returns the schema, which has been passed in the constructor
  */
  function getSchema();
  
  /*
    Returns parameter names, which have been passed in the constructor
  */  
  function getParameterNames();

```

**IRouter**
```php
  /*
    Adds a new route schema, which can later be mapped to
    using `resolve` method. If a successful match is present, the `$callback`
    will be invoked
  */
  function add($routeSchema, Callable $callback);

  /*
    Adds a route schema, which will only be matched in case of GET request
  */
  function addGet($routeSchema, Callable $callback);

  /*
    Adds a route schema, which will only be matched in case of POST request
  */
  function addPost($routeSchema, Callable $callback);

  /*
    Adds a route schema, which will only be matched in case of PUT request
  */
  function addPut($routeSchema, Callable $callback);

  /*
    Adds a route schema, which will only be matched in case of DELETE request
  */
  function addDelete($routeSchema, Callable $callback);

  /*
    Tries to map the `$routeString` to a registered route schema.
    
    If no arguments passed, router relies on incoming request super globals.
    
    If a match found, a corresponding callback is invoked.
    Otherwise, a NoRouteMatchException is thrown.
  */
  function resolve($routeString);
```



**IRouteParser**
```php
  /*
    Parses a $schema and returns a Route object.
    In preset parsers the process includes:
      1. Tokenization
      2. Tokens validation
      3. Parameter names extraction
  */
  function parse($schema);
  
  /*
    Returns `true` if a $path matches $route's schema and `false`, otherwise.
    This process relies solely on route schema stored inside the $route
  */
  function matches(Route $route, $path);
  
  /*
    Returns parameters, which are extracted from $path.
    This process relies solely on route schema stored inside the $route
  */
  function extractParameters(Route $route, $path);

```
