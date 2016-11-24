<?php

namespace PetrKnap\Web\Test\Presenter;

use Nette\Application\BadRequestException;
use Nette\Application\Responses\CallbackResponse;
use Nette\Http\Request;
use Nette\Http\Response;
use PetrKnap\Web\Test\NetteTestCase;

class ReverseProxyPresenterTest extends NetteTestCase
{
    public function testHomepageWorks()
    {
        $response = $this->runPresenter("ReverseProxy", "homepage");

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

        $this->assertContains("<html>", $content);
        $this->assertArraySubset(["Content-Type" => "text/html; charset=utf-8"], $headers);
    }

    public function testByKeywordWorks()
    {
        $this->markTestIncomplete("To do"); // TODO
    }
}
