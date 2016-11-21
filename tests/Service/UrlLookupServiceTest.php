<?php

namespace PetrKnap\Web\Test\Service;

use Nette\Http\Request;
use Nette\Http\UrlScript;
use PetrKnap\Web\Service\Exception\UrlLookupException;
use PetrKnap\Web\Service\Exception\UrlLookupException\NotFoundException;
use PetrKnap\Web\Service\UrlLookupService;
use PetrKnap\Web\Test\NetteTestCase;

class UrlLookupServiceTest extends NetteTestCase
{
    /**
     * @return UrlLookupService
     */
    private function getService()
    {
        return $this->getContainer()->getByType(UrlLookupService::class);
    }

    /**
     * @inheritdoc
     */
    protected function refillDatabase($connection)
    {
        /** @noinspection SqlNoDataSourceInspection */
        $connection->query("DELETE FROM url_lookup__keyword_statistics");
        /** @noinspection SqlNoDataSourceInspection */
        $connection->query("DELETE FROM url_lookup__keyword_to_url_map");
        /** @noinspection SqlNoDataSourceInspection */
        $connection->query(
            "INSERT INTO url_lookup__keyword_to_url_map",
            [
                [
                    "id" => 1,
                    "keyword" => "known",
                    "url" => "https://httpbin.org/html",
                    "proxy" => false
                ],
                [
                    "id" => 2,
                    "keyword" => "redirect",
                    "url" => "https://httpbin.org/html",
                    "proxy" => false
                ],
                [
                    "id" => 3,
                    "keyword" => "proxy",
                    "url" => "https://httpbin.org/html",
                    "proxy" => true
                ]
            ]
        );
    }

    /**
     * @dataProvider dataGetRecordByKeywordWorks
     * @param string $keyword
     * @param string $expectedUrl
     * @param bool $expectedProxy
     */
    public function testGetRecordByKeywordWorks($keyword, $expectedUrl, $expectedProxy)
    {
        if ($expectedUrl instanceof UrlLookupException) {
            $this->setExpectedException(get_class($expectedUrl));
        }

        $record = $this->getService()->getRecordByKeyword($keyword);

        $this->assertEquals($expectedUrl, $record->getUrl());
        $this->assertEquals($expectedProxy, $record->isProxy());
    }

    public function dataGetRecordByKeywordWorks()
    {
        return [
            ["redirect", "https://httpbin.org/html", false],
            ["proxy", "https://httpbin.org/html", true],
            ["unknown", new NotFoundException()]
        ];
    }

    /**
     * @dataProvider dataTouchKeywordWorks
     * @param string $keyword
     * @param Request $request
     * @param int $expectedTouches
     */
    public function testTouchKeywordWorks($keyword, $request, $expectedTouches)
    {
        if ($expectedTouches instanceof UrlLookupException) {
            $this->setExpectedException(get_class($expectedTouches));
        }

        $this->getService()->touchKeyword($keyword, $request);

        $this->assertEquals($expectedTouches, $this->getService()->getRecordByKeyword($keyword)->getTouches());
    }

    public function dataTouchKeywordWorks()
    {
        $request = new Request(
            new UrlScript("phpunit://test"),
            null,
            null,
            null,
            null,
            [
                "User-Agent" => __CLASS__
            ],
            Request::GET,
            "1.2.3.4"
        );
        return [
            ["redirect", $request, 1],
            ["proxy", $request, 1],
            ["unknown", $request, new NotFoundException()]
        ];
    }
}
