<?php

require_once('IRouteParser.php');
require_once('Route.php');

class RouteParser implements IRouteParser {

  const DEFAULT_SCHEMA_PATTERN = '/^(\/((\w+(-\w+)*)|({\w+})))+$/';
  const DEFAULT_PARAMETER_PATTERN = '/{(\w+)}/';

  public function __construct($schemaPattern=null, $parameterPattern=null)
  {
    if (!isset($schemaPattern)) {
      $schemaPattern = self::DEFAULT_SCHEMA_PATTERN;
    }
    if (!isset($parameterPattern)) {
      $parameterPattern = self::DEFAULT_PARAMETER_PATTERN;
    }
    if (!is_string($schemaPattern) || !is_string($parameterPattern)) {
      throw new InvalidArgumentException("Parser patterns must be of type string");
    }
    $this->schemaPattern = $schemaPattern;
    $this->parameterPattern = $parameterPattern;
  }

  // @Override
  public function parse($schema) {
    $this->validateSchema($schema);
    $parameterNames = $this->extractParameterNames($schema);
    $this->validateParameterNames($parameterNames[1]);
    return new Route($schema, $parameterNames[1]);
  }

  // @Override
  public function matches(Route $route, $path) {
    // echo "Matching '$path' against $this->routePattern\n";
    $routePattern = $this->generateRoutePattern($route);
    return preg_match($routePattern, $path) === 1;
  }

  // @Override
  public function extractParameters(Route $route, $path) {
    $parameters = [];
    $parametersMatch = [];
    $routePattern = $this->generateRoutePattern($route);
    $parameterNames = $route->getParameterNames();
    preg_match_all($routePattern, $path, $parametersMatch);

    if (count($parametersMatch) < count($parameterNames)) {
      throw new Exception("Path $path doesn't match route schema");
    }

    foreach ($parameterNames as $index => $name) {
      $parameters[$name] = $parametersMatch[$index+1][0];
    }
    return $parameters;
  }

  private function generateRoutePattern(Route $route) {
    $schema = $route->getSchema();
    $routePattern = preg_replace($this->parameterPattern, '(\w+)', $schema);
    $routePattern = '/^' . str_replace('/', '\/', $routePattern) . '$/';
    return $routePattern;
  }

  private function validateSchema($schema) {
    if (!is_string($schema)) {
      throw new InvalidArgumentException('Schema must be a string');
    }
    if (!(preg_match($this->schemaPattern, $schema) === 1)){
      throw new InvalidArgumentException("Supplied route schema doesn't conform to predefined schema pattern");
    }
  }

  private function extractParameterNames($schema) {
    $names = [];
    preg_match_all($this->parameterPattern, $schema, $names);
    return $names;
  }

  private function validateParameterNames(array $names) {
    foreach ($names as $name) {
      if (is_numeric($name)) {
        throw new InvalidArgumentException("Parameter names cannot be digits-only");
      }
    }
    $uniqueNames = array_unique($names);
    if (count($uniqueNames) !== count($names)) {
      throw new InvalidArgumentException("Parameter names must not repeat in a schema");
    }
  }

}
