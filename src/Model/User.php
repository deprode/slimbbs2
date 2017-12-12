<?php

namespace App\Model;


class User extends Model
{
    protected $user_id;
    protected $user_name;
    protected $user_image_url;
    protected $access_token;
    protected $access_secret;

    public function __toString()
    {
        return <<<TO_STRING
user_id: $this->user_id
user_name: $this->user_name
user_image_url: $this->user_image_url
access_token: $this->access_token
access_secret: $this->access_secret
TO_STRING;
    }
}