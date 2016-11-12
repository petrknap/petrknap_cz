<?php

namespace PetrKnap\Web\Common;

use Curl\Curl;

class CurlFactory implements FactoryInterface
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return Curl
     */
    public function create()
    {
        $curl = new Curl();

        $curl->setUserAgent($this->config["userAgent"]);
        $curl->setReferrer($this->config["referrer"]);
        $curl->setOpt(CURLOPT_CONNECTTIMEOUT_MS, $this->config["connectionTimeOut"]);
        $curl->setOpt(CURLOPT_FOLLOWLOCATION, $this->config["followLocation"]);
        $curl->setOpt(CURLOPT_COOKIEJAR, $this->config["cookieFile"]);
        $curl->setOpt(CURLOPT_COOKIEFILE, $this->config["cookieFile"]);

        return $curl;
    }
}
