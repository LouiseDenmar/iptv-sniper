<?php
namespace Tasks;

use Amp\Parallel\Worker\Environment;
use Amp\Parallel\Worker\Task;

class UrlTester implements Task
{
  private $url, $channel_id, $channel_name, $channel_logo, $channel_group;

  public function __construct($url, $channel) {
    $this->url = $url;
    $this->channel_id = $channel->id;
    $this->channel_name = $channel->name;
    $this->channel_logo = $channel->logo;
    $this->channel_group = $channel->group;
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

    $status = ($result["http_code"] == "200") ? "[✓] " : "[✖] ";
    $m3u = "#EXTINF:-1 tvg-id=\"$this->channel_id\" tvg-name=\"$this->channel_name\" tvg-logo=\"$this->channel_logo\" group-title=\"$this->channel_group\",$status$this->channel_name\n";
    $m3u .= $this->url . "\n\n";

    file_put_contents("nette.safe://tv_channels.m3u", $m3u, FILE_APPEND);
    return "M3U processing for $this->url complete.\n";
  }
}