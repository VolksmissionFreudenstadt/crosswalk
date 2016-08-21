<?php

namespace VMFDS\Scribe;

use VMFDS\Scribe\Console;

class App
{

    protected $commands = [
        'hilfe' => 'Diesen Hilfetext anzeigen',
    ];

    public function __construct()
    {
    }

    public function run() {
        global $argv;
        if (!isset($argv[1])) {
            $argv[1] = 'hilfe';
        }
        if (method_exists($this, $cmdFunction = 'cmd' . ucfirst($argv[1]))) {
            $this->$cmdFunction();
            exit;
        } else {
            Console::write('Ungültiger Befehl. Verwende "' . $argv[0] . ' hilfe", um eine Liste aller verfügbaren Befehle zu erhalten.');
            die();
        }
    }

    /**
     * Befehl 'hilfe'
     */
    function cmdHilfe()
    {
        global $argv;
        ksort($this->commands);

        Console::write('Verwendung: ' . $argv[0] . ' <Befehl>');
        Console::write('');
        Console::write('Verfügbare Befehle:');

        foreach ($this->commands as $cmd => $help) {
            Console::write(str_pad($cmd, 20, ' ') . $help);
        }
    }



}