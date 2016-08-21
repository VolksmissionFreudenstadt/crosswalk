<?php

namespace VMFDS\Scribe;

require_once('Classes/AutoLoader.php');

class FontCheckApp extends App
{

    protected $doc = null;

    protected $commands = [
        'hilfe' => 'Diesen Hilfetext anzeigen',
        'liste' => 'Alle verwendeten Schriftarten auflisten',
    ];


    private function findPageObjectFonts(\SimpleXMLElement $root, $fonts)
    {
        foreach ($root->PAGEOBJECT as $po) {
            if (isset($po->StoryText)) {
                if (isset($po->StoryText->DefaultStyle['FONT'])) {
                    $fontName = (string)$po->StoryText->DefaultStyle['FONT'];
                    $fonts[$fontName][] = (int)$po['OwnPage']+1;
                }
                if (isset($po->StoryText->ITEXT)) {
                    foreach ($po->StoryText->ITEXT as $iText) {
                        if (isset($iText['FONT'])) {
                            $fontName = (string)$iText['FONT'];
                            $fonts[$fontName][] = (int)$po['OwnPage']+1;
                        }
                    }
                }
            }
            if (isset($po->PAGEOBECT)) {
                $fonts = array_merge_recursive($fonts, $this->findPageObjectFonts($po));
            }
        }
        return $fonts;
    }

    public function cmdListe()
    {
        $fonts = [];
        $doc = \VMFDS\Scribe\ScribusDocument::load('../Heft.sla');

        // Durchsuche Zeichenstile
        foreach ($doc->DOCUMENT->CHARSTYLE as $charStyle) {
            $fonts[(string)$charStyle['FONT']][] = $charStyle['CNAME'];
        }

        // Durchsuche PDF Font embeddings
        foreach ($doc->DOCUMENT->PDF->Fonts as $font) {
            $fonts[(string)$font['Name']][] = 'PDF-Konfiguration (eingebettete Schriftarten)';
        }
        foreach ($doc->DOCUMENT->PDF->Subset as $font) {
            $fonts[(string)$font['Name']][] = 'PDF-Konfiguration (eingebettete Schriftarten)';
        }

        // Durchsuche Dokument
        $fonts = $this->findPageObjectFonts($doc->DOCUMENT, $fonts);

        // Liste ausgeben:
        ksort($fonts);
        Console::write('Verwendete Schriftarten');
        foreach ($fonts as $name => $places) {
            Console::write(' - ' . $name);
            Console::write('     ' . join(', ', $places)."\r\n");
        }

    }

}


$app = new FontCheckApp();
$app->run();