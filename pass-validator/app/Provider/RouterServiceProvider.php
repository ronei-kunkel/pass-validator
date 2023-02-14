<?php

namespace PassValidator\Provider;

use PassValidator\Http\Request;

class RouterServiceProvider {

  /**
   * Include the specific file of routes according to nginx rule
   * 
   * If endpoint init with "/api" load api.php file of routes
   * 
   * Otherwise load web.php file of routes if exists 
   *
   * @param Request $request
   * @return void
   */
  public static function loadRoutes(Request $request) {

    $routesFile = __DIR__ . '/../../routes/' . $request->getRoutesType() . '.php';

    if (!file_exists($routesFile)) $request->sendResponse(null, 404);

    include_once $routesFile;
  }
}
