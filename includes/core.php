<?php

function remove_head_eng($str){
		
		if($str == ''){$str = '';return $str;}
		$lower = strtolower($str);
		$split = str_split($lower);
		$position = 0;
		foreach($split as $value){
			
			$position = $position + 1;
			if(ord($value) > 126 || ord($value) < 32){
				break;
			}
		}
		$count_char = count($split);
		$length = $count_char - $position;
		$str = mb_substr($str, $position-1, $length,'utf8');
		return $str;
	}



function ellipsis($string, $limitation=''){

  $defaultstringlimitation = 100; // Define the default limitation of a string //
  
  if ($string == ''){return false;}
  if (empty($limitation)){ $limitation = $defaultstringlimitation;}

  $splitstr = str_split($string);
  $tempstr = $string;
  if(count($splitstr) > $limitation){
    $tempstr = mb_substr($string, 0, $limitation,'utf8');
    $string = $tempstr . ' ...';
  }
  return $string;
}

function showr($arry){
	if ($arry == ""){$arry = array();}
	print "<pre>";
	print_r ($arry);
	print "</pre>";
	return true;
}



function language_handler($language='',$overwrite=''){
	if ($overwrite == ''){$overwrite = false;}else {$overwrite = true;}
	if ($overwrite === true){
		if ($language == 'en'){$language = '_en';}
		if (isset($_SESSION['language']) === false){
			if ($language == ""){$_SESSION['language'] = "";}
			else if ($language == 'en'){$_SESSION['language'] = '_en';}
		}
		else {$_SESSION['language'] = $language;}
	}
	else {
		if (isset($_SESSION['language']) === false){
			if ($language == ""){$_SESSION['language'] = "";}
			else if ($language == 'en'){$_SESSION['language'] = '_en';}
		}
	}
		
	
	return true;
}

function remove_ks($name){
	if (strpos($name,'KIRKLAND SIGNATURE柯克兰') !== false){
			return str_replace('KIRKLAND SIGNATURE柯克兰','',$name);
		}
		if (strpos($name,'KIRKLAND柯克兰') !== false){
			return str_replace('KIRKLAND柯克兰','',$name);
		}
		if (strpos($name,'KIRKLAND柯蓝') !== false){
			return str_replace('KIRKLAND柯蓝','',$name);
		}
		if (strpos($name,'柯蓝') !== false){
			return str_replace('柯蓝','',$name);
		}
		if (strpos($name,'柯克兰') !== false){
			return str_replace('柯克兰','',$name);
		}
		if (strpos($name,'KIRKLAND SUNSWEET') !== false){
			return str_replace('KIRKLAND SUNSWEET','',$name);
		}
		return $name;
}
?>