<?php

/**
 * @file utilities.php
 *
 * Utility functions
 *
 */
require_once(dirname(__FILE__) . '/transtab_unicode_latex.inc.php');
 
//--------------------------------------------------------------------------------------------------
// From http://snipplr.com/view/6314/roman-numerals/
// Expand subtractive notation in Roman numerals.
function roman_expand($roman)
{
	$roman = str_replace("CM", "DCCCC", $roman);
	$roman = str_replace("CD", "CCCC", $roman);
	$roman = str_replace("XC", "LXXXX", $roman);
	$roman = str_replace("XL", "XXXX", $roman);
	$roman = str_replace("IX", "VIIII", $roman);
	$roman = str_replace("IV", "IIII", $roman);
	return $roman;
}
    
//--------------------------------------------------------------------------------------------------
// From http://snipplr.com/view/6314/roman-numerals/
// Convert Roman number into Arabic
function arabic($roman)
{
	$result = 0;
	
	$roman = strtoupper($roman);

	// Remove subtractive notation.
	$roman = roman_expand($roman);

	// Calculate for each numeral.
	$result += substr_count($roman, 'M') * 1000;
	$result += substr_count($roman, 'D') * 500;
	$result += substr_count($roman, 'C') * 100;
	$result += substr_count($roman, 'L') * 50;
	$result += substr_count($roman, 'X') * 10;
	$result += substr_count($roman, 'V') * 5;
	$result += substr_count($roman, 'I');
	return $result;
} 

//--------------------------------------------------------------------------------------------------
function latex_safe($str)
{
	global $transtab_unicode_latex;
		
	$n = mb_strlen($str);
	$safe_str = '';
	for($i = 0; $i < $n; $i++)
	{
		$char = mb_substr($str, $i, 1);
		if (array_key_exists($char, $transtab_unicode_latex)) 
		{
			$safe_str .= $transtab_unicode_latex[$char];
		}
		else
		{
			$safe_str .= $char;
		}
	}
	return $safe_str;
}
 

 
//--------------------------------------------------------------------------------------------------
/**
 * @brief Get client IP address
 *
 * From http://lutrov.com/blog/getting-the-client-ip-address/
 *
 * @return IP address
 */ 
function getip() 
{
   if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
      $result = $_SERVER['HTTP_X_FORWARDED_FOR'];
   } elseif (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
      $result = $_SERVER['HTTP_CLIENT_IP'];
   } else {
      $result = $_SERVER['REMOTE_ADDR'];
   }
   if (strstr($result, ',')) {
      $result = strtok($result, ',');
   }
   return $result;
}
 
//--------------------------------------------------------------------------------------------------
/**
 *
 * @brief PHP port of Ruby on Rails famous distance_of_time_in_words method.
 *
 * See http://api.rubyonrails.com/classes/ActionView/Helpers/DateHelper.html for more details.
 * Code from http://uk.php.net/manual/en/function.time.php#85128 
 *
 * Reports the approximate distance in time between two timestamps. Set include_seconds 
 * to true if you want more detailed approximations.
 *
 *
 * @param from_time
 * @param to time
 * @param include_seconds 
 *
 * @return Time interval in words
 *
 */
function distanceOfTimeInWords($from_time, $to_time = 0, $include_seconds = false) {
	$distance_in_minutes = round(abs($to_time - $from_time) / 60);
	$distance_in_seconds = round(abs($to_time - $from_time));

	if ($distance_in_minutes >= 0 and $distance_in_minutes <= 1) {
		if (!$include_seconds) {
			return ($distance_in_minutes == 0) ? 'less than a minute' : '1 minute';
		} else {
			if ($distance_in_seconds >= 0 and $distance_in_seconds <= 4) {
				return 'less than 5 seconds';
			} elseif ($distance_in_seconds >= 5 and $distance_in_seconds <= 9) {
				return 'less than 10 seconds';
			} elseif ($distance_in_seconds >= 10 and $distance_in_seconds <= 19) {
				return 'less than 20 seconds';
			} elseif ($distance_in_seconds >= 20 and $distance_in_seconds <= 39) {
				return 'half a minute';
			} elseif ($distance_in_seconds >= 40 and $distance_in_seconds <= 59) {
				return 'less than a minute';
			} else {
				return '1 minute';
			}
		}
	} elseif ($distance_in_minutes >= 2 and $distance_in_minutes <= 44) {
		return $distance_in_minutes . ' minutes';
	} elseif ($distance_in_minutes >= 45 and $distance_in_minutes <= 89) {
		return 'about 1 hour';
	} elseif ($distance_in_minutes >= 90 and $distance_in_minutes <= 1439) {
		return 'about ' . round(floatval($distance_in_minutes) / 60.0) . ' hours';
	} elseif ($distance_in_minutes >= 1440 and $distance_in_minutes <= 2879) {
		return '1 day';
	} elseif ($distance_in_minutes >= 2880 and $distance_in_minutes <= 43199) {
		return 'about ' . round(floatval($distance_in_minutes) / 1440) . ' days';
	} elseif ($distance_in_minutes >= 43200 and $distance_in_minutes <= 86399) {
		return 'about 1 month';
	} elseif ($distance_in_minutes >= 86400 and $distance_in_minutes <= 525599) {
		return round(floatval($distance_in_minutes) / 43200) . ' months';
	} elseif ($distance_in_minutes >= 525600 and $distance_in_minutes <= 1051199) {
		return 'about 1 year';
	} else {
		return 'over ' . round(floatval($distance_in_minutes) / 525600) . ' years';
	}
}
 
 
//--------------------------------------------------------------------------------------------------
/**
 * @brief Generate UUID
 *
 * From http://www.ajaxray.com/blog/2008/02/06/php-uuid-generator-function/
 *
  * @author     Anis uddin Ahmad <admin@ajaxray.com>
  * @param      string  an optional prefix
  * @return     string  the formatted uuid
  */
function uuid($prefix = '')
{
	$chars = md5(uniqid(mt_rand(), true));
	$uuid  = substr($chars,0,8) . '-';
	$uuid .= substr($chars,8,4) . '-';
	$uuid .= substr($chars,12,4) . '-';
	$uuid .= substr($chars,16,4) . '-';
	$uuid .= substr($chars,20,12);
	
	return $prefix . $uuid;
} 


//--------------------------------------------------------------------------------------------------
/**
 * @brief Split a string into words and retain a subset of those words
 *
 * From http://phpsense.com/php/php-word-splitter.html
 *
 * @param str String to split
 * @param words Number of words
 *
 * @return String of subset of words
 *
 */
function word_split($str,$words=10) 
{
	$arr = preg_split("/[\s]+/", $str,$words+1);
	$arr = array_slice($arr,0,$words);
	return join(' ',$arr);
}	

//--------------------------------------------------------------------------------------------------
/**
 * @brief Trim text to a susbtring of a given number of words, and append '…' if string is longer
 * than substring.
 *
 * @param str String to split
 * @param words Number of words
 *
 * @return String of subset of words
 *
 */
function trim_text($str, $words=10)
{
	$s = word_split($str, $words);
	if (mb_strlen($s) < mb_strlen($str))
	{
		$s .= '…';
	}
	return $s;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Trim string to a given length and append '…' if string is longer
 * than substring.
 *
 * @param str String to trim
 * @param length maximum length of string
 *
 * @return String of subset of words
 *
 */
function trim_string($str, $length=40)
{
	$s = $str;
	if (mb_strlen($str) > $length)
	{
		$s = mb_substr($str, 0, $length);
		$s .= '…';
	}
	return $s;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Extract the year from a date
 *
 * @param date A string representation of a date in YYYY-MM-DD format
 * @return Year in YYYY format
 */
function year_from_date($date)
{
	$year = 'YYYY';
	$matches = array();
	if (preg_match("/^([0-9]{4})(\-[0-9]{1,2})?(\-[0-9]{1,2})?$/", $date, $matches))
	{
		$year = $matches[1];
	}
	return $year;
}


?>