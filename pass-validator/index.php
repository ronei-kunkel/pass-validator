<?php

require_once __DIR__.'/vendor/autoload.php';

if (DEBUG) {
  ini_set('error_reporting', E_ALL);
  ini_set('display_errors', 1);
}

include_once __DIR__.'/public/index.php';
