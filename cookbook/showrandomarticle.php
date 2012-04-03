<?php if (!defined('PmWiki')) exit();

/**
 * Shows Random Wiki Page
 * 
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 2.0
 * @link http://www.apfelwiki.de/CookBook/ShowRandomArticle http://www.apfelwiki.de/CookBook/ShowRandomArticle
 * @copyright by the respective authors 2004-2006
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package randomwikipage
 */
 
define(RANDOMWIKIPAGE, "2.0");

if ($action == "browse")
    Markup('showrandomarticle', 'directives', '/\\(:randomarticle(.*?)?:\\)/e', "ShowRandomWikiPage('$1')");

SDVA($PmWikiAutoUpdate['RandomWikiPage'], array(
    'version' => RANDOMWIKIPAGE, 
    'updateurl' => 'http://www.apfelwiki.de/CookBook/ShowRandomArticle'
));

function ShowRandomWikiPage($args) 
{
    $pargs = ParseArgs($args);
    $groups = ($pargs['group']) ? "/^".str_replace(",", "|", $pargs['group'])."\./" : NULL; 
    $pages = ListPages($groups);
    $rand = rand(0, count($pages));
    Redirect($pages[$rand]);
}

?>