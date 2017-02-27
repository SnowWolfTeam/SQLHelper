<?php
namespace SQLHelper\Exception;

use Exception;

class SQLHelperException extends Exception
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return __CLASS__ . ":[{Line:$this->line}]: {$this->message}\n";
    }
}