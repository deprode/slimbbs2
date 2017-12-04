<?php

namespace App\Domain;


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

    public function existUser(string $user_id): bool
    {
        $sql = <<<EXIST
SELECT
  COUNT(`user_id`)
FROM
  `users`
WHERE
  `user_id` = :user_id;
EXIST;
        return $this->db->exists($sql, [':user_id' => ['value' => $user_id]]);
    }

    public function saveUser(User $user): int
    {
        // isExists
        $exists = $this->existUser($user->user_id);

        if ($exists) {
            $sql = <<<SAVE
UPDATE
  `users`
SET
  `user_name` = :user_name,
  `user_image_url` = :user_image_url,
  `access_token` = :access_token,
  `access_secret` = :access_secret
WHERE
  `user_id` = :user_id
SAVE;
        } else {
            $sql = <<<SAVE
INSERT INTO
  `users` (`user_id`, `user_name`, `user_image_url`, `access_token`, `access_secret`)
VALUES
  (:user_id, :user_name, :user_image_url, :access_token, :access_secret);
SAVE;
        }

        $values = [
            ':user_id' => ['value' => $user->user_id],
            ':user_name' => ['value' => $user->user_name],
            ':user_image_url' => ['value' => $user->user_image_url],
            ':access_token' => ['value' => $user->access_token],
            ':access_secret' => ['value' => $user->access_secret],
        ];

        return $this->db->execute($sql, $values);
    }
}