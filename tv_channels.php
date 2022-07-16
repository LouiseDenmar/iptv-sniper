<?php
  require __DIR__ . '/vendor/autoload.php';
  $m3u_source = json_decode(file_get_contents("channels.json"));

  $m3u = "#EXTM3U url-tvg=\"$m3u_source->epg\"\n\n";
  file_put_contents("tv_channels.m3u", $m3u);

  foreach ($m3u_source->channels as $channel) {
    $m3u = "#EXTINF:-1 tvg-id=\"$channel->id\" tvg-name=\"$channel->name\" tvg-logo=\"$channel->logo\" group-title=\"$channel->group\",$channel->name\n";

    foreach ($channel->sources as $source) {
      $threader = new \cs\simplemultithreader\Threader(["arguments" => ["source" => $source, "m3u" => $m3u]]);

      $jobId = $threader->thread(function($arguments) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $arguments["source"]);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);

        $result = curl_getinfo($curl);

        curl_close($curl);

        if ($result["http_code"] == "302") {
          $curl = curl_init();
          curl_setopt($curl, CURLOPT_URL, $result["redirect_url"]);
          curl_setopt($curl, CURLOPT_NOBODY, true);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          curl_exec($curl);

          $result = curl_getinfo($curl);

          curl_close($curl);
        }

        $arguments["m3u"] .= ($result["http_code"] == "200") ? $arguments["source"] . "\n\n" : "https://raw.githubusercontent.com/benmoose39/YouTube_to_m3u/main/assets/moose_na.m3u\n\n";
        file_put_contents("tv_channels.m3u", $arguments["m3u"]);
      });
    }
  }
//end tv_channels.php