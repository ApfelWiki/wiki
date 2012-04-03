<?php
if (!defined('PmWiki'))
	exit ();

/**
 * This script allows complex votings
 * 
 * @author Patrick R. Michaud <pmichaud@pobox.com> 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 0.1
 * @link http://www.pmwiki.org/wiki/Cookbook/
 * @copyright by the respective authors 2005
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package complexvote
 */

/**
 * cookbook complexvote is included
 * and running on this pmwiki installation
 */
define('complexvote', 1);

$HandleActions['complexvote'] = 'HandleComplexVote';
$HandleAuth['complexvote'] = 'read';

SDV($ComplexVoteCookie, $CookiePrefix.'complexvote');
SDV($ComplexVoteExpires, $Now +60 * 60 * 24 * 30);
SDV($ComplexVoteDir, '/');

#Markup('showcomplexvote', '<split', '/(\(:input form.*?action=complexvote.*?input end:\))/se', "showComplexVote(PSS('$0'))");

/**
 * Belongs to Markup "showcomplexvote" and hides the vote input form if someone already voted
 * 
 * @param string $text
 */
function showComplexVote($text) {
	global $ComplexVoteCookie;
	preg_match("/\(:input hidden votename\s*(.*?)\s*?:\)/", $text, $votename);
	$cookiename = $ComplexVoteCookie.$votename[1];
	if (isset ($_COOKIE[$cookiename])) {
		return "";
	} else
		return $text;

}

/**
 * Handles the voting process
 * 
 * @param string $pagename
 */
function HandleComplexVote($pagename) {

	global $HandleActions, $ComplexVoteCookie, $ComplexVoteExpires, $ComplexVoteDir;

	if (!isset ($_REQUEST['vote']))
		Redirect($pagename);

	Lock(2);
	$page = RetrieveAuthPage($pagename, 'edit');
	if (!$page)
		Abort("?cannot edit $pagename");

	$votes = preg_split("/(\(:input form.*?action=complexvote)/", $page['text'], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	$votename = $_REQUEST['votename'];

	foreach ($votes as $key => $text) {
		if (!preg_match("/\(:input hidden votename\s*".$votename."/", $text))
			continue;
		$diagrammdivider = "%comment%ComplexVotesDiagramDivider".$votename."%%";
		$cookiename = $ComplexVoteCookie.$votename;
		if (!isset ($_COOKIE[$cookiename])) {
			setcookie($cookiename, "true", $ComplexVoteExpires, $ComplexVoteDir);
		} else {
			#Redirect($pagename);
		}
		preg_match_all("/\(:input radio vote\s*(.*?)\s*?:\)(.*?)(\n|\(:|\\\\)/", $text, $options);

		# lays out the counter after the first vote
		if (strpos($text, 'Votes:') === false) {
			$text = preg_split("/((?<=\(:input end:\))\n)/", $text,-1,PREG_SPLIT_DELIM_CAPTURE| PREG_SPLIT_NO_EMPTY);
			
			$v = "\n\n%comment%Votes:\\\\\n";
			foreach ($options[2] as $k) 
				$v .= "".trim($k).": 0\\\\\n";
			$text[0] = $text[0].$v;
			$text[2] = $text[2];
			
		} else{
			$text = explode($diagrammdivider, $text);
		}

		
			
		$where = trim($options[2][array_search($_REQUEST['vote'], $options[1])]);
		$vote = preg_replace("/(\\b".$where.":\s)(\\d*)/e", "'\\1'.addone('\${2}')", $text[0], 1);
		$text = $vote."\n".$diagrammdivider.drawdiagram($vote).$diagrammdivider."\n".$text[2];
		$votes[$key] = $text;
	}

	$_POST['text'] = implode("", $votes);
	$_POST['post'] = 1;
	$HandleActions['edit'] ($pagename);
}

/**
 * Handles the drawing of the votes
 * 
 * @param string $vote
 */
function drawdiagram($vote) {
	$out = "";
	$values = "";
	preg_match_all("/\\b.*?:\s(\\d*)/", $vote, $values);
	$sum = 100 / array_sum($values[1]);
	foreach ($values[0] as $k => $v) {
		$out .= $v."\\\\\n";
		$upbound = floor($values[1][$k] * $sum);
		for ($i = -1; $i < $upbound; $i ++)
			$out .= "|";
		$out .= "\\\\\n";
	}
	return $out;
}

/**
 * santas little helper
 * 
 * @param int $value
 */
function addone($value) {
	return $value +1;
}
?>