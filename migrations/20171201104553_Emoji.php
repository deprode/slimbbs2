<?php

use Phpmig\Migration\Migration;

class Emoji extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $c = $this->getContainer();

        $query = "ALTER TABLE `comments` CONVERT TO CHARSET utf8mb4;";
        $c['db']->query($query);
        $query = "ALTER TABLE `threads` CONVERT TO CHARSET utf8mb4;";
        $c['db']->query($query);
        $query = "ALTER TABLE `users` CONVERT TO CHARSET utf8mb4;";
        $c['db']->query($query);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $c = $this->getContainer();

        $query = "ALTER TABLE `comments` CONVERT TO CHARSET utf8;";
        $c['db']->query($query);
        $query = "ALTER TABLE `threads` CONVERT TO CHARSET utf8;";
        $c['db']->query($query);
        $query = "ALTER TABLE `users` CONVERT TO CHARSET utf8;";
        $c['db']->query($query);
    }
}
