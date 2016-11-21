<?php

namespace PetrKnap\Web\Common;

use Nette\Database\IRow;

class DatabaseRecord
{
    /**
     * @var IRow
     */
    protected $row;

    public function __construct(IRow $row)
    {
        $this->row = $row;
    }

    public function __call($name, $arguments)
    {
        $method = ucfirst(substr($name, 0, 3));
        $column = lcfirst(substr($name, 3));

        return call_user_func_array([$this->row, "offset{$method}"], [$column, @$arguments[0]]);
    }
}
