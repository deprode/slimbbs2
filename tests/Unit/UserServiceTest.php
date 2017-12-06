<?php

namespace Test\Unit;

use App\Domain\DatabaseService;
use App\Domain\UserService;
use App\Model\User;

class UserServiceTest extends \PHPUnit_Framework_TestCase
{
    private $comment;
    private $data;

    protected function setUp()
    {
        parent::setUp();

        $this->data = new User();
        $this->data->user_id = 1;
        $this->data->user_name = 'testuser';
        $this->data->user_image_url = 'http://via.placeholder.com/64x64';
        $this->data->access_token = 'token';
        $this->data->access_secret = 'secret';

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('execute')->willReturn(1);
        $this->comment = new UserService($dbs);
    }

    public function testConvertUser()
    {
        $user_info = [
            'id_str'            => '1',
            'screen_name'       => 'testuser',
            'profile_image_url' => 'http://via.placeholder.com/64x64'
        ];
        $access_token = [
            'token'  => 'token',
            'secret' => 'secret'
        ];

        $this->assertEquals($this->data, $this->comment->convertUser((object)$user_info, $access_token));
    }

    public function testSaveUser()
    {
        $this->assertEquals(1, $this->comment->saveUser($this->data));
    }
}
