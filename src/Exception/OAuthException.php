<?php

namespace App\Exception;


use Throwable;

class OAuthException extends \Exception
{
    public function __construct($message = "OAuth認証に失敗しました。", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}