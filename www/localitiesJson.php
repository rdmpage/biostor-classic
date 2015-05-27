<?php

// Return all point localities in BioStor as JSON for maps display
 
require_once('../db.php');

$localities = new stdclass;
 
$localities->places = db_retrieve_localities();

echo json_encode($localities);
?>

