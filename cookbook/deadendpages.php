<?php if (!defined('PmWiki')) exit();

/*  V 0.1

	# Zeigt Seiten an, welche nicht zurueck ins Wiki Linken
	Copyright 2005 
		* Sebastian Siedentopf (schlaefer@macnews.de).

*/
	 
	 

Markup('deadendpages','directives','/\\(:deadendpages:\\)/e', "Keep(deadendpagesfct('$pagename',PSS('$1')))");

function deadendpagesfct($pagename,$args) {
	global $GroupPattern, $WikiWordPattern;
	$Ignorepattern = "Recent|Blocklist|Group";
  	$pagelist = ListPages();

 	foreach($pagelist as $pname) {
 		if (!preg_match('/^Main\..*$/',$pname)) continue;
      	if (preg_match('/^.*'.$Ignorepattern.'.*$/',$pname)) continue;
      	$page = ReadPage($pname); Lock(0);
      	if (!$page) continue;
      	if (preg_match('/\(:redirect/',$page['text'])) continue;
      	if (!preg_match("/($GroupPattern([\/.]))?($WikiWordPattern)|(\[\[|.*?\]\])/e" ,$page['text']))
	       	$out[] = LinkPage($pagename, '', $pname, '', $pname);  
    }
    sort( $out);
    return implode("<br />",$out);

}


?>