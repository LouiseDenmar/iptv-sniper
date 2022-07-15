<?php
  $epgs = json_decode(file_get_contents($_GET["json"]));

  foreach ($epgs as $epg) {
    $channels_list[] = getChannels($epg->url, $epg->channels);
    $programme_list[] = getProgrammes($epg->url, $epg->channels);
  }

  die("<pre>" . print_r($channels_list, true) . "</pre>");

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