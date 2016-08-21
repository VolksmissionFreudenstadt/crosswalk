<?php

namespace VMFDS\Scribe;

require_once('Classes/AutoLoader.php');

class ImageSourcesApp extends App
{

    protected $doc = null;
    protected $licenses = [];
    protected $defaultLicenses = [];

    protected $commands = [
        'hilfe' => 'Diesen Hilfetext anzeigen',
        'ausgabe' => 'Bildnachweise als Text erzeugen',
        'fix' => 'Bildnachweise ins Scribus-Dokument einpflegen',
    ];


    protected function getSources($csvFile) {
        $sources = CSV::get($csvFile, ',', 'Dateiname');
        $defaultLicenses = CSV::get('../Dokumentation/Lizenzliste.csv', ',', 'Code');
        foreach ($sources as $key => $source) {
            $license = NULL;
            if (isset($defaultLicenses[$source['Quelle']])) {
                $license = $defaultLicenses[$source['Quelle']];
            }
            if (isset($defaultLicenses[$source['Lizenztitel']])) {
                $license = $defaultLicenses[$source['Lizenztitel']];
            }
            if ($license) {
                $sources[$key]['Lizenztitel'] = $license['Lizenztitel'];
                $sources[$key]['Lizenz-URL'] = $license['Lizenz-URL'];
            }
            $sources[$key]['Lizenzhinweis'] = $sources[$key]['Lizenztitel'].($sources[$key]['Lizenz-URL'] ? ', '.$sources[$key]['Lizenz-URL'] : '');
        }
        return $sources;
    }

    function getLicenseText($csvFile) {
        $images = [];

        $doc = ScribusDocument::load('../Heft.sla');
        $pageTitles = $doc->getPageTitles();

        Console::write('Suche nach Bildern... ', false);

        // Alle Bilder finden
        foreach ($doc->DOCUMENT->PAGEOBJECT as $pageObject) {
            if (($pageObject['PTYPE'] == 2) && (trim($pageObject['PFILE']))) {
                $pageNo = (int)$pageObject['OwnPage'];
                if ($pageNo >= 0) {
                    $file = (string)$pageObject['PFILE'];
                    $images[$pageNo][$file] = $file;
                }
            }
        }
        Console::write('Fertig.');


        Console::write('Suche nach importierten Vektorgrafiken... ', false);
        foreach ($doc->DOCUMENT->PAGEOBJECT as $pageObject) {
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
                    }
                }
            }
        }
        Console::write('Fertig.');

        // Sortieren
        ksort($images);
        $imageList = [];
        $finalList = [];
        $licenses = [];
        $licenseCt = 0;
        $missing = [];
        $missingLicense = [];

        foreach ($images as $key => $img) {
            $imageList[$pageTitles[$key]] = $img;
        }

        $sources = $this->getSources($csvFile);

        foreach ($imageList as $page => $pageImages) {
            $s = [];
            foreach ($pageImages as $image) {
                $image = str_replace('Grafik/', '', $image);
                if (isset($sources[$image])) {
                    if ($sources[$image]['Lizenzhinweis'] == '') {
                        $missingLicense[] = $image.' ('.$page.')';
                    }
                    if (!isset($licenses[$sources[$image]['Lizenzhinweis']])) {
                        $licenseCt++;
                        $licenses[$sources[$image]['Lizenzhinweis']] = $licenseCt;
                    }
                    $s[] = ($sources[$image]['Titel'] ? CSV::quote($sources[$image]['Titel']) . ', ' : '')
                        .$sources[$image]['Quelle'].' / '
                        .$sources[$image]['Autor']
                        .' ['.$licenses[$sources[$image]['Lizenzhinweis']].']';
                } else {
                    $missing[] = $image.' ('.$page.')';
                }
            }
            if (count($s)) $finalList[] = $page . ': ' . join('; ', $s)."\r\n";
        }

        if (count($missing)) {
            Console::write('');
            Console::write('Fehlende Quellenangaben:');
            foreach ($missing as $m) {
                Console::write($m, true, false);
            }
        }
        if (count($missingLicense)) {
            Console::write('');
            Console::write('Fehlende Lizenzangaben:');
            foreach ($missingLicense as $m) {
                Console::write($m, true, false);
            }
        }

        $txt = file_get_contents('Assets/SourcesHeader.txt')
            .join('', $finalList)
            ."\r\n\r\nBildlizenzen:\r\n";
        foreach ($licenses as $license => $key) {
            $txt .= '['.$key.'] '.$license."\r\n";
        }
        return $txt;
    }

    /**
     * Befehl 'Ausgabe'
     */
    public function cmdAusgabe()
    {
        list($csvFile, $txtFile) = CommandLine::checkArguments([
            'CSV-Datei' => 'Name und Pfad der Bilderliste',
            'Ausgabedatei' => 'Name und Pfad zur Ausgabedatei',
        ]);

        $licenceText = $this->getLicenseText($csvFile);
        $txt = fopen($txtFile, 'w');
        fwrite($txt, $licenceText);
        fclose($txt);
    }


    /**
     * Befehl 'Fix'
     */
    public function cmdFix()
    {
        list($csvFile, $fontSize) = CommandLine::checkArguments([
            'CSV-Datei' => 'Name und Pfad der Bilderliste',
            'Schriftgröße' => 'Größe der Schrift in Punkt',
        ]);

        $licenseText = $this->getLicenseText($csvFile);

        Console::write('Schreibe Dokument neu...', false);
        $doc = new \DOMDocument();
        $doc->load('../Heft.sla');

        $xpath = new \DOMXPath($doc);
        $textBox = $xpath->query('//DOCUMENT/PAGEOBJECT[@ANNAME="Text__Bildnachweise"]/StoryText')->item(0);
        while ($textBox->hasChildNodes()) {
            $textBox->removeChild($textBox->firstChild);
        }
        $defStyle = $doc->createElement('DefaultStyle');
        $defStyle->setAttribute('PARENT', 'Default Paragraph Style');
        $defStyle->setAttribute('FONTSIZE', $fontSize);
        $textBox->appendChild($defStyle);

        $lines = explode("\r\n", $licenseText);
        foreach ($lines as $line) {
            $iText = $doc->createElement('ITEXT');
            $iText->setAttribute('FONTSIZE', $fontSize);
            $iText->setAttribute('CH', $line);
            $textBox->appendChild($iText);
            $para = $doc->createElement('para');
            $para->setAttribute('LINESPMode', 0);
            $para->setAttribute('LINESP', $fontSize+1);
            $textBox->appendChild($para);
        }
        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($doc->saveXML());
        $doc->save('../Heft.sla');

        Console::write('Fertig.');
    }


}

$app = new ImageSourcesApp();
$app->run();


