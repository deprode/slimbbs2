<?php

namespace Tests\Integration;

use Dotenv\Dotenv;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{

    /**
     * @var RemoteWebDriver
     */
    private $driver;

    protected function setUp()
    {
        parent::setUp();

        $env_file = __DIR__ . '/../.env';
        if (is_readable($env_file)) {
            $dot_env = new Dotenv(__DIR__ . '/../');
            $dot_env->load();
        }

        putenv('MYSQL_HOST=' . getenv('MYSQL_LOCAL_HOST'));
        $dns = 'mysql:host=' . getenv('MYSQL_HOST') . ';port=' . getenv('MYSQL_PORT') . ';dbname=' . getenv('MYSQL_DATABASE');
        try {
            $db_connection = new \PDO($dns, getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'));
            $db_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db_connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $sql = 'TRUNCATE TABLE `comments`';
            $prepare = $db_connection->prepare($sql);
            $prepare->execute();
            $sql = 'TRUNCATE TABLE `threads`';
            $prepare = $db_connection->prepare($sql);
            $prepare->execute();

        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit(1);
        }

        $host = 'http://127.0.0.1:4444/wd/hub';
        $capabilities = DesiredCapabilities::chrome();
        $options = new ChromeOptions();
        $options->addArguments(['--headless']);
        $options->addArguments(["window-size=1024,800"]);
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        $this->driver = RemoteWebDriver::create($host, $capabilities, 5000);
        $this->driver->manage()->deleteAllCookies();
    }

    /**
     * 成功時のメッセージが出ているかテスト
     * @throws TimeOutException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     */
    public function testSuccessMessage()
    {
        $this->driver->get('http://127.0.0.1:8080/');
        try {
            $this->driver->wait(30)->until(
                WebDriverExpectedCondition::titleContains('Toppage')
            );
        } catch (TimeOutException $e) {
            return;
        }

        $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div/form/label/textarea'))
            ->sendKeys('スレッド作成');
        $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div/form/input[4]'))->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('Toppage')
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div[1]/ul/li'));

        $this->assertEquals("スレッドを作成しました。", $element->getText());
    }

    /**
     * エラーメッセージが出ているかテスト
     * @throws TimeOutException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     */
    public function testErrorMessage()
    {
        $this->driver->get('http://127.0.0.1:8080/');
        $this->driver->wait(5)->until(
            WebDriverExpectedCondition::titleContains('Toppage')
        );

        $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div/form/input[4]'))->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('Toppage')
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div[1]/ul/li'));

        $this->assertEquals("投稿に失敗しました。投稿内容を見直して、もう一度やり直してください。", $element->getText());
    }

}
