<?php
// mb_internal_encoding('UTF-8');

require_once('IRouter.php');
require_once('IRouteParser.php');
require_once('Route.php');

class Router implements IRouter {

  // All registered routes must conform to the following pattern
  const ROUTE_SCHEMA_PATTERN = '/^(\/({)?(\w+)(})?)+$/';
  // Valid URL pattern
  const ROUTE_PATTERN = '/^(\/\w+)+(\?(\S+?=\S+?)?(?(3)(&(?3))*))*$/';

  private $routeBase = '';
  private $routeParser;
  private $routeConfigs = array();

  public function __construct(IRouteParser $routeParser) {
    $this->routeParser = $routeParser;
  }

  // @Override
  public function add($routeSchema, Callable $callback) {
    $this->registerRoute($routeSchema, $callback);
  }

  // @Override
  public function addGet($routeSchema, Callable $callback) {
    $this->registerRoute($routeSchema, $callback, "get");
  }

  // @Override
  public function addPost($routeSchema, Callable $callback) {
    $this->registerRoute($routeSchema, $callback, "post");
  }

  // @Override
  public function addPut($routeSchema, Callable $callback) {
    $this->registerRoute($routeSchema, $callback, "put");
  }

  // @Override
  public function addDelete($routeSchema, Callable $callback) {
    $this->registerRoute($routeSchema, $callback, "delete");
  }

  // @Override
  public function handle($routeString=null) {
    if ($routeString === null) {
      $routeString = $_SERVER["REQUEST_URI"];
    }
    $routeString = trim($routeString);
    // TEMPORARY: remove file extension
    $uri = preg_replace(
      '/^' . preg_quote($this->routeBase, '/') . '/',
      '',
      $routeString
    );
    list($path, $params) = explode("?", $uri, 2);

    // echo "$path\n";
    // echo "$params\n";

    // think of making this O(1)
    foreach ($this->routeConfigs as $config) {
      $route = $config->getRoute();
      if ($this->routeParser->matches($route, $path)) {
        $parameters = $this->routeParser->extractParameters($route, $path);
        // var_dump($parameters);
        $action = $config->getAction($_SERVER["REQUEST_METHOD"]);
        if ($action !== null) {
          call_user_func_array($action, $parameters);
          // $action(...$parameters); !!! >= PHP 5.6 (~3 times faster)
          return;
        }
      }
    }

    throw new Exception("Not Found");
  }

  public function setRouteBase($routeBase) {
    if (!is_string($routeBase)) {
      throw new InvalidArgumentException("Route base must be a string");
    }
    // if ($routeBase[-1] === '/') $routeBase = $routeBase[:-1];
    $this->routeBase = $routeBase;
  }

  private function isRouteSchemaValid($routeSchema) {
    return preg_match(self::ROUTE_SCHEMA_PATTERN, $routeSchema) === 1;
  }

  private function registerRoute($routeSchema, Callable $callback, $httpMethods=null) {
    if (!is_string($routeSchema)) {
      throw new InvalidArgumentException("Route schema must be a string");
    }
    if ($httpMethods !== null && !is_string($httpMethods) && !is_array($httpMethods)) {
      throw new InvalidArgumentException("HTTP method argument must be a string or an array");
    }
    $routeSchema = trim($routeSchema);
    if (!($this->isRouteSchemaValid($routeSchema))) {
      throw new InvalidArgumentException("Supplied route schema doesn't conform to predefined rules");
    }

    $routeConfig = $this->routeConfigs[$routeSchema];
    if ($routeConfig === null) {
      $route = $this->routeParser->parse($routeSchema);
      $routeConfig = new RouteConfig($route);
    }
    $routeConfig->attachAction($callback, $httpMethods);
    $this->routeConfigs[$routeSchema] = $routeConfig;
  }

}

class RouteConfig {

  private $route;
  private $actions = [
    'get' => null,
    'put' => null,
    'post' => null,
    'delete' => null
  ];

  public function __construct(Route $route) {
    $this->route = $route;
  }

  public function attachAction(Callable $callback, $httpMethods) {
    $type = gettype($httpMethods);
    switch ($type) {
      case 'NULL':
        $this->bindAction($callback, array_keys($this->actions));
        break;
      case 'array':
        $this->bindAction($callback, $httpMethods);
        break;
      case 'string':
        $this->bindAction($callback, array($httpMethods));
        break;
      default:
        throw new Exception("Cannot handle methods supplied with $type");
    }
  }

  public function getAction($httpMethod) {
    // echo "Retrieveing action for schema '{$this->route->getSchema()}' and method $httpMethod\n";
    return $this->actions[strtolower($httpMethod)];
  }

  public function getRoute() {
    return $this->route;
  }

  private function bindAction(Callable $callback, $methods) {
    foreach ($methods as $method) {
      $method = strtolower(trim($method));
      if ($this->actions[$method] !== null) {
        throw new LogicException("Cannot bind several actions to the same http method");
      }
      $this->actions[$method] = $callback;
    }
  }


}
