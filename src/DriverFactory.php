<?php

namespace mglaman\WebDriver;


use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeDriverService;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\Service\DriverService;
use Facebook\WebDriver\WebDriverDimension;
use mglaman\WebDriver\Driver\PhantomJs;

class DriverFactory {

  /**
   * @return \Facebook\WebDriver\Chrome\ChromeDriver
   */
  public static function chrome() {
    $capabilities = DesiredCapabilities::chrome();
    $capabilities->setCapability('chromeOptions', [
      'args' => [
        'incognito',
        'headless',
      ]
    ]);
    $port = self::getAvailablePort();
    print PHP_EOL . 'Listening on ' . $port . PHP_EOL;
    $service = new ChromeDriverService(
      getenv(ChromeDriverService::CHROME_DRIVER_EXE_PROPERTY),
      $port,
      [
        '--port=' . $port,
        '--window-size=1920,1080',
        '--start-maximized',
        '--verbose'
      ]
    );
    return ChromeDriver::start($capabilities, $service);
  }

  /**
   * @return \mglaman\PhpLoad\Driver\PhantomJs
   */
  public static function phantomjs() {
    $capabilities = DesiredCapabilities::phantomjs();
    $capabilities->setCapability('phantomjs.page.settings.userAgent', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:25.0) Gecko/20100101 Firefox/25.0');
    $port = self::getAvailablePort();
    $service = new DriverService(
      '/usr/local/bin/phantomjs',
      $port,
      [
        '--webdriver=' . $port,
      ]
    );
    $driver = PhantomJs::start($capabilities, $service);

    $dimensions = new WebDriverDimension(1200, 600);
    $driver->manage()->window()->setSize($dimensions);

    return $driver;
  }

  protected static function getAvailablePort() {
    $address = '0.0.0.0';
    $port = 0;
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_bind($socket, $address, $port);
    socket_listen($socket, 5);
    socket_getsockname($socket, $address, $port);
    return $port;
  }

}
