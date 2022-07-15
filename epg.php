<?php
  $epgs = json_decode(file_get_contents($_GET["json"]));
  $event = json_decode(file_get_contents("special_event.json"));

  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $xml .= "<tv date=\"" . date('Ymd') . "\" generator-info-name=\"IPTV-Sniper\">\n";

  if (is_object($event)) {
    $xml .= "  <channel id=\"SpecialEvents\">\n";
    $xml .= "    <display-name>Special Events</display-name>\n";
    $xml .= "    <icon src=\"https://i.imgur.com/vRlLmha.png\" />\n";
    $xml .= "    <url>https://iptv-sniper.herokuapp.com/</url>\n";
    $xml .= "  </channel>\n";
  }

  $ctr = 0;

  foreach ($epgs as $epg) {
    $filename = pathinfo($epg->url, PATHINFO_EXTENSION);
    $epg_url = ($filename['extension'] == "gz")? "compress.zlib://" . $epg_url : $epg_url;

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
  $filename = ($_GET["json"] == "epg_config.json") ? "iptv-sniper.xml" : "cryogenix.xml";
  file_put_contents("compress.zlib://$filename.gz", $xml);

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
//end epg.php