<?php

namespace PetrKnap\Web\Service;

use Netpromotion\Profiler\Profiler;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use PetrKnap\Web\Common\CurlFactory;

class ReverseProxyService
{
    const RESPONSE_CODE = "code";
    const RESPONSE_HEADERS = "headers";
    const RESPONSE_CONTENT = "content";

    /**
     * @var array
     */
    private $config;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    public function __construct(array $config, IStorage $storage, CurlFactory $curlFactory)
    {
        $this->config = $config;
        $this->cache = new Cache($storage, str_replace("\\", ".", __CLASS__));
        $this->curlFactory = $curlFactory;
    }

    /**
     * @param string $url
     * @return array
     */
    private function getResponse($url)
    {
        Profiler::start("ReverseProxyService::getRemote('%s')", $url);
        $response = $this->cache->load($url);
        if (!$response) {
            $curl = $this->curlFactory->create();
            $curl->get($url);
            $curl->close();

            $responseHeaders = [];
            foreach ($curl->response_headers as $responseHeader) {
                $separatorPosition = strpos($responseHeader, ":");
                if ($separatorPosition !== false) {
                    $headerName = trim(substr($responseHeader, 0, $separatorPosition));
                    if (preg_match('/Content-(Length|Type)/i', $headerName)) {
                        $responseHeaders[$headerName] = trim(substr($responseHeader, $separatorPosition + 1));
                    }
                }
            }

            $response = [
                self::RESPONSE_CODE => $curl->http_status_code,
                self::RESPONSE_HEADERS => $responseHeaders,
                self::RESPONSE_CONTENT => $curl->response
            ];
            $this->cache->save($url, $response, $this->config["cache"]);
        }
        Profiler::finish("ReverseProxyService::getRemote('%s')", $url);
        return $response;
    }

    /**
     * @param string $url
     * @return int
     */
    public function getResponseCode($url)
    {
        return $this->getResponse($url)[self::RESPONSE_CODE];
    }

    /**
     * @param string $url
     * @return string[]
     */
    public function getResponseHeaders($url)
    {
        return $this->getResponse($url)[self::RESPONSE_HEADERS];
    }

    /**
     * @param string $url
     * @return string
     */
    public function getResponseContent($url)
    {
        return $this->getResponse($url)[self::RESPONSE_CONTENT];
    }
}
