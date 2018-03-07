<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP\Commands;

use Heydon\Robo\Task\LFTP\CommandBase;
use Heydon\Robo\Task\LFTP;
use Robo\Common\CommandArguments;

class open extends CommandBase {

  use CommandArguments;

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
   * open constructor.
   *
   * @param \Heydon\Robo\Task\LFTP $parent
   * @param mixed $remote
   */
  public function __construct(LFTP $parent, $remote = NULL) {
    parent::__construct($parent);

    if (isset($remote)) {
      $this->setRemote($remote);
    }
  }

  /**
   * Set the path to the remote host.
   *
   * @param string|array $remote
   */
  public function setRemote($remote) {
    if (is_array($remote)) {
      $parts = $remote;
    }
    else {
      $parts = parse_url($remote);
    }

    // Add in autocomfirm if it is a sftp.
    // May need to add check for ssh as well. not too sure.
    if ($parts['scheme'] == 'sftp') {
      $this->parent->set('sftp:auto-confirm', 1);
    }

    if (isset($parts['user'])) {
      $this->setUser($parts['user']);
      unset($parts['user']);
    }

    if (isset($parts['pass'])) {
      $this->setPassword($parts['pass']);
      unset($parts['pass']);
    }

    $this->remote = http_build_url($parts);
  }

  public function setUser($user) {
    $this->user = $user;
  }

  public function setPassword($pass) {
    $this->pass = $pass;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare() {
    if (isset($this->user)) {
      $this->option('-u', $this->user);
      if (isset($this->pass)) {
        $this->option(NULL, $this->pass, ',');
      }
    }

    if (!isset($this->remote)) {
      throw new \BadMethodCallException("No destination has been set for open command.");
    }

    $this->arg($this->remote);
    return parent::prepare();
  }
}