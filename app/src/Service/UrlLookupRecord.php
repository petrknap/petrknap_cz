<?php

namespace PetrKnap\Web\Service;

use PetrKnap\Web\Common\DatabaseRecord;

/**
 * @method int getId()
 * @method string getKeyword()
 * @method string getTitle()
 * @method string getUrl()
 * @method string getTouches()
 */
class UrlLookupRecord extends DatabaseRecord
{
    /**
     * @return bool
     */
    public function isProxy()
    {
        return intval($this->row->offsetGet("proxy")) === 1;
    }
}
