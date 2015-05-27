<?php

// taxon name code

// broken, need to update...

require_once(dirname(__FILE__) . '/taxon.php');
require_once(dirname(__FILE__) . '/globalnames.php');


//--------------------------------------------------------------------------------------------------
$name = 'Pinnotheres atrinicola';
//$name = 'Aspasma ciconiae'; // local
//$name = 'Aspasmichthys ciconiae'; // accepted name in CoL, not in local BHL
//$name = 'Astyanax varzeae';
//$name = 'Aeschnidiopsis flindersiensis';

//$name='Cryptops mirus';

$name = 'Amphiprion ocellaris'; // nemo

$name = 'Mesoclemmys dahli'; // synonym of Batrachemys dahli, which is in BHL

$name = 'Tropidonophis picturatus';

$NameBankID = 0;
$namestring_id = 0;

// Is this in our copy of BHL?
if ($NameBankID == 0)
{
	echo "Looking in local BHL...";
	$NameBankID = bhl_name($name);
	echo ($NameBankID == 0 ? "NOT " : '') . "found\n";
}

if ($NameBankID == 0)
{
	echo "Looking in our namestring database...";
	
	$namestring_info = new NameString;
	$namestring_id = db_get_namestring_id($name, $namestring_info);
	
	if ($namestring_id != 0)
	{
		print_r($namestring_info);
		$NameBankID = $namestring_info->NameBankID;
		
		echo " found\n";
	}
	else
	{
		echo "NOT found\n";
	}
}

if (($NameBankID == 0) && ($namestring_id == 0))
{
	echo "Checking in  uBio...";
	$NameBankID = bioguid_ubio_search($name);
	echo ($NameBankID == 0 ? "NOT " : '') . "found\n";
	
	if ($NameBankID != 0)
	{
		$namestring_id = db_store_namestring($name, $NameBankID);
	}
}

if (($NameBankID == 0) && ($namestring_id == 0))
{
	echo "Not in uBio or local database, need to go further afield\n";
	
	$guids = find_in_global_names ($name);
	print_r($guids);
	
}
else
{
	// for fun do lookup anyway...
	$guids = find_in_global_names ($name);
	print_r($guids);	
}


echo "\"$name\" NameBankID=$NameBankID, namestring_id=$namestring_id\n";


?>