<?php

namespace Tests\Unit\Middleware;

use App\Middleware\AuthMiddleware;
use App\Service\AuthService;
use RKA\Session;
use Slim\Http\Environment;
use Slim\Http\Request;

class AuthMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    public function testAddAuth()
    {
        $_SESSION = [];
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'username';
        $_SESSION['oauth_token'] = '12345';

        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI'    => '/',
            ]
        );

        $request = Request::createFromEnvironment($environment);
        $auth = new AuthMiddleware(new AuthService(new Session(), 100));

        $request = $auth->AddAuth($request);

        $this->assertEquals(1, $request->getAttribute('userId'));
        $this->assertEquals(100, $request->getAttribute('adminId'));
        $this->assertEquals(0, $request->getAttribute('isAdmin'));
        $this->assertEquals(1, $request->getAttribute('isLoggedIn'));
        $this->assertEquals('username', $request->getAttribute('username'));
        $this->assertTrue(password_verify('12345', $request->getAttribute('userHash')));
    }
}
