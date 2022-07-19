<?php
  set_time_limit(0);
  $sources   = array();
  $responses = array();
  $redirects = array();

  $m3u_source = json_decode(file_get_contents("channels.json"));
  $m3u = "#EXTM3U url-tvg=\"$m3u_source->epg\"\n\n";

  foreach ($m3u_source->channels as $channel)
    foreach ($channel->sources as $source)
      $sources[$source] = $source;

  $responses = check($sources);

  foreach ($m3u_source->channels as $channel) {
    foreach ($channel->sources as $source) {
      if (array_key_exists($source, $responses)) {
        $status = $responses[$source]["status"];

        if ($status == "200" || substr($status, 0, 1) !== "3")
          $m3u .= save($channel, $source, $status);
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
          $m3u .= save($channel, $source, $responses[$source]["status"]);
      }
    }
  }

  $m3u = gzcompress($m3u, 9);
  $sql = "INSERT INTO (id, filename, file) files VALUES (1, 'tv_channels.m3u', '" . mysql_escape_string($m3u) . "') ON DUPLICATE KEY UPDATE id=VALUES(id),filename=VALUES(filename),file='" . mysql_escape_string($m3u) . "')";
  echo query($sql);

  function save($channel, $source, $status) {
    $status = ($status == "200") ? "[✓] " : "[✖] ";
    $m3u = "#EXTINF:-1 ch-number=\"$channel->number\" tvg-id=\"$channel->id\" tvg-name=\"$channel->name\" tvg-logo=\"$channel->logo\" group-title=\"$channel->group\",$status$channel->name\n";
    $m3u .= $source . "\n\n";
    echo "[M3U Generator] Finished checking $channel->name with a status of: " . $status . "\n";
    return $m3u;
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

  function query($sql) {
    $url = getenv('JAWSDB_MARIA_URL');
    $dbparts = parse_url($url);

    $hostname = $dbparts['host'];
    $username = $dbparts['user'];
    $password = $dbparts['pass'];
    $database = ltrim($dbparts['path'],'/');

    $conn = new mysqli($hostname, $username, $password, $database);

    if ($conn->connect_error)
      die("Connection failed: " . $conn->connect_error);

    $result = $conn->query($sql);
    $conn->close();
    return ($result === TRUE) ? $result : $conn->error;
  }
//end link_checker.php