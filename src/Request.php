<?php

namespace AWS4;

class Request
{
  /**
   * Request option. Initially filled with default values
   * @var array
   */
  protected $options = array(
    "method" => "GET",
    "host" => "", // required. ex: name-qxod7mwci3cwj4n5p6lt4kypom.us-east-1.es.amazonaws.com
    "path" => "/",
    "query" => array(),
    "headers" => array(),
    "body" => "",
    "service" => "", // required: ex: es
    "region" => "us-east-1"
  );

  /**
   * Date of request (YYYYMMDD)
   * @var integer
   */
  protected $date;

  /**
   * Time of request (YYYYMMDDTHHMMSSZ)
   * @var integer
   */
  protected $datetime;

  /**
   * Headers required by AWS4
   * @var array
   */
  protected $headers = array();

  /**
   * Name of hashing algorithm
   * @var string
   */
  protected $algo = "sha256";

  /**
   * Constructor
   * @param array $options     Request options.
   * @param array $credentials AWS Credentials (accessKeyId and secretAccessKey)
   */
  public function __construct($options = array(), $credentials = array())
  {
    $this->options = array_merge($this->options, $options);
    $this->credentials = $credentials;

    // create some time formats
    $now = time();
    $this->date = gmdate("Ymd", $now);
    $this->datetime = gmdate("Ymd", $now) . "T" . gmdate("Gis", $now) . "Z";

    // create some default headers
    $this->addDefaultHeaders();

    $this->canonical = $this->createCanonicalRequest();
  }

  public function sign()
  {
    $region = $this->options["region"];
    $service = $this->options["service"];

    // create string to sign
    $stringToSign = "AWS4-HMAC-SHA256\n{$this->datetime}\n{$this->date}/{$region}/{$service}/aws4_request\n{$this->canonical}";

    // create signature
    $kSecret = "AWS4" . $this->credentials["secretAccessKey"];
    $kDate = hash_hmac($this->algo, $this->date, $kSecret, true);
    $kRegion = hash_hmac($this->algo, $region, $kDate, true);
    $kService = hash_hmac($this->algo, $service, $kRegion, true);
    $kSigning = hash_hmac($this->algo, "aws4_request", $kService, true);

    $signature = hash_hmac($this->algo, $stringToSign, $kSigning);

    // create Authorization header
    $accessId = $this->credentials["accessKeyId"];
    $signedHeaders = "";

    $this->headers["Authorization"] = "AWS4-HMAC-SHA256 Credential={$accessId}/{$this->date}/{$region}/{$service}/aws4_request, SignedHeaders={$this->signedHeaders}, Signature={$signature}";

    return $this;
  }

  public function getHeaders()
  {
    return $this->headers;
  }

  protected function addDefaultHeaders()
  {
    $this->headers["X-Amz-Date"] = $this->datetime;
    $this->headers["Host"] = $this->options["host"];
  }

  protected function createCanonicalRequest()
  {
    // merge AWS headers with request headers
    $allheaders = array_merge($this->options["headers"], $this->headers);

    // get all parts
    $method = $this->options["method"];
    $path = $this->options["path"];
    $query = $this->createQueryString($this->options["query"]);
    $headers = $this->createHeaderString($allheaders);
    $this->signedHeaders = $this->createSignedHeaderString($allheaders); // save to $this as it will be used later
    $body = hash($this->algo, $this->options["body"]);

    $canonical = "{$method}\n{$path}\n{$query}\n{$headers}\n{$this->signedHeaders}\n{$body}";

    return hash($this->algo, $canonical);
  }

  protected function createQueryString($query)
  {
    ksort($query);

    $pairs = array();

    foreach ($query as $k => $v) {
      $pairs[] = urlencode($k) . "=" . urlencode($v);
    }

    return implode("&", $pairs);
  }

  protected function createHeaderString($headers)
  {
    ksort($headers);
    $pairs = array();

    foreach ($headers as $k => $v) {

      if (is_array($v)) {
        $v = implode(",", $v);
      }

      $pairs[] = trim(strtolower($k)) . ":" . trim($v) . "\n";
    }

    return implode("", $pairs);
  }

  protected function createSignedHeaderString($headers)
  {
    ksort($headers);
    $keys = array_keys($headers);
    return strtolower(implode(";", $keys));
  }
}
