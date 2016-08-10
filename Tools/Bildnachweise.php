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
 */
function out($outputText, $lineBreak = TRUE)
{
    echo utf8_decode($outputText) . ($lineBreak ? "\r\n" : '');
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
        fwrite($csv, join(';', $data) . "\r\n");
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

define('CSV_ERSTELLEN', 1);

/**
 * Scribus-Dokument einlesen
 * @return SimpleXMLElement[] Dokumentinhalt
 */
function getDocument() {
    $raw = new SimpleXMLElement(file_get_contents('../Heft.sla'));
    return $raw->DOCUMENT;

}

/**
 * Gesamtseitenzahl ermitteln
 * @param SimpleXMLElement $doc Dokument
 * @return int Anzahl der Seiten
 */
function getNumberOfPages($doc) {
    return  (int)$doc['ANZPAGES'];
}


/**
 * Seitentitel ermitteln
 * @param SimpleXMLElement $doc Dokument
 * @return array Seitentitel
 */
function getPageTitles($doc) {
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


function checkArgument($param, $paramHelp) {
    global $argv;
    if (!isset($argv[2])) {
        out('Fehlender Parameter: <'.$param.'>');
        out('Aufruf: '.$argv[0].' '.$argv[1].' <'.$param.'>');
        out('');
        out('Parameter:');
        out(str_pad('<'.$param.'>',20,' '). $paramHelp);
        die();
    } else {
        return $argv[2];
    }
}


/**
 * Befehl 'liste'
 */
function cmdListe() {
    $csvFile = checkArgument('CSV-Datei', 'Name und Pfad der Ausgabedatei');

    out ('Suche nach Bildern... ', false);
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
    out ('Fertig ['.count($imageList).'].');


    out ('Finde alle Clipartgruppen... ', false);
    $pageTitles = getPageTitles($doc);
    foreach ($doc->PAGEOBJECT as $pageObject) {
        if (($pageObject['PTYPE']== 6) || ($pageObject['PTYPE']==12)) {
            $title = $pageObject['ANNAME'];
            if (substr($title, 0, 5)=='SVG__') {
                $tmp = explode('__', $title);
                if ($file !== 'ignore') {
                    $file = 'Grafik/Clipart/'.$tmp[1].'.svg';
                    $imageList[$file] = $file;
                }
            } else {
                $pageNo = $pageObject['OwnPage'];
                if ($pageNo >= 0) {
                    $file = str_replace('Copy of ', '', $title);
                    $file = str_replace('Kopie von ', '', $file);
                    $file = trim($file);
                    if ((substr($file, 0, 1)=='g') || (substr($file, 0, 4)=='path')) {
                        $file = 'Grafik/SVG/'.((int)$pageNo+1).'.'.$pageTitles[(int)$pageNo].'--'.$file;
                        out ($file);
                        $imageList[$file] = $file;
                    }
                }
            }
        }
    }


    out ('Fertig ['.count($imageList).'].');

    out ('Schreibe Bilderliste nach '.$csvFile.' ... ', false);
    $csv = fopen($csvFile, 'w');
    csvWrite($csv, ['Kategorie', 'Dateiname', 'Quelle', 'Autor', 'Titel']);

    foreach ($imageList as $image) {
        csvWrite($csv, imageData($image));
    }
    fclose($csv);
    out ('Fertig.');

}

/**
 * Befehl 'Ausgabe'
 */
function cmdAusgabe() {
    global $doc, $pageTitle;
    // Alle Bilder finden
    foreach ($doc->PAGEOBJECT as $pageObject) {
        if (($pageObject['PTYPE'] == 2) && (trim($pageObject['PFILE']))) {
            $pageNo = (int)$pageObject['OwnPage'];
            if ($pageNo >= 0) {
                $file = (string)$pageObject['PFILE'];
                $images[$pageNo][$file] = $file;
                $imageList[$file] = $file;
            }
        }
    }
}

/**
 * Befehl 'hilfe'
 */
function cmdHilfe() {
    global $commands, $argv;
    ksort($commands);

    out('Verwendung: '.$argv[0].' <Befehl>');
    out('');
    out('Verfügbare Befehle:');

    foreach ($commands as $cmd => $help) {
        out (str_pad($cmd, 20, ' ').$help);
    }
}


// Kommandozeile:
if (!isset($argv[1])) $argv[1] = 'hilfe';
if (function_exists($cmdFunction = 'cmd'.ucfirst($argv[1]))) {
    $cmdFunction();
    exit;
} else {
    out('Ungültiger Befehl. Verwende "'.$argv[0].' hilfe", um eine Liste aller verfügbaren Befehle zu erhalten.');
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


