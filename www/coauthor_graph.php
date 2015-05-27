<?php

/**
 * @file coauthor_graph.php
 *
 * Output DOT format file of coauthors
 *
 */

require_once('../db.php');
require_once('../graph.php');

$author_id = 0;

if (isset($_GET['author_id']))
{
	$author_id = $_GET['author_id'];
}

if ($author_id != 0)
{
	// 1. get coauthors
	
	$coauthors = db_retrieve_coauthors($author_id);
	
	//print_r($coauthors);
	
	$n = count($coauthors->coauthors);
	
	$G = new Graph();
	
	for ($i = 0; $i < $n; $i++)
	{
		$sourceNode = $G->AddNode($coauthors->coauthors[$i]->cluster_id, $coauthors->coauthors[$i]->forename . ' ' . $coauthors->coauthors[$i]->lastname);
		
		for ($j = $i+1; $j < $n; $j++)
		{
			if ($coauthors->coauthors[$i]->cluster_id != $coauthors->coauthors[$j]->cluster_id)
			{
				$num  = db_number_coauthored_references(
					$coauthors->coauthors[$i]->id, $coauthors->coauthors[$j]->id);
		
				if ($num > 0)
				{
					$targetNode = $G->AddNode(
						$coauthors->coauthors[$j]->cluster_id, 
						$coauthors->coauthors[$j]->forename . ' '  . $coauthors->coauthors[$j]->lastname);
					$G->AddEdge($sourceNode, $targetNode, $num);
				}		
			}
		}
	}
	
//	header("Content-type: text/x-graphviz");
	header("Content-type: text/plain");
	echo $G->WriteDot();
}
else
{
	echo 'No author_id supplied';
}

?>