<?php

use Phpmig\Migration\Migration;

class UsersGenerate extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `users` (
          `user_id` int(64) NOT NULL,
          `user_name` varchar(255) NOT NULL DEFAULT 0,
          `user_image_url` varchar(2000) NOT NULL DEFAULT '',
          `access_token` varchar(80) NOT NULL,
          `access_secret` varchar(80) NOT NULL,
          PRIMARY KEY (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $c = $this->getContainer();
        $c['db']->query($query);

        $query = "ALTER TABLE `threads` MODIFY COLUMN `user_id` int(64) NOT NULL;";
        $c['db']->query($query);

        $query = "ALTER TABLE `comments` MODIFY COLUMN `user_id` int(64) NOT NULL;";
        $c['db']->query($query);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $query = "DROP TABLE IF EXISTS `users`";

        $c = $this->getContainer();
        $c['db']->query($query);

        $query = "ALTER TABLE `threads` MODIFY COLUMN `user_id` int(11) NOT NULL;";
        $c['db']->query($query);

        $query = "ALTER TABLE `comments` MODIFY COLUMN `user_id` int(11) NOT NULL;";
        $c['db']->query($query);
    }
}
