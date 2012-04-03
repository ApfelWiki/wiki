<?php 

/**
 * This sniped make it possible to jump from the search field
 * by using $JumpASearchTrigger as first character. To jump to
 * a different group homepage use a / at last character after 
 * the groupname
 * 
 * @author Patrick R. Michaud <pmichaud@pobox.com> 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 2.1.1
 * @copyright by the authors 2004-2006
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package numberofsites
 */

if (!defined('PmWiki')) exit ();

define(NUMBEROFSITES, "2.1.1");

 # see http://schlaefer.macbay.de/index.php/PmWikiCookbook/AutoUpdate
 SDVA($PmWikiAutoUpdate['PostItNotes'] , array( 
     'version' => NUMBEROFSITES, 
     'updateurl' =>  "ApfelWiki only"
 ));

Markup('numberofsites','directives','/\(:numberofsites:\)/e', "NumberOfSitesFct()");

function NumberOfSitesFct() 
{
    StopWatch('NumberOfSite2 Start');
    global $WikiLibDirs;
    
    $gruppen = array('Main' => 1, 'Tests' => 1, 'Rezensionen' => 1);
    $zaehler = 0;
    
    $dir = FmtPageName($WikiLibDirs[0]->dirfmt, '');
    $dirlist = array(preg_replace('!/?[^/]*\$.*$!','',$dir));
    while (count($dirlist)>0) {
        $dir = array_shift($dirlist);
        $dfp = opendir($dir); 
        if (!$dfp) continue;
        while (($pagefile = readdir($dfp)) != false) {
           # Ausschluß von nicht in $gruppen definierten Wikigruppen      
           
           if(!@$gruppen[substr($pagefile,0,strpos($pagefile,'.'))]) continue;
           //Ignorieren von geloeschten Dateien
           if (strstr($pagefile,","))  continue;
           $zaehler++;
        }      
        closedir($dfp);
    }
    StopWatch('NumberOfSite2 End');
    return $zaehler; 
}    

?>
