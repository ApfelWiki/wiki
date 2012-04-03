<?php if (!defined('PmWiki')) exit();

	/*
   bram brambring
   http://www.brambring.nl
   Jeff Barke (jbarke@milwaukeedept.org)


               $Id: TellAFriend.php,v 2.0 2004/12/08 15:12:10 pts00065 Exp $



       *Save this file ( fe as local/SendMail.php
       *Add include_once("local/SendMail.php"); to the end of your local.php
       *Make sure the variables MailPostsFrom and MailPostsTo are set in your local.php
       (these are the same as used by mailposts)
       *Change the parameters below to something you like (in your local.php)

     $Log: TellAFriend.php,v $
	 Revision 2.0  2004/12/08 15:12:10 
	 Updated TellAFriend.php to work with PmWiki 2.0
	 
     Revision 1.4  2004/02/12 08:11:04  pts00065
     SendMailThankYou global

     Revision 1.3  2004/02/12 07:41:44  pts00065
     beta9

     Revision 1.2  2004/02/12 07:15:06  pts00065
     -

     Revision 1.1  2004/02/11 16:49:07  pts00065
     email form

*/
$SendMailTitle="Diese Seite versenden";
$SendMailThankYou="<p>Die Seite wurde versandt.</p>";
$SendMailSendTo="Senden an:";
$SendMailSendFrom="Ihre E-Mail:";
$SendMailText="Ihre Nachricht eingeben:";
$SendMailSubject="Betreff:";
$SendMailDefaultText="";
$MailFromErrorEmail="Die angegebene E-Mailadresse ist ungültig. &nbsp;Versuchen sie es erneut.";
$SendMailNoHTML="kein HTML? &nbsp;Dann besuchen Sie die Seite im Internet.";
$MailFromDisclaimer="This page has been sent from a SendMail on <a href='$ScriptUrl'>$WikiTitle</a>.";

SDV($SendMailTitle,"Mail a Friend");
SDV($SendMailThankYou,"Thanks ! The page has been send");
SDV($SendMailSendTo,"Send this page by email to");
SDV($SendMailSendFrom,"Your email");
SDV($SendMailText,"Enter a message");
SDV($SendMailSubject,"Cool page");
SDV($SendMailNoHTML,"No HTML? Visit the page");
SDV($SendMailDefaultText,"Hi, I found this cool page! Maybe worth to look at");
SDV($MailFromErrorEmail,"This email doesn't look right:");
SDV($MailFromDisclaimer,"This email has been send from an SendMail on <A HREF='$ScriptUrl'>$WikiTitle</a>. We appolegize if this mail bla bla");


SDV($HandleActions['sendmail'], 'HandleSendMail');

SDV($HandleMailFmt,array(&$PageStartFmt,
  &$PageSendMailFmt, '$PageText',
  &$PageEndFmt));

SDV($PageSendMailFmt,"
  <a id='top' name='top'></a><h1 class='wikiaction'>$SendMailTitle</h1></a>
  <form action='\$PageUrl' method='post'>
  <input type='hidden' name='pagename' value='\$PageName' />
  <input type='hidden' name='action' value='sendmail' />
  $SendMailSendTo<br /><input type=text' name='sendto' size=50><BR />
  $SendMailSendFrom<br /><input type=text' name='sendfrom' size=50><BR />
  $SendMailText<br /><textarea cols=50 rows=5 name='mailtext' size=50>$SendMailDefaultText</textarea><BR />
  <input type='submit' name='post' value=' $[Send] ' />
  <input type='reset' value=' $[Reset] ' />
  <hr />
  </form>");

function HandleSendMail($pagename) {

	global $HandleMailFmt, $HandleActions, $FmtV, $SendMailTitle;
 
  	$page = RetrieveAuthPage($pagename,"read");
  	if (!$page) { Abort("?cannot read $pagename"); }
  	PCache($pagename, $page);
  	if (isset($page['text'])) $text=$page['text'];
  	else $text = FmtPageName($DefaultPageTextFmt,$pagename);
  	$text = '(:groupheader:)'.@$text.'(:groupfooter:)';
  	$FmtV['$PageText'] = MarkupToHTML($pagename,$text);
  	if (@$_POST['post'])
    	{ HandlePostSendMail($pagename); }
  	PrintFmt($pagename,$HandleMailFmt);
  
}

function HandlePostSendMail($pagename) {

	global $PageSendMailFmt;
	global $SendMailDefaultText;
	global $MailFromErrorEmail;
	global $WikiTitle;
	global $MailPostsFrom;
	global $MailPostsTo;
	global $SendMailNoHTML;
	global $SendMailThankYou;
	global $FmtV;
	$sendto=@$_POST['sendto'];
	$sendfrom=@$_POST['sendfrom'];
	$mailtext=@$_POST['mailtext'];

// Validate the Email Address
$pagelink=FmtPageName('$PageUrl',$pagename);
if(!ereg("^([a-z0-9]|\\-|\\.)+@(([a-z0-9]|\\-)+\\.)+[a-z]{2,4}\$",$sendto)){
  $error = 1;
  $error_html .= "$MailFromErrorEmail: $sendto<br><br>\n";
}
if(!ereg("^([a-z0-9]|\\-|\\.)+@(([a-z0-9]|\\-)+\\.)+[a-z]{2,4}\$",$sendfrom)){
  $error = 1;
  $error_html .= "$MailFromErrorEmail: $sendfrom<br><br>\n";
}
if ( $error ) {
  $PageSendMailFmt=$error_html;
  return;
}
	$PageSendMailFmt="$SendMailThankYou<hr />";
	$mailtext= htmlspecialchars($mailtext,ENT_NOQUOTES);
	$message ="Ein Freund hat Ihnen diese Seite zugeleitet mit folgender Nachricht:";
	$message .="<pre style='font-size:12px'>$mailtext</pre>" ;
	$message .="$SendMailNoHTML : <A HREF='$pagelink'>$pagelink</a><HR>";

	$message .= $FmtV['$PageText'];

	$message .="<HR>$MailFromDisclaimer";

	/* recipients */
	$to  = $sendto ;
	/* subject */
	$subject = "$SendMailDefaultText $pagename";
	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	/* additional headers */
	$headers .= "From:  $WikiTitle <$MailPostsFrom>\r\n";
	$headers .= "Cc: $sendfrom\r\n";
	//$headers .= "Bcc: $MailPostsTo\r\n";
	/* and now mail it */
	mail($to, $subject, $message, $headers);

}

?>