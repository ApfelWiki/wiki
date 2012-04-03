<?php if (!defined('PmWiki')) exit ();

/**
 * This script breaks a page in several editable sections.
 *
 * @package sectionedit
 * @author Patrick R. Michaud <pmichaud@pobox.com>
 * @author John Rankin <john.rankin@affinity.co.nz>
 * @author Sebastian Siedentopf <schlaefer@macnews.de>
 * @author Karl Loncarek <dh2mll@web.de>
 * @author Aidin Abedi <fooguru@msn.com>
 * @author Tontyna <eM@wahahn.de>
 * @version 2.2.1 (2009-02-26)
 * @link http://www.pmwiki.org/wiki/Cookbook/SectionEdit
 * @copyright by the authors 2005
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License

 Hint: to have a working TOC - use Cookbook/SlimTableOfContents

 ******************************************************
 */

/***************************************************************************
For the Admins
==============
Requirements
------------
* Requires PmWiki 2.2.0 and higher
* Last tested on PmWiki 2.2.0-stable
* Requires Cookbook/SignalWhenMarkup
* Requires Cookbook/MarkupToUnstyled

Install
-------
1. put this script into your cookbook folder
   put the SignalWhenMarkup script into your cookbook folder
   put the MarkupToUnstyled script into your cookbook folder
2. include it into your farmconfig.php (when using a Farm-setup:
          include_once("$FarmD/cookbook/sectionedit.php");
   or when using default setup add the following to your config.php:
          include_once("cookbook/sectionedit.php");

Customization
-------------
### Layout
The position and layout of the section links are controlled by the div.sectionedit class in
the script.
#eMFix:
Position and layout of 'regular' section links additionally can be controlled by
  div.sectionhorzline
  div.sectionsplit
  div.sectionhead
  div.sectionpage
When $SectionEditHeaderLinkSpan is set to true:
Position and layout of the section links in headings are controlled by the
  span.sectionedit class


### XLPage Strings
* $[(Edit Section &#x2193;)]
* $[Section] x $[of] y
#eMFix: some more XLPage Strings for the anchor's titles
* $[edit section: ] <heading> | $[edit number x]x [(<FromPagename>)]
  default: $[edit number x] = 'Nr. ';
* $[edit page] (<FromPagename>)

###  Horizontal-line Sectioning
This means that every horizontal line ---- is used for starting a new section.
To enable this option set the variable
    $SectionEditHorzLines = true;
By default the value is false;

### Autosectioning
This means that every heading is used for starting a new section. Which
headings are used depends on the variable $SectionEditAutoDepth.
To only use ==== and no headers for starting sections set the variable
    $SectionEditWithoutHeaders = true;
By default the value is false;

### eMFix: edit link in span at the end of heading
By default the edit link in headings is placed in <span></span> at the end of the heading
To move it in a div above the heading (like the edit links for ---- and ==== ) set:
    $SectionEditHeaderLinkSpan = false;
By default the value is true;
Hint: when div is used a margin-top of 0px will be applied to the <h#> tag

### Depth of Autosectioning
The following variable defines which range of headings is used for starting a
new section. Default is '6'. Here some examples:
    $SectionEditAutoDepth = 1;
means that only the heading "!" is used for a new section.
    $SectionEditAutoDepth = 3;
means that only the headings "!", "!!", and "!!!" are used for a new section.

### Autosectioning as it works at MediaWiki
    $SectionEditMediaWikiStyle = true; //default value
With this setting the automatic sectioning works as in MediaWiki, e.g.
everything between two "!" headings is one section. When set to FALSE the
sections are delimited by headings or the ==== markup.
Examples:
    value:TRUE  (sections)     FALSE (sections)   meanings:
          Text                 Text               Start of section:  \
          !Head1          \    !Head1    \        Middle of section: |
          Text            |    Text      /        End of section:    /
          !!Head2       \ |    !!Head2   \
          Text          | |    Text      /
          !!!Head3    \ | |    !!!Head3  \
          Text        | | |    Text      /
          ====      \ | | |    ====      \
          Text      / | | |    Text      /
          !!!!Head4 \ | | |    !!!!Head4 \
          Text      / / | |    Text      /
          !!!Head5  \   | |    !!!Head5  \
          Text      /   / /    Text      /
          !Head6    \          !Head6    \
          Text      /          Text      /

Usage
=====
To break a page use "====" on your wikipage *before* the wanted section.
Alternatively set $SectionEditAuto or use (:autosections:)

For Developers
==============
What's going on here?
---------------------
1. On browsing the text is searched and all occurences of ==== and headings
   are found and preceded by a (:sectionedit <type> <filename> <reference>:)
   where <type> will be e.g. '====' or e.g.'!!'. <filename> is reserved for
   usage with includes (the file where it is contained) <reference> points to
   the main document. Also headers get a style so the margin before the headers
   that are used for autosectioning are removed.
   eMFix: enhanced with vars for $title and $sectionnumber:
      (:sectionedit <filename> <type> <sectionno> <reference> <title>:)
      when $SectionEditHeaderLinkSpan the markup is placed AFTER the heading text.
2. include Markup is changed to first point to our own function which works
   the same way as 1.
3. Markup (:nosections:) (:autosections:) (:noautosections:) and
   (:nosectionsinincludes:) as directives setting some variables
4. Replacement, numbering, check and display of editlinks with Markup (:sectionedit:).
   Editlinks will be
   <filename>?action=edit&s=<sectionno>&auto=<1|>&from=<reference>
   css styling is applied depending on <type> and $SectionEditHeaderLinkSpan
5. edit-action is changed to an own function when edit contains an 's'
   parameter. Calculate correct section


Author/Contributors
-------------------
original (:breakpage:) script
Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com)

modifications to use ____ and edit page chunks
Copyright 2004 John Rankin (john.rankin@affinity.co.nz)

modifications to use ==== to seperate a page in several
editable sections on one single page
Copyright 2005 Sebastian Siedentopf (schlaefer@macnews.de)

some enhancements and bugfixes
Copyright 2005-2006 Karl Loncarek (dh2mll@web.de) <Klonk>

(optional) modifications to also use ---- to seperate a page in several
editable sections on one single page
Copyright 2008 Aidin Abedi (fooguru@msn.com)

tamed and completely rewritten - search for 'eMFix':
Copyright 2009 Tontyna <eM@wahahn.de>

Version History
---------------
* 2.2.2 - 2009-03-07 - Tontyna
** append/remove '-' at the chunks ends again (Safari needs that)
** fixed bug with SectionEditMediaWikiStyle
* 2.2.1 - 2009-02-26 - Tontyna
** no more sectioning of GroupFooters, ignore 'header-eating' markup
   improved edit-link title with MarkupToUnstyled recipe
* 2.2.0 - 2009-02-01 - Tontyna
** complete rewritten. Now
   1. "Section edit button" can be appended to heading text.
   2. Edit link has html title to indicate which section will be edited
   3  Extended css styling for edit links.
   4. Editing a section and pressing [Cancel] returns to the section.
   5. Ignores headings in (:markup:).
   6. Creates sections for includes even when main page has none.
   7. Updates page's targets field
   8. Handles simultaneous edits by displaying whole page text, when available the diff is shown.

* 2.1.8 - 2008-03-03 - Klonk
** fixed bug in parameter handling
* 2.1.7 - 2007-05-24 - Aidin
** added $SectionEditHorzLines, (:horzsections:), (:nohorzsections:)
* 2.1.6 - 2007-05-24 - Klonk
** speed optimization
* 2.1.5 - 2007-05-23 - Klonk
** fixed bug in optimization introduced with 2.1.4 (when editing was password protected)
* 2.1.4 - 2007-05-09 - Petko
** optimization of the conditional display
* 2.1.3 - 2006-09-22 - Klonk
** fixed some wrong regex, and some other minor stuff
* 2.1.2 - 2006-09-22 - Klonk
** fixed silly bug... forgot ?= in an regex
** [bugfix] (:...:) markup now still remains is not removed anymore
* 2.1.1 - 2006-09-22 - Klonk
** [bugfix] (:if ...:)====(:ifend:) lead to an empty editwindow
** now anything:)==== works also
* 2.1.0 - 2006-09-21 - Klonk
** section edit links are now only shown, when edit rigths are available
** [bugfix] Part before section could appear several times, when no author is provided
* 2.0.5 - 2006-09-20 - Klonk
** fixed bug in creation of editlinks for includes
** [bugfix] "Save and Edit" replacing the original file
* 2.0.4 - 2006-09-18 - Klonk
** added support for "action=view"
** fixed some stuff for use with PHP5
** should work now also in Auth stuff
* 2.0.3 - 2005-10-15 - Schlaefer
** [bugfix]: include parameter for lines and between anchors now working
** [bugfix]: media wiki style broke after 1.4.1
* 2.0.2 - 2005-10-09 - Schlaefer
** Change: changed & to &amp; in URLs
* 2.0.1 - 2005-09-30 - Klonk
** Change: actual pagename is now determined during the first check for sections
* 2.0 - 2005-09-28 - Klonk
** complete rewrite of browsing part, without programming around given tools
        in PmWiki. This should give greatly enhanced compatibility
** Feature: added (:noautosections:) for disabling automatic sections generation
** Change: provided directives now work also within GroupHeader and GroupFooter
** Change: The core splitting code staid the same, but I did a lot of modularizations for
        easier maintainance
** Feature: With the rewrite e.g. (:nosections:) now also works within conditional
        markup
** Change: Creation of editlinks now happens when links markup is processed
** Change: Now using 'auto' parameter in the URL for detection of section editing
        thus using a different edit handler
** Change: Adopted Edithandler to new parameters
***************************************************************************/

$RecipeInfo['SectionEdit']['Version'] = '2009-03-07 - v2.2.2';

## eMFix:
## needs to know about being in (:markup:) / control variable  $SignalMarkupMarkup
# so include cookbook SignalWhenMarkup!
require_once("$FarmD/cookbook/signalwhenmarkup.php");

## need cookbook MarkupToUnstyled
require_once("$FarmD/cookbook/markuptounstyled.php");
# our (:sectionedit:) markup disturbs MarkupToUnstyled() so customize the IgnorePattern:
$MarkupToUnstyledIgnorePattern[] = '\\(:sectionedit.*:\\)';

/*** defines the default layout of the section edit links */
SDV($HTMLStylesFmt['sectionedit'], "
div.sectionedit { text-align:right;font-size:smaller;clear:both;}
");

/****
eMFix: for better styling add something like that to your local.css:

div.sectionedit { text-align:center;font-size:smaller;clear:none;}

div.sectionhorzline{}
div.sectionsplit{border-top:1px dotted #369; }
div.sectionhead {border-top:1px dotted #369; }
div.sectionpage{border-top:3px double #00c; }

span.sectionedit { font-size:smaller; font-weight:normal;}
h1 .sectionedit{ font-size:0.4em; }
h2 .sectionedit{ font-size:0.52em; }
h3 .sectionedit{ font-size:0.6em; }
h4 .sectionedit{ font-size:0.7em; }
h5 .sectionedit{ font-size:0.75em; }
h6 .sectionedit{ font-size:0.75em; font-variant: normal;}

@media print {
  span.sectionedit,
  div.sectionedit { display:none; visibility:hidden;}
}

*****/


/*** setting default values of global variables */
SDV($SectionEditWithoutHeaders, FALSE);

### eMFix: edit link in span at the end of heading
# defaults to false due to backwards-compatibility
SDV($SectionEditHeaderLinkSpan, FALSE);
# default XLPage string
XLSDV('en', array('edit number x'=>'Nr. '));

SDV($SectionEditAutoDepth, '6');
SDV($SectionEditMediaWikiStyle, TRUE);
SDV($SectionEditInIncludes, TRUE);
SDV($SectionEditHorzLines, FALSE);

/*** Internal gloabl variable, should not be changed */
$SectionEditDisable = FALSE;
### eMFix: enhanced functionality for $SectionEditCounter,
# now **really** cares for only-one-call of SectionEditFirstTime()
$SectionEditCounter = 0;
### eMFix: obsolete, counting sections is done in SectionEditMarkup()
# $SectionEditSectionCounter = array();
# don't need this array, there won't be any 'invalid' includes
# $SectionEditIncludes = array();
# function SectionEditCheckForIncludes() is obsolete dito

$SectionEditActualPage = '';

/****************** eMFix start *******************
 * groupheader / groupfooter markup / implemented in special Action handling: browse
 */
# control variable:
$GroupHeaderFooterDone=0;

/*** function called by redirected groupheader markup */
# do it like PmWiki's original code, but increment counter
function SignalGroupHeaderDone(){
  global $GroupHeaderFooterDone, $pagename;
  $GroupHeaderFooterDone++;
StopWatch("===============  SignalGroupHeaderDone: # $GroupHeaderFooterDone ===============");
  return PRR(FmtPageName($GLOBALS['GroupHeaderFmt'],$pagename));
}

/*** function called by redirected groupfooter markup */
function SignalGroupFooterDone(){
  global $GroupHeaderFooterDone, $pagename;
  $GroupHeaderFooterDone++;
StopWatch("===============  SignalGroupFooterDone: # $GroupHeaderFooterDone ===============");
  return PRR(FmtPageName($GLOBALS['GroupFooterFmt'],$pagename));
}
/****************** eMFix end *******************/

/*** markup for setting variables*/
Markup('nosections', 'directives', '/\\(:nosections:\\)/ei', "PZZ(\$GLOBALS['SectionEditDisable'] = true)");
Markup('autosections', '>nosections', '/\\(:autosections\s*(\d)*:\\)/ei', "PZZ(\$GLOBALS['SectionEditWithoutHeaders'] = false)");
Markup('noautosections', '>autosections', '/\\(:noautosections\s*(\d)*:\\)/ei', "PZZ(\$GLOBALS['SectionEditWithoutHeaders'] = true)");
Markup('nosectionsinincludes','directives','/\\(:nosectionsinincludes:\\)/ei', "PZZ(\$GLOBALS['SectionEditInIncludes'] = false)");
Markup('horzsections', '>nosections', '/\\(:horzsections\s*(\d)*:\\)/ei', "PZZ(\$GLOBALS['SectionEditHorzLines'] = true)");
Markup('nohorzsections', '>horzsections', '/\\(:nohorzsections\s*(\d)*:\\)/ei', "PZZ(\$GLOBALS['SectionEditHorzLines'] = false)");

/*** initial markup handling*/
Markup('removesectionmarker', 'block', '/====+/', '');

/*** Replacement for the supplied (:include:) markup */
/*** eMFix: moved into 'special Action handling' for browse
Markup('include', '>if',
  '/\\(:include\\s+(\\S.*?):\\)/ei',
  "PRR(SectionEditIncludeText(\$pagename, '$1'))");
*******/

/*** Conversion if temporary Markup (:sectionedit <section> <reference>:) to link*/
# eMFix: enhanced with vars for $title and $sectionnumber
# Markup('sectionedit','links',
#   '/\\(:sectionedit\\s+(\\S.*?)\\s+(\\S.*?)\\s+(\\S.*?):\\)/ei',
#   "SectionEditCreateLink('$1','$2','$3')");

Markup('sectionedit','links',
  '/\\(:sectionedit\\s+(\\S.*?)\\s+(\\S.*?)\\s+(\\d.*?)\\s+(\\S.*?)\\s+(\w.*?):\\)/ei',
  "SectionEditCreateLink('$1','$2','$3','$4', PSS('$5'))");
#                        $1          $2          $3          $4          $5
#         "(:sectionedit $pagename ".$type.   " $nr $SectionEditActualPage $title:)\n";

/*** Convert temporary markup (:sectionedit...:) into clickable editlinks*/
/***** eMFix: reworked completely - added Parameters, enhanced styling, implements $SectionEditHeaderLinkSpan */
function SectionEditCreateLink($pagename, $type, $number, $from='', $title) {
        global $SectionEditWithoutHeaders, $SectionEditDisable, $ScriptUrl;
        global $SectionEditHeaderLinkSpan;
        /*exit, when disabled or not allowed*/
        if ($SectionEditDisable || ($SectionEditWithoutHeaders && ($type == "!")))
                return '';
        # replace apostrophs (') in title
        $title = preg_replace('/\'/','&#039;',$title);
        /*create editlink with anchor*/
        $editlink = FmtPageName("<a name='s$pagename".'_'."$number'></a><a href='\$PageUrl?action=edit&amp;s=".
                $number."&amp;auto=".
                ($SectionEditWithoutHeaders ? "n" : "y")."&amp;from=$from' title='".$title."'>$[(Edit Section &#x2193;)]</a>", $pagename);
#eMFix
# when '!' and $SectionEditHeaderLinkSpan: create a <span>
#
# for better styling:
# add extra css class to divs depending on $type:
# $type  css class
#   -    sectionhorzline ; horzline already draws a line, no need to draw another one
#   =    sectionsplit
#   !    sectionhead
#   #    sectionpage
         switch ($type) {
         case '-':
             $css = " sectionhorzline";
             break;
         case '=':
             $css = " sectionsplit";
             break;
         case '!':
             $css = " sectionhead";
             break;
         case '#':
             $css = " sectionpage";
             break;
         default:
             $css = "";
         }

        if (($type == "!") && ($SectionEditHeaderLinkSpan)) {
           $out = Keep(" <span class='sectionedit'>$editlink</span>");
        } else {
           $out = Keep("<div class='sectionedit $css'>$editlink</div>");
        }
        return $out;
}


/***Take text, add temporary markup before sections, then give back wikitext (helper function)
 * eMFix: function is called by
 *  1. SectionEditFirstTime()
 *     $text is page text - containing NO (:markup:)
 *  2. SectionEditIncludeText()
 *     $text is text of included page - might contain (:include:)s with headings etc.
 *         - could contain (:markup:) which maybe contains includes, headings etc.
 *  so the $text MUST be split / analyzed correctly
 *
 * eMFix: if $SectionEditHeaderLinkSpan create <span> in headings
 *        do the section counting here, SectionEditCreateLink isn't fit for that job,
 */
function SectionEditMarkup($mainpage,$pagename, $text) {
        global $SectionEditWithoutHeaders, $SectionEditAutoDepth, $SectionEditActualPage, $SectionEditHorzLines;
        global $SectionEditHeaderLinkSpan;
        $out = '';
        /*editing is not allowed anyway thus exit immediately*/
        if (!CondAuth($pagename, 'edit'))
                return ($text);
        /*check whether $pagename contains '#' and remove parts afterwards*/
        $pagenameparts = explode('#',$pagename);
        $pagename = $pagenameparts[0];
        /*make $pagename a valid wikipage name including group*/
        $pagename = MakePageName($mainpage,$pagename);
        /*check whether exisiting sections contain headers or ==== and split them*/
#        $p = preg_split('/((?<=header:\))|(?m:^)|(?:.*:\)))((?=!{1,'.$SectionEditAutoDepth.'}[^!])|(?=====))/', $text);

# eMFix:
# as $text could contain (:markup:)..[(:include:), !! etc.]...(:markupend:)
# it MUST be specialspit with the help of SectionEditSplitOrMergeMarkupMarkup
/****** eMFix: obsolete *******
        $horzline_match = '';
        if ($SectionEditHorzLines) $horzline_match = '|(?=----)';
        $p = preg_split('/((?m:^)|(?<=:\)))((?=!{1,'.$SectionEditAutoDepth.'}[^!])|(?=====)'.$horzline_match.')/', $text);
        //check for special PHP version and fix strange problem with preg_split
        if (phpversion()=='4.1.2') {
                if (substr($p[(count($p)-1)],0,1)=='=')
                        $p[(count($p)-1)] = '='.$p[(count($p)-1)];
                else
                        $p[(count($p)-1)] = '!'.$p[(count($p)-1)];
        }
********/
        $p = SectionEditSplitOrMergeMarkupMarkup($text);

        /*Set style to remove the space before the headings*
        # eMFix: only when $SectionEditHeaderLinkSpan is false
        if (!$SectionEditWithoutHeaders && !$SectionEditHeaderLinkSpan)
            $p = preg_replace('/((?m:^)|(?<=:\)))((!{1,'.$SectionEditAutoDepth.'})([^!]))/','$1$3%block margin-top=0px%$4',$p);

        /*creation of temporary markup for the split sections*/
        # eMFix: rewritten
        for ($i = 0; $i < count($p); $i ++) {
# Remember: $p[0] ALWAYS contains string 'before first True Match'
#           $p[0] in main text it starts with (:groupheader:), in includes ANY letter is possible
#           $p[0] can be EMPTY - in case page starts directly with a True Match!
#                 or BECOMES EMPTY (displays nothing), when page starts with diverse markups, e.g. (:title:)
# Decicion made: link to $i==0 calls edit for WHOLE PAGE
#                display link to $i==0 only for includes
# eMFix - that's wrong and by the way we really need the real Match Type
#          $horzline = (substr($p[$i],0,1) == '-');

            # 0th section is ALWAYS special, never treat as any of the types the preg_split tries to match
            if ($i==0) // && ($type != '!')) // && (substr($p[$i],0,4) != '----'))
              $type = '#';
            else
              $type = substr($p[$i],0,1);

            # now that's correct:
            $horzline = ($type == '-');
            if ($horzline) {
                $out .= '----';
            }
# eMFix
#            if (($i != 0) || ($pagename != $SectionEditActualPage) || $horzline) {
            if (($i != 0) || ($pagename != $SectionEditActualPage)) {
                /* add temporary link */
                # create the anchor's title:
                $title = "$[edit section: ]"; // 'Abschnitt bearbeiten: ';
                if ($type == '!')
                  $title .= SectionEditExtractHeaderText($pagename, $p[$i],($i == count($p)-1));
                else {
                  if ($i==0)
                    $title = "$[edit page]"; //'Seite bearbeiten';
                  else
                    $title .= "$[edit number x]".$i; // Nr. #
                }
                if ($pagename != $SectionEditActualPage) {
                    $title .= " ($pagename)";
                }
# fatal results when title contains ':)' !
$title = str_replace(':)','&#x003A;&#x0029;', $title);
#$title = str_replace(':)','[=:)=]', $title);
                # that's the temporary link, now containing title and number:
                $whatlink = "<:block>(:sectionedit $pagename ".$type." $i $SectionEditActualPage $title:)\n";

                if (($type == '!') && ($SectionEditHeaderLinkSpan)) {
                  # find end of header text, cf. SectionEditExtractHeaderText()
                  # find first nl without Backslash before and
                  # insert temporary link before that.

                  # this replacement cares for headings in the very last line,
                  # i.e. no new-line / empty line after it.
                  if ($i == count($p)-1){
                    # this one is for main wiki page text - see call to MarkupToHTML in HandleBrowse()
                    if (preg_match('/[^\n]\(:groupfooter:\)$/',$p[$i])) {
                      $p[$i] = preg_replace('/\\(:groupfooter:\\)$/',"\n(:groupfooter:)",$p[$i]);
                    }
                    #this one for includes
                    elseif (preg_match('/[^\n]$/',$p[$i])) {
                      $p[$i] .= " \n";
                    }
                  }
                  #Now, I think we are prepared for the heading text
                  $p[$i] = preg_replace('/^((?:!{1,6})(?:(?:(?:.*)\\\\(?>(?:\\\\*))\n)*)(?:.*)(?:[^\\\\]))(\n)/',
                        '$1'.$whatlink.'$2', $p[$i]);
                }
                else { // this will become a div in front of the section
                  $out .= $whatlink;
                }
            } // end: add temporary link

            if ($horzline) {
                $p[$i] = substr($p[$i],4);
            }
            $out .= $p[$i];

        }

        return ($out);
}


/*** Creation of temporary markup in front of the sections for the actual page*/
function SectionEditFirstTime($pagename, $text) {
        global $SectionEditCounter;
        global $SectionEditActualPage;
/***
  #eMFix
  1. don't do nothing when pache is cached
  2. don't do nothing when main page is finished
  3. don't do nothing when we are in (:markup:) sequence
  4. don't do nothing when SectionEditIncludeText already did it
  5. don't do nothing when once done
***/
  global $IsHTMLCached;
  if ($IsHTMLCached)return $text;  // page is cached
  global $GroupHeaderFooterDone;
  if ($GroupHeaderFooterDone > 0) return $text;  // main page is ready
  global $SignalMarkupMarkup;
  if ($SignalMarkupMarkup) return $text;   // processing (:markup:)

        /*set name of actual page in global variable*/
        if ($SectionEditActualPage == '')
                $SectionEditActualPage = $pagename;

        /*allow adding of markup only for the first function call*/
        # $SectionEditCounter prevents being executed more than once
        # $InclCount was intended to prevent from being called outside / after main page text
        #   $InclCount doesn't help when main text e.g. contains (:markup:)..(:include:)..(:markupend:),
        #   cause pmwiki function IncludeText() will increment $InclCount++
        #   and main text is not yet executed, comes always AFTER (:markup:)

        # consequence: SectionEditIncludeText() must incremet counter
        # Q: shouldn't SectionEditMarkup() increment the counter?
        # A: Maybe.
        #    - oh shit, there is another counter: $SectionEditInIncludes!!!
        #    when main text has no headings/horzline we will be called (the first time)
        #    by any included page containing our Markup and $SectionEditCounter is still ==0.
        #    But includes are the job of SectionEditIncludeText()
        #    ...
        # Q: so one counter should be enough?
        # A: Yes, you're right!
        #    Lets take $SectionEditCounter and let it be incremented by SectionEditIncludeText(), too
//       if ((++$SectionEditCounter > 1) || ($InclCount>0))
        if (++$SectionEditCounter > 1){
             return $text;
        }

        # SUCCESS: this line now only will be executed once and only in in main wiki text

        /*modify text, add temporary markup*/
        $out = SectionEditMarkup($pagename,$pagename,$text);

        /*check for includes and add filenames to global variable*/
// obsolete:   SectionEditCheckForIncludes('',$out);
        return $out;
}


/***Check text for includes within and collect pagenames (helper function)*/
/**** eMFix: this function is obsolete **********************
function SectionEditCheckForIncludes($pagename,$text) {
        global $SectionEditIncludes;
        # check whether checked page is in list, then add included pages
        if (in_array($pagename,$SectionEditIncludes) || ($pagename =='')) {
                # get all occurences of (:include...:)
                preg_match_all('/\\(:include\\s+(\\S.*?):\\)/',$text,$args);
                # create list of pagenames
                $args = ParseArgs(implode(" ",$args[1]));
                # create array only containing pagenames
                $SectionEditIncludes = array_merge((array)$SectionEditIncludes,(array)$args['']);
                # remove double page entries
                $SectionEditIncludes = array_unique((array)$SectionEditIncludes);
        }
        return;
}
************************************/



/***Add temporary markup to included text*/
/* eMFix:
   if there was no call to SectionEditFirstTime(). i.e. no !!! / ---- / ===== in main page text
   this function must do the initialization
   Discussion:
   Q: What? You mean even SectionEditCheckForIncludes('',$text);
   A: I think so.
   Q: But that needs the $page['text'], we don't have that, you know.
   A: Our markup could provide it - when you think about $0 of preg_replace
   Q: Anyway: what is that SectionEditCheckForIncludes() good for?
   A: It collects valid include names
   Q: Do we need them?
      'Invalid' includes happen only outside the page texts we handle. That's what
      $GroupHeaderFooterDone and $SignalMarkupMarkup help for.
   A: That's right, good!
      SectionEditCheckForIncludes() is obsolete!
*/
function SectionEditIncludeText($pagename,$inclspec) {
    global $SectionEditInIncludes;
#eMFix variables for flow control:
    global $GroupHeaderFooterDone, $SignalMarkupMarkup;
    global $SectionEditActualPage;
    global $SectionEditCounter;
# oh, and there could be caching = SignalGroupHeaderDone() never called
  global $IsHTMLCached;
  if ($IsHTMLCached)return $text;  // page is cached

# we're finished - PageTextUnWrap is done:
    if ($GroupHeaderFooterDone > 0)
        return IncludeText($pagename,$inclspec);

# don't do nothing in (:markup:) sequences:
    if ($SignalMarkupMarkup)
        return IncludeText($pagename,$inclspec);

  /* increment counter if WE SHOULD HANDLE the given $inclspec.
     prevents SectionEditFirstTime() */
    $SectionEditCounter++;

    /*exit when Includes should not have sections*/
    if (!$SectionEditInIncludes)
        return IncludeText($pagename,$inclspec);

    /*set name of actual page in global variable - maybe SectionEditFirstTime() didn't it*/
    if ($SectionEditActualPage == '')
        $SectionEditActualPage = $pagename;

    /*get all names that should be included*/
    $args = ParseArgs($inclspec);
    /* keeps all parameters but pagenames*/
    $argsparam = '';
    foreach($args as $k => $v) $argsparam .= ($k != '#' && !is_array($v) ) ? " ".$k."=".$v : "";
    /*keep only array with pagenames*/
    $args = $args[''];
    for ($i = 0; $i < count($args); $i ++) {
        /*get text from includes*/
        $text = IncludeText($pagename,$args[$i].$argsparam);
        /*work on text only if present and valid include*/
// eMFix: array $SectionEditIncludes is obsolete
//      if (in_array($args[$i],$SectionEditIncludes) && ($text != '')) {
        if ($text != '') {
            /*check for recursive includes, get pagenames and save them*/
// eMFix: obsolete:
//          SectionEditCheckForIncludes($args[$i],$text);
            /*add temporary markup to text from includes*/
            # eMFix: split text, ignoring (:markup:)
            $text = SectionEditMarkup($pagename,$args[$i], $text);
            $out[] = $text;
        }
        else {
# Q: doesthis ever happen?
# A: maybe if non-exstent page is included?
                $out[] = $text;
        }
    } // for $i
    return implode('',$out);
}


/***
 * This function handles the edit, preview and saving of sections.
 * It derived from the standard HandleEdit() function defined in pmwiki.php.
 *
 eMFix:
 completely reworked and simplified by Tontyna in January 2009:
 to be as clear as code can be and to be as close to pmwikis HandleEdit as possible

   * call pmwikis UpdatePage and let it handle the $new version
     so the target's field will be updated,
     and simuledit can give back merged version
   * renamed postchunk /prechunk to chunkbefore / chunkafter
     its because $EnablePost preg_greps for /^post/'
   * simplified handling of chunk
     only edittext needs nl cut off / appended again
   * added another $FmtV['$SectionsTotal'] for Preview's groupheader
     so we don't have to recalculate the splitters
   * splitting is done only once - when called first time
   * splitting according to the splitting algorithm used in 'browse'
   * $n == 0 indicates:
     whole text is edited, no chunks to care about

 */

function HandleEditSection($pagename, $auth = 'edit') {
        global $IsPagePosted, $EditFields, $ChangeSummary, $EditFunctions, $FmtV, $Now, $HandleEditFmt;
        global $PageStartFmt, $PageEditFmt, $PagePreviewFmt, $PageEndFmt, $GroupHeaderFmt, $GroupFooterFmt;
        global $PageEditForm, $EnablePost, $InputTags, $SectionEditWithoutHeaders, $SectionEditHorzLines;
        global $SectionEditMediaWikiStyle, $SectionEditAutoDepth, $MessageFmt;

        // we need some additional values in the edit form for section editing.
        // To respect Site.EditForm we replace the standard PmWiki edit form
        // e_form defined in /scripts/form.php with this
# eMFix: renamed post/prechunk to chunkbefore/after, added sectotal
        $InputTags['e_form'] = array (":html" => "<form method='post' action='\$PageUrl?action=edit&amp;s=\$PNum&amp;auto=\$AutoS&amp;from=\$FromP'>
<input type='hidden' name='action' value='edit' />
<input type='hidden' name='n' value='\$FullName' />
<input type='hidden' name='basetime' value='\$EditBaseTime' />
<input type='hidden' name='chunkbefore' value=\"\$ChunkBefore\" />
<input type='hidden' name='s' value='\$PNum' />
<input type='hidden' name='sectotal' value='\$SectionsTotal' />
<input type='hidden' name='auto' value='\$AutoS' />
<input type='hidden' name='from' value='\$FromP' />
<input type='hidden' name='chunkafter' value=\"\$ChunkAfter\" />");

/***********************************
  eMFix: explanation

  the function is called in four states:
  FIRST-TIME     -> prepare sections
 * CANCEL         -> redirect to From-Page#Anchor
  POST, POSTEDIT -> feed UpdatePage with whole text
  PREVIEW        -> feed UpdatePage with current section

  steps it must take:
  1. prepare for UpdatePage,
  2. call UpdatePage
 * 3. if posted: -> redirect to From-Page#Anchor
  4. prepare appropriate text snippet for the textarea
  5. fill form
  6. display form
****************************************/

  # the page that called me (differs from $pagename when included)
  $originalpage = @ $_REQUEST['from'];
  # $n holds the number of the section to be edited
  $n = @ $_REQUEST['s'];
  if ($n < 0) $n = 0;

  if (@$_REQUEST['cancel']) {
    //return to section that was last edited
    Redirect($originalpage, "\$PageUrl#s".$pagename."_".$n);
    return;
  }

/************** step 1: preparation *******************/
  /* standard code from HandleEdit() */
  Lock(2);
  $page = RetrieveAuthPage($pagename, $auth, true);
  if (!$page) Abort("?cannot edit $pagename");
  PCache($pagename, $page);
# thats the $page - last posted version - completely - with 'text'
  $new = $page;
  /*get fields when called second time.
    keep in mind: $EditFields contain 'text'
    so $new['text'] now holds the text we edited.
   */
  foreach ((array) $EditFields as $k) {
    if (isset( $_POST[$k])) $new["$k"]=str_replace("\r",'',stripmagic($_POST[$k]));
  }
  $new['csum'] = $ChangeSummary;
  if ($ChangeSummary) $new["csum:$Now"] = $ChangeSummary;

  /* Keep that 'text' in $edittext
     it is either
     * original and complete page text from $page[text] = state==FIRST-TIME,
       will be splitted into chunks
     or
     * the modified (snippet) we previously typed into the textarea
  ****************/
  $edittext = $new['text'];
  /****************/

  /*set variable for autosectioning depending on parameter given, when calling function*/
  # we need this for the splitting routine and for the MediaWiki-merge-again
  # Hint: Split & MediaWikiMerge is only done in state==FIRST-TIME
  if (@ $_GET['auto'] == 'y')
    $SectionEditWithoutHeaders = false;
  else
    $SectionEditWithoutHeaders = true;
  /***
     get the chunk if there is $_POST - othewise initialize to ''
     Q: what's the DoubleDollar $$ for?
     A: It's a php construct to create a variable named after the contents of the given one
     Q: Original sectionedit appended/removed '-' at the chunk's ends. Why don't we have that anymore?
     A: It's unnecessary. All text snippets are fine.
        Only $edittext might have a newline (which is perfectly all right, too) that for
        editor's sake will be removed and appended again before posting
     Bug-report: (2009-03-03) Safari eats the trailing white spaces from the section before.
     Okay, so let's append/remove '-' at the chunk's end again.
   */
  $PageChunks = array ('chunkbefore', 'chunkafter');
  foreach ((array) $PageChunks as $c) {
    $$c = '';
    if (@ $_POST[$c]) {
      $$c = str_replace("\r", '', stripmagic($_REQUEST[$c]));
    }
  }


  /* state==FIRST-TIME:
     split text, fill chunkbefore / chunkafter
  */
  if (!isset($_POST['text'])) {
    $p = array();
    # split the page text and set the currently edited section
    # Hint $n==0 means: edit whole page text, no splitting
    if ($n >0) {
      # use the same splitting algorithm we introduced for browsing - ignoring (:markup:) et al
      $p = SectionEditSplitOrMergeMarkupMarkup($new['text']);
      if ($n >= count($p)) $n = count($p)-1;
    }
    if ($n==0) {
      unset($p);
      $p[] = $new['text'];
    }

    /* here the merging of all sub-headings is done to achieve autosectioning a la MediaWiki */
    if (($n!=0) && $SectionEditMediaWikiStyle && !$SectionEditWithoutHeaders) {
      //check what heading level started this section
      #eMFix: inserted ^ - match !! only at start of the section
      if (preg_match('/^(!{1,'.$SectionEditAutoDepth.'})[^!]/',$p[$n],$parts)) {
        for ($i = $n+1; $i < count($p); $i ++) {
          // search in following sections
          if (preg_match('/((?m:^)|(?<=:\)))!{1,'.strlen($parts[1]).'}[^!]/',$p[$i]))
            //if higher or same level exists then exit
            break;
          else {
            //add currently checked section to active section
            $p[$n] .= $p[$i];
          }
        }
        // combine parts of old array into a new array
        $p = array_merge_recursive(array_slice($p,0,$n+1),array_slice($p,$i));
      }
    }

    # set $edittext to the extracted snippet:
    $edittext = $p[$n];

    /* retrieve number of sections:
       Hint: It's "count MINUS 1" and AFTER applying MediaWikiStyle cause
             1. it's more intuitively when previewing
             2. append/remove newline needs that 'real' number
    */
    $sectionstotal = count($p)-1;

    /* fill in the chunks */
    for ($i = 0; $i < count($p); $i ++) {
      if ($i < $n) {
        $chunkbefore .= $p[$i];
      }
      elseif ($i > $n) {
        $chunkafter .= $p[$i];
      }
    }
# eMFix: (2009-03-03) Safari eats the trailing white spaces from the section before.
#   So let's append '-' at the chunk's end again.
    $chunkbefore .= '-';
    $chunkafter .= '-';
  } // end of state==FIRST-TIME
  else {
    $sectionstotal = @ $_POST['sectotal'];
    // for UpdatPage's sake: append newline, but only if not whole text and not last section
    if (($n > 0) && ($n <$sectionstotal))
      $edittext .= "\n";
  }

  # beware of simultaneous edits!
  $simuledit = (@!$_POST['basetime'] || !PageExists($pagename) || $_POST['basetime']>=$page['time']) ? false : true;
  /*
     If simultaneous editing happens:
       swap to edit WHOLE text.
     The WHOLE text is either the result of Merge() or the modified version we produced by editing.
     Hint: Windows has no 'diff' command. I.e.: simuledit.php Merge() returns an empty string,
           but you can use Cookbook/SimultaneousEdits instead
  */
  if ($simuledit){
    # combine with chunks - remember: no need to care for appended chars in chunks
    #eMFix: for Safari's sake we had to append a char - remove them again
    $new['text'] = substr($chunkbefore,0,strlen($chunkbefore)-1) . $edittext . substr($chunkafter,0,strlen($chunkafter)-1);
    # we don't need $edittext anymore
    # $edittext = $new['text'];
    # tell the script that we edit whole text
    $n = 0;
    # clear chunks
    $chunkbefore='';
    $chunkafter='';
  }

  /* state==PREVIEW
     feed UpdatePage with current (modified) section
     chunkbefore / chunkafter are already filled from $_REQUEST
   */
  if (@ $_POST['preview']) {
    //give it a nice info in the heading about which section is edited. But only when not whole-text
    # pmwiki's PreviewPage() shows per MarkupToHTML the following:
    #    $text = '(:groupheader:)'.$new['text'].'(:groupfooter:)';
    # $n==0 means WHOLE TEXT
    if ($n > 0) {
      $GroupHeaderFmt = '<:block>=&gt; ($[Section] '.$n.' $[of] '.$sectionstotal.')(:nl:)';
      $GroupFooterFmt = '';
    }
  }

  /* state==POST / POSTEDIT
     feed UpdatePage with complete text:
        chunkbefore + edittext + chunkafter
     Hint: newline-fix already applied
           if $n==0 no need to do anything, already done
    #eMFix: for Safari's sake we had to append a char - remove them again
   */
  if (@$_POST['postedit'] || @$_POST['post']) {
    if ($n > 0) $new['text'] = substr($chunkbefore,0,strlen($chunkbefore)-1) . $edittext . substr($chunkafter,0,strlen($chunkafter)-1);

  } // end POST / POSTEDIT


/************** step 2: call UpdatePage *******************/
  /* standard code from HandleEdit() */
  /*
    UpdatePage needs
    * WHOLE page text to POST / POSTEDIT
    * WHOLE text when simultaneous editing
    * selected SECTION when PREVIEWing
  */
  # remember: renamed 'postchunk' to 'chunkafter'
  $EnablePost &= preg_grep('/^post/', array_keys(@$_POST));

  UpdatePage($pagename, $page, $new);
  Lock(0);


/************** step 3: redirect when posted *******************/
  /* standard code from HandleEdit() */
  # POST success --> we are finished
  if ($IsPagePosted && !@ $_POST['postedit']) {
    //jump directly to section that was last edited
    Redirect($originalpage, "\$PageUrl#s".$pagename."_".$n);
    return;
  }


/************** step 4: prepare appropriate text snippet for the textarea *******************/
  # $new['text'] should contain the text we want to edit => goes into $FmtV['$EditText']

  # when $simuledit:
  # if MergeSimulEdits() did something to the 'text' -> keep it, it's the merged version
  # if MergeSimulEdits() did nothing to the 'text' -> keep it either, it's easier to compare with revision

  # when no $simuledit / not whole text:
  # retrieve $edittext for the textarea,
  # do the newline-fix

  # Hint: when $simuledit is true, $n is 0
  if ($n > 0) {
    // when not last section: for editor's sake: remove additonal nl again that was added before
    if ($n <$sectionstotal)
      $edittext = substr($edittext,0,strlen($edittext)-1);
    $new['text'] = $edittext;
  }


/************** step 5: fill in form *******************/
  $FmtV['$DiffClassMinor'] =
    (@$_POST['diffclass']=='minor') ?  "checked='checked'" : '';
  # ATTENTION: thats the text for the textarea:
  $FmtV['$EditText'] =
    str_replace('$','&#036;',htmlspecialchars(@$new['text'],ENT_NOQUOTES));
  $FmtV['$EditBaseTime'] = $Now;

  /*additional FmtV for this script, stuff that has to be saved for "save and edit / preview /post simuledit" */
  #eMFix: renamed
  $FmtV['$ChunkBefore'] = str_replace('"', '&quot;', str_replace('$', '&#036;', htmlspecialchars($chunkbefore, ENT_NOQUOTES)));
  $FmtV['$ChunkAfter'] = str_replace('"', '&quot;', str_replace('$', '&#036;', htmlspecialchars($chunkafter, ENT_NOQUOTES)));
  $FmtV['$PNum'] = $n;
  $FmtV['$AutoS'] = @ $_GET['auto'];
  $FmtV['$FromP'] = $originalpage;
  #eMFix: added
  $FmtV['$SectionsTotal'] = $sectionstotal;

  /* standard code from HandleEdit() */
  if (@$PageEditForm) {
    $efpage = FmtPageName($PageEditForm, $pagename);
    $form = RetrieveAuthPage($efpage, 'read', false, READPAGE_CURRENT);
    if (!$form || !@$form['text'])
      Abort("?unable to retrieve edit form $efpage", 'editform');
    $FmtV['$EditForm'] = MarkupToHTML($pagename, $form['text']);
  }
# already (re)defined at the function's beginning
#  SDV($PageEditFmt, "..");

/************** step 6: display form *******************/
  SDV($HandleEditFmt, array(&$PageStartFmt, &$PageEditFmt, &$PageEndFmt));
  PrintFmt($pagename, $HandleEditFmt);
}



###########
# $SectionEditEscapePattern should be extended in future versions for each known
# recipe / markup that 'eats headings'
###########
SDV($SectionEditEscapePattern, array(
        '\\[([=@]).*?\\1\\]',                                                // [@..@] and [=..=]
// not need for this - first pattern matches already:
//       '\\(:markup(\\s+([^\n]*?))?:\\)[^\\S\n]*\\[([=@])(.*?)\\3\\]',       // markup
        '\\(:markup(\\s+([^\n]*?))?:\\)[^\\S\n]*\n(.*?)\\(:markupend:\\)',    // markupend
// not need for this - first pattern matches already:
//        '\\(:source(\\s+.*?)?\\s*:\\)[^\\S\n]*\\[([=@])(.*?)\\2\\]',         // sourceblock
          '\\(:source(\\s+.*?)?\\s*:\\)[^\\S\n]*\n(.*?)\\(:sourcee?nd:\\)',    // sourceblockend
// not need for this - first pattern matches already:
//        "\\(:code(\\s+.*?)?\\s*:\\)[^\\S\n]*\\[([=@])(.*?)\\2\\]",           // codeblock
          "\\(:code(\\s+.*?)?\\s*:\\)[^\\S\n]*\n(.*?)\\(:codee?nd:\\)"         // codeblockend
        ));
# helper variables for SectionEditMarkupEscape/Restore to restore only the tokens we escaped
$KeepStackStart = 0;
$KeepStackLast = 0;

## eMFix: new function SectionEditMarkupEscape
#  enhanced from  MarkupEscape()
#  cause I don't know what side effect would be produced if I changed global $EscapePattern
#  escapes dangerous 'header-eating' Markup using Keep().
#  can be undone with SectionEditMarkupRestore which is aware of what tokens we added to $KPV
# Helper for SectionEditSplitOrMergeMarkupMarkup
function SectionEditMarkupEscape($text) {
  global $KeepStackStart, $KeepStackLast, $KPCount;
  global $SectionEditEscapePattern;

  # remember me!
  $KeepStackStart = $KPCount;

  $out = $text;
  for ($i=0;$i < count($SectionEditEscapePattern); $i++){
    $out = preg_replace("/$SectionEditEscapePattern[$i]/eis", "Keep(PSS('$0'))", $out);
  }
  # remember me!
  $KeepStackLast = $KPCount;
  return $out;
}

## eMFix: new function SectionEditMarkupRestore
#  enhanced from  MarkupRestore()
#  undoes only the tokens SectionEditMarkupEscape Keep()t
# Helper for SectionEditSplitOrMergeMarkupMarkup
function SectionEditMarkupRestore($text) {
  global $KeepStackStart, $KeepStackLast, $KeepToken, $KPV;

  $keys = array_keys($KPV);
  $values = array_values($KPV);
  $out = $text;

  $count = $KeepStackLast;
  while ($count>$KeepStackStart){
    $count--;
    $key = $keys[$count];
    $value =$values[$count];
    $out = preg_replace("/$KeepToken$key$KeepToken/e", "\$value", $out);
  }
  return $out;
}

# eMFix: new function
# Helper Function for SectionEditMarkup and HandleEditSection to split page text ignoring (:markup:)s
# and other 'header-eating' markup
/*
  oh shit!
  It's not the same text that SectionEditMarkup / and HandleEditSection want to split!
  * SectionEditMarkup handles page text where some (I hope ALL dangerous 'header-eating') Markup
    already was applied
  * HandleEditSection receives almost PURE SOURCE, via RetrieveAuthPage / PageStore->read
    with e.g. [@...@], [=..=] still in it

  Solution: introduced function SectionEditMarkupEscape() to Keep() those markups
            and function SectionEditMarkupRestore() do restore them again
  Hint: append dangerous markup to array $SectionEditEscapePattern as detected/needed

BUT:
  * there is IncludeSection - inventing another method to include text - and this script
    will NOT produce any sectioning unless (:includesection:) is redirected too.
  Who else?

  * there might be other recipes that need to redirect for their own sake just the markup that this
  recipe needs to redirect - the last included will win...
  What could be a solution? A MarkupRedirectionChain?

  And -- oh, stop thinking to avoid madness!
  ---- ok, stopped that.

  Anybody out there having any idea how to handle this?
*/
function SectionEditSplitOrMergeMarkupMarkup($text){
  global $SectionEditWithoutHeaders, $SectionEditHorzLines,$SectionEditAutoDepth;

  ## 1. Keep() dangerous markup:
  #---------------------------
  $text = SectionEditMarkupEscape($text);

  ## 2. split the text
  #---------------------------
  # that's the original splitting algorithm from HandleEditSection:
  # split in headers, lines...
  $horzline_match = '';
  if ($SectionEditHorzLines) $horzline_match = '|(?=----)';

  if ($SectionEditWithoutHeaders)
    $p = preg_split('/((?m:^)|(?<=:\)))(?=====)'.$horzline_match.'/', $text);
  else {
    //now check whether exisiting sections contain headers and split them again
    $p = preg_split('/((?m:^)|(?<=:\)))((?=!{1,'.$SectionEditAutoDepth.'}[^!])|(?=====)'.$horzline_match.')/', $text);
  }
  //check for PHP version and fix strange problem
  if (phpversion()=='4.1.2') {
    if (substr($p[(count($p)-1)],0,1)=='=')
      $p[(count($p)-1)] = '='.$p[(count($p)-1)];
    else
      $p[(count($p)-1)] = '!'.$p[(count($p)-1)];
  }
  # End of original code from HandleEditSection
  #---------------------------

  ## 3. RestoreMarkup in the splitters
  #---------------------------
  for ($i=0;$i < count($p); $i++){
    $p[$i] = SectionEditMarkupRestore($p[$i]);
  }
  return $p;
}


/* eMFix: new helper function for SectionEditMarkup */
function SectionEditExtractHeaderText($pagename, $text, $lastline){
  # find position of first nl, without backslash in front.
  # remember: HandleBrowse calls MarkupToHTML giving
  #    $text = '(:groupheader:)'.@$text.'(:groupfooter:)';
  # when last line is heading without nl we have to ignore (:groupfooter:)
  # same (heading without nl) is possible for included pages
  # and in any case there might be any funny (:<Markup>:) signalling end-of-header
  # but that's left for the future

  if ($lastline){
    # this one is for main wiki page text - see call to MarkupToHTML in HandleBrowse()
    if (preg_match('/[^\n]\(:groupfooter:\)$/',$text)) {
      $text = preg_replace('/\\(:groupfooter:\\)$/',"\n(:groupfooter:)",$text);
    }
    #this one for includes
    elseif (preg_match('/[^\n]$/',$text)) {
      $text .= " \n";
    }
  }

  preg_match('/^(!{1,6})((?:(?:(?:.*)\\\\(?>(?:\\\\*))\n)*)(?:.*)(?:[^\\\\]))(\n)/', $text, $match);
  # $1 holds the !!!, $2 is the remainder until/before the \n
  $headertext = $match[2];
  #process $headertext
  # what about $headertext of length 0?
  # how to remove links from header text??
  # which other markup should be removed?
  $headertext = MarkupToUnstyled($pagename, PSS($headertext));
#StopWatch("------------ das is der title: $headertext");
  return $headertext;
}

/***special Action handling*/
if (($action == 'browse') || ($action == 'view')) {
        /*** eMFix: redirect (:groupheader:) Markup to catch end of main wiki page text handling */
        Markup('groupheader', '>nogroupheader',
           '/\\(:groupheader:\\)/ei',
           "SignalGroupHeaderDone()");
        /*** eMFix: Must redirect (:groupfooter:) Markup also - otherwise ther will be a Section to edit the GroupFooter and Sections for Headings etc. in the Footer too...*/
        Markup('groupfooter', '>nogroupfooter',
           '/\\(:groupfooter:\\)/ei',
           "SignalGroupFooterDone()");
        /*** Replacement for the supplied (:include:) markup*/
        # eMFix: enclosed $1 in PSS('$1')
        Markup('include', '>if',
           '/\\(:include\\s+(\\S.*?):\\)/ei',
           "PRR(SectionEditIncludeText(\$pagename, PSS('$1')))");

        $horzline_match = '';
        if ($SectionEditHorzLines) $horzline_match = '|(----+)';
        /* only show Links for section editing when browsing */
        Markup('editsecmarkgen', '<if', '/^.*(\(:nl:\)|header:\\)|\n)((!{1,6})|(====+)'. $horzline_match .').*$/se',
        "SectionEditFirstTime(\$pagename,PSS('$0'))");
}
elseif (($action == 'edit') && (@ $_REQUEST['auto'])) {
        /* change Edithandler only if a parameter "s" is supplied (s=section number) */
        $HandleActions['edit'] = 'HandleEditSection';
}