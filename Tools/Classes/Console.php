<?php

namespace VMFDS\Scribe;


class Console
{

    /**
     * Hilfsfunktion zur Ausgabe auf die Konsole
     * @param string $outputText Ausgabetext
     * @param bool $lineBreak Zeilenumbruch am Ende
     * @param bool $decode Text ist UTF8
     */
    static public function write($outputText, $lineBreak = true, $decode = true)
    {
        if ($decode) {
            $outputText = utf8_decode($outputText);
        }
        echo $outputText . ($lineBreak ? "\r\n" : '');
    }


}