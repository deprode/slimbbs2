<?php

use Phpmig\Migration\Migration;

class V03Update extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $c = $this->getContainer();

        $query = "ALTER TABLE `threads` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP;";
        $c['db']->query($query);

        $query = "DROP TRIGGER IF EXISTS `add_thread`";
        $c['db']->query($query);

        $query = <<<SQL
CREATE TRIGGER add_thread AFTER INSERT ON `comments`
    FOR EACH ROW
    BEGIN
        INSERT INTO `threads` (`thread_id`, `comment_id`, `user_id`, `updated_at`)
        VALUES(NEW.`thread_id`, NEW.`comment_id`, NEW.`user_id`, CURRENT_TIMESTAMP)
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

        $query = "ALTER TABLE `threads` DROP COLUMN `updated_at`;";
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
}
