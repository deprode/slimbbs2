<?php

namespace App\Repository;


use App\Exception\DeleteFailedException;
use App\Exception\FetchFailedException;
use App\Exception\SaveFailedException;
use App\Model\User;
use App\Service\DatabaseService;

class UserService
{
    private $db;

    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }

    public function getUser(string $user_name): array
    {
        $sql = <<<SQL
SELECT
  `user_id`, `user_name`, `user_image_url`
FROM
  `users`
WHERE
  `user_name` = :user_name;
SQL;

        $values = [
            ':user_name' => ['value' => $user_name, 'type' => \PDO::PARAM_STR],
        ];

        try {
            $data = $this->db->fetchAll($sql, $values);
            if (count($data) !== 1) {
                throw new \PDOException();
            }
            return $data[0];
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
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

    public function deleteAccount(int $user_id): bool
    {
        if ($user_id <= 0) {
            return false;
        }

        $delete_user = <<<DELETE
DELETE FROM
  `users`
WHERE
  `users`.`user_id` = :user_id;
DELETE;

        $delete_comment = <<<DELETE
DELETE FROM
  `comments`
WHERE
  `comments`.`user_id` = :user_id;
DELETE;

        try {
            $this->db->beginTransaction();
            $this->db->execute($delete_comment, [':user_id' => ['value' => $user_id, 'type' => \PDO::PARAM_INT]]);
            $this->db->execute($delete_user, [':user_id' => ['value' => $user_id, 'type' => \PDO::PARAM_INT]]);
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollback();
            throw new DeleteFailedException();
        }

        return true;
    }
}