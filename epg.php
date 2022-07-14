<?php
  require 'EpgParser.php';

  $Parser         = new \buibr\xmlepg\EpgParser();
  $epgs           = json_decode(file_get_contents($_GET["json"]));
  $channel_list   = array();
  $programme_list = array();

  foreach ($epgs as $epg) {
    $Parser->setUrl($epg->url);
    $Parser->setTargetTimeZone('Asia/Singapore');

    foreach ($epg->channels as $channel)
      $Parser->setChannelfilter($channel);

    try {
      $Parser->parseUrl();
    } catch (Exception $e) {
      throw new \RuntimeException($e);
    }

    $channels = array_values($Parser->getChannels());

    foreach ($epg->channels as $channel) {
      $key = array_search($channel, array_column($channels, "id"));

      if ($key !== false)
        $channel_list[] = $channels[$key];
    }

    $programme_list = array_merge($programme_list, $Parser->getEpgdata());
  }

  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $xml .= "<tv generator-info-name=\"IPTV-Sniper\">\n";

  foreach ($channel_list as $channel) {
    $xml .= "  <channel id=\"" . $channel["id"] . "\">\n";
    $xml .= "    <display-name>". htmlspecialchars($channel["display-name"]) . "</display-name>\n";
    $xml .= "    <icon src=\"" . $channel["icon"] . "\" />\n";
    $xml .= "    <url>" . $channel["url"] . "</url>\n";
    $xml .= "  </channel>\n";
  }

  $event = json_decode(file_get_contents("special_event.json"));

  if (is_object($event)) {
    $xml .= "  <channel id=\"SpecialEvents\">\n";
    $xml .= "    <display-name>Special Events</display-name>\n";
    $xml .= "    <icon src=\"https://i.imgur.com/vRlLmha.png\" />\n";
    $xml .= "    <url>https://iptv-sniper.herokuapp.com/</url>\n";
    $xml .= "  </channel>\n";
  }

  foreach ($programme_list as $programme) {
    $xml .= "  <programme start=\"" . $programme["start_raw"] . "\" stop=\"" . $programme["stop_raw"] . "\" channel=\"" . $programme["channel"] . "\">\n";
    $xml .= "    <title lang=\"en\">" . htmlspecialchars($programme["title"]) . "</title>\n";
    $xml .= "    <desc lang=\"en\">" . htmlspecialchars($programme["desc"]) . "</desc>\n";
    $xml .= "    <category lang=\"en\">" . htmlspecialchars($programme["category"]) . "</category>\n";
    $xml .= "  </programme>\n";
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
  file_put_contents($filename, $xml);
//end epg.php