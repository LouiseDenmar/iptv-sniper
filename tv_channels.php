<?php
  require __DIR__ . '/vendor/autoload.php';
  set_time_limit(0);

  $m3u_source = json_decode(file_get_contents("nette.safe://channels.json"));
  file_put_contents("nette.safe://tv_channels.m3u", "#EXTM3U url-tvg=\"$m3u_source->epg\"\n\n");

  foreach ($m3u_source->channels as $channel) {
    foreach ($channel->sources as $source) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $source);
      curl_setopt($curl, CURLOPT_NOBODY, true);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_exec($curl);

      $result = curl_getinfo($curl);
      curl_close($curl);

      $status = ($result["http_code"] == "200") ? "[✓] " : "[✖] ";
      $m3u = "#EXTINF:-1 ch-number=\"$channel->number\" tvg-id=\"$channel->id\" tvg-name=\"$channel->name\" tvg-logo=\"$channel->logo\" group-title=\"$channel->group\",$status$channel->name\n";
      $m3u .= $this->url . "\n\n";

      file_put_contents("nette.safe://tv_channels.m3u", $m3u, FILE_APPEND);
      echo "Testing results for $channel->name\n$source - " . $result["http_code"] . "\n\n";
    }
  }
//end tv_channels.php