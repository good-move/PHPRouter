<?php

class Route {

  // example: /library/{author}/{book}
  private $parameterNames;
  private $schema;

  public function __construct($schema, $parameterNames=null) {
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
