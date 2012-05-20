<?php if (!defined('PmWiki')) exit();


/*
 * Ermittelt fehlende Seiten fuer die Frontseite
 * @param author Sebastian Siedentopf <schlaefer@macnews.de>
 * @param version 1.3
 */
Markup('missingwikipagesshort','directives','/\(:missingwikipagesshort:\)/e' , "MissingWikiPagesShortFct()");

function MissingWikiPagesShortFct(){

    StopWatch('MissingWikiPagesShortFct Start');
    
    global $WikiLibDirs;
    
    $Group = array('Main' => 1);

    # [performance] keine Verwendung von ListPages(), da wir nur das Verzeichnis wiki.d duchsuchen muessen
    # Filterung auf gewuenschte Gruppe bereits an dieser Stelle
    $pagelist = $WikiLibDirs[0]->ls("/^".implode("|", array_keys($Group))."\./");

    # maximale Anzahl der auszugebenen Artikel
    $maxpages = 10;
  	# [performance] Annahme, dass durchschnittlich fuenfmal die Anzahl der Seiten
  	# fuer die $maxpages fehlende Verweise benoetigt wird
    $numberOfPagesToSearch = 5 * $numberOfPagesToSearch;
    // limit search to number of pages available
    $numberOfPagesAvailable = count($pagelist);
    if ( $numberOfPagesAvailable < $numberOfPagesToSearch ) :
      $numberOfPagesToSearch = $numberOfPagesAvailable;
    endif;

	$pagelist = rand_elementsOfArray_maxOutputElements($pagelist, $numberOfPagesToSearch);

    $outc = 0;
    
    foreach($pagelist as $pname) {
        if (strpos($pname, 'Recent')) continue;
      	$page = ReadPage($pname);
      	if (!$page) continue;
      	foreach(explode(',',@$page['targets']) as $r) {
        	if ($r != '' && $Group[substr($r, 0, strpos($r, '.'))] && !PageExists($r)){
        		$out[] = "[[$r|+]]";
        		if (++$outc == $maxpages) break 2;
        	}
        }
    }
    if ($outc == 0) return "";
	sort($out);
    StopWatch('MissingWikiPagesShortFct End');
    return implode(" - ", $out);

}


Markup('backlinksshort','directives','/\(:backlinksshort\\s(.*):\)/e' , "BackLinks(PSS('$1'))");

function BackLinks($args){

    StopWatch('BackLinks Start');
	$pargs = ParseArgs($args);

	if (!($link = $pargs['link']) || !PageExists($pargs['link']))
		return " Ungültiger Seitenname ";
	else
	    $link = MakePageName($pagename, $link);

	if ($pargs['group'])
		$Group = str_replace(",","|",$pargs['group']);
	
	if ($pargs['-']) 
		$ExcludePatter = str_replace(",", "|", $pargs['-'][0]);
	else
	    $ExcludePatter = "\236";

 	$backlinks = PageIndexGrep($link);

	if (count($backlinks) == 0)
		return "";
		
	if($sort = $pargs['sort']){
		switch ($sort) {
			case 'random':
				$backlinks = rand_elementsOfArray_maxOutputElements($backlinks);
				break;
			default:
				break;
		}
	}
	
	$pargs['count'] ? $count = $pargs['count'] : $count = count($backlinks);
	
	foreach($backlinks as $backlink) {
		if (!preg_match("/".$Group."\..*/",$backlink) || preg_match("/".$ExcludePatter."/",$backlink)) continue;
       	$out[] = "[[$backlink|+]] ";
		if (count($out)>=$count) break;
    }

	if (count($out) == 0)
		return "";
	
    sort($out);
    StopWatch('Backlinks Stop');
    return implode(" - ",$out);

}


function rand_elementsOfArray_maxOutputElements ($array, $maxOutElements = FALSE) {
    StopWatch('rand_elementsOfArray_maxOutputElements Start');
	srand(time());
	$count = count($array) - 1;
	
	if ($maxOutElements == FALSE)
		$maxOutElements = $count + 1;

  $seen = array();
	for ($i = 0; $i < $maxOutElements;){
		$rand = rand(0, $count);
	 	if (!isset($seen[$rand])) {
			$seen[$rand] = TRUE;
			$out[] = $array[$rand];
			$i++; 
		}
	}
    StopWatch('rand_elementsOfArray_maxOutputElements End');
	return $out;
}

?>