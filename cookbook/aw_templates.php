<?php if (!defined('PmWiki')) exit();

/*
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 0.2
 * @link 
 * @copyright by the authors 2005
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package pagecount
 */

SDV($HTMLHeaderFmt['templatehidden'] , "<script type='text/javascript'>
function templatehiddenjvscfct() {
 {	
		var expire = new Date();
		expire.setTime(expire.getTime() + 365 * 24 * 60 * 60 * 1000);
		detail=document.getElementById('aw_template');
	}
	if (detail==null) return false;
	if (detail.style.display==\"none\") {
	   detail.style.display=\"inline\";
	   detail=document.getElementById('aw_template_link');
  		detail.innerHTML = \"<a href='javascript:void(0);' onclick='templatehiddenjvscfct();'>Vorlage ausblenden.</a>\";
  		setCookie(\"apfelwikishowpagetemplate\",\"1\",expire);
	 } else {
	   detail.style.display=\"none\";
	   detail=document.getElementById('aw_template_link');
  		detail.innerHTML = \" <a href='javascript:void(0);' onclick='templatehiddenjvscfct();'>Vorlage einblenden.</a>\";
		setCookie(\"apfelwikishowpagetemplate\",\"0\",expire);
	}
  	
}
function setCookie(name, value, expires, path, domain, secure)
{
    document.cookie= name + \"=\" + escape(value) +
        ((expires) ? \"; expires=\" + expires.toGMTString() : \"\") +
        ((path) ? \"; path=\" + path : \"\") +
        ((domain) ? \"; domain=\" + domain : \"\") +
        ((secure) ? \"; secure\" : \"\");
}  	

</script>");


 	
  	Markup('aw_template', 'directives', '/\\(:input aw_template\s?:\\)/', AwGroupTemplate($pagename));
  	Markup('aw_template_switch', 'directives', '/\\(:input aw_template_switch\s?:\\)/', AwGroupTemplateSwitch($pagename));

function AwGroupTemplateSwitch($pagename){
	global $AwTemplates;	
	$group = FmtPageName("\$Group",$pagename);
	if ((isset($AwTemplates[$group]) && PageExists($AwTemplates[$group])) | (isset($AwTemplates[$pagename]) && PageExists($AwTemplates[$pagename]))) {
		if ($_COOKIE['apfelwikishowpagetemplate'] == "0")
			$show = "einblenden";
		else
			$show = "ausblenden";
		return 	$output = "<span id='aw_template_link'><a href=\"javascript:void(0);\" onclick=\"templatehiddenjvscfct();\">Vorlage ".$show.".</a></span>";
	} else 
		return "";
}

  	
function AwGroupTemplate($pagename) {
	global $AwTemplates;
	$group = FmtPageName("\$Group",$pagename);
	if (isset($AwTemplates[$pagename]) && PageExists($AwTemplates[$pagename])) {
		$template = $AwTemplates[$pagename];
		$outstr = "Seite $pagename";
	
	} elseif (isset($AwTemplates[$group]) && PageExists($AwTemplates[$group]) ) {
		$template = $AwTemplates[$group];
		$outstr = "Gruppe $group"; 
	} else
		return "";
	
	$page = ReadPage($template); 
	$display = "style='display:block;'";
	if ($_COOKIE['apfelwikishowpagetemplate'] === "0") 
		$display = "style='display:none;width:0px;'";
	
	$output = "<div id='aw_template' $display ><em style='font-size:83%'>".
		MakeLink($pagename,$template,"Vorlage f&uuml;r ". $outstr). 
		":</em><br /><textarea onkeydown='if (event.keyCode==27) event.returnValue=false;' rows=10 cols=25>". html_entity_decode($page['text']). "</textarea></div>";	
    
    return Keep($output);
}
?>