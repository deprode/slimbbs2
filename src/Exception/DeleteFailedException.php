<?php

namespace App\Exception;

use Throwable;

class DeleteFailedException extends \Exception
{
    public function __construct($message = "データの削除に失敗しました。", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}