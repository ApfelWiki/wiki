<?php
if (!defined('PmWiki'))
	exit ();

/**
 * This script generates different types of little yellow note stickies.
 * 
 * @author Patrick R. Michaud <pmichaud@pobox.com> 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 0.4.4
 * @link http://www.pmwiki.org/wiki/Cookbook/AttachlistEnhanced  this cookbook on pmwiki.org
 * @copyright by the respective authors 2004-2005
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package postitnotes
 * @todo better layout engine
 */

/*
For Developers
==============
Version History
---------------
* 0.4 - 2005-11-11 - Schlaefer
** [change] much improved code base (index system)  
*/

SDV($HTMLStylesFmt['ALE'], "
table.ALEtable img
{	width:100px;
	
}
table.ALEtable
{	border:1px silver solid;
	padding: 5px;
	width: 100%;border-collapse: collapse;
}
table.ALEtable td
{	border-top: 1px silver solid;
	
}
");

$ALEStartFmt ='<table class=\'ALEtable\'>';
$ALEEndFmt = '<tr><td><br />$ALEFoundItems items found.</td><td></td><td></td></tr></table>';
$ALEItemOrphanedFmt = '<tr><td><a href=\'$ALEItemUploadUrl$ALEItemFileName\'>$ALEItemGroup$ALEItemFileName</a>' .
		'</td><td>$ALEItemImage' .
		'</td><td></td></tr>';
	
Markup('attachmentsmissingororphaned', 'directives', '/\\(:attachlist\\s*(find.*?)\\s*:\\)/e', "Keep(attachmentsmissingororphanedfct('$pagename',PSS('$1')))");

function attachmentsmissingororphanedfct($pagename, $args) {
	global $GroupPattern, $NamePattern, $RecentUploads, $RecentChangesFmt, $UploadPrefixFmt;
	global $ALEStartFmt,$ALEItemOrphanedFmt,$ALEEndFmt,$FmtV;
	global $UrlExcludeChars,$ImgExtPattern,$UploadExts;

	$opt['ImagesAutoResizing'] = 1;
	$opt = ParseArgs($args, $opt);
	$status = $opt['find'];
	if ($opt['ImagesAutoResizing'] == 0)
		$ALEFileIgnorePattern = ".*\.[ps]\.\w{3}";

	#Checks if RecentUploads is defined, if not, use the PmWiki 1 default Values
	if (!isset ($RecentUploads))
		$RecentUploads = array (
			'Main.AllRecentUploads' => "[[$UploadUrlFmt$UploadPrefixFmt\$UploadName \$UploadName]]", 
			'$Group.RecentUploads' => '[[Attach:$UploadName $UploadName]]'
		);
	$p = array_merge($RecentUploads, $RecentChangesFmt);

	#get pagenames as exclude pattern
	foreach ($p as $kp1 => $p1)
		$PageExclude[] = substr(stristr($kp1, "."), 1);
	$PageExcludePattern = implode("|", $PageExclude);
	$PageExcludePattern .= "|PmWiki";

	$UplExtPattern = "(?:.".implode("|.",array_keys($UploadExts)).")";
	# we take care for attachments without an extension
	$UplExtPattern = preg_replace("/\\|\\.(\\W)/","|[\w]\${1}",$UplExtPattern);

	$pagelist = ListPages(NULL);
	$grouplist = array ();
	$attachlist['index'] = array ();

	foreach ($pagelist as $page) {
		if (preg_match("/($PageExcludePattern)/", $page))	continue;
		if (preg_match("/^($GroupPattern)[\\/.]($NamePattern)$/", $page, $foundgroup))
			$grouplist[$foundgroup[1]] = $foundgroup[1];

		$rcpage = ReadPage($page);
		# ignore outcommented Attachs
	 	$rcpage['text'] = preg_replace("/\\[[@=].*?[@=]\\]/s","",$rcpage['text']);
		if (preg_match_all("/\\b(?>(Attach:))([^\\s$UrlExcludeChars]+($ImgExtPattern|$UplExtPattern))/", $rcpage['text'], $pageattachments, PREG_PATTERN_ORDER)) {
			foreach ($pageattachments[2] as $file) {
				$strippednames = ALEh1fct($file,$page);
				$index = FmtPageName("$UploadUrlFmt$UploadPrefixFmt/".$strippednames['image'], $strippednames['page']);
				$attachlist['index'][$index] = $index;
				$attachlist['file'][$index] = $file;
				$attachlist['pagename'][$index][$page] = $page; 
			}
		}
	}

	$filelist = array ();
	$filelist = ALEReadDirectories("/", $ALEFileIgnorePattern);

	$out = array ();
	if ($status === "orphaned") {
		$attachments = array_diff($filelist['index'], $attachlist['index']);
		if (count($attachments)) {
			natcasesort($attachments);
			foreach ($attachments as $missing) {
				if (preg_match("/$ImgExtPattern$/",$filelist['file'][$missing])) {
					#TODO use 'index' here?
					$lktxt = "Attach:".$filelist['group'][$missing].$filelist['file'][$missing];
					$FmtV['$ALEItemImage'] = MakeLink($pagename,$lktxt,$lktxt);
				} else 
					$FmtV['$ALEItemImage'] = "";
				
				$FmtV['$ALEItemUploadUrl'] = $filelist['uploadurl'][$missing];
				$FmtV['$ALEItemFileName'] = $filelist['file'][$missing];
				$FmtV['$ALEItemGroup'] = $filelist['group'][$missing];
				$ALEItemsFmt[] = FmtPageName($ALEItemOrphanedFmt,$pagename);	
			}
	
		} else
			$ALEItemsFmt[] = "<tr><td>No orphaned attachments found.</td></tr>";
	}

	elseif ($status === "missing"){
   		$attachments = array();
    		foreach($attachlist['index'] as $index)
    			if(!in_array($index,$filelist['index']))
    				$attachments[] = $index;
   		if (count($attachments)){
   			natcasesort($attachments);
   			foreach($attachments as $index){
				$ALEItemsFmt[]="<tr valign='top'><td>".$index. "</td><td>in</td><td>";
				foreach($attachlist['pagename'][$index] as $page)
					$ALEItemsFmt[] = MakeLink($pagename,$page,$page)."<br />";
				$ALEItemsFmt[] = "</td></tr>";
			}
		}
		else
			$ALEItemsFmt[]= "<tr><td>No missing attachments found.</td></tr>";
   	}  	

	$FmtV['$ALEFoundItems'] = count($attachments);
	
	$ALEFmt = array($ALEStartFmt,implode("",$ALEItemsFmt),$ALEEndFmt);
	return Keep(FmtPageName(implode("",$ALEFmt),$pagename));
}

/**
 * reads all directories content recursive
 * 
 * @param string $dir the root directory name
 * @param string $ALEFileIgnorePattern RegEx string of ignored file names "ignore|ignore|ignore" etc
 * @return array found files with index, uploadurl, group, file
 */
function ALEReadDirectories($dir, $ALEFileIgnorePattern = "") {
	global $UploadDir, $UploadUrlFmt;
	$uploaddir = FmtPageName("$UploadDir$dir", $pagename);
	$uploadurl = FmtPageName("$UploadUrlFmt$dir", $pagename);
	$dirp = opendir($uploaddir);
	if (!$dirp)
		return "";
	$directoriesfound = array ();
	while ($file = readdir($dirp)) {
		if (substr($file, 0, 1) == '.')
			continue;
		if ($ALEFileIgnorePattern)
			if (preg_match("/".$ALEFileIgnorePattern."/", $file))
				continue;
		if (is_dir($uploaddir."/".$file)) {
			$directoriesfound[] = $file;
			continue;
		}
		$index = $dir.$file;
		$filelist['index'][$index] = $index;
		$filelist['uploadurl'][$index] = $uploadurl;
		$filelist['group'][$index] = $dir;
		$filelist['file'][$index] = $file;	
	}
	closedir($dirp);
	foreach ($directoriesfound as $actdir) {
		$dirrec = $dir.$actdir."/";
		$filelist = array_merge_recursive($filelist, ALEReadDirectories($dirrec, $ALEFileIgnorePattern));
	}
	return $filelist;
}

/**
 * Helps to find the image filepath depending on Attach syntax and $UploadPrefixFmt
 * 
 * Keep in sync with imgpopup!
 * 
 * @param string $imagename the image name only "$UploadPrefixFmt/image.ext" string
 * @param string $pagename
 * @return array array with 'image' as the new $imagename and 'page' as the new $pagename 
 * @version v1.3
 */

function ALEh1fct($imagename, $pagename) {
	global $UploadPrefixFmt;
	$pgn = explode(".", $pagename);
	$upl = preg_split("/(\/|\.(?=.*\/))/", $imagename);
	if (isset ($upl[1])) {
		if ($UploadPrefixFmt == '/$Group')
			$pagename = $upl[0].".Upload";
		else {
			$pagename = $pgn[0].".".$upl[0];
		}
		$imagename = $upl[1];
	}
	if (isset ($upl[2])) {
		$pagename = $upl[0].".".$upl[1];
		$imagename = $upl[2];
	}
	return array ('image' => $imagename, 'page' => $pagename);
}
?>