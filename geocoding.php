<?php

/**
 * @file geocoding.php
 *
 * Functions to extract geographical coordinates from text, and to convert and format
 * coordinates.
 *
 */
 

require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/bhl_text.php');

//------------------------------------------------------------------------------
/**
 * @brief Convert a decimal latitude or longitude to deg° min' sec'' format in HTML
 *
 * @param decimal Latitude or longitude as a decimal number
 *
 * @return Degree format
 */
function decimal_to_degrees($decimal)
{
	$decimal = abs($decimal);
	$degrees = floor($decimal);
	$minutes = floor(60 * ($decimal - $degrees));
	$seconds = round(60 * (60 * ($decimal - $degrees) - $minutes));
	
	if ($seconds == 60)
	{
		$minutes++;
		$seconds = 0;
	}
	
	// &#176;
	$result = $degrees . '&deg;' . $minutes . '&rsquo;';
	if ($seconds != 0)
	{
		$result .= $seconds . '&rdquo;';
	}
	return $result;
}

//------------------------------------------------------------------------------
/**
 * @brief Convert decimal latitude, longitude pair to deg° min' sec'' format in HTML
 *
 * @param latitude Latitude as a decimal number
 * @param longitude Longitude as a decimal number
 *
 * @return Degree format
 */
function format_decimal_latlon($latitude, $longitude)
{
	$html = decimal_to_degrees($latitude);
	$html .= ($latitude < 0.0 ? 'S' : 'N');
	$html .= '&nbsp;';
	$html .= decimal_to_degrees($longitude);
	$html .= ($longitude < 0.0 ? 'W' : 'E');
	return $html;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Convert degrees, minutes, seconds to a decimal value
 *
 * @param degrees Degrees
 * @param minutes Minutes
 * @param seconds Seconds
 * @param hemisphere Hemisphere (optional)
 *
 * @result Decimal coordinates
 */
function degrees2decimal($degrees, $minutes=0, $seconds=0, $hemisphere='N')
{
	$result = $degrees;
	$result += $minutes/60.0;
	if (is_numeric($seconds))
	{
		$result += $seconds/3600.0;
	}
	
	if ($hemisphere == 'S')
	{
		$result *= -1.0;
	}
	if ($hemisphere == 'W')
	{
		$result *= -1.0;
	}
	// Spanish
	if ($hemisphere == 'O')
	{
		$result *= -1.0;
	}
	// Spainish OCR error
	if ($hemisphere == '0')
	{
		$result *= -1.0;
	}
	
	return $result;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief
 *
 * @param text Text which may contain latitude and longitudes
 *
 * @return Array of points (as class with fields latitude and longitude)
 */
function points_from_text($text)
{
	
	$text = str_replace("\n", " ", $text);
	$text = str_replace("\\n", " ", $text);
	
	$points = array();
	 
	//echo $text;
	
	$matched = false;
	
	
	
	// 52,0802°N/20,7081°E
	
	if (!$matched)
	{
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			,
			(?<latitude_minutes>([0-9]+))
			°
			(?<latitude_hemisphere>[N|S])
			\s*
			\/
			\s*
			(?<longitude_degrees>([0-9]{1,3}))
			,
			(?<longitude_minutes>([0-9]+))
			°
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$degrees = $matches['latitude_degrees'][$i] . '.' . $matches['latitude_minutes'][$i];
				$pt->latitude = $degrees;
				
				if ($matches['latitude_hemisphere'][$i] == 'S')
				{
					$pt->latitude *= -1;
				}
				
				$degrees = $matches['longitude_degrees'][$i] . '.' . $matches['longitude_minutes'][$i];
				$pt->longitude = $degrees;
				
				if ($matches['longitude_hemisphere'][$i] == 'W')
				{
					$pt->longitude *= -1;
				}
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	// http://direct.biostor.org/reference/164556.text
	// N 36°41’29” W 4°48’22”
	if (!$matched)
	{		
		if (preg_match_all('/(
			(?<latitude_hemisphere>[N|S])
			\s+
			(?<latitude_degrees>([0-9]{1,2}))
			°
			\s*
			(?<latitude_minutes>([0-9]+))
			’
			(?<latitude_seconds>\d+)
			”
			\s*
			(?<longitude_hemisphere>[W|E])
			\s+
			(?<longitude_degrees>([0-9]{1,3}))
			°
			\s*
			(?<longitude_minutes>([0-9]+))
			’
			(?<longitude_seconds>\d+)
			”
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	// 42°20’N/17°41’E // http://direct.biostor.org/reference/164438.text 

	// 37° 03’N, 36° 25’E
	// http://direct.biostor.org/reference/164467
	// 54°22’21”N, 18°36’22”E
	// http://biostor.org/reference/164439
	if (!$matched)
	{		
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			°
			\s*
			(?<latitude_minutes>([0-9]+))
			’
			(
			(?<latitude_seconds>\d+)
			”
			)?
			(?<latitude_hemisphere>[N|S])
			[,|\/]
			\s*		
			(?<longitude_degrees>([0-9]{1,3}))
			°
			\s*
			(?<longitude_minutes>([0-9]+))
			’
			(
			(?<longitude_seconds>\d+)
			”
			)?
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
		
	
	// http://biostor.org/reference/81355
	if (!$matched)
	{
		// 9.4555°S, 150.7857°E
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			(?<latitude_minutes>(\.[0-9]+))
			°
			(?<latitude_hemisphere>[N|S])
			,
			\s*
			(?<longitude_degrees>([0-9]{1,3}))
			(?<longitude_minutes>(\.[0-9]+))
			°
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$degrees = $matches['latitude_degrees'][$i] + $matches['latitude_minutes'][$i];
				$pt->latitude = $degrees;
				
				if ($matches['latitude_hemisphere'][$i] == 'S')
				{
					$pt->latitude *= -1;
				}
				
				$degrees = $matches['longitude_degrees'][$i] + $matches['longitude_minutes'][$i];
				$pt->longitude = $degrees;
				
				if ($matches['longitude_hemisphere'][$i] == 'W')
				{
					$pt->longitude *= -1;
				}
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	
	// http://biostor.org/reference/134968
	if (!$matched)
	{
		// 68° 25' N and 167° 12' W
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			°
			\s*
			(?<latitude_minutes>([0-9]+))
			\'
			\s*
			(?<latitude_hemisphere>[N|S])
			\s+and\s+
			(?<longitude_degrees>([0-9]{1,3}))
			°
			\s*
			(?<longitude_minutes>([0-9]+))
			\'
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	// http://biostor.org/reference/135789
	if (!$matched)
	{
		// 31? 51' S, 88? 21' W
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			\?
			\s*
			(?<latitude_minutes>([0-9]+))
			\'
			\s*
			(?<latitude_hemisphere>[N|S])
			,
			\s*
			(?<longitude_degrees>([0-9]{1,3}))
			\?
			\s*
			(?<longitude_minutes>([0-9]+))
			\'
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	
	
	if (!$matched)
	{
		// lat 28°00'N, long 114°08'W
		if (preg_match_all('/(
			lat(itude)?
			\s*
			(?<latitude_degrees>([0-9]{1,2}))
			°
			\s*
			(?<latitude_minutes>([0-9]+))
			\'
			\s*
			(?<latitude_hemisphere>[N|S])
			,
			\s*
			long(itude)?
			\s*
			(?<longitude_degrees>([0-9]{1,3}))
			°
			\s*
			(?<longitude_minutes>([0-9]+))
			\'
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	
	if (!$matched)
	{
		// lat 13.869°N, long 89.620°W
		if (preg_match_all('/(
			lat
			\s*
			(?<latitude_degrees>([0-9]{1,2}))
			(?<latitude_minutes>(\.[0-9]+))
			°
			(?<latitude_hemisphere>[N|S])
			,
			\s*
			long
			\s*
			(?<longitude_degrees>([0-9]{1,3}))
			(?<longitude_minutes>(\.[0-9]+))
			°
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$degrees = $matches['latitude_degrees'][$i] + $matches['latitude_minutes'][$i];
				$pt->latitude = $degrees;
				
				if ($matches['latitude_hemisphere'][$i] == 'S')
				{
					$pt->latitude *= -1;
				}
				
				$degrees = $matches['longitude_degrees'][$i] + $matches['longitude_minutes'][$i];
				$pt->longitude = $degrees;
				
				if ($matches['longitude_hemisphere'][$i] == 'W')
				{
					$pt->longitude *= -1;
				}
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	// [32.11S 18.54E]
	// http://biostor.org/reference/378
	if (!$matched)
	{			
		// http://biostor.org/reference/134827
		if (preg_match_all('/(
			[\(|\[]
			(?<latitude_degrees>([0-9]{1,2}))
			\.
			(?<latitude_minutes>([0-9]+))
			(?<latitude_hemisphere>[N|S])
			\s+
			(?<longitude_degrees>([0-9]{1,3}))
			\.
			(?<longitude_minutes>([0-9]+))
			(?<longitude_hemisphere>[W|E])
			[\)|\)]
		)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	
	// 34°24'N 08° 19' E
	// 37°17' N 09°52' E 
	if (!$matched)
	{			
		// http://biostor.org/reference/134827
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))[°]
			\s*
			(?<latitude_minutes>([0-9]+))\'
			\s*
			(?<latitude_hemisphere>[N|S])
			\s+
			(?<longitude_degrees>([0-9]{1,3}))[°]
			\s*
			(?<longitude_minutes>([0-9]+))\'
			\s*
			(?<longitude_hemisphere>[W|E])
			
		)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	// 36 21 N 10 07 E 
	if (!$matched)
	{			
		// http://biostor.org/reference/134827
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			\s+
			(?<latitude_minutes>([0-9]+))\'
			\s+
			(?<latitude_hemisphere>[N|S])
			\s+
			(?<longitude_degrees>([0-9]{1,3}))
			\s+
			(?<longitude_minutes>([0-9]+))\'
			\s+
			(?<longitude_hemisphere>[W|E])
			
		)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	
	
	// Spanish
	// http://biostor.org/reference/133524
	if (!$matched)
	{
		// 32° 15,90' N 27° 31,80' O
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			°
			\s+
			(?<latitude_minutes>([0-9]+(,[0-9]+)?))
			\'
			\s+
			(?<latitude_hemisphere>[N|S])
			\s+
			(?<longitude_degrees>([0-9]{1,3}))
			°
			\s+
			(?<longitude_minutes>([0-9]+(,[0-9]+)?))
			\'
			\s+
			(?<longitude_hemisphere>[O|0|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['latitude_degrees'][$i];
				$minutes = str_replace(',', '.', $matches['latitude_minutes'][$i]);
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['longitude_degrees'][$i];
				$minutes = str_replace(',', '.', $matches['longitude_minutes'][$i]);
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	if (!$matched)
	{		
		// http://www.biodiversitylibrary.org/page/40824927
		// 48 30' 33" E, 13 00' 01" S (long first then lat)
		if (preg_match_all('/(
			(?<longitude_degrees>([0-9]{1,3}))
			\s+
			(?<longitude_minutes>([0-9]+))
			\'
			\s+
			(?<longitude_seconds>\d+)
			"
			\s+
			(?<longitude_hemisphere>[W|E])
			,
			\s+
			(?<latitude_degrees>([0-9]{1,2}))
			\s+
			(?<latitude_minutes>([0-9]+))
			\'
			\s+
			(?<latitude_seconds>\d+)
			"
			\s+
			(?<latitude_hemisphere>[N|S])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	// degrees and minutes run together because degree symbol not in OCR
	// http://biostor.org/reference/133689
	if (!$matched)
	{		
		// 1055'32"N, 8528'02"W
		if (preg_match_all('/(
				(?<latitude_degrees>[0-9]{2})(?<latitude_minutes>[0-9]{2})
				\'
				((?<latitude_seconds>\d+)")?
				(?<latitude_hemisphere>[N|S])
				,\s*
				(?<longitude_degrees>[0-9]{2})(?<longitude_minutes>[0-9]{2})
				\'
				((?<longitude_seconds>\d+)")?
				(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	// 145°44'E, 28°19'S
	//117° 35' 22" E, 23° 47' 48" S
	// http://biostor.org/reference/144773.text
	if (!$matched)
	{		
		if (preg_match_all('/(
			((?<longitude_degrees>([0-9]{1,3}))([°])?)?
			\s*
			(?<longitude_minutes>([0-9]+)(\.[0-9]+)?)
			[\']?
			(\s*(?<longitude_seconds>\d+)")?
			\s*
			(?<longitude_hemisphere>[W|E])
			\s*
			[,|-]
			\s*			
			(?<latitude_degrees>([0-9]{1,2}))([°])
			\s*
			((?<latitude_minutes>([0-9]+)(\.[0-9]+)?))?
			[\']?
			(\s*(?<latitude_seconds>\d+)")?
			\s*
			(?<latitude_hemisphere>[N|S])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
		
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['latitude_degrees'][$i];
				if (isset($matches['latitude_minutes'][$i]))
				{
					$minutes = $matches['latitude_minutes'][$i];
				}
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['longitude_degrees'][$i];
				if (isset($matches['longitude_minutes'][$i]))
				{
					$minutes = $matches['longitude_minutes'][$i];
				}
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}
	}
	
	
	if (!$matched)
	{		
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))([°])
			\s*
			((?<latitude_minutes>([0-9]+)(\.[0-9]+)?))?
			[\']?
			\s*
			(?<latitude_hemisphere>[N|S])
			\s*
			[,|-]
			\s*
			((?<longitude_degrees>([0-9]{1,3}))([°])?)?
			\s*
			(?<longitude_minutes>([0-9]+)(\.[0-9]+)?)
			[\']?
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
		
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['latitude_degrees'][$i];
				if (isset($matches['latitude_minutes'][$i]))
				{
					$minutes = $matches['latitude_minutes'][$i];
				}
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['longitude_degrees'][$i];
				if (isset($matches['longitude_minutes'][$i]))
				{
					$minutes = $matches['longitude_minutes'][$i];
				}
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}
	}
	
	
	// http://www.biodiversitylibrary.org/page/3387645
	
	if (!$matched)
	{		
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))\.(?<latitude_minutes>([0-9]+))
			(?<latitude_hemisphere>[N|S])
			[,]\s*
			(?<longitude_degrees>([0-9]{1,3}))\.(?<longitude_minutes>([0-9]+))
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['latitude_degrees'][$i];
				if (isset($matches['latitude_minutes'][$i]))
				{
					$minutes = $matches['latitude_minutes'][$i];
				}
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['longitude_degrees'][$i];
				if (isset($matches['longitude_minutes'][$i]))
				{
					$minutes = $matches['longitude_minutes'][$i];
				}
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}		
	}
	
	
	if (!$matched)
	{		
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))([°])
			(?<latitude_hemisphere>[N|S])
			[,]
			\s*
			((?<longitude_degrees>([0-9]{1,3}))([°])?)?
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			//print_r($matches);
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = 0;
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	if (!$matched)
	{		
	
		// 54° 20' 80" N.; y9°09'30"E
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))[°|�]
			\s*
			(?<latitude_minutes>([0-9]+))\'
			\s*
			((?<latitude_seconds>([0-9]+))")?
			\s*
			(?<latitude_hemisphere>[N|S])\.?
			[,|;]\s*
			(?<longitude_degrees>([0-9]{1,3}))[°|�]
			\s*
			(?<longitude_minutes>([0-9]+))\'
			\s*
			((?<longitude_seconds>([0-9]+))")?
			\s*
			(?<longitude_hemisphere>[W|E])\.?
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	// 49° 44' 10.28"N, 114° 53' 5.45"W;
	if (!$matched)
	{		
		// 38 18' 30" N, 123 4' W
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			°
			\s+
			(?<latitude_minutes>([0-9]+))\'
			\s+
			((?<latitude_seconds>([0-9]+(\.\d+)?))")?
			\s*
			(?<latitude_hemisphere>[N|S])
			[,|;]\s*
			(?<longitude_degrees>([0-9]{1,3}))
			°
			\s+
			(?<longitude_minutes>([0-9]+))\'
			\s+
			((?<longitude_seconds>([0-9]+(\.\d+)?))")?
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	if (!$matched)
	{		
		// 38 18' 30" N, 123 4' W
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			\s+
			(?<latitude_minutes>([0-9]+))\'
			\s+
			((?<latitude_seconds>([0-9]+))")?
			\s*
			(?<latitude_hemisphere>[N|S])
			[,|;]\s*
			(?<longitude_degrees>([0-9]{1,3}))
			\s+
			(?<longitude_minutes>([0-9]+))\'
			\s+
			((?<longitude_seconds>([0-9]+))")?
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	if (!$matched)
	{		
		// 0224' N 4259' E
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{2}))
			(?<latitude_minutes>([0-9]{2}))\'
			\s*
			(?<latitude_hemisphere>[N|S])
			\s+
			(?<longitude_degrees>([0-9]{2}))
			(?<longitude_minutes>([0-9]{2}))\'
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				$seconds = '';
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = '';
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}
	}
	
	if (!$matched)
	{		
		
		//http://biostor.org/reference/14507
		// No hemisphere, but for this example it's N and E
		// 36°58', 127 °57'
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			[°]
			\s?
			(?<latitude_minutes>([0-9]+))\'
			[,]\s
			(?<longitude_degrees>([0-9]{1,3}))
			[°]
			\s?
			(?<longitude_minutes>([0-9]+))\'
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, 'N');
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, 'E');
				
				$points[] = $pt;
			}
			$matched = true;
		}		
	}
	
	// NOTE Oeste instead of E
	// 17° 46' N, 71° 34' Oeste
	if (!$matched)
	{		
		
		// http://biostor.org/reference/145640
		// // 17° 46' N, 71° 34' Oeste
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			[°]
			\s*
			(?<latitude_minutes>([0-9]+(\.\d+)?))
			\s*
			\'
			\s*
			(?<latitude_hemisphere>[N|S])
			,
			\s+
			(?<longitude_degrees>([0-9]{1,3}))
			[°]
			\s*
			(?<longitude_minutes>([0-9]+(\.\d+)?))
			\s*
			\'
			\s*
			(?<longitude_hemisphere>(O|Oeste|Occidente))
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
	
				$seconds = '';
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = '';
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				
				$longitude_hemisphere = $matches['longitude_hemisphere'][$i];
				if ($longitude_hemisphere == 'Oeste')
				{
					$longitude_hemisphere = 'W';
				}
				if ($longitude_hemisphere == 'O')
				{
					$longitude_hemisphere = 'W';
				}
				if ($longitude_hemisphere == 'Occidente')
				{
					$longitude_hemisphere = 'E';
				}
				
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $longitude_hemisphere);
				
				$points[] = $pt;
			}
			$matched = true;
		}
	}		
	
	if (!$matched)
	{		
		
		// http://biostor.org/reference/4047
		// 34°49'N 24°32'W
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			[°]
			(?<latitude_minutes>([0-9]+))\'
			(?<latitude_hemisphere>[N|S])
			\s+
			(?<longitude_degrees>([0-9]{1,3}))
			[°]
			(?<longitude_minutes>([0-9]+))\'
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
	
				$seconds = '';
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = '';
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}
	}	
	
	if (!$matched)
	{			
		// http://biostor.org/reference/55225
		// N10°23'31"; W 83°58'04"
		if (preg_match_all('/(
			(?<latitude_hemisphere>[N|S])
			\s*
			(?<latitude_degrees>([0-9]{1,2}))[°]
			(?<latitude_minutes>([0-9]+))\'
			(?<latitude_seconds>([0-9]+))"
			\s*
			;
			\s+
			(?<longitude_hemisphere>[W|E])
			\s*
			(?<longitude_degrees>([0-9]{1,2}))[°]
			(?<longitude_minutes>([0-9]+))\'
			(?<longitude_seconds>([0-9]+))"
		)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	// 35.2992° N, 71.5397° E
	if (!$matched)
	{		
		// 78.91°W, 0.54°S
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}(\.\d+)?))
			[°]
			\s*
			(?<latitude_hemisphere>[N|S])
			,?
			\s+
			(?<longitude_degrees>([0-9]{1,3}(\.\d+)?))
			[°]
			\s*
			(?<longitude_hemisphere>[W|E])			
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
				
				$pt->latitude = degrees2decimal($matches['latitude_degrees'][$i], 0, 0, $matches['latitude_hemisphere'][$i]);
				$pt->longitude = degrees2decimal($matches['longitude_degrees'][$i], 0, 0, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	
	if (!$matched)
	{		
		// 78.91°W, 0.54°S
		if (preg_match_all('/(
			(?<longitude_degrees>([0-9]{1,3}(\.\d+)?))
			[°]
			\s*
			(?<longitude_hemisphere>[W|E])
			,
			\s+
			(?<latitude_degrees>([0-9]{1,2}(\.\d+)?))
			[°]
			\s*
			(?<latitude_hemisphere>[N|S])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
				
				$pt->latitude = degrees2decimal($matches['latitude_degrees'][$i], 0, 0, $matches['latitude_hemisphere'][$i]);
				$pt->longitude = degrees2decimal($matches['longitude_degrees'][$i], 0, 0, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	// 3°11'N-66°00'W
	if (!$matched)
	{		
		
		// http://biostor.org/reference/105216
		// 3°11'N-66°00'W
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			[°]
			(?<latitude_minutes>([0-9]+))\'
			(?<latitude_hemisphere>[N|S])
			\-
			(?<longitude_degrees>([0-9]{1,3}))
			[°]
			(?<longitude_minutes>([0-9]+))\'
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
	
				$seconds = '';
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = '';
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}
	}	
	
	// 18°21'48"N 103°14'16"E
	// 27° 00' 46" S-54° 57' 06" W
	if (!$matched)
	{			
		// http://biostor.org/reference/115477
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))[°]
			\s*
			(?<latitude_minutes>([0-9]+))\'
			\s*
			(?<latitude_seconds>([0-9]+))"
			\s*
			(?<latitude_hemisphere>[N|S])
			(\s+|-)
			(?<longitude_degrees>([0-9]{1,3}))[°]
			\s*
			(?<longitude_minutes>([0-9]+))\'
			\s*
			(?<longitude_seconds>([0-9]+))"
			\s*
			(?<longitude_hemisphere>[W|E])
			
		)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	// http://biostor.org/reference/83153
	if (!$matched)
	{
		// lat. 8°30'S, long. 160°42'E
		if (preg_match_all('/(
			lat\.
			\s*
			(?<latitude_degrees>([0-9]{1,2}))
			°
			\s*
			(?<latitude_minutes>([0-9]+))
			\'
			\s*
			(?<latitude_hemisphere>[N|S])
			,
			\s*
			long\.
			\s*
			(?<longitude_degrees>([0-9]{1,3}))
			°
			\s*
			(?<longitude_minutes>([0-9]+))
			\'
			\s*
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	// http://biostor.org/reference/136804
	if (!$matched)
	{
	 /*
Tanzania 
07° 52' 
s 
31° 41' 
E 
Mugesse Hill 
*/
		if (preg_match_all('/(		    
			(?<latitude_degrees>([0-9]{1,2}))
			\s*
			°
			\s*
			(?<latitude_minutes>([0-9]+))
			\'
			\s*
			(?<latitude_hemisphere>[n|N|S|s])
			\s*
			(?<longitude_degrees>([0-9]{1,3}))
			\s*
			°
			\s*
			(?<longitude_minutes>([0-9]+))
			\'
			\s*
			(?<longitude_hemisphere>[w|W|E|e])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	// http://biostor.org/reference/136996
	if (!$matched)
	{
		//$text = 'N 08°51.047\ W 10°46.502';
		//echo $text;
		// N 08°51.047\ W 10°46.502
		if (preg_match_all('/(
			(?<latitude_hemisphere>[N|S])
			\s*
			(?<latitude_degrees>([0-9]{1,2}))
			°
			(?<latitude_minutes>([0-9]+(\.\d+)?))
			[\\\]
			\s+
			(?<longitude_hemisphere>[W|E])
			\s*
			(?<longitude_degrees>([0-9]{1,3}))
			°
			(?<longitude_minutes>([0-9]+(\.\d+)?))
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	// http://biostor.org/reference/143946
	if (!$matched)
	{
		//31°09.5"N - 28°43.5'W;
		//echo $text;
		// N 08°51.047\ W 10°46.502
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			°
			(?<latitude_minutes>([0-9]+(\.\d+)?))
			["|\']
			(?<latitude_hemisphere>[N|S])
			\s+-\s+
			(?<longitude_degrees>([0-9]{1,3}))
			°
			(?<longitude_minutes>([0-9]+(\.\d+)?))
			["|\']
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
	
	// 9°4.5'N 123°16.4'E
	// http://biostor.org/reference/81353
	if (!$matched)
	{
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))
			°
			(?<latitude_minutes>([0-9]+(\.\d+)?))
			["|\']
			(?<latitude_hemisphere>[N|S])
			\s+
			(?<longitude_degrees>([0-9]{1,3}))
			°
			(?<longitude_minutes>([0-9]+(\.\d+)?))
			["|\']
			(?<longitude_hemisphere>[W|E])
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	


	// 4°52-55'N and 9°57-59'E
	if (!$matched)
	{			
		// http://biostor.org/reference/137078
		if (preg_match_all('/(
			(?<latitude_degrees>([0-9]{1,2}))[°]
			(?<latitude_minutes>([0-9]+))
			\-
			(?<latitude_seconds>([0-9]+))
			\'
			(?<latitude_hemisphere>[N|S])
			\s*and\s*
			(?<longitude_degrees>([0-9]{1,3}))[°]
			(?<longitude_minutes>([0-9]+))
			\-
			(?<longitude_seconds>([0-9]+))
			\'
			(?<longitude_hemisphere>[W|E])
			
		)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
	
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;
			
				if (isset($matches['latitude_seconds'][$i]))
				{
					$seconds = $matches['latitude_seconds'][$i];
				}
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
		
				if (isset($matches['longitude_seconds'][$i]))
				{
					$seconds = $matches['longitude_seconds'][$i];
				}
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}	
		
	// S 00° 18.251', W 091° 39.047
	// S 00° 44.478', W 90° 18.132'
	// http://biostor.org/reference/115586
	if (!$matched)
	{
		
		if (preg_match_all('/(
			
(?<latitude_hemisphere>[N|S])
\s*
(?<latitude_degrees>([0-9]{1,2}))
°
\s*
(?<latitude_minutes>([0-9]+(\.\d+)?))
[\']
,?
\s+
(?<longitude_hemisphere>[W|E])
\s*
(?<longitude_degrees>([0-9]{1,3}))
°
\s*
(?<longitude_minutes>([0-9]+(\.\d+)?))
[\']?		
			
			
			)/xu',  $text, $matches, PREG_PATTERN_ORDER))
		{
			$num = count($matches[0]);
			for ($i = 0; $i < $num; $i++)
			{
				$pt = new stdclass;

				$seconds = 0;
				$minutes = $matches['latitude_minutes'][$i];
				$degrees = $matches['latitude_degrees'][$i];
				$pt->latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['latitude_hemisphere'][$i]);
		
				$seconds = 0;
				$minutes = $matches['longitude_minutes'][$i];
				$degrees = $matches['longitude_degrees'][$i];
				$pt->longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['longitude_hemisphere'][$i]);
				
				$points[] = $pt;
			}
			$matched = true;
		}	
	}
	
	
	
	
	
	
	
	
	return $points;
}


if (0)
{
	$text = "YAQUINA).-2 males, ML 74-79 mm, Cr. Y7102B haul 262, 
	 45°38.3'N, 126°43.8'W in 2,721 m, 17 Feb. 1971, USNM 
	 817580.-1 female, ML 66 mm, Cr. Y7105B haul 276, 
	 45°56.7'N, 127°38.6'W in 2,761 m, 17 May 1971, SBMNH 
	 35 142. - 1 female, ML 67 mm, Cr. Y7 1 023 haul 263, 45°36.4'N, 
	 ";
	 
	 $text = "Material Examined (15 specimens all collected by R/V 
	 YAQUINA).-Holotype: male, ML 50 mm, Cr. Y7210A haul 
	 308,45°01.rN, 135°12.0'W in 3,932 m,USNM 7307 15. Para- 
	 types: 2 males ML 46.5-48 mm, 1 female ML 31 mm, Cr. 
	 Y7210A haul 300, 44°58.rN,132\"'14.7'W in 3,585 m, 10 June 
	 1972, CAS 067789.-1 male, ML 42 mm, Cr. Y7105B haul 
	 281,44°38.5'N, 127°39.5'W in 2,816 m, 19May 1971,SBMNH 
	 35 144.- 1 male ML 30 mm, 1 female ML 85 mm, Cr. Y72 lOA 
	 haul 303, 45°05.rN, 133°10.9'W in 3,700 m, 10 July 1972, 
	 UMML 31.1938.-1 female, ML 57 mm, Cr. Y7005C haul 
	 232, 44°40.2'N, 1 33°35.7'W in 3,742 m, 3 June 1970, SBMNH 
	 35145.-2 males, ML 17.5-53 mm, Cr. Y7210A haul 299, 
	 44''56.8'N, 132°11.5'W in 3,580 m, 10 June 1972, UMML 
	 31.1937.-1 male, ML 17 mm, Cr. Y7210A haul 307, 
	 45'^3.5'N, 134°45.0'W in 3,900 m, 10 Oct. 1972, CAS 
	 067790.-2 males ML 30-36 mm, 1 female ML 29 mm, Cr. 
	 Y7210A haul 305, 45°05.2'N, 134°43.4'W in 3,900 m, 9 Oct. 
	 1972, USNM 817582.-1 male, ML 28 mm, Cr. Y7206Bhaul 
	 288, 44°06.2'N, 125°22.7'W in 2,940 m. 14 June 1972, CAS 
	 067791. ";
	 
	 $text = "Material Examined (5 specimens). — Holotype: male, ML 
	 93 mm, R/V YAQUINA Cr. 6606, 44°37.0'N, 125°01.0'W in 
	 1,260 m, 6 Aug. 1966, USNM 729991. Paratypes: 1 female, 
	 ML 99 mm, R/V ACONA, 44°24.2'N, 125°10.3'W in 1,000 
	 m, 14 Aug. 1964, CAS 06 1430.-1 female, ML 115 mm, R/V 
	 ACONA. 44°3I.3'N, 125°05.4'W in 1,250 m, 15 Jan. 1965. 
	 Other material (2 specimens in very poor condition, one partly 
	 eaten and both mauled in the net); 1 male, ML 62 mm, R/V 
	 ACONA, 44°36'N, 126°06.9'W in 3,000 m, 30 Dec. 1963, 
	 UMML 31.1 943. - 1 male, ML 56 mm, R/V ACONA, 44°36'N, 
	 126-06. 9' W in 3,000 m, 30 Dec. 1963. ";
	 
	 //$text = "2 UNIV. KANSAS MUS. NAT. HIST. OCC. PAP. No. 150 \nvent length is abbreviated SVL. The Museum of Natural History, The \n University of Kansas is abbreviated KU. \nI take pleasure in naming this distinctive new species for Professor Robert \n C. Bearse, Associate Vice Chancellor for Research, Graduate Studies, and \n Public Service, The University of Kansas; his enlightened and imaginative \n administrative actions have continuously enhanced the programs of the \n Museum of Natural History. \nEleutherodactylus bearsei new species \nHolotype. — KU 2 12268, a gravid female, from the CataratasAhuashiyacu \n (06°30'S, 76°20'W, 730 m), 14 km (by road) northeast of Tarapoto, Provincia \n San Martin, Departamento San Martin, Peru, obtained on 8 February 1 989 by \n William E. Duellman. \nParatypes. — KU 212269-71 and 212273, three adult males and one \n gravid female, collected with the holotype. \nReferred specimens. — KU 212272, a subadult female, and 212274 and \n 2 1 73 14-15, juveniles, from the type locality; KU 2 1 2275-76, juveniles, from \n 30 km (by road) southwest of Zapatero (ca. 10 km NE of San Jose de Sisa), \n 500 m, Provincia Lamas, Departamento San Martin, Peru. \nDiagnosis. — A member of the Eleutherodactylus unistrigatus group, as \n defined by Lynch (1976), characterized by: (1) skin of dorsum shagreened \n (scattered low tubercles in males), lacking folds; skin on venter areolate; (2) \n tympanum distinct, vertically ovoid, its diameter about one-third diameter of \n eye; (3) snout acutely rounded in dorsal view, bluntly rounded in profile; \n canthus rostralis sharp; (4) upper eyelid broader than interorbital distance, not \n bearing tubercles; cranial crests absent; (5) vomerine dentigerous processes \n prominent, transverse; (6) males with vocal slits and subgular vocal sac; \n nuptial excrescense absent; (7) first finger shorter than second; discs truncate, \n largest on Fingers II— IV; (8) fingers bearing lateral keels; (9) ulnar tubercles \n diffuse; (10) low tubercles on tarsus; tubercles absent on heel; (11) two \n metatarsal tubercles; inner elliptical, 8-10 times size of outer tubercle; (12) \n toes unwebbed, bearing narrow lateral keels and toepads nearly as large as \n those on outer fingers; ( 13) dorsum brown with darker brown marks on back \n and transverse bars on limbs; posterior surfaces of thighs and flanks uniform \n brown; dark brown labial bars; venter brown with cream flecks; (14) adults \n moderate-sized; three males 22.7-25.5 mm SVL, two females 38.0 and 38.8 \n mm SVL. \nEleutherodactylus bearsei most closely resembles E. platydactylus from \n the Amazonian slopes of the Andes in central and southern Peru, E. diadematus \n in the upper Amazon Basin, and a new species from Panguana in Amazonian \n Peru being described by Hedges and Schliiter. Eleutherodactylus platydactylus \n differs from E. bearsei by having larger, conical tubercles on the dorsum, \n";
	 
	 //$text = "HYLID FROGS FROM THE GUIANA HIGHLANDS 3 \nMuseum of Comparative Zoology at Harvard University (MCZ), Museum of \n Natural History at The University of Kansas (KU), National Museum of \n Natural History (USNM), Nationaal Natuurhistorisch Museum (formerly \n Rijksmuseum van Natuurlijke Historie) (RMNH). the University of Guyana \n Department of Biology (UGDB), and the University of Puerto Rico at \n Mayagiiez (UPR-M ). Measurements and structural features follow Duellman \n (1970), except that webbing formula is that of Savage and Heyer (1967). as \n modified by Myers and Duellman ( 1982). Snout-vent length is abbreviated \n SVL. \nHyla Lalrenti, 1768 \nNearly 300 species, most of which are placed in one of more than 40 \n phenetic groups, are recognized in the paraphyletic genus Hyla. Seven of \n these groups occur in the Guianan Region. These are: ( 1 ) Hyla boans group \n (Duellman. 1970). (2) Hyla geographica group (Duellman. 1973). (3) Hyla \n granosa group ( Hoogmoed. 1 979a) , (4) Hyla leucophyllata group ( Duellman, \n 1 970), (5 ) Hyla marmorata group ( Bokermann, 1 964 ), (6) Hyla microcephalia \n group (Duellman. 1970). and (7) Hyla parviceps group (Duellman and \n Crump. 1974). \nTwo of the three new species described herein cannot be relegated to any \n of these recognized species groups. The other new species is a member of the \n Hyla geographica group. For ease of comparison, comparable features are \n numbered sequentially in the diagnoses. \nHyla hadroeeps new species \nHolotype. — KU 69720, an adult male, from area north of Acarai Moun- \n tains, west of New River (ca. 02°N, 58°W). Rupununi District, Guyana, \n obtained in January 1962 by William A. Bently. \nDiagnosis. — The single male has a SVL of 53.9 mm and the following \n characteristics: ( 1 ) body robust; head blunt; ( 2 ) skin on dorsum bearing many \n large, round tubercles: skin of head not co-ossified with underlying dermal \n bones; (3) tympanum distinct; (4) fingers about two-thirds webbed; (5) toes \n nearly fully webbed; (6) fringes and calcars absent on limbs; (7) axillary \n membrane extending to midlength of upper arm; (8) dorsum brown with \n irregular darker brown markings; venter cream with brown flecks; (9) \n vomerine odontophores short, diagonal. \nA subgular vocal sac immediately distinguishes Hyla hadroeeps from \n species of Phrynohyas and Osteocephalus, some of which it resembles \n superficially. The thick tubercular skin, large size, and absence of black and \n orange or yellow flash colors distinguish it from members of the Hyla \n marmorata group. The absence of dermal fringes on the limbs distinguishes \n H. hadroeeps from H. tuberculosa. \n";
	 
	 $text="Station 4760: 53° 53^ N.; 144° 53^ W.; 2,200 fathoms; May 21 Gonatusfabricii. 



Station 4763: 53° 46' N.; 164° 29' W.; 56 fathoms; May 28 Gonatus fubridi. 



Station 4705: 53° 12' N.; 171° 37' W.; 1,217 Mhoms;(Gonatus fubridi. 



May 29. XCrystalloteuthisberingicma. 



Station 4768: 54° 20' 80\" N.; y9°09'30\"E.; 764 fathoms; lime 3. Galiteuthis armata. ";

$text = 'Homestead (25.08S, 116.54E) in West- as long as scape';
	 
	 $text = 'Horseshoe Cove, Bodega Head, Sonoma County (38 18\' 30" N, 123 4\' W). ';
	 
	 $text = '19� 09\' S., 36� 55\' E.';
	 
	 $text = 'St. 3998\'". 7°34\'S., 8°48\'W. 1. III. 1930. ';
	 
	 $text = "Azores Material. 109522, RA^ Atlantis II 49, RHB1916, 
35°30'N 2r46'W, 39-41 m, 2229-0003 h, 24-25.VI.I969, 8:42- 
48 mm SL; 109523, RA' Atlantis II 49, RHB1919, 35°56'N 
22°40'W, 650-750 m, 0708-1030 h, 25.VI.I969, 3:40-43 mm 
SL; 109524, RA^ Atlantis II 49, RHB1920, 36°23'N 23°35'W, 63- 
65 m, 2045-2218 h, 25.VI.1969, 3:42-44 mm SL; 109591, RA' 
Chain 105, RHB2551, 34°49'N 24°32'W, 700-740 m, 1620-1845 
h, 08. VII. 1972, 1:45 mm SL; 109592, R/V Chain 105, RHB2552, 
34°17'N 24°05'W, 60-70 m, 2158-2305 h, 08.VII.1972, 2:40-43 
mm SL; 109671, RA' Delaware 7/63-04, DL63-04:012, 36°57'N 
24°50'W, 180 m, 1730-1815 h, 12.V.1963, 1:42 mm SL. 
Loweina rara (Lutken 1892) ";

$text = '48 30\' 33" E, 13 00\' 01" S';

$text = 'lat 13.869°N, long 89.620°W';

$text= 'COSTA RICA: Heredia 
 Province, unnamed creek at Hwy. 4, ca. 
3 Km from jet. with Hwy. 32 (N10°15\'10", 
 W83°55\'ll"; elev. 200 m) 10.vi.2001, DEB 
 (DB 01-28), 22 larvae (TAMU); La Selva 
 Biological Station, SW Puerto Viejo, Sura 
 Creek at Rio Puerto Viejo (N10°25\'49"; 
 W84°00\'06", elev. 33 m), 09.vi.2001, 8L, 
 DEB (DB 01-26), 8L (5L TAMU, 3L 
 FAMU); Rio Isla Grande at Hwy. 4, ca. 
 5 Km. W. of Rio Frio (N10°23\'31"; 
 W 83°58\'04", elev. 65 m), 10.vi.2001, 
 DEB (DB 01-27), IL (PERC)';
 
 $text = ' approximately 78.91° W, 0.54° S, 2,115 m a.s.l., by L. A';
	 
	 
	 $text = "Boca de Rio Cunucunuma, 49 km W Esme- 
ralda, 3°11'N-66°00'W, 135 m (T. F. AMA- 
ZONAS). Evergreen forest on river plain. 
Holdridge classification: TROPICAL humid 
forest (bh-T).";

$text = "Odostomia flexuosa Monterosato, 1874. Journ. Conchyl, 22 (3): 267. [Localidad tipo: Palermo]. 
Chrysallida interspatiosa Linden y Eikenboom, 1992. Basteria, 56: 21-23, fig. 10. [Localidad tipo: 
Azores, San Miguel, 37° 39' N 25° 32' O, profundidad 480 m]. Otro material examinado: Banco Hyéres: 2 c, DW 203, 845 m. Banco Irving: 49 c, DW 237, 670 m. 

Localidad tipo: Banco Hyéres, estación DW 200, 31° 19,10' N 28° 36,00' 0, 1060 m. 

";

$text = "1986, Grayum, M. et al. 6243 (CR, NY); Estacion Cacao, Parque Nacional 
Guanacaste, 1100 m, 1055'32\"N, 8528'02\"W, 12 July 1996, Gonzalez, J. 
1101 (INB); Parque Nacional Guanacaste, estacion Maritza, sendero a la 
cimade Volcan Orosi, 600m, 1057.6'N,8529.6'W,2July 1989,7/JVfiio 142 
";

$text = 'T. R. Roberts, 
June-July 1993. CMK 13137, 6 ex., 59.4-68.1 mm SL; Vientiane province, Mekong River at 
mouth of Nam Mang and lower 100 m of Nam Mang, 18°21\'48"N 103°14\'16"E; M. Kottelat et 
al, 22 February 1997. UMMZ 235320, 2 ex., 135.8-170.6 mm SL; Champasak province, ';

$text = "37°17' N 09°52' E ";

$text='Heptapterus mbya, sp. n. Figs. 1-3, Tables 1, 2 

Holotype: CI-FML 4008, 136.0 mm SL, Argentina, Misiones, rio Paranâ basin, arroyo 
Moreno at Ruta Provincial 202 (26° 54\' 24" S-54° 54\' 50" W) headwaters of arroyo Garuhapé, 
October 21 , 2004, M. Azpelicueta, D. Aichino, D. Méndez (Fig. 1). 

Paratypes: Ail spécimens corne from Argentina, province of Misiones. AI 247, 4 ex. 
(1 C&S), 88.0-116.5 mm SL, arroyo Azul (200 m downstream from Puente Quemado, 27° 00\' 
46" S-54° 57\' 06" W), October 21 , 2004, M. Azpelicueta, D. Aichino, D. Méndez; AI 269, 2 ex., 

NEW CATFISH F \'ROM THE PARANÂ RIVER 
';

$text = 'Border oi-\' Ciiimhii (Simiui) and Gulf Provinci:: Crater Mr. Wildlife Management 
Area, alluvial forest near Pio River, 6° 47\' S, 115° 02\' E, 4-i(M6() m, 23 Mar 1997 
(fl, fr), W. Tcikeinbi 1 1 .866 (iioioivpi:: LAG; isotvfi.s: A, K, E). ';

$text = "31? 51' S, 88? 21' W";

$text = "Tanzania 
07° 52' 
s 
31° 41' 
E 
Mugesse Hill 
";

$text = 'Boke Prefecture/Kolaboui, swampy area in secondary 
forest island, N 10°45.075\ W 14°27.040\ 23 & 24 April 2005, 
coll. MA. Bangoura & A. Hillers (originally listed as H. spatzi 
in Hillers et al. 2006); Nigeria: ZMB 7729 (holotype), Yoruba 
(Lagos), coll. Krause; Sierra Leone: ZMB 74884-74885, Tin- 
gi Hills, big pond with a few trees around and swampy area in 
savanna, N 08°51.047\ W 10°46.502\ 427 m a.s.l., 5 June 2007, 
coll. J. Johnny & A. Hillers; Togo: ZMB 39028, station Sokode, 
coll. Schroder. Mont Bero Classified Forest, savanna,
N 08°08\'30.9", W 08°34\'09.6", 1 December 2003,';

$text = '31°09.5"N - 28°43.5\'W';

$text = '17 40\' S, 149 05\' W';

$text = '9°4.5\'N 123°16.4\'E';

$text='Paratypes: 22 â , 20 9 from the Galapagos Islands, Ecuador. - Fernandina: 2 9 (one 
dissected, MHNG 3010), Cabo Douglas, GPS: S 00° 18.269\', W 091° 39.098\', u[ltra] v[iolet] 
l[ight], 15.ii.2005 (B. Landry, P. Schmitz). - Isabela: 2 6 (one dissected, slide BL 1580), V[ol- 
can]. Darwin, campamento base, Bflack] L[ight] - W[hite] L[ight] Trap, l.iii.2000, L[azaro] 
R[oque] # 2000-04 (L. Roque); 1 9, Volcan Darwin, 400 m s[obre el] nfivel del] m[ar], UVL - 
WL Trap, 3.iii.2000, LR # 2000-07 (L. Roque); 1 9 , 2 km W Puerto Villamil, M[ercury] V[apor] 
L[amp], 5.iii.l989 (B. Landry); 1 9, NE slope Alcedo, GPS: elevfation]. 292 m, S 00° 23.829\', 
W 91° 01.957\', uvl, 30.iii.2004 (B. Landry, P. Schmitz); 1 ó% V Alcedo, 200 m, [parte] arida al- 
ta, luz fluorescente, 12.iv.2001, Coll[ection] # 2001-06 (L. Roque); U,V. Darwin, 630 m elev., 
MVL, 16.V.1992 (B. Landry). - San Cristobal: 2 o\ 4 km SE P[uer]to Baquarizo [sic], MVL, 
12.ii.1989 (B. Landry); 3 6 (one dissected, slide BL 1171), 4 9 (one dissected, slide BL 1579), 
4 km SE P[uer]to Baquarizo [sic], MVL, 20.ii.1989 (B. Landry); 2 o\ transition zone, SW El 
Progreso, GPS: elev. 75 m, S 00° 56.359\', W 89° 32.906\', uvl, 15.iii.2004 (B. Landry, P. Schmitz); 
1 9 , near Loberia, GPS: elev. 14 m, S 00° 55.149\', W 89° 36.897\', uvl, 16.iii.2004 (B. Landry, P. 
Schmitz). - Santa Cruz: 1 a, Tortuga Bay, littoral zone, MVL, 29.Ì.1989 (B. Landry); 3 9, 
E[stacion] C[ientifica] C[harles] D[arwin], MVL, 4.iii.l992 (B. Landry); 3 3 (one dissected, 
slide BL 1582), 3 9 (one dissected, slide BL 1581), E.C.C.D., MVL, 6.iii.l992 (B. Landry); 1 
ó\ Finca S[teve]. Devine, MVL, 17.iii.1989 (B. Landry); 1 9, C[harles] D[arwin] Research] ';

$text = 'S 00° 18.269\', W 091° 39.098\'';
$text = 'S 00° 18.251\', W 091° 39.047';
	 
	$text = 'Material examined: Holotype male: \'ECU[ADOR], Galapagos, San Cristobal I antiguo 
botadero, ca. 4 km SE I Pto Baquerizo, G [lobal] Positioning] S [y stem]: 169 m elev[ationJ. I S 
00°54.800\', W 089°34.574\' I 25.ii.2005, u[ltra]v[iolet]l[ight], leg. B. Landry\' [white, printed]; 
\'HOLOTYPE I Galagete I krameri Landry & Schmitz\' [red card stock, hand written]. Deposited 
in the MHNG. '; 


$text = "54°22’21”N, 18°36’22”E";

$text = "52,0802°N/20,7081°E ";

//$text = "37° 03’N, 36° 25’E";

//$text = "42°20’N/17°41’E"; // http://direct.biostor.org/reference/164438.text

$text = "N 36°41’29” W 4°48’22”"; // http://direct.biostor.org/reference/164556.text

$text = '49° 44\' 10.28"N, 114° 53\' 5.45"W;';

	print_r(points_from_text($text));
	
}

?>