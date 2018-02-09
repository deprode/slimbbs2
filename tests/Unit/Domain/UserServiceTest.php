<?php

namespace Test\Unit;

use App\Domain\DatabaseService;
use App\Domain\UserService;
use App\Model\User;

class UserServiceTest extends \PHPUnit_Framework_TestCase
{
    private $user;
    private $data;

    protected function setUp()
    {
        parent::setUp();

        $this->data = new User();
        $this->data->user_id = 1;
        $this->data->user_name = 'testuser';
        $this->data->user_image_url = 'http://via.placeholder.com/48x48';
        $this->data->access_token = 'token';
        $this->data->access_secret = 'secret';
    }

    public function testGetUser()
    {
        $data = [
            [
                'user_id'        => '1',
                'user_name'      => 'testuser',
                'user_image_url' => 'http://via.placeholder.com/48x48',
            ]
        ];

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('fetchAll')->willReturn($data);
        $this->user = new UserService($dbs);

        $this->assertEquals($data[0], $this->user->getUser('user_name'));
    }

    public function testConvertUser()
    {
        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('execute')->willReturn(1);
        $this->user = new UserService($dbs);

        $user_info = [
            'id_str'            => '1',
            'screen_name'       => 'testuser',
            'profile_image_url' => 'http://via.placeholder.com/48x48'
        ];
        $access_token = [
            'token'  => 'token',
            'secret' => 'secret'
        ];

        $this->assertEquals($this->data, $this->user->convertUser((object)$user_info, $access_token));
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testSaveUser()
    {
        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('execute')->willReturn(1);
        $this->user = new UserService($dbs);

        $this->assertEquals(1, $this->user->saveUser($this->data));

        $error_dbs = $this->createMock(DatabaseService::class);
        $error_dbs->expects($this->any())->method('execute')->will($this->throwException(new \PDOException()));
        $this->user = new UserService($error_dbs);

        $this->user->saveUser($this->data);
    }

    /**
     * @expectedException \App\Exception\DeleteFailedException
     */
    public function testDeleteAccount()
    {
        // 先に匿名をはじいているかテスト
        $this->user = new UserService(new DatabaseService($this->createMock(\PDO::class)));
        $this->assertFalse($this->user->deleteAccount(0));

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->at(0))->method('execute')->willReturn(1);
        $this->user = new UserService($dbs);

        $this->assertTrue($this->user->deleteAccount(1));

        $error_dbs = $this->createMock(DatabaseService::class);
        $error_dbs->expects($this->any())->method('execute')->will($this->throwException(new \PDOException()));
        $this->user = new UserService($error_dbs);

        $this->user->deleteAccount(1);
    }
}
