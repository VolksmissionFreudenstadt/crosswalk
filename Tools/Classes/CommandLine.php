<?php

namespace VMFDS\Scribe;

class CommandLine
{

    /**
     * Prüfe, ob alle benötigten Parameter vorhanden sind
     * @param array $params Parameter
     * @return array Werte
     */
    public static function checkArguments($params)
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
                $values[$ct - 2] = $argv[$ct];
            }
            return $values;
        }

    }


}