<?php

namespace Test\Unit;

use App\Domain\MessageService;
use Slim\Flash\Messages;

class MessageServiceTest extends \PHPUnit_Framework_TestCase
{
    private $message;

    public function setUp()
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function testGetMessage()
    {
        $_SESSION['slimFlash']['Info'][0] = 'スレッドを作成しました。';
        // new Messages()で$_SESSIONの中身を読み取るので、その直前にセッションを作成し、読み取らせている
        $this->message = new MessageService(new Messages());

        $this->assertEquals('スレッドを作成しました。', $this->message->getMessage($this->message::INFO));
        $this->assertEquals('', $this->message->getMessage());
    }

    public function testSetMessage()
    {
        $this->message = new MessageService(new Messages());

        $this->message->setMessage();
        self::assertTrue(empty($_SESSION['slimFlash']));
        $this->message->setMessage($this->message::INFO, 'SavedThread');
        self::assertFalse(empty($_SESSION['slimFlash']));
        self::assertEquals('スレッドを作成しました。', $_SESSION['slimFlash'][$this->message::INFO][0]);
    }
}
