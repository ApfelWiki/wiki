<?php

	if ( !defined('PmWiki') )
		exit();

	/*
	 * Include configuration for local installation
	 */
	if ( file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config_local.php') ) :
		include('config_local.php');
	endif;

/*
 * Debugmodus
 */
// Set 1 to activate debug modus. Set 0 in production!
$EnableDiag = 0;
// Activate Stopwatch output in debugmodus
if ( $EnableDiag === 1 ) :
  $EnableStopWatch = 1;
  $HTMLFooterFmt['stopwatch'] = 'function:StopWatchHTML 1';
endif;

################ Allgemein ################
## Wikititel
	$WikiTitle = "apfelwiki.de";
## Startup page name when wiki is called without specific page
	$DefaultName = 'ApfelWiki';
## Wikititel
	$WikiTitle = "apfelwiki.de";
## Wikiwoerter werden nicht am CamelCase getrennt
	$SpaceWikiWords = 0;
## URL wird nicht mittels ?n= sondern / angezeigt
	$EnablePathInfo = 1;
	$PageUrlFmt = $DefaultWikiUrl . '$Group/$Title_';
##Header werden unterdrueckt - Was macht diese Funktion genau?
	$MyHeader = '';
## CamelCase Woerter werden nicht zu Wikilinks
	$LinkWikiWords = 0;

	## UTF-8 als Kodierung aktivieren
	include_once("scripts/xlpage-utf-8.php");
## setzt die max. Ausfuehrungszeit fuer einen Befehl herauf. Wird fuer die inzwischen sonst nicht mehr funktionierende Wikisuche benoetigt
	ini_set('max_execution_time', 120);
## setzt & auf &amp; fuer die automatisch von php erzeugte PHPSESSID
	ini_set('arg_separator.output', '&amp;');
## Anzahl der Eitraege in RecentChanges
	$RCLinesMax = 2000;
## Beschraenkung der Seitenhistorie auf ein Jahr
	$DiffKeepDays = 360;
## Php 5.2.x Modifikationen für RecentChanges etc.
	ini_set('pcre.backtrack_limit', 1000000);
	ini_set('pcre.recursion_limit', 1000000);


	## Deutsche Lokalisierungen
	XLPage('de', 'PmWikiDe.XLPage');
	XLPage('de', 'PmWikiDe.XLPageLocal');
	XLPage('de', 'PmWikiDe.XLPageCookbook');

## Zeitstempel
	$TimeFmt = "%d.%m.%Y %H:%M Uhr";

############## Sicherheitsanalyse #############
##include_once("$FarmD/cookbook/analyze.php");
## $AnalyzeKey = 'sischer';
################ Uploads ################  
## Aktivieren der Uploadmoeglichkeit
	$EnableUpload = 1;
##Uploads koennen durch Dateien gleichen Namens ueberschrieben werden
	$EnableUploadOverwrite = 1;
##Ueberschriebene Dateien werden nicht geloescht, sondern versioniert
	$EnableUploadVersions = 1;
##Allgemeine Maximale Uploadgroesse
	$UploadMaxSize = 100000; //100kb
#Maximale Uploadgroesse fur Zip Anhaenge
	$UploadExtSize['zip'] = 500000;
	$UploadExtSize['png'] = 350000;
	$UploadExtSize['jpg'] = 300000;
##Erlaubte Dateianhaenge 
	$UploadExts = array(
			'gif' => 'image/gif',
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png' => 'image/png',
			'zip' => 'application/zip',
			'gz' => 'application/x-gzip',
			'tgz' => 'application/x-gzip',
			'hqx' => 'application/mac-binhex40',
			'sit' => 'application/x-stuffit',
			'pdf' => 'application/pdf',
			'php' => 'text/plain',
			'psd' => 'text/plain',
			'ps' => 'application/postscript',
			'eps' => 'application/postscript',
			'txt' => 'text/plain',
			'rtf' => 'application/rtf',
			'midi' => 'audio/midi',
			'ppt' => 'application/powerpoint',
			'' => 'text/plain' );
##  Uploads gruppenbasiert (PmWiki Standard)
	$UploadPrefixFmt = '/$Group';
## Angabe des Uploadverzeichnisses
	$UploadUrlFmt = $DefaultWikiUrl . "briefkasten";

################ URL Link Definitionen und Farbe ################
## behandelt den Link ins forum wie einen normalen Link
	$UrlLinkFmt = "<a class='urllink' target='_blank' href='\$LinkUrl'>\$LinkText</a>";

## In *map.txt Definierte Intermap Links werden wie Wikilinks dargestellt
	$IMapLinkFmt['Forum:'] = "<a class='selflink' href='\$LinkUrl'>\$LinkText </a>";
	$IMapLinkFmt['Fo:'] = "<a class='selflink' href='\$LinkUrl'>\$LinkText </a>";
	$IMapLinkFmt['ForumIndex:'] = "<a class='selflink' href='\$LinkUrl'>\$LinkText </a>";
	$IMapLinkFmt['wikien:'] = "<a class='selflink' href='\$LinkUrl'>\$LinkText </a>";
	$IMapLinkFmt['wikide:'] = "<a class='selflink' href='\$LinkUrl'>\$LinkText </a>";

## Tabellen sind in Zebra mittels abwechselnden Farben durch tr.ind1 und tr.ind2 in local.css darstellbar
	$TableRowIndexMax = 2;
	$TableRowAttrFmt = "class='ind\$TableRowIndex'";


	################ Skins ################
## legt das Standardskin fest
	$Skin = 'newaw';

##http://www.pmwiki.org/wiki/Cookbook/SkinChange
##Angabe der ueber ?skin=xxx anwaehlbaren Skins
	$PageSkinList = array(
			'apfelwiki' => 'apfelwiki',
			'newaw' => 'newaw' );
	include_once("cookbook/skinchange.php");

	################ Nachrichtenseite ##############
	# kurzer Zeitstring fuer Startseitentemplate #nachrichtenawstartseite auf 
	# http://apfelwiki.de/index.php/Site/PageListTemplates
	$FmtPV['$CreatedAWGermanShort'] = 'strftime("%d.%m.%Y", $page["ctime"])';


## html tags einbinden aktivieren
	$AllowedHTML = "sup|sub|div|table|td|tr";
	Markup("html", "<directives", "/&lt;(\\/?($AllowedHTML)\\b.*?)&gt;/e",
			"Keep(PSS('<$1>'))");
	Markup("html-$AllowedHTML", '>{$var}',
			'/&lt;(\/?(' . $AllowedHTML . ')(?![a-z!])(([\'"]).*?\4|.*?)*?)&gt;/ie',
			'\'<:block>\'.Keep(PSS(\'<$1>\'))');


################ Spam Block #############
## Blockliste aktivieren
	$EnableBlocklist = 10;
//  Verschiedene Listen aktivieren
# Blocklist fuer manuelle Eintraege via Wikiinterface
	$BlocklistPages = array( 'SiteAdmin.Blocklist, SiteAdmin.Blocklist-MoinMaster, SiteAdmin.Blocklist-PmWiki' );
	# Download the MoinMaster blocklist every twelve hours
	$BlocklistDownload['SiteAdmin.Blocklist-MoinMaster'] = array(
			'url' => 'http://master19.moinmo.in/BadContent',
			'format' => 'regex' );

# Download a shared blocklist from pmwiki.org every day
	$BlocklistDownload['SiteAdmin.Blocklist-Chongqed'] = array(
			'url' => 'http://blacklist.chongqed.org/',
			'format' => 'regex',
	);
# Download a shared blocklist from pmwiki.org every day
	$BlocklistDownload['SiteAdmin.Blocklist-PmWiki'] = array(
			'format' => 'pmwiki' );
##Allows the poster to see a message on the edit page
	// about why they have been blocked.
	$EnableWhyBlocked = "1";
	# perform immediate checks for ?action=comment
	$BlocklistActions['comment'] = 1;
	# perform immediate checks for ?action=postdata
	$BlocklistActions['postdata'] = 1;

	$BlocklistDownloadRefresh = 86400 * 7;

  /*
   * Captcha aktivieren
   *
   * Falls der aktuelle Nutzer bereits einmal ein Captcha positiv beantwortet
   * hat, wird Captcha für 24h nicht mehr aktiviert.
   */
  $CaptchaDisabled = 1;
  if ( isset($_COOKIE['CaptchaDisabler']) === FALSE ) :
    global $CaptchaDisabled;
    include_once("cookbook/captcha.php");
    $EnablePostCaptchaRequired = 1;
    $EnableCaptchaImage = 1;
    $CaptchaDisabled = 0;
  endif;

  $LogoutCookies[] = 'CaptchaDisabler';
	if ( $action == 'edit' || $action == 'postnewpage' ) :
    $EditFunctions[] = 'CaptchaDisabler';
    function CaptchaDisabler() {
      global $EnablePost;
      if ( $EnablePost ) :
        setcookie('CaptchaDisabler', '1', time()+86400, '/');
      endif;
    }
  endif;

################ GUI-Buttons ################        
	##Schaltet die Buttons ueber den Edittextfeld ein (gEdit)
	$EnableGUIButtons = 1;
	## Zusätzliche Buttons der Standarddistrubution
	$GUIButtons['ol'] = array( 520, '\\n# ', '\\n', '$[Ordered list]',
			'$GUIButtonDirUrlFmt/ol.gif"$[Ordered (numbered) list]"' );
	$GUIButtons['ul'] = array( 530, '\\n* ', '\\n', '$[Unordered list]',
			'$GUIButtonDirUrlFmt/ul.gif"$[Unordered (bullet) list]"' );

	## Zusätzliche awspezifische Buttons
	$GUIButtons['sig'] = array( 460, ' ~~~~ ', '', '', '$FarmPubDirUrl/cookbook/awguibuttons/sig.gif"$[Autorkürzel und Zeitstempel]"' );
	/* $GUIButtons['stickyNote'] = array(700, '(:note Hinweis |', ':)\\n', '$[Text]',
	  '$FarmPubDirUrl/cookbook/awguibuttons/sticky.gif"$[Hinweiszettel]"'); */
	$GUIButtons['maccommand'] = array(
      600, '[[Main.Befehlstaste|&#x2318;]]', '', '',
			'$PubDirUrl/cookbook/awguibuttons/command.gif"$[Befehlstaste]"' );
	$GUIButtons['macalt'] = array(
      605, '[[Main.Wahltaste|&#x2325;]]', '', '',
			'$PubDirUrl/cookbook/awguibuttons/alt.gif"$[Wahltaste]"' );
	$GUIButtons['macshift'] = array( 
      610, '[[Main.Umschalttaste|&#x21E7;]]', '', '',
			'$PubDirUrl/cookbook/awguibuttons/shift.gif"$[Umschalttaste]"' );

	################ Coobook ################
## Erzeugen der Uebersicht auf Kategorie/Index
	$CategoryGroup = 'Katalog';
	include_once("cookbook/categoryindex.php");

## http://www.pmwiki.org/wiki/PmWiki/RefCount
	include_once("scripts/refcount.php");

## Was macht dieses Skript?
//	include_once("$FarmD/cookbook/datetimestamp2.php");
##Seitenzaehler auf frontseite
	include_once("cookbook/numberofsites2.php");

## Anzahl der Seitenaufrufe: Sorgt fuer anlegen der .count Dateien sowie
## Ausgabe der Variablen (:pagecount:) (:pagecountday:)
	include_once("cookbook/pagecount.php");

## Erstellt das Seiteninhaltsverzeichnis
	$TocSize = 'small';
	include_once("cookbook/pagetoc.php");
	## Ueberschrift des Inhaltsverzeichnisses
	$DefaultTocTitle = "Inhalt";
	## Formatierung der Ueberschrift
	# 	$TocHeaderFmt = "<b>$DefaultTocTitle</b>";
## Markup Extensions
## http://www.pmwiki.org/pmwiki2/pmwiki.php/Cookbook/MarkupExtensions
## Provisorisch, bis Q&A Syntax in PmWiki2 aufgenommen
	include_once("cookbook/extendmarkup2.php");

## Zum erstellen einer Indexseite
	include_once("cookbook/titledictindex.php");

## Fuer gelbe kleine NoteIt Zettelchen
	include_once("cookbook/postitnotes.php");

## Rot13 der Emailadressen
	include_once("cookbook/e-protect.php");

## Definiert das Aussehen von Wikilinks zu noch nicht existenten Seiten. E.g. wird das PmWiki Standardfragezeichen entfernt.
	$LinkPageCreateFmt = "<a class='createlinktext' href='\$PageUrl?action=edit'>\$LinkText</a>";
	$LinkPageCreateSpaceFmt = "<a class='createlinktext' href='\$PageUrl?action=edit'>\$LinkText</a>";

## Mailen einer Wikiseite
# http://www.pmwiki.org/wiki/Cookbook/TellAFriend
	include_once("cookbook/tellafriend.php");
//	include_once('cookbook/TellAFriend/default.php');
## Sorgt fuer die Bereitstellung des rss Feeds
//	if ($action == 'rss') include_once("cookbook/rss.php");    

	if ( $action == 'rss' ||
			$action == 'atom' ||
			$action == 'rdf' ||
			$action == 'dc' )
		include_once("$FarmD/scripts/feeds.php");


	$FeedFmt['rss']['item']['title'] = '{$Group} - {$Title} : {$LastModified} $[by] {$LastModifiedBy} - {$LastModifiedSummary}';
	$FeedFmt['rss']['item']['description'] = 'FeedText';

	function FeedText($pagename, &$page, $tag) {
		$p = ReadPage($pagename);
		$content = MarkupToHTML($pagename, $p['text']);
		return "<$tag><![CDATA[$content]]></$tag>";
	}

## Aufruf eines Zufallsartikles
	$PmWikiAutoUpdate['RandomWikiPage']['source'] = true;
	include_once("cookbook/showrandomarticle.php");

## Activates Simuledit
	$EnableSimulEdit = 1;

## Umbenennen von Seiten via ?acton=rename
	include_once("cookbook/rename.php");

## serverseitige Bilderskalierung und Galerie
	include_once("cookbook/awimages.php");


## Mediawiki Markup Umsetzung
	#verwendet für die McGyver Integration. Vorsicht, nicht zusammen mit complexvote verwenden!
	#include_once("$FarmD/cookbook/wikimarkupconversion.php");
	# welche Umsetzung ist gewuenscht (MediaWiki)
	#$WikiMarkupExtensions = array ("MediaWiki");
## Erlaubt das abschnittsweise bearbeiten von Seiten
	Markup('delete====', 'directive', '/====/', "");
	# die Includefunktionalitaet des Scriptes ist bugy und deaktiviert
	$SectionEditInIncludes = false;
	include_once("cookbook/sectionedit.php");
	#entfernt die ueberfluessigen ==== automatisch beim speichern
	$ROSPatterns['/====/'] = '';


## Ermittlung von fehlenden oder verwaisten Attachments
	include_once("cookbook/attachlistenhanced.php");

## Markup Spezialitaeten von ApfelWiki
	include_once("cookbook/apfelwikicustommarkup.php");

## Ermittelt fehlende Seiten (fuer die RightBar)
	include_once("cookbook/missingwikipages.php");

## Ermittelt Sackgassenartikel
	include_once("cookbook/deadendpages.php");

## Ermittelt kurze Wikiseiten
	include_once("cookbook/stubpages.php");


## Stellt das Loeschen von Seiten via Review bereit
	#$MarkForDeleteGroupExcludePattern = "";
	$MarkForDeletePagename = "ApfelWiki.Loeschkandidaten";
	$MarkForDeleteForm = "ApfelWiki.LoeschkandidatenFormular";
	$MarkForDeleteAllreadyOnPage = utf8_decode("
<strong>Der Löschkandidat konnte nicht hinzugefügt werden.</strong><br /><br />
Die Seite <em>\"" . MakeLink($pagename,
					$_REQUEST['pagetodelete'], $_REQUEST['pagetodelete']) . "\"</em> befindet sich bereits bei den " . MakeLink($pagename,
					$MarkForDeletePagename, "Löschkandidaten") . ".");
	$MarkForDeletePutOnPage = '(:noteblock color=pink float=right:)
L&ouml;schkandidat
(:notecontent:)
Diese Seite wurde am $now von [[~$Author]] als [[ApfelWiki.Loeschkandidaten#$FullName|L&ouml;schkandidat]] markiert. 
(:noteblockend:)';

	include_once("$FarmD/cookbook/markfordelete.php");

## template Einblendung im Bearbeitenmodus
	if ( $action == 'edit' || $action == 'postnewpage' ) {
		$AwTemplates = array(
				'Rezension' => "Rezension.Vorlage",
				'Main' => "Site.Vorlage",
				'CookBook' => "CookBook.Vorlage",
				'ApfelWiki/Loeschkandidaten' => "ApfelWiki.LoeschkandidatenVorlage",
		);
		include_once("$FarmD/cookbook/aw_templates.php");
	}

## sorgt für das runterskalieren kleiner Bilder sowie den popup in neuen Fenster
	$ImgPopUpShowZoomLinkTreshold = 1.1;
	$ImgPopUpSameWindow = true;
	$ImgPopUpMaxImgWidth = 300;
  $ImgPopUpRemoveChrome = FALSE;
	include_once("cookbook/imgpopup.php");

## sorgt fuer das Springen im Suchfeld
	include_once("cookbook/jumpnsearch.php");

## yahoo Suche
## Auskommentiert, da nicht mit PHP5 kompatibel

	/* 	$YahooSearchLocalSiteURI = "apfelwiki.de";
	  $YahooSearchAPPID = "apfelwiki";
	  $YahooSearchNoSearchResultsFmt = '<strong>Kein Suchergebnis.</strong><br /><br />' .
	  'Es wurden keine &Uuml;bereinstimmungen mit "<em>'.$_REQUEST['q'].'"</em> gefunden. <br />' .
	  '<br />Bitte versuchen Sie einen einen anderen Suchbegriff.';
	  $YahooSearchNoSearchTermFmt = "<strong>Kein Suchergebnis.</strong><br /><br />" .
	  "Es wurde kein Suchtext eingegeben.<br /><br />" .
	  "Bitte geben sie einen zu suchenden Text in das Suchfeld ein.";
	  include_once("cookbook/yahoosearch.php"); */

## sorgt fuer das Springen im Suchfeld
	include_once("cookbook/complexvote.php");

## Rezensionen (Wertung und Sortierung)
	include_once("cookbook/rezensionen.php");

## automatische Wiederherstellen von Seiten nach einer bestimmten Zeit
	$AutoRestoreKeep = 900;						# keep edits for 15 minutes (900 seconds)
	include_once("cookbook/autorestore.php");

## automatische Wiederherstellen von Seiten nach einer bestimmten Zeit
## als letzten Cookbook include stehen lassen
	include_once("cookbook/pmwikiautoupdate.php");

## Javascript Counter (:jscounter:)
	#$JSYear=2006;$JSMonth=1;$JSDay=10;$JSHour=18;$JSMinute=00;
	#include_once("$FarmD/cookbook/jscountdown.php");
## legt fest, dass die RecentChanges den Title statt den Seitennamen anzeigen
#	$RecentChangesFmt['$SiteGroup.AllRecentChanges'] = 
#		'* [[$Group.$Name | $Titlespaced ($Group)]]  . . . $CurrentTime $[by] #$AuthorLink: [=$ChangeSummary=]';
#	$RecentChangesFmt['$Group.RecentChanges'] = 
#		'* [[$Group/$Name | $Titlespaced]]  . . . $CurrentTime $[by] #$AuthorLink: [=$ChangeSummary=]';
#Erstellt die RecentChanges mit Verlauf
	$RecentChangesFmt['$SiteGroup.AllRecentChanges'] =
			'* [[$Group.$Name]] [-([[$Group.$Name?action=diff|Verlauf]])-]  . . . $CurrentTime $[by] $AuthorLink: [=$ChangeSummary=]';

## laegt an und aktualisiert die Wikiseite RecentPages beim generieren von RecentChanges. RecentPages zeigt auf der Homepage die letzten Aenderungen mittels include
	## Achtung: Zwei Leerstellen nach dem Wikilink!
	$RCTime = strftime('%d.%m. %H:%M', $Now);
	$RecentChangesFmt['Main.RecentPages'] = '* [[$Group.$Name  | $Titlespaced]]  $[by] $AuthorLink';

## Sammelt die Beitraege eines Autors in Contributions/Autorkuerzel
	include_once("scripts/author.php");
	if ( PageExists($AuthorPage) ) {
		$cpagename = MakePageName('Contributions.HomePage', $Author);
		$RecentChangesFmt[$cpagename] =
				'* [[$Group.$Name]]  . . . $CurrentTime - \'\' [=$ChangeSummary=] \'\' ';
	}


## Ausgabe des title statt name falls definiert
	$FPLByGroupIFmt = "<dd><a href='\$PageUrl'>\$Title</a></dd>\n";

## TraceTrail für eine Anzeige der zuletzt besuchten Seiten

	include_once("cookbook/tracetrail2.php");

/*
 * Einbinden Analytics
 *
 * Jedoch nur, wenn wirklich auf dem Produktionsserver läuft
 */
if ( $_SERVER['SERVER_NAME'] === 'www.apfelwiki.de' && $EnableDiag === 0 ) :
  $HTMLHeaderFmt['javascript'][] = '<script src="/mint/?js" type="text/javascript"></script>';
endif;

/*
 * Setzt das Format für GroupFooter Seiten
 *
 * - Abgrenzung oben durch horizontale Linie
 * - Einpacken in Style-Klasse `groupfooter`
 */
$GroupFooterFmt = '(:nl:)(:div1 class="groupfooter":)(:nl:)-----(:nl:)(:include {$Group}.GroupFooter self=0 basepage={*$FullName}:)(:nl:)(:div1end:)(:nl:)(:div class="clearfix":)(:nl:)(:divend:)';

## Vorlagenmenü
	$LinkPageCreateFmt = "<a class='createlinktext' rel='nofollow' href='\$LinkUrl'>\$LinkText</a>";

?>