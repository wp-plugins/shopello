<?php
namespace SWP;

use \Curl\Curl;

class SystemTests
{
    /** @var Curl */
    private $curl;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

    public function isCurlInstalled()
    {
        return function_exists('curl_version');
    }

    public function pingShopello()
    {
        $this->curl->reset();

        $this->curl->setUserAgent('Shopello-PHP API Client/1.0 Testclient');
        $this->curl->setOpt(CURLOPT_ENCODING, 'gzip');
        $this->curl->setOpt(CURLOPT_HEADER, false);
        $this->curl->setOpt(CURLOPT_NOBODY, false);

        $this->curl->get('https://se.shopelloapi.com/');

        if (!empty($this->curl->error)) {
            return $this->curl->error . ' (HTTP CODE ' . $this->curl->http_status_code . ')';
        }

        return true;
    }
}
