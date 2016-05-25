<?php

// Need mapping between codes, collections, and DiGIR

//--------------------------------------------------------------------------------------------------
// expand a numeric range such as 11697-9 into individual numbers and return as array
function expand_range($start, $end)
{
	//echo "start=$start end=$end\n";

	$range = array();
	
	$matched = false;
	
	$base = '';
	if (is_numeric($start))
	{
		$matched = true;
	}
	else
	{
		if (preg_match('/^(?<prefix>.*)\.(?<suffix>\d+)$/', $start, $m))
		{
			$base = $m['prefix'] . '.';
			$start = $m['suffix'];
			$matched = true;
		}
		if (preg_match('/^(?<prefix>\d+)\/(?<suffix>\d+)$/', $start, $m))
		{
			$base = $m['prefix'] . '/';
			$start = $m['suffix'];
			$matched = true;
		}
		if (preg_match('/^(?<prefix>[A-Z]-?)(?<suffix>\d+)$/', $start, $m))
		{
			$base = $m['prefix'];
			$start = $m['suffix'];
			$matched = true;
		}
	}
	
	if ($matched)
	{
		//echo $start . "\n";
		//echo $end . "\n";
		
		$len = strlen($end);
		if ($len < strlen($start))
		{
			$part = substr($start, 0, strlen($start) - $len);
			$end = $part . $end;
		}
		
		//echo $start . "\n";
		//echo $end . "\n";
		
		// Sanity check, if difference between start and end is large, may be a typo,
		// set a maximum limit to avoid this exploding...
		$diff = $end - $start;
		if ($diff < 100)
		{
			for ($i = $start; $i <= $end; $i++)
			{
				$range[] = $base . $i;
			}
		}		
		
		//print_r($range);
	}
	
	return $range;
}

//--------------------------------------------------------------------------------------------------
// extend specimen range...
function extend_specimens($startcode, $text)
{
	$ids = array();
	
	$parts = explode(' ', $startcode);
	$institutionCode = $parts[0];
	
	$matched = false;
	
	if (!$matched)
	{
		$pattern = '/' . addcslashes($startcode, "'/") . '(-\d+)?(?<run>(,\s+(\d+(\.\d+)+)(-\d+)?)+)' . '/';
		if (preg_match($pattern, $text, $m))
		{
			$run = $m['run'];
			$items = preg_split('/,\s+/', $run);
			
			foreach ($items as $item)
			{
				if ($item == '')
				{
				}
				else
				{
					if (preg_match('/^(?<start>.*)-(?<end>.*)$/', $item, $mm))
					{
						$range = expand_range($mm['start'], $mm['end']);
						
						foreach ($range as $r)
						{
							$ids[] = $institutionCode . ' ' . $r;
						}				
					}
					else
					{
						$ids[] = $institutionCode . ' ' . $item;
					}
				}
			}			
			$matched = true;
		}
	}
	
	if (!$matched)
	{
		$pattern = '/' . addcslashes($startcode, "'/") . '(?<run>(,\s+([0-9]{4,}(-\d+)?))+)' . '/';
		if (preg_match($pattern, $text, $m))
		{
			$run = $m['run'];
			$items = preg_split('/,\s+/', $run);
		
			foreach ($items as $item)
			{
				if (is_numeric($item))
				{
					$ids[] = $institutionCode . ' ' . $item;
				}
				else
				{
					if (preg_match('/^(?<start>\d+)\-(?<end>\d+)$/', $item, $mm))
					{
						$range = expand_range($mm['start'], $mm['end']);
						
						foreach ($range as $r)
						{
							$ids[] = $institutionCode . ' ' . $r;
						}
					}
				}
			}
			$matched = true;
			
		}
	}
	//echo "ids\n";
	//print_r($ids);
	
	return $ids;
}
	

//--------------------------------------------------------------------------------------------------
// Extract museum specimen code
function extract_specimen_codes($t)
{

	// Standard acronyms that have simple [Acronym] [number] specimen codes
	// (allowing for a prefix before [number]
	$acronyms = array(
		'ABTC','ADT-CRBMUV', 'AM', 'AM M', 'AM\-M\.', 'AMCC','AMNH','ANSP','ANWC','AMS','AMS\. [1|I]\.', 'ANSP','ASIZB','ASU',
		'BBM', 'BBM-BSIP', 'BBM-NG', 'BNHS','BPBM', 'BSIP', 'BYU',
		'CAS','CASENT','CASIZ', 'CAS-SU','CFBH','CM','CMK','CRBMUV','CWM',
		'DHMECN','DZUFMG',
		'fmnh', // lower case, OCR of SMALLCAPS comes out like this, see http://biostor.org/reference/65937
		'FMNH', 'FMNH no.',
		'HKU',
		'IBUNAM-EM','ICN','ICN-MHN-CR','ILPLA','INHS','IRSNB','IZUA',
		'JAC','JCV', 'JM',
		'KFBG','KU','KUHE', 'KUVP',
		'LACM','LSUMZ',		
		'MACN','MACN-Ict','MCP','MCNU', 'MCSN', 'MCZ','MFA-ZV-I','MG','MHNCI','MNCN','MHNG','MHNUC','MNRJ','MPEG','MRAC','MRT','MUJ','MUZUC','MVUP','MVZ','MZF','MZUC','MZUFV','MZUSP',
		'NHMW', 'NMNH', 'NRM','NSV','NT','NTM',
		'OMNH',
		'QCAZ','QM','QMJ',
		'RAN','RMNH','ROM',
		'SAIAB', 'SAM-A','SAMA','SIUC','SMNS', 'SU',
		'TNHC','THNHM',
		'UAZ', 'UCR','UCMVZ','UFMG','UMFS','UMMZ','UNT','USNM','USNM\.', 'USNMENT','USNM\sENT','UTA','UWBM',
		'WAM','WHT',
		'YPM',
		'ZFMK','ZIL','ZMA','ZMB','ZMH','ZMUC','ZRC','ZSI F','ZUFRJ');

	$specimens = array();
	$ids = array();
	
	
	//echo join("|", $acronyms) . "\n";
	
	$regexp = 
			'/
		(?<code>
		'
		. join("|", $acronyms)
//		. 'FMNH no\.'
		. '
		)
		\s*
		(:|_|\-)?
		(?<number>((?<prefix>(J|R|A[\.|\s]?|A\-))?[0-9]{3,}))
		
		(
			(\-|–|­|—)
			(?<end>[0-9]{2,})
		)?		
		
		/x';
		
		
	$regexp = str_replace("\n", "", $regexp);
	$regexp = str_replace("\t", "", $regexp);
		
	//echo $regexp . "\n";
	
	// Try and match typical code [A-Z] \d+, allowing for some quirks such as
	// letter prefixes for number, and support ranges
	if (preg_match_all(
/*		'/
		(?<code>
		'
		. join("|", $acronyms)
		. '
		)
		\s*
		(:|_|\-)?
		(?<number>((?<prefix>(J|R|A[\.|\s]?|A\-))?[0-9]{3,}))
		
		(
			(\-|–|­|—)
			(?<end>[0-9]{2,})
		)?		
		
		/x',  */
		$regexp,
		
		$t, $out, PREG_PATTERN_ORDER))
	{
		//print_r($out);
		$found = true;
		
		for ($i = 0; $i < count($out[0]); $i++)
		{
			$s = new stdClass;
			$s->code = $out['code'][$i];
			$s->prefix = $out['prefix'][$i];
			$s->number = $out['number'][$i];
			$s->end = $out['end'][$i];
			array_push($specimens, $s);
		}
	}
	
	// Special cases -------------------------------------------------------------------------------

	// ---------------------------------------------------------------------------------------------
	// BMNH, e.g. BMNH1947.2.26.89
	if (preg_match_all(
		'/
		(?<code>BM\s*((\(|\[)?NH(\)|\]))?)
		([N|n]o\.\s*)?
		(?<number>([0-9]{2,4}(\.[0-9]+)+) )
		
		(
			(\-|–|­|—)
			(?<end>[0-9]+)
		)?		
		
		/x',  
		
		$t, $out, PREG_PATTERN_ORDER))
	{
		//print_r($out);
		$found = true;
		
		for ($i = 0; $i < count($out[0]); $i++)
		{
			$s = new stdClass;
			$s->code = trim($out['code'][$i]);
			$s->prefix = '';
			$s->number = $out['number'][$i];
			$s->end = $out['end'][$i];
			array_push($specimens, $s);
		}
		
		//print_r($specimens);
		
	}
	
	// ---------------------------------------------------------------------------------------------
	// BMNH, e.g. BM(NH) 1981.5.26: 11-14
	if (preg_match_all(
		'/
		(?<code>BM\s*((\(|\[)?NH(\)|\]))?)
		\s*
		(?<number>([0-9]{2,4}(\.[0-9]+)+):\s+\d+(-\d+)?)
		/x',  
		
		$t, $out, PREG_PATTERN_ORDER))
	{
		//print_r($out);
		$found = true;
		
		for ($i = 0; $i < count($out[0]); $i++)
		{
			$s = new stdClass;
			$s->code = $out['code'][$i];
			$s->prefix = '';
			$s->number = $out['number'][$i];
			$s->number = str_replace("\n", '', $s->number);
			$s->end = '';
			array_push($specimens, $s);
		}
		
		//print_r($specimens);
		
	}	
		
	
	// ---------------------------------------------------------------------------------------------
	// MNHN
	if (preg_match_all(
		'/
		(?<code>MNHN)
		\s*
		(?<number>([0-9]{4}\.[0-9]+) )

		(
			(\-|–|­|—)
			(?<end>[0-9]+)
		)?		

		/x',  
		
		$t, $out, PREG_PATTERN_ORDER))
	{
		//print_r($out);
		$found = true;
		
		for ($i = 0; $i < count($out[0]); $i++)
		{
			$s = new stdClass;
			$s->code = $out['code'][$i];
			$s->prefix = '';
			$s->number = $out['number'][$i];
			$s->end = $out['end'][$i];
			array_push($specimens, $s);
		}
		
		//print_r($specimens);
		
	}	
	
	// ---------------------------------------------------------------------------------------------
	if (preg_match_all(
		'/
		(?<code>NCA|QVM|ZSM)
		\s*
		(?<number>([0-9]+(:|\/)[0-9]+))
		/x',  
		
		$t, $out, PREG_PATTERN_ORDER))
	{
		//print_r($out);
		$found = true;
		
		for ($i = 0; $i < count($out[0]); $i++)
		{
			$s = new stdClass;
			$s->code = $out['code'][$i];
			$s->number = $out['number'][$i];
			array_push($specimens, $s);
		}
		
		//print_r($specimens);
		
	}
	
	// ---------------------------------------------------------------------------------------------
	if (preg_match_all(
		'/
		(?<code>NHM)
		\s+
		(?<number>(R\.?[0-9]+))
		/x',  
		
		$t, $out, PREG_PATTERN_ORDER))
	{
		//print_r($out);
		$found = true;
		
		for ($i = 0; $i < count($out[0]); $i++)
		{
			$s = new stdClass;
			$s->code = $out['code'][$i];
			$s->number = $out['number'][$i];
			array_push($specimens, $s);
		}
		
		//print_r($specimens);
		
	}
	
	// ---------------------------------------------------------------------------------------------
	// ZFMK
	if (preg_match_all(
		'/
		(?<code>ZFMK)
		\s*
		(?<number>([0-9]+\.[0-9]+) )

		(
			(\-|–|­|—)
			(?<end>[0-9]+)
		)?		

		/x',  
		
		$t, $out, PREG_PATTERN_ORDER))
	{
		//print_r($out);
		$found = true;
		
		for ($i = 0; $i < count($out[0]); $i++)
		{
			$s = new stdClass;
			$s->code = $out['code'][$i];
			$s->prefix = '';
			$s->number = $out['number'][$i];
			$s->end = $out['end'][$i];
			array_push($specimens, $s);
		}
		
		//print_r($specimens);
		
	}	
	
	
	// ---------------------------------------------------------------------------------------------
	// MHNG n°18 11.089
	if (preg_match_all(
		'/
		(?<code>MHNG)
		\s+
		n°
		\s*
		(?<number>([0-9]+\s*)+\.([0-9]{3}))
		/x',  
		
		$t, $out, PREG_PATTERN_ORDER))
	{
		//print_r($out);
		$found = true;
		
		for ($i = 0; $i < count($out[0]); $i++)
		{
			$s = new stdClass;
			$s->code = $out['code'][$i];
			$s->prefix = '';
			$s->number = preg_replace('/\s+/', '', $out['number'][$i]);
			$s->end = '';
			array_push($specimens, $s);
		}
		
		//print_r($specimens);
		
	}	
	
	
	
	// ---------------------------------------------------------------------------------------------
	// Post process to handle lists of specimens
	foreach ($specimens as $z)
	{	
		// Fix any codes that seem broken
		if ($z->code == 'USNM ENT')
		{
			$z->code = 'USNMENT';
		}

		if ($z->code == 'USNM.')
		{
			$z->code = 'USNM';
		}

		if ($z->code == 'fmnh')
		{
			$z->code = 'FMNH';
		}

		if ($z->code == 'BSIP')
		{
			$z->code = 'BBM-BSIP';
		}
		
		if ($z->code == 'BM (NH)')
		{
			$z->code = 'BM(NH)';
		}
		
		if ($z->code == 'BM [NH]')
		{
			$z->code = 'BM(NH)';
		}
		if ($z->code == 'BM[NH]')
		{
			$z->code = 'BM(NH)';
		}
		
		if ($z->code == 'FMNH no.')
		{
			$z->code = 'FMNH';
		}
		


		if ($z->end == '')
		{
			$ids[] = $z->code . ' ' . $z->number;
		}		
		else
		{
			$range = expand_range($z->number, $z->end);
			foreach ($range as $r)
			{
				$ids[] = $z->code . ' ' . $r;
			}	
		}	
	}
	
	
	return $ids;
}

if (0)
{
	
	// test code
	$samples = array();
	$failed = array();
	$specimens = array();
	
	/*array_push($samples, 'spinosa: ECUADOR: PICHINCHA: USNM 288443: RÌo Blanco. LOS RÌOS: USNM 286741≠44: RÌo Palenque.');
	
	array_push($samples, 'BMNH1947.2.26.89');
	
	array_push($samples, 'Material examined. ≠ Holotype - male, 30.3 mm SVL, WHT 5862, Hunnasgiriya (Knuckles), elevation 1,100 m (07∫23\' N, 80∫41\' E), coll. 17 Oct.2003. Paratypes - females, 35.0 mm SVL, WHT 2477, Corbett\'s Gap (Knuckles), 1,245 m (07∫ 22\' N, 80∫ 51\' E) coll. 6 Jun.1999; 33.8 mm SVL, WHT 6124, Corbett\'s Gap (Knuckles), 1,245 m (07∫ 22\' N, 80∫ 51\' E) coll. 16 Jun.2004; males, 30.3 mm SVL, WHT 5868, Hunnasgiriya, same data as holotype, coll. 16 Oct.2003; 31.3 mm');
	*/
	/*
	array_push($samples,'Gephyromantis runewsweeki, spec. nov. Figs 1-4, 6
	Types. Holotype: ZSM 49/2005, collected by M. Vences, I. De la Riva, E. and T. Rajearison on 25 January 2004 at the top of Maharira mountain, Ranomafana National Park, south-eastern Madagascar (21∞20.053\' S, 47∞ 24.787\' E), ca. 1350 m above sea level. ≠ Paratype: MNCN 42085, adult male with same collecting data as holotype.');
	
	array_push($samples,'Figure 57. L. orarius sp. nov., male paratype ex QVM 23:17693.
	Mesal (left) and anterior (right) views of right gonopod telopodite.
	Dashed lines indicate course of prostatic groove; scale bar = 0.25 mm.
	Figure 59. L. otwayensis sp. nov., male paratype, NMV K-9619. Mesal');
	
	array_push($samples,'FIGURES 1≠6. Adults and male genitalia. 1, Schinia immaculata, male, Arizona, Coconino Co.
	Colorado River, Grand Canyon, river mile 166.5 L, USNMENT 00229965; 2, S biundulata, female,
	Nevada, Humboldt Co. Sulphur, USNMENT 00220807; 3, S. immaculata, male genitalia; 4, S.
	immaculata, aedoeagus; 5, S. biundulata, male genitalia; 6, S. biundulata, aedoeagus.
	Material Examined. PARATYPES (3∞): U.S.A.: ARIZONA: COCONINO CO. 1∞
	same data as holotype except: USNM ENT 00210120 (NAU); river mile 166.5 L, old high
	water, 36.2542 N, 112.8996 W, 14 Apr. 2003 (1∞), R. J. Delph, USNM ENT 00219965
	(USNM); river mile 202 R, new high water, 36.0526 N, 113.3489 W, 15 May 2001 (1∞), J.
	Rundall, USNM ENT 00210119 (NAU). Paratypes deposited in the National Museum of
	Natural History, Washington, DC (USNM) and Northern Arizona University, Flagstaff,
	AZ (NAU).');
	
	*/
	// exmaples
	
	/*array_push($samples, 'WHT 5868');
	array_push($samples, 'BMNH1947.2.26.89');
	array_push($samples, 'ZSM 49/2005');
	array_push($samples, 'MNCN 42085');
	array_push($samples, 'USNM ENT 00210120');
	array_push($samples, 'MCZ A-119850');
	array_push($samples, 'SAMA R37834');
	array_push($samples, 'NT R.18657');
	array_push($samples, 'QVM 23:16172');
	array_push($samples, 'WAM R166250'); */
	//array_push($samples, 'LSUMZ 81921–7');
	//array_push($samples, 'LSUMZ 81921–7');
	//array_push($samples, 'MNHN 2000.612-23');
	array_push($samples,'BMNH 1933.9.10.9–11');
	array_push($samples, 'AMS R 93465');
	array_push($samples, 'SAMAR20583');
	array_push($samples, 'TNHC63518');
	array_push($samples,'FIGURES 1≠6. Adults and male genitalia. 1, Schinia immaculata, male, Arizona, Coconino Co.
	Colorado River, Grand Canyon, river mile 166.5 L, USNMENT 00229965; 2, S biundulata, female,
	Nevada, Humboldt Co. Sulphur, USNMENT 00220807; 3, S. immaculata, male genitalia; 4, S.
	immaculata, aedoeagus; 5, S. biundulata, male genitalia; 6, S. biundulata, aedoeagus.
	Material Examined. PARATYPES (3∞): U.S.A.: ARIZONA: COCONINO CO. 1∞
	same data as holotype except: USNM ENT 00210120 (NAU); river mile 166.5 L, old high
	water, 36.2542 N, 112.8996 W, 14 Apr. 2003 (1∞), R. J. Delph, USNM ENT 00219965
	(USNM); river mile 202 R, new high water, 36.0526 N, 113.3489 W, 15 May 2001 (1∞), J.
	Rundall, USNM ENT 00210119 (NAU). Paratypes deposited in the National Museum of
	Natural History, Washington, DC (USNM) and Northern Arizona University, Flagstaff,
AZ (NAU).');

//array_push($samples, 'Material examined. ≠ Holotype - male, 30.3 mm SVL, WHT 5862, Hunnasgiriya (Knuckles), elevation 1,100 m (07∫23\' N, 80∫41\' E), coll. 17 Oct.2003. Paratypes - females, 35.0 mm SVL, WHT 2477, Corbett\'s Gap (Knuckles), 1,245 m (07∫ 22\' N, 80∫ 51\' E) coll. 6 Jun.1999; 33.8 mm SVL, WHT 6124, Corbett\'s Gap (Knuckles), 1,245 m (07∫ 22\' N, 80∫ 51\' E) coll. 16 Jun.2004; males, 30.3 mm SVL, WHT 5868, Hunnasgiriya, same data as holotype, coll. 16 Oct.2003; 31.3 mm');

	$samples = array();

	//$samples[] ="SÃO PAULO: 1. Teodoro Sampaio, (−22.52, −52.17), MZUSP 8885, 25819; 2. Estação Biológica de Boracéia, Salesópolis, (−23.65, −45.9), USNM 460569; 3. Parque Estadual da Serra do Mar, Núcleo Santa Virgínia, 10 km NW Ubatuba, (−23.36, −45.13), 850 m, NSV 160599. PARANÁ: 4. Parque Barigüi, Bairro Mercês, Curitiba, (−25.42, −49.30), 861 m, MHNCI 2599. SANTA CATARINA: 5. Ilha de Santa Catarina, (−27.6, −48.5), BMNH 50.7.8.24, 50.7.8.25, 7.1.1.174; 6. Serra do Tabuleiro, (−27.83, −48.78), JCV 28. RIO GRANDE DO SUL: 7. Parque Nacional dos Aparados da Serra, Cambará do Sul, (−29.25, −49.83), 800 m, MCNU 829; 8. Aratiba, (−27.27, −52.32), 420 m, MZUSP 33474 (holotype), MZUSP 33475, MCNU 826, 827, 831, 833–838, 840 (paratypes), MCNU 829. UNKNOWN LOCALITY: probably from the state of Minas Gerais, UFMG 3015.";
	
	$samples[] = "We employed DNA barcoding as a third source of standardized data for species identification. We sequenced two mitochondrial DNA barcode markers for amphibians, the 5’ end of the cytochrome oxidase I (COI) gene and a fragment of the ribosomal 16S gene, using published primers and protocols (Vences et al. 2005; Smith et al. 2008; Crawford et al. 2010). GenBank accession numbers for each gene (COI, 16S) for each Panamanian specimen are as follows: MVUP 2042 (JF769001, JF769004) and AJC 2067 (JF769000, JF769003). We also obtained sequence data from one E. planirostris from Havana, Cuba, deposited in the Museum of Natural History “Felipe Poey”, Havana, with specimen number MFP.11512 (JF769002, JF769005). Gene sequences and metadata were also deposited at Barcode of Life Data Systems (Ratnasingham & Hebert 2007) under project code “BSINV”. Species identification utilized character-based phylogenetic inference and genetic distances (Goldstein & DeSalle 2011), as well as qualitative observations of morphology and advertisement call.
We compared the 16S DNA data with 16 closely related sequences (Frost et al. 2006; Heinicke et al. 2007) from GenBank (Fig. 1). Note, specimen USNM 564984 is currently identified as P. casparii in GenBank EF493599, but was re-identified as P. planirostris in Heinicke et al. (2011). Excluding gapped sites, the alignment contained 518 base pairs (bp), of which 57 were parsimony-informative and 37 were singletons. Phylogenetic analysis of 16S data followed protocols in Crawford et al. (2010). Parsimony inference resulted in 4 shortest trees of 148 steps (not shown), with support measured by 2,000 boostrap pseudoreplicates. A maximum likelihood-based tree (-Ln score = 1520.37670) is shown in Fig 1.";

	$samples = array();
	$samples[] = "SAM-A15368 SM 18 1 $ CL 22,8 mm 

SAM-A15369 SM 33 1 $ CL 21,0 mm 

SAM-A 15370 SM 88 1 $ 1 $ CL 13,0 mm, 14,2 mm 

SAM-A15371 SM 111 1 S CL 18,4 mm 

SAM-A13195 off Natal, 30°30'S 31°45'E200 m 1 $ 

SAM-A13197 off Natal, 26°30'S 42°40'E 500 m 1 <? 

SAM-A13198 off Agulhas Bank, 37°10'S 21°50'E 500 m 1 <J 

SAM-A13236 off Natal, 31°44'S 44°35'E 500 m3^ 
";

/*
	$samples = array(
	"A female specimen (FMNH no. 405579) collected by H.B. Conover 
on 3 February 1927 at c. 1450 m from Tabora provides an additional locality"
);
*/

	$samples = array('MHNG n°18 11.089');

	$ok = 0;
	foreach ($samples as $str)
	{
		$s = extract_specimen_codes($str);
		$matched = count($s);
		
		if ($matched > 0)
		{
			$specimens = array_merge($specimens, $s);
			$ok++;
		}
		else
		{
			array_push($failed, $str);
		}
	}
	
	// report
	
	echo "--------------------------\n";
	echo count($samples) . ' samples, ' . (count($samples) - $ok) . ' failed' . "\n";
	print_r($failed);
	
	print_r($specimens);
	
	// Post process specimens
}



?>