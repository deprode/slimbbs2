<?php

namespace App\Exception;

use Throwable;

class FetchFailedException extends \Exception
{
    public function __construct($message = "データの取得に失敗しました。", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}