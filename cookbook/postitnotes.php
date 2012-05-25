<?php

/**
 * This script generates different types of little yellow note stickies.
 * 
 * <http://www.pmwiki.org/wiki/Cookbook/PostItNotes>
 *
 * For more information see the readme.md
 * 
 * @author Patrick R. Michaud <pmichaud@pobox.com> 
 * @author John Rankin <john.rankin@affinity.co.nz>
 * @author Sebastian Siedentopf <openmail+sourcecode@siezi.com>
 * @version 2.0.4
 * @link http://www.pmwiki.org/wiki/Cookbook/PostItNotes the postitnotes cookbook on pmwiki.org
 * @copyright by the respective authors 2004-2012
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package postitnotes
 */

if (!defined('PmWiki'))
	exit ();

$RecipeInfo['PostItNotes']['Version'] = '2012-05-25';

/**
 * cookbook postitnotes is included
 * and running on this pmwiki installation
 */
define(POSTITNOTES, $RecipeInfo['PostItNotes']['Version']);

# see http://schlaefer.macbay.de/index.php/PmWikiCookbook/AutoUpdate
SDVA($PmWikiAutoUpdate['PostItNotes'] , array( 
    'version' => POSTITNOTES, 
    'updateurl' =>  "http://pmwiki.org/wiki/Cookbook/PostItNotes"
));


//the overall note style
SDV($HTMLStylesFmt['note'], "
div.widenote {
  position: relative;
	z-index:50;
	font-size: smaller;
	clear:both;
	color: #3C3528;
	float: right;
  margin: 0 1em 1em 1em;
	border: 1px solid #ccc;
	width: 200px;
	line-height: 1.4;
	background-color: #ffffa1; 
  box-shadow: 0px 1px 1px #ccc;
  border-radius: 2px;
  }
div.widenote p {
    padding: 0.5em 1em;
}
div.widenote-header {
	background-color: #FFE53E;
  border-radius: 2px;
  }
div.widenote-header a {
	color:#3C3528;
	text-decoration: none;
  }

");

//this javascript is needed for the hidden notes
SDV($HTMLHeaderFmt['notehidden'] , "<script type='text/javascript'>
function switchhiddenjvscfct(n) {
 {
		detail=document.getElementById(n);
	}
	if (detail==null) return false;
	if (detail.style.display==\"none\") {
	   detail.style.display=\"inline\";
	 } else {
	   detail.style.display=\"none\";
	}
}
</script>");
// defines where the images for the fance note are
SDV($PostItNotesSkinDirUrl,$PubDirUrl."/cookbook/postitnotes");
SDV($PostItNotesEditLink, true);
SDVA($PostItNotesImageNames, array("top" => "sticky_top.png", 'middle' => "sticky_middle.png", "bottom" => "sticky_bottom.png", "edit" => "sticky_edit.png") );
$PostItNodesID = 0;

/*
$PostItNoteFmt['fancy'] = '
(:table id="$PINID" class="widenote" style="border:0px; width:213px; background:none; border-collapse:collapse; $PINTS" :)
(:cell class="widenote-header" style="background: transparent url('.Keep("$PostItNotesSkinDirUrl/{$PostItNotesImageNames['top']}").') no-repeat; height: 33px;":)
(:cellnr style="background: transparent url('.Keep("$PostItNotesSkinDirUrl/{$PostItNotesImageNames['middle']}").') repeat-y; min-height: 35px;" :)'.Keep("<div style='padding:5px 0px 0px 15px;width:180px;overflow:hidden;'>") . '$PINSCC.' . Keep("</div>").'
(:cellnr style="background: transparent url('.Keep("$PostItNotesSkinDirUrl/{$PostItNotesImageNames['bottom']}").') no-repeat; height: 35px; text-align:right;":)
$PINEditLink
(:tableend:)';
*/

$PostItNoteFmt['normal'] = '
(:div1 id="$PINID" class="widenote" style="$PINTS" :)
(:div2 class="widenote-header"style="$PINFCS":) $PINEditLink
$PINFCC
(:div2end:)
(:div3:)
$PINSCC
(:div3end:)
(:div1end:)
';

// compatibility with TableEdit recipe
if (is_array($RecipeInfo['TableEdit'])) {
	/* $PostItNoteFmt['fancy'] = '(:notabledit:)'.$PostItNoteFmt['fancy'].'(:tabledit:)'; */
	$PostItNoteFmt['normal'] = '(:notabledit:)'.$PostItNoteFmt['normal'].'(:tabledit:)';
}

Markup('note', '<split', "/\\(:note((block)?|(fancy?))(.*?)(\\(:note)?\\1(end)?:\\)/se", "PostItNotesC('$pagename', '$1', PSS('$4'), '$0')");

/*
 * Seperates the title, content and arguments of the note
 */
function PostItNotesC($pagename, $noteblock, $notecontent, $noteall){
#	return Keep("<div style='color:red'>$noteall</div>");
	// evaluate which type of note is used
	$pargs['fancy'] = (substr($notecontent,0,5) === "fancy") ? 1 : 0;
	$pargs['hidden'] = (substr($notecontent,0,6) === "hidden") ? 1 : 0;
	$pargs['block'] = ($noteblock === "block") ? 1 : 0;
	
	if ($pargs['fancy']) $notecontent = substr($notecontent,5);
	if ($pargs['hidden']) $notecontent = substr($notecontent,6);
	if ($pargs['block']) { // split block entries and get note parameter
		list ($args, $notecontent) = preg_split("/^(.*?):\\)/s", $notecontent, -1,  PREG_SPLIT_DELIM_CAPTURE| PREG_SPLIT_NO_EMPTY);
		$notecontent ?$pargs = array_merge($pargs, ParseArgs($args)) : $notecontent = $args;
	}
	if (!($pargs['fancy'] && $pargs['block']))
		list ($notetitle, $notecontent) = PostItNotesSplitTitleAndContent($pagename, $notecontent);
	return FmtPostItNotes ($pagename, $notetitle, $notecontent, $pargs);
}

/**
 * formats the note
 */
function FmtPostItNotes ($pagename, $notetitle, $notecontent, $pargs) {
  global $FmtV;
	global $PostItNoteFmt, $PostItNodesID, $PostItNotesImageNames, $PostItNotesSkinDirUrl, $PostItNotesEditLink;

  $PostItNoteFmt['normal'] ;

  $FmtV['$PINTS'] = "";
  $FmtV['$PINFCS'] = "";
  $FmtV['$PINEditLink'] = "";

  $FmtV['$PINID'] = $PostItNodesID++;
  $FmtV['$PINFCC'] = $notetitle;
	$FmtV['$PINSCC'] = $notecontent;

	if($pargs['color']) {
	    $style = PostItNotesColors($pargs['color']);
	    $FmtV['$PINTS'] .= $style['table'];
	    $FmtV['$PINFCS'] .= $style['noteheader'];
	}
	
	if ($pargs['hidden']) {
	    $id = rand ();
	    $FmtV['$PINFCC'] = Keep("<a href=\"javascript:void(0);\" onclick=\"switchhiddenjvscfct('".$id."')\"; style='color:{$style['color']}'>").$FmtV['$PINFCC'].Keep("</a>");
	    $FmtV['$PINSCC'] = Keep("<div id=\"".$id."\" style=\"display:none;\">").$FmtV['$PINSCC'].Keep("</div>");
	}
    
    if ($pargs['float']) $FmtV['$PINTS'] .= "float:{$pargs['float']};"; 
    if ($pargs['width']) $FmtV['$PINTS'] .= "width:{$pargs['width']};";

    $format = $PostItNoteFmt['normal'];
        
    if ($PostItNotesEditLink) :
      $FmtV['$PINEditLink'] = Keep(FmtPageName(
              "<a name='note{$FmtV['$PINID']}'></a>"
              . "<a href='\$PageUrl?action=edit&amp;note={$FmtV['$PINID']}' style='float:right; color:{$style['color']}'>"
              . "$[(&#x2193;)]"
              . "</a>",
              $pagename));
    endif;

    /*
    if ($pargs['fancy']) {
        if ($PostItNotesEditLink) :
          $FmtV['$PINEditLink'] = Keep(FmtPageName(
                  "<a name='note{$FmtV['$PINID']}'></a>"
                    . "<a href='\$PageUrl?action=edit&amp;note={$FmtV['$PINID']}'>"
                    . "<img src='$PostItNotesSkinDirUrl/{$PostItNotesImageNames['edit']}'>"
                    . "</a>",
                  $pagename));
        endif;
        if ($notetitle) :
          $FmtV['$PINSCC'] = Keep("<strong>").$FmtV['$PINFCC'].Keep("</strong><br /><br />"). $FmtV['$PINSCC'];
        endif;
        $format = $PostItNoteFmt['fancy'];
    }
    */

    return FmtPageName ($format, $pagename);
}

/**
 * helper function which seperates the title and content of a note
 * 
 * @param string $message  header and content of the note seperated by |: "title|content"
 * @param string $pagename 
 * @return array array with ['title'] and ['content'] of the page
 */
function PostItNotesSplitTitleAndContent($pagename, $notecontent) {
	if (strpos($notecontent, "(:notecontent:)")) 
		return  explode("(:notecontent:)", $notecontent);
	
	$notetitle = substr($notecontent, 0, strpos($notecontent, "|"));
	if ($notetitle == false) {
		$notetitle = FmtPageName('$[Note]',$pagename);
		$notecontent = $notecontent;
	} else
		$notecontent = substr(strstr($notecontent, "|"), 1);
	return array (trim($notetitle), trim($notecontent));
}


/**
 * generates the color value for a given color name
 * 
 * @param string $color color name as predefined, WEBSAFE, or #hex
 * @return array 
 */
function PostItNotesColors($color = "yellow") {
    //defines the websave colors
	$wsc = array ("ALICEBLUE" => "#F0F8FF", "ANTIQUEWHITE" => "#FAEBD7", "AQUA" => "#00FFFF", "AQUAMARINE" => "#7FFFD4", "AZURE" => "#F0FFFF", "BEIGE" => "#F5F5DC", "BISQUE" => "#FFE4C4", "BLACK" => "#000000", "BLANCHEDALMOND" => "#FFEBCD", "BLUE" => "#0000FF", "BLUEVIOLET" => "#8A2BE2", "BROWN" => "#A52A2A", "BURLYWOOD" => "#DEB887", "CADETBLUE" => "#5F9EA0", "CHARTREUSE" => "#7FFF00", "CHOCOLATE" => "#D2691E", "CORAL" => "#FF7F50", "CORNFLOWERBLUE" => "#6495ED", "CORNSILK" => "#FFF8DC", "CRIMSON" => "#DC143C", "CYAN" => "#00FFFF", "DARKBLUE" => "#00008B", "DARKCYAN" => "#008B8B", "DARKGOLDENROD" => "#B8860B", "DARKGRAY" => "#A9A9A9", "DARKGREEN" => "#006400", "DARKKHAKI" => "#BDB76B", "DARKMAGENTA" => "#8B008B", "DARKOLIVEGREEN" => "#556B2F", "DARKORANGE" => "#FF8C00", "DARKORCHID" => "#9932CC", "DARKRED" => "#8B0000", "DARKSALMON" => "#E9967A", "DARKSEAGREEN" => "#8FBC8F", "DARKSLATEBLUE" => "#483D8B", "DARKSLATEGRAY" => "#2F4F4F", "DARKTURQUOISE" => "#00CED1", "DARKVIOLET" => "#9400D3", "DEEPPINK" => "#FF1493", "DEEPSKYBLUE" => "#00BFFF", "DIMGRAY" => "#696969", "DODGERBLUE" => "#1E90FF", "FIREBRICK" => "#B22222", "FLORALWHITE" => "#FFFAF0", "FORESTGREEN" => "#228B22", "FUCHSIA" => "#FF00FF", "GAINSBORO" => "#DCDCDC", "GHOSTWHITE" => "#F8F8FF", "GOLD" => "#FFD700", "GOLDENROD" => "#DAA520", "GRAY" => "#BEBEBE", "GREEN" => "#008000", "GREENYELLOW" => "#ADFF2F", "HONEYDEW" => "#F0FFF0", "HOTPINK" => "#FF69B4", "INDIANRED" => "#CD5C5C", "INDIGO" => "#4B0082", "IVORY" => "#FFFFF0", "KHAKI" => "#F0D58C", "LAVENDER" => "#E6E6FA", "LAVENDERBLUSH" => "#FFF0F5", "LAWNGREEN" => "#7CFC00", "LEMONCHIFFON" => "#FFFACD", "LIGHTBLUE" => "#ADD8E6", "LIGHTCORAL" => "#F08080", "LIGHTCYAN" => "#E0FFFF", "LIGHTGOLDENRODYELLOW" => "#FAFAD2", "LIGHTGREEN" => "#90EE90", "LIGHTGREY" => "#D3D3D3", "LIGHTPINK" => "#FFB6C1", "LIGHTSALMON" => "#FFA07A", "LIGHTSEAGREEN" => "#20B2AA", "LIGHTSKYBLUE" => "#87CEFA", "LIGHTSLATEGRAY" => "#778899", "LIGHTSTEELBLUE" => "#B0C4DE", "LIGHTYELLOW" => "#FFFFE0", "LIME" => "#00FF00", "LIMEGREEN" => "#32CD32", "LINEN" => "#FAF0E6", "MAGENTA" => "#FF00FF", "MAROON" => "#800000", "MEDIUMAQUAMARINE" => "#66CDAA", "MEDIUMBLUE" => "#0000CD", "MEDIUMORCHID" => "#BA55D3", "MEDIUMPURPLE" => "#9370DB", "MEDIUMSEAGREEN" => "#3CB371", "MEDIUMSLATEBLUE" => "#7B68EE", "MEDIUMSPRINGGREEN" => "#00FA9A", "MEDIUMTURQUOISE" => "#48D1CC", "MEDIUMVIOLETRED" => "#C71585", "MIDNIGHTBLUE" => "#191970", "MINTCREAM" => "#F5FFFA", "MISTYROSE" => "#FFE4E1", "MOCCASIN" => "#FFE4B5", "NAVAJOWHITE" => "#FFDEAD", "NAVY" => "#000080", "OLDLACE" => "#FDF5E6", "OLIVE" => "#808000", "OLIVEDRAB" => "#6B8E23", "ORANGE" => "#FFA500", "ORANGERED" => "#FF4500", "ORCHID" => "#DA70D6", "PALEGOLDENROD" => "#EEE8AA", "PALEGREEN" => "#98FB98", "PALETURQUOISE" => "#AFEEEE", "PALEVIOLETRED" => "#DB7093", "PAPAYAWHIP" => "#FFEFD5", "PEACHPUFF" => "#FFDAB9", "PERU" => "#CD853F", "PINK" => "#FFC0CB", "PLUM" => "#DDA0DD", "POWDERBLUE" => "#B0E0E6", "PURPLE" => "#800080", "RED" => "#FF0000", "ROSYBROWN" => "#BC8F8F", "ROYALBLUE" => "#4169E1", "SADDLEBROWN" => "#8B4513", "SALMON" => "#FA8072", "SANDYBROWN" => "#F4A460", "SEAGREEN" => "#2E8B57", "SEASHELL" => "#FFF5EE", "SIENNA" => "#A0522D", "SILVER" => "#C0C0C0", "SKYBLUE" => "#87CEEB", "SLATEBLUE" => "#6A5ACD", "SLATEGRAY" => "#708090", "SNOW" => "#FFFAFA", "SPRINGGREEN" => "#00FF7F", "STEELBLUE" => "#4682B4", "TAN" => "#D2B48C", "TEAL" => "#008080", "THISTLE" => "#D8BFD8", "TOMATO" => "#FF6347", "TURQUOISE" => "#40E0D0", "VIOLET" => "#EE82EE", "WHEAT" => "#F5DEB3", "WHITE" => "#FFFFFF", "WHITESMOKE" => "#F5F5F5", "YELLOW" => "#FFFF00", "YELLOWGREEN" => "#9ACD32",);

	/* These are predefined styles. You can add more styles or css arguments if you want*/
	$style['yellow']['table'] = array ("background-color" => "#ffffa1;");
	$style['yellow']['noteheader'] = array ("background-color" => " #ffe53e");

	$style['blue']['table'] = array ("background-color" => "#71ffff");
	$style['blue']['noteheader'] = array ("background-color" => " #5ceaea");

	$style['purple']['table'] = array ("background-color" => "#ccdaff");
	$style['purple']['noteheader'] = array ("background-color" => " #b2c7ff");

	$style['pink']['table'] = array ("background-color" => "#ffe0e0");
	$style['pink']['noteheader'] = array ("background-color" => " #ffc7c7");

	$style['green']['table'] = array ("background-color" => "#d5ffcc",);
	$style['green']['noteheader'] = array ("background-color" => " #a9fBb7");

	$style['gray']['table'] = array ("background-color" => "#eeeeee");
	$style['gray']['noteheader'] = array ("background-color" => " #e0e0e0");

	
	/* calculates the layout for a non predefined style */
	if (!$style[$color]) { //if the color is not predefined
		if (strrchr($color, "#")) { // if it is a hex color value
			$style = PostItNotesHexColors($color);
			$color = "free";
		}
		elseif ($wsc[$color]) { //if it is a websave color
			$style = PostItNotesHexColors(strtolower($wsc[$color]));
			$color = "free";
		} else //if the color can not be recognized fallback to standard yellow
			$color = "yellow";
	}

	/* generates the css ouput */
	$style = $style[$color];
	foreach ($style as $tableelement => $csselement)
		foreach ($csselement as $csselement => $cssstyle)
			$styleout[$tableelement] .= $csselement.":".$cssstyle.";";
    $styleout['color'] = $style['table']['color'];
	return $styleout;
}

/**
 * This function sets a style given by a hex color.
 * 
 * It generates the title background color and decides if the 
 * title text color should be inverted based on the hex value of the background
 * color.
 * 
 * @param string $color the color as hex value: "#A0B2C3" 
 * @return array comlete style array
 */
function PostItNotesHexColors($color) {
	preg_match("/#(\w\w)(\w\w)(\w\w)/", $color, $hex);
	$mean = (hexdec($hex[1]) + hexdec($hex[2]) + hexdec($hex[3])) / 3;
	$hexreduce = array (hexdec($hex[1]), hexdec($hex[2]), hexdec($hex[3]));
	foreach ($hexreduce as $key => $p)
		if ($mean > 128) {
			if (($lower = $p -15 * ($p / 255 + 1)) >= 0)
				$hexreduce[$key] = $lower;
		} else {
			if (($upper = $p +60 * ($p / 255 + 1)) <= 255)
				$hexreduce[$key] = $upper;
		}

	// for better contrast we eventually invert the text color
	if ($mean < 128)
		$out['textcolor'] = "white";
	else
		$out['textcolor'] = "black";

	foreach ($hexreduce as $key => $p)
		$hexreduce[$key] = strtolower(sprintf("%02X", $p));
	$out['hex'] = implode("", $hexreduce);
	
	$style['free']['table'] = array ("background-color" => $color);
	$style['free']['table']['color'] = $out['textcolor'];
	$style['free']['noteheader'] = array ("background-color" => "#".$out['hex']);
	
	return $style;
}

if ($action == 'edit' && isset( $_REQUEST['note']))
        /* change Edithandler only if a parameter "s" is supplied */    
        $HandleActions['edit'] = 'HandleEditNote';

/* This function handles the edit, preview and saving of sections.
 * It derived from the standard HandleEdit() function defined in pmwiki.php. */
/*
 * The codebase ist sectionedit.php v 2.0.3
 */
function HandleEditNote($pagename, $auth = 'edit') {

	global $IsPagePosted, $EditFields, $ChangeSummary, $EditFunctions, $FmtV, $Now, $HandleEditFmt;
	global $PageStartFmt, $PageEditFmt, $PagePreviewFmt, $PageEndFmt, $GroupHeaderFmt, $GroupFooterFmt;
	global $PageEditForm, $EnablePost, $InputTags, $SectionEditWithoutHeaders;
	global $SectionEditMediaWikiStyle, $SectionEditAutoDepth,$MessageFmt;
	

            $InputTags['e_form'] = array (":html" => "<form method='post' action='\$PageUrl?action=edit?note=\$PNum?type=\$NoteType'>
    <input type='hidden' name='action' value='edit' />
    <input type='hidden' name='n' value='\$FullName' />
    <input type='hidden' name='basetime' value='\$EditBaseTime' />
    <input type='hidden' name='prechunk' value=\"\$PreChunk\" />
    <input type='hidden' name='note' value='\$PNum' />
    <input type='hidden' name='type' value ='\$NoteType' />
    <input type='hidden' name='postchunk' value=\"\$PostChunk\" />");


  /* standard code from HandleEdit()*/
     if ($_REQUEST['cancel']) {
             Redirect($pagename);
             return;
     }
     Lock(2);
     $IsPagePosted = false;
     $page = RetrieveAuthPage($pagename, $auth, true);
     if (!$page)
             Abort("?cannot edit $pagename");
     PCache($pagename, $page);
     $new = $page;

    

	/*disable sectioning when simultaneous edits*/


		/* splits the page text and sets the currently edited section */
        ## post it adaption start
        $notenumber = $_REQUEST['note'];
        $cnn = $notenumber;
	    $r = preg_split("/(\\(:note(?!(blockend|content)))/s", $new['text'], -1,PREG_SPLIT_DELIM_CAPTURE);
	    for($i = 0; $i < count($r); $i++) 
            if (substr($r[$i], 0, 6) == "(:note") {
                $s[] = $r[$i].$r[$i+1];
                $i++;
            } 
            else {
                if ($i == 0) $cnn++; // takes care if note is first item of page or not
                $s[] = $r[$i];
            }

        for ($i = 0; $i < count($s); $i++) {
            if ($i < $cnn)
                $t[0] .= $s[$i];
            elseif ($i == $cnn) {
               preg_match("/\\(:note((block)?|(fancy?)).*?(\\(note)?\\1(end)?:\\)/s", $s[$i],$u);
               $t[1] = $u[0];
               $t[2] = substr($s[$i], strlen($t[1]));
 
            }
            elseif ($i > $cnn)
                $t[2] .= $s[$i];
        }
        #print_r($t);exit;
          /* here the section for editing is selected*/
          $n = 1;
          $p=$t;
          
          
            $new['text'] = $p[$n];
            /* standard code from HandleEdit()*/
            foreach ((array) $EditFields as $k)
                    if (isset ($_POST[$k]))
                            $new[$k] = str_replace("\r", '', stripmagic($_POST[$k]));
            if ($ChangeSummary)
                    $new["csum:$Now"] = $ChangeSummary;

            /*if a preview previously took place the currently not edited text sections are obtained*/
            $PageChunks = array ('prechunk', 'postchunk');
            foreach ((array) $PageChunks as $c) {
                    $$c = '';
                    if (@ $_POST[$c])
                            $$c = str_replace("\r", '', stripmagic($_REQUEST[$c]));
            }
            if (@ $_POST['post']) {//the currently not edited sections are added
                    $new['text'] = $prechunk."\n".$new['text']."\n".$postchunk;
            }
            elseif (@ $_POST['preview']) { //page header contains info which section is edited
                    $GroupHeaderFmt = ''; $GroupFooterFmt = '';
            }
            else {
                    /* if the section is edited, the not edited sections go into $prechunk and
                     * $postchunk and retained here while editing/previewing until saving the whole page            
                     */
                     $prechunk = "";
    				$postchunk = "";
                    for ($i = 0; $i < count($p); $i ++) {
                            if ($i < $n) {
                                    $prechunk .= $p[$i];
                            }
                            elseif ($i > $n) {
                                    $postchunk .= $p[$i];
                            }
                    }
            }

            /* standard code from HandleEdit()*/
            $EnablePost &= (@ $_POST['post'] || @ $_POST['postedit']);
            foreach ((array) $EditFunctions as $fn)
                    $fn ($pagename, $page, $new);
            Lock(0);
            if ($IsPagePosted && !@ $_POST['postedit']) {
                    //jump directly to section that was last edited
                    Redirect($pagename, "\$PageUrl#note".$notenumber);
                    return;
            }
            $FmtV['$DiffClassMinor'] = (@ $_POST['diffclass'] == 'minor') ? "checked='checked'" : '';
            $FmtV['$EditText'] = str_replace('$', '&#036;', htmlspecialchars(@ $new['text'], ENT_NOQUOTES));
            $FmtV['$EditBaseTime'] = $Now;

            /*additional FmtV for this script*/
            $FmtV['$PreChunk'] = str_replace('"', '&quot;', str_replace('$', '&#036;', htmlspecialchars($prechunk, ENT_NOQUOTES)));
            $FmtV['$PostChunk'] = str_replace('"', '&quot;', str_replace('$', '&#036;', htmlspecialchars($postchunk, ENT_NOQUOTES)));
            $FmtV['$PNum'] = $wtf;
    	 	$FmtV['$NoteType'] = $notetype;

            /* standard code from HandleEdit() */
            if ($PageEditForm) {
                    $form = ReadPage(FmtPageName($PageEditForm, $pagename), READPAGE_CURRENT);
                    $FmtV['$EditForm'] = MarkupToHTML($pagename, $form['text']);
            }
            SDV($HandleEditFmt, array (& $PageStartFmt, & $PageEditFmt, & $PageEndFmt));
            PrintFmt($pagename, $HandleEditFmt);
}
        
?>