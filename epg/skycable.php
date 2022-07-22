<?php
  $type = (isset($_GET["format"]) && $_GET["format"] == "xml") ? "application/xml" : "application/gzip";
  $format = (isset($_GET["format"]) && $_GET["format"] == "xml") ? "&format=xml" : "";
  header("Content-Type: $type");

  if ($type == "application/gzip")
    header("Content-Disposition: attachment; filename=cryogenix.xml.gz");

  $epgs = json_decode(file_get_contents("https://skyepg.mysky.com.ph/Main/getEventsbyType"), JSON_PRETTY_PRINT);

  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
  $xml .= "<tv date=\"" . date('Ymd') . "\" generator-info-name=\"AdoboTV\">\n";

  $i = array_search($_GET["channel"], array_column($epgs["location"], "id"));

  if (!isset($_GET["channel"])) {
    $channels = array_column($epgs["location"], "name", "id");

    foreach ($epgs["location"] as $channels) {
      $xml .= "  <channel id=\"" . preg_replace('/[^A-Za-z0-9\-]/', '', $channels["name"]) . "\">\n";
      $xml .= "    <display-name>" . htmlspecialchars($channels["name"]) . "</display-name>\n";
      $xml .= "    <icon src=\"https://skyepg.mysky.com.ph/Main/" . $channels["userData"]["logo"] . "\" />\n";
      $xml .= "    <url>https://iptv-sniper.herokuapp.com/epg/skycable.php?channel=" . $channels["id"] ."$format</url>\n";
      $xml .= "  </channel>\n";
    }
  }
  else {
    $xml .= "  <channel id=\"" . preg_replace('/[^A-Za-z0-9\-]/', '', $epgs["location"][$i]["name"]) . "\">\n";
    $xml .= "    <display-name>" . htmlspecialchars($epgs["location"][$i]["name"]) . "</display-name>\n";
    $xml .= "    <icon src=\"https://skyepg.mysky.com.ph/Main/" . $epgs["location"][$i]["userData"]["logo"] . "\" />\n";
    $xml .= "    <url>https://iptv-sniper.herokuapp.com/epg/skycable.php?channel=" . $epgs["location"][$i]["id"] ."$format</url>\n";
    $xml .= "  </channel>\n";
  }

  foreach ($epgs["events"] as $programme) {
    if (isset($_GET["channel"]) && $programme["location"] !== $_GET["channel"]) continue;

    $xml .= "  <programme start=\"" . date("YmdHis O", strtotime($programme["start"])) . "\" stop=\"" . date("YmdHis O", strtotime($programme["end"])) . "\" channel=\"" . preg_replace('/[^A-Za-z0-9\-]/', '', $epgs["location"][$i]["name"]) . "\">\n";
    $xml .= "    <title lang=\"en\">" . htmlspecialchars($programme["name"]) . "</title>\n";
    $xml .= "    <desc lang=\"en\">" . htmlspecialchars($programme["userData"]["description"]) . "</desc>\n";
    $xml .= "    <category lang=\"en\">Sky Cable</category>\n";
    $xml .= "  </programme>\n";
  }

  $xml .= "</tv>";
  echo ($type == "application/gzip") ? gzencode($xml, 9) : $xml;
//end skyepg.php