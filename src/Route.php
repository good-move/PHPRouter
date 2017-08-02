<?php

class Route {

  // path: /library/{author}/{book}
  // parameterNames: [author, book]
  private $parameterNames;
  private $schema;

  public function __construct($schema, $parameterNames=[]) {
    $this->schema = $schema;
    $this->parameterNames = $parameterNames;
  }

  public function getSchema() {
    return $this->schema;
  }

  public function getParameterNames() {
    return $this->parameterNames;
  }

}
