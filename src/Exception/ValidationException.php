<?php

namespace App\Exception;

use Throwable;

class ValidationException extends \Exception
{
    public function __construct($message = "バリデーションに失敗しました。", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}