<?php

namespace Test\Unit;

use App\Model\User;
use App\Repository\UserService;
use App\Service\DatabaseService;
use Aura\SqlQuery\QueryFactory;

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
        $this->data->user_image_url = 'https://via.placeholder.com/48x48';
        $this->data->access_token = 'token';
        $this->data->access_secret = 'secret';
    }

    public function testGetUser()
    {
        $user_data = new User();
        $user_data->user_id = '1';
        $user_data->user_name = 'testuser';
        $user_data->user_image_url = 'https://via.placeholder.com/48x48';
        $data = [$user_data];

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('fetchAll')->willReturn($data);

        $query = new QueryFactory('common');
        $this->user = new UserService($dbs, $query);

        $this->assertEquals($data[0], $this->user->getUser('user_name'));
        $this->assertInstanceOf(User::class, $this->user->getUser('user_name'));
    }

    /**
     * @expectedException \App\Exception\FetchFailedException
     */
    public function testGetUserHash()
    {
        $access_token = 'access_token';
        $user_data = [];
        $user_data['access_token'] = $access_token;
        $data = [$user_data];

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('fetchAll')->willReturn($data);

        $query = new QueryFactory('common');
        $this->user = new UserService($dbs, $query);

        $this->assertEquals($access_token, $this->user->getUserToken('1234567'));


        $error_dbs = $this->createMock(DatabaseService::class);
        $error_dbs->expects($this->any())->method('execute')->will($this->throwException(new \PDOException()));
        $this->user = new UserService($error_dbs, $query);

        $this->user->getUserToken('1234567');
    }

    public function testConvertUser()
    {
        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('execute')->willReturn(1);

        $query = new QueryFactory('common');
        $this->user = new UserService($dbs, $query);

        $user_info = [
            'id_str'                  => '1',
            'screen_name'             => 'testuser',
            'profile_image_url_https' => 'https://via.placeholder.com/48x48'
        ];
        $access_token = [
            'token'  => 'token',
            'secret' => 'secret'
        ];

        $this->assertEquals($this->data, $this->user->convertUser($user_info, $access_token));
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testSaveUser()
    {
        $query = new QueryFactory('common');

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->any())->method('execute')->willReturn(1);
        $this->user = new UserService($dbs, $query);

        $this->assertNull($this->user->saveUser($this->data));

        $error_dbs = $this->createMock(DatabaseService::class);
        $error_dbs->expects($this->any())->method('execute')->will($this->throwException(new \PDOException()));
        $this->user = new UserService($error_dbs, $query);

        $this->user->saveUser($this->data);
    }

    /**
     * @expectedException \App\Exception\DeleteFailedException
     */
    public function testDeleteAccount()
    {
        $query = new QueryFactory('common');

        // 先に匿名をはじいているかテスト
        $this->user = new UserService(new DatabaseService($this->createMock(\PDO::class)), $query);
        $this->assertFalse($this->user->deleteAccount(0));

        $dbs = $this->createMock(DatabaseService::class);
        $dbs->expects($this->at(0))->method('execute')->willReturn(1);
        $this->user = new UserService($dbs, $query);

        $this->assertTrue($this->user->deleteAccount(1));

        $error_dbs = $this->createMock(DatabaseService::class);
        $error_dbs->expects($this->any())->method('execute')->will($this->throwException(new \PDOException()));
        $this->user = new UserService($error_dbs, $query);

        $this->user->deleteAccount(1);
    }
}
