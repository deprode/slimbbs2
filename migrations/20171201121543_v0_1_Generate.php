<?php

use Phpmig\Migration\Migration;

class V01Generate extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $c = $this->getContainer();

        $query = "CREATE TABLE IF NOT EXISTS `comments` (
          `comment_id` int(11) NOT NULL AUTO_INCREMENT,
          `thread_id` int(11) NOT NULL,
          `user_id` int(64) NOT NULL,
          `like_count` int(6) NOT NULL DEFAULT 0,
          `comment` varchar(2000) NOT NULL DEFAULT '',
          `photo_url` varchar(2000) NOT NULL DEFAULT '',
          `created_at` datetime NOT NULL,
          `updated_at` datetime DEFAULT NULL,
          PRIMARY KEY (`comment_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $c['db']->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `threads` (
          `thread_id` int(11) NOT NULL,
          `comment_id` int(11) NOT NULL,
          `user_id` int(64) NOT NULL,
          `count` int(11) NOT NULL DEFAULT '1',
          PRIMARY KEY (`thread_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $c['db']->query($query);

        $query = "CREATE TABLE IF NOT EXISTS `users` (
          `user_id` varchar(50) NOT NULL,
          `user_name` varchar(255) NOT NULL DEFAULT 0,
          `user_image_url` varchar(2000) NOT NULL DEFAULT '',
          `access_token` varchar(80) NOT NULL,
          `access_secret` varchar(80) NOT NULL,
          PRIMARY KEY (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $c['db']->query($query);


        $query = <<<SQL
CREATE TRIGGER add_thread AFTER INSERT ON `comments`
    FOR EACH ROW
    BEGIN
        INSERT INTO `threads` (`thread_id`, `comment_id`, `user_id`)
        VALUES(NEW.`thread_id`, NEW.`comment_id`, NEW.`user_id`)
        ON DUPLICATE KEY UPDATE
          count = count + 1;
    END;
SQL;
        $c['db']->query($query);

        $query = <<<SQL
CREATE TRIGGER decrement_count AFTER DELETE ON `comments`
FOR EACH ROW
BEGIN
    UPDATE `threads` SET `threads`.`count` = `threads`.`count`-1 WHERE `threads`.`thread_id` = OLD.`thread_id`;
    DELETE FROM `threads` WHERE `thread_id` = OLD.`thread_id` AND count = 0;
END;
SQL;
        $c['db']->query($query);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $c = $this->getContainer();

        $query = "DROP TABLE IF EXISTS comments";
        $c['db']->query($query);
        $query = "DROP TABLE IF EXISTS `threads`";
        $c['db']->query($query);
        $query = "DROP TABLE IF EXISTS `users`";
        $c['db']->query($query);

        $query = "DROP TRIGGER IF EXISTS `add_thread`";
        $c['db']->query($query);
        $query = "DROP TRIGGER IF EXISTS `decrement_count`";
        $c['db']->query($query);
    }
}
