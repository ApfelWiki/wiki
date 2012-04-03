<?php if (!defined('PmWiki')) exit();
/*  Copyright 2004 Patrick R. Michaud (pmichaud@pobox.com).

    This file adds a "dictindex" (dictionary index) format for
    the (:pagelist:) and (:searchresults:) directives.  To see results
    in dictionary format, simply add fmt=dictindex to the directive, as in

        (:pagelist group=Main fmt=dictindex:)

    By default the items are display as a simple definition list, but
    this can be controlled by $FPLDictIndex...Fmt variables:

        $FPLDictIndexStartFmt   - start string
        $FPLDictIndexEndFmt     - end string
        $FPLDictIndexLFmt       - string to output for each new letter
        $FPLDictIndexIFmt       - string to output for each item in list
        $FPLDictIndexHeaderLink - string for the link list at the upper page

    To enable this module, simply add the following to config.php:

        include_once('cookbook/titledictindex.php');

*/

$FPLFunctions['dictindex'] = 'FPLDictIndex';

function FPLDictIndex($pagename,&$matches,$opt) {
global $FPLDictIndexStartFmt,$FPLDictIndexEndFmt,
    $FPLDictIndexLFmt,$FPLDictIndexIFmt, $FPLDictIndexHeaderLink, $FmtV;
  $opt['order']='title';
  $matches = MakePageList($pagename, $opt);
  SDV($FPLDictIndexStartFmt,"<a name='dictindexheader'></a><dl class='fpldictindex'><span>\$IndexLinks</span><hr><p class='vspace'></p>\n");
  SDV($FPLDictIndexEndFmt,'</dl>');
  SDV($FPLDictIndexLFmt,"<dt><a href='#dictindexheader'>&#9650;</a> <a name='\$IndexLetter'></a>\$IndexLetter</dt>\n");
  SDV($FPLDictIndexIFmt,"<dd><a href='\$PageUrl'>\$Title</a></dd>\n");
  SDV($FPLDictIndexHeaderLink,"\n".'<a href="#$IndexLetter">$IndexLetter</a>');
  $out = array();
  $headerlinks= array();
  foreach($matches as $item) {
    $pletter = substr($item['title'],0,1);
    $FmtV['$IndexLetter'] = $pletter;
    if (strcasecmp($pletter,@$lletter)!=0) { 
      $out[] = FmtPageName($FPLDictIndexLFmt,$item['pagename']);
      $headerlinks[] = FmtPageName($FPLDictIndexHeaderLink,$item['pagename']);
      $lletter = $pletter; 
    }
    $out[] = FmtPageName($FPLDictIndexIFmt,$item['pagename']);
  }
  $FmtV['$IndexLinks']=implode(' &bull; ',$headerlinks);
  return FmtPageName($FPLDictIndexStartFmt,$pagename).implode('',$out).
    FmtPageName($FPLDictIndexEndFmt,$pagename);
}

?>
