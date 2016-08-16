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

}