<?php

use Phpmig\Migration\Migration;

class V05DeleteTrigger extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $c = $this->getContainer();

        $query = "DROP TRIGGER IF EXISTS `add_thread`";
        $c['db']->query($query);
        $query = "DROP TRIGGER IF EXISTS `decrement_count`";
        $c['db']->query($query);

        $query = <<<SQL
ALTER TABLE `threads` CHANGE `thread_id` `thread_id` INT(11) NOT NULL AUTO_INCREMENT;
SQL;
        $c['db']->query($query);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $c = $this->getContainer();

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

        $query = <<<SQL
CREATE TRIGGER decrement_count AFTER DELETE ON `comments`
FOR EACH ROW
BEGIN
    UPDATE `threads` SET `threads`.`count` = `threads`.`count`-1 WHERE `threads`.`thread_id` = OLD.`thread_id`;
    DELETE FROM `threads` WHERE `thread_id` = OLD.`thread_id` AND count = 0;
END;
SQL;
        $c['db']->query($query);

        $query = <<<SQL
ALTER TABLE `threads` CHANGE `thread_id` `thread_id` INT(11) NOT NULL;
SQL;
        $c['db']->query($query);
    }
}
