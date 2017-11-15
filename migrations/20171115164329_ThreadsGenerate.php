<?php

use Phpmig\Migration\Migration;

class ThreadsGenerate extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `threads` (
          `thread_id` int(11) NOT NULL,
          `comment_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          PRIMARY KEY (`thread_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $c = $this->getContainer();
        $c['db']->query($query);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $query = "DROP TABLE IF EXISTS `threads`";

        $c = $this->getContainer();
        $c['db']->query($query);
    }
}
