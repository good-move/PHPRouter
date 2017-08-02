<?php

interface IRouter {

  /*
    Adds a new route schema, which can later be mapped to
    using `hangle` method. If a successful match is present, the `callback`
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
    Tries to map the `routeString` to a registered route schema.
    If a match found, a corresponding callback is invoked.
    Otherwise, a NoRouteMatchException is thrown.
  */
  function handle($routeString);

}
