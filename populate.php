<?php

// Get images, thumbnails, and OCR text for an item to pre-populate local copy of BHL

require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/bhl_text.php');
require_once (dirname(__FILE__) . '/bhl_utilities.php');

//--------------------------------------------------------------------------------------------------
// BHL Item 
$ItemID = 15591;

$ItemID = 25781;

$ItemID = 30469;

$ItemID = 26735; // BMCZ 133

// Bull Mus Comp Zool
// TitleID = 2803

$ItemID = 26726; // vol 151

// Kansas
$ItemID = 26012;


// Official lists and indexes of names and works in zoology
if (0)
{
	$items = array(20019);
}

// Fixes
if (0)
{
	$items = array(20513);
}

// Fieldiana Zoology (sigh)
if (0)
{
	$items=array(21043, 20944, 25058, 30774, 30778, 21009, 21062, 20497, 21612, 21485, 21437, 21582, 20521, 21513, 21668, 21462, 21496, 24864, 20540, 21445, 21470, 25062, 20896, 21215, 20762, 21080, 20988, 21117, 26386, 26384, 21113, 21205, 20808, 20856, 21394, 21564, 21478, 21475, 21483, 20910, 21233, 21108, 20803, 21065, 20834, 20810, 20967, 20998, 20780, 20898, 25992, 21251, 20933, 21229, 20969, 20816, 21016, 20800, 20917, 20949, 21223, 21115, 20753, 20835, 21111, 21267, 21257, 21014, 20802, 21094, 21124, 21112, 21206, 21232, 20876, 21082, 21249, 20813, 20824, 21024, 21638, 21629, 20524, 21586, 21114, 21604, 21484, 21188, 21325, 21662, 21439, 21400, 21617, 21560, 21419, 20853, 21401, 21532, 21609, 25996, 25293, 21534, 21181, 21490, 21494, 21109, 21184, 20500, 21555, 21319, 21528, 21438, 21336, 21516, 21642, 21316, 20481, 20473, 20482, 20444, 20462, 20373, 20413, 20453, 20386, 20485, 20405, 20474, 20370, 20398, 20435, 20465, 30676, 30755, 20355, 25990, 25989, 20423, 20379, 20460, 20401, 25966, 25970, 25967, 20422, 20475, 25973, 25997, 25987, 25988, 20410, 20390, 21294, 20746, 21266, 20733, 20556, 20978, 21245, 21025, 30772, 30791, 20368, 21051, 25994, 25991, 25993, 21239, 20784, 20559, 25983, 20759, 20854, 20806, 20557, 20831, 15559, 15560, 20769, 20942, 21258, 20765, 20926, 20952, 21244, 20367, 20562, 20921, 20947, 20850, 26383, 20786, 20801, 20781, 21064, 20999, 21260, 21301, 21010, 20927, 20932, 20787, 20750, 21236, 20974, 20764, 21053, 21061, 21241, 21005, 20941, 20849, 21210, 20866, 20385, 30754, 21017, 21035, 20960, 21118, 20940, 21261, 21071, 20874, 20968, 21276, 21278, 21264, 20872, 21029, 20832, 20981, 21281, 21277, 21297, 20934, 21012, 20761, 21079, 21030, 20995, 20955, 20742, 20929, 20975, 20912, 21083, 20878, 21296, 21084, 20980, 20889, 20740, 20563, 21047, 21240, 20561, 20936, 21308, 21090, 20979, 20930, 20741, 21046, 20877, 21028, 20963, 21309, 21298, 20894, 21098, 20731, 21286, 20838, 20959, 21289, 20951, 20987, 20867, 20766, 20982, 20855, 21119, 21044, 20879, 21284, 21049, 21093, 21121, 21031, 20821, 20976, 20729, 20977, 21299, 20972, 21305, 21699, 21701, 21700, 21702, 20857, 20961, 20891, 21015, 20844, 21081, 21048, 20861, 21166, 20791, 21050, 20859, 21270, 21036, 20825, 20935, 20922, 20890, 20818, 20953, 20836, 30753, 20352, 20354, 30744, 20409, 20467, 20436, 30743, 20490, 20353, 20417, 30745, 20489, 20403, 20468, 20470, 21027, 25059, 20950, 20778, 21054, 20931, 21243, 21023, 20814, 20919, 20863, 21078, 25052, 21602, 24870, 21615, 21644, 21567, 21418, 21663, 21459, 24866, 20885, 25057, 25982, 25980, 25978, 25979, 21307, 20730, 21304, 20770, 21077, 20805, 21285, 20957, 21032, 20735, 21282, 20882, 24859, 21102, 25061, 25985, 20564, 21091, 20768, 21127, 20788, 20869, 20883, 21072, 21237, 20870, 20823, 20839, 20817, 21211, 20771, 20973, 20754, 21262, 20958, 21238, 21052, 20956, 21259, 21008, 20842, 20819, 20782, 20860, 20913, 21013, 20822, 20965, 20985, 21000, 21290, 21265, 20792, 21055, 20996, 21303, 21306, 21026, 20841, 20767, 20749, 21038, 21006, 30789, 21536, 21464, 20549, 21514, 24053, 21552, 21388, 21449, 21425, 21633, 20533, 21182, 21371, 20548, 21427, 21634, 25213, 21588, 21492, 21618, 30773, 21563, 21530, 21362, 20535, 21450, 21441, 21619, 21649, 21318, 21434, 20517, 20529, 21370, 21442, 21457, 21354, 21502, 21600, 21456, 21620, 21410, 21473, 21647, 21614, 20441, 20437, 24863, 29599, 20907, 20789, 21089, 20990, 21313, 20886, 21295, 24438, 20946, 21228, 20830, 20809, 20851, 20983, 21248, 20560, 20937, 20783, 21221, 21268, 21040, 20833, 21314, 21209, 21110, 20843, 21217, 20920, 20811, 21018, 20871, 20847, 21088, 20864, 21246, 20893, 21283, 20997, 21269, 21280, 21224, 21039, 20884, 21063, 21105, 20760, 21019, 21058, 20901, 20745, 21208, 20862, 20966, 20895, 20798, 21096, 21087, 20962, 21056, 21066, 21183, 20744, 20964, 20558, 20868, 20928, 25063, 21060, 20905, 25080, 21226, 21011, 30775, 20986, 20779, 20900, 21045, 20772, 20752, 20915, 21312, 25995, 21042, 20785, 20903, 20993, 20970, 21067, 21214, 20948, 21086, 20887, 20807, 20906, 21203, 20954, 21263, 21074, 23885, 20748, 21279, 20984, 20773, 20945, 21212, 20466, 20420, 20356, 20381, 20396, 20414, 20429, 20421, 20361, 20406, 20380, 20408, 20427, 20751, 20873, 21021, 30787, 21123, 20820, 25056, 25079, 21107, 21302, 21033, 21122, 21300, 20994, 20815, 15484, 25986, 20366);
}
// Bulk import
if (0)
{
	//--------------------------------------------------------------------------------------------------
	$TitleID = 2192; // Bulletin of the British Museum (Natural History). Entomology
	$TitleID = 2202; // Bulletin of the British Museum (Natural History). Zoology
	$TitleID = 2198; // Bulletin of the British Museum (Natural History). Botany

	$TitleID = 2471; // Official lists and indexes of names and works in zoology


	$TitleID = 3989; // Breviora
	$TitleID = 2803; // Bull Mus Comp Zool
	$TitleID = 2680; // Journal of Hymenoptera research
	$TitleID = 2203; // Trans Linn Soc
/*	$TitleID = 11541; // Bulletin of Zoological Nomenclature
	
	// Kansas
	$TitleID = 3179; // The University of Kansas science bulletin.
	$TitleID = 5584; // Occasional papers of the Natural History Museum, the University of Kansas, Lawrence, Kansas.
	$TitleID = 3141; // University of Kansas publications, Museum of Natural History.
	$TitleID = 8255; // Transactions of the Kansas Academy of Sciences
	$TitleID = 8256; // Transactions of the Kansas Academy of Sciences
	$TitleID = 15415; // The University of Kansas science bulletin.
	$TitleID = 4672; // Occasional papers of the Museum of Natural History, the University of Kansas.
	
	// California
	$TitleID = 3943; // Proceedings of the California Academy of Sciences, 4th series.

	$TitleID = 15816; // Proceedings of the California Academy of Sciences.
	$TitleID = 3952; // Proceedings of the California Academy of Sciences.
	$TitleID = 3966; // Proceedings of the California Academy of Sciences.
	$TitleID = 4274; // Proceedings of the California Academy of Sciences.
	$TitleID = 7411; // Proceedings of the California Academy of Sciences.
	$TitleID = 12931; // Proceedings of the California Academy of Sciences.
	
	$TitleID = 6525; // Proceedings of the Linnean Society of New South Wales.*/
	
	$items = array();
	$sql = 'SELECT * FROM bhl_item WHERE (TitleID = ' . $TitleID . ')';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		array_push($items, $result->fields['ItemID']);
		$result->MoveNext();
	}
	
	print_r($items);
}

// BMNH 12 (Madagascar grasshoppers)
//$items = array(19513);

//$items = array(32860); //	Proceedings of the United States National Museum v47 1915 Rathburn crab plates

//$items = array(47571); // Catalogue of colubrine snakes in the collection of the British Museum

$items = array(32774);

foreach ($items as $ItemID)
{
	// This will hold pages
	$pages = array();
	
	// Find all pages for this item, ordered by SequenceOrder
	$sql = 'SELECT page.PageID
	FROM page 
	INNER JOIN bhl_page USING (PageID)
	WHERE (page.ItemID = ' . $ItemID . ')
	ORDER BY page.SequenceOrder
	';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$pages[] = $result->fields['PageID'];
		$result->MoveNext();
	}
	
	print_r($pages);
	
	foreach ($pages as $page)
	{
		echo "$page\n";
		bhl_fetch_page_image($page);
		bhl_fetch_ocr_text($page, '', 30);
	}

}

?>