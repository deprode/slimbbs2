<?php

namespace App\Domain;


use App\Model\Message;
use Slim\Flash\Messages;

class MessageService
{
    private $flash;
    const DEFAULT_KEY = 'Message';

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

    public function setMessage($key = self::DEFAULT_KEY): void
    {
        $message = $this->message($key);
        if (!empty($message)) {
            $this->flash->addMessage($key, $message);
        }
    }
}