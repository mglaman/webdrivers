<?php


namespace mglaman\WebDriver;

use Composer\Script\Event;
use Composer\Util\Filesystem;
use Composer\Util\RemoteFilesystem;

class ChromeDriverDownload {

  public static function download(Event $event) {
    $binPath = $event->getComposer()->getConfig()->get('bin-dir');
    $io = $event->getIO();

    if (is_file($binPath . '/chromedriver')) {
      $io->write('ChromeDriver already downloaded');
      return;
    }

    $last_release = trim(file_get_contents('http://chromedriver.storage.googleapis.com/LATEST_RELEASE'));

    switch (PHP_OS) {
      case 'Darwin':
        $platform = 'mac64';
        break;
      case 'WIN32':
      case 'WINNT':
      case 'Windows':
        $platform = 'win32';
        break;
      case 'Linux':
        $platform = 'linux64';
        break;
      default:
        throw new \Exception('Invalid OS?');
    }

    $url = sprintf('http://chromedriver.storage.googleapis.com/%s/chromedriver_%s.zip', $last_release, $platform);
    $io->write(sprintf('Downloading ChromeDriver from %s', $url));

    $remoteFs = new RemoteFilesystem($io);
    $remoteFs->copy($url, $url, $binPath . '/chromedriver.zip');
    $zip = new \ZipArchive();
    $zip->open($binPath . '/chromedriver.zip');
    $zip->extractTo($binPath . '/chromedriver-extracted', ['chromedriver']);
    $zip->close();

    $fs = new Filesystem();
    $fs->copyThenRemove($binPath . '/chromedriver-extracted/chromedriver', $binPath . '/chromedriver');
    $fs->remove($binPath . '/chromedriver.zip');
    chmod($binPath . '/chromedriver', 0751);

    $io->write('Downloaded ChromeDriver');
  }

}
