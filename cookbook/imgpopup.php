<?php
/**
 * Scales images down and open them im new popup window. 
 * 
 * <br><br>
 * This script makes
 * <ul>
 * <li>every image a link to a popup window with upload opporunity</li>
 * <li>scales the view of every picture wider than $ImgPopUpMaxImgWidth (default 200 pixel)</li> 
 * </ul>
 *  
 * @author Patrick R. Michaud <pmichaud@pobox.com> 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @autor Laurent Meister <meister@apfelwiki.de>
 * @version 1.2.8
 * @link http://www.pmwiki.org/wiki/Cookbook/ImgPopUp http://www.pmwiki.org/wiki/Cookbook/ImgPopUp
 * @copyright by the authors 2005, 2009
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package imgpopup
 */



/* **************************************************************************
File: imgpopup.php

For the Admins
==============
Requirements
------------
* Last testet on PmWiki 2b55

Install
-------
1. put this script into your cookbook folder
2. include it into your config.php: 
    include_once("$FarmD/cookbook/imgpopup.php");
3. optionaly set the variable $ImgPopUpMaxImgWidth (in pixel) before the include. For example use 
    $ImgPopUpMaxImgWidth = 150;
    to scale all images to 150px width
4. If you want to use a small magnifier glas before link text put an magnifier.png into /pub/cookbook/imgpopup

Customization
-------------
### Variables
* [optional] $ImgPopUpMaxImgWidth = <valueinpixel>; 
** default = 200
** sets the max. image width before scaling to 200 pixel

* [optional] $ImgPopUpSkinDirUrl = "<directory>"; 
** default = "cookbook/imgpopup" 
** sets the directory for the magnifier image to "/pub/cookbook/imgpopup"

* [optional] $ImgPopUpProvideUploadLink = <true|false>
** default = true 
** sets if a upload link should be provided in the popup

* [optional] $ImgPopUpNoZoomLink = <true|false>
** default = false 
** sets if a Zoom Image link is shown under the image

* [optional] $ImgPopUpIgnoreUnscaledImages = <true|false>
** default = false 
** sets if unscaled images also open in a popup

* [optional] $ImgPopUpSameWindow = <true|false>
** default = false 
** sets if the image is shown in a new (popup) or in the same window 

* [optional] $ImgPopUpShowZoomLinkTreshold = float
** default = 1.0
** sets an treshold for showing the zoom link. Set it to 1.1 means a image will get a zoom link if it is $ImgPopUpMaxImgWidth * 1.1 pixels wide. This prevents images with only a few pixels wider than $ImgPopUpMaxImgWidth to get displayed as zoomable

### XLPage Strings
 'Zoom Image' => '',
 'Upload this image again' => '',
 'Close window' => '',
 'Go back' => '',

### Misc
If you do not like the link text under the image you can set display:none; in .imgpopuptext{}.

For the Users
=============
Just use the plain Attach:image.ext syntax.

### Markup
* (:ImgPopUpNoZoomLink:) 
** images on this page don't get a zoom link ($ImgPopUpNoZoomLink = true;)

* (:ImgPopUpDisabled:)
** disables ImgPopUp for this wiki page ($ImgPopUpEnabled = false;)

For Developers
==============

Version History
---------------
*1.2.9 
** [bugfix] solved problem regarding the reupload of pictures.
* 1.2.8
** [bugfix] deleted unnessesary <br>
* 1.2.7 - 2005-10-02 - Schlaefer
** [bugfix] GET divider is &amp; not ?
* 1.2.6 - 2005-09-05 - Schlaefer
** [feature] (:ImgPopUpDisabled:) disables ImgPopUp for a side
** [bugfix] "Go back" string in popup if $ImgPopUpSameWindow = 1; (contributed by noskule)
* 1.2.5 - 2005-09-03 - Schlaefer
** [feature] $ImgPopUpShowZoomLinkTreshold
** [bugfix] simple tables no longer corrupted
* 1.2.4 - 2005-09-02 - Schlaefer
** [bugfix] "Close window" link didn't respect $ImgPopUpSameWindow (reported by noskule)
* 1.2.3 - 2005-09-01 - Schlaefer
** [bugfix] caption pipe for non scaled images was shown (reported by noskule)
** [feature] new parameters $ImgPopUpIgnoreUnscaledImages, $ImgPopUpSameWindow (suggested by noskule)
* 1.2.2 - 2005-09-01 - Schlaefer
** [bugfix] respecting "|" as image capture mark (reported by noskule) 
** [feature] (:ImgPopUpNoZoomLink:) and $ImgPopUpNoZoomLink introduced (suggested by noskule)
* 1.2.1 - - Schlaefer
** [feature] link with magnifier under the scaled down image
** [feature] make upload link in popup window opitonal
** [code] phpdoc commenting 
* 1.2 - 2005-08-25 - Schlaefer
** [bugfix] better support for $UploadPrefixFmt = /$FullName 
** [feature] click on zoomed image closes window if javascript enabled
* 1.1 - 2005-08-24 - Schlaefer
** [bugfix] not uploaded files gives no error anymore
** [bugfix] simple support for attachments from other groups
* 1.0 - 2005-08-24 - Schlaefer
** initial release

****************************************************************************/
if (!defined('PmWiki'))
	exit ();

/**
 * cookbook imgpopup is included
 * and running on this pmwiki installation
 */
define('imgpopup', 1);

if ($action != 'imgpopup')
	Markup('imgpopup', '<img', "/(\\b(?>(\\L))([^\\s$UrlExcludeChars]+$ImgExtPattern)(\"([^\"]*)\")?(\\s*\\|(?=[^|]))?)/e", "ImgPopUpFct('$0','$3')");
Markup('imgpopupparam', '<imgpopup', "/\(:ImgPopUp(\\w*?)\\s*:\)/e", "ImgPopUpParamFct('$1')");

$HandleActions['imgpopup'] = 'HandleImgPopUp';
$HandleAuth['imgpopup'] = 'read';

SDV($ImgPopUpEnabled,true);
SDV($ImgPopUpProvideUploadLink,true);
SDV($ImgPopUpShowZoomLinkTreshold, 1.0);
SDV($ImgPopUpIgnoreUnscaledImages,false);
SDV($ImgPopUpSameWindow,false);
SDV($ImgPopUpNoZoomLink, false);
SDV($ImgPopUpSkinDirUrl,"cookbook/imgpopup");
SDV($ImgPopUpMaxImgWidth, 200);
SDV($HTMLStylesFmt['imgpopup'] , "
  .imgpopup img {border:0px #dddddd solid;width:".$ImgPopUpMaxImgWidth."px; }
  .imgpopuptext {text-decoration: none;font-size:smaller; padding-left:5px;}
  .imgpopupimg img { border:1px solid #cccccc; padding:4px; background-color:#f9f9f9;}
  
");

/** 
* Evaluates possible parameters set on the particular page for ImgPopUpFct()
*
* @param string $args arguments
* @since v1.2.2, 2005-09-01
*/
function ImgPopUpParamFct($args){
	global $ImgPopUpNoZoomLink, $ImgPopUpEnabled;
	if ($args == "NoZoomLink")
		$ImgPopUpNoZoomLink = true;
	elseif ($args == "Disabled")
		$ImgPopUpEnabled = false;
	return;
}

/**
 * Scales down the images and link them to the popup window.
 * 
 * @param string $wholestring the whole "Attach:$UploadPrefixFmt/image.ext" string
 * @param string $imagename the image name only "$UploadPrefixFmt/image.ext" string
 * @return string if succesfull it returns $wholestring but surrounded by scaling an link html/css syntax
 */
function ImgPopUpFct($wholestring, $imagename) {
	global $UploadFileFmt, $pagename, $PageUrl, $ImgPopUpMaxImgWidth, $ImgPopUpSkinDirUrl, $pagename, 
		$PubDirUrl, $ImgPopUpIgnoreUnscaledImages, $ImgPopUpNoZoomLink,$ImgPopUpSameWindow,
		$ImgPopUpEnabled, $ImgPopUpShowZoomLinkTreshold;

	$names = imgpopuph1fct($imagename, $pagename);

	$filepath = FmtPageName("$UploadFileFmt/".$names['image'], $names['page']);
	
	$magnifierpath =  "$PubDirUrl/$ImgPopUpSkinDirUrl/magnifier.png";
	$magnifierhtml = "";
	if (file_exists(FmtPageName("pub/$ImgPopUpSkinDirUrl/magnifier.png",$pagename)))
		$magnifierhtml = "<img src='$magnifierpath' style='margin:0px;' />";
	
	if ($ImgPopUpNoZoomLink)
		$showzoomlink = "display:none";
	if (!$ImgPopUpSameWindow)
		$openpopupin = "target='_blank'";	

	if (!file_exists($filepath))
		return $wholestring;
	$size = getimagesize($filepath);
	$width = $size[0];
	$height = $size[1];
	
	if ($width >  $ImgPopUpMaxImgWidth && $ImgPopUpEnabled) {
		$wholestring = chop(str_replace("|","",$wholestring));	
		$out = Keep(FmtPageName("<a href='\$PageUrl?action=imgpopup&amp;image=$imagename' ".$openpopupin."  class='imgpopup'>",$pagename)).
				$wholestring.Keep("</a>");
				if ($width > $ImgPopUpShowZoomLinkTreshold * $ImgPopUpMaxImgWidth) {
					$out .= Keep(FmtPageName("<a href='\$PageUrl?action=imgpopup&amp;image=$imagename'  ".$openpopupin." class='imgpopuptext' style='".$showzoomlink."'><br/>
					$magnifierhtml".
					"$[Zoom Image]".
					"</a><br/>",$pagename));
				}
	} elseif(!$ImgPopUpIgnoreUnscaledImages && $ImgPopUpEnabled) {
		$wholestring = chop(str_replace("|","",$wholestring));
		$out = Keep(FmtPageName("<a href='$PageUrl?action=imgpopup&amp;image=$imagename'  ".$openpopupin.">",$pagename)).$wholestring.Keep("</a>");
	} else {
		$out = $wholestring;
	}
	
	return $out;
}

/**
 * Generates of the pop up window.
 * 
 * @param string $pagename 
 */
function HandleImgPopUp($pagename) {
	global $PageStartFmt, $PageEndFmt, $PageUrl,$ImgPopUpProvideUploadLink,$ImgPopUpSameWindow;
	
	$imagename = $_REQUEST['image'];
	$names = imgpopuph1fct($imagename, $pagename);
	
	if ($ImgPopUpSameWindow){
		$jsgoto = "javascript:history.go(-1);";
		$textgoto = "$[Go back]";
	}
	else {
		$jsgoto = "javascript:window.close();";
		$textgoto = "$[Close window]";
	}
		
	$text[] = "(:noheader:)(:nofooter:)(:notitle:)(:noleft:)";
	$text[] = "(:div style='text-align:center;':)";
	$text[] = "'''[=".$names['image']."=]''' \n ";
	$text[] = Keep("<a href='".$jsgoto."' class='imgpopupimg'>")."Attach:".$names['image'].Keep("</a>")."\n";
	if ($ImgPopUpProvideUploadLink)
		$text[] = "[[".FmtPageName("\$PageUrl?action=upload&upname=".$names['image'], $names['page'])." | $[Upload this image again]]] \n";
	$text[] = Keep("<a href='".$jsgoto."' style='text-decoration: none;' title='... or click on the image (JavaScript needed)'>").
					FmtPageName("[-$textgoto-]",$pagename);
					Keep("</a>");
	$text[] = "(:divend:)";
	$text = implode("\n", $text);
	$PagePopUpImg = MarkupToHTML($names['page'], $text);
	SDV($HandleImgPopUpFmt, array (& $PageStartFmt, & $PagePopUpImg, & $PageEndFmt));
	PrintFmt($pagename, $HandleImgPopUpFmt);
}

/**
 * Helps to find the image filepath depending on Attach syntax and $UploadPrefixFmt
 * 
 * @param string $imagename the image name only "$UploadPrefixFmt/image.ext" string
 * @param string $pagename
 * @return array array with 'image' as the new $imagename and 'page' as the new $pagename 
 * @since v1.2, 2005-08-25
 */
function imgpopuph1fct($imagename, $pagename) {
	global $UploadPrefixFmt;
	$pgn = explode(".", $pagename);
	$upl = explode("/", $imagename);
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