<?php


namespace App\Model;


class Message
{
    const Message        = '';
    const SavedThread    = 'スレッドを作成しました。';
    const SavedComment   = 'コメントを保存しました。';
    const DeletedThread  = 'スレッドは削除されました。';
    const DeletedComment = 'コメントを削除しました。';

    private $messages;

    public function __construct()
    {
        $ref = new \ReflectionObject($this);
        $this->messages = $ref->getConstants();
    }

    public function __get($key)
    {
        if (!in_array($key, array_keys($this->messages), true)) {
            throw new \InvalidArgumentException();
        }

        return $this->messages[$key];
    }
}