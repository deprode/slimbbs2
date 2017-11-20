<?php

use Phpmig\Migration\Migration;

class ThreadCount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $c = $this->getContainer();

        $query = "ALTER TABLE `threads` ADD COLUMN `count` int(11) NOT NULL DEFAULT 1;";
        $c['db']->query($query);

        $query = "DROP TRIGGER IF EXISTS `add_thread`";
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

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $c = $this->getContainer();

        $query = "DROP TRIGGER IF EXISTS `add_thread`";
        $c['db']->query($query);

        $query = <<<SQL
CREATE TRIGGER add_thread AFTER INSERT ON `comments`
    FOR EACH ROW
    BEGIN
        INSERT INTO `threads` (`thread_id`, `comment_id`, `user_id`)
        VALUES(NEW.`thread_id`, NEW.`comment_id`, NEW.`user_id`);
    END;
SQL;
        $c['db']->query($query);

        $query = "ALTER TABLE `threads` DROP COLUMN `count`;";
        $c['db']->query($query);
    }
}
