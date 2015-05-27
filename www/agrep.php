<?php

/* $$Id: agrep.inc.php,v 1.1.1.1 2003/05/09 16:54:00 rdmp1c Exp $ */

/*
 * @param $agrep_pattern pattern to search for
 * @param $agrep_file file to search for pattern
 * @param $agrep_line array of lines that match pattern
 * @param $agrep_matches array of strings that match pattern
 * @param $agrep_mismatch number of mismatches allowed (optional, default is 3)
 */ 
function agrep($agrep_file, $agrep_pattern, &$agrep_line, &$agrep_matches, $agrep_mismatch = 3)
{
	$agrep_path = "/usr/local/bin/agrep";	// path to agrep
	$agrep_options = "-n -w -i"; //"-x -n"; 				// whole words, output line number
	$agrep_command = "$agrep_path -$agrep_mismatch $agrep_options '$agrep_pattern' $agrep_file";
	
	//echo $agrep_command . "\n";
	
	exec ($agrep_command, $agrep_result);
	
	if (sizeof($agrep_result) != 0)
	{
		for ($i=0; $i<sizeof($agrep_result);  $i++)
		{
			if (preg_match('/^(?<line>\d+):\s*(?<hit>.*)$/', $agrep_result[$i], $m))
			{
				$agrep_line[$i] = $m['line'];
				$agrep_matches[$i] = $m['hit'];
			}
		}
	}	
	return sizeof($agrep_result);
}

// test
if (0)
{
$filename = '1.txt';
$pattern = 'Abgarus';
$lines = array();
$matches = array();

echo agrep($filename, $pattern, $lines, $matches);

print_r($matches);
}


?>
