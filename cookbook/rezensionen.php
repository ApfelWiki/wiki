<?php

/**
 * Rezensionswertung
 * 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 0.1.2
 * @copyright by the respective authors 2005
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package rezensionen
 * 
 */

Markup('rezensionen','<split','/\\(:rezensionstart:\\)(.*?)\\(:rezensionende:\\)/se',"rezensionAnzeigen(PSS('$1'))");
Markup('{$PageUrl}', '>{$fmt}', '/\\{\\$PageUrl\\}/', FmtPageName('$PageUrl', $pagename));

$RezensionenLfdNr = 0;

define(REZENSIONEN, '0.1.2');

SDVA($PmWikiAutoUpdate['Rezensionen'], array(
    'version' => REZENSIONEN, 
    'updateurl' => 'ApfelWiki only at the moment'
));

SDV($HandleActions['rezensionbewerten'], 'HandleRezensionBewerten');
SDV($HandleAuth['rezensionbewerten'], 'read');

function rezensionAnzeigen($rezension) { 
	global $RezensionenLfdNr, $PageUrl;
	
	$RezensionenLfdNr += 1;
	
	$output[] = $rezension.Keep("<br />");
	
	$output[] = Keep("<form action='$PageUrl?action=rezensionbewerten' method='post' style='font-size:smaller; display:inline;' >");
	
	if (preg_match("/%comment%Hilfreiche Rezension:(\d*?)%%/", $rezension, $hilfreichzaehler))
		$output[] = $hilfreichzaehler[1]." positive Wertung(en). ";
	 
	$output[] = Keep("War diese Rezension hilfreich?: " .
			"<input type='submit' value='Ja'/>" .
			"<input type='hidden' name='RezensionsID' value='$RezensionenLfdNr' />" .
			"</form><br />");
			
	return implode("",$output); 
}

function HandleRezensionBewerten($pagename, $auth = 'read') {
	global $HandleActions, $ChangeSummary;
	
	Lock(2);
	$page = RetrieveAuthPage($pagename, $auth);
	
	
	$bearbeiteterezension = $_POST['RezensionsID'];
	
	$rezensionen = preg_split("/(?=\\(:rezensionstart:\\))/", $page['text']);
	
	if (!preg_match("/%comment%Hilfreiche Rezension:(\d*?)%%/s",$rezensionen[$bearbeiteterezension],$match))
	 	$rezensionen[$bearbeiteterezension] = str_replace("(:rezensionende:)","%comment%Hilfreiche Rezension:1%%\n(:rezensionende:)",$rezensionen[$bearbeiteterezension]);
	else {
	 	$cc = $match[1]+1 ;
	 	$rezensionen[$bearbeiteterezension] = preg_replace("/%comment%Hilfreiche Rezension:\d*?%%/","%comment%Hilfreiche Rezension:$cc%%",$rezensionen[$bearbeiteterezension]);	
	}	
	
	foreach ($rezensionen as $k => $rez) {
		if (preg_match("/%comment%Hilfreiche Rezension:(\d*?)%%/s",$rez,$match))
			$RezWertung[$k] = $match[1];
		else 
			$RezWertung[$k] = 0;
	}
	
	arsort($RezWertung, SORT_NUMERIC);
	reset($RezWertung);
	
	foreach ($RezWertung as $k => $v) 
		$output[] = $rezensionen[$k];
	
	$pagetext = implode("",$output);
	
	$_POST['text'] = get_magic_quotes_gpc() ? addslashes($pagetext) : $pagetext;
	$ChangeSummary = FmtPageName("$[Rezensionsbewertung]", $pagename);
	$_POST['post'] = 1;
	$HandleActions['edit'] ($pagename, $auth);
}
?>