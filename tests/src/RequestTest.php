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
      "host" => "something.aws.com",
      "service" => "es"
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
      "Authorization" => "AWS4-HMAC-SHA256 Credential=accessKeyId_testing/20160810/us-east-1/es/aws4_request, SignedHeaders=host;x-amz-date, Signature=ac42d63fabef52ce9d21788d5327af29747966f94625f5e9adc055835fc0e16d"
    );

    $this->assertEquals($expected, $headers);
  }
}
