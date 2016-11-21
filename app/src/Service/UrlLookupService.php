<?php

namespace PetrKnap\Web\Service;

use Nette\Database\Context;
use Nette\Http\Request;
use PetrKnap\Web\Service\Exception\UrlLookupException\NotFoundException;

class UrlLookupService
{
    /**
     * @var Context
     */
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param string $keyword
     * @return UrlLookupRecord
     * @throws NotFoundException
     */
    public function getRecordByKeyword($keyword)
    {
        /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
        $result = $this->database->query(
            "SELECT * FROM url_lookup__records WHERE keyword = ? LIMIT 1",
            $keyword
        )->fetch();
        if ($result === false) {
            throw new NotFoundException(sprintf(
                "Result for keyword = '%s' not found",
                $keyword
            ));
        }
        return new UrlLookupRecord($result);
    }

    /**
     * @param string $keyword
     * @param Request $request
     * @throws NotFoundException
     */
    public function touchKeyword($keyword, Request $request)
    {
        /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
        $mapId = $this->database->query(
            "SELECT id FROM url_lookup__keyword_to_url_map WHERE keyword = ? LIMIT 1",
            $keyword
        )->fetchField(0);

        if ($mapId === false) {
            throw new NotFoundException(sprintf(
                "Map for keyword = '%s' not found",
                $keyword
            ));
        }

        $this->database->beginTransaction();
        /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
        $this->database->query(
            "INSERT IGNORE INTO url_lookup__user_agents (hashed_user_agent, user_agent)
              VALUES (UNHEX(SHA2(?, 224)), ?)",
            $request->getHeader("User-Agent"),
            $request->getHeader("User-Agent")
        );
        /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
        $this->database->query(
            "INSERT INTO url_lookup__keyword_statistics (keyword_to_url_map_id, hashed_user_agent, address, referrer)
              VALUES (?, UNHEX(SHA2(?, 224)), INET_ATON(?), ?)",
            $mapId,
            $request->getHeader("User-Agent"),
            $request->getRemoteAddress(),
            $request->getReferer()
        );
        $this->database->commit();
    }
}
