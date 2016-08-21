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
            Console::write('FEHLER: Fehlende Angaben.');
            Console::write('Aufruf: ' . $argv[0] . ' ' . $argv[1], false);
            foreach ($params as $param => $paramHelp) {
                Console::write(' <' . $param . '>', false);
            }
            Console::write('');
            Console::write('');
            Console::write('Parameter:');
            foreach ($params as $param => $paramHelp) {
                Console::write(str_pad('<' . $param . '>', 20, ' ') . $paramHelp);
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