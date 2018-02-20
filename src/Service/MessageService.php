<?php

namespace App\Service;


use App\Model\Message;
use Slim\Flash\Messages;

class MessageService
{
    private $flash;
    const DEFAULT_KEY   = '';
    const DEFAULT_VALUE = 'Message';

    const INFO  = 'Info';
    const ERROR = 'Error';

    public function __construct(Messages $flash)
    {
        $this->flash = $flash;
    }

    private function message($key = self::DEFAULT_KEY): string
    {
        try {
            return (new Message())->$key;
        } catch (\InvalidArgumentException $e) {
            return '';
        }
    }

    public function getMessage($key = self::DEFAULT_KEY): string
    {
        if ($this->flash->hasMessage($key)) {
            return $this->flash->getMessage($key)[0];
        }
        return '';
    }

    public function getInfoMessage(): string
    {
        if ($this->flash->hasMessage(self::INFO)) {
            return $this->flash->getFirstMessage(self::INFO);
        }
        return '';
    }

    public function getErrorMessage(): string
    {
        if ($this->flash->hasMessage(self::ERROR)) {
            return $this->flash->getFirstMessage(self::ERROR);
        }
        return '';
    }

    public function setMessage($key = self::DEFAULT_KEY, $value = self::DEFAULT_VALUE): void
    {
        $message = $this->message($value);
        if (!empty($message)) {
            $this->flash->addMessage($key, $message);
        }
    }
}