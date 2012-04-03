<?php if (!defined('PmWiki')) exit();
/*  Copyright 2007 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

*/

$RecipeInfo['Captcha']['Version'] = '2008-07-13';

SDV($CaptchaValue, rand(1000, 9999));
SDV($CaptchaName, 'response');
SDV($EnableCaptchaImage, (int)function_exists('imagecreatetruecolor'));

SDV($FmtPV['$CaptchaKey'], "\$GLOBALS['CaptchaKey']");
SDV($FmtPV['$CaptchaValue'], 'CaptchaValue()');
SDV($FmtPV['$Captcha'], 'CaptchaFn($pn)');

SDV($Conditions['captcha'], '(boolean)IsCaptcha()');

SDVA($InputTags['captcha'], array(
  ':fn' => 'InputCaptcha',
  ':html' => "<input type='hidden' name='captchakey' value='\$CaptchaKey' /><input type='text' \$InputFormArgs />",
  ':args' => array('value'),
  'size' => 5,
  'name' => $CaptchaName,
  ));

array_unshift($EditFunctions, 'RequireCaptcha');
SDV($HandleActions['captchaimage'], 'HandleCaptchaImage');
SDV($HandleAuth['captchaimage'], 'read');

function RequireCaptcha($pagename, $page, $new) {
  global $EnablePostCaptchaRequired, $MessagesFmt, 
    $CaptchaRequiredFmt, $EnablePost;
  if (!IsEnabled($EnablePostCaptchaRequired, 0)) return;
  if (IsCaptcha()) return;
  SDV($CaptchaRequiredFmt, 
    "<div class='wikimessage'>$[Must enter valid code]</div>");
  $MessagesFmt[] = $CaptchaRequiredFmt;
  $EnablePost = 0;
}

function IsCaptcha() {
  global $IsCaptcha, $CaptchaName, $EnableCaptchaSession;
  if (isset($IsCaptcha)) return $IsCaptcha;
  $key = @$_POST['captchakey'];
  $resp = @$_POST[$CaptchaName];
  $sid = session_id();
  @session_start();
  if ($key && $resp && @$_SESSION['captcha-challenges'][$key] == $resp)
    $IsCaptcha = 1;
  if (IsEnabled($EnableCaptchaSession, 0)) {
    $IsCaptcha |= @$_SESSION['iscaptcha'];
    @$_SESSION['iscaptcha'] = $IsCaptcha;
  }
  $IsCaptcha = (int)@$IsCaptcha;
  if (!$sid) session_write_close();
  return $IsCaptcha;
}
  

function InputCaptcha($pagename, $type, $args) {
  CaptchaValue();
  return Keep(InputToHTML($pagename, $type, $args, $opt));
}


function CaptchaValue() {
  global $CaptchaKey, $CaptchaValue;
  if ($CaptchaKey > '' &&
      @$_SESSION['captcha-challenges'][$CaptchaKey] == $CaptchaValue) 
    return $CaptchaValue;
  $sid = session_id();
  @session_start();
  if ($CaptchaKey == '') $CaptchaKey = count(@$_SESSION['captcha-challenges']);
  $_SESSION['captcha-challenges'][$CaptchaKey] = $CaptchaValue;
  if (!$sid) session_write_close();
  return $CaptchaValue;
}


function CaptchaFn($pagename) {
  global $CaptchaChallenge, $EnableCaptchaImage;
  if (@$CaptchaChallenge) return $CaptchaChallenge;
  if ($EnableCaptchaImage) return CaptchaImage($pagename);
  return CaptchaValue();
}


function CaptchaImage($pagename) {
  global $CaptchaImageFmt;
  CaptchaValue();
  SDV($CaptchaImageFmt, "<img src='{\$PageUrl}?action=captchaimage&amp;captchakey={\$CaptchaKey}' border='0' align='top' />");
  return Keep(FmtPageName($CaptchaImageFmt, $pagename));
}

function HandleCaptchaImage($pagename, $auth = 'read') {
  global $CaptchaImage;
  $key = @$_REQUEST['captchakey'];
  if ($key == '') return '';
  @session_start();
  $value = @$_SESSION['captcha-challenges'][$key];
  if (!$value) return '';

  $width = 60;
  $height = 22;
  $fontwidth = 10;
  $fontheight = 14;
  $img = imagecreatetruecolor($width, $height);
  $white = imagecolorallocate($img, 240, 240, 240);
  imagefilledrectangle($img, 0, 0, $width, $height, $white);
  imagealphablending($img, 1);
  imagecolortransparent($img);
  for($i=0; $i < 100; $i++) {
    $r = rand(200, 255); $g = rand(200, 255); $b = rand(200, 255);
    $color = imagecolorallocate($img, $r, $g, $b);
    imagefilledellipse($img, round(rand(0, $width)), round(rand(0, $height)),
        round(rand(0, $width/8)), round(rand(0, $height/4)), $color);
  }
  $vlen = strlen($value);
  $x = rand(2, $width/$vlen);
  for($i=0; $i < $vlen; $i++) {
    $y = rand(2, $height - $fontheight - 2);
    $r = rand(0, 150); $g = rand(0, 150); $b = rand(0, 150);
    $fg = imagecolorallocatealpha($img, $r, $g, $b, 30);
    $c = substr($value, $i, 1);
    imagechar($img, 5, $x, $y, $c, $fg);
    $x += rand($fontwidth + 2, ($width-$x)/($vlen-$i));
  }
  header('Content-type: image/jpeg');
  header('Cache-Control: no-store, no-cache, must-revalidate');
  header('Expires: Tue, 01 Jan 2002 00:00:00 GMT');
  imagejpeg($img);
  return;
}

