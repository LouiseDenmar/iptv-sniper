<?php
  $channel_list   = array();
  $programme_list = array();

  $epgs_json = json_decode(file_get_contents($_GET["json"]));

  foreach ($epgs_json as $epg_json) {
    $xml = new XMLReader();
    $xml->open("compress.zlib://" . $epg_json->url);

    while ($xml->read() && $xml->name !== 'programme') {
    }

    while ($xml->name === 'programme') {
      $element = new SimpleXMLElement($xml->readOuterXML());

      $key = array_search(strval($element->attributes()->channel), $epg_json->channels);

      if ($key !== false)
        $programme_list[] = $programme_list[$key];
      // $channel_list[$channel_id] = [
			// 	'id'=>(string)$element->attributes()->id,
			// 	'display-name'=>(string)$element->{'display-name'},
			// 	'url'=>(string)$element->{'url'},
			// 	'email'=>(string)$element->{'email'},
			// 	'icon'=> null,
			// ];
    }
    // $epg_xml = new EpgParser($epg_json_contents);
    // $epg_xml_channels = $epg_xml->array["tv"]["channel"];
    // $epg_xml_programmes = $epg_xml->array["tv"]["programme"];

    // foreach ($epg_json->channels as $epg_json_channel) {
    //   foreach ($epg_xml_programmes as $epg_xml_programme) {
    //     if ($epg_xml_programme["attrib"]["channel"] ==  $epg_json_channel) {
    //       $programme_list[] = [
    //         "start_raw" => $epg_xml_programme["attrib"]["start"],
    //         "stop_raw"  => $epg_xml_programme["attrib"]["stop"],
    //         "channel"   => $epg_xml_programme["attrib"]["channel"],
    //         "title"     => $epg_xml_programme["title"]["cdata"]
    //       ];
    //     }
    //   }
    // }
  }

  die("<pre>" . print_r($programme_list, true) . "</pre>");
//end epg.php