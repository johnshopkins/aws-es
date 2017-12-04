<?php

namespace AWSElasticsearch;

class Indices
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

  public function getSettings($params)
  {
    $path = "/" . $params["index"] . "/_settings";

    // get AWS headers
    $options = $this->getRequestOptions("GET", $path);
    $request = new Request($options, $this->credentials);
    $headers = $request->sign()->getHeaders();

    // make request
    $url = $this->getRequestUrl($path);
    $response = $this->http->get($url, ['headers' => $headers]);
    return $response->getBody();
  }

  public function updateAliases($params)
  {
    $path = "/_aliases";
    $body = $this->encodeBody($params["body"]);

    // get AWS headers
    $options = $this->getRequestOptions("POST", $path, $body);
    $request = new Request($options, $this->credentials);
    $headers = $request->sign()->getHeaders();

    // make request
    $url = $this->getRequestUrl($path);
    $response = $this->http->post($url, [
      'headers' => $headers,
      'body' => $body
    ]);
    return $response->getBody();
  }

  public function create($params)
  {
    $path = "/" . $params["index"];
    $body = $this->encodeBody($params["body"]);

    // get AWS headers
    $options = $this->getRequestOptions("PUT", $path, $body);
    $request = new Request($options, $this->credentials);
    $headers = $request->sign()->getHeaders();

    // make request
    $url = $this->getRequestUrl($path);
    $response = $this->http->put($url, [
      'headers' => $headers,
      'body' => $body
    ]);
    return $response->getBody();
  }

  public function delete($params)
  {
    $path = "/" . $params["index"];

    // get AWS headers
    $options = $this->getRequestOptions("DELETE", $path);
    $request = new Request($options, $this->credentials);
    $headers = $request->sign()->getHeaders();

    // make request
    $url = $this->getRequestUrl($path);
    $response = $this->http->delete($url, ['headers' => $headers]);

    return $response->getStatusCode() == 200;
  }
}
