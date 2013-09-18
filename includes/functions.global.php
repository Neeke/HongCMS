<?php if(!defined('ROOT')) die('Access denied.');


/**
 * 处理伪静态URL
 *
 * @param string $url
 * @return string
 */
function URL($url = ''){
	global $_CFG;
	return SYSDIR . Iif($url, Iif(!$_CFG['siteRewrite'], 'index.php/') . $url);
}


//获取图片的URL
function GetImageURL($path, $filename, $size = 1){
	switch($size){
		case 1:
			$size = '_s.jpg';
			break;
		case 2:
			$size = '_m.jpg';
			break;
		case 3:
			$size = '_l.jpg';
			break;
	}

	return SYSDIR  . "uploads/$path/$filename$size";
}

// ##############################################################

function DisplayDate($timestamp = 0, $dateformat = '', $time = 0){
	global $_CFG;

	if(!$dateformat){
		$dateformat = $_CFG['siteDateFormat'] . Iif($time, ' H:i:s');
	}

	$timezoneoffset = ForceInt($_CFG['siteTimezone']);

	return @gmdate($dateformat, Iif($timestamp, $timestamp, time()) + (3600 * $timezoneoffset));
}

// ##############################################################

function DisplayFilesize($filesize){

	$kb = 1024;         // Kilobyte
	$mb = 1048576;      // Megabyte

	if($filesize < $kb){
		$size = $filesize . ' B';
	}else if($filesize < $mb){
		$size = round($filesize/$kb,2) . ' K';
	}else{
		$size = round($filesize/$mb,2) . ' M';
	}

	return (isset($size) AND $size != '0 B' AND  $size != ' B') ? $size : 0;
}

// ##############################################################

function Iif($expression, $returntrue, $returnfalse = ''){
	if($expression){
		return $returntrue;
	}else{
		return $returnfalse;
	}
}

// ##############################################################

function SafeSql($source){
	$entities_match = array(',',';','$','!','@','#','%','^','&','*','_','(',')','{','}','|',':','"','<','>','?','[',']','\\',"'",'.','/','*','+','~','`','=');
	return str_replace($entities_match, '', trim($source));
}

// ##############################################################

function SafeSearchSql($source){
	$entities_match = array('$','!','@','#','%','^','&','*','_','(',')','{','}','|',':','"','<','>','?','[',']','\\',"'",'.','/','*','~','`','=');
	return str_replace($entities_match, '', trim($source));
}


// ##############################################################

function IsEmail($email){
	return preg_match("/^[a-z0-9]+[.a-z0-9_-]*@[a-z0-9]+[.a-z0-9_-]*\.[a-z0-9]+$/i", $email);
}

// ##############################################################

function IsName($name){
	$entities_match = array(',',';','$','!','@','#','%','^','&','*','(',')','{','}','|',':','"','<','>','?','[',']','\\',"'",'/','*','+','~','`','=');
	for ($i = 0; $i<count($entities_match); $i++) {
	     if(strpos($name, $entities_match[$i])){
               return false;
		 }
	}
   return true;
}

// ##############################################################

function IsAlnum($str){
   return preg_match("/^[[:alnum:]]+$/i", $str);
}

// ##############################################################

function PassGen($length = 8){
	$str = 'abcdefghijkmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	for ($i = 0, $passwd = ''; $i < $length; $i++)
		$passwd .= substr($str, mt_rand(0, strlen($str) - 1), 1);
	return $passwd;
}

// ##############################################################

function IsGet($VariableName) {
	if (isset($_GET[$VariableName])) {
		return true;
	} else {
		return false;
	}

}

// ##############################################################

function IsPost($VariableName) {
	if (isset($_POST[$VariableName])) {
		return true;
	} else {
		return false;
	}

}

// ##############################################################

function ForceInt($InValue, $DefaultValue = 0) {
	$iReturn = intval($InValue);
	return ($iReturn == 0) ? $DefaultValue : $iReturn;
}

// ##############################################################

function ForceString($InValue, $DefaultValue = '') {
	if (is_string($InValue)) {
		$sReturn = EscapeSql(trim($InValue));
		if (empty($sReturn) && strlen($sReturn) == 0) $sReturn = $DefaultValue;
	} else {
		$sReturn = EscapeSql($DefaultValue);
	}
	return $sReturn;
}

// ##############################################################

function ForceStringFrom($VariableName, $DefaultValue = '') {
	if (isset($_GET[$VariableName])) {
		return ForceString($_GET[$VariableName], $DefaultValue);
	} elseif (isset($_POST[$VariableName])) {
		return ForceString($_POST[$VariableName], $DefaultValue);
	} else {
		return $DefaultValue;
	}
}

// ##############################################################

function ForceIntFrom($VariableName, $DefaultValue = 0) {
	if (isset($_GET[$VariableName])) {
		return ForceInt($_GET[$VariableName], $DefaultValue);
	} elseif (isset($_POST[$VariableName])) {
		return ForceInt($_POST[$VariableName], $DefaultValue);
	} else {
		return $DefaultValue;
	}

}

// ##############################################################

function ForceCookieFrom($VariableName, $DefaultValue = '') {
	if (isset($_COOKIE[$VariableName])) {
		return ForceString($_COOKIE[$VariableName], $DefaultValue);
	} else {
		return $DefaultValue;
	}
}

// ##############################################################

function EscapeSql($query_string) {

	if (get_magic_quotes_gpc()) {
		$query_string = stripslashes($query_string);
	}

	$query_string = htmlspecialchars(str_replace (array('\0', '　'), '', $query_string), ENT_QUOTES);
	
	if(function_exists('mysql_real_escape_string')) {
		$query_string = mysql_real_escape_string($query_string);
	}else if(function_exists('mysql_escape_string')){
		$query_string = mysql_escape_string($query_string);
	}else{
		$query_string = addslashes($query_string);
	}

	return $query_string;
}

// ##############################################################

function html($String) {
	 return str_replace(array('&amp;','&#039;','&quot;','&lt;','&gt;'), array('&','\'','"','<','>'), $String);
}

// ##############################################################

function ShortTitle($string, $length=81){
	if(strlen($string) == 0) 	return '';
	if(strlen($string) <= $length) return $string;

	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
	$strcut = '';

	$n = $tn = $noc = 0;
	while($n < strlen($string)) {
		$t = ord($string[$n]);
		if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
			$tn = 1; $n++; $noc++;
		} elseif(194 <= $t && $t <= 223) {
			$tn = 2; $n += 2; $noc += 2;
		} elseif(224 <= $t && $t < 239) {
			$tn = 3; $n += 3; $noc += 2;
		} elseif(240 <= $t && $t <= 247) {
			$tn = 4; $n += 4; $noc += 2;
		} elseif(248 <= $t && $t <= 251) {
			$tn = 5; $n += 5; $noc += 2;
		} elseif($t == 252 || $t == 253) {
			$tn = 6; $n += 6; $noc += 2;
		} else {
			$n++;
		}

		if($noc >= $length) break;
	}

	if($noc > $length) $n -= $tn;

	$strcut = substr($string, 0, $n);
	$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	return $strcut.'...';
}


// ###############################################################

function GetIP() {
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$thisip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$thisip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$thisip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$thisip = $_SERVER['REMOTE_ADDR'];
	}

	preg_match("/[\d\.]{7,15}/", $thisip, $thisips);
	$thisip = $thisips[0] ? $thisips[0] : 'unknown';
	return $thisip;
}

// ###############################################################

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 600) {

	$ckey_length = 4;
	$key = md5($key ? $key : 'default_key');
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

?>