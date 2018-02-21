<?php


namespace App\Exception;

use Throwable;

class CsrfException extends \Exception
{
    public function __construct($message = "Csrf tokenが一致しません。", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}