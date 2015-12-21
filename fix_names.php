<?php

// fetch names if we haven't got them...
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/bhl_names.php');
require_once (dirname(__FILE__) . '/gnrd.php');

$ids = array(126311);

$ids=array(105416,105402,125890,59343,107177);

$ids=array(110878);

$ids=array(99906,108144,100200,100656,107955,107963,107976,83078,66142,87244,87255,105412,87185,87103,65960,66503,87120,105406,86522,105403,126315,107177,95592,97207,105415,87635,97899,65958,48993,59343,14562,125890,103534,109586,891,97484,889,105414,1312,104732,84533,113895,105402,85290,114597,126311,102399);

$ids = array(54668,14790,54677,99677,127560,60044,127575,127574,99512,107049,106157,105730,125900,127561,105962,61688,58301,105714,58630,14789,98326,14787,106156,97776,99962,98307,14781,97871,4435,844,97482,832,97490,101826,106132);

$ids=array(127609);
$ids=array(120824);

$ids=array(116847);

$ids=array(128019);

$ids=array(114747);

$ids=array(128106);



//foreach ($ids as $reference_id)

$start = 1;
$end = 126555;
$start = 51457;
$start = 97870;

$start = 128106;
$end = 128279;

$start = 128365;
$end = 128366;

$start = 129112;
$start = 129460;
$end=129461;

$start=129491;
$end=129492;

$start=66064;
$end=66065;

$start = 86824;
$end = 86825;

$start = 127643;
$end = 127644;

//$ids=array(1908,100192,4943,1686);

$ids=array(131913);

$ids=array(102396);

$start = 79907;
$end = 79907;

$start = 132199;
$end = 132203;


$start = 132748;
$end = 132751;

$start=92095;
$end=92095;

$start = 113710;
$end   = 113710;

$start = 115222;
$end=115222;

$start = 133739;
$end=133739;


$start = 134862;
$end=134862;

$start = 135520;
$end  = 135520;

$start = 135565;
$end = 135632;

$start = 135752;
$end = 135753;

$start = 135771;
$end = 135771;


$start = 135788;
$end = 135788;

$start=135886;
$end=135886;

$start = 136806;
$end = 136972;

$start = 111677;
$end = 111677;


$start = 137048;
$end= 137051;

$start = 137051;
$end= 137070;

//$start = 137071;
//$end= 137073;

$start = 128905;
$end = 128905;

$start = 143114;
$end = 143347;

$start = 39139;
$end = 39141;

$start = 143388;
$end = 143388;

// 


$start = 143863;
$end = 143863;

$start = 144300;
$end = 117536;

$start = 146564;
$end = 146567;

$start = 126692;
$end = 126692;

$start =  146656;
$end =  146656;

$start = 146771;
$end = 146771;


//$start = 107145;
//$end = 107145;

$start = 147447;
$end = 147466;

$start = 147489;
$end = 147523;

$start = 147653;
$end = 147653;


$start = 147666;
$end = 147666;

$start = 147667;
$end   = 147716;

$start = 148342;
$end   = 148342;

$start = 148378;
$end   = 148378;

$start = 151297;
$end   = 151297;

$start = 153764;
$end   = 153764;

$start = 157441;
$end   = 157916;

$start = 158067;
$end   = 160045;

$start = 160086;
$end   = 160086;

$start = 160224;
$end   = 160224;
         




//$ids=array(144543,144542,117078);

for ($reference_id = $start; $reference_id <= $end; $reference_id++)
//foreach($ids as $reference_id )
{
	
	echo "\n===BioStor reference $reference_id===\n";
	
	if (db_retrieve_reference($reference_id) != null)
	{
		$nm = bhl_names_in_reference_by_page($reference_id);
		
		if (isset($nm->names) && (count($nm->names) == 0))
		//if (1) // BHL
		{
			// fetch names
			$pages = bhl_retrieve_reference_pages($reference_id);
			$page_ids = array();
			foreach ($pages as $p)
			{
				echo $p->PageID;
				
				// delete any existing names
				$sql = 'DELETE FROM bhl_page_name WHERE PageID=' .  $p->PageID;
				echo $sql . "\n";
				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
				
				
				if (1)
				{
					// BHL API
				
					$parameters = array(
					'op' 		=> 'GetPageMetadata',
					'pageid' 	=> $p->PageID,
					'ocr' 		=> 'f',
					'names' 	=> 't',
					'apikey' 	=> '0d4f0303-712e-49e0-92c5-2113a5959159',
					'format' 	=> 'json'
					);
				
					$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?' . http_build_query($parameters);
					
					$json = get($url);
					
					//echo $json;
					if ($json != '')
					{
						$response = json_decode($json);
						
						foreach ($response->Result->Names as $Name)
						{
							echo '.';
							//print_r($Name);
							if (isset($Name->NameBankID) 
								&& ($Name->NameBankID != '')
								&& isset($Name->NameConfirmed))
							{
								$sql = 'INSERT INTO bhl_page_name(NameBankID,NameConfirmed,PageID) VALUES(' . $Name->NameBankID . ',' . $db->qstr($Name->NameConfirmed) . ',' . $p->PageID . ');';
								//echo $sql . "\n";
								$result = $db->Execute($sql);
								if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
							}
						}
					}
				}
				else
				{
					// GNDR
					$url = 'http://gnrd.globalnames.org/name_finder.json?url=http://direct.biostor.org/bhlapi_page_text.php?PageID=' . $p->PageID;

					$response = get_names_from_url($url);

					// Result
					//echo "GNRD response\n";
					//print_r($response);

					// Unique names
					$names = get_unique_names($response);
					echo "Unique names\n";
					print_r($names);
					
					foreach ($names as $name)
					{
						echo '.';
						$sql = 'INSERT INTO bhl_page_name(NameBankID, NameConfirmed,PageID) VALUES(0,' . $db->qstr($name) . ',' . $p->PageID . ');';
						//echo $sql . "\n";
						$result = $db->Execute($sql);
						if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);						
					}

					
				
				}
				echo "\n";
			}	
		}
		else
		{
			echo " done!";
		}
		
	}
	echo "\n";
}

?>
