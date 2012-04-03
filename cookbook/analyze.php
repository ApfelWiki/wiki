<?php if (!defined('PmWiki')) exit();
/*  Copyright 2006-2007 Patrick R. Michaud (pmichaud@pobox.com)
    This file is analyze.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  

    This script reports back a number of configuration 
    and local settings for the site analyzer at 
    http://www.pmwiki.org/wiki/PmWiki/SiteAnalyzer .  It creates a
    special ?action=analyze operation, which can only be used
    if the calling system knows the correct key (as set by the
    $AnalyzeKey variable).

    A side effect of ?action=analyze is that it creates a
    Site.Analyze page to test permissions and other items 
    associated with creating a page.

    The following variables are available for customization:

    $AnalyzeKey  - key string to allow access to ?action=analyze
    $AnalyzeSkip - array of test names to be skipped during analysis
    $EnableAnalyzeSave - If set to 1, then allow pmwiki.org to save
      a copy of the analyzer results for further analysis.
      If set to 0 (default), then use the value of the 
      "save results" checkbox in the analyzer form.  If set to -1,
      never allow results to be saved, regardless of the checkbox
      setting.
*/    

$HandleActions['analyze'] = 'HandleAnalyze';
$HandleAuth['analyze'] = 'auth';

SDV($RecipeInfo['PmWiki:SiteAnalyzer']['Version'], '2007-02-16');

function HandleAnalyze($pagename, $auth='read') {
  global $AnalyzeKey, $AnalyzeSkip, $EnableAnalyzeSave, $RecipeInfo;
  header('Content-type: text/plain');

  if (!$AnalyzeKey) { print "NoKey=1\n"; exit(0); }
  if ($_REQUEST['key'] != $AnalyzeKey) { print "InvalidKey=1\n"; exit(0); }

  WritePage('Site.Analyze', 
            array('text'=>'Test Analysis page', 
                  'passwdread' => '@lock'));

  echo 'AnalyzeVersion=', $RecipeInfo['PmWiki:SiteAnalyzer']['Version'], "\n";

  SDV($EnableAnalyzeSave, 0);
  $vars = array('EnableAnalyzeSave', 'VersionNum', 'Version',
                'ScriptUrl', 'PubDirUrl',
                'FarmD', 'WorkDir', 'EnableDiag');
  foreach($vars as $k) { $v = @$GLOBALS[$k]; echo "$k=$v\n"; }

  echo 'AnalyzeSkip=', join(',', (array)$AnalyzeSkip), "\n";

  $conf = array('register_globals', 'post_max_size', 
                'safe_mode', 'safe_mode_gid', 'upload_max_filesize');
  foreach($conf as $v) echo "$v=", ini_get($v), "\n";

  $fn = array('phpversion', 'session_save_path', 'get_magic_quotes_gpc', 
              'get_magic_quotes_runtime');
  foreach($fn as $f) echo "$f=".$f()."\n";

  $session = array('SERVER_SOFTWARE', 'PHP_SELF', 'REQUEST_URI', 
                   'SCRIPT_NAME');
  foreach($session as $s) echo "$s={$_SERVER[$s]}\n";

  global $FarmD, $LocalDir, $WorkDir, $UploadDir;
  $files = array('farmd' => $FarmD, 'cwd' => getcwd(), 
                 'local' => $LocalDir, 'workdir' => $WorkDir,
                 'uploads' => $UploadDir,
                 'cookbook' => 'cookbook',
                 'scripts' => "$FarmD/scripts", 
                 'cookbook_farm' => "$FarmD/cookbook",);
  foreach($files as $f=>$v) {
    if (!file_exists($v)) continue;
    $owner = @fileowner($v);
    $group = @filegroup($v);
    $perms = sprintf("%o", @fileperms($v));
    echo "$f=$owner:$group:$perms:$v\n";
  }

  $dir = 'cookbook';
  $dfp = @opendir($dir);
  if ($dfp) {
    $cookbookfiles = array();
    while ( ($name = readdir($dfp)) !== false ) {
      if ($name{0} == '.') continue;
      $cookbookfiles[] = $name;
      $text = implode('', @file("$dir/$name"));
      if (preg_match("/^\\s*\\\$RecipeInfo\\['(.*?)'\\]\\['Version'\\]\\s*=\\s*'(.*?)'\\s*;/m", $text, $match))
        SDV($RecipeInfo[$match[1]]['Version'], $match[2]);
      if (preg_match("/^\\s*SDV\\(\\s*\\\$RecipeInfo\\['(.*?)'\\]\\['Version'\\]\\s*,\\s*'(.*?)'\\s*\\)\\s*\\;/m", $text, $match))
        SDV($RecipeInfo[$match[1]]['Version'], $match[2]);
    }
    closedir($dfp);
    echo "CookbookFiles=", implode(' ', $cookbookfiles), "\n";
  }

  if (@$RecipeInfo) 
    echo "RecipeInfo=" . count($RecipeInfo) . "\n";
  foreach((array)$RecipeInfo as $r => $v) 
    if ($v['Version']) echo "RecipeInfo[$r]={$v['Version']}\n";

  exit(0);
}
