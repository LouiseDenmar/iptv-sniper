<?php
	$epgs = json_decode(file_get_contents($_GET["json"] . ".json"));

	require 'EpgParser.php';

	$Parser = new \buibr\xmlepg\EpgParser();
	$channel_list = array();

	foreach ($epgs as $epg) {
		$Parser->setUrl($epg["url"]);
		$Parser->setTargetTimeZone('Asia/Singapore');

		foreach ($epg["channels"] as $channel)
			$Parser->setChannelfilter($channel);

		try {
			$Parser->parseUrl();
		} catch (Exception $e) {
			throw new \RuntimeException($e);
		}

        $channels = array_values($Parser->getChannels());

        foreach ($epg["channels"] as $channel) {
            $key = array_search($channel, array_column($channels, "id"));

            if ($key !== false)
		        $channel_list[] = $channels[$key];
        }

		$programme_list = $Parser->getEpgdata();
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

	foreach ($programme_list as $programme) {
  		$xml .= "  <programme start=\"" . $programme["start_raw"] . "\" stop=\"" . $programme["stop_raw"] . "\" channel=\"" . $programme["channel"] . "\">\n";
    	$xml .= "    <title lang=\"en\">" . htmlspecialchars($programme["title"]) . "</title>\n";
    	$xml .= "    <desc lang=\"en\">" . htmlspecialchars($programme["desc"]) . "</desc>\n";
    	$xml .= "    <category lang=\"en\">" . htmlspecialchars($programme["category"]) . "</category>\n";
		$xml .= "  </programme>\n";
	}

	$xml .= "</tv>";
	file_put_contents("iptv-sniper.xml", $xml);
//end epg.php