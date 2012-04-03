<?php if (!defined('PmWiki')) exit();

/*  Copyright 2004 Laurent Meister (kt007) (meister at apfelwiki dot de)
    
    Version: newpage 1.0.9
    
    *Licence*
    You can redistribute this file and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  
    
    *Description*
    This file enables the "NewPageForm" feature for PmWiki, which
    allows authors to create new pages with a form.
    
    *Installation*
    Simply copy this file into the 'cookbook/' subdirectory or some other
    suitable place and then do

        include_once('cookbook/newpageform.php');
        
     Create a page (like Main.NewPage) an put (:newpageform:) on it.
     
    *Using dropdown box*
    The default setting uses an inputbox for the groupname. Alternatively you can switch to a drop box. Follow those steps:
    
    1)on line 42 uncomment //$NewGroup = array('Main', 'PmWiki');  
    2)on line 51 uncomment global $NewGroup;
    3)there, put all the groupnames which have to appear in the dropdown box between the brackets.
    4)On line 58 replace "<td><input type='text' name='group'value='Main'></td></tr>";" with the commented string below.
    
    *Comment*
    This is not really nice. I will make a revision of the script in January /February 2005.
    
    *Attention*           
    This script works only with pmwiki2!
    
   
*/

$NewGroup = array('Main', 'Tests');

markup('newpageform','inline','/\\(:newpageform:\\)/e',"Keep(NewPageForm(\$pagename))");

## NewPageForm() generates the form for entering a new page.  Note that
## once a page has been created, it's a normal wikipage and is edited
## according to the normal editing code (i.e., there's no form-based
## editing yet).
function NewPageForm($pagename) {
global $NewGroup;
    $out[] = "<form method='post'>
    <input type='hidden' name='action' value='postnewpage' />
    <table>
      <tr><td class='newpagefield'> $[Author]:</td>
        <td><input type='text' name='author' value='\$Author' /></td></tr>
      <tr><td class='seitenname'>$[Group]:</td>
          <td><select name='group' >" ;
        foreach($NewGroup as $k=>$v) {
    $x = is_string($k) ? $k : $v;
    $out[] = "<option value='$x'>$v</option>";
  } 
  $out [] = "
         <tr><td class='gruppe'>$[Pagename]:</td>
        <td><input type='text' name='newpagename'></td></tr>";

  $out[] = "
         </table>
      <div align='left'><input type='submit' value='$[submit new page]' />
       </div>
     </form>"; 
  return FmtPageName(implode('',$out),$pagename);
}

$HTMLStylesFmt[] = ".pitsfield { text-align:right; font-weight:bold; }\n";

include_once("$FarmD/scripts/author.php");

if ($action=='postnewpage') { 
$pagename = "{$_REQUEST ['group']}."."{$_REQUEST ['newpagename']}";
    if (!PageExists($pagename))
  { 	 if ($_REQUEST ['group'] == "") {
	   $pagename = "Main."."{$_REQUEST ['newpagename']}";}
     if ($_REQUEST ['group'] == "PITS") {
	   Abort("cannot create pages in PITS. Please use the PITS Form for a new issue. It's located at $ScriptUrl/PITS/PITS ");}
	    if ($_REQUEST ['newpagename'] == "") {
        $UrlPage="{$_REQUEST ['group']}";
        Redirect($PageNotFound);}
    else
     Lock(2);
	  $action = 'edit';
	 // $_REQUEST['post'] = 1;
	//  $CreateTime = strftime('%Y-%m-%d %H:%M',$Now);
	//  $EditMessageFmt = "<p class='vspace'>Please review and make any edits 
	//	to your issue below, then press 'Save'</p>";
	}
	
    else
  		{Redirect($pagename);}
    
}


?>