<?php

require_once('router/DefaultRouteParser.php');
require_once('router/Route.php');

use \PHPUnit\Framework\TestCase;

final class TestDefaultRouteParser extends TestCase {

  public function setUp() {
    $this->parser = new DefaultRouteParser();
    $this->schema = 'schema';
    $this->route = $this->parser->parse($this->schema);
  }

  /**
  *@test
  */
  public function test_shouldReturnRoute() {
    $this->assertEquals('Route', get_class($this->route), "A RouteParser must return a Route");
  }

  /**
  *@test
  */
  public function test_shouldNotLookForParameters() {
    $this->assertEquals([], $this->route->getParameterNames(), "DefaultRouteParser must never look for parameters");
  }

  /**
  *@test
  */
  public function test_shouldMatchExact() {
    $this->assertEquals(
      true,
      $this->parser->matches($this->route, $this->schema),
      "DefaultRouteParser must map schema to path");
  }

  /**
  *@test
  */
  public function test_noExtractedParameters() {
    $this->assertEquals(
      [],
      $this->parser->extractParameters($this->route, $this->schema),
      "DefaultRouteParser must never look for parameters"
    );
  }

}
