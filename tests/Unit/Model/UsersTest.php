<?php

namespace Tests\Unit;

use App\Model\User;

class UsersTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $user = new User();
        $user->user_id = 123;
        $user->user_name = 'user_name';
        $user->user_image_url = 'http://example.com/img';
        $user->access_token = 'token';
        $user->access_secret = 'secret';

        $string = <<<TOSTRING
user_id: 123
user_name: user_name
user_image_url: http://example.com/img
access_token: token
access_secret: secret
TOSTRING;

        $this->assertEquals($string, (string)$user);
        $this->assertInternalType('string', (string)$user);
    }
}
