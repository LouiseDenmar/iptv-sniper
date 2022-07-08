<?php
	$epgs = array(
		array(
			"url"      => "https://iptv-org.github.io/epg/guides/ph/clickthecity.com.epg.xml",
			"channels" => array(
				"JeepneyTV.ph",
				"KapamilyaChannel.ph",
				"PTV.ph",
				"GMATV.ph",
				"GTV.ph",
				"CinemaOneGlobal.ph",
				"CinemaxSingapore.sg",
				"HBOSingapore.sg",
				"PBO.ph",
				"VivaCinema.ph",
				"AXNPhilippines.ph",
				"RockEntertainment.sg",
				"RockExtreme.sg",
				"ANC.ph",
				"CNNPhilippines.ph",
				"AnimaxPhilippines.ph"
			)
		),
		array(
			"url"      => "https://iptv-org.github.io/epg/guides/my/astro.com.my.epg.xml",
			"channels" => array(
				"CinemaxMalaysia.my"
			)
		)
	);

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
  		$xml .= "  <programme start=\"" . $programme["start_raw"] . "\" stop=\"" . $programme["stop"] . "\" channel=\"" . $programme["channel"] . "\">\n";
    	$xml .= "    <title lang=\"en\">" . htmlspecialchars($programme["title"]) . "</title>\n";
    	$xml .= "    <desc lang=\"en\">" . htmlspecialchars($programme["desc"]) . "</desc>\n";
    	$xml .= "    <category lang=\"en\">" . htmlspecialchars($programme["category"]) . "</category>\n";
		$xml .= "  </programme>\n";
	}

	$xml .= "</tv>";
	file_put_contents("cryogenix.xml", $xml);
//end epg.php