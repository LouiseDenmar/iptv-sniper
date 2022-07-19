<?php
  set_time_limit(0);
  $sources   = array();
  $responses = array();
  $redirects = array();

  $m3u_source = json_decode(file_get_contents("channels.json"));
  file_put_contents("tv_channels.m3u", "#EXTM3U url-tvg=\"$m3u_source->epg\"\n\n");

  foreach ($m3u_source->channels as $channel)
    foreach ($channel->sources as $source)
      $sources[$source] = $source;

  $responses = check($sources);

  foreach ($m3u_source->channels as $channel) {
    foreach ($channel->sources as $source) {
      if (array_key_exists($source, $responses)) {
        $status = $responses[$source]["status"];

        if ($status == "200" || substr($status, 0, 1) !== "3")
          save($channel, $source, $status);
        else if (substr($status, 0, 1) == "3")
          $redirects[$source] = $responses[$source]["redirect"];
      }
    }
  }

  if (!empty($redirects)) {
    $responses = check($redirects);

    foreach ($m3u_source->channels as $channel) {
      foreach ($channel->sources as $source) {
        if (array_key_exists($source, $responses))
          save($channel, $source, $responses[$source]["status"]);
      }
    }
  }

  function save($channel, $source, $status) {
    $status = ($status == "200") ? "[✓] " : "[✖] ";
    $m3u = "#EXTINF:-1 ch-number=\"$channel->number\" tvg-id=\"$channel->id\" tvg-name=\"$channel->name\" tvg-logo=\"$channel->logo\" group-title=\"$channel->group\",$status$channel->name\n";
    $m3u .= $source . "\n\n";
    file_put_contents("tv_channels.m3u", $m3u, FILE_APPEND);
    echo "[M3U Generator] Finished checking $channel->name with a status of: " . $status . "<br />";
  }

  function check($urls) {
    $mh = curl_multi_init();
    $ch = array();
    $rs = array();
    $keys = array();

    foreach ($urls as $key => $url) {
      $keys[] = $key;
      $ch[$url] = curl_init($url);
      curl_setopt($ch[$url], CURLOPT_NOBODY, true);
      curl_setopt($ch[$url], CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch[$url], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
      curl_setopt($ch[$url], CURLOPT_ENCODING , "");
      curl_multi_add_handle($mh, $ch[$url]);
    }

    $i = 0;
    $running = null;

    do {
      curl_multi_exec($mh, $running);

      if ($running)
        curl_multi_select($mh);

      while (($info = curl_multi_info_read($mh)) !== false) {
        $rs[$keys[$i]] = array(
          "status"   => curl_getinfo($info['handle'], CURLINFO_HTTP_CODE),
          "redirect" => curl_getinfo($info['handle'], CURLINFO_REDIRECT_URL)
        );

        $i++;
      }
    }
    while ($running);

    foreach ($ch as $handle)
      curl_multi_remove_handle($mh, $handle);

    curl_multi_close($mh);

    return $rs;
  }
//end link_checker.php