<?php

use Phpmig\Migration\Migration;

class FirstGenerate extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `comments` (
          `comment_id` int(11) NOT NULL AUTO_INCREMENT,
          `thread_id` int(11) NOT NULL,
          `user_id` int(11) NOT NULL,
          `like_count` int(6) NOT NULL DEFAULT 0,
          `comment` varchar(2000) NOT NULL DEFAULT '',
          `photo_url` varchar(2000) NOT NULL DEFAULT '',
          `created_at` datetime NOT NULL,
          `updated_at` datetime DEFAULT NULL,
          PRIMARY KEY (`comment_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $c = $this->getContainer();
        $c['db']->query($query);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $query = "DROP TABLE IF EXISTS comments";

        $c = $this->getContainer();
        $c['db']->query($query);
    }
}
