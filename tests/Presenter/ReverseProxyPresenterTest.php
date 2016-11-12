<?php

namespace PetrKnap\Web\Test\Presenter;

use Nette\Application\BadRequestException;
use Nette\Application\Responses\CallbackResponse;
use Nette\Http\Request;
use Nette\Http\Response;
use PetrKnap\Web\Test\NetteTestCase;

class ReverseProxyPresenterTest extends NetteTestCase
{
    /**
     * @dataProvider dataPresenterWorks
     * @param string|null $url
     * @param bool|null $isRedirect
     * @param mixed $expected
     * @runInSeparateProcess
     */
    public function testPresenterWorks($url, $isRedirect, $expected)
    {
        if ($expected instanceof \Exception) {
            $this->setExpectedException(get_class($expected));
        }

        $response = $this->runPresenter("ReverseProxy", "default", [
            "url" => $url,
            "is_redirect" => $isRedirect
        ]);

        /** @var CallbackResponse $response */
        $this->assertInstanceOf(CallbackResponse::class, $response);

        $headers = [];
        $httpRequest = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $httpResponse = $this->getMock(Response::class);
        $httpResponse->method("setCode")->willReturnCallback(function ($value) use (&$headers) {
            $headers[null] = $value;
        });
        $httpResponse->method("setHeader")->willReturnCallback(function ($key, $value) use (&$headers) {
            $headers[$key] = $value;
        });

        ob_start();
        /** @var Response $httpResponse */
        $response->send($httpRequest, $httpResponse);
        $content = ob_get_contents();
        ob_end_clean();

        if ($isRedirect) {
            $this->assertArraySubset([
                null => Response::S301_MOVED_PERMANENTLY,
                "Location" => $expected
            ], $headers);
        } else {
            if (isset($expected[0])) {
                $this->assertContains($expected[0], $content);
            }
            if (isset($expected[1])) {
                $this->assertArraySubset($expected[1], $headers);
            }
        }
    }

    public function dataPresenterWorks()
    {
        /** @noinspection SpellCheckingInspection */
        return [
            [null, null, new BadRequestException()],
            [null, false, new BadRequestException()],
            [null, true, new BadRequestException()],
            "redirect" => [
                "http://httpbin.org/html",
                true,
                "http://httpbin.org/html"
            ],
            "proxy" => [
                "http://httpbin.org/html",
                false,
                ["<html>", ["Content-Type" => "text/html; charset=utf-8"]]
            ],
        ];
    }
}
