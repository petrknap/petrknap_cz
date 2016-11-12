<?php

namespace PetrKnap\Web\Test\Service;

use PetrKnap\Web\Service\ReverseProxyService;
use PetrKnap\Web\Test\NetteTestCase;

class ReverseProxyServiceTest extends NetteTestCase
{
    /**
     * @return ReverseProxyService
     */
    private function getService()
    {
        return $this->getContainer()->getByType(ReverseProxyService::class);
    }

    /**
     * @dataProvider dataGetResponseCodeWorks
     * @param string $url
     * @param int $expectedResponseCode
     */
    public function testGetResponseCodeWorks($url, $expectedResponseCode)
    {
        $this->assertEquals($expectedResponseCode, $this->getService()->getResponseCode($url));
    }

    public function dataGetResponseCodeWorks()
    {
        return [
            ["http://httpbin.org/status/200", 200],
            ["http://httpbin.org/status/204", 204],
            ["http://httpbin.org/status/301", 200],
            ["http://httpbin.org/status/403", 403],
            ["http://httpbin.org/status/404", 404]
        ];
    }

    /**
     * @dataProvider dataGetResponseHeadersWorks
     * @param string $url
     * @param array $expectedResponseHeaders
     */
    public function testGetResponseHeadersWorks($url, $expectedResponseHeaders)
    {
        $this->assertArraySubset($expectedResponseHeaders, $this->getService()->getResponseHeaders($url));
    }

    public function dataGetResponseHeadersWorks()
    {
        return [
            ["http://httpbin.org/status/200", [
                "Content-Length" => 0
            ]],
            ["http://httpbin.org/image/png", [
                "Content-Type" => "image/png"
            ]],
            ["http://httpbin.org/response-headers?Content-Type=text%2Fplain%3B%20charset%3DUTF-8", [
                "Content-Type" => "text/plain; charset=UTF-8"
            ]]
        ];
    }

    /**
     * @dataProvider dataGetResponseContentWorks
     * @param string $url
     * @param mixed $expectedResponseContent
     */
    public function testGetResponseContentWorks($url, $expectedResponseContent)
    {
        $this->assertEquals($expectedResponseContent, $this->getService()->getResponseContent($url));
    }

    public function dataGetResponseContentWorks()
    {
        $get = function ($url) {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        };
        return [
            ["http://httpbin.org/status/200", ""],
            ["http://httpbin.org/html", $get("http://httpbin.org/html")],
            ["http://httpbin.org/image/png", $get("http://httpbin.org/image/png")]
        ];
    }
}
