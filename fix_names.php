<?php

// fetch names if we haven't got them...
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/bhl_names.php');
require_once (dirname(__FILE__) . '/gnrd.php');


$start   = 189713;
$end     = 190686;

$start   = 190695;
$end     = 191434;
$start   = 191437;
$end     = 192055;
$start   = 192056;
$end     = 192083;

$start   = 208749;
$end     = 208809;








$ids=array();

$ids=array(
114877
);

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
