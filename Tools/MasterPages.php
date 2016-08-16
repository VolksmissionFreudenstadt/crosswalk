<?php

namespace VMFDS\Scribe;

require_once('Classes/AutoLoader.php');

class MasterPagesApp extends App
{

    protected $doc = null;

    protected $commands = [
        'hilfe' => 'Diesen Hilfetext anzeigen',
        'liste' => 'Alle verwendeten Musterseiten auflisten',
        'seiten' => 'Seiten mit Angabe der verwendeten Musterseite auflisten',
        'fix' => 'Musterseiten korrigieren',
    ];


    public function cmdListe()
    {
        $doc = \VMFDS\Scribe\ScribusDocument::load('../Heft.sla');
        $masterPages = $doc->getMasterPages();

        // Liste ausgeben:
        Console::write('Musterseiten im Dokument:');
        foreach ($masterPages as $idx => $title) {
            Console::write(str_pad($idx, 5, ' ') . $title);
        }

    }

    public function cmdSeiten()
    {
        $doc = \VMFDS\Scribe\ScribusDocument::load('../Heft.sla');
        $masterPages = $doc->getMasterPages();
        $pageTitles = $doc->getPageTitles();

        $pages = [];
        foreach ($doc->DOCUMENT->PAGE as $page) {
            if ($page['NUM'] >= 0) {
                $pages[(int)$page['NUM']] = (string)$page['MNAM'];
            }
        }

        $num = $doc->getNumberOfPages();

        ksort($pages);
        Console::write('Seiten:');
        foreach ($pages as $page => $master) {
            if (($page > 1) && ($page < ($num - 1))) {
                $shouldBe = ($page % 2) ? 'Normal links' : 'Normal rechts';
            }
            Console::write(str_pad($pageTitles[$page] . ' [' . $page . ']', 20, ' ') . str_pad($master, 20,
                    ' ') . $shouldBe);
        }

    }

    private function alignTitleBox(&$titleBox, $align) {
        if (isset($titleBox->StoryText->trail)) {
            if (isset($titleBox->StoryText->trail['ALIGN'])) {
                $titleBox->StoryText->trail['ALIGN'] = $align;
            } else {
                $titleBox->StoryText->trail->addAttribute('ALIGN', $align);
            }
            if (isset($titleBox->StoryText->trail['PARENT'])) {
                $titleBox->StoryText->trail['PARENT'] = 'AS Seitenüberschrift';
            } else {
                $titleBox->StoryText->trail->addAttribute('PARENT', 'AS Seitenüberschrift');
            }
        }
        return $titleBox;
    }

    public function cmdFix()
    {
        $doc = \VMFDS\Scribe\ScribusDocument::load('../Heft.sla');
        $masterPages = $doc->getMasterPages();
        $pageTitles = $doc->getPageTitles();
        $num = $doc->getNumberOfPages();

        foreach ($doc->DOCUMENT->PAGE as $page) {
            $idx = (int)$page['NUM'];
            $title = isset($pageTitles[$idx]) ? $pageTitles[$idx] : '';
            $tmp = explode(' ', (string)$page['MNAM']);
            if (($idx > 1) && ($idx < ($num - 2)) && ($tmp[0] == 'Normal')) {
                $shouldBe = ($idx % 2) ? 'Normal links' : 'Normal rechts';
                if ((string)$page['MNAM'] != $shouldBe) {
                    Console::write('Ändere Musterseite für Seite #' . ($idx + 1) . ' (' . $title . ') von "' . (string)$page['MNAM'] . '" zu "' . $shouldBe . '"');
                    $page['MNAM'] = $shouldBe;
                    $titleBox = $doc->getTitleBox($idx);
                    if (is_object($titleBox)) {
                        if ($shouldBe == 'Normal links') {
                            $titleBox['XPOS'] = '267.244724409449';
                            $titleBox['WIDTH'] = '384.082790084939';
                            $titleBox = $this->alignTitleBox($titleBox, 0);
                        } else {
                            $titleBox['XPOS'] = '737.795905511811';
                            $titleBox['WIDTH'] = '384.082790084939';
                            $titleBox['path'] = 'M0 0 L384.083 0 L384.083 20.9302 L0 20.9302 L0 0 Z';
                            $titleBox['copath'] = 'M0 0 L384.083 0 L384.083 20.9302 L0 20.9302 L0 0 Z';
                            $titleBox = $this->alignTitleBox($titleBox, 2);
                            if (isset($titleBox['ALIGN'])) unset($titleBox['ALIGN']);
                            if (isset($titleBox->StoryText->DefaultStyle['ALIGN'])) unset($titleBox->StoryText->DefaultStyle['ALIGN']);
                        }
                    }
                }
            }
        }
        $doc->asXML('../Heft.sla');
    }

}


$app = new MasterPagesApp();
$app->run();