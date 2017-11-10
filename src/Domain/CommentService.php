<?php

namespace App\Domain;

use Illuminate\Database\Query\Builder;

class CommentService
{
    private $table;

    public function __construct(Builder $table)
    {
        $this->table = $table;
    }

    public function getComments()
    {
        return $this->table->get();
    }
}