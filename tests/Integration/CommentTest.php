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

        $_SESSION = [];

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
     * Twitterにログイン
     * @throws TimeOutException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     */
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
            function () {
                return WebDriverExpectedCondition::titleContains('Slimbbs')
                    || WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[@id="challenge_response"]'));
            }
        );

        if (mb_strstr($this->driver->getCurrentURL(), 'twitter.com')) {
            $this->driver->findElement(WebDriverBy::xpath('//*[@id="challenge_response"]'))
                ->sendKeys(getenv('TWITTER_CELLPHONE'));
            $this->driver->findElement(WebDriverBy::xpath('//*[@id="email_challenge_submit"]'))->click();

            $this->driver->wait()->until(
                WebDriverExpectedCondition::titleContains('Twitter')
            );
            $this->driver->findElement(WebDriverBy::xpath('//*[@id="allow"]'))->click();

            $this->driver->wait()->until(
                WebDriverExpectedCondition::titleContains('Slimbbs')
            );
        }
    }

    /**
     * スレッドの作成
     * @throws TimeOutException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     */
    private function makeThread()
    {
        $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div/form/label/textarea'))
            ->sendKeys('スレッド作成');
        $this->driver->findElement(WebDriverBy::xpath('/html/body/div/div/form/input[4]'))->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::titleContains('Toppage')
        );
    }

    /**
     * 作成したスレッドに移動
     * @throws TimeOutException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     */
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
            WebDriverExpectedCondition::titleContains('Slimbbs')
        );
    }

    /**
     * コメントの編集のテスト
     * @throws TimeOutException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     */
    public function testCommentEdit()
    {
        try {
            $this->login();
            $this->makeThread();
            $this->moveThread();
        } catch (TimeOutException $e) {
            return;
        }
        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[@id="c1"]/footer/button'))
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('//*[@id="c1"]/footer/button'));
        $this->driver->executeScript('arguments[0].scrollIntoView(true);', [$element]);
        $element->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[@id="c1"]/main/div[2]/form/label/textarea'))
        );

        $this->driver->findElement(WebDriverBy::xpath('//*[@id="c1"]/main/div[2]/form/label/textarea'))
            ->clear()->sendKeys('コメントの更新');
        $this->driver->findElement(WebDriverBy::xpath('//*[@id="update-1"]'))
            ->click();

        sleep(1);

        $this->driver->navigate()->refresh();
        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[@id="1"]'))
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('//*[@id="1"]'));

        $this->assertEquals("コメントの更新", $element->getText());
    }

    /**
     * 「そうだね」のテスト
     * @throws TimeOutException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     */
    public function testAddLike()
    {
        try {
            $this->login();
            $this->makeThread();
            $this->moveThread();
        } catch (TimeOutException $e) {
            return;
        }

        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[@id="like-1--submit"]'))
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('//*[@id="like-1--submit"]'));
        $this->driver->executeScript('arguments[0].scrollIntoView(true);', [$element]);
        $element->click();
        $this->driver->wait(5)->until(
            WebDriverExpectedCondition::elementValueContains(WebDriverBy::xpath('//*[@id="like-1--submit"]'), '1')
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('//*[@id="like-1--submit"]'));

        $this->assertEquals("そうだね ×1", $element->getAttribute('value'));
    }

    /**
     * 投稿にURLが含まれるかのテスト
     * @throws TimeOutException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     */
    public function testLinkUrl()
    {
        try {
            $this->driver->get('http://127.0.0.1:8080/');
            $this->makeThread();
            $this->moveThread();
        } catch (TimeOutException $e) {
            return;
        }

        $this->driver->findElement(WebDriverBy::xpath('//*[@id="comment_form"]/label[1]/textarea'))
            ->sendKeys('テストテスト' . PHP_EOL . 'https://www.example.com/foo/?bar=baz&inga=42&quux');
        $this->driver->findElement(WebDriverBy::xpath('//*[@id="comment_form"]/input[5]'))->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[@id="2"]/a'))
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('//*[@id="2"]/a'));

        $this->assertEquals("https://www.example.com/foo/?bar=baz&inga=42&quux", $element->getAttribute('href'));
        $this->assertEquals("https://www.example.com/foo/?bar=baz&inga=42&quux", $element->getText());

        $element = $this->driver->findElement(WebDriverBy::xpath('//*[@id="2"]'));
        $this->assertEquals("テストテスト" . PHP_EOL . "https://www.example.com/foo/?bar=baz&inga=42&quux", $element->getText());
    }


    /**
     * 投稿に更新日が含まれるかのテスト
     * @throws TimeOutException
     * @throws \Facebook\WebDriver\Exception\NoSuchElementException
     */
    public function testUpdated()
    {

        try {
            $this->login();
            $this->makeThread();
            $this->moveThread();
        } catch (TimeOutException $e) {
            return;
        }
        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[@id="c1"]/footer/button'))
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('//*[@id="c1"]/footer/button'));
        $this->driver->executeScript('arguments[0].scrollIntoView(true);', [$element]);
        $element->click();

        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[@id="c1"]/main/div[2]/form/label/textarea'))
        );

        $this->driver->findElement(WebDriverBy::xpath('//*[@id="c1"]/main/div[2]/form/label/textarea'))
            ->clear()->sendKeys('コメントの更新');
        $this->driver->findElement(WebDriverBy::xpath('//*[@id="update-1"]'))
            ->click();

        sleep(1);

        $this->driver->navigate()->refresh();
        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[@id="1"]'))
        );

        $element = $this->driver->findElement(WebDriverBy::xpath('//*[@id="c1"]/header/div[2]/span'));

        $this->assertEquals("[編集済み]", $element->getText());

        $this->driver->get('http://127.0.0.1:8080/comment/1');
        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[@id="top"]'))
        );
        
        $element = $this->driver->findElement(WebDriverBy::xpath('//*[@id="top"]/header/div[2]/span'));
        $this->assertEquals("[編集済み]", $element->getText());
    }
}
