<?php

require_once('db.php');

//for ($reference_id=66009;$reference_id<=66058;$reference_id++)

// 85560-85454


//for ($reference_id=85454;$reference_id<=85560;$reference_id++)
//for ($reference_id=85581;$reference_id<=85582;$reference_id++)

$ids=array(
45062,
44966,
44952,
44816,
44626,
44625,
47587
);

$ids=array(85596);

$ids=array(85604);

$ids=array(51688);

$ids=array(85711,85717);

//$ids=array(86103);

$ids=array(80076,80077,80078,80079,80080,80082,80083,80084,80085,80089,80090,80091,80093,80095,80100,80103,80104,80106,80109,80112,80115,80117);

$ids=array(133261);

$ids=array(153594);

$ids=array(113726,
113727,
113728,
113729,
113730,
113731,
113732,
113733,
113734,
113735,
113736,
113737,
113738,
113739,
113740,
113741,
113742,
113743,
113744,
113745,
113746,
113747,
113748,
144304
);

$ids=array(63546);

$ids=array(63294,
62313,
62826,
63630,
63519,
63483)
;

$ids=array(177750);


$ids=array(
   3433,
   3432,
  76518,
   3430,
   3429,
  76554,
   3431,
  76544,
  76548,
  76527,
  76524,
  76556,
  76529,
   3428,
  76553,
  76531,
  76534,
  76526,
  76537,
  76530,
   4957,
  76522,
   3427,
  97268,
   3426,
   3425,
  76535,
  76549,
  76543,
  76519,
  76558,
  76533,
  76550,
   3424,
  76521,
  76523,
   3423,
  76551,
  76540,
   3422,
  76557,
  76539,
  76528,
  76542,
  76525,
  76517,
  76536,
  76532,
   3421,
   3420,
  76538,
  76546,
  76547,
  76555,
  76541,
  76520,
  76552,
   3776,
  76545
);

$ids=array(215242,
215243,
215244,
215245,
215246,
215247,
215248,
215249,
215250,
215251,
215252,
215253,
215254,
215255,
215256,
215257,
215258,
215259,
215260,
215261,
215262);

$ids=array(
95448
);

$ids=array(59406,900,77827,899,898,897,896,895);
$ids=array(120582);
$ids=array(119648,119682,145610,119841,120114);
$ids=array(71833,69572);
$ids=array(125809,125811,125867,125767,125875);
$ids=array(125845,125846,125765,125805,125863);
$ids=array(65115);
$ids=array(125819,125823,125874,125780,125807,125864,125771,125770);
$ids=array(134838);
$ids=array(68267,67934,68747,68098,68099,68023,67600,68100,68375,68376,68707,68778,68737);
$ids=array(119761,119420,192515);
$ids=array(60119);
$ids=array(120448,120422,119458,119734,120514,120606,119441);
$ids=array(120466,120582);

$ids=array(245732);

$start   = 242536;
$end     = 242536;

$ids=array(114565);

$start   = 250352;
$end     = 250352;

$ids=array(
143903,
//59892,
114490,
114503,
235564,
235567,
235123,
114505,
235561,
235973,
117471,
88771,
71812,
61550,
//65375,
61540,
235556,
235574,
235551,
13209,
58814,
235564,
235563,
59714,
59716,
59223,
59150,
99264,
235547,
235571,
61411,
235555,
235577,
235593,
60661,
60658,
60660,
59251,
59248,
61403,
235795,
235784,
61446,
235550,
61358,
61541,
61531,
235578,
13649,
51253,
60659,
61544,
61280,
60010,
61445,
60006,
60007,
57670,
58280,
90264,
60008,
60012,
59204,
60011,
60013,
60009,
88644,
81613,
81615,
69152,
235791,
59259,
60665,
60663,
60669,
60668,
13237,
61552,
61682,
235597,
128035,
61419,
235576,
235572,
235549,
235781,
235560,
59250,
235588,
59155,
131615,
131598,
131615,
131606,
61546,
61045,
61550,
235785,
61412,
61409,
61538,
235553,
235797,
235589,
60121,
61157,
235787,
235558,
13199,
235575,
235787,
235786,
61550,
94206,
59174,
60555,
117432,
114692,
114670,
61049,
61158,
61405,
61054,
235584,
207717,
90622,
127205,
//59387,
61157,
235277,
);

foreach ($ids as $reference_id)
//for ($reference_id=$start;$reference_id<=$end;$reference_id++)
{

	echo "-- reference_id = $reference_id\n";
	
	$article = db_retrieve_reference($reference_id);


	$page_range = array();
	if (isset($article->spage) && isset($article->epage))
	{
		$page_range = 
			bhl_page_range($article->PageID, $article->epage - $article->spage + 1);
	}
	else
	{
		// No epage, so just get spage (to do: how do we tell user we don't have page range?)
		$page_range = 
			bhl_page_range($article->PageID, 0);				
	}
	
	//print_r($page_range);
	
	echo "DELETE FROM rdmp_reference_page_joiner WHERE reference_id=$reference_id;\n";
	
	$count = 0;
	foreach ($page_range as $page)
	{
		$sql = 'INSERT INTO rdmp_reference_page_joiner (reference_id, PageID, page_order) VALUES (' . $reference_id . ',' . $page . ',' . $count++ . ');';
		echo $sql . "\n";
		
		//$result = $db->Execute($sql);
		//if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	}		




}

?>