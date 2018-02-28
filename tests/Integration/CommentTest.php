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

class CommentTest extends TestCase
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

    private function login()
    {
        $this->driver->get('http://127.0.0.1:8080/');
        $this->driver->findElement(WebDriverBy::xpath('/html/body/div/header/nav/article[2]/a'))->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('Twitter')
        );

        $this->driver->findElement(WebDriverBy::xpath('//*[@id="username_or_email"]'))
            ->sendKeys(getenv('TWITTER_USERNAME'));
        $this->driver->findElement(WebDriverBy::xpath('//*[@id="password"]'))
            ->sendKeys(getenv('TWITTER_PASSWORD'));

        $this->driver->findElement(WebDriverBy::xpath('//*[@id="allow"]'))->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('Slimbbs')
        );
    }

    private function makeThread()
    {
        $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div[1]/form/label/textarea'))
            ->sendKeys('スレッド作成');
        $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div[1]/form/input[4]'))->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('Toppage')
        );
    }

    private function moveThread()
    {
        $element = $this->driver->findElement(WebDriverBy::linkText('スレッド作成'));
        $element->getLocationOnScreenOnceScrolledIntoView();
        $this->driver->executeScript('arguments[0].scrollIntoView(true);', [$element]);
        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOf($element)
        );
        $element->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('Thread')
        );
    }

    public function testCommentEdit()
    {
        try {
            $this->login();
            $this->makeThread();
            $this->moveThread();
        } catch (TimeOutException $e) {
            return;
        }

        $element = $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div[2]/article/section/footer/button'));
        $this->driver->executeScript('arguments[0].scrollIntoView(true);', [$element]);
        $element->click();
        $this->driver->wait(1);

        $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div[2]/article/section/main/div[2]/form/label/textarea'))
            ->clear()->sendKeys('コメントの更新');
        $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div[2]/article/section/main/div[2]/form/input[4]'))
            ->click();

        $this->driver->navigate()->refresh();
        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('Thread')
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div[2]/article/section/main/div'));

        $this->assertEquals("コメントの更新", $element->getText());
    }

    public function testAddLike()
    {
        try {
            $this->login();
            $this->makeThread();
            $this->moveThread();
        } catch (TimeOutException $e) {
            return;
        }

        $element = $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div[2]/article/section/footer/div[2]/form/input[3]'));
        $this->driver->executeScript('arguments[0].scrollIntoView(true);', [$element]);
        $element->click();
        $this->driver->wait(5)->until(
            WebDriverExpectedCondition::elementValueContains(WebDriverBy::xpath('/html/body/div/div[2]/article/section/footer/div[2]/form/input[3]'), '1')
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div[2]/article/section/footer/div[2]/form/input[3]'));

        $this->assertEquals("そうだね ×1", $element->getAttribute('value'));
    }
}
