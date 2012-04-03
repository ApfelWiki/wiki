<?php
/*
  PMWiki module datetimestamp.php
  
  VERSION:: 2004-10-30

  tested with pmwiki.2.20

  activate this by the following statement in local/config.php:

    include_once("local/datetimestamp.php");
    
  replaces "[[datetime]]" by the current datetimestamp in the specified format
  replaces "[[date]]" by the current datestamp in the specified format
  replaces "[[time]]" by the current timestamp in the specified format

  the outputformat can be specified in the variables
  $timeFmt, $dateFmt and $datetimeFmt in config.php before including the module
  formatstring according to arguments of the php-function date() (see php-manual)
  (samples see below)

  docs see Cookbook.DateTimeStamp
  knut alboldt, (alboldt at gmx.net)
  
  Log:
  
  2004-10-30 new version for pmwiki2
  
  
*/

# SDV($timeFmt,"h:i:s T");
SDV($timeFmt,"h:i:s");

# SDV($DateFmt,"D, d. F Y");
SDV($dateFmt,"d.m.Y");
SDV($datetimeFmt,$dateFmt . ", " . $timeFmt);

Markup("datetime","directives","/\\(:datetime:\\)/e","date('$datetimeFmt');");
Markup("date","directives","/\\(:date:\\)/e","date('$dateFmt');");
Markup("time","directives","/\\(:time:\\)/e","date('$timeFmt');");


?>
