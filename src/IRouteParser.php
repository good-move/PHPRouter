<?php

interface IRouteParser {

  function parse($schema);
  function matches(Route $route, $path);
  function extractParameters(Route $route, $path);

}
