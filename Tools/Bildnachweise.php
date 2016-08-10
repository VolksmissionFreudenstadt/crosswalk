<?php

$pageTitle = [];
$images = [];

$commands = [
    'hilfe' => 'Diesen Hilfetext anzeigen',
    'liste' => 'Alle verwendeten Bilder in eine CSV-Datei ausgeben',
    'ausgabe' => 'Bildnachweise als Text erzeugen',
];

/**
 * Hilfsfunktion zur Ausgabe auf die Konsole
 * @param string $outputText Ausgabetext
 * @param bool $lineBreak Zeilenumbruch am Ende
 * @param bool $decode Text ist UTF8
 */
function out($outputText, $lineBreak = true, $decode = true)
{
    if ($decode) {
        $outputText = utf8_decode($outputText);
    }
    echo $outputText . ($lineBreak ? "\r\n" : '');
}

/**
 * Hilfsfunktion zur Ausgabe in eine CSV-Date
 * @param Resource $csv CSV-Dateihandle
 * @param array $data Daten
 */
function csvWrite($csv, $data)
{
    foreach ($data as $key => $value) {
        if (!is_numeric($value)) {
            $data[$key] = '"' . $value . '"';
        }
    }
    if (CSV_ERSTELLEN) {
        fwrite($csv, utf8_decode(join(';', $data)) . "\r\n");
    }
}

/**
 * Liefert CSV-Daten aus einem Dateinamen
 * @param string $fileName Dateiname
 * @return array CSV-Daten
 */
function imageData($fileName)
{
    $data = explode('/', $fileName);
    unset ($data[0]);
    $data[3] = $data[4] = $data[5] = '';

    // Daten für eigene Bilder:
    if (($data[1] == 'CI') || ($data[1] == 'Eigene Illustrationen')) {
        $data[3] = 'Volksmission Freudenstadt';
        $data[4] = 'Christoph Fischer';
    }

    // Daten für Pixelio
    if (strpos($fileName, 'pixelio.de') !== false) {
        $tmp = pathinfo($fileName, PATHINFO_FILENAME);
        $tmp = str_replace('R_', '', $tmp);
        $tmp = str_replace('K_', '', $tmp);
        $tmp = str_replace('B_', '', $tmp);
        $tmp = str_replace('original_', '', $tmp);
        $tmp = str_replace('by_', '', $tmp);
        $tmp = str_replace('_pixelio.de', '', $tmp);
        $tmp2 = explode('_', $tmp);
        $data[3] = 'pixelio';
        $data[4] = $tmp2[1];
    }

    // Daten für freeimages
    if (strpos($fileName, '--sxc-') !== false) {
        $tmp = pathinfo($fileName, PATHINFO_FILENAME);
        $tmp2 = explode('--', $tmp);
        $data[5] = $tmp2[0];
        $data[4] = substr($tmp2[1], 4);
        $data[3] = 'freeimages';
    }

    ksort($data);
    return $data;
}

/**
 * Quellenverzeichnis einlesen
 * @param string $csvFile Pfad zum Quellenverzeichnis
 * @return array Quellen
 */
function getSources($csvFile)
{
    $sources = [];
    $recs = explode("\n", str_replace("\r\n", "\n", utf8_encode(file_get_contents($csvFile))));
    unset($recs[0]);
    foreach ($recs as $rec) {
        $tmp = explode(';', $rec);
        // Anführungszeichen entfernen
        foreach ($tmp as $key => $value) {
            if ((substr($value, 0, 1) == '"') && (substr($value, -1) == '"')) {
                $tmp[$key] = substr($value, 1, -1);
            }
        }
        if (isset($tmp[1])) {
            $fileName = 'Grafik/' . $tmp[0] . '/' . $tmp[1];
            $sources[$fileName] = [
                'file' => $fileName,
                'site' => $tmp[2],
                'author' => $tmp[3],
                'source' => $tmp[2] . ($tmp[3] ? ' / ' . $tmp[3] : ''),
                'title' => $tmp[4],
            ];
        }
    }
    return $sources;
}


/**
 * Scribus-Dokument einlesen
 * @return SimpleXMLElement[] Dokumentinhalt
 */
function getDocument()
{
    $raw = new SimpleXMLElement(file_get_contents('../Heft.sla'));
    return $raw->DOCUMENT;

}

/**
 * Gesamtseitenzahl ermitteln
 * @param SimpleXMLElement $doc Dokument
 * @return int Anzahl der Seiten
 */
function getNumberOfPages($doc)
{
    return (int)$doc['ANZPAGES'];
}


/**
 * Seitentitel ermitteln
 * @param SimpleXMLElement $doc Dokument
 * @return array Seitentitel
 */
function getPageTitles($doc)
{
    $numPages = getNumberOfPages($doc);
    $pageTitle = [];
    foreach ($doc->PAGEOBJECT as $pageObject) {
        $pageNo = $pageObject['OwnPage'];
        if ($pageObject['PTYPE'] == 4) {
            if ($pageObject->StoryText->DefaultStyle['PARENT'] == 'AS Seitenüberschrift') {
                $title = $pageObject->StoryText->ITEXT['CH'];
                if (is_numeric(substr($title, 0, 1))) {
                    $title = substr($title, 0, strpos($title, ' '));
                }
                $pageTitle[(int)$pageNo] = $title;
            }

        }
    }
    $pageTitle[0] = $pageTitle[$numPages - 1] = 'Umschlag';
    return $pageTitle;
}


/**
 * Prüfe, ob alle benötigten Parameter vorhanden sind
 * @param array $params Parameter
 * @return array Werte
 */
function checkArguments($params)
{
    global $argv;
    if (count($argv) < (count($params) + 2)) {
        out('FEHLER: Fehlende Angaben.');
        out('Aufruf: ' . $argv[0] . ' ' . $argv[1], false);
        foreach ($params as $param => $paramHelp) {
            out(' <' . $param . '>', false);
        }
        out('');
        out('');
        out('Parameter:');
        foreach ($params as $param => $paramHelp) {
            out(str_pad('<' . $param . '>', 20, ' ') . $paramHelp);
        }
        die();
    } else {
        $values = [];
        $ct = 1;
        foreach ($params as $param => $paramHelp) {
            $ct++;
            $values[$ct-2] = $argv[$ct];
        }
        return $values;
    }

}


/**
 * Befehl 'liste'
 */
function cmdListe()
{
    list($csvFile) = checkArguments(['CSV-Datei' => 'Name und Pfad der Ausgabedatei']);

    out('Suche nach Bildern... ', false);
    $doc = getDocument();
    $imageList = [];
    // Alle Bilder finden
    foreach ($doc->PAGEOBJECT as $pageObject) {

        if ((trim($pageObject['PFILE']))) {
            $pageNo = (int)$pageObject['OwnPage'];
            if ($pageNo >= 0) {
                $file = (string)$pageObject['PFILE'];
                $imageList[$file] = $file;
            }
        }
    }
    out('Fertig [' . count($imageList) . '].');


    out('Finde alle Clipartgruppen... ', false);
    $pageTitles = getPageTitles($doc);
    foreach ($doc->PAGEOBJECT as $pageObject) {
        if (($pageObject['PTYPE'] == 6) || ($pageObject['PTYPE'] == 12)) {
            $title = $pageObject['ANNAME'];
            if (substr($title, 0, 5) == 'SVG__') {
                $tmp = explode('__', $title);
                if ($file !== 'ignore') {
                    $file = 'Grafik/Clipart/' . $tmp[1] . '.svg';
                    $imageList[$file] = $file;
                }
            } elseif (substr($title, 0, 12) == 'ScreenBean__') {
                $tmp = explode('__', $title);
                if ($file !== 'ignore') {
                    $file = 'Grafik/ScreenBeans/' . $tmp[1] . '.svg';
                    $imageList[$file] = $file;
                }
            } else {
                $pageNo = $pageObject['OwnPage'];
                if ($pageNo >= 0) {
                    $file = str_replace('Copy of ', '', $title);
                    $file = str_replace('Kopie von ', '', $file);
                    $file = trim($file);
                    if ((substr($file, 0, 1) == 'g') || (substr($file, 0, 4) == 'path')) {
                        $file = 'Grafik/SVG/' . ((int)$pageNo + 1) . '.' . $pageTitles[(int)$pageNo] . '--' . $file;
                        out($file);
                        $imageList[$file] = $file;
                    }
                }
            }
        }
    }


    out('Fertig [' . count($imageList) . '].');

    out('Schreibe Bilderliste nach ' . $csvFile . ' ... ', false);
    $csv = fopen($csvFile, 'w');
    csvWrite($csv, ['Kategorie', 'Dateiname', 'Quelle', 'Autor', 'Titel']);

    foreach ($imageList as $image) {
        csvWrite($csv, imageData($image));
    }
    fclose($csv);
    out('Fertig.');

}

/**
 * Befehl 'Ausgabe'
 */
function cmdAusgabe()
{
    list($csvFile, $txtFile) = checkArguments([
        'CSV-Datei' => 'Name und Pfad der Bilderliste',
        'Ausgabedatei' => 'Name und Pfad zur Ausgabedatei',
    ]);
    $sources = getSources($csvFile);

    $doc = getDocument();
    $pageTitles = getPageTitles($doc);

    out('Suche nach Bildern... ', false);
    $imageList = [];

    // Alle Bilder finden
    foreach ($doc->PAGEOBJECT as $pageObject) {
        if (($pageObject['PTYPE'] == 2) && (trim($pageObject['PFILE']))) {
            $pageNo = (int)$pageObject['OwnPage'];
            if ($pageNo >= 0) {
                $file = (string)$pageObject['PFILE'];
                $images[$pageNo][$file] = $file;
            }
        }
    }
    out('Fertig.');
    out('Finde alle Clipartgruppen... ', false);
    $pageTitles = getPageTitles($doc);
    foreach ($doc->PAGEOBJECT as $pageObject) {
        $pageNo = (int)$pageObject['OwnPage'];
        if ($pageObject['XPOS']>0) {
            if (($pageObject['PTYPE'] == 6) || ($pageObject['PTYPE'] == 12)) {
                $title = $pageObject['ANNAME'];
                if (substr($title, 0, 5) == 'SVG__') {
                    $tmp = explode('__', $title);
                    if ($tmp[1] !== 'ignore') {
                        $file = 'Grafik/Clipart/' . $tmp[1] . '.svg';
                        $images[$pageNo][$file] = $file;
                    }
                } elseif (substr($title, 0, 12) == 'ScreenBean__') {
                    $tmp = explode('__', $title);
                    if ($file !== 'ignore') {
                        $file = 'Grafik/ScreenBeans/' . $tmp[1] . '.svg';
                        $images[$pageNo][$file] = $file;
                    }
                }
            }
        }
    }
    out('Fertig.');

    // Sortieren
    ksort($images);
    $imageList = [];
    foreach ($images as $key => $img) {
        $imageList[$pageTitles[$key]] = $img;
    }

    $missing = [];

    out ('Schreibe Quellennachweise nach "'.$txtFile.'"... ', false);
    $txt = fopen($txtFile, 'w');
    foreach ($imageList as $page => $pageImages) {
        $s = [];
        foreach ($pageImages as $image) {
            if (isset($sources[$image])) {
                $source = trim(($sources[$image]['title'] ? '"'.$sources[$image]['title'].'", ' : '').$sources[$image]['source']);
                if ($source) $s[] = $source;
            } else {
                $missing[] = $image;
            }
        }
        if (count($s)) fwrite($txt, utf8_decode($page . ': ' . join('; ', $s)."\r\n"));
    }
    fclose($txt);
    out ('Fertig.');


    if (count($missing)) {
        out('');
        out('Fehlende Quellenangaben:');
        foreach ($missing as $m) {
            out($m);
        }
    }
}

/**
 * Befehl 'hilfe'
 */
function cmdHilfe()
{
    global $commands, $argv;
    ksort($commands);

    out('Verwendung: ' . $argv[0] . ' <Befehl>');
    out('');
    out('Verfügbare Befehle:');

    foreach ($commands as $cmd => $help) {
        out(str_pad($cmd, 20, ' ') . $help);
    }
}


// Kommandozeile:
if (!isset($argv[1])) {
    $argv[1] = 'hilfe';
}
if (function_exists($cmdFunction = 'cmd' . ucfirst($argv[1]))) {
    $cmdFunction();
    exit;
} else {
    out('Ungültiger Befehl. Verwende "' . $argv[0] . ' hilfe", um eine Liste aller verfügbaren Befehle zu erhalten.');
    die();
}


// CSV-Ausgabe
if (CSV_ERSTELLEN) {
}

// Ausgabe
for ($i = 0; $i < $numPages; $i++) {
    if (isset($images[$i])) {
        out($pageTitle[$i] . ' ' . join(', ', $images[$i]));
    }
}


