# Deployment Apfelwiki aus git repository #

## Quellcode aus git auschecken ##

Von github auschecken oder als zip downloaden.

## ApfelWiki Konfiguration aktivieren ##

Die ApfelWiki `config_local.php` (befindet sich nicht im Repository) nach `local/config_local.php` kopieren.

## Hochladen mit ftp_upload.sh ##

### Info ###

Voraussetzungen: ncftp (installierbar bspw. via [MacPorts](http://www.macports.org/))

Das Skript löscht keine Dateien, sondern lädt hoch und überschreibt vorhandene Dateien  falls nötig. Das Skript kopiert alle relevanten Dateien auf den Server *mit Ausnahme* von:

- `briefkasten/` (potentiell Dateien auf dem Server vorhanden)
- `cache/` (wird von PmWiki automatisch angelegt falls nötig)

Darüber hinaus werden selektiv einige Dateien aus 

- `wiki.d/` 
 
herauf geladen. Ist der Ordner `wiki.d` auf dem Server nicht vorhanden, wird er neu angelegt (u.U. sind die Schreibrechte korrekt zu setzen).


### Skript konfigurieren ###

Im Kopf des Scriptes ausfüllen:

Zielverzeichnis auf dem Server relativ zum Login-Verzeichnis auf dem Server. Für Produktion `/` oder für das Testverzeichnis `awtest/`.

	WORKDIR="awtest/"

Der FTP-User:
	
	USER="<user>"

Das FTP-Passwort für diesen FTP-User:

	PASSWD="<password>"

### Skript ausführen ###

Im Terminal ausführen:

	sh /<Pfad zum Skript>/ftp_deploy.sh

