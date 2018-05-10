<?php

namespace App\Repository;


use App\Exception\DeleteFailedException;
use App\Exception\FetchFailedException;
use App\Exception\SaveFailedException;
use App\Model\User;
use App\Service\DatabaseService;
use Aura\SqlQuery\QueryFactory;

class UserService
{
    private $db;
    private $query;

    /**
     * UserService constructor.
     * @param DatabaseService $db
     * @param QueryFactory $query
     */
    public function __construct(DatabaseService $db, QueryFactory $query)
    {
        $this->db = $db;
        $this->query = $query;
    }

    /**
     * @param string $user_name
     * @return User
     * @throws FetchFailedException
     */
    public function getUser(string $user_name): User
    {
        $select = $this->query->newSelect();
        $select
            ->cols(['user_id', 'user_name', 'user_image_url'])
            ->from('users')
            ->where('user_name = :user_name');

        $values = [
            ':user_name' => ['value' => $user_name, 'type' => \PDO::PARAM_STR],
        ];

        try {
            $data = $this->db->fetchAll($select->getStatement(), $values, User::class);
            if (count($data) !== 1) {
                throw new \PDOException();
            }
            return $data[0];
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }

    /**
     * @param array $user_info
     * @param array $access_token
     * @return User
     */
    public function convertUser($user_info = [], $access_token = []): User
    {
        $user = new User();

        $user->user_id = $user_info['id_str'];
        $user->user_name = $user_info['screen_name'];
        $user->user_image_url = $user_info['profile_image_url_https'];
        $user->access_token = $access_token['token'];
        $user->access_secret = $access_token['secret'];

        return $user;
    }

    /**
     * @param User $user
     * @return void
     * @throws SaveFailedException
     */
    public function saveUser(User $user): void
    {
        $select = $this->query->newSelect();
        $select
            ->from('users')
            ->cols(['user_id'])
            ->where('user_id = :user_id');

        $insert = $this->query->newInsert();
        $insert
            ->into('users')
            ->cols(['user_id', 'user_name', 'user_image_url', 'access_token', 'access_secret']);

        $update = $this->query->newUpdate();
        $update
            ->table('users')
            ->cols([
                'user_id'        => ':user_id',
                'user_name'      => ':user_name',
                'user_image_url' => ':user_image_url',
                'access_token'   => ':user_image_url',
                'access_secret'  => ':access_secret'])
            ->where('user_id = :user_id_value');

        $values = [
            ':user_id'        => ['value' => $user->user_id],
            ':user_name'      => ['value' => $user->user_name],
            ':user_image_url' => ['value' => $user->user_image_url],
            ':access_token'   => ['value' => $user->access_token],
            ':access_secret'  => ['value' => $user->access_secret],
        ];

        try {
            $this->db->beginTransaction();
            $data = $this->db->execute($select->getStatement(), [
                ':user_id' => ['value' => $user->user_id]
            ]);
            if (empty($data)) {
                $this->db->execute($insert->getStatement(), $values);
            } else {
                $values[':user_id_value'] = ['value' => $user->user_id];
                $this->db->execute($update->getStatement(), $values);
            }
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollback();
            throw new SaveFailedException();
        }
    }

    /**
     * @param int $user_id
     * @return bool
     * @throws DeleteFailedException
     */
    public function deleteAccount(int $user_id): bool
    {
        if ($user_id <= 0) {
            return false;
        }

        $delete_user = $this->query->newDelete();
        $delete_user
            ->from('users')
            ->where('user_id = :user_id');

        $delete_comment = $this->query->newDelete();
        $delete_comment
            ->from('comments')
            ->where('user_id = :user_id');

        try {
            $this->db->beginTransaction();
            $this->db->execute($delete_comment->getStatement(), [':user_id' => ['value' => $user_id, 'type' => \PDO::PARAM_INT]]);
            $this->db->execute($delete_user->getStatement(), [':user_id' => ['value' => $user_id, 'type' => \PDO::PARAM_INT]]);
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollback();
            throw new DeleteFailedException();
        }

        return true;
    }
}