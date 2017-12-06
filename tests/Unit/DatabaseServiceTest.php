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
            'user_image_url' => 'http://via.placeholder.com/64x64'
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
}
