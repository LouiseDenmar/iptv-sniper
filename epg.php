<?php
  require 'EpgParser.php';
  $channel_list   = array();
  $programme_list = array();

  $epgs_json = json_decode(file_get_contents($_GET["json"]));

  foreach ($epgs_json as $epg_json) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $epg_json->url);
    $epg_json_contents = curl_exec($ch);
    curl_close($ch);
  
    $epg_xml = new EpgParser($epg_json_contents);
    $epg_xml_channels = $epg_xml->array["tv"]["channel"];
    $epg_xml_programmes = $epg_xml->array["tv"]["programme"];

    foreach ($epg_json->channels as $epg_json_channel) {
      foreach ($epg_xml_programmes as $epg_xml_programme) {
        if ($epg_xml_programme["attrib"]["channel"] ==  $epg_json_channel) {
          $programme_list[] = [
            "start_raw" => $epg_xml_programme["attrib"]["start"],
            "stop_raw"  => $epg_xml_programme["attrib"]["stop"],
            "channel"   => $epg_xml_programme["attrib"]["channel"],
            "title"     => $epg_xml_programme["title"]["cdata"]
          ];
        }
      }
    }
  }

  die("<pre>" . print_r($programme_list, true) . "</pre>");
//end epg.php