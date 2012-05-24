<?php
if (!defined('PmWiki'))
	exit ();
/**
 * Mark pages for deletion and collect them on a singe summary page
 *  
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 0.3.3
 * @link http://apfelwiki.de/ http://apfelwiki.de/
 * @copyright by the authors 2005-2007
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package markfordelete
 */

/*
Localization
------------
  'Week from' => 'Header on the summary page',
  'New Deletion Candidate' => 'Text in the RecentChanges for the summary page',
  'Marked for Deletion' => 'Text in the RecentChanges for the marked page',
  'Delete' => 'Text of the template snippet',

 */

define(MARKFORDELETE, "0.3.3");

SDVA($PmWikiAutoUpdate['MarkForDelete'], array(
    'version' => MARKFORDELETE, 
    'updateurl' => 'http://www.pmwiki.org/wiki/Cookbook/MarkForDelete'
));


// use term1|term2|...|termX 
SDV($MarkForDeleteExcludePattern, $KeepToken);
SDV($MarkForDeletePagename, 'Site.MarkForDelete');
SDV($MarkForDeleteForm, 'Site.MarkForDeleteForm');
// can be a pagename for redirection or html formated string
SDV($MarkForDeleteAllreadyOnPage, $MarkForDeletePagename);
// can be a pagename which is included or wiki markup formated string
// I use a little sticky from my the postitnotes cookbook here
SDV($MarkForDeletePutOnPage, '----
%red center%This page was marked for deletion by [[~$Author]] on $now.%% [['.$MarkForDeletePagename.'#$FullName|See here...]]
----');
PageExists($MarkForDeletePutOnPage);
SDV($MarkForDeleteLinkFmt, '<a href=\'$PageUrl?action=markfordelete\'>$[Delete]</a>');

SDV($HandleActions['markfordelete'], 'HandleMarkForDelete');
SDV($HandleAuth['markfordelete'], 'read');

$FmtPV['$PageToDelete'] = "'{$_REQUEST['pagetodelete']}'";

/**
 * Controler for markfordelete action
 * 
 * @param string $pagename
 * @param string $auth
 */
function HandleMarkForDelete($pagename, $auth = "read") {
	global $MarkForDeletePagename, $MarkForDeleteForm, $MarkForDeleteAllreadyOnPage;
	
	$page = RetrieveAuthPage($MarkForDeletePagename, "read");
	
	if (preg_match("/\[\[".$_REQUEST['pagetodelete']."\]\]/", $page['text'])) {
		if (PageExists($MarkForDeleteAllreadyOnPage))
			Redirect($MarkForDeleteAllreadyOnPage, '$PageUrl'."?pagetoaadelete=$pagename");
		else {
			global $PageStartFmt, $PageEndFmt;
			SDV($HandleMarkForDelete, array (& $PageStartFmt, utf8_encode($MarkForDeleteAllreadyOnPage), & $PageEndFmt));
			PrintFmt($pagename, $HandleMarkForDelete);
			exit;
		}
	} else
		if (isset ($_REQUEST['perform']))
			MarkForDelete($pagename, $auth);
		else
			Redirect($MarkForDeleteForm, '$PageUrl'."?pagetodelete=$pagename");
}

/**
 * Writes the summary page and calls the function to put a note on the marked page.
 * 
 * @param string $pagename
 * @param string $auth
 */
function MarkForDelete($pagename, $auth) {
	global $HandleActions, $ChangeSummary, $Now, $Author, $FmtV;
	global $MarkForDeletePagename, $MarkForDeleteForm, $EditFunctions;

  $EditFunctions = array_diff($EditFunctions, array('RequireCaptcha') );

	if (!PageExists($MarkForDeletePagename))
		Abort("Summary page for as marked for delete pages \"$MarkForDeletePagename\" not found.<br /><br />
					Generate the page \"$MarkForDeletePagename\" or use your own with ".'$MarkForDeletePagename=your.page'."
					in your config.php. ");

	$pagetodelete = $_REQUEST['pagetodelete'];
	$shortexplain = stripmagic($_REQUEST['shortexplain']);
	$now = strftime($GLOBALS["TimeFmt"], $Now);
	
	$page = RetrieveAuthPage($MarkForDeletePagename, "read");
	
	MarkForDeleteWriteNote($pagetodelete, $Author);

	$weekstring = FmtPageName("\$[Week from]",$MarkForDeleteForm);
	$europeanstartofweek = (date('w') == 0) ? 7 : date('w');
	$startofcurrentweek = date('d.m.Y', mktime(0, 0, 0, date('n'), ((date('j')) - $europeanstartofweek) + 1, date('Y')));
	$endcurrentweek = date('d.m.Y', mktime(0, 0, 0, date('n'), ((date('j')) - $europeanstartofweek + 7), date('Y'))); 
	$entry = "\n* [[$pagetodelete]]\n** $shortexplain - [[~$Author]] $now";

	$text = preg_split("/(?=!!\\s*$weekstring)/", $page['text']);
	preg_match("/$weekstring (\d\d\.\d\d\.\d\d\d\d)/", $text[1], $match);
	if ($match[1] == $startofcurrentweek)
		$text[1] = $text[1].$entry."\n";
	else
		$text[1] = "!!$weekstring $startofcurrentweek - $endcurrentweek $entry \n\n".$text[1];

	$pagetext = implode("", $text);
	$_POST['text'] = get_magic_quotes_gpc() ? addslashes($pagetext) : $pagetext;
	$ChangeSummary = FmtPageName("\$[New Deletion Candidate]: $pagetodelete", $pagename);
	$_POST['post'] = 1;
	$HandleActions['edit'] ($MarkForDeletePagename, $auth);

}

/**
 * Puts a note on the top of a wikipage that it is marked for deletion
 * 
 * @param string $pagetodelete the pagename of the the wikipage
 * @param string $Author the author name who marked the page for delete
 */
function MarkForDeleteWriteNote($pagetodelete, $author) {
	global $EditFunctions, $FmtV, $ChangeSummary, $EnablePost, $Now;
	global $MarkForDeletePutOnPage;
	Lock(2);
	if (!PageExists($pagetodelete))
		Abort("To deleted page \"$pagetodelete\" not found.");
	$page = RetrieveAuthPage($pagetodelete, "read");
	if (PageExists(MakePageName($pagename, $MarkForDeletePutOnPage)))
		$MarkForDeletePutOnPage = "(:include $MarkForDeletePutOnPage:)";
	$new = $page;
	// for the RecentChangesFmt
	$ChangeSummary = FmtPageName("$[Marked for Deletion]",$pagetodelete);
	// for the diff history
	$new["csum:$Now"] = $ChangeSummary;
	$FmtV['$now'] = strftime($GLOBALS["TimeFmt"], $Now);
	$new['text'] = FmtPageName("$MarkForDeletePutOnPage",$pagetodelete)."\n\n".$new['text'];
 	$EnablePost = 1;
 	foreach ($EditFunctions as $function)
                $function ($pagetodelete, $page, $new);
	Lock(0);
	return;
}

/**
 * When <!--function:MarkForDeleteLink--> is put in the skin tmpl file
 * it returns an edit link if 
 * <ul>
 * <li> the current page is not allready on the delete summary page</li>
 * <li> the current page is not in the $MarkForDeleteExcludePattern </li>
 * </ul>
 * 
 * @param string $pagetodelete the pagename of the the wikipage
 * @param string $Author the author name who marked the page for delete
 * @param string $now the human time when the page was marked in human readable format
 */
function MarkForDeleteLink() {
	global $pagename, $MarkForDeleteExcludePattern, $MarkForDeletePagename, $MarkForDeleteLinkFmt;
	if (isset($MarkForDeletePagename))
		$page = RetrieveAuthPage($MarkForDeletePagename, "read", false, READPAGE_CURRENT);
	if (!preg_match("/$MarkForDeleteExcludePattern/",$pagename) && !preg_match("/\[\[$pagename\]\]/", $page['text']))
		echo FmtPageName($MarkForDeleteLinkFmt, $pagename);
}
?>