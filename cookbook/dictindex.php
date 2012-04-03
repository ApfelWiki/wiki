<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com).

    This file adds a "dictindex" (dictionary index) format for
    the (:pagelist:) and (:searchresults:) directives.  To see results
    in dictionary format, simply add fmt=dictindex to the directive, as in

        (:pagelist group=Main fmt=dictindex:)

    By default the items are display as a simple definition list, but
    this can be controlled by $FPLDictIndex...Fmt variables:

        $FPLDictIndexStartFmt - start string
        $FPLDictIndexEndFmt   - end string
        $FPLDictIndexLFmt     - string to output for each new letter
        $FPLDictIndexIFmt     - string to output for each item in list

    To enable this module, simply add the following to config.php:

        include_once('cookbook/dictindex.php');

*/

$FPLFunctions['dictindex'] = 'FPLDictIndex';

function FPLDictIndex($pagename,&$matches,$opt) {
  global $FPLDictIndexStartFmt,$FPLDictIndexEndFmt,
    $FPLDictIndexLFmt,$FPLDictIndexIFmt,$FmtV;
  $matches = MakePageList($pagename, $opt);
  for($n=0;$n<count($matches);$n++) 
    $matches[$n]['name'] = FmtPageName('$Name',$matches[$n]['pagename']);
  $cmp = create_function('$x,$y',
    "return strcasecmp(\$x['name'],\$y['name']);");
  usort($matches,$cmp);
  SDV($FPLDictIndexStartFmt,"<dl class='fpldictindex'>");
  SDV($FPLDictIndexEndFmt,'</dl>');
  SDV($FPLDictIndexLFmt,"<dt>\$IndexLetter</dt>");
  SDV($FPLDictIndexIFmt,"<dd><a href='\$PageUrl'>\$Name</a></dd>");
  $out = array();
  foreach($matches as $item) {
    $pletter = substr($item['name'],0,1);
    $FmtV['$IndexLetter'] = $pletter;
    if ($pletter!=@$lletter) { 
      $out[] = FmtPageName($FPLDictIndexLFmt,$item['pagename']);
      $lletter = $pletter; 
    }
    $out[] = FmtPageName($FPLDictIndexIFmt,$item['pagename']);
  }
  return FmtPageName($FPLDictIndexStartFmt,$pagename).implode('',$out).
    FmtPageName($FPLDictIndexEndFmt,$pagename);
}

?>
