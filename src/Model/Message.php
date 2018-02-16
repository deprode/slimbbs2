<?php


namespace App\Model;


class Message
{
    const Message            = '';
    const SavedThread        = 'スレッドを作成しました。';
    const SavedComment       = 'コメントを保存しました。';
    const DeletedThread      = 'スレッドは削除されました。';
    const DeletedComment     = 'コメントを削除しました。';
    const CommentFetchFailed = 'コメントの取得に失敗しました。しばらく時間をおいて、もう一度やり直してください。';
    const ThreadFetchFailed  = 'コメントの取得に失敗しました。スレッドが削除されたかもしれません。';
    const ThreadsFetchFailed = 'スレッドの取得に失敗しました。しばらく時間をおいてから、再度読み込んでください。';
    const LoginFailed        = 'ログインに失敗しました。時間をおいてから、もう一度やり直してください。';
    const UserSaveFailed     = 'ユーザー情報の保存に失敗しました。管理責任者までお問い合わせください。';

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