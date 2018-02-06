<?php

namespace Test\Unit;

use App\Domain\DatabaseService;

class DatabaseServiceTest extends \PHPUnit_Framework_TestCase
{
    private $database;
    private $prepare;
    private $data;

    public function setUp()
    {
        parent::setUp();
        $this->data = [
            'comment_id'     => 1,
            'user_id'        => 1,
            'created_at'     => '2017-12-06 13:42:28',
            'user_name'      => 'testuser',
            'user_image_url' => 'http://via.placeholder.com/48x48'
        ];

        $pdo = $this->createMock(\PDO::class);
        $this->prepare = $this->createMock(\PDOStatement::class);
        $this->prepare->expects($this->any())->method('rowCount')->willReturn(1);
        $this->prepare->expects($this->any())->method('fetchAll')->willReturn($this->data);
        $pdo->expects($this->any())->method('prepare')->willReturn($this->prepare);
        $this->database = new DatabaseService($pdo);
    }

    /**
     * @expectedException \PDOException
     */
    public function testFetchAll()
    {
        $sql = 'SELECT `comment` FROM `comments`';

        $this->assertEquals($this->data, $this->database->fetchAll($sql));

        $this->prepare->expects($this->once())->method('fetchAll')->will($this->throwException(new \PDOException()));
        $this->database->fetchAll($sql);
    }

    /**
     * @expectedException \PDOException
     */
    public function testExecute()
    {
        $sql = 'DELETE FROM `comments` WHERE `comment_id` = 1';

        $this->assertEquals(1, $this->database->execute($sql));

        $this->prepare->expects($this->once())->method('execute')->will($this->throwException(new \PDOException()));
        $this->database->execute($sql);
    }

    public function testBeginTransaction()
    {
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('beginTransaction')->willReturn(true);
        $pdo->expects($this->once())->method('beginTransaction');
        $database = new DatabaseService($pdo);
        $database->beginTransaction();
    }

    public function testCommit()
    {
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('commit')->willReturn(true);
        $pdo->expects($this->once())->method('commit');
        $database = new DatabaseService($pdo);
        $database->commit();
    }

    public function testRollback()
    {
        $pdo = $this->createMock(\PDO::class);
        $pdo->method('rollback')->willReturn(true);
        $pdo->expects($this->once())->method('rollback');
        $database = new DatabaseService($pdo);
        $database->rollback();
    }
}
