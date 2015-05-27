<?php
  require_once(dirname(dirname(__FILE__)) . '/Apache/Solr/Service.php');


  require_once(dirname(dirname(__FILE__)) . '/db.php');
  require_once(dirname(dirname(__FILE__)) . '/reference.php');

  
  // 
  // 
  // Try to connect to the named server, port, and url
  // 
  $solr = new Apache_Solr_Service( 'localhost', '8983', '/solr' );
  
  if ( ! $solr->ping() ) {
    echo 'Solr service not responding.';
    exit;
  }
  
  
  	$sql = "SELECT COUNT(reference_id) as c FROM rdmp_reference WHERE (PageID <> 0)";
  	
  	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


  	$num = $result->fields['c'];
  	
  	$page_size = 100;
  	
  	$pages = $num/$page_size;
  	
  	for ($page = 0; $page < $pages; $page++)
  	{

		$parts = array();
		
		$sql = "SELECT reference_id FROM rdmp_reference  WHERE (PageID <> 0) LIMIT " . ($page * $page_size) .  "," .  $page_size;
		
		echo $sql . "\n";
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		$ids = array();
		while (!$result->EOF) 
		{
			$ids[] = $result->fields['reference_id'];
			$result->MoveNext();
		}
		
		foreach ($ids as $id)
		{
		
			$reference = db_retrieve_reference ($id);
			
			$item = reference_to_solr($reference);
			/*
			$obj  = reference_to_mendeley($reference);
			
		
			$item = array();
			$item['id'] 				= 'reference/' . $id;
			$item['title'] 				= $obj->title;
			$item['publication_outlet'] = $obj->publication_outlet;
			$item['year'] 				= $obj->year;
			
			$authors = array();
			foreach ($obj->authors as $a)
			{
				$authors[] = $a->forename . ' ' . $a->surname;
			}
			$item['authors'] = $authors;
			$item['citation'] = reference_authors_to_text_string($reference)
				. ' ' . $reference->year 
				. ' ' . $reference->title
				. ' ' . reference_to_citation_text_string($reference);
				
			*/
			print_r($item);
		
		
			$parts[] = $item;
		}
	
		
		print_r($parts);
		
	  $documents = array();
	  
	  foreach ( $parts as $item => $fields ) {
		$part = new Apache_Solr_Document();
		
		foreach ( $fields as $key => $value ) {
		  if ( is_array( $value ) ) {
			foreach ( $value as $datum ) {
			  $part->setMultiValue( $key, $datum );
			}
		  }
		  else {
			$part->$key = $value;
		  }
		}
		
		$documents[] = $part;
	  }
		
	  //
	  //
	  // Load the documents into the index
	  // 
	  try {
		$solr->addDocuments( $documents );
		$solr->commit();
		$solr->optimize();
	  }
	  catch ( Exception $e ) {
		echo $e->getMessage();
	  }
	}
?>