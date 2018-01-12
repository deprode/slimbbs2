<?php

namespace App\Exception;

use Throwable;

class UploadFailedException extends \Exception
{
    public function __construct($message = "ファイルのアップロードに失敗しました。", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}