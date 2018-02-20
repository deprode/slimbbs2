<?php

namespace Test\Unit;

use App\Service\MessageService;
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

    public function testGetInfoMessage()
    {
        $_SESSION['slimFlash']['Info'][0] = 'スレッドを作成しました。';
        // new Messages()で$_SESSIONの中身を読み取るので、その直前にセッションを作成し、読み取らせている
        $this->message = new MessageService(new Messages());
        $this->assertEquals('スレッドを作成しました。', $this->message->getInfoMessage());

        $_SESSION = [];
        $this->message = new MessageService(new Messages());
        $this->assertEquals('', $this->message->getInfoMessage());
    }

    public function testGetErrorMessage()
    {
        $_SESSION['slimFlash']['Error'][0] = 'スレッドを作成できません。';
        // new Messages()で$_SESSIONの中身を読み取るので、その直前にセッションを作成し、読み取らせている
        $this->message = new MessageService(new Messages());
        $this->assertEquals('スレッドを作成できません。', $this->message->getErrorMessage());

        $_SESSION = [];
        $this->message = new MessageService(new Messages());
        $this->assertEquals('', $this->message->getErrorMessage());
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
