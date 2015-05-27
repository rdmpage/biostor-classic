<?php

$files = scandir(dirname(__FILE__));

foreach ($files as $filename)
{
	if (preg_match('/^\d+\.json$/', $filename))
	{	

		echo "<a href=\"$filename\">$filename</a>\n";
	}
}

?>
