CrossWalk
=========
Arbeitsheft und Material für CrossWalk

CrossWalk ist ein Projekt der [Volksmission Freudenstadt](http://www.volksmission-freudenstadt.de/)  
Autor: [Pastor Christoph Fischer](http://christoph-fischer.org), christoph.fischer@volksmission.de.      


## Voraussetzungen

Folgende Voraussetzungen sind nötig, um das Heft bearbeiten zu können: 

### Scribus

Das Quellformat des Hefts ist Scribus, aktuell in der Version 1.5.2. Scribus ist Open Source und kann für die meisten Betriebssysteme [hier](http://www.scribus.net/) kostenlos heruntergeladen werden. Bitte beachte: Die Version 1.4.x kann das Dokument nicht öffnen! 

### Schriftarten
Alle benötigten Schriftarten werden im Ordner "[Schriftarten](Grafik/Schriftarten/)" mitgeliefert. Dieses Projekt verwendet die folgenden freien Schriftarten:

* *Handlee* von Joe Prince, verfügbar unter der SIL Open Font License 1.1.
* *Just Me Again Down Here* von Kimberly Geswein, verfügbar unter der SIL Open Font License 1.1.
* *Open Sans* von Steve Matteson, Ascender Corp., verfügbar unter der Apache 2.0 License.
* *Rock Salt* von Sideshow, verfügbar unter der Apache 2.0 License.
* *Stardos Stencil* von Vernon Adams, verfügbar unter der SIL Open Font License.

## Das Heft herunterladen und bearbeiten
Das Heft mit seinen Quelldateien lässt sich mit Git ganz einfach in der jeweils aktuellen Version von GitHub herunterladen: 

 * Voraussetzungen (Git) installieren. Unter Ubuntu geht das wie folgt:   
`sudo apt-get install git`
 * Dateien herunterladen:  
 `git clone https://github.com/VolksmissionFreudenstadt/crosswalk.git`  

Alternativ dazu stehen fertige PDF-Dateien auf GitHub unter [Releases](https://github.com/VolksmissionFreudenstadt/crosswalk/releases/) zur Verfügung.

### Lehrerheft
Das Scribus-Dokument [Heft.sla](Heft.sla) enthällt sowohl das Schülerheft als auch das Lehrerheft mit den Lösungen. Um zwischen den beiden Versionen umzuschalten, gehst du in Scribus wie folgt vor:

 * "Ebenen"-Dialog aufrufen (Fenster > Ebenen, oder `F6`)
 * Bei der Ebene "Lösungen" die Sichtbarkeit bzw. Druckbarkeit je nach Wunsch an- oder ausschalten. Die Druckbarkeit wirkt sich auch auf den PDF-Export aus.

### Entwicklerversionen
CrossWalk wird aktuell weiterentwickelt. Dazu verwenden wir den [develop](/tree/develop)-Zweig. Jede dort erfasste Änderung wird von [Travis CI](https://travis-ci.org/VolksmissionFreudenstadt/crosswalk/) automatisch geladen und die zwei PDF-Dateien für Schüler- und Lehrerheft werden innerhalb von ca. 10 Minuten bereitgestellt. 

Den aktuellen Build-Status siehst du hier:
![Travis CI Build Status](https://travis-ci.org/VolksmissionFreudenstadt/crosswalk.svg)

Die aktuellen PDFs der jeweils letzten Entwicklerversion können hier heruntergeladen werden:

 * [Schülerheft](http://www.volksmission-freudenstadt.de/fileadmin/crosswalk/build/schuelerheft.pdf) (Entwicklerversion)
 * [Lehrerheft](http://www.volksmission-freudenstadt.de/fileadmin/crosswalk/build/lehrerheft.pdf) (Entwicklerversion)


### Seiten hinzufügen/entfernen
Vermutlich willst du zumindest die Seiten, die sich auf die Volksmission Freudenstadt beziehen (7.6-7.7) entfernen oder durch eigenes Material ersetzen. 

Wenn sich durch das Einfügen oder Löschen von Seiten die Verteilung der linken und rechten Seiten ändert, musst du die Musterseiten ("Normal rechts" und "Normal links") neu zuweisen.
Wenn du auf deinem Rechner [PHP](http://www.php.net) installiert hast, dann geht das ganz einfach:

1. Schließe das Dokument in Scribus.
2. Öffne eine Eingabeaufforderung oder ein Terminalfenster in deinem crosswalk-Verzeichnis und gib die folgenden Befehle ein:  

    cd Tools
    php MasterPages.php fix
  
Das Skript passt automatisch die Links-Rechts-Verteilung aller Seiten an. Vor dem Druck in der Druckerei musst du nun nur noch sicherstellen, dass dein Dokument eine gerade Anzahl von Seiten hat. Dazu musst du evtl. am Ende eine Leerseite einfügen oder löschen. 

### Druck
Wir lassen unser Arbeitsheft als gebundene Broschüre mit Drahtheftung im A4-Format bei der Firma wir-machen-druck in Backnang drucken. Die Produktionskosten liegen bei einem Druck von 20 Exemplaren zwischen 8 und 10 Euro pro Exemplar.



## Lizenz
Alle hier bereitgestellten Inhalte werden auf der Basis der [Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International-Lizenz](https://creativecommons.org/licenses/by-sa/4.0/legalcode#languages) zur Verfügung gestellt. Bilder haben je nach Quelle eine eigene Lizenz, die in den Bildnachweisen am Ende des Hefts und in der Datei [Bilderliste.csv](Dokumentation/Bilderliste.csv) dokumentiert ist. Ausgenommen sind Logos der Volksmission Freudenstadt und ihrer Arbeitsbereiche. Für diese gilt: Alle Rechte vorbehalten.

