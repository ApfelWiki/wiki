<?php

/***************************************************************************
 * wikilog.php, version 0.3.10 requires PmWiki 2.0.x                        *
 * This is a calendar display and wikilog macro for the PmWiki engine      *
 * Copyright (c) 2003, 2004 John Rankin john.rankin@affinity.co.nz         *
 *                                                                         *
 * To use it, create a GroupHeader page that says (:wikilog:)              *
 * Make sure you press 'Return' at the end of the line, then Save.         *
 * To override the default home page name, write (:wikilog MagicWord:)     *
 * {$WikilogTitle} returns the long form of a date-style page title        *
 *                                                                         *
 * From some other group, you can list the latest entries using the        *
 * (:wikilognews Group/HomePage:) markup, where 'Group is the name of      *
 * the wikilog group and HomePage is the name of the home page. If         *
 * omitted, HomePage defaults to Group.                                    *
 *                                                                         *
 * The calendar display is copied from a free software program             *
 * written by Stephan Uhlmann <su@su2.info>                                *
 ***************************************************************************/
/***************************************************************************
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

// =========================================================================
// Configuration
// =========================================================================

// This configures how weekdays are presented.
// 0 - american style, Sun to Sat
// 1 - european style, Mon to Sun
SDV($calendar_weekstyle,1);

// This configures how dates are presented.
// 0 - american style, 11/17/2002
// 1 - european style, 17.11.2002
// 2 - international style, 2002-11-17
SDV($calendar_datestyle,1);
SDV($space_date_titles,1);
SDV($SpaceDateString,'-');

// How many months relative to the current one to start the calendar.
// Can be a negative (going back) or a positive (going ahead) number.
// The value zero (0) starts the calendar with the current month.
SDV($calendar_months_start,0);

// How many months altogether to show on the calendar.
SDV($calendar_months_number,2);

// How many months after we should wrap and start a new row.
SDV($calendar_month_wrap,3);

// Whether or not to display current entries at the bottom of the calendar.
// Enter 'false' to omit the current period's entries.
SDV($display_log_entries,true);

// Whether to display current entries in ascending or descending order.
// Enter 'false' to show the oldest first.
SDV($newest_first,true);

// Whether to display a summary or full calendar on individual entry pages.
// Enter 'false' to include the calendar on every page.
SDV($summary_only,true);

// This is the default home of each collection (Group) of date pages.
SDV($wikilog_home_page,FmtPageName('$Group',$pagename));

// Allow page names to be all numerics.
$CalendarPattern = "[0-9]{8}";
$WikiDateCreateFmt = 
      "<a class='nonexistent-date' href='\$PageUrl?action=edit'>\$LinkText</a>";

## process datelinks
Markup('datelink','>inline',
    "/\[\[($GroupPattern(?:[\/.])$CalendarPattern)\|(.*?)\]\]/e",
    "Keep(MakeDateLink(\$pagename,'$1','$2'),'L')");

// Whether to display non-existent date entries in the calendar as day or day?
// Enter 'true' to use the wiki '?' convention for non-existent entries
SDV($day_as_wiki,false);

// Whether to display a list or list and Publish button on wikilog news pages
// Enter 'true' to include a Publish button after the news list
SDV($enable_publish,false);

// Set depending on whether you need [[Page?logdate= or [[Page&logdate=
SDV($url_connector,"?");

// Month and day names in chosen language
SDV($language,'de');
SDV($scriptlocation,'scripts');
if (file_exists("cookbook/wikilog-i18n-$language.php"))
    include_once("cookbook/wikilog-i18n-$language.php");
elseif (file_exists("cookbook/wikilog/wikilog-i18n-$language.php"))
    include_once("cookbook/wikilog/wikilog-i18n-$language.php");
else include_once("cookbook/wikilog/wikilog-i18n-en.php");

// Publish button format
SDV($PublishCalendarFmt,
  "<form class='calendarpub' target='_blank' action='\$ScriptUrl' method='get'>
    <input type='hidden' name='pagename' value='\$PageName' />
    <input type='hidden' name='action' value='publish' />
    <input type='hidden' name='logdate' value='\$Year\$Month' />
    <input type='radio' name='order' value='d' checked='checked' />$sequence[0]
    <input type='radio' name='order' value='a' />$sequence[1]
    <input type='submit' value='$publish' /></form>");
/*
SDV($PublishCalendarFmt,"<form class='calendarpub' target='_blank' action='\$PageUrl' method='get'>
    <input type='hidden' name='action' value='publish' />
    <input type='hidden' name='logdate' value='\$Year\$Month' />
    <input type='radio' name='order' value='d' checked='checked' />$sequence[0]
    <input type='radio' name='order' value='a' />$sequence[1]
    <input type='submit' value='$publish' /></form>");
*/
// (:wikilogbox:) to post short stories to today's page
SDV($CalendarBoxFmt,"<div id='story'><h5>\$CalendarTitle</h5>
    <form action='\$PageUrl' method='post'>
        <input type='hidden' name='pagename' value='\$PageName' />
        <input type='hidden' name='action' value='wikilog' />
        <input type='hidden' name='order' value='\$Chrono' />
        <table width='95%'><tr>
        <td class='prompt'>Headline</td>
        <td><input type='text' name='headline' value='' size='45' /></td></tr>
        <tr><td class='prompt'>Story</td>
        <td><textarea name='text' rows='7' cols='45' wrap='virtual'></textarea></td></tr>
        <tr><td class='prompt'>Author</td>
        <td><input type='text' name='author' value='' size='32' />
        <input type='submit' name='post' value=' Post ' />
        <input type='reset' value='Reset' /></td></tr></table></form></div>");
if ($action == 'wikilog') 
    SDV($HandleActions['wikilog'],'HandleWikilogPost');
else if ($action=='print' || $action=='publish') {
    Markup('wbox','>links','/\(:wikilogbox(chrono)?\s*(.*?):\)/','');
    Markup('pubcal','>title','/\(:publishcalendar:\)/','');
} else
    Markup('wbox','>links','/\(:wikilogbox(chrono)?\s*(.*?):\)/e',
        "'<:block>'.str_replace('\$CalendarTitle',SetCalendarTitle('$2'),
            str_replace('\$Chrono','$1',
            FmtPageName(\$GLOBALS['CalendarBoxFmt'],\$pagename)))");
# load the wikilog stylesheet
$HTMLHeaderFmt[] = 
 "<link rel='stylesheet' href='\$FarmPubDirUrl/css/wikilog.css' type='text/css' />";
/*
You can define different colours for the dates with or without an entry in the
wikilog stylesheet. The class attributes for the TD tag are:
calendar-today-entry    : current date and there is an entry
calendar-today-noentry  : current date but there is no entry
calendar-entry          : any other date with an entry
calendar-noentry        : any other date without an entry
calendar-blank      : empty cells at the start and end of month

The class attribute for date links is:
nonexistent-date        : wikilink without an entry

*/
// ======================================================================

$logdate = isset($_GET['logdate']) ? $_GET['logdate'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : '';
if ($order!='') SDV($HandleActions['publish'],'HandleCalendarPublish');
Markup('wikilog','<title',"/^\(:wikilog(\s+($NamePattern))?:\)$/e",
    "view_calendar(\$pagename,'$2')");
Markup('wikinews','directives',
    "/^\(:wikilognews\s+($GroupPattern)(?:\/($NamePattern))?:\)$/e",
    "view_calendar_list(\$pagename,'$1','$2')");
Markup('wikidate','>wikilog','/\(:\wikilogtitle:\)/e','show_date($pagename)');
Markup('pubcal','>title','/\(:publishcalendar:\)/e',
   "Keep(FmtPageName(\$GLOBALS['PublishCalendarFmt'],\$pagename))");
   
Markup('wikinews2','directives',
    "/^\(:wikilognews2\s+($GroupPattern)(?:\/($NamePattern))?:\)$/e",
    "view_calendar_list2(\$pagename,'$1','$2')");      
   
$Name = FmtPageName('$Name',$pagename);
if (IsDate($Name)) $SpacedName = preg_replace('/(..)(..)$/',
        "$SpaceDateString$1$SpaceDateString$2",$Name);
function view_calendar_list2($pagename,$homegroup,$hometitle) {
	global $calendar_months_start, $calendar_months_number, $enable_publish;
	global $wikilog_home_page, $logdate, $PublishCalendarFmt, $newest_first, $noentries;
	$hometitle = $homegroup;
	$hometitle = "$homegroup/$hometitle";
	if ($homegroup == "Nachrichten"){
		$calendar_months_number=2;
		$calendar_months_start=0;
		$newest_first = false;
	}
	if ($homegroup == "Termine"){
		$calendar_months_number=2;
		$calendar_months_start=0;
	}
	// start configured number of months before/ahead
	$startingdate = $logdate."16";
	if (IsDate($startingdate)) {
		$year = substr($startingdate,0,4);
		$month= substr($startingdate,4,2);
	} else {
		$year = date("Y");
		$month= date("m");
		}
	$startingtime = mktime(0,0,0,$month,16,$year);

	// generate the calendar navigation bar
	$r="";
	// generate the calendar
	$itime=$startingtime+$calendar_months_start*2592000;
	$i=0;
	$d="";

	while ($i<$calendar_months_number) {
		$l=list_entries2(strftime("%m",$itime),strftime("%Y",$itime),
							$homegroup);
			
		
		//Wenn keine Eintraege
		if (stristr($l,"<li>$noentries"))
		{
			$l="";
			if($homegroup=="Nachrichten")
			{	$itime=$itime-2*2592000;
			}
		} else {
		if ($newest_first) $d = $l.$d; else $d .= $l;
		break;}

		
		
	// +1 month
	// It's actually 30 days which could break if displaying a lot of months
	// but it should be ok when displaying only one or two years at a time
		$itime=$itime+2592000;
		$i++;
	}

	// generate the chrono list
	$r.= "$d";
	if ($enable_publish) {
    	$PublishCalendarFmt = str_replace('$Month',$month,
	       str_replace('$Year',"$homegroup.$year",$PublishCalendarFmt));
        $r .= '(:publishcalendar:)<p />'."\n";
    }
	return "$r";
}


function list_entries2($month, $year, $group) {
	global $pagename,$monthnames,$today,$noentries,$below,$newest_first;
	global $wikilog_startseite;

	$r="";
	$title = FmtPageName('$Name',$pagename);
	$prefix = $group . "/" . $year . $month;
	$todays_time=mktime(0,0,0,date("m"),date("d"),date("Y"));
	$last_day_of_month=strftime("%d",mktime(0,0,0,$month+1,0,$year));
	$zaehler = 0;
	$rueck = 31;

	for ($k=1;$k<=$last_day_of_month;$k++) {
		
		if($group == "Nachrichten")
		{	
			if($rueck==0)
				{break;}
			$i = $rueck--;
			
		}
		if($group == "Termine")
		{
			if ( $k < date("j") & $month==date("n"))
		 	{ 
		 		$k=date("j");
		 		$i=$k; 
		 	} else
		 	{ $i = $k;}
		 	
		}
		
		if ($i<10) { $day="0".$i; } else { $day=$i; }
		$entrydate = $year . $month . $day;
		$entryname= $prefix . $day;
		if (PageExists($entryname)) {
			
			$zaehler++;
			if ($zaehler == 3)
				{break;}
		
			$line="<em>[[". $entryname . " | ";
		if (abs($todays_time - mktime(0,0,0,$month,$i,$year)) < 86400) {
				$line.=$today;
			} else {
				$line.=shortdate($year,$month,$day);
			}
			$line.="]]:</em> ";
			if ($entrydate == $title) {
				$firstpara = "<b>$below</b>";
			} 
			else 
			{
				
				$line.="<ul class='loglist'>";
				 $snippet = ReadPage($entryname);
				 preg_match_all('/^(?:!!)(?![!*])\s?(.*)/m',$snippet['text'],$text, PREG_PATTERN_ORDER);
				if(count($text[1])){
					foreach ($text[1] as $p){
						$line .= "<li>".$p."</li>";
					}
				}
				else
				{
				
				$snippet['text'] .= "\n";
					$firstpara = substr($snippet['text'], 0, 
					  strpos($snippet['text'], "\n"));
					$firstpara = preg_replace("/^[#*!:]+\s*/","", 
					  htmlspecialchars($firstpara,ENT_NOQUOTES));
			$firstpara = preg_replace("/`\\..*?$/","...",$firstpara);
			$firstpara = preg_replace("/\\(:(redirect)\s+(.*):\\)/",
				"''$1s to page [[$2]]''", $firstpara);
			$firstpara = preg_replace("/\\[\\=(.*?)\\=\\]/se",
				"Keep(PSS('$1'))",$firstpara);
				$firstpara="<li>".$firstpara."</li>";
				}
		    
			
			}
			$line.=$firstpara . "</ul>\n";
			if ($newest_first) $r=$line.$r; else $r.=$line;
		}

	}
	if ($r == "") {
		$r = "<li>$noentries ".$monthnames[$month-1]." $year.</li>\n";
	}
	return $r;
}

function view_calendar($pagename,$homepage) {
        global $calendar_months_start, $calendar_months_number,$calendar_month_wrap;
        global $wikilog_home_page, $summary_only, $logdate, $PublishCalendarFmt;
        global $newest_first, $display_log_entries, $enable_publish;
        global $group;

    $group = FmtPageName('$Group',$pagename);
        $title = FmtPageName('$Name',$pagename);
        if ($homepage != '') $wikilog_home_page = $homepage;

        // check to see whether to display a summary only
        if (IsDate($title) && ($summary_only==true)) {
                $r = "</p><p class='datetrail'>".adjacent_entries($group,$title)."</p>\n";
                return $r.set_page_title($title)."<h3>".longdate($title)."</h3><p>";
        }

        // start configured number of months before/ahead
        $startingdate = $logdate."16";
        if (IsDate($startingdate)) {
                $year = substr($startingdate,0,4);
                $month= substr($startingdate,4,2);
        } else {
                if (IsDate($title)) {
                        $year = substr($title,0,4);
                        $month= substr($title,4,2);
                } else {
                        $year = date("Y");
                        $month= date("m");
                }
        }
        $startingtime = mktime(0,0,0,$month,16,$year);

        // generate the calendar navigation bar
        $r = calendar_nav_bar($pagename,$startingtime);

        // generate the calendar
        $r.="<div id='wikilog'><table class='calendar-outer'><tr>\n";
        $itime=$startingtime+$calendar_months_start*2592000;
        $i=0;
        $d="";

        while ($i<$calendar_months_number) {
                $r.="<td class='calendar-outer'>";
                $r.=calendar_month(strftime("%m",$itime),strftime("%Y",$itime),
                                                        $group);
                $r.="</td>\n";
                if ($display_log_entries==true) { 
                        $l=list_entries(strftime("%m",$itime),
                                strftime("%Y",$itime),$group);
                        if ($newest_first) $d = $l.$d; else $d .= $l;
                }
    // +1 month
    // It's actually 30 days which could break if displaying a lot of months
    // but it should be ok when displaying only one or two years at a time
                $itime=$itime+2592000;
                $i++;
                if (($i%$calendar_month_wrap==0) && ($i!=12)) $r.="</tr><tr>";
        }

        $r.="</tr></table></div>\n";

		if ($group != "Nachrichten"){
        // generate the chrono list
        if ($display_log_entries==true) {
        $r.= home_link($wikilog_home_page) . "<ul class='loglist'>\n$d</ul>";
        if ($enable_publish) {
                $r.= '(:publishcalendar:)<p />'."\n";
                $PublishCalendarFmt = str_replace('$Month',$month,
                        str_replace('$Year',$year,$PublishCalendarFmt));
        }
        }
		}

        // translate the page title
        if (IsDate($title)) {
                $r.= set_page_title($title) . "<h3>" . longdate($title) . "</h3>";
        }
        return "$r<p>";
}

function set_page_title($title) {
    global $SpaceDateString;
        return '(:title '. 
preg_replace('/(..)(..)$/',"$SpaceDateString$1$SpaceDateString$2",$title).':)';
}

function view_calendar_list($pagename,$homegroup,$hometitle) {
        global $calendar_months_start, $calendar_months_number, $enable_publish;
        global $wikilog_home_page, $logdate, $PublishCalendarFmt, $newest_first;
        if ($hometitle=='') $hometitle = $homegroup;
        $hometitle = "$homegroup/$hometitle";

        // start configured number of months before/ahead
        $startingdate = $logdate."16";
        if (IsDate($startingdate)) {
                $year = substr($startingdate,0,4);
                $month= substr($startingdate,4,2);
        } else {
                $year = date("Y");
                $month= date("m");
                }
        $startingtime = mktime(0,0,0,$month,16,$year);

        // generate the calendar navigation bar
        $r = calendar_nav_bar($pagename,$startingtime);

        // generate the calendar
        $itime=$startingtime+$calendar_months_start*2592000;
        $i=0;
        $d="";

        while ($i<$calendar_months_number) {
                $l=list_entries(strftime("%m",$itime),strftime("%Y",$itime),
                                                        $homegroup);
                if ($newest_first) $d = $l.$d; else $d .= $l;
        // +1 month
        // It's actually 30 days which could break if displaying a lot of months
        // but it should be ok when displaying only one or two years at a time
                $itime=$itime+2592000;
                $i++;
        }

        // generate the chrono list
        $r.= home_link($hometitle) . "<ul class='loglist'>\n$d</ul>";
        if ($enable_publish) {
        $PublishCalendarFmt = str_replace('$Month',$month,
               str_replace('$Year',"$homegroup.$year",$PublishCalendarFmt));
        $r .= '(:publishcalendar:)<p />'."\n";
    }
        return "$r<p>";
}

function home_link($wikiloghome) {
    global $WikilogHomeText, $HTMLVSpace;
        $WikilogHomeLink= ($WikilogHomeText=='') ? "[[$wikiloghome]]" :
                        "[[$wikiloghome | $WikilogHomeText]]";
    return "$HTMLVSpace<p class='wikiloghome'>$WikilogHomeLink:</p>";
}

function calendar_month($month,$year,$group) {
        global $pagename, $monthnames, $shortdaynames, $calendar_weekstyle;

        $prefix = $group . "/" . $year . $month;
        $todays_time=mktime(0,0,0,date("m"),date("d"),date("Y"));
        $last_day_of_month=strftime("%d",mktime(0,0,0,$month+1,0,$year));
        $r="<table class='calendar-inner'>";

        // header with month and year
        $r.="<caption>". $monthnames[$month-1] . " ". $year . "</caption>\n";

        // weekday names
        $r.="<tr>";
        for ($i=$calendar_weekstyle;$i<7+$calendar_weekstyle;$i++)
        {
                $r.="<th>" . $shortdaynames[$i] . "</th>";
        }
        $r.="</tr>\n";

        $count=0;
        // pre-padding
        $r.="<tr>";
        for ($i=0;$i<(strftime("%w",mktime(0,0,0,$month,1,$year))-$calendar_weekstyle+7)%7;$i++)
        {
                $r.="<td class='calendar-blank'>&nbsp;</td>";
                $count++;
        }

        // days
        for ($i=1;$i<=$last_day_of_month;$i++) {

                if ($count%7==0) $r.="</tr>\n<tr>";
                if ($i<10) { $day="0".$i; } else { $day=$i; }
                $entryname= $prefix . $day;
                $r.="<td class='calendar-";
                if (abs($todays_time - mktime(0,0,0,$month,$i,$year)) < 86400) {
                        $r.="today-";
                }
                if (PageExists($entryname)) {
                        $r.="entry'><b>[[". $entryname . "|" . $day . "]]</b>";
                } else {
                        $r.="noentry'>[[". $entryname . "|" . $day . "]]";
                }
                $r.="</td>";
                $count++;
        }

        // post-padding
        while ($count%7!=0) {
                $r.="<td class='calendar-blank'>&nbsp;</td>";
                $count++;
        }
        return $r."</tr></table>\n";
}

function list_entries($month, $year, $group) {
        global $pagename,$monthnames,$today,$noentries,$below,$newest_first;

        $r="";
        $title = FmtPageName('$Name',$pagename);
        $prefix = $group . "/" . $year . $month;
        $todays_time=mktime(0,0,0,date("m"),date("d"),date("Y"));
        $last_day_of_month=strftime("%d",mktime(0,0,0,$month+1,0,$year));

        for ($i=1;$i<=$last_day_of_month;$i++) {

                if ($i<10) { $day="0".$i; } else { $day=$i; }
                $entrydate = $year . $month . $day;
                $entryname= $prefix . $day;
                if (PageExists($entryname)) {
                        $line="<li>[[". $entryname . " | ";
                if (abs($todays_time - mktime(0,0,0,$month,$i,$year)) < 86400) {
                                $line.=$today;
                        } else {
                                $line.=shortdate($year,$month,$day);
                        }
                        $line.="]]: ";
                        if ($entrydate == $title) {
                                $firstpara = "<b>$below</b>";
                        } else {
                                $snippet = ReadPage($entryname);
                                $snippet['text'] .= "\n";
                                $firstpara = substr($snippet['text'], 0, 
                                  strpos($snippet['text'], "\n"));
                                $firstpara = preg_replace("/^[#*!:]+\s*/","", 
                                  htmlspecialchars($firstpara,ENT_NOQUOTES));
                $firstpara = preg_replace("/`\\..*?$/","...",$firstpara);
                $firstpara = preg_replace("/\\(:(redirect)\s+(.*):\\)/",
                        "''$1s to page [[$2]]''", $firstpara);
                $firstpara = preg_replace("/\\[\\=(.*?)\\=\\]/se",
                    "Keep(PSS('$1'))",$firstpara);
                        }
                        $line.=$firstpara . "</li>\n";
                        if ($newest_first) $r=$line.$r; else $r.=$line;
                }

        }
        if ($r == "") {
                $r = "<li>$noentries ".$monthnames[$month-1]." $year.</li>\n";
        }
        return $r;
}

function IsDate($title) {
        if (is_numeric($title) && strlen($title) == 8) {
                $year = substr($title,0,4);
                $month= substr($title,4,2);
                $day  = substr($title,6,2);
                return checkdate($month, $day, $year);
        } else {
                return false;
        }
}

function longdate($title) {
        global $calendar_datestyle, $monthnames, $longdaynames;
        $year = substr($title,0,4);
        $month= substr($title,4,2);
        $day  = substr($title,6,2);
        if ($day[0] == "0") { $day = $day[1]; }
        $longmonth = $monthnames[$month-1];
        $r = ($calendar_datestyle == 0) ? ", $longmonth $day, " : 
                   ", $day $longmonth ";        $dayname=$longdaynames[strftime("%w",mktime(0,0,0,$month,$day,$year))];
        return "$dayname$r$year";
}

function calendar_nav_bar($pagename,$startingtime) {
    global $calendar_months_number;
        $currentmonth= date("Y") . date("m");
        $lasttime =$startingtime-$calendar_months_number*2592000;
        $lastmonth= strftime("%Y",$lasttime) . strftime("%m",$lasttime);
        $nexttime =$startingtime+$calendar_months_number*2592000;
        $nextmonth=strftime("%Y",$nexttime) . strftime("%m",$nexttime);
        $r = "</p><p class='datetrail'>&laquo; ";
        if ($currentmonth < $lastmonth)
                $r.=calendar_nav($pagename,$currentmonth) . " &middot; ";
        $r.=calendar_nav($pagename,$lastmonth) . " &middot; " . 
            calendar_nav($pagename,$nextmonth);
        if ($currentmonth > $nextmonth)
                $r.=" &middot; " . calendar_nav($pagename,$currentmonth);
        return $r . " &raquo;</p>\n";
}

function calendar_nav($pagename,$yearmo) {
        global $calendar_months_number, $monthnames, $period, $url_connector;
        $year = substr($yearmo,0,4);
        $r = "[[$pagename$url_connector"."logdate=$yearmo | ";
        $r.= $monthnames[substr($yearmo,4,2)-1] . " " . $year;
        return ($calendar_months_number == 1 ) ? "$r]]" : "$r $period]]";
}

function adjacent_entries($group, $title) {
        global $wikilog_home_page, $WikilogHomeText, $url_connector;
        $year =substr($title,0,4);
        $month=substr($title,4,2);
        $day  =substr($title,6,2);
        $prefix = $group . "/" . $year . $month;
        $last_day_of_month=strftime("%d",mktime(0,0,0,$month+1,0,$year));
        $prev="";
        for ($i=1;$i<$day;$i++) {
                if ($i<10) { $prevday="0".$i; } else { $prevday=$i; }
                $entryname = $prefix . $prevday;
                if (PageExists($entryname)) {
                        $prev = "&laquo; [[$entryname | " .
                            shortdate($year,$month,$prevday) . "]] | ";
                }
        }
        $next="";
        for ($i=$last_day_of_month;$i>$day;$i--) {
                if ($i<10) { $nextday="0".$i; } else { $nextday=$i; }
                $entryname = $prefix . $nextday;
                if (PageExists($entryname)) {
                        $next = " | [[" . $entryname . " | ";
                        $next.= shortdate($year,$month,$nextday) . "]] &raquo;";
                }
        }
        $home_text = ($WikilogHomeText=='') ? $wikilog_home_page :
                        $WikilogHomeText;
        $home  = "[[$wikilog_home_page$url_connector"."logdate=$year$month | $home_text]]";
        return $prev . $home . $next;
}

function shortdate($year, $month, $day) {
        global $calendar_datestyle;
    switch ($calendar_datestyle) {
        case 0:
            return "$month/$day/$year";
        case 1:
            return "$day.$month.$year";
        case 2:
            return "$year-$month-$day";
    }
}

function show_date($page) {
        global $default_date_text;
        $title = FmtPageName('$Name',$page);
        return (IsDate($title)) ? "$default_date_text: ".longdate($title) : $default_date_text;
}

function MakeDateLink($pagename,$ref,$btext) {
        global $LinkPageCreateFmt,$WikiDateCreateFmt,$day_as_wiki;
        if ($day_as_wiki==true) 
                return MakeLink($pagename,$ref,$btext);
        $hold = $LinkPageCreateFmt;
        $LinkPageCreateFmt = $WikiDateCreateFmt;
        $r = MakeLink($pagename,$ref,$btext);
        $LinkPageCreateFmt = $hold;
        return $r;
}

function HandleCalendarPublish($pagename) {
        global $HandlePublishFmt,$GCount,$logdate,$order,$noentries;
        global $calendar_months_start,$calendar_months_number,$monthnames;
#       global $LastModified,$Now,$TimeFmt;
        if (preg_match('/(.+)\\.([0-9]+)/',$logdate,$match)) {
                $group = $match[1];
                $logdate = $match[2];
        } else
                $group = FmtPageName('$Group',$pagename);
        $year = substr($logdate,0,4);
        $month= substr($logdate,4,2);
        $startingtime = mktime(0,0,0,$month,16,$year);
        $itime=$startingtime+$calendar_months_start*2592000;
        $i=0;
        while ($i<$calendar_months_number) {
                $imonth = strftime("%m",$itime);
                $iyear  = strftime("%Y",$itime);
                $prefix = $group . "/" . $iyear . $imonth;
                $last_day_of_month=
                        strftime("%d",mktime(0,0,0,$imonth+1,0,$iyear));
                $entries = 0;
                for ($j=1;$j<=$last_day_of_month;$j++) {
                        if ($j<10) { $day="0".$j; } else { $day=$j; }
                        $entryname = $prefix . $day;
                        if (PageExists($entryname)) {
                                $pagearray[] = $iyear . $imonth . $day;
                                $entries++;
                        }
                }
                if ($entries == 0) $pagearray[] = $iyear . $imonth . "00";
                $itime=$itime+2592000;
                $i++;
        }
        if ($order=='d') rsort($pagearray);
        for($i=0;$i<count($pagearray);$i++) {
                if (substr($pagearray[$i],6,2)=='00') 
                        $PublishList[] = "markup:!$noentries " .
                                $monthnames[substr($pagearray[$i],4,2)-1] .
                                " " . substr($pagearray[$i],0,4);
                else {
                        $entrylong = longdate($pagearray[$i]);
                        $PublishList[] = 
                "markup:![[$group.$pagearray[$i] | $entrylong]]";
                        $PublishList[] = 
                "function:PrintThisPage $group.$pagearray[$i]";
                }
        }
        $GLOBALS['PublishList'] = $PublishList;
        $GCount = 0;
        PrintFmt($pagename,$HandlePublishFmt);
}

function SetCalendarTitle($text) {
  global $DefaultCalendarTitle;
  return ($text=='') ? $DefaultCalendarTitle : $text;
}

function HandleWikilogPost($pagename) {
   global $_POST,$JournalTimeFmt;
   SDV($JournalTimeFmt,'%H:%M');
   $name = @$_POST['author'];
   $name = ($name=='') ? 'anonymous' : '[[~' . $name . ']]';
   $now = time();
   $_POST['text']= "!!!!".$_POST['headline']."\n".
                   $_POST['text']."\n\n=>'-Posted at ".
                   strftime($JournalTimeFmt,$now)." by $name-'";
   $todayspage = FmtPageName('$Group',$pagename)."/".strftime("%Y%m%d",$now);
   if (PageExists($todayspage)) {
      $page = RetrieveAuthPage($todayspage, "edit");
      $_POST['text'] = (@$_POST['order']=='x') ? 
                        $_POST['text'] . "\n\n" . $page['text'] : 
                        $page['text'] . "\n\n" . $_POST['text'];
   }
   HandleEdit($todayspage);
   exit;
}

?>