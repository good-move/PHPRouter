<?php

require_once('IRouteParser.php');
require_once('Route.php');

class DefaultRouteParser implements IRouteParser {

  public function parse($schema) {
    return new Route($schema);
  }

  public function matches(Route $route, $path) {
    return $route->getSchema() === $path;
  }

  public function extractParameters(Route $route, $path) {
    return [];
  }

}
