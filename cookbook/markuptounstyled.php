<?php if (!defined('PmWiki')) exit ();
/**
 * MarkupToUnstyled - converts PmWiki markup into unstyled text.
 *
 * Version 2009-03-01
 *
 * Copyright 2009 M.Scheierling (Tontyna) <eM@wahahn.de>
 *   Hammirs gemacht so gut wies ist - möge es funzen
 *   ansonsten kann jeder damit tun, was er will.
 *
 * This script lets you convert PmWiki markup into pure unstyled text.
 * It's like MarkupToHTML without HTML tags.
 * Cookbook is required by
 *** SlimTableOfContents - to extract the pure text for the TOC
 *** SectionEdit (since v 2.2.1-2009-02-26) to retrieve the edit link HTML title
 *
 */

/***************************************************************************
Requirements
------------
* Requires PmWiki 2.2.??? (don't know)
* Tested on PmWiki 2.2.0-stable

Install
-------
1. Put this script into your cookbook folder

   When using cookbook SlimTableOfContents or SectionEdit (since v 2.2.1-2009-02-26)
   you don't have to do anything, they include it automatically

2. When NOT using those cookbooks:
   include it into your farmconfig.php (when using a Farm-setup:
          include_once("$FarmD/cookbook/markuptounstyled.php");
   or when using default setup add the following to your config.php:
          include_once("cookbook/markuptounstyled.php");

3. Customize the $MarkupToUnstyledIgnorePattern array - see Customization

Usage
-----
Whenever you need unstyled text-only call function MarkupToUnstyled():
  $unstyledtext = MarkupToUnstyled($pagename, $markuptext);


How it works
------------
MarkupToUnstyled()
1. redirects all link functions to suppress the generation of <a href></a> tags
   and produce only the regular PmWiki link text
   e.g. [[Main.HomePage|+]]          becomes 'TitleOfHomePage'
   e.g. [[Main.PageNotYetCreated|+]] becomes 'PageNotYetCreated'
2. removes markup patterns from the input text which shouldn't be executed in step 4.
   i.e. markup that produces output we don't want in the unstyled text
   see Customization
3. removes html tags BEFORE evaluation markup (e.g. [@..@] might already be wrapped with <code class='escaped'>
   Hint: < and > in page text is encoded as &lt; / &gt;
4. evaluates markup by calling PmWiki's MarkupToHTML
5. removes newlines from result
6. removes html tags from result
7. replaces non-styling %...% - produced by $KeepTokens which might be restored in step 4.
8. restores LinkFunctions back to their original function call

Customization
--------------
The array $MarkupToUnstyledIgnorePattern holds regex patterns for markup that should be ignored in unstyled text.
These patterns are removed from the input before calling MarkupToHTML

By default it holds the replace pattern for [[target |#]] reference links and [[#anchor]]s:
SDV($MarkupToUnstyledIgnorePattern, array(
        "(?>\\[\\[([^|\\]]+))\\|\\s*#\\s*\\]\\]",  // [[target |#]] reference links
        "(?>\\[\\[#([A-Za-z][-.:\\w]*))\\]\\]"     // [[#anchor]]
   )
);

Depending on the Cookbooks / Markups your Wiki uses you should extend the
$MarkupToUnstyledIgnorePattern array - AFTER including the script.

E.g. if you have Cookbook/Footnote installed you should add the following to your config.php
  $MarkupToUnstyledIgnorePattern[] = '\\[\\^(.*?)\\^\\]';

Cookbook/SectionEdit already adds the following pattern:
  $MarkupToUnstyledIgnorePattern[] = '\\(:sectionedit.*:\\)';

Hint: The default array will be extended in future versions - I'm no PmWiki expert and there might be a lot more
PmWiki builtin markups that should be ignored.

***************************************************************************/

$RecipeInfo['MarkupToUnstyled']['Version'] = '2009-03-01';


###########
# $MarkupToUnstyledIgnorePattern should be extended in future versions for each known
# recipe / markup that should be ignored in Unstyled Text
###########
SDV($MarkupToUnstyledIgnorePattern, array(
        "(?>\\[\\[([^|\\]]+))\\|\\s*#\\s*\\]\\]",  // [[target |#]] reference links
        "(?>\\[\\[#([A-Za-z][-.:\\w]*))\\]\\]"     // [[#anchor]]
#        '\\[\\^(.*?)\\^\\]'      // footnote recipe
   )
);


# Helper function
# to return link text only, without <a href></a>
# cf. LinkSuppress() in PmWiki's pagerev.php
function MarkupToUnstyledLinkSuppress($pagename,$imap,$path,$title,$txt,$fmt=NULL)
  { return $txt; }

function MarkupToUnstyled($pagename, $text){
global $LinkFunctions;
  // Links shall give NO LINK, but their link text
  // keep $LinkFunctions - cf. PrintDiff() in PmWiki's pagerev.php
  $lf = $LinkFunctions;
  // suppress them
  foreach($LinkFunctions as $k => $val) {
    $LinkFunctions[$k] = 'MarkupToUnstyledLinkSuppress';
  }

  // Pattern we don't want to be evaluated in Unstyled Text
global $MarkupToUnstyledIgnorePattern;
  for ($i=0;$i < count($MarkupToUnstyledIgnorePattern); $i++){
    $text = preg_replace("/$MarkupToUnstyledIgnorePattern[$i]/is", "", $text);
  }

  // remove html tags BEFORE evaluation markup (e.g. [@..@] already wrapped with <code class='escaped'>
  // Hint: < and > in page text is encoded as &lt; / &gt;
  $text = preg_replace('/<.*?>/s', '', $text);
  // evaluate markup
  $text = MarkupToHTML($pagename, $text);
  // remove newlines
  $text = preg_replace('/\\n/s', '', $text);
  // remove html tags
  $text = preg_replace('/<.*?>/s', '', $text);
  //replace non-styling %...% - produced by restored $KeepTokens
  $text = preg_replace ("/(%)(.*?)(%)([^%]*)/", "&#x0025;"."$2"."&#x0025;$4", $text);

 // restore LinkFunctions
  $LinkFunctions = $lf;

  // return unstyled text
  return $text;
}