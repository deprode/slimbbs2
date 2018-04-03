<?php

namespace App\Repository;

use App\Collection\ThreadCollection;
use App\Exception\FetchFailedException;
use App\Model\Thread;
use App\Service\DatabaseService;
use App\Traits\TimeElapsed;
use Aura\SqlQuery\QueryFactory;

class ThreadService
{
    use TimeElapsed;

    private $db;
    private $query;

    public function __construct(DatabaseService $db, QueryFactory $query)
    {
        $this->db = $db;
        $this->query = $query;
    }

    public function getSortValue($key = 'new'): string
    {
        $values = ['new' => 'DESC', 'old' => 'ASC'];

        return isset($values[$key]) ? $values[$key] : $values['new'];
    }

    /**
     * @param string $sort_key
     * @return ThreadCollection
     * @throws FetchFailedException
     */
    public function getThreads($sort_key = 'new'): ThreadCollection
    {
        $sort_value = $this->getSortValue($sort_key);

        $select = $this->query->newSelect();
        $select
            ->from('threads')
            ->cols(['threads.thread_id', 'comments.comment', 'users.user_name', 'threads.count', 'threads.updated_at'])
            ->join('left', 'comments', 'threads.comment_id = comments.comment_id')
            ->join('left', 'users', 'users.user_id = comments.user_id')
            ->orderBy(['updated_at ' . $sort_value]);

        try {
            return new ThreadCollection($this->db->fetchAll($select->getStatement(), [], Thread::class));
        } catch (\PDOException $e) {
            throw new FetchFailedException();
        }
    }
}