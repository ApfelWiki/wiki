<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PITS (PmWiki Issue Tracking System), you can 
    redistribute it and/or modify it under the terms of the GNU General 
    Public License as published by the Free Software Foundation; either 
    version 2 of the License, or (at your option) any later version.  

    This file defines code needed for the PmWiki Issue Tracking System 
    (PITS).  The code is highly specialized towards the needs of
    pmwiki.org, and may not be useful (or even usable) anywhere else,
    but enough people have asked for it that I'm making it available
    in the Cookbook and wishing people the best of luck with it.

    Eventually there will likely be a "supported" version of this
    capability, and the supported version *may* even use the code below :-).
    But until Pm clears a few other items off of his to-do list, he
    may have limited ability to provide detailed support.  (Offers of
    financial support have been known to significantly affect his
    development/support priorities, however. :)

    Issues with PITS can, of course, be registered in PITS on
    pmwiki.org (http://www.pmwiki.org/PITS).

    Okay, enough disclaimers, here's the code already.
*/

$PitsCategories = array('', 'Bug', 'Feature' => 'Feature/Change Request',
  'Documentation', 'Cookbook', 'CoreCandidate','PHP Compatibility', 'Other');

markup('pitsform','inline','/\\(:pitsform:\\)/e',"Keep(PitsForm(\$pagename))");
markup('pitslist','directives','/\\(:pitslist\\s*(.*?):\\)/e',
  "FmtPitsList('',\$pagename,array('q'=>PSS('$1')))");
markup('pits','directive',
  '/^(Summary|Created|Status|Category|From|Assigned|Version|OS|Priority):.*/',
  "<:block><div class='pits'>$0</div>");

## PitsForm() generates the form for entering a new issue.  Note that
## once an issue has been created, it's a normal wikipage and is edited
## according to the normal editing code (i.e., there's no form-based
## editing yet).
function PitsForm($pagename) {
  global $PitsCategories;
  $out[] = "<form method='post'>
    <input type='hidden' name='action' value='postpits' />
    <table>
      <tr><td class='pitsfield'>Author:</td>
        <td><input type='text' name='author' value='\$Author' /></td></tr>
      <tr><td class='pitsfield'>Summary:</td>
        <td><input type='text' name='summary' size='60'/></td></tr>
      <tr><td class='pitsfield'>Category:</td>
        <td><select name='category'>";
  foreach($PitsCategories as $k=>$v) {
    $x = is_string($k) ? $k : $v;
    $out[] = "<option value='$x'>$v</option>";
  }
  $out[] = "</select></td></tr>
      <tr><td class='pitsfield'>Priority:</td>
      <td>Low";
  for($i=1;$i<=5;$i++) 
    $out[] = " <input type='radio' name='priority' value='$i' />";
  $out[] = "High</td></tr>
      <tr><td class='pitsfield'>PmWiki Version:</td>
        <td><input type='text' name='version' /></td></tr>
      <tr><td class='pitsfield'>OS/Webserver/<br />PHP Version:</td>
        <td><input type='text' name='os' /></td></tr>
      <tr><td class='pitsfield' valign='top'>Description:</td>
        <td><textarea name='description' cols='60' rows='15'></textarea>
        </td></tr></table>
      <div align='center'><input type='submit' value='Submit new issue' accesskey='s' /></div>
      </form>";
  return FmtPageName(implode('',$out),$pagename);
}

$HTMLStylesFmt[] = "
  .pitsfield { text-align:right; font-weight:bold; }
  table.pits th a { text-decoration:none; }
  table.pits th { background-color:#eeeeee; }
";

include_once("$FarmD/scripts/author.php");

if ($action=='postpits') { 
  Lock(2);
  foreach(ListPages('/^PITS\\.\\d/') as $i) 
    $issue = max(@$issue,substr($i,5));
  $pagename = sprintf("PITS.%05d",@$issue+1);
  $action = 'edit';
  $_REQUEST['post'] = 1;
  $CreateTime = strftime('%Y-%m-%d %H:%M',$Now);
  $EditMessageFmt = "<p class='vspace'>Please review and make any edits 
    to your issue below, then press 'Save'.</p>";
  $_POST['csum'] = $_REQUEST['summary'];
  $_POST['text'] = "
Summary: {$_REQUEST['summary']}
Created: $CreateTime
Status: Open
Category: ".implode(' ',(array)@$_REQUEST['category'])."
From: [[~{$_REQUEST['author']}]]
Assigned: 
Priority: {$_REQUEST['priority']}

Version: {$_REQUEST['version']}
OS: {$_REQUEST['os']}

Description:
{$_REQUEST['description']}";
}

## FmtPitsList creates a table of PITS issues according to various
## criteria.  
function FmtPitsList($fmt,$pagename,$opt) {
  $opt = array_merge($opt,@$_REQUEST);
  $pitslist = ListPages('/^PITS\\.\\d+$/');
  $out[] = FmtPageName("<table border='1' cellspacing='0' class='pits'>
    <tr><th><a href='?order=-name'>Issue#</a></th>
      <th><a href='?order=created'>Created</a></th>
      <th><a href='?order=category'>Category</a></th>
      <th><a href='?order=version'>Version</a></th>
      <th><a href='?order=-priority'>Priority</a></th>
      <th><a href='?order=status'>Status</a></th>
      <th><a href='?order=summary'>Summary</a></th></tr>", $pagename);
  $terms = preg_split('/((?<!\\S)[-+]?[\'"].*?[\'"](?!\\S)|\\S+)/',
    $opt['q'],-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
  foreach($terms as $t) {
    if (trim($t)=='') continue;
    if (preg_match('/([^\'":=]*)[:=]([\'"]?)(.*?)\\2$/',$t,$match))
      $opt[strtolower($match[1])] = $match[3]; 
  }
  $n=0; $plist=array();
  foreach($pitslist as $p) {
    $page = ReadPage($p);
    preg_match_all("/(^|\n)([A-Za-z][^:]*):([^\n]*)/",$page['text'],$match);
    $fields = array();
    for($i=0;$i<count($match[2]);$i++) 
      $fields[strtolower($match[2][$i])] = 
        htmlentities($match[3][$i],ENT_QUOTES);
    foreach(array('created','category','version','priority','status','summary',
        ) as $h) {
      if (!@$opt[$h]) continue;
      foreach(preg_split('/[ ,]/',$opt[$h]) as $t) {
        if (substr($t,0,1)!='-' && substr($t,0,1)!='!') {
          if (strpos(strtolower(@$fields[$h]),strtolower($t))===false) 
            continue 3;
        } else if (strpos(strtolower(@$fields[$h]),
             strtolower(substr($t,1)))!==false) 
          continue 3;
      }
    }
    $plist[$n] = $fields;
    $plist[$n]['name'] = $p;
    $n++;
  }
  $cmp = CreateOrderFunction(@$opt['order'].',-priority,status,category,name');
  usort($plist,$cmp);
  foreach($plist as $p) {
    $out[] = Keep(FmtPageName("<tr><td><a class='pitslink' href='\$PageUrl'>\$Name</a></td>",$p['name']));
    foreach(array('created','category','version','priority','status','summary',
        ) as $h) 
      $out[] = @"<td>{$p[$h]}</td>";
    $out[] = "</tr>";
  }
  $out[] = "</table>";
  return implode('',$out);
}

## This function creates specialized ordering functions needed to
## (more efficiently) perform sorts on arbitrary sets of criteria.
function CreateOrderFunction($order) { 
  $code = '';
  foreach(preg_split('/[\\s,|]+/',strtolower($order),-1,PREG_SPLIT_NO_EMPTY) 
      as $o) {
    if (substr($o,0,1)=='-') { $r='-'; $o=substr($o,1); }
    else $r='';
    if (preg_match('/\\W/',$o)) continue;
    $code .= "\$c=strcasecmp(@\$x['$o'],@\$y['$o']); if (\$c!=0) return $r\$c;\n";
  }
  $code .= "return 0;\n";
  return create_function('$x,$y',$code);
}

?>
