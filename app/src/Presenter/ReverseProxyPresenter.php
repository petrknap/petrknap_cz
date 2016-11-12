<?php

namespace PetrKnap\Web\Presenter;

use Netpromotion\Profiler\Profiler;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Http\Response;
use PetrKnap\Web\Service\ReverseProxyService;

class ReverseProxyPresenter extends Presenter
{
    /**
     * @inject
     * @var ReverseProxyService
     */
    public $reverseProxyService;

    public function renderDefault()
    {
        $url = $this->getParameter("url");
        $isRedirect = $this->getParameter("is_redirect");

        if (empty($url)) {
            throw new BadRequestException();
        }

        /** @noinspection PhpUnusedParameterInspection */
        $this->sendResponse(new CallbackResponse(function ($unused, Response $response) use ($url, $isRedirect) {
            Profiler::start("ReverseProxyPresenter::renderDefault('%s', %d)->sendResponse(...)", $url, $isRedirect);
            ob_start();
            {
                foreach ($response->getHeaders() as $name => $unused) {
                    $response->setHeader($name, null);
                }
                if ($isRedirect) {
                    $response->setCode(IResponse::S301_MOVED_PERMANENTLY);
                    $response->setHeader("Location", $url);
                    $response->setHeader("Content-Length", 0);
                } else {
                    $response->setCode($this->reverseProxyService->getResponseCode($url));
                    foreach ($this->reverseProxyService->getResponseHeaders($url) as $name => $value) {
                        $response->setHeader($name, $value);
                    }
                    print($this->reverseProxyService->getResponseContent($url));
                }
                $response->setHeader("Connection", "close");
            }
            ob_end_flush();
            Profiler::finish("ReverseProxyPresenter::renderDefault('%s', %d)->sendResponse(...)", $url, $isRedirect);
        }));
    }
}
