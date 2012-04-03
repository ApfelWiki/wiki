<?php if (!defined('PmWiki')) exit();

/**
 * List all categories
 *  
 * @author Patrick R. Michaud <pmichaud@pobox.com> 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 3.5.1
 * @copyright by the authors 2005-2006
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package categories
 */

define(CATEGORIES, "3.5.1");

SDVA($PmWikiAutoUpdate['Categories'], array(
    'version' => CATEGORIES, 
    'updateurl' => 'ApfelWiki only at the moment'
));

SDV ($CategorieIndexGroups, "");
SDV ($CategorieIndexCacheTime, 15); //minutes
SDV ($CategorieIndexCacheFile, "cache/categoryindex");

Markup('CategoryIndex', '<split', "/\\(:CategoryIndex:\\)/e",
  "CategoryIndex('$pagename')");

function CategoryIndex ($pagename) {
    global $LinkIndexFile, $CategoryGroup, $CategorieIndexGroups, $CategorieIndexCacheTime, $CategorieIndexCacheFile;

    ## read cache 
   	if (file_exists($CategorieIndexCacheFile)) {
   	    $releasetime = filemtime($CategorieIndexCacheFile) + ($CategorieIndexCacheTime * 60);
   	    if ($releasetime > time() && ($filesize = filesize ($CategorieIndexCacheFile))){
   	        $fp = fopen ($CategorieIndexCacheFile, 'r');
   	        $cache = fread ($fp, $filesize);
   	        fclose($fp);
   	        return $cache;
   	    }    
    }
    
    $matches = array();
    if (!file_exists($LinkIndexFile)) {
    	$pagelist = ListPages();
    	foreach($pagelist as $pname) {
    		if (!preg_match("/".$CategorieIndexGroups."\..*/",$pname)) continue;
    		$page = ReadPage($pname);
    		if (!$page) continue;
    		if ($page['targets']) {
    		    $targets = substr($page['targets'],strpos($page['targets'], '=') + 1);
    		    $hitsfound = preg_match_all("/$CategoryGroup\\.([^,\\n]*)/",$targets, $hits, PREG_PATTERN_ORDER);
    		} elseif ($page['text'])
    		    $hitsfound = preg_match_all("/\\[\\[!([^\\|\\]]+?)\\]\\]/",$page['text'], $hits, PREG_PATTERN_ORDER);
    		if ($hitsfound) 
    			foreach ($hits[1] as $hit) $matches[$hit] = $hit;
        }
    } else {
        $fp = @fopen($LinkIndexFile, 'r');
        if ($fp) {
            while (!feof($fp)) {
                $line = fgets($fp, 4096);
                if (!preg_match("/^".$CategorieIndexGroups."/", $line)) continue;
                while (substr($line, -1, 1) != "\n" && !feof($fp)) 
                    $line .= fgets($fp, 4096);
                if (strpos($line, '=') === false) 
                    continue;
                else 
                    $line = substr($line,strpos($line, '=') + 1);
                if (preg_match_all("/$CategoryGroup\\.([^,\\n]*)/",$line, $hits, PREG_PATTERN_ORDER))  
                   	foreach ($hits[1] as $hit) $matches[$hit] = $hit;
            }
            fclose($fp);
        }
    }
    
    $out = "";
    if (count($matches) > 0) {
        natcasesort($matches);
        foreach ($matches as $match)  $out[] = "* [[!$match]]" ;
        $out = implode("\n", $out);
    
        ## write cache
        mkdirp("cache");
    	$handle = @fopen ($CategorieIndexCacheFile, 'w');
        @fwrite($handle, $out);	
        @fclose ($handle);
    }  
    return $out;
}


?>