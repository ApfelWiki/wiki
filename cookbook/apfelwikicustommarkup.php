<?php
if (!defined('PmWiki'))
	exit ();
	
# Die Datei sammelt kleinere Markup Anpassungen, um das unuebersichtliche Zumuellen des Cookbookverzeichnisses zu vermeiden.

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

Markup('googlesuchfeld', '>links', '/\\(:(googlesuche):\\)/i', FmtPageName("<form class='wikisearch' method='get' action='http://www.google.de/custom'>
		<input class='wikisearchbox'    type='text' name='q' value='' size='40' /><input 
    class='wikisearchbutton' type='submit' value='Suche' />
		<input type='hidden' name='domains' value='apfelwiki.de' />
		<input type='hidden' name='sitesearch' value='apfelwiki.de' /> 
		</form>", $pagename));
#value in input sollte '$[Search]' statt 'Suche' heissen. Die Lokalisierung funktioniert jedoch aus unbekannten Grund nicht


############## Einfuegen des Google Coop Suchfeldes ###############
# Die googlesuche stellt ein Suchfeld identisch der Standardsuche zur Verfuegung

Markup('googlecoopsuchfeld', '>links', '/\\(:(googlecoop):\\)/i', FmtPageName("<form class='wikisearch' method='get' id='searchbox_013271604353590352065:pvntxplgwoy' action='http://www.google.com/cse'>
		<input class='wikisearchbox'    type='text' name='q' value='' size='40' /><input 
    class='wikisearchbutton' type='submit' value='Suche' />
		 <input type='hidden' name='cx' value='013271604353590352065:pvntxplgwoy' />
    <input type='hidden' name='cof' value='FORID:0' />
    <input name='q' type='text' size='40' />
    <input type='submit' name='sa' value='Search' /> 
		</form>", $pagename));
		
		        
#value in input sollte '$[Search]' statt 'Suche' heissen. Die Lokalisierung funktioniert jedoch aus unbekannten Grund nicht


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
$FmtPV['$BaseName'] = 'str_replace("-", ".", $name)';

Markup('{$BaseTitle}', '>{$fmt}',
      '/{\\$BaseTitle}/e',"AWBacklinks('$pagename')");
      
function AWBacklinks($pagename) {
	global $PCache;
	$orgpagename = preg_replace('/-/', '.', FmtPageName('$Name', $pagename), 1);
	if( !isset( $PCache[$orgpagename]['title'] ) ) 
    		PCache($orgpagename, ReadPage($orgpagename, READPAGE_CURRENT));
	return FmtPageName("\$Title",$orgpagename);
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

########################## googletracking ################################  
##
##$HTMLHeaderFmt['googletracking'] = "<script src=\"http://www.google-analytics.com/urchin.js\" type=\"text/javascript\">
##</script>
##<script type=\"text/javascript\">
##_uacct = \"UA-74322-1\";
##urchinTracker();
##</script>";

########################## Badgets ################################    
Markup('apfelwikibeta', 'directives',
      '/\\(:awbeta:\\)/e', "Keep(MakeLink(\"$pagename\",\"ApfelWiki.Beta\",\"<img src='$PubDirUrl/cookbook/awcustommarkup/ApfelWiki-beta.gif' alt='Beta'/>\"))");
      
Markup('footerbadgets', 'directives','/\\(:(Rezension|Diskussion):\\)/e', "FooterBadgetsFct('$1')");

function FooterBadgetsFct($option){
	global $PubDirUrl,$pagename;
	$name = FmtPageName("$option.\$Group-\$Name",$pagename);
	if (PageExists($name)) 
		return Keep(MakeLink($pagename,$name,"<img src='$PubDirUrl/cookbook/awcustommarkup/ApfelWiki-$option.gif' alt='$option'/>"));
	else  {
		
		return Keep("<a href='http://www.apfelwiki.de/$option/".FmtPageName("\$Group-\$Name",$pagename)."'><img src='$PubDirUrl/cookbook/awcustommarkup/ApfelWiki-$option-sw.gif' alt='$option'/></a>");
	}
}

########################## Firefox Search Plugin ###############################

Markup('firefoxsearchpluginscript','style','/\(:firefoxsearchpluginscript:\)/e', "firefoxsearchpluginscriptfunction()");

function firefoxsearchpluginscriptfunction()
{
 $l='<script type="text/javascript">
<!--
function errorMsg()
{
  alert("Netscape 6 or Mozilla is needed to install a search plugin");
}
function addEngine(name,ext,cat)
{
  if ((typeof window.sidebar == "object") && (typeof
  window.sidebar.addSearchEngine == "function"))
  {
    window.sidebar.addSearchEngine(
      "http://apfelwiki.de/pub/cookbook/firefox/"+name+".src",
      "http://apfelwiki.de/pub/cookbook/firefox/"+name+"."+ext,
      name,
      cat );
  }
  else
  {
    errorMsg();
  }
}
//-->
</script>';

	return $l;
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
?>