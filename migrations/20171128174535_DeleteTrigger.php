<?php

use Phpmig\Migration\Migration;

class DeleteTrigger extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $query = <<<SQL
CREATE TRIGGER decrement_count AFTER DELETE ON `comments`
            FOR EACH ROW
            BEGIN
                UPDATE `threads` SET `threads`.`count` = `threads`.`count`-1 WHERE `threads`.`thread_id` = OLD.`thread_id`;
                DELETE FROM `threads` WHERE `thread_id` = OLD.`thread_id` AND count = 0;
            END;
SQL;
        $c = $this->getContainer();
        $c['db']->query($query);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $query = "DROP TRIGGER IF EXISTS `decrement_count`";

        $c = $this->getContainer();
        $c['db']->query($query);
    }
}
