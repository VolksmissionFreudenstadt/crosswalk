# Entwicklertools

Die Skripte in diesem Verzeichnis dienen als Hilfsmittel für verschiedene wiederkehrende Aufgaben bei der Bearbeitung des Skribus-Dokuments. Die Ausführung setzt eine installierte PHP Umgebung (>=5.6) voraus.

Die meisten Anwendungen greifen nur lesend auf die [Heft.sla](../Heft.sla) zu. 

Alle Skripte haben eine Hilfsfunktion, die die verfügbaren Befehle anzeigt. Diese lässt sich mit folgendem Befehl aufrufen:

`php <scriptname.php> hilfe`

## Bildnachweise
Mit [Bildnachweise.php](Bildnachweise.php) lässt sich schnell und automatisiert ein Verzeichnis aller verwendeten Bildquellen erstellen.

## FontChecker
Mit [FontChecker.php](FontChecker.php) lassen sich alle im Dokument verwendeten Schriftarten auflisten. Damit kann schnell überprüft werden, ob sich z.B. durch "Copy & paste" eine unerwünschte Schriftart eingeschlichen hat.

### Schriftarten auflisten
Das Skript wird wie folgt aufgerufen:

`php FontChecker.php liste`


## Musterseiten

Mit [MasterPages.php](MasterPages.php) lassen sich die verwendeten Musterseiten anpassen.

### Auflisten
Dieser Befehl gibt eine Liste der vorhandenen Musterseiten aus:

`php MasterPages.php liste`


### Seitenübersicht
Dieser Befehl listet alle aktiven Seiten mit ihrer Musterseite auf:

`php MasterPages.php seiten`

### Musterseiten korrigieren
Dieser Befehl setzt die Musterseiten "Normal links" und "Normal rechts" korrekt und schiebt die Textboxen mit dem Seitentitel an die richtige Stelle:

`php MasterPages.php fix`

*Achtung*: Dieser Befehl schreibt die [Heft.sla](../Heft.sla) neu. 

### Musterseiten korrigieren
Dieser Befehl setzt alle Musterseiten auf "Normal rechts", um den Druck als einzelne Arbeitsblätter zu ermöglichen. Außerdem schiebt das Skript die Textboxen mit dem Seitentitel an die richtige Stelle:

`php MasterPages.php arbeitsblatt`

*Achtung*: Dieser Befehl schreibt die [Heft.sla](../Heft.sla) neu. 




