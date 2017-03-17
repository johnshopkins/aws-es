<?php

namespace AWSElasticsearch;

class Client
{
  protected $defaultRequestOptions = array();

  public function __construct($http, $host, $credentials, $region = "us-east-1")
  {
    $this->http = $http;
    $this->host = $host;
    $this->credentials = $credentials;
    $this->region = $region;

    $this->defaultRequestOptions = array(
      "host" => $this->host,
      "service" => "es",
      "region" => $this->region
    );
  }

  protected function buildEndpoint($params)
  {
    $path = "";

    if (isset($params["index"])) $path .= "/" . $params["index"];
    if (isset($params["type"])) $path .= "/" . $params["type"];
    if (isset($params["id"])) $path .= "/" . $params["id"];

    return $path;
  }

  protected function encodeBody($body)
  {
    return json_encode($body);
  }

  protected function getRequestOptions($method, $path, $body = "")
  {
    return array_merge($this->defaultRequestOptions, array(
      "method" => $method,
      "path" => $path,
      "body" => $body
    ));
  }

  protected function getRequestUrl($path)
  {
    return "http://" . $this->host . $path;
  }

  public function index($params = array())
  {
    $path = $this->buildEndpoint($params);
    $body = $this->encodeBody($params["body"]);

    // get AWS headers
    $options = $this->getRequestOptions("PUT", $path, $body);
    $request = new Request($options, $this->credentials);
    $headers = $request->sign()->getHeaders();

    // make request
    $url = $this->getRequestUrl($path);
    $response = $this->http->put($url, array(), $headers, array("body" => $body));
    return $response->getBody();
  }

  /**
   * Determine if an asset (index, type, document) exists
   * @param  array $params Parameters (index, type, id)
   * @return boolean TRUE if exists; FALSE if not exists
   */
  public function exists($params)
  {
    $path = $this->buildEndpoint($params);

    // get AWS headers
    $options = $this->getRequestOptions("HEAD", $path);
    $request = new Request($options, $this->credentials);
    $headers = $request->sign()->getHeaders();

    // make request
    $url = $this->getRequestUrl($path);
    $response = $this->http->head($url, array(), $headers);

    return $response->getStatusCode() == 200;
  }

  /**
   * Determine if a document exists
   * @param  array $params Parameters (index, type, id)
   * @return boolean TRUE if exists; FALSE if not exists
   */
  public function delete($params)
  {
    $path = $this->buildEndpoint($params);

    // get AWS headers
    $options = $this->getRequestOptions("DELETE", $path);
    $request = new Request($options, $this->credentials);
    $headers = $request->sign()->getHeaders();

    // make request
    $url = $this->getRequestUrl($path);
    $response = $this->http->delete($url, array(), $headers);

    return $response->getStatusCode() == 200;
  }

  public function indices()
  {
    return new Indices($this->http, $this->host, $this->credentials, $this->region);
  }
}
