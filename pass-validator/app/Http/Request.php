<?php

namespace PassValidator\Http;

class Request {

  public $routesType = null;

  private $uri = [];

  public $method = null;

  public $resource = null;

  public $id = null;

  public $action = null;

  public $route = null;

  public $body = null;

  public $headers = [];

  public function __construct() {
    $this->routesType = $_REQUEST['routes'];

    $this->padronizeUri();

    $this->setMethod()
      ->setResource()
      ->setId()
      ->setAction()
      ->setRoute()
      ->setBody()
      ->setHeaders();
  }

  public function sendResponse($response, $httpCode) {
    $contentTypeHeader = 'text/html; charset=UTF-8';

    if($this->routesType === 'api') $contentTypeHeader = 'application/json';

    header('Content-Type: '.$contentTypeHeader, true);
    header('HTTP/1.1 '.$httpCode);

    if (!is_null($response)) {
      $phpStdOut = fopen('php://output', 'w');
      $response = ($this->routesType === 'api') ? json_encode($response) : $response;
      fwrite($phpStdOut, $response);
      fclose($phpStdOut);
    }
    exit;
  }


  /**
   * Set Methods
   */

  private function setBody() {
    $wrapper = fopen('php://input', 'r');
    $this->body = preg_replace('/(\n)?([\s])+/', '', stream_get_contents($wrapper));
    fclose($wrapper);
    return $this;
  }

  private function setHeaders() {
    $this->headers = apache_request_headers();
    return $this;
  }

  private function setMethod() {
    $this->method = $_SERVER['REQUEST_METHOD'];
    return $this;
  }

  private function setResource() {
    $this->resource = ($this->uri['resource'] === '/') ? '/' : $this->uri['resource'];
    return $this;
  }

  private function setId() {
    $this->id = (empty($this->uri['id']) and $this->uri['id'] !== '0') ? null : $this->uri['id'];
    return $this;
  }

  private function setAction() {
    $this->action = empty($this->uri['action']) ? null : $this->uri['action'];
    return $this;
  }

  private function setRoute() {
    $this->route = ($this->uri['resource'] === '/') ? $this->uri['resource'] : '/' . $this->uri['resource'];

    if (!empty($this->uri['id'])) $this->route .= '/' . $this->uri['id'];

    if (!empty($this->uri['action'])) $this->route .= '/' . $this->uri['action'];
    return $this;
  }


  /**
   * Get Methods
   */

  public function getBody() {
    $body = json_decode($this->body, true);
    if(!$body) $this->sendResponse(['error' => 'Invalid body'], 400);
    return $body;
  }

  public function getHeaders() {
    return $this->headers;
  }

  public function getRoutesType() {
    return $this->routesType;
  }

  public function getMethod() {
    return $this->method;
  }

  public function getResource() {
    return $this->resource;
  }

  public function getId() {
    return $this->id;
  }

  public function getAction() {
    return $this->action;
  }

  public function getRoute() {
    return $this->route;
  }

  public function getLimit() {
    return $this->uri['limit'] ?? null;
  }

  public function getOffset() {
    return $this->uri['offset'] ?? null;
  }

  /**
   * Miscellaneous Methods
   */

  private function padronizeUri() {
    $requestUri = $_REQUEST;
    $uri = array_values(array_filter(explode('/', $requestUri['uri']), function($value) {
      return ($value !== null && $value !== false && $value !== '');
    }));

    unset($requestUri['uri']);

    if(empty($uri)) $uri = [
      0 => '/',
      1 => '',
      2 => ''
    ];

    if($uri[0] === 'api') {
      unset($uri[0]);
      $cleanUri = array_values($uri);
      $uri = $cleanUri;
      unset($cleanUri);
    }

    $uri['resource'] = $uri[0] ?? '';
    $uri['id']       = $uri[1] ?? '';
    $uri['action']   = $uri[2] ?? '';
    unset($uri[0]);
    unset($uri[1]);
    unset($uri[2]);

    foreach ($uri as $key => $value) {
      $requestUri[$key] = $value;
    }

    $this->uri = $requestUri;
    return $this;
  }
}
