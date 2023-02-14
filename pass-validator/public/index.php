<?php

use PassValidator\Http\Request;
use PassValidator\Provider\RouterServiceProvider;
use PassValidator\Router\Router;

$request = new Request();

RouterServiceProvider::loadRoutes($request);

Router::run($request);
