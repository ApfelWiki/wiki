<?php if (!defined('PmWiki'))
	exit ();

/**
 * Brings a simple Bookmark and Favorite System to PmWiuki
 *   
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @version 0.2.11
 * @link 
 * @copyright by the authors 2005
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package favorites
 */

define ('Favorites', 0.2);
if (FmtPageName('$Group',$pagename) == "Favorites")
	$RssMaxItems = 50;
  
SDV($HandleActions['addtofavorites'],'HandleFavoritesAddToFavorites'); 

function HandleFavoritesAddToFavorites ($pagename, $auth='read'){
	global $Author, $IsPagePosted, $RecentChangesFmt;
	$IsPagePosted = 1;
	$favpagename = MakePageName("Favorites.Favorites","Favorites.$Author");
	$RecentChangesFmt = array( $favpagename => '* [[$Group.$Name]]  ');
	Lock(2);
	$empty = array();
	PostRecentChanges($pagename, $empty, $empty);
	Lock (0);
	Redirect($pagename);
}

SDV($HandleActions['deletefromfavorites'],'HandleFavoritesDeleteFromFavorites'); 

function HandleFavoritesDeleteFromFavorites ($pagename, $auth='read'){
	global $Author, $Group, $Name, $PCache;
	$favpagename = MakePageName("Favorites.Favorites","Favorites.$Author");
	if (!PageExists($favpagename) || !$pagetodelete = $_REQUEST['page'])
		Redirect($pagename);
	Lock(2);
	$favpage = RetrieveAuthPage($favpagename,$auth);
	if( !isset( $PCache[$pagetodelete]['title'] ) ) 
    		PCache($pagetodelete, ReadPage($pagetodelete, READPAGE_CURRENT));
	$repstr = FmtPageName("\$Group[\\/.]\$Name",$pagetodelete);
	$favpage['text'] = preg_replace("/.*\\[\\[$repstr\\]\\].*\n/", "", $favpage['text']);
	WritePage ($favpagename, $favpage);
	Lock (0);
	Redirect($pagename);
}

$HTMLStylesFmt['favorites'] ="
	#favoritescss ul {
	position: absolute;
	margin: 0;
	padding: 0;
	list-style: none;
	width: 260px;right:0px;
	border-bottom: 1px solid #bbb;
	z-index:51; 
	border: 1px solid #bbb; background:white;
	}
	
	#favoritescss ul li {
	position: relative; 
	}

	#favoritescss ul li ul {
	position: absolute;
	right:260px; width:260px;
	top: 0px;
	display: none; 
	}
	.favoritesli a {padding: 5px;}
	.favoritesli, .favoritesli a {
	display: block;
	text-decoration: none;
	color: #777;
	background: #fff;
	
	border-bottom: 0;
	font-size:9pt; }
	.favoritesli, .favoritesli a:visited, .favoritesli a:hover, .favoritesli a {color:#5A5F59 !important; }
	#favoritescss .favoriteslimg  {float:right;padding:0 6px 0 0;}
	#favoritescss a.createlinktext {border-bottom:1px dotted #5A5F59;color: #ba0000 !important;}
	#favoritescss .favoritesrimg  {float:left;padding:0 6px 0 0;}
	.favoritestxt {display:block;padding:5px;}
	#favoritescss ul li:hover .favoritesli, #favoritescss ul li:hover  a {background:#eee; color:#5A5F59;}
	#favoritescss ul li:hover ul li , #favoritescss ul li:hover ul li  a {background:white;}
	#favoritescss ul li:hover ul li:hover , 	#favoritescss ul li:hover ul li:hover  a {background:#eee;color:#5A5F59;}
	#favoritescss ul li:hover ul { display: block;}
	


		
	";
	
$HTMLHeaderFmt['favorites'] = "
		<script type=\"text/javascript\">
		function favoritesSwitchHidden(divid) {
			var view		= 	document.getElementById(divid);
			if (view == null) return false;
			if (view.style.display == \"none\") {
   				view.style.display	=	\"inline\";
 			} else {
   				view.style.display	=	\"none\";
			}
			return 0;
		}
		function favoritesClose(){
			var elem = document.getElementById(\"wikibody\");	
			if(window.addEventListener){ 
				elem.addEventListener('click', function(){document.getElementById('favoritesmasterul').style.display	= \"none\";}, false);
			} else { 
				elem.attachEvent('onclick', favoritesSwitchHidden('favoritescss'));
			}
		}
		setTimeout(\"favoritesClose();\",1000);
		setTimeout(\"favoritesSwitchHidden('favoritescss');\",500);favoritesSwitchHidden('favoritescss');
		</script>";

function FavoritesTemplateLink() {
	global $Author,$pagename,$PageUrl, $PubDirUrl;

	// returns no link if the current author doesn't made an own profile yet
	if (!PageExists(MakePageName("Profiles.Profiles","Profiles.$Author")))
		return;

	$minus = "<img class='favoriteslimg' src='$PubDirUrl/cookbook/favorites/minus.gif' alt='Favoriten entfernen' title='Favoriten entfernen' />	";
	$submenu = "<span style='color:#A0A0A0;'>&#x25c0;</span>";
	
	$favpagename = MakePageName("Favorites.Favorites","Favorites.$Author");
	
	//standard pages
	$footer = "<li  style='border-bottom:1px solid silver;'><div class='favoritesli'><span class='favoritestxt'>$submenu Mein ApfelWiki</span><ul>";
	$footer .= "<li><div class='favoritesli'>".MakeLink($pagename,"Profiles.$Author","Profil")."</div></li>";		
	$footer .= "<li><div class='favoritesli'>".MakeLink($pagename,"Contributions.$Author","Beitr&auml;ge")."</div></li>";		
	$footer .= "<li><div class='favoritesli'>".MakeLink($pagename,"$favpagename?action=rss","Favoriten als RSS Feed")."</div></li>";
	$footer .= "</ul></div></li>";
	//favorites help
	$footer .= "<li><div class='favoritesli'>".MakeLink($pagename,"ApfelWiki.Favoriten","Hilfe zu Favoriten")."</div></li>";		
	//close
	$footer .= "</ul></span></div>";
	
	// currently no implementation for IE
	if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
		#echo " | ".MakeLink($pagename,$favpagename,"Favoriten");
		return;
	}
	
	echo "<div id='favoritescss' style='display:none;'> | <a href='javascript:void(null)' onclick='favoritesSwitchHidden(\"favoritesmasterul\");'> Favoriten</a>";
	echo "<span id='favoritesmasterul' style='display:none'> <ul>"; 

	// favorites section
	echo "<li><div class='favoritesli'><a style='font-style: italic;text-align:center;' href='$PageUrl?action=addtofavorites'>Aktuelle Seite zu den Favoriten hinzuf&uuml;gen.</a></div></li> ";
	// returns no further items if no favpage exists
	if (!PageExists($favpagename)) {
		echo $footer;
		return;
	}
	$trail = ReadTrail($pagename,$favpagename);
	foreach($trail as $page) {
		if (strstr($page['pagename'],"PITS."))
			$pitsentries[] = $page;
		else {
			echo "<li>";
			echo MakeLink($pagename,$pagename."?action=deletefromfavorites&page=".$page['pagename'],$minus);
			echo "<div class='favoritesli'>";
			echo MakeLink($pagename,$page['pagename'],FavoritesGetPageTitle($page['pagename']));
			echo "</div></li>";
		} 
	}	

	// generates a pits submenu if there is any pits entry on the favpage
	if ($pitsentries) {
		echo "<li><div class='favoritesli'>".MakeLink($pagename,"PITS.PITS","$submenu PITS")."</div><ul>";
		foreach($pitsentries as $page) {
			echo "<li>";
			echo MakeLink($pagename,$pagename."?action=deletefromfavorites&page=".$page['pagename'],$minus);
			echo "<div class='favoritesli'>";
			echo MakeLink($pagename,$page['pagename'],FavoritesGetPageTitle($page['pagename']));
			echo "</div></li>";
		}	
		echo "</ul></li>";
	}
	
	// Recent Contributions	
	$contributionspagename = MakePageName("Contributions.Contributions","Contributions.$Author");
	if (PageExists($contributionspagename)) {
		$trail = ReadTrail($pagename,$contributionspagename);
		#echo count($trail); 
		#exit;
		echo "<li style='border-top:1px solid silver;'><div class='favoritesli'>".MakeLink($pagename,$contributionspagename,"$submenu Letzte Beitr&auml;ge")."</div><ul>";
		for ($i=0; $i<count($trail)/3, $i<10;$i++) {
			echo "<li>";
			echo "<div class='favoritesli'>";
			echo MakeLink($pagename,$trail[$i]['pagename'],FavoritesGetPageTitle($trail[$i]['pagename']));
			echo "</div></li>";
		}
		echo "</ul></li>";
	}
	
	echo $footer;
} 

function FavoritesGetPageTitle ($pagename) {
	global $PCache;
	if( !isset( $PCache[$pagename]['title'] ) ) 
    		PCache($pagename, ReadPage($pagename, READPAGE_CURRENT));
	return FmtPageName("\$Group - \$Title",$pagename);
}
?>
