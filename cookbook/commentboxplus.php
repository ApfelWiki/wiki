<?php if (!defined('PmWiki')) exit();
/*
    commentboxstyled.php
    Copyright 2005, 2006 Hans Bracker, an adaptation of commentbox.php by
    John Rankin, copyright 2004, 2005 John Rankin john.rankin@affinity.co.nz
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    
    Put commentboxplus.css file into $Farm/pub/css/ directory.

    Adds (:commentbox:) and (:commentboxchrono:) markups.
    Put (:commentbox:) at the top of a page, or a GroupHeader,
    latest entries appear at top, underneat the commentbox,
    i.e. in reverse chronological order.

    Put (:commentboxchrono:) at the bottom of a page, or a GroupFooter,
    latest entries appear at bottom, above the commentbox,
    i.e. in chronological order.

    (:commentbox SomePageName:) or (:commentboxchrono SomePageName:) will post the comment
    on page 'SomePageName' instead of the page where the commentbox is.

    Adds commentbox with chronologfical entries automatically to pages
    which contain 'Journal' or 'Diary' in their name.

    If you use forms markup instead of (:commentbox:) or
    (:commentboxchrono:) markup, add name=cboxform to (:input form ....:),
    which functions as an identifier for the internal authentication check.

    You can hide the commentbox for users who have no page edit permissions
    with conditional markup of the EXACT form:
    (:if auth edit:)(:commentbox:)(:if:)
    and set $MPAnchorFmt = "(:commentbox:)(:if:)";
    if comments should be placed underneath the commentbox
    or (for commentboxchrono)
    (:if auth edit:)(:commentboxchrono:)(:if:)
    and set $MPAnchorChronoFmt = "(:if auth edit:)(:commentboxchrono:)";
    if comments should be placed above the commentbox
    Otherwise users can post comments even if they don't have page edit permission.

    You can also set $MPAnchorFmt and $MPAnchorChronoFmt to some different string 
    to use as an anchor for positioning the comments. 
    For instance set   $MPAnchorFmt = "[[#comments]]";
    and place [[#comments]] somewhere on the page, 
    then the comment posts will appear BENEATH this anchor.
    But note: setting   $MPAnchorChronoFmt = "[[#comments]]";
    and place [[#comments]] somewhere on the page, 
    then the comment posts will appear ABOVE this anchor.

*/
# Version date
$RecipeInfo['CommentBoxPlus']['Version'] = '2007-12-09a';

# enable page breaks after a number of posts: set in config.php $EnablePageBreaks = 1;
# requires installing pagebreak2.php. See Cookbook.BreakPage
SDV($EnablePageBreaks, false); # default is No Page Breaks
# with $EnablePageBreaks = 1; set number of posts per page: for instance $PostsPerPage = 50;
SDV($PostsPerPage,20); # default is 20 posts.

# The form check will display a warning message if user has not provided content and name.
# Set to false if no javascript form check is required.
SDV($EnableCommentFormCheck, 0);
SDV($NoCommentMessage, '$[Please enter a comment to post]');
SDV($NoAuthorMessage, '$[Please enter your name as author]');
SDV($NoCodeMessage, '$[Please enter the code number]');

# Set $EnableAccessCode to true if you want your users to enter a random
# generated access code number in order to post.
# This may stop robot scripts from posting.
SDV($EnableAccessCode, false);

# Set $EnableWebsiteField to true if you want an extra field for users to enter 
# a website beside their name. 
SDV($EnableWebsiteField, false);

# Set $EnablePreventDirectives to true if you want to prevent users from posting 
# any kind of directive (: ... :)
SDV($EnablePreventDirectives, false);

# Set $EnablPostToAnyPage = 1; if you want users to be able to post comments 
# to other pages than the current one, in conjunction with the use of  
# the markup (:commentbox GroupName.PageName:) or a postto form field.
# Be aware there is a security risk in this, as users could write markup to post to 
# edit protected or admin only pages as in the Site group. To counteract this risk
# include this script (or set $EnablePostToAnyPage =1;) ONLY FOR THOSE PAGES(s) a 
# commentbox form shall be used, AND PROTECT THESE PAGES by restricting edit access to them!
SDV($EnablePostToAnyPage, 0);

# set $EnablePostToGroupPage = 1; to allow posting to pages in the same group
# as the page with the (:commentbox:) form
SDV($EnablePostToGroupPage, 0);

# default list of pages allowed to post to 
SDVA($PostToCommentPageFmt, array(
	'{$Group}.{$Name}-Comments',
	'{$Group}.{$Name}-Discuss',  
	'Comments.{$Group}-{$Name}',
));

# set $RedirectToCommentPage = 1; if users shall be redirected after posting to a comment page 
# specified with (:commnetbox Group.TargetPage:). 
# Default is 0: users stay on the page where the commentbox form is.
SDV($EnableRedirectToCommentPage, 0);

# load commentbox.css for styling
SDV($CommentBoxPlusUrl,
	(substr(__FILE__, 0, strlen($FarmD)) == $FarmD)
	? '$FarmPubDirUrl/cookbook/commentboxplus' : '$PubDirUrl/cookbook/commentboxplus');
    
SDV($HTMLHeaderFmt['cbplus'], "
	<link href='$CommentBoxPlusUrl/commentboxplus.css' rel='stylesheet' type='text/css' />");

if($EnableCommentFormCheck==1) {
	$CommentBoxCheckFmt = "
	<script type='text/javascript' language='JavaScript1.2'>
	function checkform ( form ) {
	if (form.text && form.text.value == \"\") { window.alert( '$NoCommentMessage' ); form.text.focus(); return false; }
	if (form.author && form.author.value == \"\") { window.alert( '$NoAuthorMessage' ); form.author.focus(); return false; }".
	($EnableAccessCode ? "\n   if (form.access && form.access.value == \"\") { window.alert( '$NoCodeMessage' ); form.access.focus(); return false; }":"").
	(isset($EnablePostCaptchaRequired) ? "\n   if (form.{$CaptchaName} && form.{$CaptchaName}.value == \"\") { window.alert( '$NoCodeMessage' ); form.{$CaptchaName}.focus(); return false; }":"").
	"return true ;
	}
	</script>
	";
	# add markup (:cboxcheck:) for use when commentbox is constucted with (:input:) markup
	Markup('cboxcheck', '>block', '/\\(:cboxcheck:\\)/', Keep($CommentBoxCheckFmt));
}

SDV($DiaryBoxFmt,"<div id='diary'><form class='inputform' action='".PageVar($pagename, '$PageUrl')."' method='post' onsubmit='return checkform(this);'>
	<input type='hidden' name='n' value='\$FullName' />
	<input type='hidden' name='action' value='comment' />
	<input type='hidden' name='accesscode' value='\$AccessCode' />
	<input type='hidden' name='csum' value='$[Entry added]' />
	<table width='90%'><tr>
	<th class='prompt' align='right' valign='top'>$[New entry]&nbsp;</th>
	<td><textarea class='inputtext commenttext' name='text' rows='6' cols='50'></textarea><br />".
	(isset($EnablePostCaptchaRequired) ? "</td></tr><tr><th class='prompt' align='right' valign='top'><input type='hidden' name='captchakey' value='\$CaptchaKey' />
	Enter code: <em class='access'>\$Captcha </em></th><td><input type='text' name='\$CaptchaName' size='5' class='inputbox' /> " : "").
	($EnableAccessCode ? "</td></tr>
	<tr><th class='prompt' align='right' valign='top'>$[Enter code] <em class='access'>\$AccessCode</em></th>
	<td><input type='text' size='4' maxlength='3' name='access' value='' class='inputbox' /> "
	  : "<input type='hidden' name='access' value='\$AccessCode' /><br />").
	"<input class='inputbutton commentbutton' type='submit' name='post' value=' $[Post] ' />
	<input class='inputbutton commentbutton' type='reset' value='$[Reset]' /></td></tr></table></form></div>");

SDV($CommentBoxFmt,"
	<div id='message'><form name='cbox' class='inputform' action='".PageVar($pagename, '$PageUrl')."' method='post' onsubmit='return checkform(this);'>
	<input type='hidden' name='n' value='\$FullName' />
	<input type='hidden' name='action' value='comment' />
	<input type='hidden' name='order' value='\$Chrono' />
	<input type='hidden' name='postto' value='\$PostTo' />
	<input type='hidden' name='accesscode' value='\$AccessCode' />
	<input type='hidden' name='csum' value='$[Comment added]' />
	<table width='90%'><tr>
	<th class='prompt' align='right' valign='top'>$[Add Comment]&nbsp;</th>
	<td><textarea class='inputtext commenttext' name='text' id='text' rows=6 cols=40></textarea>
	</td></tr><tr><th class='prompt' align='right' valign='top'>$[Sign as Author]&nbsp;</th>
	<td><input class='inputbox commentauthorbox' type='text' name='author' value='\$Author' size='32' />".
	($EnableWebsiteField ? "<tr><th class='prompt' align='right' valign='top'>$[Website]&nbsp;</th>
	<td><input class='inputbox' type='text' name='website' value='' size='32' />" : ""). 
	(isset($EnablePostCaptchaRequired) ? "</td></tr><tr><th class='prompt' align='right' valign='top'><input type='hidden' name='captchakey' value='\$CaptchaKey' />
	Enter code: <em class='access'>\$Captcha</em></th><td><input type='text' name='\$CaptchaName' size='5' class='inputbox' /> " : "").
	($EnableAccessCode ? "</td></tr>
	<tr><th class='prompt' align='right' valign='top'>$[Enter code] <em class='access'>\$AccessCode</em></th>
	<td><input type='text' size='4' maxlength='3' name='access' value='' class='inputbox' /> "
	: "<input type='hidden' name='access' value='\$AccessCode' />").
	"<input class='inputbutton commentbutton' type='submit' name='post' value=' $[Post] ' />
	<input class='inputbutton commentbutton' type='reset' value='$[Reset]' /></td></tr></table><br /></form></div>".
	($EnableCommentFormCheck ? $CommentBoxCheckFmt : "")
	);

# date and time formats
SDV($JournalDateFmt,'%d %B %Y');
SDV($JournalTimeFmt,'%H:%M');

# journal and diary patterns as part of page name
SDV($JournalPattern,'/Journal$/');
SDV($DiaryPattern,'/Diary$/');

# comment authentication 
SDV($HandleAuth['comment'], 'read');

$HandleActions['comment'] = 'HandleCommentPost';

Markup('cbox','<links','/\(:commentbox(chrono)?(?:\\s+(\\S.*?))?:\)/e',
	"'<:block>'.Keep(str_replace(array('\$Chrono','\$PostTo','\$AccessCode','\$CaptchaValue'),
	array('$1','$2',RandomAccess(), CaptchaValueRelay()),
	FmtPageName(\$GLOBALS['CommentBoxFmt'],\$pagename)))");
        

Markup('dbox','<block','/\(:diarybox:\)/e',
	"'<:block>'.str_replace('\$AccessCode',RandomAccess(),
	FmtPageName(\$GLOBALS['DiaryBoxFmt'],\$pagename))");
        
if (preg_match($JournalPattern,$pagename) ||
		preg_match($DiaryPattern,$pagename)) {
			$GroupHeaderFmt .= '(:if auth edit:)(:diarybox:)(:if:)(:nl:)';
			if (!PageExists($pagename)) $DefaultPageTextFmt = '';
}

if ($action=='print' || $action=='publish')
	Markup('cbox','<block','/\(:commentbox(chrono)?(?:\\s+(\\S.*?))?:\)/','');    

# commentbox markup calls CaptcheValue(). If Captcha recipe is not installed function fails silently
function CaptchaValueRelay(){
	if (function_exists('CaptchaValue')) CaptchaValue();
}
function RandomAccess() {
  return rand(100,999);
}
# provide {$AccessCode} page variable:
$FmtPV['$AccessCode'] = RandomAccess(); 

function auditJP($MaxLinkCount, $req) {
	SDV($MaxLinkCount, 1);
	if (!($req['access'] && ($req['access']==$req['accesscode'])
		&& $req['post'])) return false;
	preg_match_all('/https?:/',$req['text'],$match);
	return (count($match[0])>$MaxLinkCount) ? false : true;
}

function HandleCommentPost($pagename, $auth) {
  global $JournalPattern, $DiaryPattern,
         $Author, $EnablePostCaptchaRequired, $IsPagePosted,
         $EnableRedirectToCommentPage, $PostToCommentPageFmt,
         $EnablePostToAnyPage, $EnablePostToGroupPage, $EnablePreventDirectives;
	$req = CBRequestArgs();
	
	if(auditJP('', $req)==false) Redirect($pagename);
	if($EnablePostCaptchaRequired AND !IsCaptcha()) Redirect($pagename);
	if (!$req['post'] || $req['text']=='') Redirect($pagename);
	if ($req['author']=='') $Author = 'anonymous';
	$currpage = $pagename; // set current page
	$currgroup = PageVar($pagename, '$Group'); 
	// handling of postto page
	if (!$req['postto']=='') { 
		$tgtpage = MakePageName($pagename, $req['postto']);
		$tgtgroup = PageVar($tgtpage, '$Group');
		$tgtname =  PageVar($tgtpage, '$Name');
		if($EnablePostToAnyPage==1) $pagename = $tgtpage;
		if($EnablePostToGroupPage==1 AND $tgtgroup==$currgroup ) 
			$pagename = $tgtpage; 
		else {
			foreach($PostToCommentPageFmt as $n ) {
				$n = FmtPageName($n, $pagename);
				if($n==$tgtpage) { 
					$tgt = 1;
					$pagename = $tgtpage;
					continue;
				}
			}
			if(!isset($tgt)) { 
				CBMessage("ERROR: No permission to post to $tgtpage");
				HandleBrowse($currpage); 
				exit;
			}
		}
	}
	if ($EnablePreventDirectives==1) {
		$req['text'] = preg_replace('/\\(:/', '(&#x3a;', $req['text']);
		$req['author'] = preg_replace('/\\(:/', '(&#x3a;', $req['author']);
	}
	$page = RetrieveAuthPage($pagename, $auth, true);
	$HandleCommentFunction = (preg_match($JournalPattern,$pagename)) ? 'Journal' :
		((preg_match($DiaryPattern,$pagename)) ? 'Diary'   : 'Message');
	$HandleCommentFunction = 'Handle' . $HandleCommentFunction . 'Post';
	$newpage = $page;
	$newpage['text'] = $HandleCommentFunction($pagename, $req, $page['text']);
	UpdatePage($pagename, $page, $newpage);
	if($EnableRedirectToCommentPage==1) Redirect($pagename);
	if ($IsPagePosted && $tgtpage) 
		CBMessage("Successful post to $tgtpage");
	HandleBrowse($currpage);
	exit;
}

function FormatDateHeading($txt,$datefmt,$fmt) {
	return str_replace($txt,strftime($datefmt,time()),$fmt);
}

## Journal entry
function HandleJournalPost($pagename, $req, $pagetext) {
   global $JournalDateFmt,$JournalTimeFmt,$JPItemStartFmt,$JPItemEndFmt,$JPDateFmt,$JPTimeFmt,
            $Author;
	SDV($JPDateFmt,'>>journaldate<<(:nl:)!!!!$Date');
	SDV($JPTimeFmt,"\n>>journalhead<<\n!!!!!&ndash; \$Time &ndash;\n");
	SDV($JPItemStartFmt,">>journalitem<<\n");
	SDV($JPItemEndFmt,"");
	$date = FormatDateHeading('$Date',$JournalDateFmt,$JPDateFmt);
	$time = $date . FormatDateHeading('$Time',$JournalTimeFmt,$JPTimeFmt);
	$entry = $time.$JPItemStartFmt.stripmagic($req['text']).$JPItemEndFmt;
	$text = (strstr($pagetext, $date)) ?
				str_replace($date, $entry, $pagetext) :
				"$entry\n>><<\n\n" . $pagetext;
	return $text;
}

## Diary entry
function HandleDiaryPost($pagename, $req, $pagetext) {
	global $JournalDateFmt,$DPDateFmt,$DPItemStartFmt,$DPItemEndFmt,$DPItemFmt;
	SDV($DPDateFmt,">>diaryhead<<\n!!!!\$Date ");
	SDV($DPItemStartFmt,"\n>>diaryitem<<\n");
	SDV($DPItemEndFmt,"");
	$date = FormatDateHeading('$Date',$JournalDateFmt,$DPDateFmt);
	$entry = $date.$DPItemStartFmt.stripmagic($req['text']).$DPItemEndFmt;
	$text = (strstr($pagetext, $date)) ?
			str_replace($date, $entry, $pagetext) :
			"$entry\n>><<\n\n" . $pagetext;
	return $text;
}

##  Comment entry
function HandleMessagePost($pagename, $req, $pagetext) {
   global $JournalDateFmt, $JournalTimeFmt, $MPDateFmt, $MPTimeFmt, $MPAuthorLink,
        $MPItemFmt, $MPItemStartFmt, $MPItemEndFmt, $MPDateTimeFmt, $MPAnchorFmt, $MPAnchorChronoFmt, 
        $CommentboxMessageFmt, $MultipleItemsPerDay, $Author, $PageUrl, $PostsPerPage,
        $EnablePostAuthorRequired, $EnableWebsiteField, $EnablePageBreaks;
    
	if ($EnableWebsiteField==1) {
		$website = $req['website'];
		$weblink = '&mdash; [-[[(http://)'.$website.']]-]';
		if($req['website']=='') $weblink = '';
	}
	else $weblink = '';
	$id = StringCount($pagename,">>messagehead<<")+1;
	if ($EnablePageBreaks==1) {
		$r = fmod($id-1,$PostsPerPage);
		if($r==0 && $id>1)  SDV($MPItemEndFmt, "\n>><<\n\n(:comment breakpage:)");
		else SDV($MPItemEndFmt, "\n>><<");
   }
	else SDV($MPItemEndFmt,"\n>><<");

	SDV($MPDateFmt,'>>messagedate<<(:nl:)!!!!$Date');
	SDV($MPTimeFmt,"(:nl:)>>messagehead<<\n!!!!!\$Author  &mdash; [-at \$Time-] $weblink \n");
	SDV($MPItemStartFmt,">>messageitem<<\n");
	SDV($MPDateTimeFmt,"(:nl:)>>messagehead<<\n!!!!!\$Author  &mdash;  [-\$Date, \$Time-] $weblink \n");
	SDV($MultipleItemsPerDay,0); # set to 1 to have date above for multiple entries per day
	SDV($MPAuthorLink, 1); # set to 0 to disable author name as link
	SDV($MPAnchorFmt,"(:commentbox:)");
	SDV($MPAnchorChronoFmt,"(:commentboxchrono:)");
	$name = $req['author'];
	if ($req['author']=='') $req['author'] = 'anonymous';
	# disable anonymous posts, but this looses also any message content:
	# if($EnablePostAuthorRequired == 1 && $name=='') Redirect($pagename);
	if($name=='') $name = 'anonymous';
	else $name = ($MPAuthorLink==1) ? '[[~' . $name . ']]' : $name;
	if ($MultipleItemsPerDay) {
		$date = FormatDateHeading('$Date',$JournalDateFmt,$MPDateFmt);
		$entry = '[[#comment'.$id.']]';
		$entry .= str_replace('$Author',$name,
					FormatDateHeading('$Time',$JournalTimeFmt,$MPTimeFmt));
	} else {
			$date = '';
			$entry = '[[#comment'.$id.']]';
			$entry .= FormatDateHeading('$Date',$JournalDateFmt,
						str_replace('$Author',$name,
						FormatDateHeading('$Time',$JournalTimeFmt,$MPDateTimeFmt)));
	}
	$entry.= $MPItemStartFmt.stripmagic($req['text']).$MPItemEndFmt;
	$order= $req['order'];
	if ($order=='') { # order is not chrono, latest top
		if (strstr($pagetext,$MPAnchorFmt)) {
			$pos = strpos($pagetext,$MPAnchorFmt);
			$len = strlen($MPAnchorFmt);
			$before = substr($pagetext,0,$pos+$len)."\n";
			$after  = substr($pagetext,$pos+$len);
		}
		else {
			$before = '';
			$after  = $pagetext;
		}
		$entry = "$date$entry";
		$after = ($MultipleItemsPerDay && strstr($after, $date)) ?
					str_replace($date, $entry, $after) : "$entry$after";
   }
	else { # order is chrono, latest last
		$entry .= "\n";
		if (strstr($pagetext,$MPAnchorChronoFmt)) {
			$pos = strpos($pagetext,$MPAnchorChronoFmt);
			$before = substr($pagetext,0,$pos);
			$after  = substr($pagetext,$pos);
		}
		else {
			$before = $pagetext;
			if ($before[strlen($before)-1]!='\n') $before .="\n";
			$after  = '';
		}
		$before .= ($MultipleItemsPerDay && strstr($before, $date)) ?
						substr($entry,1) : "$date$entry";
	}
	return "$before\n$after";
}

# add page variable {$PostCount},
# counts message items per page
$FmtPV['$PostCount'] = 'StringCount($pn,">>messagehead<<")';
function StringCount($pagename,$find) {
	$page = ReadPage($pagename, READPAGE_CURRENT);
	$n = substr_count($page['text'], $find);
	if ($n==0) return '';  #suppressing 0
	return $n;
}

## get arguments from POST or GET
function CBRequestArgs ($req = NULL) {
	if (is_null($req)) $req = array_merge($_GET, $_POST);
	foreach ($req as $key=>$val) {
   	if(is_array($val))
   		foreach($value as $k=>$v)
   			$req[$key][$k] = htmlspecialchars(stripmagic($v),ENT_NOQUOTES);
		else $req[$key] = htmlspecialchars(stripmagic($val),ENT_NOQUOTES);
	}
	return $req;
} 

## display message
function CBMessage($msg) { 
	global $MessagesFmt;
	$MessagesFmt[] = "<div class='wikimessage'>$[$msg]</div>";
} //}}}
//EOF