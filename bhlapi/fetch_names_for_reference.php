<?php

require_once('../config.inc.php');
require_once('../db.php');

$ids=array(
113472
);

//foreach ($ids as $reference_id)


for ($reference_id=113472; $reference_id <= 113472; $reference_id++)
{
	echo "-- $reference_id\n";
	
	$article = db_retrieve_reference($reference_id);

	$pages = bhl_retrieve_reference_pages($reference_id);
	
	//print_r($pages);

	foreach ($pages as $page)
	{
		$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetPageNames&pageid=' . $page->PageID . '&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';
			
		$json = get($url);
		
		$obj = json_decode($json);
		
		//print_r($obj);
		
		if ($obj->Status == 'ok')
		{
			foreach ($obj->Result as $name)
			{
				$sql = "DELETE FROM bhl_page_name WHERE NameConfirmed='" . addslashes($name->NameFound) . "' AND PageID=" . $page->PageID . ";";
		
				echo $sql . "\n";
		
				$sql = "INSERT INTO bhl_page_name(NameBankID,PageID,NameConfirmed) VALUES (";
		
				if ($name->NameBankID != null)
				{
					$sql .= $name->NameBankID;
				}
				else
				{
					$sql .= '0';
				}
				$sql .= ',' . $page->PageID;
				$sql .= ",'" . addslashes($name->NameFound) . "'";
				$sql .= ');';
				
				echo $sql . "\n";
			}
		}
		

	}			

}

?>