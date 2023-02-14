<?php

use PassValidator\Router\Router;

/**
 * rest endpoint
 */
Router::post('/verify', 'PassValidator\Controller\VerifyController::verify');