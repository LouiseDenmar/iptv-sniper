<?php
  set_time_limit(0);
  $sources   = array();
  $responses = array();
  $redirects = array();

  $branch = (getenv("env") == "iptv-sniper") ? "master" : "staging";
  $m3u_source = json_decode(parse_m3u("https://raw.githubusercontent.com/jmvbambico/iptv-sniper/$branch/channels.m3u"));
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

    foreach ($m3u_source->channels as $channel)
      foreach ($channel->sources as $source)
        if (array_key_exists($source, $responses))
          $m3u .= save($channel, $source, $responses[$source]["status"]);
  }

  if (db_insert($m3u))
    echo "[M3U Generator] Channel statuses were successfully saved in the database.\n";

  function parse_m3u($m3u) {
    $m3ufile = file_get_contents($m3u);

    $re = '/#EXTINF:(.+?)[,]\s?(.+?)[\r\n]+?((?:https?|rtmp):\/\/(?:\S*?\.\S*?)(?:[\s)\[\]{};"\'<]|\.\s|$))/';
    $attributes = '/([a-zA-Z0-9\-\_]+?)="([^"]*)"/';

    $m3ufile = str_replace("tvg-logo", "logo", $m3ufile);
    $m3ufile = str_replace("tvg-id", "id", $m3ufile);
    $m3ufile = str_replace("tvg-name", "name", $m3ufile);
    $m3ufile = str_replace("group-title", "group", $m3ufile);

    preg_match_all($re, $m3ufile, $matches);

    $ctr = 1;
    $items = array();

    foreach($matches[0] as $list) {
      preg_match($re, $list, $matchList);

      $mediaURL = preg_replace('/[\n\r]/', "", $matchList[3]);
      $mediaURL = preg_replace('/\s+/', "", $mediaURL);

      preg_match_all($attributes, $list, $matches, PREG_SET_ORDER);

      foreach ($matches as $match)
        $newdata[$match[1]] = $match[2];

      foreach ($matches as $match) {
        $newdata["number"] = $ctr;
        $newdata["sources"] = array($mediaURL);
      }

      $items[] = $newdata;
      $ctr++;
    }

    $globalist = array(
        "epg" => "https://" . getenv("env") . ".herokuapp.com/epg/adoboTV.php,https://" . getenv("env") . ".herokuapp.com/epg/skycable.php",
        "channels" => $items
    );

    return json_encode($globalist, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
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
          "status"   => curl_getinfo($info["handle"], CURLINFO_HTTP_CODE),
          "redirect" => curl_getinfo($info["handle"], CURLINFO_REDIRECT_URL)
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

  function save($channel, $source, $status) {
    $status = ($status == "200") ? "[✓] " : "[✖] ";
    $m3u = "#EXTINF:-1 ch-number=\"$channel->number\" tvg-id=\"$channel->id\" tvg-name=\"$channel->name\" tvg-logo=\"$channel->logo\" group-title=\"$channel->group\",$status$channel->name\n";
    $m3u .= $source . "\n\n";
    echo "[M3U Generator] Finished checking $channel->name with a status of: " . $status . "\n";
    return $m3u;
  }

  function db_insert($m3u) {
    $url = getenv("JAWSDB_MARIA_URL");
    $dbparts = parse_url($url);

    $hostname = $dbparts["host"];
    $username = $dbparts["user"];
    $password = $dbparts["pass"];
    $database = ltrim($dbparts["path"], "/");

    $conn = new mysqli($hostname, $username, $password, $database);

    if ($conn->connect_error)
      die("Connection failed: " . $conn->connect_error);

    $file = mysqli_real_escape_string($conn, $m3u);
    $sql = "INSERT INTO files (id, filename, file) VALUES (1, 'tv_channels.m3u', COMPRESS('$file')) ON DUPLICATE KEY UPDATE file=VALUES(file)";
    $result = $conn->query($sql);
    $conn->close();
    return ($result === TRUE) ? $result : $conn->error;
  }
//end link_checker.php