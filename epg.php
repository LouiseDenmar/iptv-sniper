<?php
  $epgs = json_decode(file_get_contents($_GET["json"]));

  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $xml .= "<tv date=\"" . date('Ymd') . "\" generator-info-name=\"IPTV-Sniper\">\n";

  $ctr = 0;

  foreach ($epgs as $epg) {
    $channels_list[] = getChannels($epg->url, $epg->channels);
    $programme_list[] = getProgrammes($epg->url, $epg->channels);

    foreach ($channel_list[$ctr] as $channel) {
      $xml .= "  <channel id=\"" . $channel["id"] . "\">\n";
      $xml .= "    <display-name>". htmlspecialchars($channel["display-name"]) . "</display-name>\n";
      $xml .= "    <icon src=\"" . $channel["icon"] . "\" />\n";
      $xml .= "    <url>" . $channel["url"] . "</url>\n";
      $xml .= "  </channel>\n";
    }

    $ctr++;
  }

  $xml .= "</tv>";
  echo $xml;

  function getChannels($url, $channels) {
    $channels_list = array();

    $xml = new XMLReader();
    $xml->open("compress.zlib://" . $url);

    while ($xml->read() && $xml->name !== 'channel') {
    }

    while ($xml->name === 'channel') {
      $channel = new SimpleXMLElement($xml->readOuterXML());
      $key = array_search(strval($channel->attributes()->id), $channels);

      if ($key !== false)
        $channels_list[] = [
          "id"           => strval($channel->attributes()->id),
          "display-name" => strval($channel->{'display-name'}),
          "icon"         => strval($channel->{'icon'}->attributes->src),
          "url"          => strval($channel->{'url'})
        ];

      $xml->next('channel');
      unset($channel);
    }

    $xml->close();
    return $channels_list;
  }

  function getProgrammes($url, $channels) {
    $programme_list = array();

    $xml = new XMLReader();
    $xml->open("compress.zlib://" . $url);

    while ($xml->read() && $xml->name !== 'programme') {
    }

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