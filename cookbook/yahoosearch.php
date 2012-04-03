<?php

/**
 * Yahoo search box and results
 *  
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 0.1.13
 * @link http://apfelwiki.de http://apfelwiki.de
 * @copyright by the authors 2005-2006
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package yahoosearch
 */ 
 
/*
Admins
======
Localization
------------  
 'of' => '',
 'Next' => '',
 'Previous' => ''   

 */

define(YAHOOSEARCH, '0.1.12');

SDVA($PmWikiAutoUpdate['YahooSearch'], array(
    'version' => YAHOOSEARCH, 
    'updateurl' => 'ApfelWiki only at the moment'
));

SDV($YahooSearchResultNumber, 10);
SDV($YahooSearchAPPID, "");
SDV($YahooSearchLocalSiteURI, $_SERVER['HTTP_HOST']);
SDV($YahooSearchNoSearchResultsFmt, 'No search results for: '.$_REQUEST['q']);
SDV($YahooSearchNoSearchTermFmt, 'No search term.');

Markup('yahoosearchbox', '<yahoosearchresults', '/\\(:yahoosearchbox:\\)/e', "Keep(YahooSearchBox(\$pagename))");

Markup('yahoosearchresults', 'directives', '/\\(:yahoosearchresults(.*?)?:\\)/e', "Keep(YahooSearchResults('$pagename', PSS('$1')))");

function YahooSearchBox($pagename) {
	$out = FmtPageName("<form action='\$PageUrl' method='post'>
			<input type='text' name='q' size='40' value='".stripmagic($_REQUEST['q'])."'/>
			<input type='submit' value='$[Search]' />"."<input type='hidden' name='type' value='web' />
			</form>	", $pagename);
	return $out;
}

function YahooSearchResults($pagename, $searchstring) {
	global $YahooSearchResultNumber, $YahooSearchNoSearchResultsFmt, $YahooSearchNoSearchTermFmt;

	if ($searchstring) $_REQUEST['q'] = $searchstring;

	if (!$_REQUEST['q'])
		return Keep("<hr /><br />$YahooSearchNoSearchTermFmt<br /><br /><hr />");
		
	$q = YahooSearchBuildQuery();
	#$xml = @file_get_contents("http://api.search.yahoo.com/WebSearchService/V1/webSearch".$q);
	
	if ($xml = @file_get_contents("http://api.search.yahoo.com/WebSearchService/V1/webSearch".$q)) {
	    $searchresults = new XMLObject;
	    $searchresults->setXMLString($xml);
		$searchresults->XMLTree();
		
		$ResultSet = $searchresults->allNodesWithChildsAsArray('ResultSet', 'args');
		if (isset($ResultSet['totalResultsReturned'])) {
			$sr['Title'] = $searchresults->allNodesWithChildsAsArray('Title');
			$sr['Summary'] = $searchresults->allNodesWithChildsAsArray('Summary');
			$sr['Url'] = $searchresults->allNodesWithChildsAsArray('Url');
			
			$bis = ($ResultSet['totalResultsReturned'] < $YahooSearchResultNumber) ? $ResultSet['totalResultsReturned'] : $YahooSearchResultNumber;
			$bis = $ResultSet['firstResultPosition'] + $bis - 1;
			
			//upper navigation
			$out .= "<span style='font-size:smaller; float:right;'>".$ResultSet['firstResultPosition']." - $bis $[of] ".$ResultSet['totalResultsAvailable']."</span>";
			$out .= $navigation = YahooSearchNextPrevNavigation($ResultSet['totalResultsAvailable'], $ResultSet['firstResultPosition'], $ResultSet['totalResultsReturned']);
			$out .= "<br /><hr />";
			
			//simple search highlight
			$searchterms = preg_replace("/\\s+/", "|", str_replace("/"," ",trim($_REQUEST['q'])));
			
			foreach ($sr['Title'] as $k => $t) {
				$sr['Summary'][$k] = preg_replace("/$searchterms/i", "<strong>\\0</strong>", $sr['Summary'][$k]);
				$out .= "<a style='font-weight:bold;' href='".$sr['Url'][$k]."'>".str_replace("apfelwiki.de -", "", $sr['Title'][$k])."</a><br/><div style='padding:0px 20px;'>".$sr['Summary'][$k].""."</div><br />";
			}
			
			//bottom navigation
			$out .= "<hr /><span style='font-size:smaller;float:right;'><a href='http://developer.yahoo.net/search/index.html'>Powered by Yahoo</a></span>";
			$out .= $navigation;

			
		} else
			$out = "<hr /><br />$YahooSearchNoSearchResultsFmt<br /><br /><hr />";
		
		return Keep(FmtPageName($out,$pagename));	
	} else {
		header("Location: http://www.google.de/custom?q=".rawurlencode($_REQUEST['q'])."&domains=apfelwiki.de&sitesearch=apfelwiki.de");
		exit;
	}
}

function YahooSearchBuildQuery() {
	global $YahooSearchResultNumber, $YahooSearchAPPID, $YahooSearchLocalSiteURI;
	if (empty ($_REQUEST['q']))
		Abort('Error in build_query() in cookbook "yahoosearch". No search term found.');
	if (empty ($YahooSearchAPPID))
		Abort('Error in build_query() in cookbook "yahoosearch". No yahoo appid specified. Set it in your config file with the "$YahooSearchAPPID" variable.');
	$q = '?query='.rawurlencode($_REQUEST['q']);
	if (!empty ($_REQUEST['start']))
		$q .= "&start=".$_REQUEST['start'];
	$q .= "&results=$YahooSearchResultNumber";
	$q .= "&site=$YahooSearchLocalSiteURI";
	$q .= "&appid=$YahooSearchAPPID";
	return $q;
}

/**
 * Returns the navigation
 * 
 * @param string $totalResultsAvailable number of found items
 * @param string $start First result currently displayed
 * @param string last Last result currently displayed
 */
function YahooSearchNextPrevNavigation($totalResultsAvailable, $start, $last) {
	global $YahooSearchResultNumber;
	if ($start > 1)
		$out .= '<a href="'.$_SERVER['PHP_SELF'].'?q='.rawurlencode($_REQUEST['q']).'&amp;start='. ($start - $YahooSearchResultNumber).'">&lt;&lt;&lt; $[Previous]</a>&nbsp;|&nbsp;';
	if ($last < $totalResultsAvailable)
		$out .= '<a href="'.$_SERVER['PHP_SELF'].'?q='.rawurlencode($_REQUEST['q']).'&amp;start='. ($last +1).'">$[Next] &gt;&gt;&gt;</a>';
	return $out;
}

/**
 * Simple XML parser to make this cookbook php version independent. 
 */
class XMLObject {

	var $xmlstring;
	var $xmltree;

	/** 
    * Class constructor.
    */
	function XMLObject() {
		$this->xmlstring = "";
		$this->xmltree = array ();
	}

	function setXMLString($axmlstring) {
		if (!preg_match("/(<\\?xml.*?\\?>)(.*)/s", $axmlstring, $match))
			return 0;
		else {
			$this->xmlstring = $match[2];
			return 1;
		}
	}

	function XMLTreeAsHTML() {
		$this->XMLTree();
		return $this->generatexmltreeashtml($this->xmltree);
	}

	function allNodesWithChildsAsArray($nodename, $voa = "value") {
		$output = array ();
		return $xml = $this->allnodeswithchilds($this->xmltree, $nodename, $output, $voa);
	}

	function allnodeswithchilds($xml, $nodename, $output, $voa = "value") {
		if (!is_array($xml)) return $output;
		foreach ($xml as $k => $element) {
			if ($k === $nodename) {
				$output = array_merge_recursive($output, $element[$voa]);
			}
			elseif (is_array($element)) {
				$output = $this->allnodeswithchilds($element, $nodename, $output, $voa);
			}
		}
		return $output;
	}

	function XMLTree() {
		$this->xmltree = $this->generatexmltree($this->xmlstring);
	}

	function generatexmltree($xml, $sort='node') {
		preg_match_all("/(<([\w]+)([^>]*)>)(.*?)(<\/\\2>)/s", $xml, $matches, PREG_PATTERN_ORDER);
		foreach ($matches[4] as $key => $inner)
			if (strstr($inner, "<")) {
				if ($sort == 'element')
					$output[$matches[2][$key]]['value'] = $this->generatexmltree($inner); 
				elseif ($sort == 'node')
					$output[$matches[2][$key]]['value'] = array_merge_recursive($output[$matches[2][$key]]['value'], $this->generatexmltree($inner));
				
				preg_match_all("/\\s([\w:]+)=\"(.*?)\"/s", $matches[3][$key], $args, PREG_PATTERN_ORDER);
				$output[$matches[2][$key]]['args'] = $this->array_combine_emulated($args[1], $args[2]);

			} else {
				if ($sort == 'element'){
					$output[$matches[2][$key]]['args'][$key] = $matches[3][$key];
					$output[$matches[2][$key]]['value'][$key] = $inner;
				} elseif ($sort == 'node') {
					$output[$matches[2][$key]]['args'][] = $matches[3][$key];
					$output[$matches[2][$key]]['value'][] = $inner;
				}
			}
		return $output;
	}

	function generatexmltreeashtml($xml) {
		$out = "<ul>";
		foreach ($xml as $k => $element) {
			if (is_array($element))
				$element = $this->generatexmltreeashtml($element);
			$out .= "<li>".$k." => ".$element."</li>";
		}
		$out .= "</ul>";
		return $out;
	}

	function array_combine_emulated($keys, $vals) {
		$keys = array_values((array) $keys);
		$vals = array_values((array) $vals);
		$n = max(count($keys), count($vals));
		$r = array ();
		for ($i = 0; $i < $n; $i ++) {
			$r[$keys[$i]] = $vals[$i];
		}
		return $r;
	}

	/** 
	 * Show the results as simple html tree. Use: 
	 * $test = new XMLObject();
	 * $test->Test();
	 */
	function Test() {
		/*$xml = "<?xml testme?><head><upper>test1</upper><upper>test2</upper></head> ";*/
		$_REQUEST['q'] = "rosetta";
		$q = YahooSearchBuildQuery();
		#echo 'http://api.search.yahoo.com/WebSearchService/V1/webSearch'.$q;
		$xml = file_get_contents('http://api.search.yahoo.com/WebSearchService/V1/webSearch'.$q);
		$this->setXMLString($xml);
		echo $this->XMLTreeAsHTML(); 
		exit;
	}
}

?>