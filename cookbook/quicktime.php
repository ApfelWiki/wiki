<?php
if (!defined('PmWiki'))
	exit ();

/**
 * This script allows to embed quicktime content. 
 * 
 * @author Patrick R. Michaud <pmichaud@pobox.com> 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 0.2.1
 * @link http://www.pmwiki.org/wiki/Cookbook/QuickTime http://www.pmwiki.org/wiki/Cookbook/QuickTime
 * @copyright by the respective authors 2004-2005
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package quicktime
 */

/*
For Developers
==============
Quicktime Embeded: http://www.apple.com/quicktime/tutorials/embed.html

Version History
---------------
* 0.2 - 2006-01-26 - Schlaefer
** [feature] html Links
** [change] no use of IMapLinkFmt
* 0.1 - initial release - Schlaefer
*/

define(QUICKTIME, '0.2.1');

# see http://schlaefer.macbay.de/index.php/PmWikiCookbook/AutoUpdate
SDVA ($PmWikiAutoUpdate['QuickTime'] , array(
    'version' => QUICKTIME, 
    'updateurl' =>  "http://pmwiki.org/wiki/Cookbook/QuickTime"
));


SDV($QuickTimeExternalResource, 1);

Markup('quicktime', '<img', "/\\(:quicktime (.*?:)(.*?)(\\s.*?)?\\s*?:\\)/e", "Keep(Quicktime('$1','$2','$3','$pagename'))");
/**
 * Belongs to Markup "quicktime" 
 * 
 * @param string $imap "Attach:"
 * @param string $path 
 * @param string $args for video a height an width parameter is mandantory
 */
function Quicktime($imap, $path, $args, $pagename) {
	global $UploadFileFmt, $QuickTimeExternalResource;

	if ($imap == "Attach:") {
		$filepath = FmtPageName("$UploadFileFmt/".$path, $pagename);
		$FileUrl = LinkUpload($pagename, $imap, $path, $imap.$path, $imap.$path, "\$LinkUrl");
		if (!file_exists($filepath))
			return $FileUrl;
	} elseif ($imap == "http:"){
		$FileUrl = $imap.$path;
		if  ($QuickTimeExternalResource == false)
			return $FileUrl;
	}
	
	if ($FileUrl) {
		$args = ParseArgs($args);
		
		## object tag
		$out = "<object id=\"id6\" classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\" 
		codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\"";
		if (isset($args['width']))
			$out .= " width=\"".$args['width']."\"";
		if (isset($args['height']))
			$out .= " height=\"".$args['height']."\"";	
		$out .= ">";
		$out .= "<param name=\"src\" value=\"$FileUrl\" />";
		foreach ($args as $key => $arg)
			if ($key != '#')
				$out .= "<param name=\"$key\" value=\"$arg\" />";
		
		## embed tag
		$out .= "<embed src=\"$FileUrl\"";
		foreach ($args as $key => $arg)
			if ($key != '#')
				$out .= " $key=\"$arg\"";
		$out .= " type=\"video/quicktime\" class=\"mov\" pluginspage=\"http://www.apple.com/quicktime/download/\"></embed>";
		
		$out .= "</object>";
		
		return $out;
	}
}

$LinkFunctions['Quicktime:'] = 'LinkUploadQuicktime';
/**
 * Allows Quicktime:attachment.ext. Only experimental.
 * <br>
 * See LinkUpload() for parameter details  
 */
function LinkUploadQuicktime($pagename, $imap, $path, $title, $txt, $fmt = NULL) {
	global $UploadFileFmt, $pagename, $PageUrl, $PubDirUrl, $IMapLinkFmt;
	$filepath = FmtPageName("$UploadFileFmt/".$path, $pagename);
	$IMapLinkFmtTemp = $IMapLinkFmt['Attach:'];
	if (file_exists($filepath))
		$IMapLinkFmt['Attach:'] = "\$LinkUrl";
	$out = LinkUpload($pagename, "Attach:", $path, $title, $txt);
	$IMapLinkFmtTemp['Attach:'] = $IMapLinkFmtTemp;
	if (file_exists($filepath))
		$out = "<embed src=\"$out\" autoplay=true controller=true loop=false pluginpage=\"http://www.apple.com/quicktime/download\">";
	return Keep($out);
}
?>