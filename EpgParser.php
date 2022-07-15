<?php
  class EpgParser {
    $file = "data.xml";
    $depth = 0;

    function startElement($parser, $name, $attrs)
    {
        global $depth;

        for ($i = 0; $i < $depth; $i++) {
            echo "  ";
        }
        echo "$name\n";
        $depth++;
    }

    function endElement($parser, $name)
    {
        global $depth;
        $depth--;
    }

    $xml_parser = xml_parser_create();
    xml_set_element_handler($xml_parser, "startElement", "endElement");
    if (!($fp = fopen($file, "r"))) {
        die("could not open XML input");
    }

    while ($data = fread($fp, 4096)) {
        if (!xml_parse($xml_parser, $data, feof($fp))) {
            die(sprintf("XML error: %s at line %d",
                        xml_error_string(xml_get_error_code($xml_parser)),
                        xml_get_current_line_number($xml_parser)));
        }
    }
    xml_parser_free($xml_parser);
}
//end EpgParser.php