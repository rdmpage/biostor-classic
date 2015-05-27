<?php

/**
 * @file edits.php
 *
 * Dump list of edit count by IP (who is editing BioStor?)	
 *
 */

require_once ('../db.php');

		$sql = 'SELECT INET_NTOA(ip) as ip, COUNT(id) as c 
FROM rdmp_reference_version
GROUP BY INET_NTOA(ip)
ORDER BY c DESC
LIMIT 40';

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		echo '<table border="1">' . "\n";
		echo '<tr><th>IP</th><th>Host</th><th>Number of edits</th></tr>' . "\n";
		while (!$result->EOF) 
		{			
		
			echo '<tr>';
			echo '<td>' . $result->fields['ip'] . '</td>';
			echo '<td>' . gethostbyaddr($result->fields['ip']) . '</td>';
			echo '<td>' . $result->fields['c'] . '</td>';		
			echo '</tr>';
			
			$result->MoveNext();
		}	
		echo '</table>';


?>