<?php

	if ( !defined('PmWiki') )
		exit();

################ Allgemein ################
## Standardwikiurl
#	$DefaultWikiUrl = 'http://www.myserver.de/';

## FarmPubDirUrl
#	$FarmPubDirUrl = 'http://www.myserver.de/pub';

## Versteckt die index.php in der Wikiurl
#	$ScriptUrl = $DefaultWikiUrl;

################ Upload ################

	$UploadDir = "briefkasten";

################ Passwortschutz ################
## Blendet passwortgeschuetzte Seiten in pagelist und searchresult aus.
#	$EnablePageListProtect = 1;

## Editing password
#	$DefaultPasswords['edit'] = crypt('password');

## Uploadpasswort
#	$DefaultPasswords['upload'] = crypt('password');

## Administratorpasswort
#	$DefaultPasswords['admin'] = crypt('password');

## Passwort fuer Passwortaenderungen mittels ?action=attr
#	$DefaultPasswords['attr'] = crypt('password');

##  Loeschkennwort
#	$DeleteKeyPattern = "^\\s*password\\s*$";

?>