<?php
namespace Tasks;

use Amp\Parallel\Worker\Environment;
use Amp\Parallel\Worker\Task;

class UrlTester implements Task
{
  private $url;

  public function __construct($url) {
    $this->url = $url;
  }

  public function run(Environment $environment) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->url);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_exec($curl);
    $result = curl_getinfo($curl);
    curl_close($curl);
    return yield $result["http_code"];
  }
}