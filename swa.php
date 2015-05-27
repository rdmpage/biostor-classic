<?php

/**
 * @file swa.php
 *
 * Smith-Waterman alignment algorithm for words
 *
 * See http://en.wikipedia.org/wiki/Smith-Waterman_algorithm for basic algorithm
 */

//--------------------------------------------------------------------------------------------------
/**
 * @brief Clean token 
 *
 * Remove terminal punctuation, such as full stops
 *
 * @param token Text token to be cleaned
 *
 * @return Cleaned token
 */
function clean_token($token)
{
	$token = preg_replace('/\.$/', '', $token);
	return $token;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Split string into array of tokens using whitespace as the delimiter
 *
 * @param str String to be tokenised
 *
 * @return Array of tokens
 */
function tokenise_string($str)
{
	return preg_split("/[\s]+/", $str);
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Align words in two strings using Smith-Waterman algorithm
 *
 * Strings are split into words, and the resulting arrays are aligned using Smith-Waterman algorithm
 * which finds a local alignment of the two strings. Aligning words rather than characters saves
 * memory
 *
 * @param str1 First string (haystack)
 * @param str2 First string (needle)
 * @param html Will contain the alignment between str1 and str2 in HTML format
 *
 * @return The score (0-1) of the alignment, where 1 is a perfect match between str2 and a subsequence of str1
 */
function smith_waterman ($str1, $str2, &$html)
{
	$score = 0.0;
	
	// Weights
	$match 		= 2;
	$mismatch 	= -1;
	$deletion 	= -1;
	$insertion 	=-1;
	
	// Tokenise input strings, and convert to lower case
	$X = tokenise_string($str1);
	$Y = tokenise_string($str2);
	
	// Lengths of strings
	$m = count($X);
	$n = count($Y);
	
	// Create and initialise matrix for dynamic programming
	$H = array();
	
	for ($i = 0; $i <= $m; $i++)
	{
		$H[$i][0] = 0;
	}
	for ($j = 0; $j <= $m; $j++)
	{
		$H[0][$j] = 0;
	}
	
	$max_i = 0;
	$max_j = 0;
	$max_H = 0;
	
	for ($i = 1; $i <= $m; $i++)
	{
		for ($j = 1; $j <= $n; $j++)
		{		
			$a = $H[$i-1][$j-1];
			
			$s1 = clean_token($X[$i-1]);
			$s2 = clean_token($Y[$j-1]);
			
			// Compute score of four possible situations (match, mismatch, deletion, insertion
			if (strcasecmp ($s1, $s2) == 0)
			{
				// Strings are identical
				$a += $match;
			}
			else
			{
				// Strings are different
				//$a -= levenshtein($X[$i-1], $Y[$i-1]); // allow approximate string match
				$a += $mismatch; // you're either the same or you're not
			}
		
			$b = $H[$i-1][$j] + $deletion;
			$c = $H[$i][$j-1] + $insertion;
			
			$H[$i][$j] = max(max($a,$b),$c);
						
			if ($H[$i][$j] > $max_H)
			{
				$max_H = $H[$i][$j];
				$max_i = $i;
				$max_j = $j;
			}
		}
	}
	
	// Best possible score is perfect alignment with no mismatches or gaps
	$maximum_possible_score = count($Y) * $match;
	$score = $max_H / $maximum_possible_score;
	
	//echo "<p>Score=$score</p>";
	
		
	// Traceback to recover alignment
	$alignment = array();
	
	$value = $H[$max_i][$max_j];
	$i = $max_i-1;
	$j = $max_j-1;
	while (($value != 0) && (($i != 0) && ($j != 0)))
	{
		//echo $H[$i][$j] . "\n";
		//echo $i . ',' . $j . "\n";
		//echo $X[$i] . '-' . $Y[$j] . "\n";
		//print_r($X);
		//print_r($Y);
		
		$s1 = clean_token($X[$i]);
		$s2 = clean_token($Y[$j]);
		
		if ($s2 != '')
		{
			array_unshift($alignment, 
				array(
					'pos' => $i, 
					'match' => ((strcasecmp($s1,$s2)==0) ? 1 : 0),
					'token' => $X[$i]
					)
				);
		}
			
		$up = $H[$i-1][$j];
		$left =  $H[$i][$j-1];
		$diag = $H[$i-1][$j-1];
	
		if ($up > $left)
		{
			if ($up > $diag)
			{
				$i -= 1;
			}
			else
			{
				$i -= 1;
				$j -= 1;
			}
		}
		else
		{
			if ($left > $diag)
			{
				$j -= 1;
			}
			else
			{
				$i -= 1;
				$j -= 1;
			}
		}
	}
	//echo $i . ',' . $j . "\n";
	//echo $X[$i] . '-' . $Y[$j] . "\n";
	
	// Store last token in alignment
	$s1 = clean_token($X[$i]);
	$s2 = clean_token($Y[$j]);
		
	array_unshift($alignment, 
		array(
			'pos' => $i, 
			'match' => ((strcasecmp($s1,$s2)==0) ? 1 : 0),
			'token' => $X[$i]
			)
		);

	// HTML snippet showing alignment
	
	// Local alignment
	$snippet = '';
	$last_pos = -1;
	foreach ($alignment as $a)
	{
		if ($a['pos'] != $last_pos)
		{
		
			if ($a['match'] == 1)
			{
				$snippet .= '<span style="color:black;font-weight:bold;background-color:yellow;">';
			}
			else
			{
				$snippet .= '<span style="color:rgb(128,128,128);font-weight:bold;background-color:yellow;">';
			}
			$snippet .= $a['token'] . ' ';//$Z[$a['pos']] . ' ';
		
			$snippet .= '</span>';
		}
		$last_pos = $a['pos'];
		
	}	
	// Embed this in haystack string
	
	// Before alignment
	$start_pos = $alignment[0]['pos'] - 1;
	$prefix_start = max(0, $start_pos - 10);
	$prefix = '';
	while ($start_pos > $prefix_start)
	{
		$prefix = $X[$start_pos] . ' ' . $prefix;
		$start_pos--;
	}
	if ($start_pos > 0) $prefix = '…' . $prefix;

	// After alignment	
	$end_pos = $alignment[count($alignment) - 1]['pos'] + 1;
	$suffix_end = min(count($X), $end_pos + 10);
	$suffix = '';
	while ($end_pos < $suffix_end)
	{
		$suffix .= ' ' . $X[$end_pos];
		$end_pos++;
	}
	if ($end_pos < count($X)) $suffix .= '…';

	$html = $prefix . $snippet . $suffix;	
	
	return $score;
}


// test cases

if (0)
{

$str1='general notes. 199 pagophila eburnea versus pagophila alba. the name of the ivory gull has recently been changed from pagophila alba (gunnerus) to pagophila eburnea (phipps) because the original description of pagophila alba (larus albus gunnerus, in leem\'s beskr. finm. lapp., 1767, p. 285; northern norway) was not considered identifiable (c/. hartert et al., list british birds, 1912, p. 203; committee, british ornith. union, list british birds, 1915, p. 394). the specific name alba was originally applied to the ivory gull by stejneger (proc. u. s. nat. mus., vi, june 13, 1882, p. 39), and has since been generally accepted by american ornithologists. an examination of the original description seems to leave little doubt that the name is properly applicable to the species in question, since this is the only gull with normally pure white plumage living in the far northern regions. the bird should, therefore, continue to be known as pagophila alba. for the benefit of those who may not have access to the rare book in which larus albus gunnerus appeared, we hereby transcribe the original description: "prseter laros jam enumeratos apud nos adhuc datur 1) larus albus, norlandis vald-maase dictus, qui toto interdum corpore albus esse & laro cano vel & fusco magnitudine convenire perhibetur. v. dn. matthias brunn, vefsensium nunc pastor, se hunc in prasfectura vosteraalen norlandiae nonnunquam vidisse mini persvasit. plur. v. dn. buschmann, praepositus helgelandise & paroecise naesne pastor meritissimus, me itidem certiorem fecit, eundem larum in helgelandia nostra vulgo satis notum esse, ut ab omnibus reliquis laris ante memoratis distinctum, licet ipse eum nondum nisi eminus viderit. ni valde fallor, larus hie habendus est idem ac senator martensii, qui toto corpore albus, rostro & pedibus nigris describitur esse. sed, quid de nigredine rostri judicem, nescio, quum hie color in rostris reliquorum larorum, e. g. marinorum, hyberboreorum, canorum & tridactylorum jam adultorum prorsus evanescat. characterem martensii, quern addit, nimirum: tridactylus, taceo; ilium tamen ob oculos habeant, qui occasionem larum nostrum album examinandi nacti fuerint." — harry c. oberholser. some necessary changes in crustacean nomenclature. in 1915 mr. l. a. borradaile (ann. and mag. nat. hist. [8], vol. 15, p. 207) distributed the species of periclimenes among four subgenera which he called ensiger, corniger, cristiger and falciger. the type of the genus periclimenes, p. insignis costa, 1844= alpheus scriptus risso, 1826, falls in the subgenus cristiger, which should therefore be known as the subgenus periclimenes. the name corniger has previously been used once in fishes and twice in crustacea; it may be replaced by the name laomenes, nom.no v. the name falciger has been previously used in the coleoptera; it may be replaced by cuapetes, nom. nov. — austin h. clark. ';
$str2='some necessary changes in crustacean nomenclature';

//$str1='330 Prof. F. M\'Coy on the Classification of ■ That there are no errors in these observations would be an undue assumption ; for who, on such subjects and in the examination of these minute objects, can hope to escape from occasional error ? I invite malacologists to offer their corrections, if I have diflPered on insufficient grounds from so eminent a naturalist as M. Deshayes ; and I conclude with the evocation, "Si quid novisti rectius istis, Candidas impsrti." I am, Gentlemen, your most obedient servant, William Clark. P.S. I beg that the notice relative to the Venus orbiculata of Montagu, in my paper on the genus Cacum, in the \' Annals \' for August, may be considered as cancelled. XXXIV. — On the Classification of some British Fossil Crustacea, with Notices of neiv Forms in the University Collection at Cambridge. By Frederick M\'Coy, Professor of Geology and Mineralogy in Queen^s College, Belfast. [Continued from p. 179.] Enoploclytia (M\'Coy), n. g. Etym. evoTrXc?, armatus, and Chjtia. Gen. Char. Carapace fusiform, back rounded, sides convex, gently compressed, posterior end slightly narrowed and deeply Enoploclijtia. notched for the insertion of the abdomen, much contracted anteriorly, the front extended into a long, sharp-pointed depressed rostrum, the sides of which are armed with three or four strong spines ; one strong spine over the upper external angle of the orbit; eyes on short, thick peduncles; nuchal ';

//$str2='On the classification of some British fossil Crustacea, with notices on the new forms in the University collection at Cambridge';

//$str1='424 Bulletin of Zoological Nomenclature STENOBHYNCHUS LAIHARCK, 1818 (CRUSTACEA, DECAPODA): PROPOSED VALIDATION UNDER THE PLENARY POWERS WITH DESIGNATION OF CANCER SETICOBNIS HERBST, 1788, AS TYPESPECIES. Z.N.(S.) 751 By John S. Garth (Allan Hancock Foundation, Los Angeles, U.S.A.) and Lipke B. Holthiiis (Bijksmuseum van Natuurlijke Historic, Leiden, The Netherlands) The generic name Stenorynchus Lamarck at present is in general use for a genus of spider crabs which inhabits the tropical American and West African seas. From a nomenclatorial viewpoint, however, this name for two reasons cannot be used for the genus in question. In order to avoid unnecessary confusion in the nomenclature of this genus, the International Commission on Zoological Nomenclatiu-e is hereby asked to make use of its plenary powers so as to make possible the continued use of the generic name Stenorynchus in the sense adopted by modern authors. The following are the original references to the generic names dealt with in the present proposal : Inachus Weber, 1795, Nomencl. entomol.: 93 (type-species, selected by MUne Edwards (H.), 1837 (Cuvier\'s Begne Anim. ed. 4 (= Discip. ed.) 18 : pi. 34 fig. 2): Cancer Scorpio Fabricius, 1779, Beise Norwegen: 345 (= Cancer dorsettensis Pennant, 1777, Brit. Zool. (ed. 4) 4 : 8)). Gender: masculine. Leptopodia [Leach, 1814], in Brewster\'s Edinb. Encycl. 7 : 431 (type-species, by present selection: Cancer phalangium Fabricius, 1775, Syst. Ent.: 408). Gender: feminine. Macropodia [Leach, 1814], in Brewster\'s Edinb. Encycl. 7: 395 (tj^e-species, by monotypy: Cancer longirostris Fabricius, 1775, Syst. Ent.: 408). Gender: feminine. Macropus Latreille, [1802-1803], Hist. nat. Crust. Ins. 3 : 27 (tjrpe-species, by monotypy: Cancer phalangium Fabricius, 1775, Syst. Ent.: 408). Gender: masculine. Paxiolus Leach, 1815, Zool. Miscell. 2 : 19 (type-species, by monotypy: Padolus boscii Leach, 1815, Zool. Miscell. 2 : 20). Gender: masculine. Stenorynchus Lamarck, 1818, Hist. nat. A7iim. sans Vetiebr. 5 : 236 (typespecies, selected b}^ ]\Iilne Edwards (H.), 1837 (Cuvier\'s Begne Anim. ed. 4 (= Discip. ed.) 18 : pi. 35 fig. 3) : Cancer phalangium Fabricius, 1775, Syst. Ent. : 408). Gender: masculine. The genus which at present is generally named Stenorynchus Lamarck contains two species : Cancer seticornis Herbst, 1788, from the East coast of America and the West coast of Africa, and Leptopodia debilis Smith (S. I.), 1871, from the West coast of America. Until 1897 this genus was generally named Leptopodia Leach, but Rathbun (1897, Proc. biol. Soc. Washington 11 : 155) pointed out that, since the original description of Leptopodia included neither Cancer seticornis nor Leptopodia debilis, the name Leptopodia cannot be used for the genus. Rathbun (1897 : 158) further concluded that the name Stenorynchus Lamarck, 1818, was available for the genus, it bemg a name given to two species, Bull. zool. Xomencl., Vol. 20, Part 6. December 1963. ';

//$str2='Stenorhynchus Lamarck, 1818 (Crustacea, Decapoda): proposed validation under the plenary powers with designation of Cancer seticornis herbst, 1788, as type-species. Z.N.(S.) 751';


//$str1 = 'so, This is a test case of my code';
//$str2 = 'This is another test case';


$str1='424 Bulletin of Zoological Nomenclature STENOBHYNCHUS LAIHARCK, 1818 (CRUSTACEA, DECAPODA): PROPOSED VALIDATION UNDER THE PLENARY POWERS WITH DESIGNATION OF CANCER SETICOBNIS HERBST, 1788, AS TYPESPECIES. Z.N.(S.) 751 By John S. Garth (Allan Hancock Foundation, Los Angeles, U.S.A.) and Lipke B. Holthiiis (Bijksmuseum van Natuurlijke Historic, Leiden, The Netherlands) The generic name Stenorynchus Lamarck at present is in general use for a genus of spider crabs which inhabits the tropical American and West African seas. From a nomenclatorial viewpoint, however, this name for two reasons cannot be used for the genus in question. In order to avoid unnecessary confusion in the nomenclature of this genus, the International Commission on Zoological Nomenclatiu-e is hereby asked to make use of its plenary powers so as to make possible the continued use of the generic name Stenorynchus in the sense adopted by modern authors. The following are the original references to the generic names dealt with in the present proposal : Inachus Weber, 1795, Nomencl. entomol.: 93 (type-species, selected by MUne Edwards (H.), 1837 (Cuvier\'s Begne Anim. ed. 4 (= Discip. ed.) 18 : pi. 34 fig. 2): Cancer Scorpio Fabricius, 1779, Beise Norwegen: 345 (= Cancer dorsettensis Pennant, 1777, Brit. Zool. (ed. 4) 4 : 8)). Gender: masculine. Leptopodia [Leach, 1814], in Brewster\'s Edinb. Encycl. 7 : 431 (type-species, by present selection: Cancer phalangium Fabricius, 1775, Syst. Ent.: 408). Gender: feminine. Macropodia [Leach, 1814], in Brewster\'s Edinb. Encycl. 7: 395 (tj^e-species, by monotypy: Cancer longirostris Fabricius, 1775, Syst. Ent.: 408). Gender: feminine. Macropus Latreille, [1802-1803], Hist. nat. Crust. Ins. 3 : 27 (tjrpe-species, by monotypy: Cancer phalangium Fabricius, 1775, Syst. Ent.: 408). Gender: masculine. Paxiolus Leach, 1815, Zool. Miscell. 2 : 19 (type-species, by monotypy: Padolus boscii Leach, 1815, Zool. Miscell. 2 : 20). Gender: masculine. Stenorynchus Lamarck, 1818, Hist. nat. A7iim. sans Vetiebr. 5 : 236 (typespecies, selected b}^ ]\Iilne Edwards (H.), 1837 (Cuvier\'s Begne Anim. ed. 4 (= Discip. ed.) 18 : pi. 35 fig. 3) : Cancer phalangium Fabricius, 1775, Syst. Ent. : 408). Gender: masculine. The genus which at present is generally named Stenorynchus Lamarck contains two species : Cancer seticornis Herbst, 1788, from the East coast of America and the West coast of Africa, and Leptopodia debilis Smith (S. I.), 1871, from the West coast of America. Until 1897 this genus was generally named Leptopodia Leach, but Rathbun (1897, Proc. biol. Soc. Washington 11 : 155) pointed out that, since the original description of Leptopodia included neither Cancer seticornis nor Leptopodia debilis, the name Leptopodia cannot be used for the genus. Rathbun (1897 : 158) further concluded that the name Stenorynchus Lamarck, 1818, was available for the genus, it bemg a name given to two species, Bull. zool. Xomencl., Vol. 20, Part 6. December 1963. ';

$str2='Stenorhynchus Lamarck, 1818 (Crustacea, Decapoda): proposed validation under the plenary powers with designation of Cancer seticornis herbst, 1788, as type-species. Z.N.(S.) 751';

//$str1 = '92 Bulletin of Zoological Nomenclature 44(2) June 1 987 Case 2567 Callianidea H. Milne Edwards, 1837 (Crustacea, Decapoda): proposed conservation K. Sakai Laboratory of Crustacea, Shikoku Women\'s University, 771-11 Tokushima, Ohjincho-Furukawa , Japan L. B. Holthuis Rijksmuseum van Natuurlijke Historic, Post bus 9517, 2300 RA Leiden, The Netherlands Abstract. The purpose of this application is the conservation of the name Callianidea H. Milne Edwards. 1 837 for a cosmopolitan mud shrimp genus. It is threatened by Isea Guerin-Meneville, 1 832. not used as a valid name since its inception because it was long regarded as a homonym of an older name, Isaea (an amphipod genus). 1. The name Isaea H. Milne Edwards, 1830 (p. 380) was first established for an amphipod genus with the single included species Isaea montagui Milne Edwards, 1830. 2. In 1832, Guerin-Meneville (p. 299) proposed the name Isea for a monotypic decapod genus containing the new species Isea elongata. 3. H. Milne Edwards (1837, p. 321) considered Isaea and Isea to be homonyms, and replaced the latter by the name Callianisea. In the same paper Milne Edwards described the new genus Callianidea with the single included species typa, and remarked under Callianisea that Guerin\'s material was in very poor condition and that therefore the supposed differences between Callianidea and Callianisea might prove to be non-existent. Later authors have shared this view and synonymised the two genera. 4. The first author to do this was Guerin-Meneville himself ( 1 856, p. xviii), who cited ■Callianidea.-Edw., Crust.. II, 319 (1837).— Sin. Isea Guer. Ann. Sac. ent., t. I, p. 30 ( 1 832) - Callianisea, Edw., Crust., II, 32 1 ( 1 837)\'. The name Callianidea was preferred over Callianisea by Guerin-Meneville and all later authors, probably because (1) it had \'page priority\' (being mentioned 2 pages earlier), and (2) Callianidea was based on complete material while the type material of Callianisea was in a very poor condition when described, so there was doubt about the identity of the species. GuerinMeneville\'s (1856) action was that of the first reviser (Art. 24) and it fixed the precedence oi Callianidea over Callianisea, the two names having been published on the same date. 5. A further replacement name for Isea Guerin-Meneville, 1832 was proposed by Dana (1852, p. 11), who suggested the name Callisea because Callianisea \'is so near Callianassa and Callianidea. a contraction to Callisea would be preferable\'. Neither Callianisea nor Callisea found acceptance by zoologists. ';
//$str2 = 'Case 2567. Callianidea H. Milne Edwards, 1837 (Crustacea, Decapoda): proposed conservation';

//$str1 = 'Bulletin of Zoological Nomenclature 49(3) September 1992 187 Case 2827 Gebia major capensis Krauss, 1843 (currently Upogebia capensis; Crustacea, Decapoda): proposed replacement of neotype, so conserving the usage of capensis and also that of G. africana Ortmann, 1894 (currently Upogebia africana) N. Ngoc-Ho Laboratoire de Zoologie ( Arthropodes) , Museum National d\'Histoire Naturelle, 61 rue de Buffon, 75231 Paris, France Gary C.B. Poore Department of Crustacea, Museum of Victoria, Swanston Street, Melbourne, Victoria 3000, Australia Abstract. The purpose of this appHcation is to conserve the accustomed usage of the specific names of two South African species of prawns: Upogebia capensis (Krauss, 1843) and U. africana (Ortmann, 1894). The latter species is commonly known as the mud-prawn or mud-shrimp. It is proposed to designate a replacement neotype for capensis from material of the species as presently understood; the previously designated neotype is a specimen of africana. 1. Three species of Gebia Leach, 1815 (p. 342; family upogebiidae) were described from South Africa. Gebia major var. capensis Krauss, 1843 (p. 54) was originally described as a variety of Gebia major de Haan, [1841] (pi. 35, fig. 7; text (p. 165) published in [ 1 849]; see Sherborn & Jentink ( 1 895, p. 1 50) and Holthuis ( 1 953. p. 37) for the dates of publication). The type material from Table Bay is now lost. The original description was short and by modern standards very incomplete and cannot be definitely reconciled with any single species known today. G. subspinosa Stimpson, 1860 (p. 22) was described from Simon\'s Bay; the fate of its type material is unknown. G. africana Ortmann, 1894 (p. 22, pi. 2. fig. 4) was described from Port Elizabeth. The holotype of this species is in the Zoological Museum, Strasbourg; it is a male without its abdomen (cephalothorax length 19.5 mm). Although in rather poor condition, it still shows the main characteristics of the species. 2 . Since 1 9 1 all three species have been referred to the genus Upogebia Leach, [1814] (pp. 386, 400; see Rathbun, 1897, p. 154, footnote for the date of publication). Until 1947 there was confusion between the three taxa and usually only one nominal species, U. capensis, was recognised (see, for example, Stebbing, 1900. p. 45; Stebbing. 1910; Balss, 1916, p. 34; Lenz & Strunck, 1914. p. 291; de Man, 1927, pp. 32 34; de Man. 1928, pp. 37, 41, 51). Barnard (1947, pp. 380, 381 ; 1950. pp. 514^520. fig. 96) revised the South African species of Upogebia and concluded that two species were involved: U. capensis (Krauss), characterised by a subdistal spine on the upper border of the merus of pereopod 1 and coxal spines on pereopods 1-3. and U. africana (Ortmann).';

//$str2='Gebia major capensis Krauss, 1843 (currently Upogebia capensis; Crustacea, Decapoda): proposed replacement of neotype, so conserving the usage of capensis and also that of G. africana Ortmann, 1894 (currently Upogebia africana)';


$html = '';
$score = smith_waterman($str1, $str2, $html);

echo $html;

}

?>