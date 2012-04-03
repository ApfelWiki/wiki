<?php if (!defined('PmWiki')) exit ();
/**
 * SignalWhenMarkup - tells wheter MarkupToHTML is inside a (:markup:) sequence for PmWiki 2.x
 *
 * Version 2009-02-01
 *
 * Copyright 2009 M.Scheierling (Tontyna) <eM@wahahn.de>
 *   Hammirs gemacht so gut wies ist - möge es funzen
 *   ansonsten kann jeder damit tun, was er will.
 *
 * This script tells you whether the Markup Chain (MarkupToHTML / MarkupTable execution)
 * is inside a (:markup:) Markup
 * Some cookbooks like SlimTableOfContents, SectionEdit, TitleMarkup
 * need to know or should at least care about that.
 * They should / should not execute their code inside (:markup:) page source text
 *
 */

/***************************************************************************
Requirements
------------
* Requires PmWiki 2.2.??? (don't know)
* Tested on PmWiki 2.2.0-stable

Install
-------
Put this script into your cookbook folder

When using cookbook SlimTableOfContents
you don't have to do anything, it includes it automatically

When NOT using those cookbooks:
include it into your farmconfig.php (when using a Farm-setup:
          include_once("$FarmD/cookbook/signalwhenmarkup.php");
or when using default setup add the following to your config.php:
          include_once("cookbook/signalwhenmarkup.php");

Usage
-----
Whenever you need to know wheter your own markup is evaluated within (:markup:) code,
check the value of $SignalMarkupMarkup
  $SignalMarkupMarkup == 0  - we are NOT in a (:markup:) sequence
  $SignalMarkupMarkup == 1...n we are in the n'th level

How it works
------------
redirects the markups 'markup' and 'markupend'

***************************************************************************/

$RecipeInfo['SignalWhenMarkup']['Version'] = '2009-02-01';


/*** control variable, tells about current depth of calls to MarkupMarkup*/
$SignalMarkupMarkup = 0;


# das hier funzt nicht. Ist womöglich stdmarkup noch gar nicht mit dabei oder was?
#$MarkupTable['markup']['rep'] = "SignalWhenMarkupMarkup(\$pagename, PSS('$4'), PSS('$2'))";
# das hier macht übrigens das TOTALE Durchnander; alle die sich auf diese beziehen, sind komplett verruckelt:
#DisableMarkup('markup');
#DisableMarkup('markupend');

/* redirect markup to SignalWhenMarkupMarkup */
Markup('markup', '<[=',
    "/\\(:markup(\\s+([^\n]*?))?:\\)[^\\S\n]*\\[([=@])(.*?)\\3\\]/sei",
    "SignalWhenMarkupMarkup(\$pagename, PSS('$4'), PSS('$2'))");
Markup('markupend', '>markup',
    "/\\(:markup(\\s+([^\n]*?))?:\\)[^\\S\n]*\n(.*?)\\(:markupend:\\)/sei",
    "SignalWhenMarkupMarkup(\$pagename, PSS('$3'), PSS('$1'))");


/*** function called by redirected markup/markupend markups
 * markups might be - verdammt, wie ist das englische Wort für verschachtelt?
 */
function SignalWhenMarkupMarkup($pagename, $text, $opt = '') {
global $SignalMarkupMarkup;
  # increment counter
  $SignalMarkupMarkup++;
  # do what (original) markups did:
  $retval = MarkupMarkup($pagename, $text, $opt);
  # decrement counter
  $SignalMarkupMarkup--;
  return $retval;
}

