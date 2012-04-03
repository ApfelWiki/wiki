<?php

/**
 * This sniped make it possible to jump from the search field
 * by using $JumpASearchTrigger as first character. To jump to
 * a different group homepage use a / at last character after 
 * the groupname
 * 
 * @author John Rankin <john.rankin@affinity.co.nz>
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 1.2
 * @link http://www.pmwiki.org/wiki/Cookbook/SearchExtensions http://www.pmwiki.org/wiki/Cookbook/SearchExtensions
 * @copyright by the authors 2004-2006
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package jumpnsearch
 */

SDV($JumpNSearchTrigger, ".");
SDV($JumpNSearchTriggerGoogle, "g.");
SDV($JumpNSearchTriggerPmWiki, "w.");
define(JUMPNSEARCH, "1.2");

SDVA($PmWikiAutoUpdate['JumpNSearch'], array(
    'version' => JUMPNSEARCH, 
    'updateurl' => 'ApfelWiki only at the moment'
));

if ($action == 'search' || $_REQUEST['type'] == 'web') {
	$text = stripmagic($_REQUEST['q']);
	$jumpto = '';
	if (strpos($text, $JumpNSearchTrigger) === 0) { 
		$text = substr($text, 1);
	
		if (strpos($text, $JumpNSearchTriggerGoogle) === 0) {
	        	$text = substr($text, 2);
		    	header("Location: http://www.google.de/custom?q=".rawurlencode(utf8_decode($text))."&domains=apfelwiki.de&sitesearch=apfelwiki.de");		    
		    	exit;
		}
        
        if (strpos($text, $JumpNSearchTriggerPmWiki) === 0) {
	        	$text = substr($text, 2);
	    	    header("Location: $ScriptUrl?action=search&q=".rawurlencode($text));		    
		    	exit;
		}
        
        else {
            
    		$text = preg_replace("/\\s*/", "", ucwords($text));
		    # jump to a different page in a different group
    		if (preg_match("/^$GroupPattern([\\/.])$NamePattern$/", $text)) 
    			$jumpto = $text;
		
    		# jump to a different group homepage
    		elseif (strrpos($text, "/") == strlen($text)-1 ) { 
    			$text = substr($text, 0, strlen($text) - 1);
    			$jumpto = FmtPageName("$text.$DefaultName", $pagename);
    			if (!PageExists($jumpto))
    				$jumpto = FmtPageName("$text.$text", $pagename);
    		}
		
    		# jump to a differnt page in the same group 
    		elseif (preg_match("/^$NamePattern$/", $text)) {
    			$jumpto = (isset ($HTTP_GET_VARS['group'])) 
    				? stripmagic($HTTP_GET_VARS['group']).".$text" 
    				: "Main.$text";
    			if ($jumpto && PageExists($jumpto))
    				Redirect($jumpto);
		    }
		
	    }
	} 
}
?>