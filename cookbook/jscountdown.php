<?php

$HTMLHeaderFmt['jscountdwon'] = "<script language='JavaScript'>
      var year = $JSYear;
      var month = $JSMonth;
      var day = $JSDay;
      var hour = $JSHour;
      var minute = $JSMinute;
      var second = 00;
      var targetDate = new Date (year, month-1, day, hour, minute, second);
      function JSCountdown() {
		todayDate = new Date();
		if(todayDate<=targetDate) { 
			var yearsleft = 0;
			var monthsleft = 0;
			var daysleft = 0;
			var hoursleft = 0;
			var minutesleft = 0;
			var secondsleft = 0;
			var counter = 0;
		  	daysleft = Math.floor((targetDate - todayDate)/1000/60/60/24);
		    counter = targetDate - daysleft * 24 * 60 * 60 * 1000;
		 	hoursleft = Math.floor((counter - todayDate)/1000/60/60);
		 	counter = counter - hoursleft * 60 * 60 * 1000;
		  	minutesleft = Math.floor((counter - todayDate)/1000/60);
		  	counter = counter - minutesleft * 60 * 1000;
		  	secondsleft = Math.floor((counter - todayDate)/1000);	  	
			(daysleft == 0 ) ? daysleft = '' : 
          	(daysleft !=1 ) ? daysleft += ' Tage, ' : daysleft += ' Tag, ';
			(hoursleft != 1) ? hoursleft += ' Stunden, ' : hoursleft += ' Stunde, ';
          	(minutesleft != 1) ? minutesleft += ' Minuten und ' : minutesleft += ' Minute und ';
          	if( secondsleft < 10) secondsleft= '0' + secondsleft;
          	(secondsleft!=1) ? secondsleft += ' Sekunden' : secondsleft += ' Sekunde';
			countdowndiv = document.getElementById('countdowndiv');
         	countdowndiv.innerHTML= daysleft + hoursleft + minutesleft + secondsleft;
          	setTimeout('JSCountdown()',200);
        }
        else {
        		countdowndiv = document.getElementById('countdowndiv');
         	countdowndiv.innerHTML= '0 Tage,  0 Stunden,  0 Minuten  und  00 Sekunden';
        }
      }
    	setTimeout('JSCountdown()',500);
   </script>";

Markup('countdown', 'directives', "/\(:jscountdown:\)/e", "JSCountdown('$0')");

function JSCountdown() {
	global $JSYear, $JSMonth, $JSDay, $JSHour, $JSMinute; 
	$targetDate = mktime ($JSHour, $JSMinute, 0, $JSMonth, $JSDay, $JSYear);
	$timediff = $targetDate - time();
	if ($timediff > 0) {
		$daysleft = date("z", $timediff);
		$hoursleft = date("G", $timediff)-1;
		$minutesleft = date("i", $timediff);
		$secondsleft = date("s", $timediff);
		
		if ($daysleft == 0)
			$daysleft == "";
		elseif ($daysleft != 1)
			$daysleft .= ' Tage, ';
		else
			$daysleft .= ' Tag, ';
		
		($hoursleft != 1) ? $hoursleft .= ' Stunden, ' : 	$hoursleft .= ' Stunde, ';
		($minutesleft != 1) ? $minutesleft .= ' Minuten und ' : $minutesleft .= ' Minute und ';
		($secondsleft!=1) ? $secondsleft .= ' Sekunden' : $secondsleft .= ' Sekunde';
		
		$out =  $daysleft.$hoursleft.$minutesleft.$secondsleft;
	}
	else
		$out	 = '0 Tage,  0 Stunden,  0 Minuten  und  00 Sekunden';
	return Keep("<span id='countdowndiv'>$out</span>");
}

?>
