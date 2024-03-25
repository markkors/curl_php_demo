<?php
// Maak een array met de maandnamen in het Nederlands
$maanden = [
    1 => "januari",
    2 => "februari",
    3 => "maart",
    4 => "april",
    5 => "mei",
    6 => "juni",
    7 => "juli",
    8 => "augustus",
    9 => "september",
    10 => "oktober",
    11 => "november",
    12 => "december"
];

// Haal de huidige dag en maand op
$dag = date("d");
$maand = date("n");

// Maak de datumstring met de dag en maandnaam in het Nederlands
$datumString = $dag . "_" . $maanden[$maand];

// Zet de datumstring naar lowercase
$datumStringLowercase = strtolower($datumString);
// haal de HTML van de Wikipedia pagina op
$html_header = get_web_page("nl.wikipedia.org/wiki/$datumStringLowercase");

$html_output = null;

$content = $html_header['content'];

$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($content);
$xpath = new DOMXPath($dom);

// haal de titel op
$query = '//span[@class="mw-page-title-main"]';
$matches = $xpath->query($query);

if ($matches->length > 0) {
    // Er is een overeenkomst gevonden
    $element = $matches->item(0);
    $html_output .= "<h1>Vandaag:" . $element->nodeValue . "</h1>";
} else {
    // Geen overeenkomst gevonden
    $html_output .= "Dag niet gevonden.";
}

// welk onderwerp zoeken we?
$onderwerp = "Algemeen";
// make first character uppercase
if(isset($_GET['subject'])) $onderwerp = $_GET['subject'];
$onderwerp = ucfirst($onderwerp);

// Zoek naar het <ul><li> element met "Algemeen" als kop
$query = '//ul/li[b[text()="' . $onderwerp. '"]]';

$html_output .="<h4>Wat is er vandaag gebeurd in de categorie: $onderwerp</h4>";

$html_output .= "<p style='font-size:0.5em;'>Kies uit de volgende categorieën: Algemeen, Kunst en cultuur, Media, Oorlog, Politiek, Religie, Sport, Wetenschap en technologie</p>";

$matches = $xpath->query($query);

if ($matches->length > 0) {
    $parentLi = $matches->item(0); // Het gevonden <li> element met "Algemeen" als kop

    // Vind alle directe kinderen van het gevonden <li> element, behalve <ul> elementen
    $childrenQuery = './ul/li';
    $children = $xpath->query($childrenQuery, $parentLi);

    // Loop door alle gevonden kinderen en druk deze af
    if ($children->length > 0) {
        $html_output .= "<ul>";
        foreach ($children as $child) {
            $html_output .= "<li>" . $child->nodeValue . "</li>";
        }
        $html_output .= "</ul>";
    } else {
        $html_output .= "<p>Geen gebeurtenissen gevonden.</p>";
    }
} else {
    $html_output .= "<p>'$onderwerp' niet gevonden.</p>";
}

$html_output .= "<p>Bron: Wikipedia<p>";

/**
 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
 * array containing the HTTP server response header fields and content.
 */
function get_web_page( $url )
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watskebeurt....</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h1 {
            color: #333;
        }
        ul {
            list-style-type: none;
            padding-left: 0;
        }
        li {
            margin-bottom: 10px;
        }
    </style>

</head>
<body>
    <div>
        <?= $html_output ?>
    </div>
</body>
</html>