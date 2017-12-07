<?php

namespace App\Domain;


use App\Exception\SaveFailedException;
use App\Model\User;

class UserService
{
    private $db;

    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }

    public function convertUser($user_info = [], $access_token = []): User
    {
        $user = new User();

        $user->user_id = $user_info->id_str;
        $user->user_name = $user_info->screen_name;
        $user->user_image_url = $user_info->profile_image_url;
        $user->access_token = $access_token['token'];
        $user->access_secret = $access_token['secret'];

        return $user;
    }

    public function saveUser(User $user): int
    {
        $sql = <<<SAVE
INSERT INTO
  `users` (`user_id`, `user_name`, `user_image_url`, `access_token`, `access_secret`)
VALUES
  (:user_id, :user_name, :user_image_url, :access_token, :access_secret)
ON DUPLICATE KEY UPDATE 
  `user_id` = VALUES(`user_id`),
  `user_name` = VALUES(`user_name`),
  `user_image_url` = VALUES(`user_image_url`),
  `access_token` = VALUES(`access_token`),
  `access_secret` = VALUES(`access_secret`);
SAVE;

        $values = [
            ':user_id'        => ['value' => $user->user_id],
            ':user_name'      => ['value' => $user->user_name],
            ':user_image_url' => ['value' => $user->user_image_url],
            ':access_token'   => ['value' => $user->access_token],
            ':access_secret'  => ['value' => $user->access_secret],
        ];

        try {
            return $this->db->execute($sql, $values);
        } catch (\PDOException $e) {
            throw new SaveFailedException();
        }
    }
}