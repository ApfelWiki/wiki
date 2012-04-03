<?php if (!defined('PmWiki')) exit();
/*  
	@version: 1.4

*/



SDV($RssDiffDelFmt['a'],"<i style='color:red;'><b>\$[Deleted line \$DiffLines:]</b></i><br/>");
SDV($RssDiffDelFmt['c'],"<i style='color:red;'><b>\$[Changed line \$DiffLines from:]</b></i><br />");
SDV($RssDiffAddFmt['d'],"<i style='color:green;'><b>\$[Added line \$DiffLines:]</b></i><br/>");
SDV($RssDiffAddFmt['c'],"<br/><i style='color:green;'><b>$[to:]</b></i><br/> ");
SDV($RssDiffEndDelAddFmt,"<br/>");



/***********************************/

SDV($HandleActions['rss'],'HandleRss');
SDV($HandleActions['rdf'],'HandleRss');

SDV($RssMaxItems,5);				# maximum items to display
SDV($RssSourceSize,400);			# max size to build desc from
SDV($RssDescSize,5000);				# max desc size
SDV($RssItems,array());				# RSS item elements
SDV($RssItemsRDFList,array());			# RDF <items> elements

if ($action=='rdf') {
  ### RSS 1.0 (RDF) definitions
  SDV($RssTimeFmt,'%Y-%m-%dT%H:%MZ');	# time format
  SDV($RssItemsRDFListFmt,"<rdf:li rdf:resource=\"\$PageUrl\" />\n");
  SDV($RssChannelFmt,array('<?xml version="1.0"?'.'>
    <rdf:RDF  xmlns="http://purl.org/rss/1.0/"
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
        xmlns:dc="http://purl.org/dc/elements/1.1/">
      <channel rdf:about="$PageUrl">
        <title>$WikiTitle | $Group / $Title</title>
        <link>$PageUrl</link>
        <description>$RssChannelDesc</description>
        <dc:date>$RssChannelBuildDate</dc:date>
        <items>
          <rdf:Seq>',&$RssItemsRDFList,'
          </rdf:Seq>
        </items>
      </channel>'));
  SDV($RssItemFmt,'
      <item rdf:about="$PageUrl">
        <title>$WikiTitle | $Group / $Title</title>
        <link>$PageUrl</link>
        <description>$RssItemDesc</description>
        <dc:date>$RssItemPubDate</dc:date>
      </item>');
  SDV($HandleRssFmt,array(&$RssChannelFmt,&$RssItems,'</rdf:RDF>'));
}

### RSS 2.0 definitions
SDV($RssTimeFmt,'%Y-%m-%dT%H:%MZ');
SDV($RssChannelFmt,'<?xml version="1.0" encoding="UTF-8"?>
  	<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
      <title>$WikiTitle | $Group / $Title</title>
      <link>$PageUrl</link>
      <description>$RssChannelDesc</description>
      <lastBuildDate>$RssChannelBuildDate</lastBuildDate>
      <generator>$Version</generator>');
SDV($RssItemFmt,'
        <item>
          <title>$Group - $RssItemTitle ... $RssItemTime von $RssItemAuthor - $RssItemChangeSum</title>
          <link>$PageUrl</link>
          <description>$RssItemDesc</description>
          <dc:contributor>$RssItemAuthor</dc:contributor>
          <dc:date>$RssItemPubDate</dc:date>
        </item>');
SDV($HandleRssFmt,array(&$RssChannelFmt,&$RssItems,'</channel></rss>'));

function HandleRss($pagename) {
  global $RssMaxItems,$RssSourceSize,$RssDescSize,
    $RssChannelFmt,$RssChannelDesc,$RssTimeFmt,$RssChannelBuildDate,
    $RssItemsRDFList,$RssItemsRDFListFmt,$RssItems,$RssItemFmt,
    $HandleRssFmt,$FmtV;
    
    global $RssDiffDelFmt,$RssDiffAddFmt, $RssDiffEndDelAddFmt;
    
  $t = ReadTrail($pagename,$pagename);
  $page = RetrieveAuthPage($pagename, 'read', false, READPAGE_CURRENT);
  if (!$page) Abort("?cannot read $pagename");
  $cbgmt = $page['time'];
  $r = array();
  for($i=0;$i<count($t) && count($r)<$RssMaxItems;$i++) {
      if (!PageExists($t[$i]['pagename'])) continue;
    $page = RetrieveAuthPage($t[$i]['pagename'],'read',false); 
    if (!$page) continue;
    	
    #look if there is a page title
	if (preg_match("/\\(:title\\s(.*?):\\)/",$page['text'],$pagetitle))
		$pagetitle = $pagetitle[1];
	else
		$pagetitle = FmtPageName("\$Name",$t[$i]['pagename']);	
    		
    	$text="";
    	foreach ($page as $k => $v) {
			if (!preg_match("/^diff:(\d+):(\d+):?([^:]*)/", $k, $match))
				continue;
			$diffgmt = $match[1];
			$csum = utf8_decode($page["csum:$diffgmt"]);

			$difflines = explode("\n", $v."\n");
			$in = array ();
			$out = array ();
			$dtype = '';
			
			foreach($difflines as $d) {
		      if ($d>'') {
		        if ($d[0]=='-' || $d[0]=='\\') continue;
		        if ($d[0]=='<') { $out[]=substr($d,2); continue; }
		        if ($d[0]=='>') { $in[]=substr($d,2); continue; }
		      }
		      if (preg_match("/^(\\d+)(,(\\d+))?([adc])(\\d+)(,(\\d+))?/",
		          $dtype,$match)) {
		        if (@$match[7]>'') {
		          $lines='lines';
		          $count=$match[1].'-'.($match[1]+$match[7]-$match[5]);
		        } elseif ($match[3]>'') {
		          $lines='lines'; $count=$match[1].'-'.$match[3];
		        } else { $lines='line'; $count=$match[1]; }
		        if ($match[4]=='a' || $match[4]=='c') {
		        	 $txt = str_replace('line',$lines,$RssDiffDelFmt[$match[4]]);
          		 $FmtV['$DiffLines'] = $count;
         		 $text.= FmtPageName($txt,$pagename);
		       	$text.= str_replace("\n","<br />",htmlspecialchars(join("\n",$in)));
		        }
		        if ($match[4]=='d' || $match[4]=='c') {
		        	  $txt = str_replace('line',$lines,$RssDiffAddFmt[$match[4]]);
          		 $FmtV['$DiffLines'] = $count;
         		 $text.= FmtPageName($txt,$pagename);
		          $text.= str_replace("\n","<br />",htmlspecialchars(join("\n",$out)));
		        }
				$text .= FmtPageName($RssDiffEndDelAddFmt,$pagename);
		      }
		      $in=array(); $out=array(); $dtype=$d;
		    }
		
			break;
			
		}
    
    		$text = "<![CDATA[". (utf8_decode($text))."]]>";
    		$text = entityencode(preg_replace("/<(?!!\[CDATA\[|i|\/i|b|\/b).*?>/s", "", $text));
	
		if (strstr($t[$i]['pagename'],"PITS")) $text = "";
	    	
	    	$r[] = array(
			'name' => $t[$i]['pagename'],
			'time' => $page['time'],
	       	'desc' => $text, 
			'author' => utf8_decode($page['author']), 
			'csum' => $csum, 
			'pagetitle' => utf8_decode($pagetitle),
		);
	    	
	    	if ($page['time']>$cbgmt) $cbgmt=$page['time'];
  	}
  
  	SDV($RssChannelBuildDate,
    entityencode(gmdate('D, d M Y H:i:s \G\M\T', $cbgmt)));
  	SDV($RssChannelDesc,entityencode(FmtPageName('$Group.$Title',$pagename)));
  	foreach($r as $page) {
    		$FmtV['$RssItemTitle'] = $page['pagetitle'];
    		$FmtV['$RssItemPubDate'] = gmstrftime($RssTimeFmt,$page['time']);
    		$FmtV['$RssItemTime'] = strftime('%d.%m. %H:%M',$page['time']);
    		$FmtV['$RssItemDesc'] = $page['desc']; 
    		$FmtV['$RssItemAuthor'] = $page['author'];
    		$FmtV['$RssItemChangeSum'] = $page['csum'];
    		$RssItemsRDFList[] = 
      		entityencode(FmtPageName($RssItemsRDFListFmt,$page['name']));
    		$RssItems[] = 
      		entityencode(FmtPageName($RssItemFmt,$page['name']));
  	}
  	header("Content-type: text/xml");
  	PrintFmt($pagename,$HandleRssFmt);
  	exit();
}

# entityencode() and $EntitiesTable are used to convert non-ASCII characters 
# and named entities into numeric entities, since the RSS and RDF
# specifications don't have a good way of incorporating them by default.
function entityencode($s) {
  global $EntitiesTable;
  $s = str_replace(array_keys($EntitiesTable),array_values($EntitiesTable),$s);
  return preg_replace('/([\\x80-\\xff])/e',"'&#'.ord('\$1').';'",$s); 
}

SDVA($EntitiesTable, array(
  # entities defined in "http://www.w3.org/TR/xhtml1/DTD/xhtml-lat1.ent"
  '&nbsp;' => '&#160;', 
  '&iexcl;' => '&#161;', 
  '&cent;' => '&#162;', 
  '&pound;' => '&#163;', 
  '&curren;' => '&#164;', 
  '&yen;' => '&#165;', 
  '&brvbar;' => '&#166;', 
  '&sect;' => '&#167;', 
  '&uml;' => '&#168;', 
  '&copy;' => '&#169;', 
  '&ordf;' => '&#170;', 
  '&laquo;' => '&#171;', 
  '&not;' => '&#172;', 
  '&shy;' => '&#173;', 
  '&reg;' => '&#174;', 
  '&macr;' => '&#175;', 
  '&deg;' => '&#176;', 
  '&plusmn;' => '&#177;', 
  '&sup2;' => '&#178;', 
  '&sup3;' => '&#179;', 
  '&acute;' => '&#180;', 
  '&micro;' => '&#181;', 
  '&para;' => '&#182;', 
  '&middot;' => '&#183;', 
  '&cedil;' => '&#184;', 
  '&sup1;' => '&#185;', 
  '&ordm;' => '&#186;', 
  '&raquo;' => '&#187;', 
  '&frac14;' => '&#188;', 
  '&frac12;' => '&#189;', 
  '&frac34;' => '&#190;', 
  '&iquest;' => '&#191;', 
  '&Agrave;' => '&#192;', 
  '&Aacute;' => '&#193;', 
  '&Acirc;' => '&#194;', 
  '&Atilde;' => '&#195;', 
  '&Auml;' => '&#196;', 
  '&Aring;' => '&#197;', 
  '&AElig;' => '&#198;', 
  '&Ccedil;' => '&#199;', 
  '&Egrave;' => '&#200;', 
  '&Eacute;' => '&#201;', 
  '&Ecirc;' => '&#202;', 
  '&Euml;' => '&#203;', 
  '&Igrave;' => '&#204;', 
  '&Iacute;' => '&#205;', 
  '&Icirc;' => '&#206;', 
  '&Iuml;' => '&#207;', 
  '&ETH;' => '&#208;', 
  '&Ntilde;' => '&#209;', 
  '&Ograve;' => '&#210;', 
  '&Oacute;' => '&#211;', 
  '&Ocirc;' => '&#212;', 
  '&Otilde;' => '&#213;', 
  '&Ouml;' => '&#214;', 
  '&times;' => '&#215;', 
  '&Oslash;' => '&#216;', 
  '&Ugrave;' => '&#217;', 
  '&Uacute;' => '&#218;', 
  '&Ucirc;' => '&#219;', 
  '&Uuml;' => '&#220;', 
  '&Yacute;' => '&#221;', 
  '&THORN;' => '&#222;', 
  '&szlig;' => '&#223;', 
  '&agrave;' => '&#224;', 
  '&aacute;' => '&#225;', 
  '&acirc;' => '&#226;', 
  '&atilde;' => '&#227;', 
  '&auml;' => '&#228;', 
  '&aring;' => '&#229;', 
  '&aelig;' => '&#230;', 
  '&ccedil;' => '&#231;', 
  '&egrave;' => '&#232;', 
  '&eacute;' => '&#233;', 
  '&ecirc;' => '&#234;', 
  '&euml;' => '&#235;', 
  '&igrave;' => '&#236;', 
  '&iacute;' => '&#237;', 
  '&icirc;' => '&#238;', 
  '&iuml;' => '&#239;', 
  '&eth;' => '&#240;', 
  '&ntilde;' => '&#241;', 
  '&ograve;' => '&#242;', 
  '&oacute;' => '&#243;', 
  '&ocirc;' => '&#244;', 
  '&otilde;' => '&#245;', 
  '&ouml;' => '&#246;', 
  '&divide;' => '&#247;', 
  '&oslash;' => '&#248;', 
  '&ugrave;' => '&#249;', 
  '&uacute;' => '&#250;', 
  '&ucirc;' => '&#251;', 
  '&uuml;' => '&#252;', 
  '&yacute;' => '&#253;', 
  '&thorn;' => '&#254;', 
  '&yuml;' => '&#255;', 
  # entities defined in "http://www.w3.org/TR/xhtml1/DTD/xhtml-special.ent"
  '&quot;' => '&#34;', 
  #'&amp;' => '&#38;#38;', 
  #'&lt;' => '&#38;#60;', 
  #'&gt;' => '&#62;', 
  '&apos;' => '&#39;', 
  '&OElig;' => '&#338;', 
  '&oelig;' => '&#339;', 
  '&Scaron;' => '&#352;', 
  '&scaron;' => '&#353;', 
  '&Yuml;' => '&#376;', 
  '&circ;' => '&#710;', 
  '&tilde;' => '&#732;', 
  '&ensp;' => '&#8194;', 
  '&emsp;' => '&#8195;', 
  '&thinsp;' => '&#8201;', 
  '&zwnj;' => '&#8204;', 
  '&zwj;' => '&#8205;', 
  '&lrm;' => '&#8206;', 
  '&rlm;' => '&#8207;', 
  '&ndash;' => '&#8211;', 
  '&mdash;' => '&#8212;', 
  '&lsquo;' => '&#8216;', 
  '&rsquo;' => '&#8217;', 
  '&sbquo;' => '&#8218;', 
  '&ldquo;' => '&#8220;', 
  '&rdquo;' => '&#8221;', 
  '&bdquo;' => '&#8222;', 
  '&dagger;' => '&#8224;', 
  '&Dagger;' => '&#8225;', 
  '&permil;' => '&#8240;', 
  '&lsaquo;' => '&#8249;', 
  '&rsaquo;' => '&#8250;', 
  '&euro;' => '&#8364;', 
  # entities defined in "http://www.w3.org/TR/xhtml1/DTD/xhtml-symbol.ent"
  '&fnof;' => '&#402;', 
  '&Alpha;' => '&#913;', 
  '&Beta;' => '&#914;', 
  '&Gamma;' => '&#915;', 
  '&Delta;' => '&#916;', 
  '&Epsilon;' => '&#917;', 
  '&Zeta;' => '&#918;', 
  '&Eta;' => '&#919;', 
  '&Theta;' => '&#920;', 
  '&Iota;' => '&#921;', 
  '&Kappa;' => '&#922;', 
  '&Lambda;' => '&#923;', 
  '&Mu;' => '&#924;', 
  '&Nu;' => '&#925;', 
  '&Xi;' => '&#926;', 
  '&Omicron;' => '&#927;', 
  '&Pi;' => '&#928;', 
  '&Rho;' => '&#929;', 
  '&Sigma;' => '&#931;', 
  '&Tau;' => '&#932;', 
  '&Upsilon;' => '&#933;', 
  '&Phi;' => '&#934;', 
  '&Chi;' => '&#935;', 
  '&Psi;' => '&#936;', 
  '&Omega;' => '&#937;', 
  '&alpha;' => '&#945;', 
  '&beta;' => '&#946;', 
  '&gamma;' => '&#947;', 
  '&delta;' => '&#948;', 
  '&epsilon;' => '&#949;', 
  '&zeta;' => '&#950;', 
  '&eta;' => '&#951;', 
  '&theta;' => '&#952;', 
  '&iota;' => '&#953;', 
  '&kappa;' => '&#954;', 
  '&lambda;' => '&#955;', 
  '&mu;' => '&#956;', 
  '&nu;' => '&#957;', 
  '&xi;' => '&#958;', 
  '&omicron;' => '&#959;', 
  '&pi;' => '&#960;', 
  '&rho;' => '&#961;', 
  '&sigmaf;' => '&#962;', 
  '&sigma;' => '&#963;', 
  '&tau;' => '&#964;', 
  '&upsilon;' => '&#965;', 
  '&phi;' => '&#966;', 
  '&chi;' => '&#967;', 
  '&psi;' => '&#968;', 
  '&omega;' => '&#969;', 
  '&thetasym;' => '&#977;', 
  '&upsih;' => '&#978;', 
  '&piv;' => '&#982;', 
  '&bull;' => '&#8226;', 
  '&hellip;' => '&#8230;', 
  '&prime;' => '&#8242;', 
  '&Prime;' => '&#8243;', 
  '&oline;' => '&#8254;', 
  '&frasl;' => '&#8260;', 
  '&weierp;' => '&#8472;', 
  '&image;' => '&#8465;', 
  '&real;' => '&#8476;', 
  '&trade;' => '&#8482;', 
  '&alefsym;' => '&#8501;', 
  '&larr;' => '&#8592;', 
  '&uarr;' => '&#8593;', 
  '&rarr;' => '&#8594;', 
  '&darr;' => '&#8595;', 
  '&harr;' => '&#8596;', 
  '&crarr;' => '&#8629;', 
  '&lArr;' => '&#8656;', 
  '&uArr;' => '&#8657;', 
  '&rArr;' => '&#8658;', 
  '&dArr;' => '&#8659;', 
  '&hArr;' => '&#8660;', 
  '&forall;' => '&#8704;', 
  '&part;' => '&#8706;', 
  '&exist;' => '&#8707;', 
  '&empty;' => '&#8709;', 
  '&nabla;' => '&#8711;', 
  '&isin;' => '&#8712;', 
  '&notin;' => '&#8713;', 
  '&ni;' => '&#8715;', 
  '&prod;' => '&#8719;', 
  '&sum;' => '&#8721;', 
  '&minus;' => '&#8722;', 
  '&lowast;' => '&#8727;', 
  '&radic;' => '&#8730;', 
  '&prop;' => '&#8733;', 
  '&infin;' => '&#8734;', 
  '&ang;' => '&#8736;', 
  '&and;' => '&#8743;', 
  '&or;' => '&#8744;', 
  '&cap;' => '&#8745;', 
  '&cup;' => '&#8746;', 
  '&int;' => '&#8747;', 
  '&there4;' => '&#8756;', 
  '&sim;' => '&#8764;', 
  '&cong;' => '&#8773;', 
  '&asymp;' => '&#8776;', 
  '&ne;' => '&#8800;', 
  '&equiv;' => '&#8801;', 
  '&le;' => '&#8804;', 
  '&ge;' => '&#8805;', 
  '&sub;' => '&#8834;', 
  '&sup;' => '&#8835;', 
  '&nsub;' => '&#8836;', 
  '&sube;' => '&#8838;', 
  '&supe;' => '&#8839;', 
  '&oplus;' => '&#8853;', 
  '&otimes;' => '&#8855;', 
  '&perp;' => '&#8869;', 
  '&sdot;' => '&#8901;', 
  '&lceil;' => '&#8968;', 
  '&rceil;' => '&#8969;', 
  '&lfloor;' => '&#8970;', 
  '&rfloor;' => '&#8971;', 
  '&lang;' => '&#9001;', 
  '&rang;' => '&#9002;', 
  '&loz;' => '&#9674;', 
  '&spades;' => '&#9824;', 
  '&clubs;' => '&#9827;', 
  '&hearts;' => '&#9829;', 
  '&diams;' => '&#9830;'));

?>
