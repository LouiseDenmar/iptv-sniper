<?php
  require __DIR__ . '/vendor/autoload.php';

  use Amp\Parallel\Worker;
  use Amp\Promise;
  use Tasks\UrlTester;

  $m3u_source = json_decode(file_get_contents("nette.safe://channels.json"));
  file_put_contents("nette.safe://tv_channels.m3u", "#EXTM3U url-tvg=\"$m3u_source->epg\"\n\n");

  $promises = [];

  foreach ($m3u_source->channels as $channel)
    foreach ($channel->sources as $source)
      $promises[$channel->id][$source] = Worker\enqueue(new UrlTester($source));

  $responses = Promise\wait(Promise\all($promises));
  var_dump($responses);

  // $status = ($result["http_code"] == "200") ? "[✓] " : "[✖] ";
  // $m3u = "#EXTINF:-1 tvg-id=\"$this->channel_id\" tvg-name=\"$this->channel_name\" tvg-logo=\"$this->channel_logo\" group-title=\"$this->channel_group\",$status$this->channel_name\n";
  // $m3u .= $this->url . "\n\n";

  // file_put_contents("nette.safe://tv_channels.m3u", $m3u, FILE_APPEND);
//end tv_channels.php