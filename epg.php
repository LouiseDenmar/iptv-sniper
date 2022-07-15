<?php
  $epgs = json_decode(file_get_contents($_GET["json"]));
  $programme_list = array();

  foreach ($epgs as $epg) {
    $xml = new XMLReader();
    $xml->open("compress.zlib://" . $epg_json->url);

    while ($xml->read() && $xml->name !== 'programme') {
    }

    while ($xml->name === 'programme') {
      $programme = new SimpleXMLElement($xml->readOuterXML());
      $key = array_search(strval($programme->attributes()->channel), $epg->channels);

      if ($key !== false)
        $programme_list[strval($programme->attributes()->channel)] = [
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
  }

  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $xml .= "<tv date=\"" . date('Ymd') . "\" generator-info-name=\"IPTV-Sniper\">\n";

  foreach ($epgs as $epg) {
    foreach ($epg->channels as $channel) {
      $xml .= "  <channel id=\"" . $channel["id"] . "\">\n";
      $xml .= "    <display-name>". htmlspecialchars($channel["display-name"]) . "</display-name>\n";
      $xml .= "    <icon src=\"" . $channel["icon"] . "\" />\n";
      $xml .= "    <url>" . $channel["url"] . "</url>\n";
      $xml .= "  </channel>\n";
    }
  }

  $xml .= "</tv>";
  echo $xml;
//end epg.php