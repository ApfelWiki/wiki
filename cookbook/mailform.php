<?php

/*
   bram brambring
   http://www.brambring.nl


               $Id: TellAFriend.php,v 1.4 2004/02/12 08:11:04 pts00065 Exp $



       *Save this file ( fe as local/mailform.php
       *Add include_once("local/mailform.php"); to the end of your local.php
       *Make sure the variables MailPostsFrom and MailPostsTo are set in your local.php
       (these are the same as used by mailposts)
       *Change the parameters below to something you like (in your local.php)

     $Log: TellAFriend.php,v $
     Revision 1.4  2004/02/12 08:11:04  pts00065
     MailFormThankYou global

     Revision 1.3  2004/02/12 07:41:44  pts00065
     beta9

     Revision 1.2  2004/02/12 07:15:06  pts00065
     -

     Revision 1.1  2004/02/11 16:49:07  pts00065
     email form

*/
$MailFormTitle="DIESES FEATURE IST ERST IN VORBEREITUNG!";
$MailFormThankYou="Die Seite wurde versandt.";
$MailFormSendTo="Senden an";
$MailFormSendFrom="Ihre Nachricht";
$MailFormText="Nachrichtentext eingeben";
$MailFormSubject="Betreff";
$MailFormDefaultText="Es funktioniert wirklich noch nicht. :)";
//$MailFromErrorEmail="Dit ziet er niet echt uit als een  email adres:";
//$MailFormNoHTML="Geen HTML? Bezoek de pagina";
//$MailFromDisclaimer="Dit bericht is verstuurd van een email formulier op <A HREF='$ScriptUrl'>$WikiTitle</a>. De beheerder van de site heeft geen invloed op het gebruik van het formulier.";

SDV($MailFormTitle,"Mail a Friend");
SDV($MailFormThankYou,"Thanks ! The page has been send");
SDV($MailFormSendTo,"Send this page by email to");
SDV($MailFormSendFrom,"Your email");
SDV($MailFormText,"Enter a message");
SDV($MailFormSubject,"Cool page");
SDV($MailFormNoHTML,"No HTML? Visit the page");
SDV($MailFormDefaultText,"Hi, I found this cool page! Maybe worth to look at");
SDV($MailFromErrorEmail,"This email doesn't look right:");
SDV($MailFromDisclaimer,"This email has been send from an mailform on <A HREF='$ScriptUrl'>$WikiTitle</a>. We appolegize if this mail bla bla");

if ($action!='mailform') return;
//SDV($HandleActions['mailform'],'HandleMailForm');

SDV($HandleMailFmt,array(&$PageStartFmt,
  &$PageMailFormFmt,'function:PrintText',
  &$PageEndFmt));



SDV($PageMailFormFmt,"
  <a id='top' name='top'></a><h1 class='wikiaction'>$MailFormTitle</h1></a>
  <form action='\$PageUrl' method='post'>
  <input type='hidden' name='pagename' value='\$fullName' />
  <input type='hidden' name='action' value='mailform' />
  $MailFormSendTo<br /><input type=text' name='sendto' size=50><BR />
  $MailFormSendFrom<br /><input type=text' name='sendfrom' size=50><BR />
  $MailFormText<br /><textarea cols=50 rows=5 name='mailtext' size=50>$MailFormDefaultText</textarea><BR />
  <input type='submit' name='post' value=' $[Send] ' />
  <input type='reset' value=' $[Reset] ' />
  </form>");

function HandleMailForm($FullName) {
  global $HandleMailFmt,$HandleActions,$Text,$MailFormTitle;
  $page = RetrieveAuthPage($FullName,"read");
  if (!$page) { Abort("?cannot read $fullname"); }
  $Text=$page['text'];
  //SetPageVars($FullName,$page,$MailFormTitle);
  ProcessTextDirectives($FullName);
  if (@$_POST['post'])
    { HandlePostMailForm($fullname); }
  PrintFmt($fullname,$HandleMailFmt);
}

function HandlePostMailForm($fullname) {
global $PageMailFormFmt;
global $MailFormDefaultText;
global $MailFromErrorEmail;
global $WikiTitle;
global $MailPostsFrom;
global $MailPostsTo;
global $MailFormNoHTML;
global $MailFormThankYou;
$sendto=@$_POST['sendto'];
$sendfrom=@$_POST['sendfrom'];
$mailtext=@$_POST['mailtext'];
// Validate the Email Address
$pagelink=FmtPageName('$PageUrl',$fullname);
if(!ereg("^([a-z0-9]|\\-|\\.)+@(([a-z0-9]|\\-)+\\.)+[a-z]{2,4}\$",$sendto)){
  $error = 1;
  $error_html .= "$MailFromErrorEmail: $sendto<br><br>\n";
}
if(!ereg("^([a-z0-9]|\\-|\\.)+@(([a-z0-9]|\\-)+\\.)+[a-z]{2,4}\$",$sendfrom)){
  $error = 1;
  $error_html .= "$MailFromErrorEmail: $sendfrom<br><br>\n";
}
if ( $error ) {
  $PageMailFormFmt=$error_html;
  return;
}
$PageMailFormFmt="$MailFormThankYou<HR>";
$mailtext= htmlspecialchars($mailtext,ENT_NOQUOTES);
$message ="$MailFormNoHTML : $pagelink\n\n";
$message .="<pre style='font-size:16px'>$mailtext</pre>" ;
$message .="<A HREF='$pagelink'>$pagelink</a><HR>";

ob_start();
PrintText($fullname);
$message .=ob_get_clean();

$message .="<HR>$MailFromDisclaimer";

/* recipients */
$to  = $sendto ;
/* subject */
$subject = "$MailFormDefaultText $fullname";
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
/* additional headers */
$headers .= "From:  $WikiTitle <$MailPostsFrom>\r\n";
$headers .= "Cc: $sendfrom\r\n";
$headers .= "Bcc: $MailPostsTo\r\n";
/* and now mail it */
mail($to, $subject, $message, $headers);
#print "<DIV style='color:white' target="_Blank">$message</div>" ;
}
?>
