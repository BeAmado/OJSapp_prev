<?php

//functions to map some special chars that as it seems can't be translated to utf8 yet

// the ascci codes returened by the function ord()

/**codes:

32 = empty space

195,131, 194,129     = Á (&Aacute;)  dá bode
195,131, 226,128,154 = Â (&Acirc;)     OK
195,131, 194,141     = Í (&Iacute;)  dá bode
195,131, 197,160     = Ê (&Ecirc;)     OK
195,131, 198,146     = Ã (&Atilde;)    OK
*******, 194,170     = ê (&ecirc;)     OK
*******, 194,169     = é (&eacute;)    OK
*******, 194,167     = ç (&ccedil;)    OK
*******, 194,181     = õ (&otilde;)   OK
*******, 194,173     = í (&iacute;)    OK
*******, 194,161     = á (&aacute;)    OK
*******, 194,163     = ã (&atilde;)    OK
*******, 194,129     = À (&Agrave;)*   dá super bode
*******, 226,128,176 = É (&Eacute;)   OK
*******, 194,186     = ú (&uacute;)   OK
*******, 226,128,156 = Ó (&Oacute;) OK
*******, 226,128,161 = Ç (&Ccedil;) OK
*******, 226,128,157 = Ô (&Ocirc;) OK
*******, 226,128,162 = Õ (&Otilde;) OK
*******, 197,161     = Ú (&Uacute;) OK
*******, 
*******, 
*******, 
*******, 
*******, 
*******, 
*******, 

195,162,226,130,172, 197,147 = (left curly quotes, opening) OK
*******************, 194,157 = (right curly quotes, closing) dá bode
*******, 
*******, 
*******, 
*******, 

*/

//problem chars
$code2char = [
	"195,131,194,129" => ["Aacute", "Agrave"],
	"195,131,194,141" => "Iacute",
	"195,162,226,130,172,194,157" => "rdquo"
];




$char2code = [
	"acute" => [
		"A" => [194,129], // problem on translation
		"E" => [226,128,176],
		"I" => [194,141], //problem on translation
		"O" => [226,128,156],
		"U" => [197,161],
		"a" => [194,161],
		"e" => [194,169],
		"i" => [194,173],
		"o" => [],
		"u" => [194,186]
	],

	"circ" => [
		"A" => [226,128,154],
		"E" => [197,160],
		"I" => [],
		"O" => [226,128,157],
		"U" => [],
		"a" => [],
		"e" => [194,170],
	],

	"tilde" => [
		"A" => [198,146],
		"O" => [226,128,162],
		"a" => [194,163],
		"o" => [194,181]
	],

	"grave" => [
		"A" => [194,129], //problem on translation, same code as &Aacute;
		"E" => [],
		"I" => [],
		"O" => [],
		"U" => []
	],
	
	"cedil" => [
		"c" => [194,167],
		"C" => [226,128,161]
	]
];

$myEncodeChars = ["&" => "0123amper0123", ";" => "0123smcol0123"];
$myDecodeChars = ["0123amper0123" => "&", "0123smcol0123" => ";"];


function splitString($str) {
	$arr = [];
	
	$size = strlen($str);
	
	//echo "\nLength o $str = $size\n";
	for ($i = 0; $i < $size; $i++) {
		$char = substr($str, $i, 1);
		array_push($arr, $char);
	}
	
	return $arr;
}

function glueString($str_arr) {
	$str = "";
	foreach ($str_arr as $char) {
		$str .= $char;
	}
	return $str;
}

function translate2utf8($str) {
	$encoding = mb_detect_encoding($str);
	
	$in_encoding = $encoding;
	$out_encoding = $encoding;
	
	//echo "\nactual encoding is $encoding\n";
	
	if ($encoding === "UTF-8") {
		$out_encoding = "Windows-1252";
	}
	else {
		//$out_encoding = "UTF-8";
	}
	return iconv($in_encoding, $out_encoding."//TRANSLIT", $str);
}

function search4Numbers($numbers, $arr) {
	$size = count($numbers);
	//$found = false;
	$pos = -1;
	for($i = 0; $i < count($arr); $i++) {
		$pos = $i;
		for($j = 0; $j < $size; $j++) {
			if ($arr[$i + $j] !== $numbers[$j]) {
				$pos = -1;
				break; //breaking out of the inner loop with the $j control
			}
		}
		if ($pos > -1) break; //breaking out of the inner loop with the $i control
	}
	return $pos;
}

function str2codes($str) {
	$arr = splitString($str);
	$codesArr = [];
	foreach ($arr as $char) {
		array_push($codesArr, ord($char));
	}
	return $codesArr;
}

function codes2char($codesArr) {
	$strArr = [];
	foreach ($codesArr as $code) {
		array_push($strArr, chr($code));
	}
	return $strArr;
}

function codes2str($codesArr) {
	return glueString(codes2char($codesArr));
}

function coerce2int($arr) {
	$returnArray = [];
	foreach ($arr as $item) {
		$num = (int) $item;
		array_push($returnArray, $num);
	}
	return $returnArray;
}


function transformCodes(&$codesArr) {
	
$problemCharCodes = [
	" Agrave " => [32,195,131,194,129,32], // same as Aacute with spaces surrounding (code 32 is an empty space)
	"Aacute" => [195,131,194,129],
	"Iacute" => [195,131,194,141],
	"rdquo" => [195,162,226,130,172,194,157]
];
	
$myChars = ["&" => "0123amper0123", ";" => "0123smcol0123"];
	$keys = array_keys($problemCharCodes);
	$i = 0;

	while ($i < count($keys)) {
		$search = $keys[$i];
		$pos = search4numbers($problemCharCodes[$search], $codesArr);
		if ($pos > -1) {
			//substitutes the codes
			array_splice($codesArr, $pos, count($problemCharCodes[$search]), str2codes($myChars["&"] . $search . $myChars[";"]));
		}
		else {
			$i++;
		}
	}

	return 0;
}


function myEncodeSpecialChars($str) {
	$codes = str2codes($str); //step 1
	transformCodes($codes); //step 2
	return codes2str($codes); //step 3
}

function myDecodeSpecialChars($str) {
	$str = htmlentities($str); //step 5
	
$myChars = ["&" => "0123amper0123", ";" => "0123smcol0123"];
	$str = str_replace($myChars["&"], "&", $str); //step 6
	$str = str_replace($myChars[";"], ";", $str); //step 6

	return html_entity_decode($str); //step 7
}

function unmessEncoding($str) {
	$str = myEncodeSpecialChars($str); // steps 1 through 3
	$str = translate2utf8($str); // step 4
	$str = myDecodeSpecialChars($str); // steps 5 through 7
	return $str;
}
