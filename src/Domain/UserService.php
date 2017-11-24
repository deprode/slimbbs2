<?php

namespace App\Domain;


use App\Model\User;

class UserService
{
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function convertUser($user_info = [], $access_token = [])
    {
        $user = new User();

        $user->user_id = $user_info->id_str;
        $user->user_name = $user_info->screen_name;
        $user->user_image_url = $user_info->profile_image_url;
        $user->access_token = $access_token['token'];
        $user->access_secret = $access_token['secret'];

        return $user;
    }

    public function existUser(string $user_id)
    {
        $sql = <<<EXIST
SELECT COUNT(`user_id`) FROM `users` WHERE `user_id` = :user_id
EXIST;
        $prepare = $this->db->prepare($sql);
        $prepare->bindValue(':user_id', $user_id);
        $count = $prepare->fetchColumn();

        return $count > 0;
    }

    public function saveUser(User $user)
    {
        // isExists
        $exists = $this->existUser($user->user_id);

        if ($exists) {
            $sql = <<<UPDATE
UPDATE `users`
SET `user_name` = :user_name, `user_image_url` = :user_image_url, `access_token` = :access_token, `access_secret` = :access_secret
WHERE `user_id` = :user_id
UPDATE;
        } else {
            $sql = <<<SAVE
INSERT INTO `users` (`user_id`, `user_name`, `user_image_url`, `access_token`, `access_secret`)
VALUES (:user_id, :user_name, :user_image_url, :access_token, :access_secret);
SAVE;
        }
        $prepare = $this->db->prepare($sql);
        $prepare->bindValue(':user_id', $user->user_id);
        $prepare->bindValue(':user_name', $user->user_name);
        $prepare->bindValue(':user_image_url', $user->user_image_url);
        $prepare->bindValue(':access_token', $user->access_token);
        $prepare->bindValue(':access_secret', $user->access_secret);
        $prepare->execute();
    }
}