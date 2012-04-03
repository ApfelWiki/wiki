<?php if (!defined('PmWiki')) exit();
/*

Tell-A-Friend Module
====================

Abstract
--------

When visitors visit a page they find interesting, they share the page with
others. PmWiki does not provide native support for sharing pages with others
via a web form. This recipe allows the site administrator to create a form
interface allowing visitors to share pages, including robust email validation. 

Copyright
---------

Originally created by Bram Brambring (http://www.brambring.nl) (c) 2004
Earlier versions include input by Jeffrey W. Barke (c) 2005--2006.
Version 3.0.0 re-write by Benjamin C. Wilson (c) 2006

Copyright (c) 2004--2006. All Rights Reserved.

This file may be redistributed and/or modified under the terms of the GNU
General Public License as published by the Free Software Foundation; either
version 2 of the License, or (at your option) any later version.

How to Use Tell-A-Friend
------------------------

Installation
~~~~~~~~~~~~

In order to use this recipe, you will need to upload its files into the
cookbook subdirectory ($FarmD/cookbook). Then, include the recipe file in the
local/config.php directory (e.g.
include_once("/path/to/TellAFriend/default.php"); Next, you will need to set
the $TAFEmailAdministrator email address (e.g. $TAFEmailAdministrator =
'webmaster@example.org'). Finally, you will need to create a form on a wiki
page and tell the recipe where the page is by setting the $TAFMailFormPage
(default "$SiteGroup.TAForm").

Sample Form
~~~~~~~~~~~

[@
{$TAFErrors}
(:table id=taf border=0:)
(:cellnr:)Your Name
(:cell:)(:input form id=taf method=post:)
(:input text name='name' size=30:)
(:cellnr:)Your Email
(:cell:)(:input text name='email' size=30:)
(:cellnr:)Send To:
(:cell:)(:input text name='to' size=40:)
(:cellnr:)Send Copy to Self:
(:cell:)(:input checkbox chkSendSelf 1:)
(:cellnr:)Message:
(:cell:)(:input textarea name='text' cols=40 rows=5 noscroll=noscroll:)
(:input hidden name='ip' value='':)
(:input hidden name='action' value='taf':)
(:input submit name=post value="Go":)
(:input end:)
(:tableend:)
@]

Usage
~~~~~

In order to add a Tell-A-Friend form on a page, the recipe must be properly
configured. Once configured, the form can be inserted using the
"(:tellafriend:)" markup. If you want this form used throughout a group,
consider placing it in the GroupFooter or GroupHeader as appropriate.

*/
define('TAFPATH', dirname(__FILE__) . '/');
SDV($TAFVersion, "Tell-A-Friend for PmWiki v.3.0.0");
SDV($TAFCaptchaSupport, 0);
SDV($TAFDefaultSubject, "$WikiTitle - $pagename");
SDV($TAFEmailAdministrator, 'webmaster@example.net');
SDV($TAFEmailDisclaimer, 'This email was sent to you by a visitor from our site.');
SDV($TAFHandlePageFmt, array(&$PageStartFmt, '$PageText', &$PageEndFmt));
SDV($TAFMailFormPage, "$SiteGroup.TAForm");
SDV($TAFNoHTML, 'If your email does not support HTML, please visit the page:');
SDV($HandleActions['taf'], 'TAFHandleMailForm');

# Markup
Markup('tellafriend','_begin','/\(:tellafriend(.*?):\)/ie',"TAFMailForm('$1');");

SDV($HandleActions['sendmail'], 'TAFMailForm');

function TAFMailForm($args) {
    global $TAFMailFormPage, $TAFMailFormSuppress;
    if ($TAFMailFormSupress) return '';
    $opts = ParseArgs($args);
    $page = ReadPage($TAFMailFormPage);
    if ($page['text']) { return $page['text']; }
    return "ERROR: [[$TAFMailFormPage]] does not exist";
}
function TAFHandleMailForm($pagename) {
    global $WikiTitle, $FmtPV, $FmtV, $HandleActions;
    global $TAFHandlePageFmt, $DefaultPageTextFmt;

    $page = RetrieveAuthPage($pagename, 'read');
    if (!$page) { Abort("?cannot read $pagename"); }
    PCache($pagename, $page);
    $pagetext = (isset($page['text'])) 
        ? $page['text']
        : FmtPageName($DefaultPageTextFmt, $pagename);
    $pagetext = '(:groupheader:)'.@$pagetext.'(:groupfooter:)';
    $FmtV['$PageText'] = MarkupToHtml($pagename, $pagetext);
    if (@$_POST) { TAFSendEmail($pagename, $pagetext); }
    PrintFmt($pagename, $TAFHandlePageFmt);
}
function TAFSendEmail($pagename, $pagetext) {
    global $FmtPV;
    global $TAFDefaultSubject, $TAFEmailAdministrator, $TAFEmailDisclaimer;
    global $TAFMailFormPage, $TAFNoHTML, $TAFVersion;

    #-----------------------------------
    # Start Email Object.
    require(TAFPATH . 'library/smtp.php');
    $Mail = new SMTP;
    $Mail->Delivery('client');
    $Mail->FromHost($_SERVER['SERVER_NAME'], $havemx) or die('From Host Error');
    if (!$havemx) die("The Hostname $hostname does not have a valid MX Zone");
    #-----------------------------------

    #-----------------------------------
    # Grab the form data.
    $f_copyToSelf = $_POST['chkSendSelf'];
    $f_toEmail = preg_split('/,/',$_POST['to']);
    $f_fromEmail = $_POST['from_email'];
    $f_fromName = ($_POST['from_name']) ? $_POST['from_name'] : 'Anonymous';
    $f_text = $_POST['text'];
    #-----------------------------------

    #-----------------------------------
    # Check for valid to and from email addresses.
    $errors = array();
    $nosend = false;
    foreach ((array) $f_toEmail as $email) {
        list($valid, $msg) = TAFCheckEmailAddress($email);
        if (!$valid) { $errors[] = $msg; } else {$Mail->AddTo($email);}
    }
    list ($valid, $msg) = TAFCheckEmailAddress($f_fromEmail);
    if (!$valid) { $errors[] = $msg; } else {
        $Mail->From($f_fromEmail, $f_fromName);
        if ($f_copyToSelf == true) $Mail->AddCC($f_fromEmail);
        $nosend = true;
    }
    if (count($errors)) $FmtPV['$TAFErrors'] = implode("\n* ", $errors);
    #-----------------------------------

    $mailtext = nl2br(stripslashes(htmlspecialchars("$f_text\n\n", ENT_NOQUOTES)));
    $pagelink = FmtPageName('$PageUrl', $pagename);
    $pagetext = preg_replace('/\(:tellafriend(.*?):\)/ie','',$pagetext);
    $pagetext = MarkupToHtml($pagename, $pagetext);
    $footer = "$TAFEmailDisclaimer\n\n$TAFNoHTML <a href='$pagelink'>$pagelink</a>";
    $message = implode('<hr />', array($mailtext, $pagetext, nl2br($footer)));

    $Mail->Html($message);
    $Mail->AddBcc($TAFEmailAdministrator);
    $Mail->AddHeader('X-Composer', $TAFVersion);
    if (!$nosend) $sent = $Mail->Send($TAFDefaultSubject);
}
function TAFCheckEmailAddress($e) {
 /*
   Try several things to make sure it is most likely valid:
   1.  preg_match it to make sure it looks valid
   2a. If that passes, check for an MX entry for the domain
   2b. If no MX, check for any DNS entry for the domain
  */
  if (!preg_match("/(^[a-z0-9.+-_]{1,64})@([a-z0-9-]+(.[a-z0-9-]+){1,255})$/i", $e, $m)){
    return array(false, "Invalid address format ($e)");
  }
  return (getmxrr($m[2], $mx_records) || $m[2] != gethostbyname($m[2]))
    ? array(true, "Ok.")
    : array(false, "No DNS or MX record for ($e).");
}
