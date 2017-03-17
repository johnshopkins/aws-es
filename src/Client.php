<?php

namespace AWSElasticsearch;

class Client
{
  public function __constuct($http, $credentials)
  {
    $this->http = $http;
    $this->credentials = $credentials;
  }

  public function index($params = array())
  {
    print_r($params); die();
  }

  public function indices()
  {

  }

  public function exists()
  {

  }

  public function delete()
  {

  }
}
