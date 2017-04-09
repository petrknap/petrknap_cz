<?php

namespace PetrKnap\Web\Presenter;

use Netpromotion\Profiler\Profiler;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use Nette\Http\Response;
use PetrKnap\Web\Service\Exception\UrlLookupException;
use PetrKnap\Web\Service\ReverseProxyService;
use PetrKnap\Web\Service\UrlLookupService;

class ReverseProxyPresenter extends Presenter
{
    /**
     * @inject
     * @var ReverseProxyService
     */
    public $reverseProxyService;

    /**
     * @inject
     * @var UrlLookupService
     */
    public $urlLookupService;

    public function renderHomepage()
    {
        $this->doResponse($this->context->getParameters()["homepage"], false);
    }

    public function renderByKeyword($keyword, $extension)
    {
        if ($extension) {
            $keyword .= ".{$extension}";
        }

        try {
            $this->urlLookupService->touchKeyword($keyword, $this->getHttpRequest());
            $record = $this->urlLookupService->getRecordByKeyword($keyword);
            $this->doResponse($record->getUrl(), !$record->isProxy());
        } catch (UrlLookupException $e) {
            throw new BadRequestException($e->getMessage());
        }
    }

    private function doResponse($url, $isRedirect)
    {
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
                    if (substr($url, -3) == ".js") {
                        $response->setContentType("application/javascript");
                    } elseif (substr($url, -4) == ".css") {
                        $response->setContentType("text/css");
                    }
                    $content = $this->reverseProxyService->getResponseContent($url);
                    if (0 === strpos($content,"<!DOCTYPE")) {
                        $response->setContentType("text/html");
                    }
                    print($content);
                }
                $response->setHeader("Connection", "close");
            }
            ob_end_flush();
            Profiler::finish("ReverseProxyPresenter::renderDefault('%s', %d)->sendResponse(...)", $url, $isRedirect);
        }));
    }
}
