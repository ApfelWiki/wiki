<?php

/**
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 1.3.4
 * @link 
 * @copyright by the authors 2005-2012
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package pagecount
 */

define (PAGECOUNT, '1.3.4');

$PageAttributes['pageviewcounterglobal'] = ('Pagecount: Global');
$PageAttributes['pageviewcounterdaily'] = ('Pagecount: Daily');

# We don't want to count the serach bots. In addition to this some 
# bots call pages with wrong utf-8 names. Which can be very bad 
# if PageExists() always returns always TRUE. ;)
$PageCountExcludeBots = "google|yahoo|msnbot|seekbot|jeeves|baiduspider|libcurl|gigabot|slurp|wget|becomebot|fast|zyborg";

if ($action == 'browse' && !preg_match("/$PageCountExcludeBots/i", @$_SERVER['HTTP_USER_AGENT']) ) {
	
	# We can't evaluate the page name if it isn't specified in the url at this point. Also $DefaultName is potentaly defined later in a per group script so we hardcode the portal main name here and count all links to nonexisting pages as a call to the portal site. 
	
	# PageExists returns 1 with empty strings in 2.1beta20
	if (PageExists($pagename) && $pagename != "") 
		 $counterpagename = $pagename;
	else
		$counterpagename = "Main.ApfelWiki";
	
	if (PageExists($counterpagename)) 	
	{
		Lock(2);
		$page = ReadPage($counterpagename);
	
		# initialize the start values
		if (!isset ($page['pageviewcounterglobal'])) {
			$page['pageviewcounterglobal'] = 0;
			$page['pageviewcounterdaily'] = 0;
			$page['pageviewcounterlastupdate'] = date("d");
		}
	
		#get old values from .count files
		$page = getOldValues($counterpagename, $page);
	
		# resets the daily counter on a new day
		if ($page['pageviewcounterlastupdate'] != date("d"))
			$page['pageviewcounterdaily'] = 0;
	
		# set new values
		$page['pageviewcounterglobal'] += 1;
		$page['pageviewcounterdaily'] += 1;
		$page['pageviewcounterlastupdate'] = date("d");
		
		# don't insert new modification date in the file
		# i.e. prevent multiple appearing in the rss feed
		$Now = $page['time'];
		
		WritePage($counterpagename, $page);
		
		Lock(0);
	}
}

/**
 * Gets the old values from the .count file and delete it afterwards
 *
 * @param string $pagename the name of the page
 * @param array $page a complete page obtained by ReadPage()
 * @return array input array $page with old counter values
 */
function getOldValues($pagename,$page) {
	$counterfile = "wiki.d/.".str_replace("/", ".", $pagename).".count";

	if (file_exists($counterfile)) {
		$file = fopen($counterfile, "r");
		if ($file) {
			$page['pageviewcounterglobal'] = intval(fgets($file, 4096));
			$page['pageviewcounterdaily'] = intval(fgets($file, 4096));
			$page['pageviewcounterlastupdate'] = intval(fgets($file, 4096));
		}
		fclose($file);
		unlink($counterfile);
	}
	return $page;
}

/*
 * Markup for displaying the pagecounter value on a page
 */
Markup( "pagecount", "directives", "/\\(:pagecount:\\)/e",
    "getPageViewCounter(\$pagename)");
Markup("pagecountday", "directives", "/\\(:pagecountday:\\)/e", 
    "getPageViewCounter(\$pagename, 'pageviewcounterdaily')");

/**
 * Reads and returns the pageview-counter of a page
 * 
 * @param type $pagename name of the page to read
 * @param type $type global or daily counter
 * @return string 
 */
function getPageViewCounter($pagename, $type = 'pageviewcounterglobal') {
	$page = ReadPage($pagename, READPAGE_CURRENT);
	return number_format($page[$type], 0, ',', '.');
}

/*
 * For the wiki form to show the current value in the input field
 */
Markup('{$PopularPagesItems}', '>{$fmt}', '/{\\$PopularPagesItems}/', $_REQUEST['items'] ? $_REQUEST['items'] : 10 );

Markup('popularpages','<split','/\\(:popularpages:\\)/e', "popularpagesfct('$pagename')");
/**
 * Shows the most visited pages
 */
function popularpagesfct($pagename) {
	global $GroupPattern, $WikiWordPattern;
	$Ignorepattern = "Recent|Blocklist|Group|PageNotFound|PITS";
	if(!$maxitems = $_REQUEST['items']) $maxitems = 10;
  	$pagelist = ListPages();
 	foreach($pagelist as $pname) {
 		if (preg_match('/^.*'.$Ignorepattern.'.*$/',$pname)) continue;
      	$page = ReadPage($pname,READPAGE_CURRENT); 
      	if (!$page) continue;
      	$counterg[] = $page['pageviewcounterglobal'];
      	$pgnameg[] = $pname;  
      	if ($page['pageviewcounterlastupdate'] == date("d")) {
      		$counterd[] = $page['pageviewcounterdaily'];
      		$pgnamed[] = $pname;  	 
 		}
    }
    arsort( $counterg);
    $pagesg = array_keys($counterg);
    foreach ($pagesg as $index => $name) $pagesg[$index] = $pgnameg[$name]; 
    $counterg = array_merge($counterg);
    
    arsort( $counterd);
    $pagesd = array_keys($counterd);
    foreach ($pagesd as $index => $name) $pagesd[$index] = $pgnamed[$name]; 
    $counterd = array_merge($counterd);
    
    $out[] = "\n||cellspacing=0 cellpadding=3";
    $out[] = "||!Gesamt|| || || ||!Heute|| || ||";
	$out[] = "|| ''Platz'' || ''Besuche'' ||''Seite'' || || ''Platz'' || ''Besuche'' ||''Seite'' ||";
    for ($i=0;$i<$maxitems;$i++) {
    		$out[] = "|| " .($i+1). " || " .$counterg[$i].  "||[[" .$pagesg[$i]. "|+]] || || ".($i+1). " || " .$counterd[$i].  "||[[" .$pagesd[$i]. "|+]] ||"; 	
    } 
    return implode("\n",$out);
}

?>