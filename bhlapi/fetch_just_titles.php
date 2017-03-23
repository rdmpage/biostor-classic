<?php

// just title

require_once('../config.inc.php');
require_once('../lib.php');

$titles = array(955,
958,
960,
968,
972,
974,
2233,
2247,
2255,
2270,
2288,
2297,
2301,
2328,
42247,
2381,
2382,
2383,
2384,
2385,
2386,
2387,
2388,
2389,
2390,
2391,
2392,
2393,
2394,
2396,
2397,
2398,
3847,
2399,
2400,
2401,
2402,
2403,
2404,
2405,
2406,
2407,
2408,
2394,
2409,
2410,
2411,
2412,
2413,
2414,
2415,
2416,
2417,
2418,
2419,
2420,
2421,
2422,
3843,
2425,
2426,
2427,
2428,
2429,
2430,
2431,
2432,
2433,
2434,
2435,
2436,
2437,
2438,
2439,
2442,
2443,
2444,
2445,
2446,
2447,
2380,
2448,
2449,
2450,
2451,
3851,
2452,
2453,
2454,
2456,
2457,
2536,
2537,
2538,
2540,
2541,
2542,
2543,
2547,
2549,
2550,
2552,
2555,
2559,
2561,
2565,
2567,
2569,
2571,
2573,
2577,
2582,
2586,
2590,
2591,
2593,
2594,
2598,
2599,
2600,
2602,
2604,
2606,
2607,
2608,
2612,
2613,
2614,
2617,
2618,
2620,
2623,
2624,
2626,
2627,
2628,
2629,
2636,
2641,
2643,
2644,
2648,
2649,
2650,
2651,
2652,
2655,
2658,
2659,
2660,
2662,
2663,
2664,
4816,
4961,
5638,
5639);

$titles=array(2542);

foreach ($titles as $TitleID)
{
	$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetTitleMetadata&titleid=' . $TitleID . '&items=true&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';
	$json = get($url);
	$title_obj = json_decode($json);

	print_r($title_obj);
	//exit();



	if ($title_obj->Status == 'ok')
	{

		$sql = "REPLACE INTO bhl_title(TitleID, FullTitle, ShortTitle) VALUES ("
			. $title_obj->Result->TitleID . ",'" . addslashes($title_obj->Result->FullTitle) . "','" . addslashes($title_obj->Result->ShortTitle) . "');";
		echo $sql . "\n";
		
		if (isset($title_obj->Result->Doi))
		{
			$sql = "UPDATE bhl_title SET Doi='" . $title_obj->Result->Doi . "' WHERE TitleID=" . $title_obj->Result->TitleID . ";";
			echo $sql . "\n";
		}
		
		// authors
		if (isset($title_obj->Result->Authors))
		{
			$authors = array();
			foreach ($title_obj->Result->Authors as $a)
			{
				$authors[] = $a->Name;
			}
			
			$sql = "UPDATE bhl_title SET Authors='" . join(";", $authors) . "' WHERE TitleID=" . $title_obj->Result->TitleID . ";";
			echo $sql . "\n";
		}
		
		
		
		
	}
	
}

?>
