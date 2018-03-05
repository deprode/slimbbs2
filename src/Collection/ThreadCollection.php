<?php


namespace App\Collection;


class ThreadCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var \App\Model\Thread[]
     */
    public $threads;

    public function __construct(array $threads)
    {
        $this->threads = $threads;
    }

    public function count()
    {
        return count($this->threads);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->threads);
    }
}