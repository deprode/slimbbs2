<?php

namespace Tests\Unit\Domain;

use App\Domain\LoginFilter;
use App\Model\User;
use App\Repository\UserService;
use App\Service\OAuthService;
use PHPUnit\Framework\TestCase;
use Slim\Http\Request;

class LoginFilterTest extends TestCase
{
    private $filter;
    private $request;

    private $user;
    private $oauth;
    private $user_data;

    protected function setUp()
    {
        parent::setUp();

        $this->user = $this->createMock(UserService::class);
        $this->oauth = $this->createMock(OAuthService::class);

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

        $this->filter = new LoginFilter($this->user, $this->oauth);
        $this->filter->save($this->request);
    }

    public function testSave()
    {
        $this->oauth->method('verifyToken')->willReturn(true);

        $this->user->expects($this->once())->method('convertUser')->willReturn($this->user_data);
        $this->user->expects($this->once())->method('saveUser')->with($this->identicalTo($this->user_data));

        $this->oauth->expects($this->once())->method('loginUser');

        $this->request = $this->createMock(Request::class);
        $this->request->method('getParams')->willReturn([
            'oauth_token'    => 'ok',
            'oauth_verifier' => 'verify',
        ]);

        $this->filter = new LoginFilter($this->user, $this->oauth);
        $this->filter->save($this->request);
    }
}
