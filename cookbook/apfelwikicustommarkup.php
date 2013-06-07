<?php
if (!defined('PmWiki'))
	exit ();
	
/**
 * Diese Datei sammelt kleinere Markup() Anpassungen, um das unuebersichtliche
 * Zumuellen des Cookbookverzeichnisses zu vermeiden.
 */

################ (:bildlinks, rechts, mitte markup:) ################
Markup('bildlinks', 'directives', '/^\(:(bildlinks)(\s.*):\)/e', "blg('$1','$2')");
Markup('bildrechts', 'directives', '/^\(:(bildrechts)(\s.*):\)/e', "blg('$1','$2')");
Markup('bildmitte', 'directives', '/^\(:(bildmitte)(\s.*):\)/e', "blg('$1','$2')");

function blg($Status, $Message) {
    $frame = "border:1px solid #cccccc;";
	$margin = "8px;";
	if ($Status == "bildlinks")
		$align = "style='float:left; margin-right:".$margin.";".$frame."'";
	elseif ($Status == "bildrechts") $align = "style='float:right; margin-left:".$margin.";margin-right:-8px;".$frame."'";
	elseif ($Status == "bildmitte") $align = "align='center' style='".$frame."'";
	$ss = explode("|", $Message);
	return "<span style='clear:both;'></span><table $align cellspacing='0' ><tr ><td  align='center' style='background-color:#f9f9f9;'> $ss[0] </td></tr>	<tr ><td  align='center' style='background-color:#f9f9f9;'> [-$ss[1]-] </td></tr>	</table><span style='clear:both;'></span>";
}

################ Einfuegen des Google Suchfeldes ################
# Die googlesuche stellt ein Suchfeld identisch der Standardsuche zur Verfuegung
Markup('googlesuchfeld', '>links', '/\\(:(googlesuche):\\)/i', FmtPageName('<form
action="'.$ScriptUrl.'/ApfelWiki/Search" method="get" class="googleSearchForm">
<input type="hidden" name="cx" value="013237906231695894092:-qr7cunyryk" />
<input type="hidden" name="cof" value="FORID:9" />
<input type="search" tabindex=1 placeholder="$[Search]" name="q" accesskey="f" class="inputbox" value="'.$_GET['q'].'"/>
<input type="submit" value="$[Search]" class="inputbutton" />
</form>'
    , $pagename));
#value in input sollte '$[Search]' statt 'Suche' heissen. Die Lokalisierung funktioniert jedoch aus unbekannten Grund nicht

Markup('googleergebnisse', '>links', '/\\(:(googleergebnisse):\\)/i', FmtPageName(<<<EOD
<div id="cse" style="width: 100%;">Loading</div>
<script src="http://www.google.com/jsapi" type="text/javascript"></script>
<script type="text/javascript">
  google.load('search', '1', {language : 'de', style : google.loader.themes.V2_DEFAULT});
  google.setOnLoadCallback(function() {
    var customSearchOptions = {};  var customSearchControl = new google.search.CustomSearchControl(
      '013237906231695894092:-qr7cunyryk', customSearchOptions);
    customSearchControl.setResultSetSize(google.search.Search.FILTERED_CSE_RESULTSET);
    customSearchControl.draw('cse');
    function parseParamsFromUrl() {
      var params = {};
      var parts = window.location.search.substr(1).split('\x26');
      for (var i = 0; i < parts.length; i++) {
        var keyValuePair = parts[i].split('=');
        var key = decodeURIComponent(keyValuePair[0]);
        params[key] = keyValuePair[1] ?
            decodeURIComponent(keyValuePair[1].replace(/\+/g, ' ')) :
            keyValuePair[1];
      }
      return params;
    }

    var urlParams = parseParamsFromUrl();
    var queryParamName = "q";
    if (urlParams[queryParamName]) {
      customSearchControl.execute(urlParams[queryParamName]);
    }
  }, true);
</script>
EOD
    , $pagename));

################ CSS Definitionen direkt im Wikiquelltext ################
# Identische Funktion von Pm geplant, aber noch nicht implementiert. Wegen seiner Nuetzlichkeit hier vorweggenommen
# Die Funktion fuegt den css String an das Standard-CSS Output-Array, welches an jede html Ausgabe vorne weggesetzt wird.

Markup('css','directives','/\\(:css\s(.*):\\)/e', "addHTMLStylesFmt('$1')");
function addHTMLStylesFmt($string) {
	global $HTMLStylesFmt;
	$HTMLStylesFmt[]=$string;
}

######### Kleine in Klammern gesetzte Signatur bei fuenf Tilden  #########
	$ROSPatterns['/(?<!~)~~~~~(?!~)/'] = '[-([[~$Author]] $CurrentTime)-]';
  	Markup('~~~~~','<links','/(?<!~)~~~~~(?!~)/',"[-([[~$Author]] $CurrentTime)-]");
  	
######### Einfacher Timestamp  #########
	$ROSPatterns['/~~time/'] = strftime("[-%d.%m.%Y %H:%M Uhr-]", $Now);
  	Markup('~~time','<[-','/~~time/', strftime("[-%d.%m.%Y %H:%M Uhr-]", $Now));
  	
	
######################### edonkey links ####################################
  Markup('edonkey:', '<links', 
      '/edonkey:\\S+/e',
      "str_replace('|', '%7c', '$0')");
            
######################## azureus links #####################################
Markup('magnet:?xt=urn:btih:', '<links', 
      '/magnet:?xt=urn:btih:S+/e');
      
####################### Diskussions Backlinks #############################
$FmtPV['$BacklinkName'] = 'preg_replace("/-/", ".", $name, 1)';
$FmtPV['$BacklinkTitle'] = "AWBacklinks('$pagename')";

function AWBacklinks($pagename) {
	global $PCache;
	$orgpagename = preg_replace('/-/', '.', FmtPageName('$Name', $pagename), 1);
	if( !isset( $PCache[$orgpagename]['title'] ) ) 
    		PCache($orgpagename, ReadPage($orgpagename, READPAGE_CURRENT));
	return FmtPageName('$Title',$orgpagename);
}
      
########################## Bewertungssterne ################################    
Markup('bewertungssterne', 'directives',
      '/\\(:(\\*{1,5}|-|\\d\\*):\\)/e', "BewertungsSterne('$1')");

function BewertungsSterne($bewertung) {
	global $PubDirUrl;
	if ($bewertung == "-") $count = 0;
	else $count = preg_match("/(\\d)\\*/",$bewertung,$match) ? $match[1] : strlen($bewertung);
	return Keep("<img src='$PubDirUrl/cookbook/bewertungssterne/". $count. "sterne.gif' alt='Bewertung ". $count. " Sterne'/>");
}

/**
 * Rezension und Diskussion Buttons
 */
Markup('footerbadgets', 'directives','/\\(:(Rezension|Diskussion):\\)/e', "FooterBadgetsFct('$1')");

function FooterBadgetsFct($option){
	global $PubDirUrl, $ScriptUrl, $pagename;
	$name = FmtPageName("$option.\$Group-\$Name",$pagename);
  $additionalClass = '';
	if (!PageExists($name)) :
    $additionalClass = 'missing';
  endif;
  return Keep(MakeLink($pagename,$name,"$option", NULL,
        "<a class='button {$additionalClass}' href='\$LinkUrl' title='\$LinkAlt' rel='nofollow'>\$LinkText</a>"
        ));
}

########################## Qualitaetsoffensive ###############################
## Siehe dazu auch http://apfelwiki.de/forum/viewtopic.php?t=511

Markup('qualitaetsoffensive','>[=', "/\\(:Qualitaetsoffensive:\\)/", "(:include ApfelWiki.QualitaetsoffensiveVorlage:)");

############ CSS Spielereien ##########

$WikiStyleCSS[] = 'line-height';
$WikiStyleCSS[] = 'list-style-type';

############ Robots Einstellung ################

## Individuelle Robotseinträge auf einer Seite 

Markup('robots', 'directives',
     '/\\(:robots\\s+(\\w[\\w\\s,]*):\\)/e',
     "PZZ(\$GLOBALS['MetaRobots'] = '$1')");


############ Jump Box #################
$FmtPV['$_UniqId_'] = '($GLOBALS["_UniqId_"] = uniqid("id"))';
$FmtPV['$_PrevId_'] = '$GLOBALS["_UniqId_"]';

$InputTags['jumpbox'] = array(
  'name' => 'n',
  ':html' =>
    "<form action='{$PageUrl}' method='get'>
     <select onchange='window.location.href=this.options[this.selectedIndex].value'
       \$InputSelectArgs class='inputbox' >\$InputSelectOptions</select>
     <input id='{\$_UniqId_}' type='submit' value='$[Jump to page]' class='inputbutton' />
     <script type='text/javascript'><!--
document.getElementById('{\$_PrevId_}').style.display = 'none';
//--></script></form>");

Markup('input-jumpbox', '<split',
  '/\\(:input\\s+jumpbox\\s.*?:\\)(?:\\s*\\(:input\\s+jumpbox\\s.*?:\\))*/ei',
  "InputSelect(\$pagename, 'jumpbox', PSS('$0'))");

function currentYear() {
  echo date('Y');
}

/**
 * Abildung von 'center' auf deutsche Bezeichnungen
 */
  foreach(
      array(
          'mitte'  => 'center',
          'zentriert'  => 'center',
          ) as $deutsch => $english)
    SDV($WikiStyle[$deutsch],array('apply'=>'block','text-align'=>$english));
?>