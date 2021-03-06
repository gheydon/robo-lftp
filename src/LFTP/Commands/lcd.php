<?php
/**
 * @file
 */

namespace Heydon\Robo\Task\LFTP\Commands;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class lcd extends cd {

  private $absolute;

  /**
   * {@inheritdoc}
   */
  public function prepare() {
    $fs = new Filesystem();

    $cwd = $this->parent->getCwd();

    if ($this->absolute = $fs->isAbsolutePath($this->destination)) {
      $relative_path = $fs->makePathRelative($this->destination, $cwd->getPathname());
      $destination = new SplFileInfo($this->destination, dirname($relative_path), $relative_path);
    }
    else {
      $relative_path = $this->destination;
      $destination = realpath($cwd->getPathname() . '/' . $this->destination);
      $destination = new SplFileInfo($destination, dirname($relative_path), $relative_path);
    }

    if (!$destination->isDir()) {
      throw new \BadMethodCallException('No such directory ' . $this->destination);
    }

    $this->parent->setCwd($destination);

    return parent::prepare(); // TODO: Change the autogenerated stub
  }

}