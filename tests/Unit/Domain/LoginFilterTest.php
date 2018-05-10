<?php

namespace Tests\Unit\Domain;

use App\Domain\LoginFilter;
use App\Exception\SaveFailedException;
use App\Model\User;
use App\Repository\UserService;
use App\Service\OAuthService;
use App\Service\StorageService;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

class LoginFilterTest extends TestCase
{
    private $filter;
    private $request;

    private $user;
    private $oauth;
    private $storage;
    private $user_data;

    protected function setUp()
    {
        parent::setUp();

        $this->user = $this->createMock(UserService::class);
        $this->oauth = $this->createMock(OAuthService::class);
        $this->storage = $this->createMock(StorageService::class);

        $this->user_data = new User();
        $this->user->user_id = 'user_id';
        $this->user->user_name = 'user_name';
        $this->user->user_image_url = 'http://example.com/icon';
        $this->user->access_token = 'access_token';
        $this->user->access_secret = 'access_secret';
    }

    /**
     * @expectedException \App\Exception\OAuthException
     */
    public function testSaveError()
    {
        $this->request = $this->createMock(Request::class);
        $this->request->method('getParams')->willReturn([
            'oauth_token'    => null,
            'oauth_verifier' => null,
        ]);

        $this->filter = new LoginFilter($this->user, $this->oauth, $this->storage);
        $this->filter->save($this->request);
    }

    public function testSave()
    {
        $this->oauth->method('verifyToken')->willReturn(true);

        $this->user->expects($this->once())->method('convertUser')->willReturn($this->user_data);
        $this->user->expects($this->once())->method('saveUser')->with($this->identicalTo($this->user_data));

        $user_info = (new \StdClass());
        $user_info->profile_image_url_https = __DIR__ . '/../../data/dummy.png';
        $this->oauth->expects($this->any())->method('getUserInfo')->willReturn($user_info);
        $this->oauth->expects($this->once())->method('loginUser');

        $this->request = $this->createMock(Request::class);
        $this->request->method('getParams')->willReturn([
            'oauth_token'    => 'ok',
            'oauth_verifier' => 'verify',
        ]);

        $this->filter = new LoginFilter($this->user, $this->oauth, $this->storage);
        $this->filter->save($this->request);
    }

    /**
     * @expectedException \App\Exception\SaveFailedException
     */
    public function testFetchUserProfileIconPathError()
    {
        $this->storage = $this->createMock(StorageService::class);
        $this->storage->expects($this->any())->method('getFullPath')->willThrowException(new SaveFailedException());

        $method = new \ReflectionMethod(LoginFilter::class, 'fetchUserProfileIconPath');
        $method->setAccessible(true);

        $this->filter = new LoginFilter($this->user, $this->oauth, $this->storage);
        $method->invokeArgs($this->filter, ['file_not_found.png']);

        $method->invokeArgs($this->filter, [__DIR__ . '/../../data/dummy.png']);
    }

    public function testFetchUserProfileIconPath()
    {
        $this->storage = $this->createMock(StorageService::class);
        $this->storage->expects($this->any())->method('getFullPath')->willReturn('http://example.com/filename');

        $method = new \ReflectionMethod(LoginFilter::class, 'fetchUserProfileIconPath');
        $method->setAccessible(true);

        $this->filter = new LoginFilter($this->user, $this->oauth, $this->storage);

        $this->assertEquals('http://example.com/filename', $method->invokeArgs($this->filter, [__DIR__ . '/../../data/dummy.png']));
    }
}
