<?php
  $epgs = json_decode(file_get_contents($_GET["json"]));
  $event = json_decode(file_get_contents("special_event.json"));

  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $xml .= "<tv date=\"" . date('Ymd') . "\" generator-info-name=\"AdoboTV\">\n";

  if (is_object($event)) {
    $xml .= "  <channel id=\"SpecialEvents\">\n";
    $xml .= "    <display-name>Special Events</display-name>\n";
    $xml .= "    <icon src=\"https://i.imgur.com/vRlLmha.png\" />\n";
    $xml .= "    <url>https://iptv-sniper.herokuapp.com/</url>\n";
    $xml .= "  </channel>\n";
  }

  $ctr = 0;

  foreach ($epgs as $epg) {
    $epg_url = (pathinfo($epg->url, PATHINFO_EXTENSION) == "gz")? "compress.zlib://" . $epg->url : $epg->url;
    $channels_list[]   = getChannels($epg_url, $epg->channels);
    $programmes_list[] = getProgrammes($epg_url, $epg->channels);

    foreach ($channels_list[$ctr] as $channel) {
      $xml .= "  <channel id=\"" . $channel["id"] . "\">\n";
      $xml .= "    <display-name>". htmlspecialchars($channel["display-name"]) . "</display-name>\n";
      $xml .= "    <icon src=\"" . $channel["icon"] . "\" />\n";
      $xml .= "    <url>" . $channel["url"] . "</url>\n";
      $xml .= "  </channel>\n";
    }

    foreach ($programmes_list[$ctr] as $programme) {
      $xml .= "  <programme start=\"" . $programme["start"] . "\" stop=\"" . $programme["stop"] . "\" channel=\"" . $programme["channel"] . "\">\n";
      $xml .= "    <title lang=\"en\">" . htmlspecialchars($programme["title"]) . "</title>\n";
      $xml .= "    <desc lang=\"en\">" . htmlspecialchars($programme["description"]) . "</desc>\n";
      $xml .= "    <category lang=\"en\">" . htmlspecialchars($programme["category"]) . "</category>\n";
      $xml .= "  </programme>\n";
    }

    $ctr++;
  }

  if (is_object($event)) {
    date_default_timezone_set($event->timezone);

    $event_start = DateTime::createFromFormat('ga F j, Y', $event->start)
                           ->setTimezone(new DateTimeZone("Asia/Singapore"))
                           ->format("YmdHis O");

    $event_end   = DateTime::createFromFormat('ga F j, Y', $event->end)
                           ->setTimezone(new DateTimeZone("Asia/Singapore"))
                           ->format("YmdHis O");

    $xml .= "  <programme start=\"" . $event_start . "\" stop=\"" . $event_end . "\" channel=\"SpecialEvents\">\n";
    $xml .= "    <title lang=\"en\">" . htmlspecialchars($event->title) . "</title>\n";
    $xml .= "    <desc lang=\"en\">" . htmlspecialchars($event->description) . "</desc>\n";
    $xml .= "    <category lang=\"en\">" . htmlspecialchars($event->category) . "</category>\n";
    $xml .= "  </programme>\n";
  }

  $xml .= "</tv>";
  $filename = ($_GET["json"] == "epg_config.json") ? "adoboTV.xml" : "cryogenix.xml";

  if (db_insert($filename, $xml))
    echo "[EPG Updater] $filename was successfully updated in the database.\n";

  function getChannels($url, $channels) {
    $xml = new XMLReader();
    $xml->open($url);
    $channels_list = array();

    while ($xml->read() && $xml->name !== 'channel') {}

    while ($xml->name === 'channel') {
      $channel = new SimpleXMLElement($xml->readOuterXML());
      $channel_id = strval($channel->attributes()->id);
      $key = array_search($channel_id, $channels);

      if ($key !== false) {
        $channels_list[$channel_id] = [
          "id"           => $channel_id,
          "display-name" => strval($channel->{'display-name'}),
          "icon"         => null,
          "url"          => strval($channel->{'url'})
        ];

        if (isset($channel->{'icon'}) && $channel->{'icon'} instanceof SimpleXMLElement) {
          $attributes = $channel->{'icon'}->attributes();
          $pathinfo 	= pathinfo($attributes->src);
  
          if (empty($attributes->src)) {
            $channels_list[$channel_id]['icon'] = strval($channel->{'icon'});
            $xml->next('channel');
            unset($channel);
          } 
          elseif (!filter_var($attributes->src, FILTER_VALIDATE_URL)) {
            $channels_list[$channel_id]['icon'] = strval($channel->{'icon'});
            $xml->next('channel');
            unset($channel);
          }
          elseif (empty($pathinfo['extension'])) {
            $channels_list[$channel_id]['icon'] = strval($channel->{'icon'});
            $xml->next('channel');
            unset($channel);
            continue;
          }
          else
            $channels_list[$channel_id]['icon'] = strval($attributes->src);
        }
      }

      $xml->next('channel');
      unset($channel);
    }

    $xml->close();
    return $channels_list;
  }

  function getProgrammes($url, $channels) {
    $xml = new XMLReader();
    $xml->open($url);
    $programme_list = array();

    while ($xml->read() && $xml->name !== 'programme') {}

    while ($xml->name === 'programme') {
      $programme = new SimpleXMLElement($xml->readOuterXML());
      $key = array_search(strval($programme->attributes()->channel), $channels);

      if ($key !== false)
        $programme_list[] = [
          "start"       => strval($programme->attributes()->start),
          "stop"        => strval($programme->attributes()->stop),
          "channel"     => strval($programme->attributes()->channel),
          "title"       => strval($programme->title),
          "description" => strval($programme->desc),
          "category"    => strval($programme->category)
        ];

      $xml->next('programme');
      unset($programme);
    }

    $xml->close();
    return $programme_list;
  }

  function db_insert($filename, $xml) {
    $url = getenv('JAWSDB_MARIA_URL');
    $dbparts = parse_url($url);

    $hostname = $dbparts['host'];
    $username = $dbparts['user'];
    $password = $dbparts['pass'];
    $database = ltrim($dbparts['path'],'/');

    $conn = new mysqli($hostname, $username, $password, $database);

    if ($conn->connect_error)
      die("Connection failed: " . $conn->connect_error);

    $id = ($filename == "adoboTV.xml") ? 2 : 3;
    $file = mysqli_real_escape_string($conn, $xml);
    $sql = "INSERT INTO files (id, filename, file) VALUES ($id, $filename, COMPRESS('$file')) ON DUPLICATE KEY UPDATE id=VALUES(id),filename=VALUES(filename),file=VALUES(file)";
    $result = $conn->query($sql);
    $conn->close();
    return ($result === TRUE) ? $result : $conn->error;
  }
//end epg.php