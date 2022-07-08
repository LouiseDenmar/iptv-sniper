<?php
	$epgs = array(
		array(
			"url"      => "https://iptv-org.github.io/epg/guides/ph/clickthecity.com.epg.xml",
			"channels" => array(
			    "JeepneyTV.ph",
			    "KapamilyaChannel.ph",
				"AnimaxPhilippines.ph",
				"AXNPhilippines.ph"
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
	$xml .= "<tv date=\"" . date('Ymd') . "\">";

	foreach ($channel_list as $channel) {
    	$xml .= "<channel id=\"" . $channel["id"] . "\">";
        $xml .= "<display-name>". htmlspecialchars($channel["display-name"]) . "</display-name>";
        $xml .= "<icon src=\"" . $channel["icon"] . "\" />";
        $xml .= "<url>" . $channel["url"] . "</url>";
    	$xml .= "</channel>";
	}

	foreach ($programme_list as $programme) {
  		$xml .= "<programme start=\"" . $programme["start_raw"] . "\" stop=\"" . $programme["stop"] . "\" channel=\"" . $programme["channel"] . "\">";
    	$xml .= "<title lang=\"en\">" . htmlspecialchars($programme["title"]) . "</title>";
    	$xml .= "<desc lang=\"en\">" . htmlspecialchars($programme["desc"]) . "</desc>";
    	$xml .= "<category lang=\"en\">" . htmlspecialchars($programme["category"]) . "</category>";
		$xml .= "</programme>";
	}

	$xml .= "</tv>";
	file_put_contents("cryogenix.xml", $xml);
//end epg.php