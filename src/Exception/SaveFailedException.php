<?php

namespace App\Exception;


class SaveFailedException extends \Exception
{
    public function __construct($message = "データの保存に失敗しました。", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}