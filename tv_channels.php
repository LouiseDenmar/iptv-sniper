<?php
  require __DIR__ . '/vendor/autoload.php';

  use Amp\Parallel\Worker;
  use Tasks\UrlTester;

  $m3u_source = json_decode(file_get_contents("nette.safe://channels.json"));
  file_put_contents("nette.safe://tv_channels.m3u", "#EXTM3U url-tvg=\"$m3u_source->epg\"\n\n");

  foreach ($m3u_source->channels as $channel)
    foreach ($channel->sources as $source)
      echo Worker\enqueue(new UrlTester($source, $channel));
//end tv_channels.php