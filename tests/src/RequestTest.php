<?php

namespace AWS4;

use \phpmock\phpunit\PHPMock;

class RequestTest extends \PHPUnit_Framework_TestCase
{
  use PHPMock;

  public function setUp()
	{
    // mock the time function
    $time = $this->getFunctionMock(__NAMESPACE__, "time");
    $time->expects($this->any())->willReturn(strtotime("August 10, 2016 10am"));
	}

  public function testCreateCanonicalRequest()
  {
    // bare bones implementation

    $options = array(
      "method" => "POST",
      "host" => "something.aws.com",
      "path" => "/testing/123",
      "query" => array("test" => "testing"),
      "headers" => array("testing" => "test"),
      "body" => "something cool",
      "service" => "es",
      "region" => "us-east-1"
    );

    $cred = array(
      "accessKeyId" => "accessKeyId_testing",
      "secretAccessKey" => "secretAccessKey_testing"
    );

    $request = new Request($options, $cred);
    $headers = $request->sign()->getHeaders();

    $expected = array(
      "X-Amz-Date" => "20160810T140000Z",
      "Host" => "something.aws.com",
      "Authorization" => "AWS4-HMAC-SHA256 Credential=accessKeyId_testing/20160810/us-east-1/es/aws4_request, SignedHeaders=host;x-amz-date;testing, Signature=77e1abb94122f02fd2674e15b7e471cf9a957a4eecc013ca2995b60733cfd654"
    );

    $this->assertEquals($expected, $headers);
  }
}
