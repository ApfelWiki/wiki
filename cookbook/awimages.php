<?php if (!defined('PmWiki')) exit();

/**
 * 
 *  
 * @author Patrick R. Michaud <pmichaud@pobox.com> 
 * @author Thomas Riley <tjriley(at)users.sourceforge.net.> 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 0.3.3
 * @copyright by the authors 2006, 2007
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package awimages
 */


include_once("$FarmD/cookbook/c_arguments.php");

define(AWIMAGES,"0.3.2");

SDVA($PmWikiAutoUpdate['AWImages'], array(
    'version' => AWIMAGES, 
    'updateurl' => 'ApfelWiki only at the moment'
));

SDV($AWImagesCachePath, 'cache/awimages');

SDV($AWPopUpProvideUploadLink,true);
SDV($AWPopUpEnabled,true);
SDV($AWPopUpSameWindow,true);
SDV($AWPopUpNoZoomLink, false);
SDV($AWPopUpSkinDirUrl,"cookbook/imgpopup");
SDV($HTMLStylesFmt['awpopup'] , "
  .awpopuptext {text-decoration: none;font-size:smaller; padding-left:5px;}
 
");

Markup('awgalerie','<split','/\\(:galerie(.*?)?:\\)(.*?)\\(:galerieende:\\)/se',"AWGalerieC('$pagename',PSS('$1'),'$2')");

function AWGalerieC($pagename, $args, $images) {
	$pargs = ParseArgs($args);
	
	preg_match_all("/Attach:.*?\\s(?=Attach|$)/s", PSS($images), $images);
	foreach ($images[0] as $image) {
		$Arguments = preg_split("/(Attach:[^\\s]*)/", trim($image), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$arguments = new ParseArguments;
		$arguments->setString($args);
		$arguments->mergeWithString($Arguments[1]);
		$out[] = AWImagesC($pagename, "$image ".$arguments->getString(), $args); 
	}
	
	if ($pargs['spalten'])
		for ($i=$pargs['spalten']-1; $i<count($out); $i=$i+$pargs['spalten'])
			$out[$i] = "$out[$i] <br />";
	
	return implode(" ", $out);
}


Markup('awbilder', 'directives', '/\\(:bild(\\s.*?):\\)/e', "(AWImagesC('$pagename',PSS('$1'), PSS('$0')))");


function AWImagesC($pagename, $Arguments, $WholeString) {
    global $UploadDir;
    
	$Arguments = preg_split("/(Attach:[^\\s]*)/", trim($Arguments), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	$image = PopUpFct($Arguments[0],str_replace("Attach:", "", $Arguments[0]));
	$args = ParseArgs($Arguments[1]);
	
	$magnifier = @in_array("lupe",$args['']) ? true : false;
	
	if (@in_array("rahmen",$args['']))  $rahmen = TRUE; 
	
	if ($args['vorschau']) { 
		list($width,$height) = explode("x",$args['vorschau']);
		$thumb = TRUE;
	}
	if (@in_array("vorschau",$args[''])) 
		if (!is_numeric($width) || !is_numeric($height)) 
			$width = $height = 200;
	
	
		
	if (@in_array("vorschau",$args['']))  $thumb = TRUE;
	if ($rahmen || $thumb)
	    
	    # tests if the file exists, duplicate function in gunther(), 
	    # TODO:refactor
	    #$img = h2fct($Arguments[0], $pagename);
        #$image = $img['image'];
    	#$original_file = FmtPageName("$UploadDir/".$img['group']."/$image", $pagename);
    	#if (!file_exists($original_file))
    	#    return $WholeString; 
    	    
		$image = Keep(PopUpFct(gunther($pagename, str_replace("Attach:", "", $Arguments[0]), $rahmen, $thumb, $width, $height),str_replace("Attach:", "", $Arguments[0]), $magnifier));

	
	if ($args['titel']){  
		#return Keep("<span style='position:relative;margin:3px;'>$image <br/><span style='width:200px;height:50px;display:block;padding:3px;position:absolute;'>{$args['titel']}</span></span>");
		$title = "<span style='display:block;padding:0px 5px 0px 5px;width:{$width}px;'>".$args['titel']."</span>" ;
		return Keep("<table style='display:inline;'><tr><td>").$image.Keep("</td></tr><tr><td> $title</td></tr></table>");	
	} 
	return "$image";
}

/**
 * Scales down images and generates the thumbnails
 *
 * @author Thomas Riley <tjriley(at)users.sourceforge.net.> 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @link http://gunther.sourceforge.net/ http://gunther.sourceforge.net/ 
 */

function gunther($pagename, $thumbname, $rahmen=FALSE , $thumb = FALSE, $width, $height, $border = 3) {
	global $PubDirUrl, $FarmD, $UploadDir, $UploadPrefixFmt;
	global $AWImagesCachePath;
	
	
	$awimages_cache_url = str_replace('/pub',"/$AWImagesCachePath",$PubDirUrl);
	$edible_base_dir = "$FarmD/pub/cookbook/awimages";
	$thumbs_dir = "$FarmD/$AWImagesCachePath";

	$img = h2fct($thumbname, $pagename);
	$image = $img['image'];

	$original_file = FmtPageName("$UploadDir/".$img['group']."/$image", $pagename);

	# echo $original_file = LinkUpload($pagename, "Attach:", $thumbname, "title", "text","\$FileUrl"); 
	
	if (!file_exists($thumbs_dir))
		mkdir($thumbs_dir);

	if (!file_exists($original_file))
		return ""; #return "Das Originalbild $thumbname konnte nicht gefunden werden.";
		
	$w = $width;
	$h = $height;

	//generate image from the specific format
	$size = getimagesize($original_file);
    $type = $size[2];
	switch($type) {
		case 1:	
			$im = imagecreatefromgif($original_file);
			break;
		case 2:
			$im = imagecreatefromjpeg($original_file);
			break;
		case 3:
			$im = imagecreatefrompng($original_file);
			break;
		default:
			return "Das Bildformat wird nicht von der Markup (:bild:) unterstützt.";
			break;
	}
		  

	$orig_w = imagesx($im);
	$orig_h = imagesy($im);

	if($rahmen && !$thumb) {
		$w = $orig_w;
		$h = $orig_h;	
	} 
	
	// don't go bigger than original
	if ($w > $orig_w)
		$w = $orig_w;
	if ($h > $orig_h)
		$h = $orig_h;

	$thumb_file_name = $image.'_'.$w.'x'.$h.'r'.$rahmen.'b'.$border.".jpg";
	$thumb_file = $thumbs_dir.'/'.$thumb_file_name;
	
	if (file_exists($thumb_file) 
		&& filemtime($thumb_file) > filemtime($original_file) && // regenerate thumb if original is uploaded again
		filemtime($thumb_file) > filemtime(__FILE__)) // regenerate thumb if this script has changed
				return "<img src='$awimages_cache_url/$thumb_file_name' />"; 


	$scale = $orig_w / $w;
	if ($orig_h / $scale > $h)
		$scale = $orig_h / $h;
	
	define('TOP', $edible_base_dir.'/shadow_top.png');
	define('BOTTOM', $edible_base_dir.'/shadow_bottom.png');
	define('LEFT', $edible_base_dir.'/shadow_left.png');
	define('RIGHT', $edible_base_dir.'/shadow_right.png');
	define('TOPRIGHT', $edible_base_dir.'/shadow_topright.png');
	define('TOPLEFT', $edible_base_dir.'/shadow_topleft.png');
	define('BOTTOMRIGHT', $edible_base_dir.'/shadow_bottomright.png');
	define('BOTTOMLEFT', $edible_base_dir.'/shadow_bottomleft.png');

	$top = imagecreatefrompng(TOP);
	$bottom = imagecreatefrompng(BOTTOM);
	$left = imagecreatefrompng(LEFT);
	$right = imagecreatefrompng(RIGHT);
	$topleft = imagecreatefrompng(TOPLEFT);
	$topright = imagecreatefrompng(TOPRIGHT);
	$bottomleft = imagecreatefrompng(BOTTOMLEFT);
	$bottomright = imagecreatefrompng(BOTTOMRIGHT);

	$total_w = $orig_w / $scale + $border * 2 + imagesx($left) + imagesx($right);
	$total_h = $orig_h / $scale + $border * 2 + imagesy($top) + imagesy($bottom);

	$im2 = @ imagecreatetruecolor($total_w, $total_h);

	// fill whole area with white
	imagefilledrectangle($im2, 0, 0, $total_w, $total_h, 16777215);
	
	// copy scaled image into centre
		imagecopyresampled($im2, $im, $border +imagesx($left), $border +imagesy($top), 0, 0, $orig_w / $scale, $orig_h / $scale, $orig_w, $orig_h);
	
	if ($rahmen) {
		// add drop-shadow
		imagecopyresampled($im2, $bottom, imagesx($left), $total_h -imagesy($bottom), 0, 0, $total_w -imagesx($left) - imagesx($right), imagesy($bottom), imagesx($bottom), imagesy($bottom));
		imagecopyresampled($im2, $top, imagesx($left), 0, 0, 0, $total_w -imagesx($left) - imagesx($right), imagesy($top), imagesx($top), imagesy($top));
		imagecopyresampled($im2, $topleft, 0, 0, 0, 0, imagesx($topleft), imagesy($topleft), imagesx($topleft), imagesy($topleft));
		imagecopyresampled($im2, $topright, $total_w -imagesx($topright), 0, 0, 0, imagesx($topright), imagesy($topright), imagesx($topright), imagesy($topright));
		imagecopyresampled($im2, $bottomleft, 0, $total_h -imagesy($bottomleft), 0, 0, imagesx($bottomleft), imagesy($bottomleft), imagesx($bottomleft), imagesy($bottomleft));
		imagecopyresampled($im2, $bottomright, $total_w -imagesx($bottomright), $total_h -imagesy($bottomright), 0, 0, imagesx($bottomright), imagesy($bottomright), imagesx($bottomright), imagesy($bottomright));
		imagecopyresampled($im2, $left, 0, imagesy($topleft), 0, 0, imagesx($left), $total_h -imagesy($bottomleft) - imagesy($topleft), imagesx($left), imagesy($left));
		imagecopyresampled($im2, $right, $total_w -imagesx($bottomright), imagesy($topright), 0, 0, imagesx($right), $total_h -imagesy($bottomright) - imagesy($topright), imagesx($right), imagesy($right));

		imagedestroy($bottom);
		imagedestroy($top);
		imagedestroy($left);
		imagedestroy($right);
		imagedestroy($topleft);
		imagedestroy($topright);
		imagedestroy($bottomright);
		imagedestroy($bottomleft);
	} 
	
	imagedestroy($im);
	imagejpeg($im2, $thumb_file, 80);

	imagedestroy($im2);
	
	return "<img src='$awimages_cache_url/$thumb_file_name' />";
}

/**
 * Helps to find the image filepath depending on Attach syntax and $UploadPrefixFmt
 * 
 * @param string $imagename the image name only "$UploadPrefixFmt/image.ext" string
 * @param string $pagename
 * @return array array with 'image' as the new $imagename and 'page' as the new $pagename 
 * @since v1.3, 2006-01-24
 */
function h2fct($imagename, $pagename) {
	#echo $imagename;
	global $UploadPrefixFmt;
	$pgn = preg_split("/[\/.]/", $pagename);
	$upl = explode("/", $imagename);
	$group = $pgn[0];
	if (isset ($upl[1])) {
		if ($UploadPrefixFmt == '/$Group')
			$pagename = $upl[0].".Upload";
		else {
			$pagename = $pgn[0].".".$upl[0];
		}
		$imagename = $upl[1];
		$group = $upl[0];
	}
	elseif (isset ($upl[2])) {
		$pagename = $upl[0].".".$upl[1];
		$imagename = $upl[2];
	}

	return array ('image' => $imagename, 'page' => $pagename, 'group' => $group );
}

$HandleActions['imgpopup'] = 'HandleAWUp';
$HandleAuth['imgpopup'] = 'read';

/**
 * Scales down the images and link them to the popup window.
 * 
 * @param string $wholestring the whole "Attach:$UploadPrefixFmt/image.ext" string
 * @param string $imagename the image name only "$UploadPrefixFmt/image.ext" string
 * @return string if succesfull it returns $wholestring but surrounded by scaling an link html/css syntax
 */
function PopUpFct($wholestring, $imagename, $magnifier = false) {
	global $UploadFileFmt, $pagename, $PageUrl, $ImgPopUpMaxImgWidth, $AWPopUpSkinDirUrl, $pagename, 
		$PubDirUrl, $ImgPopUpIgnoreUnscaledImages, $AWPopUpNoZoomLink,$AWPopUpSameWindow,
		$AWPopUpEnabled, $ImgPopUpShowZoomLinkTreshold;

	$names = h2fct($imagename, $pagename);

	$filepath = FmtPageName("$UploadFileFmt/".$names['image'], $names['page']);
	
	$magnifierpath =  "$PubDirUrl/$AWPopUpSkinDirUrl/magnifier.png";
	$magnifierhtml = "";
	if (file_exists(FmtPageName("pub/$AWPopUpSkinDirUrl/magnifier.png",$pagename)))
		$magnifierhtml = "<img src='$magnifierpath' style='margin:0px;' />";
	
	if ($AWPopUpNoZoomLink)
		$showzoomlink = "display:none";
	if (!$AWPopUpSameWindow)
		$openpopupin = "target='_blank'";	

	if (!file_exists($filepath))
		return $wholestring;
	
	$out = Keep(FmtPageName("<a href='\$PageUrl?action=imgpopup&amp;image=$imagename' ".$openpopupin."  class='awpopup'>",$pagename)).
			$wholestring.Keep("</a>");
			// with zoom magnifier
			if ($magnifier) {
				$out .= Keep(FmtPageName("<a href='\$PageUrl?action=imgpopup&amp;image=$imagename'  ".$openpopupin." class='awpopuptext' style='".$showzoomlink."'><div >
				$magnifierhtml".
				"$[Zoom Image]".
				"</a></div>",$pagename));
			}
	 
	return $out;
}

/**
 *  @author Sebastian Siedentopf
 *	@author Tom Riley
 *  @link http://gunther.sourceforge.net Gunther 
 *	@license http://www.gnu.org/copyleft/gpl.html GNU General Public License
*/

/**
 * Generates of the pop up window.
 * 
 * @param string $pagename 
 */
function HandleAWUp($pagename) {
	global $PageStartFmt, $PageEndFmt, $PageUrl,$AWPopUpProvideUploadLink,$AWPopUpSameWindow;
	
	$imagename = $_REQUEST['image'];
	$names = h2fct($imagename, $pagename);
	
	if ($AWPopUpSameWindow){
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
	if ($AWPopUpProvideUploadLink)
		$text[] = "[[".FmtPageName("\$PageUrl?action=upload&amp;upname=".$names['image'], $names['page'])." | $[Upload this image again]]] \n";
	$text[] = Keep("<a href='".$jsgoto."' style='text-decoration: none;' title='... or click on the image (JavaScript needed)'>").
					FmtPageName("[-$textgoto-]",$pagename);
					Keep("</a>");
	$text[] = "(:divend:)";
	$text = implode("\n", $text);
	$PagePopUpImg = MarkupToHTML($names['page'], $text);
	SDV($HandleImgPopUpFmt, array (& $PageStartFmt, & $PagePopUpImg, & $PageEndFmt));
	PrintFmt($pagename, $HandleImgPopUpFmt);
}
?>