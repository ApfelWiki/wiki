<?php if (!defined('PmWiki')) exit();
/* PmWiki DropShadow skin
 *
 * Examples at: http://pmwiki.com/Cookbook/DropShadow and http://solidgone.org/Skins/
 * Copyright (c) 2009 David Gilbert
 * This work is licensed under a Creative Commons Attribution-Share Alike 3.0 United States License.
 * Please retain the links in the footer.
 * http://creativecommons.org/licenses/by-sa/3.0/us/
 */
global $FmtPV;
$FmtPV['$SkinName'] = '"DropShadow"';
$FmtPV['$SkinVersion'] = '"2.1.0"';

global $PageLogoUrl, $PageLogoUrlHeight, $PageLogoUrlWidth, $HTMLStylesFmt;
if (!empty($PageLogoUrl)) {
	dg_SetLogoHeightWidth(15);
	$HTMLStylesFmt['dropshadow'] .=
		'#siteheader .sitetitle a{height:' .$PageLogoUrlHeight .'; background: url(' .$PageLogoUrl .') left top no-repeat} '.
		'#siteheader .sitetitle a, #siteheader .sitetag{padding-left: ' .$PageLogoUrlWidth .'} '.
		'#siteheader .sitetag{margin-top: ' .(35-substr($PageLogoUrlHeight,0,-2)) .'px}';
}
$SkinColor = dg_SetSkinColor('blue', array('red','blue','orange','yellow','green'));

# ----------------------------------------
# - Standard Skin Setup
# ----------------------------------------
$FmtPV['$WikiTitle'] = '$GLOBALS["WikiTitle"]';
$FmtPV['$WikiTag'] = '$GLOBALS["WikiTag"]';

# Move any (:noleft:) or SetTmplDisplay('PageLeftFmt', 0); directives to variables for access in jScript.
$FmtPV['$RightColumn'] = "\$GLOBALS['TmplDisplay']['PageRightFmt']";
Markup('noright', 'directives',  '/\\(:noright:\\)/ei', "SetTmplDisplay('PageRightFmt',0)");
$FmtPV['$ActionBar'] = "\$GLOBALS['TmplDisplay']['PageActionFmt']";
Markup('noaction', 'directives',  '/\\(:noaction:\\)/ei', "SetTmplDisplay('PageActionFmt',0)");
$FmtPV['$TabsBar'] = "\$GLOBALS['TmplDisplay']['PageTabsFmt']";
Markup('notabs', 'directives',  '/\\(:notabs:\\)/ei', "SetTmplDisplay('PageTabsFmt',0)");
$FmtPV['$SearchBar'] = "\$GLOBALS['TmplDisplay']['PageSearchFmt']";
Markup('nosearch', 'directives',  '/\\(:nosearch:\\)/ei', "SetTmplDisplay('PageSearchFmt',0)");
Markup('fieldset', 'inline', '/\\(:fieldset:\\)/i', "<fieldset>");
Markup('fieldsetend', 'inline', '/\\(:fieldsetend:\\)/i', "</fieldset>");

# Define a link stye for new page links
global $LinkPageCreateFmt;
SDV($LinkPageCreateFmt, "<a class='createlinktext' href='\$PageUrl?action=edit'>\$LinkText</a>");

# Override pmwiki styles otherwise they will override styles declared in css
global $HTMLStylesFmt;
$HTMLStylesFmt['pmwiki'] = '';

# Add a custom page storage location
global $WikiLibDirs;
$PageStorePath = dirname(__FILE__)."/wikilib.d/{\$FullName}";
$where = count($WikiLibDirs);
if ($where>1) $where--;
array_splice($WikiLibDirs, $where, 0, array(new PageStore($PageStorePath)));

# ----------------------------------------
# - Standard Skin Functions
# ----------------------------------------
function dg_SetSkinColor($default, $valid_colors){
global $SkinColor, $ValidSkinColors, $_GET;
	if ( !is_array($ValidSkinColors) ) $ValidSkinColors = array();
	$ValidSkinColors = array_merge($ValidSkinColors, $valid_colors);
	if ( isset($_GET['color']) && in_array($_GET['color'], $ValidSkinColors) )
		$SkinColor = $_GET['color'];
	elseif ( !in_array($SkinColor, $ValidSkinColors) )
		$SkinColor = $default;
	return $SkinColor;
}

# Determine logo height and width
function dg_SetLogoHeightWidth ($wPad, $hPad=0){
global $PageLogoUrl, $PageLogoUrlHeight, $PageLogoUrlWidth;
	if (!isset($PageLogoUrlWidth) || !isset($PageLogoUrlHeight)){
		$size = @getimagesize($PageLogoUrl);
		if (!isset($PageLogoUrlWidth))  SDV($PageLogoUrlWidth, ($size ?$size[0]+$wPad :0) .'px');
		if (!isset($PageLogoUrlHeight))  SDV($PageLogoUrlHeight, ($size ?$size[1]+$hPad :0) .'px');
	}
}
