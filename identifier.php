<?php

/**
 * @file identifier.php
 *
 * Support for identifiers, such as SICI
 *
 */
 
class Identifier
{

}
 
//--------------------------------------------------------------------------------------------------
/**
 * @brief Encapsulate Serial Item and Contribution Identifier (SICI)
 *
 * The SICI identifies an article in a publication.
 *
 * For introduction to SICI see http://en.wikipedia.org/wiki/SICI. There is an 
 * Internet-Draft outlining the use of SICIs as URNs http://tools.ietf.org/html/draft-hakala-sici-01
 *
 */

class Sici extends Identifier
{
	public $sici_string = '';
	public $A1 = array();

	//----------------------------------------------------------------------------------------------
	function __construct($str = '')
	{
		$this->sici_string = $str;
		$this->A1 = array (
			'0' => 0,
			'1' => 1,
			'2' => 2,
			'3' => 3,
			'4' => 4,
			'5' => 5,
			'6' => 6,
			'7' => 7,
			'8' => 8,
			'9' => 9,	
			'A' => 10,
			'B' => 11,
			'C' => 12,
			'D' => 13,
			'E' => 14,
			'F' => 15,
			'G' => 16,
			'H' => 17,
			'I' => 18,
			'J' => 19,
			'K' => 20,
			'L' => 21,
			'M' => 22,
			'N' => 23,
			'O' => 24,
			'P' => 25,
			'Q' => 26,
			'R' => 27,
			'S' => 28,
			'T' => 29,
			'U' => 30,
			'V' => 31,
			'W' => 32,
			'X' => 33,
			'Y' => 34,
			'Z' => 35,
			'#' => 36
			);
	}

	//----------------------------------------------------------------------------------------------
	//
	/**
	 * @brief Compute checksum digit for SICI
	 *
	 */
	function checksum()
	{
		$num = array();
		
		$len = strlen($this->sici_string);
		for($i = 0; $i < $len; $i++)
		{
			if (isset($this->A1[$this->sici_string[$i]])) $num[$i] = $this->A1[$this->sici_string[$i]];
			else $num[$i] = 36;
		}
		
		$oddSum = 0;
		$evenSum = 0;
		$i = $len-1;
		while ($i >= 0)
		{
			$oddSum += $num[$i];
			$i--;
			if($i >= 0) $evenSum += $num[$i];
			$i--;
		}
		
		$remainder = ((3 * $oddSum) + $evenSum) % 37;
		$check = array_search (36-$remainder, $this->A1);
		$this->sici_string .= $check;
	}
	
	//----------------------------------------------------------------------------------------------
	/**
	 * @brief Create a SICI from a reference object
	 *
	 * Calls can_create method to test whether object has enough information to create a SICI
	 * (minimally we need a ISSN, volume, starting page, and year). If successful returns SICI
	 *
	 * @param reference Reference object 
	 *
	 * @return SICI if successful, otherwise ''
	 *
	 */
	function create($reference)
	{
		$this->sici_string = '';

		if (!$this->can_create($reference))
		{
			// not enough for even a minimal SICI
			return $this->sici_string;
		}
		
		$this->sici_string = $reference->issn;
		$this->sici_string .= '(' . $reference->year . ')';
		$this->sici_string .= $reference->volume;
		if (isset($reference->issue) && ($reference->issue != ''))
		{
			$this->sici_string .= ':' . $reference->issue;
		}
		$this->sici_string .= '<';
		$this->sici_string .= $reference->spage; 
		
		
		if (isset($reference->title))
		{
			$this->sici_string  .= ':';

			// Title
			$title = str_replace('(', '', $reference->title);
			$title = str_replace(')', '', $title);

			$title = str_replace('[', '', $title);
			$title = str_replace(']', '', $title);
			
/*			$title = strtr($title,
				'ÂÊÁÈÉËÍÎÌÏÓÔÒÛÚÙÜÖÜÅÔØ',  
				'AEAEEEIIIIOOOUUUUOUAOO'								
				);*/
		
			$title = ucwords($title);
		
			$words = explode(' ', $title);
			
			$count = 0;
			foreach($words as $w)
			{
				if ($count < 6)
				{
					$this->sici_string .= $w[0];
				}
				$count++;
			};
		}
		
		$this->sici_string .= '>';
		$this->sici_string .= '2'; // CSI
		$this->sici_string .= '.0'; // article
		$this->sici_string .= '.CO;'; // MFI online
		$this->sici_string .= '2';
		$this->sici_string .= '-';
		
		$this->checksum();
		
		return $this->sici_string;
	}
	
	//----------------------------------------------------------------------------------------------
	/**
	 * @brief Test whether we have enough information to generate a SICI
	 *
	 * Minimally we need a ISSN, volume, starting page, and year
	 *
	 * @param reference Reference object 
	 *
	 * @return True if we have enough information, false otherwise.
	 */
	function can_create($reference)
	{
		$can = false;
		
		if (
			isset($reference->issn)
			&& isset($reference->volume)
			&& isset($reference->spage)
			&& isset($reference->year)
			)
		{
			if (
				($reference->issn != '')
				&& ($reference->volume != '')
				&& ($reference->spage != '')
				&& ($reference->year != '')
				)
			{
				$can = true;
			}
		}
		return $can;
	}
	
	//----------------------------------------------------------------------------------------------
	/**
	 * @brief Unpack a SICI into the constituent parts
	 *
	 * Decompose a SICI into an associative array, based on regular expressions in Biblio Utils.pm
	 * I've edited the item regular expression to handle ISSNs that end in 'X'
	 *
	 * For example, given the SICI 0096-3801(1958)108:3392<25:BCANSO>2.0.CO;2-R
	 *
	 * this method returns:
	 * <pre>
	 * Array
	 * (
	 *     [item] => 0096-3801(1958)108:3392
	 *     [contrib] => 25:BCANSO
	 *     [control] => 2.0.CO;2-R
	 *     [issn] => 0096-3801
	 *     [chron] => 1958
	 *     [enum] => 108:3392
	 *     [year] => 1958
	 *     [locn] => 
	 *     [title] => BCANSO
	 *     [site] => 25
	 *     [issue] => 3392
	 *     [volume] => 108
	 *     [csi] => 2
	 *     [dpi] => 0
	 *     [mfi] => CO
	 *     [version] => 2
	 *     [check] => R
	 * )
	 * </pre>
	 *
	 * @return Associative array listing parts of SICI
	 */
	function unpack()
	{
		
		/*	my %out = ();
			($out{item}, $out{contrib}, $out{control}) = ($sici =~ /^(.*)<(.*)>(.*)$/);
			($out{issn}, $out{chron}, $out{enum}) = ($out{item} =~ /^(\d{4}-\d{4})\((.+)\)(.+)/);
			($out{site}, $out{title}, $out{locn}) = (split ":", $out{contrib});
			($out{csi}, $out{dpi}, $out{mfi}, $out{version}, $out{check}) = ($out{control} =~ /^(.+)\.(.+)\.(.+);(.+)-(.+)$/); 
			($out{year}, $out{month}, $out{day}, $out{seryear}, $out{seryear}, $out{sermonth}, $out{serday}) = ($out{chron} =~ /^(\d{4})?(\d{2})?(\d{2})?(\/(\d{4})?(\d{2})?(\d{2})?)?/);
			$out{enum} = [split ":", $out{enum}];
		*/
		
		$reference = new stdclass;
		
		$match = array();
		if (preg_match('/^(.*)<(.*)>(.*)$/', $this->sici_string, $match))
		{
			//print_r($match);
			
			$out['item'] = $match[1];
			$out['contrib'] = $match[2];
			$out['control'] = $match[3];
		}
		
		if (isset($out['item']))
		{
			if (preg_match('/^(\d{4}-\d{3}([0-9]|X))\((.+)\)(.+)/', $out['item'], $match))
			{	
				//print_r($match);
				
				$out['issn'] = $match[1];
				$out['chron'] = $match[3];
				$out['enum'] = $match[4];
			}
		}
		
		if (isset($out['chron']))
		{
			if (preg_match('/^(\d{4})?(\d{2})?(\d{2})?(\/(\d{4})?(\d{2})?(\d{2})?)?/', $out['chron'], $match))
			{	
				//print_r($match);
				
				if (isset($match[1])) $out['year'] = $match[1];
				if (isset($match[2])) $out['month'] = $match[2];
				if (isset($match[3])) $out['day'] = $match[3];
				if (isset($match[4])) $out['seryear'] = $match[4];
				if (isset($match[5])) $out['sermonth'] = $match[5];
				if (isset($match[6])) $out['serday'] = $match[6];
			}
		}
		
		if (isset($out['contrib']))
		{
			list($out['site'], $out['title'], $out['locn']) = split (":", $out['contrib']);
		}
		
		if (isset($out['enum']))
		{
			list($out['volume'], $out['issue'], $out['locn']) = split (":", $out['enum']);
		}
		
		if (isset($out['control']))
		{
			if (preg_match('/^^(.+)\.(.+)\.(.+);(.+)-(.+)$/', $out['control'], $match))
			{
				//print_r($match);
				
				if (isset($match[1])) $out['csi'] = $match[1];
				if (isset($match[2])) $out['dpi'] = $match[2];
				if (isset($match[3])) $out['mfi'] = $match[3];
				if (isset($match[4])) $out['version'] = $match[4];
				if (isset($match[5])) $out['check'] = $match[5];
			
			}
		}
		
		return $out;
	}
	
 
}


// test
/*
$reference = new stdclass;
$reference->title = 'Branchinecta cornigera, a new species of anostracan phyllopod from the State of Washington';
$reference->issn='0096-3801';
$reference->volume=108;
$reference->issue=3392;
$reference->spage = 25;
$reference->epage=37;
$reference->year=1958;

$s = new Sici;

$sici = $s->create($reference) . "\n";

echo $sici;

print_r($s->unpack());
*/


?>