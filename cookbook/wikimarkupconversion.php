<?php if (!defined('PmWiki')) exit();

/*  V 0.3.4

	Contributors 
	Sebastian Siedentopf (schlaefer@macnews.de).
	
	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
	
*/

global $EditFunctions;
$key = array_search ('PostPage',$EditFunctions);
$EditFunctions[$key] = "PostPageMarkupConversion";

function PostPageMarkupConversion($pagename, &$page, &$new) {
	global $WikiMarkupExtensions;
	if (!isset($WikiMarkupExtensions)){
		PostPage($pagename, $page, $new); 
		return;}
	
	#####  MediaWiki ##### start markup definition #####
 	$Markups['MediaWiki']['Simple'] = array(
		#http://en.wikipedia.org/wiki/Wikipedia:How_to_edit_a_page#Wiki_markup
		
		#tables
		"#\{\|(.*?)[\n\r]#" => "(:table $1:)\n",
		"#\|\}#" => "(:tableend:)",
		"#\|\-[\n\r]\|(?!\|)(?:((?!\[{2}).*?)\|)?#" => "\n(:cellnr $1:)", 
		"#[\n\r]\|(?!\|)(?:((?!\[{2}).*?)\|)?#" => "\n(:cell $1:)",
		
		#external urls 
		"#(?<!\[)\[(http:[\S]*)\s(.*?)[\s]?\]#" => "[[$1 | $2]]",
		"#(?<!\[)\[(http:[\S]*)[\s]?\]#" => "$1",
	   
		#headings
		"#^==([^=].*)==$#m" => "!$1",
		"#^===([^=].*)===$#m" => "!!$1",
		"#^====([^=].*)====$#m" => "!!!$1",
		"#^=====([^=].*)=====$#m" => "!!!!$1",
		"#^======([^=].*)======$#m" => "!!!!!$1",
		
		#html like tags
		"#<small>(.*?)</small>#" => "[-$1-]",
		"#<big>(.*?)</big>#" => "[+$1+]",
		"#<tt>(.*?)</tt>#" => "@@$1@@",
		"#<nowiki>(.*?)</nowiki>#" => "[=$1=]",
		"#\<\!\-\-(.*?)\-\-\>#" => "(:comment $1:)",
		"#<br>#" => "\n",
		
		#misc
		"#__TOC__#" => "(:toc:)",
		"#\[\[Bild:(.*)\]\]#" => "Attach:$1",
		"#\[\[Image:(.*)\]\]#" => "Attach:$1",
		"#<--#" => "<-",
		
		#Definition Lists
		"#^;(.*?)[\n\r]?:(.*)#m" => ":'''$1''':$2", 
		
	);
	$Markups['MediaWiki']['Functions'] = array( 
		#regex => points to function preffct($BackreferencesAsArray[1])
		"#<pre>(.*?)</pre>#s" => "preffct", 
	);
	#####  MediaWiki ##### end markup definition ##### 
	 
	foreach($WikiMarkupExtensions as $p){
		if(!in_array($p,array_keys($Markups))) continue;
		$new['text'] = preg_replace(array_keys($Markups[$p]['Simple']),
        	array_values($Markups[$p]['Simple']), $new['text']);
        foreach ($Markups[$p]['Functions'] as $key=>$value)
				$new['text'] = preg_replace_callback($key,$value, $new['text']);
	} 

  	PostPage($pagename, $page, $new); 
  	return;
}

#handels <pre>...</pre> markup
function preffct($text){
	$lines = preg_split("/[\n\r]/",$text[1]); 
	foreach ($lines as $p1)
		$out[] = " ".$p1."\n";  
	return implode("",$out);
}

?>