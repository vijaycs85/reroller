<?php

namespace Vijaycs85\Reroller;

use Hussainweb\DrupalApi\Client;
use Hussainweb\DrupalApi\Request\Collection\NodeCollectionRequest;
use Hussainweb\DrupalApi\Request\FileRequest;

/**
 * Class Controller
 *
 * @package Vijaycs85\Reroller
 */
class Controller {

  /**
   * @var \Hussainweb\DrupalApi\Client
   */
  protected $client;

  /**
   * @var string
   */
  protected $drupalRoot;

  /**
   * @var bool
   */
  private $debug;

  /**
   * Controller constructor.
   *
   * @param $drupal_root
   * @param $debug
   */
  public function __construct($drupal_root, $debug) {
    $this->client = new Client();
    $this->drupalRoot = $drupal_root;
    $this->debug = $debug;
  }

  /**
   * Create an object.
   *
   * @param $drupal_root
   * @param bool $debug
   *
   * @return static
   */
  public static function create($drupal_root, $debug = FALSE) {
    return new static($drupal_root, $debug);
  }

  /**
   * Fetch by branch.
   *
   * @param array $query
   *
   * @param null $branch
   */
  public function byBranch($query = [], $branch = NULL) {
    /** @var \Hussainweb\DrupalApi\Entity\node $issue */
    foreach ($this->getIssues($query) as $issue) {
      $files = [];
      $patch_url = NULL;
      foreach ($issue->field_issue_files as $file_data) {
        if ($file_data->file->resource == 'file') {
          $files[$file_data->file->id] = $file_data->file->uri;
        }
      }
      $patch_url = $this->getLatestPatch($files);

      if ($patch_url) {
        $error = $this->applyPatch($patch_url, $branch);
        if ($error) {
          echo 'Can\'t apply the latest patch at https://www.drupal.org/node/' . $issue->nid . "\n";
        }
        else {
          echo "patch in $issue->nid is green!\n";
        }
      }
    }
  }

  /**
   * Helper to get issues.
   *
   * @param string $query
   *   String query.
   *
   * @return \Hussainweb\DrupalApi\Entity\Collection\EntityCollection|\Hussainweb\DrupalApi\Entity\Entity
   *   List of nodes.
   */
  protected function getIssues($query) {
    $issues_request = new NodeCollectionRequest($query);
    return $this->client->getEntity($issues_request);
  }

  /**
   * Helper to run set of commands to apply patch.
   *
   * @param string $url
   *   Absolute URL of patch.
   * @param $branch
   *   String branch. e.g. 8.4.x
   *
   * @return int
   *   0 if success. 1 if error.
   */
  protected function applyPatch($url, $branch) {
    $command_out = NULL;
    if ($this->debug) {
      $command_out = ' &> /dev/null';
    }
    $command = 'cd ' . $this->drupalRoot . '; git checkout ' . $branch . $command_out . '; git reset --hard origin/' . $branch . $command_out . '; git pull origin ' . $branch . $command_out . '; curl -s ' . $url . ' | git apply --index' . $command_out;
    passthru($command, $error);
    return $error;
  }

  /**
   * Helper to get latest patch from given files.
   *
   * @param array $files
   *   An array of file information.
   *
   * @return string|null
   *   Latest patch URL if available. NULL otherwise.
   */
  protected function getLatestPatch($files = []) {
    // Find the latest patch file.
    if (count($files)) {
      krsort($files);
      foreach ($files as $fid => $file) {
        $file_request = new FileRequest($fid);
        $file_info = $this->client->getEntity($file_request);
        if ($file_info->mime == 'text/x-diff') {
          return $file_info->url;
          break;
        }
      }
    }
    return NULL;
  }

}

