<?php

require_once('router/RouteParser.php');

use \PHPUnit\Framework\TestCase;

final class TestRegExRouteParser extends TestCase {

  const DEFAULT_SCHEMA_PATTERN = '/^(\/|(\/((\w+(-\w+)*)|({\w+})))+)$/';

  public function setUp() {
    $this->parser = new RouteParser(self::DEFAULT_SCHEMA_PATTERN);
  }

  /**
  * @test
  * @expectedException InvalidArgumentException
  */
  public function shouldNotParseInteger() {
    $schema = 1;
    $this->parser->parse($schema);
  }

  /**
  * @test
  * @expectedException InvalidArgumentException
  */
  public function shouldNotParseFloat() {
    $schema = 4.3;
    $this->parser->parse($schema);
  }

  /**
  * @test
  * @expectedException InvalidArgumentException
  */
  public function shouldNotParseArray() {
    $schema = [];
    $this->parser->parse($schema);
  }

  /**
  * @test
  * @expectedException InvalidArgumentException
  */
  public function shouldNotParseFunction() {
    $schema = function(){};
    $this->parser->parse($schema);
  }

  /**
  * @test
  * @expectedException InvalidArgumentException
  */
  public function shouldNotParseNull() {
    $schema = null;
    $this->parser->parse($schema);
  }

  /**
  * @test
  */
  public function shouldReturnRoute() {
    $parser = new RouteParser('/.*/');
    $route = $parser->parse("schema");
    $this->assertEquals('Route', get_class($route), "A RouteParser must return a Route");
  }

  /**
  * @test
  */
  public function shouldParseValidPaths() {
    $schemas = [
      '/',
      '/root',
      '/root/level1',
      '/root/level1/level2',
      '/root/level1/level2/level3',
      '/root/dashed-level/level'
    ];
    foreach ($schemas as $schema) {
      try {
        $this->parser->parse($schema);
        $this->assertTrue(true);
      }
      catch (InvalidArgumentException $e) {
        $this->assertTrue(false, "Schema $schema should be considered invalid!");
      }
    }
  }

  /**
  * @test
  */
  public function shouldThrowOnInvalidPaths() {
    $schemas = [
      '', // empty string
      'root', // no slashes
      '/root/', // no trailing slash allowed
      '\root/level1', // inverse slash
      '/root+.=?|[]', // prohibited symbols
      '/root/{}', // empty parameter definition
      '/root/{123}', // imvalid parameter name (cannot consist of digits only)
      '/root/{{param}}', // invalid parameter definition
      '/root{id}', // invalid parameter placement
      '/root/{id}/{id}', // repeated parameter name
      '/root/{asdf.+?|=[]}', // invalid parameter format
      '/root/{{p}', // invalid path format
      '/root/{p}}', // invalid path format
      '/root{}/{p}', // invalid path format
      '/root}/{p}', // invalid path format
      '/{ro{ot/{p}', // invalid path format
    ];
    foreach ($schemas as $schema) {
      try {
        $this->parser->parse($schema);
      }
      catch (InvalidArgumentException $e) {
        $this->assertTrue(true);
        continue;
      }
      $this->assertTrue(false, "Schema $schema should be considered invalid!");
    }
  }

  /**
  *@test
  */
  public function shouldParseValidParameterNames() {
    $configurations = [
      ['/{id}', ['id']],
      ['/root/{id}', ['id']],
      ['/root/level1/{id}/{sub_id}', ['id', 'sub_id']],
      ['/root/{parameter}/level2', ['parameter']],
      ['/root/{param}/level2/{parameter_any_level}/level4', ['param', 'parameter_any_level']],
    ];

    foreach ($configurations as $config) {
      $route = $this->parser->parse($config[0]);
      $paramNames = $route->getParameterNames();
      $this->assertEquals(
        true,
        $paramNames == $config[1],
        "Failed to extract schema parameters");
    }
  }

  /**
  *@test
  */
  public function shouldMapSimpleRoutes() {
    $paths = [
      '/',
      '/root',
      '/root/level1',
      '/root/level1/level2',
      '/root/level1/level2/level3'
    ];

    foreach ($paths as $path) {
      // create route and match the exact path
      $route = $this->parser->parse($path);
      $this->assertEquals(true, $this->parser->matches($route, $path), "Failed to match path $path");
    }
  }

  /**
  *@test
  */
  public function shouldMapParametrizedRoutes() {
    $schemas = [
      '/',
      '/root/{id}',
      '/root/{param1}/{param2}',
      '/root/{param1}/level/{param2}',
      '/root/{param1}/level1/level2/{param2}',
    ];

    $paths = [
      [
        '/'
      ],
      [
        '/root/4',
        '/root/abc',
        '/root/abc123',
        '/root/123abc',
        '/root/123_abc_123'
      ],
      [
        '/root/abc/123',
        '/root/123/abc',
        '/root/123abc/abc123',
        '/root/123abc/abc_123_'
      ],
      [
        '/root/author/level/book',
        '/root/author/level/book_id',
        '/root/author/level/147',
        '/root/aut_hor12/level/147gfk'
      ],
      [
        '/root/123/level1/level2/asdfb234'
      ]
    ];

    foreach ($schemas as $index => $schema) {
      $route = $this->parser->parse($schema);
      foreach ($paths[$index] as $path) {
        $this->assertEquals(true, $this->parser->matches($route, $path), "Failed to match schema $path");
      }
    }
  }

  public function shouldExtractExactParameters() {
    $schema = '/library/{author}/book/{chapter}';
    $paths = [
      [
        'path' => '/library/orwell/book/1',
        'arguments' => ['orwell, 1']
      ],
      [
        'path' => '/library/orwell/book/2',
        'arguments' => ['orwell, 2']
      ],
      [
        'path' => '/library/orwell/book/abc3',
        'arguments' => ['orwell, abc3']
      ],
      [
        'path' => '/library/hacksley/book/1',
        'arguments' => ['hacksley, 1']
      ]
    ];

    $route = $this->parser->parse($schema);
    foreach ($paths as $index => $path) {
      $arguments = $this->parser->extractParameters($route, $path['path']);
      $this->assertEquals(
        $path['arguments'],
        $arguments,
        "Invalid arguments extracted from path {$path["path"]}"
      );
    }
  }

}
