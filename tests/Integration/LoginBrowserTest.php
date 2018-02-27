<?php

namespace Tests\Integration;

use Dotenv\Dotenv;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;


class LoginBrowserTest extends \PHPUnit_Framework_TestCase
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

        $host = 'http://localhost:4444/wd/hub';
        $capabilities = DesiredCapabilities::chrome();
        $options = new ChromeOptions();
        $options->addArguments(['--headless']);
        $options->addArguments(["window-size=1024,800"]);
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        $this->driver = RemoteWebDriver::create($host, $capabilities, 5000);
        $this->driver->manage()->deleteAllCookies();
    }

    public function testLogin()
    {
        $this->driver->get('http://localhost:8080/');
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

        $this->assertEquals("Toppage - Slimbbs", $this->driver->getTitle());
        $this->assertNotContains("Error", $this->driver->getTitle());
    }
}
