<?php

/**
 * Update notification script for installed recipes.
 * 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 0.5.5
 * @link http://schlaefer.macbay.de/index.php/PmWikiCookbook/AutoUpdate http://schlaefer.macbay.de/index.php/PmWikiCookbook/AutoUpdate
 * @copyright by the respective authors 2006
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package pmwikiautoupdate
 */

define(PMWIKIAUTOUPDATE, "0.5.5");

SDVA($PmWikiAutoUpdate['PmWikiAutoUpdate'], array(
    'version' => PMWIKIAUTOUPDATE, 
    'updateurl' => 'http://schlaefer.macbay.de/index.php/PmWikiCookbook/AutoUpdate'
));

Markup('pmwikiautoupdate', '<split', "/\\(:pmwikiautoupdate:\\)/e", "PmWikiAutoUpdateList()");
function PmWikiAutoUpdateList(){
    global $PmWikiAutoUpdate;
    natcasesort($recipes = array_keys($PmWikiAutoUpdate));
    $out[] = "|| border=1 cellspacing=0 cellpadding=3";
    $out[] = "||!Recipe||!Installed Version||!Version Available||!Download||";
    if (count($recipes) > 0){
        foreach ($recipes as $recipe) {
            $data = "";
            if (substr($PmWikiAutoUpdate[$recipe]['updateurl'], 0, 7) == "http://") {
                $fp = fopen ($PmWikiAutoUpdate[$recipe]['updateurl']. "?action=source&pmwaurname=$recipe", "r");          
                while (!feof($fp)) 
                	$data .= fgets($fp, 1024);
                fclose($fp);
                if (!$data) continue;
                $xml_parser = xml_parser_create();
                xml_parse_into_struct($xml_parser, $data, $values, $index);
                xml_parser_free($xml_parser);
                if ($version = trim($values[$index['VERSION'][0]]['value']))
                    $downloadurl = trim($values[$index['UPDATEURL'][0]]['value']);
                elseif (preg_match("/Version:\\s*(.*?)\\s/", $data, $matches)) {
                    $version = $matches[1];
                    $downloadurl = $PmWikiAutoUpdate[$recipe]['updateurl'];
                } 
                else {
                   $version = "Couldn't find a version informaion.";
                   $downloadurl = $PmWikiAutoUpdate[$recipe]['updateurl'];
                }
            }
            else {
                $version = "-";
                $downloadurl = $PmWikiAutoUpdate[$recipe]['updateurl'];
            }
                
            if ($PmWikiAutoUpdate[$recipe]['version'] != $version)
                $out[] = "||%color=red%".$recipe." || ".$PmWikiAutoUpdate[$recipe]['version']." || $version ||$downloadurl ||";    
            else
               $out [] = "||%color=green%".$recipe." || ".$PmWikiAutoUpdate[$recipe]['version']." || $version ||$downloadurl ||";    
                    
        }
    } else
    $out [] = FmtPageName("$[No registered recipes found.]");
    return implode("\n", $out);
}

if ($action == "source" && $PmWikiAutoUpdate[$_REQUEST['pmwaurname']]['source'])
    $HandleActions['source'] = HandlePmWikiAutoUpdate($pagename);
    
function HandlePmWikiAutoUpdate($pagename, $auth = "read") {
    global $PmWikiAutoUpdate;
    $recipe = $PmWikiAutoUpdate[$_REQUEST['pmwaurname']];
    header ('Content-type: text/xml; charset="UTF-8"');
    echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
    <pmwikiautoupdate>
        <id>{$_REQUEST['pmwaurname']}</id>";
    foreach ($recipe as $key => $value)
        echo "<$key>$value</$key>";
    echo "</pmwikiautoupdate>";
    exit;
}

?>