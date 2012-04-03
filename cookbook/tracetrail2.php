<?php if (!defined('PmWiki')) exit();
/*
 * TraceTrail - Sets a trail with the recent visited wiki pages
 * Copyright 2006 by Americo Albuquerque (aalbuquerque@lanowar.sytes.net)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * 2007-01-07 : Minor modification adding an option to skip the current page  
 * and leave it out of the trail.  Bill Reveile, Austin TX
 * Put $TraceSkipCurr=1; in config.php to enable it. 
 */

define('TraceTrail_VERSION', '20070107');

SDV($TracePageFmt, '{$Group}/{$Name}');
SDV($TraceLinkFmt, '[[{$LinkPage} | {$PageName}]]');
SDV($TraceSepFmt, ' > ');
SDV($TraceCount, 5);
SDV($TraceTrailFmt, '<span class="wikitracetrail">Breadcrumbs{$TraceTrail}</span>');
SDV($TraceSkipCurr, 0);
Markup('tracetrails', '<links', '/\(:tracetrails:\)/se', "TraceTrail(\$pagename)");

function TraceTrail($pagename) {
  global $TracePageFmt, $TraceSepFmt, $TraceTrailFmt, $TraceCount, $TraceSkipCurr;
  @session_start();
  $pagename = ResolvePageName($pagename);
  $group = PageVar($pagename, '$Group');
  $page = PageVar($pagename, '$Name');
  $trace = isset($_SESSION['trace'])? explode('|', $_SESSION['trace']) : array();
  $trace = array_reverse(array_unique(array_reverse(array_merge($trace,
                         array(preg_replace('/\\{(!?[-\\w.\\/]*)(\\$\\w+)\\}/e', 
                         "htmlspecialchars(PageVar('$pagename', '$2', '$1'), ENT_NOQUOTES)", $TracePageFmt))))));
  $tracecount = count($trace);
  if($tracecount>($TraceCount + $TraceSkipCurr)) $trace = array_slice($trace, $tracecount-($TraceCount+$TraceSkipCurr), $TraceCount+$TraceSkipCurr);
  $_SESSION['trace'] = implode('|', $trace);
  $trace = array_slice($trace, 0, count($trace) - $TraceSkipCurr);
  array_walk($trace, "SetTrace");
  return preg_replace('/{\$TraceTrail}/', implode($TraceSepFmt, $trace), $TraceTrailFmt);
}

function SetTrace(&$value, $key) {
  global $TraceLinkFmt, $ScriptUrl;
  $pagename = ResolvePageName($value);
  $value = preg_replace('/{\$LinkPage}/', $pagename,
   preg_replace('/{\$PageName}/', $value, $TraceLinkFmt));
}
