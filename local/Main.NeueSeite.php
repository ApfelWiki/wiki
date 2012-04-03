<?php
include_once("$FarmD/cookbook/newpageform.php");

if ($_GET['createfrom']!="") {
   $pagename=MakePageName($_GET['createfrom'],$pagename);
}
$CreatePageForm='<form action="'.$ScriptUrl.'">
  <input type="text" name="n" value="NameOfThePage" />
  <input type="hidden" name="action" value="edit" />
  <input type="hidden" name="createfrom" value="'.$pagename.'" />
  <input type="submit" name="submit" value="Create" />
</form>';
Markup('createpage','directives','/\(\:createpage\:\)/e',
      "Keep('$CreatePageForm')");
      
?>