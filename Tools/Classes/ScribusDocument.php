<?php

namespace VMFDS\Scribe;

class ScribusDocument extends \SimpleXMLElement
{

    protected $fileName = '';

    public static function load($fileName)
    {
        $o = new ScribusDocument(file_get_contents($fileName));
        $o->setFileName($fileName);
        return $o;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function getMasterPages() {
        $masterPages = [];
        foreach ($this->DOCUMENT->MASTERPAGE as $masterPage) {
            $masterPages[(int)$masterPage['NUM']] = (string)$masterPage['NAM'];
        }
        return $masterPages;
    }

    /**
     * Seitentitel ermitteln
     * @return array Seitentitel
     */
    function getPageTitles()
    {
        $numPages = $this->getNumberOfPages();
        $pageTitle = [];
        foreach ($this->DOCUMENT->PAGEOBJECT as $pageObject) {
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
     * Gesamtseitenzahl ermitteln
     * @return int Anzahl der Seiten
     */
    function getNumberOfPages()
    {
        return (int)$this->DOCUMENT['ANZPAGES'];
    }


    /**
     * Titelbox für eine Seite finden
     * @param int $pageNumber Seitennummer, Zählung ab 0
     */
    function getTitleBox($pageNumber) {
        $numPages = $this->getNumberOfPages();
        $pageTitle = [];
        foreach ($this->DOCUMENT->PAGEOBJECT as $pageObject) {
            $pageNo = (int)$pageObject['OwnPage'];
            if (($pageNo == $pageNumber) && ($pageObject['PTYPE'] == 4)) {
                if ($pageObject->StoryText->DefaultStyle['PARENT'] == 'AS Seitenüberschrift') {
                    return $pageObject;
                }
            }
        }
        return NULL;
    }

}