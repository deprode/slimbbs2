<?php


namespace App\Exception;

use Throwable;

class NotAllowedException extends \Exception
{
    public function __construct($message = "アクセスが許可されていません。", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}