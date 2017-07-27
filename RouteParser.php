<?php

require_once('IRouteParser.php');
// require_once('Route.php');

class RouteParser implements IRouteParser {

  const ROUTE_SCHEMA_PATTERN = '/^(\/({)?(\w+)(})?)+$/';
  const PARAMETER_PATTERN = '/{(\w+)}/';

  public function parse($schema) {
    $pathParameters = [];
    preg_match_all(self::PARAMETER_PATTERN, $schema, $pathParameters);
    // var_dump($pathParameters[1]);

    // check for parameters uniqueness

    $routePattern = preg_replace(self::PARAMETER_PATTERN, '(\w+)', $schema);
    $routePattern = '/^' . str_replace('/', '\/', $routePattern) . '$/';
    // echo "$routePattern\n";
    return new Route($schema, $pathParameters[1]);
  }

  public function matches(Route $route, $path) {
    // echo "Matching '$path' against $this->routePattern\n";
    $routePattern = $this->generateRoutePattern($route);
    return preg_match($routePattern, $path) === 1;
  }

  public function extractParameters(Route $route, $path) {
    $parameters = [];
    $parametersMatch = [];
    $routePattern = $this->generateRoutePattern($route);
    $parameterNames = $route->getParameterNames();
    preg_match_all($routePattern, $path, $parametersMatch);
    foreach ($parameterNames as $index => $name) {
      $parameters[$name] = $parametersMatch[$index+1][0];
    }
    return $parameters;
  }

  private function generateRoutePattern(Route $route) {
    $schema = $route->getSchema();
    $routePattern = preg_replace(self::PARAMETER_PATTERN, '(\w+)', $schema);
    $routePattern = '/^' . str_replace('/', '\/', $routePattern) . '$/';
    return $routePattern;
  }

}
