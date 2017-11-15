<?php

use Phpmig\Migration\Migration;

class ThreadsTrigger extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $query = <<<SQL
CREATE TRIGGER add_thread AFTER INSERT ON `comments`
            FOR EACH ROW
            BEGIN
                INSERT INTO `threads` (`thread_id`, `comment_id`, `user_id`)
                VALUES(NEW.`thread_id`, NEW.`comment_id`, NEW.`user_id`);
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
        $query = "DROP TABLE IF EXISTS `add_thread`";

        $c = $this->getContainer();
        $c['db']->query($query);
    }
}
