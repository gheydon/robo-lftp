<?php
namespace Heydon\Robo\Task\LFTP\LFTP;

/**
 * @file
 */


use Robo\Common\ExecCommand;
use Robo\Task\BaseTask;
use Symfony\Component\Filesystem\Filesystem;

/**
 * LFTPBase
 */
abstract class LFTPBase extends BaseTask {

  use ExecCommand;

  /**
   * Local directory.
   *
   * @var string
   */
  protected $local;

  /**
   * remote directory.
   *
   * @var string
   */
  protected $remote;

  /**
   * Remote user
   *
   * @var string
   */
  protected $user;

  /**
   * Password
   *
   * @var string
   */
  protected $pass;

  /**
   * @param string $local
   *
   * @return $this
   */
  public function localDirectory(string $local) {
    $fs = new Filesystem();
    if ($fs->exists($local)) {
      $this->local = realpath($local);
    }
    return $this;
  }

  public function remote(string $remote) {
    $parts = $this->preRemote($remote);
    $this->postRemote($parts);
    return $this;
  }

  protected function preRemote(string $remote) {
    $parts = parse_url($remote);

    if (isset($parts['user'])) {
      $this->setUser($parts['user']);
      unset($parts['user']);
    }

    if (isset($parts['pass'])) {
      $this->setPassword($parts['pass']);
      unset($parts['pass']);
    }
    
    return $parts;
  }
  
  protected function postRemote(array $parts) {
    $this->remote = http_build_url($parts);
  }
  
  public function setUser(string $user) {
    $this->user = $user;
    return $this;
  }

  public function setPassword(string $pass) {
    $this->pass = $pass;
    return $this;
  }

  protected function getLocal() {
    return $this->local;
  }
}
