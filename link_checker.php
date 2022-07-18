<?php
  set_time_limit(0);

  $m3u_source = json_decode(file_get_contents("channels.json"));
  $m3u = "#EXTM3U url-tvg=\"$m3u_source->epg\"\n\n";

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
      $m3u .= "#EXTINF:-1 ch-number=\"$channel->number\" tvg-id=\"$channel->id\" tvg-name=\"$channel->name\" tvg-logo=\"$channel->logo\" group-title=\"$channel->group\",$status$channel->name\n";
      $m3u .= $source . "\n\n";

      echo "[M3U Generator] Testing $channel->name\n$source - " . $result["http_code"] . "\n";
    }
  }

  $conn = new mysqli("lcpbq9az4jklobvq.cbetxkdyhwsb.us-east-1.rds.amazonaws.com", "nsvwm2u733p490gm", "kdj0cijcfrceu8gk", "ofagdoh1n25jv0tw");

  if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

  $file = gzcompress($m3u, 9);
  $sql = "INSERT INTO files (id, filename, file) VALUES (1, 'tv_channels.m3u', '" . $file . "') ON DUPLICATE KEY UPDATE file='" . $file . "';";

  echo ($conn->query($sql) === TRUE) ? "[M3U Generator] Playlist has been updated successfully!" : "[M3U Generator] An error occured while trying to update the playlist: " . $conn->error;
  $conn->close();
//end link_checker.php