<?php 
#if (!defined('PmWiki')) exit();

/**
 * Generates a plain string from a ParseArgs() result.
 * $args == GenerateArgs(ParseArgs($args))
 * Not well testet for all kinds of possible args
 * @version 0.2
 * @param array $args
 * return string
 */


class ParseArguments {
	var $args_array;
	var $args_string;
	
	function setString($newstring) {
		$this->args_string = $newstring;
		$this->args_array = ParseArgs($newstring);
	}
	
	function setArray($newarray){
		$this->args_array = $newarray;
		$this->args_string = $this->GenerateArgs($newarray);
	}
	
	function getString(){
		return $this->args_string;
	}
	
	function getArray(){
		return $this->args_array;
	}

	function mergeWithArray($car){
		$ta = $this->args_array;
		foreach ($car as $k => $arg)
			if (is_array($arg))
				array_merge_recursive (array($ta[$k]),$car[$k]);
			else
			 	$ta[$k] = $arg;
		$this->setArray($ta);
	}
	
	function mergeWithString($sar){
		$this->mergeWithArray(ParseArgs($sar));
	}
		
	function GenerateArgs($args) {
		if (!isset ($args))
			return "";
		foreach ($args as $k => $v) {
			// ignorin the ordering information
			if ($k === '#')	continue;
			// "-value" and "+value"  and single "values"
			elseif ($k === '+' || $k === '-' || $k === '' ) foreach ($v as $w)
				$out .= " $k$w";
			else
				$out .= " $k='$v'";
		}
		return $out;
	}
}
?>