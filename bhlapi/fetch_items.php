<?php

require_once('../config.inc.php');
require_once('../lib.php');

// fetch one title
$TitleID = 40214;

$want = array();

// Optional list of just the items we want
$want = array(102858, 103982,
103976,
103983,
103978,
103977,
103975);

$want = array(103975,103977);

$TitleID=3622;
$want=array(110822);

$TitleID=2192;
$want=array(102952);

$TitleID=2804;
$want=array();






/*
$TitleID=6525;
$want=array(109055,
109056,
109057,
109058,
109060,
109062,
109063,
109066,
109067,
109068,
109114,
109150,
109151,
109152,
109153,
109163,
109166,
109203,
109566,

110726,
110727,
110718,
110770
);
*/

/*
$TitleID=8070;
$want=array(100655);

$TitleID=42670;
$want=array();

*/

$TitleID = 15774;
$want = array(102950,
107518,
102949,
102947,
102946,
102945,
102944);



$TitleID=46200;
$want=array();

$TitleID=9586;
$want=array(39564);

$TitleID=11325;
$want=array();

$TitleID=2356;
$want=array(55164);

$TitleID=2680;
$want=array(109365,
109442,
109446,
109510,
109570,
109445
);

$want = array(21557,21356);

$want = array(21356);

// TvE
$TitleID=10088;
$want=array(105919);
$want=array(105942);

// Novitates
$TitleID=3882;
$want=array();

$djvu_string = '';

$Titles = array(3066,2977,5133,7188,7192,3037,3082,2666,3529,3410,3368,3500,2689,3438,3580,3855,3421,5080,2708,3376,3395,5137,2930,3176,2808,3097,3020,3131,5644,5642,3127,3167,2849,2893,3329,3483,3403,3400,3408,2944,3190,3122,2844,3085,2871,2851,3000,3029,2823,2932,5578,3206,2966,3187,3002,2857,3044,2841,2951,2982,3181,3129,2801,2872,3125,3209,3042,2843,3109,3137,3126,3099,3189,2912,3205,2854,2865,3051,3551,3544,2692,3504,3128,3521,3409,3152,3265,3574,3370,3335,3533,3844,3351,2890,3336,3456,3526,5582,5359,3458,3146,3415,3419,3123,3148,2669,3478,3259,3452,3369,3276,3440,3555,3256,2653,2645,2654,2616,2634,2551,2588,2625,2563,2657,2580,2646,2548,2574,2609,2637,7139,7176,3816,5576,5575,2597,2556,2632,2576,5561,5564,5562,2596,2647,5565,5583,5573,5574,2585,2566,3235,2794,3217,2782,2723,3011,3201,3052,7186,7204,2546,3072,5580,5577,5579,3195,2827,2726,5570,2805,2891,2847,2724,2869,962,963,2815,2975,3210,2811,2959,2985,3200,2545,2728,2955,2980,2887,5641,2829,2842,2824,3084,3030,3212,3241,3038,2960,2965,2830,2798,3192,3007,2810,3074,3832,3197,3033,2974,2886,3171,2903,2562,7175,3045,3777,2994,3132,2973,3213,3089,2910,3001,3221,3223,3215,2908,3055,2870,3013,3225,3222,3238,2967,3040,2807,3096,3056,3026,2989,3812,2962,3008,2946,3100,2914,3237,3101,3012,2923,2789,2729,3069,3196,2727,2969,3248,3107,3828,2963,2790,3068,2913,3054,3824,3249,3239,3823,3113,2780,3229,2875,2993,3232,2984,3019,3835,2812,3014,2892,3133,3848,2915,3839,3071,3825,3801,3057,2862,3009,2778,3010,3841,3005,3245,3591,3593,3592,3594,2894,2995,2926,3043,2881,3098,3070,2898,3143,2725,3829,2896,3218,3060,2866,2968,2956,2924,3815,2987,2873,7174,2531,2533,7165,2584,2639,2610,7164,2661,2532,2592,7166,3799,2578,2640,2642,3842,5134,2983,2821,3075,2964,3199,3050,2703,2953,2900,3095,5127,3519,5085,3781,3557,3486,3814,3575,3791,5082,2920,5132,5569,5568,5566,5567,3247,2779,3244,2816,3094,2846,3228,2991,3821,2784,3226,2917,5076,3117,5136,5571,2730,3108,2814,3138,2831,2905,2918,3854,3193,2906,2864,2876,2858,3172,2817,3006,2802,3214,2992,3194,3073,2990,3211,3036,2879,2859,2825,2897,2947,3041,2863,2998,3017,3031,3233,3216,2834,3076,3027,3243,3246,3053,2878,2813,2797,3062,3034,7202,3834,3389,2717,2701,4818,3475,3323,3810,3357,3547,3803,3788,3792,2716,3795,3794,5280,3506,3417,3534,7187,3482,3454,3300,3787,3809,3372,3535,3562,3258,3365,2685,2697,3308,3373,3385,3294,3427,3517,3811,3536,3344,3398,3560,3531,2611,5079,6731,2941,2832,3106,3021,3253,3840,3236,4957,2979,3186,2868,2850,2888,3015,3204,3780,2970,2826,3180,3779,3763,3849,3254,3170,3124,2880,3178,2954,2852,3046,2907,2884,3105,2901,3202,2928,3227,3028,3800,3806,3182,3063,2919,3083,3804,2806,3047,3079,2935,2793,3169,2899,2999,2929,2839,3111,3104,2996,3077,3086,3147,2792,2997,3833,2904,2961,5138,3081,2939,5155,3184,3039,7189,3018,2822,2934,3067,2818,2800,2949,3252,5581,3065,2828,2937,3024,3003,3087,3175,2981,3103,2921,2848,2940,3165,2988,3853,3091,4754,2796,3224,3016,2819,2978,3173,2638,2595,2534,2558,2572,2589,2603,3761,2539,2581,2557,2583,2601,2799,2909,3048,7200,3136,2860,5131,5154,3121,3242,3058,3135,3240,3025,2856,903,5572,2544,3359,5433,3278,3554,5422,5336,5287,3272,3145,3159,3269,2713,3305,5454,5429,2631,2633,2554,2553,2535);


$Titles=array(42310);

$Titles=array(42540);
//$want=array(93398);

$Titles=array(10603);

$Titles=array(3179);

$Titles=array(7383);

$Titles=array(10088);
$want=array(40981);

// Proc USNM
$Titles=array(7519);
$want=array();

// Bull Zool Soc FR

$Titles=array(7415);
$want=array();

$Titles=array(49174);
$want=array();

$Titles=array(11103);
$want=array();

// Ann Ent Soc Fr
$Titles=array(8188);
$want=array();

// D E Z.
$Titles=array(48608);
$want=array();

// Tijd Ned Dierk Ver
$Titles=array(8982);
$want=array();

// Entomological News
$Titles=array(2356);
$want=array();

$Titles=array(2359);
$want=array();

// Comptes rendus des séances de la Société de biologie et de ses filiales
$Titles=array(8070);
$want=array();

$Titles =array(15534);
$want=array();

// Bull MCZ
$Titles=array(2803);
$want=array();

// Tulane studies in zoology and botany
$Titles=array(5361);
$want=array();

$Titles=array(48421);
$want=array();

//Bulletin of the Southern California Academy of Sciences
$Titles=array(4949);
$want=array();

//The University of Kansas science bulletin.
$Titles=array(3179);
$want=array();

// Ann Soc ent Belge
$Titles=array(11933);
$want=array();


// Rec Ind Mus
$Titles=array(10294);
$want=array();

// Transaction of Linnaen Society series 2
$Titles=array(51416);
$want=array();

// B Z N
$Titles = array(51603);
$want=array();

// Insecutor inscitiae menstruus
$Titles=array(8145);
$want=array();

// Proc Zool Soc Lond
$Titles=array(44963);
$want=array();

// Revision of the caddisfly genus Psilotreta (Trichoptera: Odontoceridae) 
$Titles=array(52238);
$want=array();

$Titles=array(52368);
$want=array();

$Titles=array(2202);
$want=array();


// Revue suisse de zoologie
$Titles=array(8981);
$want=array();

// Bulletin du Muséum national d'histoire naturelle
$Titles=array(5943);
$want=array();

$Titles=array(49359);
$want=array();

// Proceedings of the California Academy of Sciences, 4th series
$Titles=array(3943);
$want=array();

// Bulletin of entomological research
$Titles=array(10305);
$want=array();

// Bulletin de la Société philomathique de Paris
$Titles=array(9580);
$want=array();

$Titles=array(51416);
$want=array();

$Titles=array(6928);
$want=array();

// Discovery Reports
$Titles=array(6168);
$want=array();


$Titles=array(8128);
$want=array();

$Titles=array(15727);
$want=array(84911);


$Titles=array(45493);
$want=array();

$Titles=array(46204);
$want=array();

$Titles=array(52289);
$want=array();

// Notes from the Leyden Museum
$Titles=array(8740);
$want=array();

// Tulane
$Titles=array(3119);
$want=array();

// Rec Ind Mus 1922 vol. 24
$Titles=array(53477);
$want=array();

// Mem Indian Museum
$Titles=array(52566);
$want=array();

// Nouvelles archives du Muséum d'histoire naturelle
$Titles=array(52015);
$want=array();


// Revue d'entomologie
$Titles=array(10428);
$want=array();

// Archiv für Naturgeschichte
$Titles=array(6638);
$want=array();

//Archiv für Naturgeschichte. Abteilung B.
//$Titles=array(12937);
//$want=array();

//Archiv für Naturgeschichte. Abteilung A.
//$Titles=array(12938);
//$want=array();

$Titles =array(51603);
$want=array(44798);

$Titles =array(8942);
$want=array();

$Titles =array(3179);
$want=array(40698);

// Annali del Museo civico di storia naturale di Genova
$Titles =array(7929);
$want=array(33428);

// Mitteilungen der Münchner Entomologischen Gesellschaft
$Titles = array(15739);
$want=array();

// Archivos do Museu Nacional do Rio de Janeiro
$Titles = array(6524);
$want=array();

// Bull Z Nom
$Titles=array(51603);
$want=array(44292);

$Titles = array(50753);
$want=array();

// Arkiv
$Titles = array(6919);
$want=array();

// 	 Journal of shellfish research
$Titles = array(2179);
$want=array();

// Transactions of the Entomological Society of London
$Titles = array(11516);
$want=array();

$Titles =array(2510);
$want=array(89742);

// Transactions of the San Diego Society of Natural History
$Titles=array(3144);
$want=array();

$Titles=array(16284);
$want=array();

$Titles=array(16268);
$want=array();

$Titles=array(4050);
$want=array(55046,55069,55047,25735);

$Titles = array(13264);
$want=array();

// Bullettino della Società entomologica italiana
$Titles =array(9612);
$want=array(39875);

// Entomological News
$Titles =array(2356);
$want=array(113866,113867,113865);

// Verhandlungen der Naturforschenden Gesellschaft in Basel
$Titles =array(46540);
$want=array(106380);

// Great Basin Naturalist
$Titles=array(7928);
$want=array();

// Occasional papers of the California Academy of Sciences
$Titles = array(7410);
$want=array();

$Titles = array(3179);
$want=array(25828);

// Zool Jahr Ab Syst
$Titles = array(8980);
$want=array();

// Terra Nova
$Titles = array(42665);
$want=array();
$Titles = array(18281);
$want=array();

$Titles=array(7928);
$want=array(33366);

$Titles=array(7922);
$want=array();

//$Titles=array(14107);
//$want=array();

$Titles=array(7933);
$want = array(63349);

// American Journal of Science
$Titles=array(14965);
$want = array(113468);

$Titles=array(14924);
$want = array();

// Discovery Reports (one set)
$Titles=array(15981);
$want = array();

// Journal of the College of Agriculture
$Titles=array(8662);
$want = array();

// Papers and proceedings of the Royal Society of Tasmania
$Titles=array(9494);
$want = array();

// Liangqi baxing dongwu yanjiu = Acta herpetologica Sinica
$Titles=array(53832);
$want = array();

$Titles=array(53833);
$want = array();

$Titles=array(3141);
$want = array();

// Annali del Museo civico di storia naturale di Genova
$Titles =array(7929);
$want=array();

// Memoirs of the Queensland Museum
$Titles=array(12912);
$want=array();

// Comptes rendus des séances de la Société de biologie et de ses filiales
$Titles=array(8070);
$want=array(108557);

// Bericht über die Senckenbergische Naturforschende Gesellschaft in Frankfurt am Main
$Titles=array(8745);
$want=array(33793);

// Bollettino dei musei di zoologia ed anatomia comparata della R. Università di Torino
$Titles=array(10776);
$want=array();


$Titles=array(6335);
$want=array();



$Titles = array(50753);
$want=array();

// Proc USNM
$Titles=array(7519);
$want=array(32319);

$Titles=array(2744);
$want=array();

$Titles = array(49442);
$want=array(104470);

$Titles = array(48522);
$want=array();

$Titles = array(4252);
$want=array();

$Titles = array(46202);
$want=array();

$Titles=array(10241);
$want=array();

// Proceedings of the New England Zoölogical Club.
$Titles=array(10605);
$want=array();

// Jahrbuch der Hamburgischen Wissenschaftlichen Anstalten
$Titles=array(9594);
$want=array();

// Entomologische Blätter
$Titles=array(50899);
$want=array();

// Memoirs of the American Entomological Society
$Titles=array(6193);
$want=array();

// Annali del Museo civico di storia naturale Giacomo Doria
$Titles=array(43408);
$want=array();

// Verhandlungen der Naturforschenden Gesellschaft in Basel.
$Titles = array(46540);
$want=array();

// Spolia zeylanica
$Titles = array(10229);
$want=array();

// Spolia zeylanica
$Titles = array(10229);
$want=array();

// Journal of the Asiatic Society of Bengal
$Titles = array(51678);
$want=array();

$Titles = array(13353);
$want = array(49895);

// Proceedings of the Malacological Society of London
$Titles = array(15224);
$want=array();

// Archives de zoologie expérimentale et générale.
$Titles = array(5559);
$want=array(27784);

// Pomona College journal of entomology
$Titles = array(8154);
$want=array();


$Titles = array(7519);
$want=array(53452);

//Bulletin of the British Ornithologists' Club.
$Titles = array(46639);
$want=array();

$Titles=array(43431);
$want=array();

// Arbeiten aus dem Zoologischen Instituten der Universität Wien und der Zoologischen Station in Triest
$Titles=array(6106);
$want=array();

// Decapod crustacea of Bermuda
$Titles=array(23648);
$want=array(64731);

$Titles=array(48602);
$want=array();

// Biologia Centrali-Americana
$Titles=array(730);
$want=array();


$Titles = array(17344);
$want=array();

$Titles = array(20608);
$want=array();

$Titles = array(2087);
$want=array();

$Titles = array(44766);
$want=array();

$Titles = array(11687);
$want=array();

$Titles=array(1730);
$want=array();

// Archives de zoologie expérimentale et générale.
$Titles = array(5559);
$want=array();

// Boletin de la Sociedad de Biología de Concepción
$Titles = array(45409);
$want=array();

$Titles = array(13816);
$want=array();

// Gayana Zool 
$Titles = array(39684);
$want=array();

// Memoirs of the National Museum of Victoria
$Titles = array(58640);
$want=array();


$Titles = array(50545);
$want=array();

// Verhandlungen der Kaiserlich-Königlichen Zoologisch-Botanischen Gesellschaft in Wien.
$Titles = array(13275);
$want=array();

// Memoirs of the Carnegie Museum
$Titles = array(7536);
$want=array();

// Memoirs of the Carnegie Museum
$Titles = array(53696);
$want=array();

//J Royal Micro
$Titles = array(7413);
$want=array();

// The Journal of the Quekett Microscopical Club.
$Titles = array(7003);
$want=array();

// The Journal of microscopy and natural science
$Titles = array(3926);
$want=array();

// The Nautilis
$Titles = array(6170);
$want=array();

// J Hamb
$Titles = array(9594);
$want=array();

// Monitore zoologico italiano
$Titles=array(8983);
$want=array();

// 10428
//107001

// Bulletin - United States National Museum
$Titles=array(7548);
$want=array();

// Denkschriften der Medicinisch-Naturwissenschaftlichen Gesellschaft zu Jena
$Titles=array(53760);
$want=array();


// Proc USNM
$Titles=array(7519);
$want=array(53183);

// Malacologia
$Titles=array(12920);
$want=array();

// Opuscula zoologica
$Titles=array(44805);
$want=array();


// Öfversigt af Finska vetenskaps-societetens förhandlingar
$Titles=array(14261);
$want=array();

// Bulletin of the British Museum (Natural History). Geology.
$Titles=array(2197);
$want=array();


$Titles=array(13766);
$want=array();

// Zoologische Jahrbücher. Supplementheft.
$Titles=array(13352);
$want=array();

// 	 Zeitschrift für die gesammten Naturwissenschaft.
$Titles=array(44824);
$want=array();

$Titles=array(3595);
$want=array();

// Carnegie Institution of Washington publication
$Titles=array(5800);
$want=array();

$Titles=array(103380);

$Titles=array(8985);
$want=array();

// Occasional papers of the Boston Society of Natural History
$Titles=array(50720);
$want=array();

// Quarterly journal of microscopical science
$Titles=array(13831);
$want=array();

// Proceedings of the Biological Society of Washington. v 90
$Titles=array(3622);
$want=array(120622);

// Memoirs of the National Museum of Victoria
$Titles = array(58640);
$want=array();

// Australian Zoologist
$Titles = array(57946);
$want=array();

// Verhandlungen der Kaiserlich-Königlichen Zoologisch-Botanischen Gesellschaft in Wien
$Titles = array(13275);
$want=array();


$Titles = array(11322);
$want=array();

// Comptes rendus des séances de la Société de biologie et de ses filiales.
$Titles=array(8070);
$want=array();

// Iberus : revista de la Sociedad Española de Malacología
$Titles=array(49914);
$want=array();

// Diptères
$Titles=array(9859);
$want=array();

// Stuttgarter Beiträge zur Naturkunde
$Titles=array(49392);
$want=array();

// Ent Tid
$Titles=array(10616);
$want=array();

// Liangqi baxing dongwu yanjiu = Acta herpetologica Sinica
$Titles=array(53832);
$want = array();

// J Bombay
$Titles=array(7414);
$want=array();

// Phil J Sci
$Titles=array(69);
$want=array();

$Titles=array(50446);
$want = array();

$Titles=array( 13311);
$want = array();

$Titles=array(8096);
$want = array();

$Titles=array(1086);
$want = array();

$Titles=array(57946);
$want=array(121701,121700);

$Titles=array(10088);
$want=array(89700);

$Titles=array(5999);
$want=array();


$Titles=array(14688);
$want=array();



$Titles=array(57949);
$want=array();

$Titles=array(14292);
$want=array();

$Titles =array(44837);
$want=array();

$Titles = array(8079);
$want=array(39670);

$Titles=array(9197);
$want=array();

$Titles=array(5550);
$want=array();

$Titles=array(8075);
$want=array();

$Titles=array(3622);
$want=array(110033);

// Great Basin naturalist memoirs
$Titles=array(8018);
$want=array();

$Titles=array(3622);
$want=array(107526);


$Titles =array(2510);
$want=array(89742);

$Titles =array(5075);
$want=array();


// Memoirs of the National Museum of Victoria
$Titles = array(58640);
$want=array(121120,121121,121136,121937,122131,122129,122130);

$Titles = array(51876);
$want=array();

// Transactions and proceedings of the Royal Society of South Australia (Incorporated)
$Titles = array(51127);
$want=array();


// Transactions of the Sapporo Natural History Society
$Titles = array(5052);
$want=array();

// Journal of the New York Entomological Society
$Titles = array(8089);
$want=array();

// Tijdschrift voor natuurlijke geschiedenis en physiologie
$Titles = array(13509);
$want=array();

//Bulletin of the British Museum (Natural History). Historical.
$Titles = array(5067);
$want=array();

// Zool Jahr Ab Syst
$Titles = array(8980);
$want=array(121145);

$Titles = array(6885);
$want=array(84743);

// The Anoplura and Mallophaga of North American mammals
$Titles = array(60046);
$want=array();

// Fishery bulletin / U.S. Dept. of Commerce, National Oceanic and Atmospheric Administration, National Marine Fisheries Service
$Titles = array(3598);
$want=array();

$Titles=array(44718);
$want=array();

// American journal of conchology
$Titles=array(15900);
$want=array();

// Papers and proceedings of the Royal Society of Tasmania
$Titles=array(9494);
$want = array();

// Deutsche Südpolar-Expedition, 1901-1903, im Auftrage des Reichsamtes des Innern, hrsg. von Erich von Drygalski.
$Titles=array(2166);
$want = array();

// The Journal of the College of Science, Imperial University of Tokyo, Japan = Tokyo Teikoku Daigaku kiyo. Rika.
$Titles=array(7002);
$want = array();

$Titles=array(53715);
$want = array();


// Revista chilena de entomología / Universidad de Chile, Sociedad Chilena de Entomología
$Titles=array(46370);
$want=array();


// Occasional papers of the Museum of Natural History, the University of Kansas
$Titles=array(4672);
$want=array();


$Titles=array(14647);
$want=array();

// Memoirs of the Museum of Comparative Zoölogy, at Harvard College, Cambridge, Mass.
$Titles=array(7537);
$want=array();

// Archiv für Naturgeschichte. Abteilung A.
$Titles=array(12938);
$want=array();

$Titles = array(6885);
//$want=array(79453);
$want=array();

$Titles=array(12360);
$want=array();

// Memoirs of Museum of Victoria
$Titles=array(59883);
$want=array();


// Iheringia. Série zoologia.
$Titles=array(50228);
$want=array();


// Revue Zoologique par La Société Cuvierienne
$Titles=array(2214);
$want=array();

$Titles=array(45557);
$want=array();

// Brigham Young University science bulletin. Biological series.
$Titles=array(7958);
$want=array();

// Jahres-Bericht der Schlesischen Gesellschaft für Vaterländische Cultur.
$Titles=array(50438);
$want=array();

// Bollettino del Laboratorio di zoologia generale e agraria della R. Scuola superiore d'agricoltura in Portici.
$Titles=array(8269);
$want=array();

// Deutsche entomologische Zeitschrift Iris / herausgegeben vom Entomologischen Verein Iris zu Dresden.
$Titles=array(12260);
$want=array();

$Titles=array(58640);
$want=array(121937); // volume 39

// Swenska wetenskaps academiens handlingar
$Titles=array(49868);
$want=array();

$Titles=array(6525);
$want=array(111306);


$Titles=array(57946);
$want=array(121400);


// Natural history of Victoria. Prodromus of the zoology of Victoria; 
$Titles=array(4821);
$want=array();

// Breviora
$Titles=array(3989);
$want=array();

// Abhandlungen der Senckenbergischen Naturforschenden Gesellschaft
$Titles=array(16181);
$want=array();

// Fieldiana. Zoology memoirs.
$Titles=array(42257);
$want=array();

// Entomologische Zeitschrift
$Titles=array(44720);
$want=array();

// Bulletin de la Société d'histoire naturelle de Metz.
$Titles=array(49864);
$want=array();

// Report on the Pycnogonida, dredged by H.M.S. Challenger during the years 1873-76. By Dr. P.P.C. Hoek.
$Titles=array(13132);
$want=array();

// Pycnogonida, by T.V. Hodgson.
$Titles=array(12871);
$want=array();


// Comptes rendus hebdomadaires des séances de l'Académie des sciences
$Titles=array(4466);
$want=array();

// Boletín de la Sociedad Española de Historia Natural.
$Titles=array(6171);
$want=array();

// Proceedings of the Biological Society of Washington. v 112
$Titles=array(3622);
$want=array(107571);

// 	Stuttgarter Beiträge zur Naturkunde. Serie B. Geologie und Paläontologie
$Titles=array(43750);
$want=array();

// Memoirs of the California Academy of Sciences
$Titles=array(3949);
$want=array();

// Entomologische Zeitung
$Titles=array(8641);
$want=array();


// Memoirs of Museum of Victoria
$Titles=array(59883);
$want=array(122635,122636,122637,122638,122639);

// Publication (Field Museum of Natural History : 1909). Zoological series.
$Titles=array(42255);
$want=array();


// Mission scientifique du cap Horn, 1882-1883.
$Titles=array(2480);
$want=array();

// Transactions of the Linnean Society of London
$Titles=array(683);
$want=array();

// Bulletin of the Essex Institute
$Titles=array(7933);
$want=array();

// Revista do Museu Paulista
//10241

// Bulletin de l'Institut océanographique de Monaco
$Titles=array(2185);
$want=array();

// Transactions and proceedings of the New Zealand Institute
$Titles=array(48984);
$want=array();

// Annales des sciences naturelles. Zoologie et biologie animale
$Titles=array(13266);
$want=array();


// Mitteilungen aus dem Naturhistorischen Museum in Hamburg
$Titles=array(9579);
$want=array();

// Bulletin biologique de la France et de la Belgique
$Titles=array(10057);
$want=array();

$Titles=array(52228,52087);
$want=array();

$Titles=array(60244);
$want=array();

// Anzeiger / Kaiserliche Akademie der Wissenschaften in Wien. Mathematisch-Naturwissenschaftliche Klasse.
$Titles=array(39806);
$want=array();

$Titles=array(4050);
$want=array(24617);

// Journal and proceedings of the Asiatic Society of Bengal
$Titles=array(47024);
$want=array();

// Mittheilungen aus der Zoologischen Station zu Neapel
$Titles=array(8813);
$want=array();

// The echinoderm fauna of Torres Strait: its composition and its origin, by Hubert Lyman Clark
$Titles=array(14613);
$want=array();

// Redia
$Titles=array(15675);
$want=array();

// Résultats des campagnes scientifiques accomplies sur son yacht par Albert Ier, prince souverain de Monaco
$Titles=array(2169);
$want=array();

// Records of the Canterbury Museum
$Titles=array(42577);
$want=array();

// Memoirs of the National Museum of Victoria
$Titles = array(58640);
$want=array(122435,122667,121119);

// Expédition antarctique française (1903-1905) : commandée par le dr. Jean Charcot. Sciences naturelles : documents scientifiques.
$Titles=array(7063);
$want=array();

// Natuurkundig tijdschrift voor Nederlandsch Indië / uitgegeven door de Natuurkundige Vereeniging in Nederlandsch Indië
$Titles=array(13350);
$want=array();

// Sitzungsberichte der Kaiserlichen Akademie der Wissenschaften. Mathematisch-Naturwissenschaftliche Classe. Abt. 1, Mineralogie, Botanik, Zoologie, Geologie und Paläontologie.
$Titles=array(6884);
$want=array();


// The natural history of Juan Fernandez and Easter Island / edited by Carl Skottsberg
$Titles=array(25662);
$want=array();

$Titles=array(7415);
$want=array(110342,
110352,
110959,
111018,
111120);

// Memoirs of Museum of Victoria
$Titles = array(59883);
$want=array(122921,122922,122923);
$want=array(122916, 122917);

// Természetrajzi Füzetek kiadja a Magyar nemzeti Muzeum
$Titles=array(13503);
$want=array();



// Trudy Russkago entomologicheskago obshchestva. Horae Societatis entomologicae rossicae, variis semonibus in Russia usitatis editae
$Titles=array(12032);
$want=array();

// Annals of the Missouri Botanical Garden
$Titles=array(702);
$want=array(89027);

// The Entomologist.
$Titles=array(11469);
$want=array();


// Stettiner Entomologische Zeitung.
$Titles=array(8630);
$want=array();

// Abhandlungen und Berichte des Königl. Zoologischen und Anthropologisch-Etnographischen Museums zu Dresden
$Titles = array(49442);
$want=array();

$Titles = array(9579);
$want=array(93454);

// Bulletin de la Société entomologique de France
$Titles=array(8187);
$want=array();

// Sitzungsberichte. Kaiserliche Akademie der Wissenschaften in Wien, Mathematisch-Naturwissenschaftliche Klasse. Abt. 1, Mineralogie, Krystallographie, Botanik, Physiologie der Pflanzen, Zoologie, Paläontologie, Geologie, physische Geographie und Reisen /
$Titles=array(7337);
$want=array();

$Titles=array(10088);
$want=array(88934);

// to do
// Annalen des Naturhistorischen Museums in Wien
$Titles=array(5560);
$want=array();

// Wissenschaftliche ergebnisse der Schwedischen zoologischen expedition nach dem Kilimandjaro, dem Meru und den umgebenden Massaisteppen Deutsch-Ostafrikas 1905-1906, unter leitung von prof. dr. Yngve Sjöstedt. Hrsg. mit unterstützung von der Königl. schwedischen akademie der wissenschaften 
$Titles=array(1805);
$want=array();

// Opinions and declarations rendered by the International Commission on Zoological Nomenclature
$Titles=array(50753);
$want=array();

// Memorias de la Real Sociedad Española de Historia Natural
$Titles=array(11494);
$want=array();

// Technical series.
$Titles=array(11812);
$want=array();

// 	The London and Edinburgh philosophical magazine and journal of science.
$Titles=array(58332);
$want=array();

// Entomologische Nachrichten.
$Titles=array(9698);
$want=array();

// Transactions and proceedings of the Royal Society of South Australia (Incorporated).
$Titles=array(51127);
$want=array();

// Insecta; publication mensuelle de la Station entomologique de la Faculté des sciences de Rennes.
$Titles=array(12277);
$want=array();

// 	Anales del Museo Nacional de Buenos Aires
$Titles=array(5595);
$want=array();

// Proc USNM
$Titles=array(7519);
$want=array(32576);

// Beiträge zur Meeresfauna der Insel Mauritius und der Seychellen 
$Titles=array(49512);
$want=array();

// Histoire naturelle des araignées
$Titles=array(51973);
$want=array();

// Anales de la Sociedad Científica Argentina
$Titles=array(44792);
$want=array();

// Actes de la Société linnéenne de Bordeaux
$Titles=array(16235);
$want=array();

// Transactions of the South African Philosophical Society
$Titles=array(53491);
$want=array();

// Memoirs of Museum of Victoria
$Titles = array(59883);
//$want=array(122921,122922,122923);
//$want=array(122916, 122917);

$want=array(122979, 122980, 122999, 123000);

// Proceedings of the Linnean Society of New South Wales
$Titles=array(6525);
$want=array(30106);

// The reptiles of the Indo-Australian archipelago
$Titles=array(5069);
$want=array();

// Annales de la Société royale malacologique de Belgique
$Titles=array(6205);
$want=array();


// An account of the Crustacea of Norway
$Titles=array(1164);
$want=array();

// Report of the Copepoda collected by Professor Herdman, at Ceylon, in 1902,
$Titles=array(59334);
$want=array();

$Titles=array(53611);
$want=array();



// Swenska wetenskaps academiens handlingar
$Titles=array(49868);
$want=array();

// Wissenschaftliche Ergebnisse der Schwedischen Südpolar-Expedition
$Titles=array(6756);
$want=array();


// Bulletin of the Southern California Academy of Sciences 1996->
$Titles=array(60457);
$want=array();

// Entomologische berichten
$Titles=array(8649);
$want=array();


// Jahrbuch der Hamburgischen Wissenschaftlichen Anstalten
$Titles=array(9594);
$want=array();

// Bulletin de la Société zoologique de France
$Titles=array(51699);
$want=array();

// Bericht über die Senckenbergische Naturforschende Gesellschaft in Frankfurt am Main
$Titles=array(8745);
$want=array();

// Zoologische Jahrbücher. Supplementheft.
$Titles=array(13352);
$want=array();

// Proceedings of the Royal Zoological Society of New South Wales
$Titles=array(58245);
$want=array();

// Abhandlungen aus dem Gebiete der Naturwissenschaften / hrsg. vom Naturwissenschaftlichen Verein in Hamburg.
$Titles=array(11442);
$want=array();

// A monograph of the North American Proctotrypidae by William H. Ashmead.
$Titles=array(38713);
$want=array();


// Bulletin - United States National Museum
$Titles=array(7548);
$want=array();

// Chloropidae. Eine monographische Studie / von Theodor Becker. Tiel I, Palaärktische Region :
$Titles=array(9555);
$want=array();

// Jahrbücher der Deutschen Malakozoologischen Gesellschaft
$Titles=array(15952);
$want=array();


// Revue d'entomologie
$Titles=array(10428);
$want=array();


$Titles=array(52228);
$want=array();

$Titles=array(52070);
$want=array();

// Transactions of the South African Philosophical Society
$Titles=array(53491);
$want=array();


$Titles=array(60244);
$want=array(123329,123330,123331,
123321,123322,123323,
123316,123317);

// Liangqi baxing dongwu yanjiu = Acta herpetologica Sinica
$Titles=array(53832);
$want = array(114383,114384,123346);

// Sitzungsberichte der Kaiserlichen Akademie der Wissenschaften
$Titles=array(6888);
$want=array();


$Titles=array(10088);
$want=array(41000);


// Abhandlungen herausgegeben vom Naturwissenschaftlichen Verein zu Bremen.
$Titles=array(4220);
$want=array();

// Miscellaneous publication - University of Kansas, Museum of Natural History.
$Titles=array(4050);
$want=array(25998);

// Entomologische Mitteilungen
$Titles = array(9479);
$want=array();

// Bulletin / Illinois Natural History Survey.
$Titles = array(14605);
$want=array();

// Memoirs of the American Entomological Society
$Titles=array(6193);
$want=array();

// TvE
$Titles=array(10088);
$want=array(89662);

$Titles=array(52368);
$want=array();

// Proceedings of the Biological Society of Washington
$Titles=array(3622);
$want=array(107600);

// Archives de parasitologie
$Titles=array(43753);
$want=array();

// Sitzungsberichte der Kaiserlichen Akademie der Wissenschaften.
// Mathematisch-Naturwissenschaftliche Klasse. 
// Abt. 3, Anatomie und Physiologie des Menschen und der Tiere sowie aus jenem der theoretischen Medizin.
$Titles=array(8100);
$want=array();

// Aquila
$Titles=array(8350);
$want=array();

// The Journal of entomology : descriptive and geographical.
$Titles=array(12498);
$want=array();

// Descriptions of new genera and species of coleoptera / by T. Broun
$Titles=array(17491);
$want=array();

// Bullettino della Società entomologica italiana
$Titles =array(9612);
$want=array(39682);

$Titles =array(11247);
$want=array();

$Titles=array(60751,60677,60679,60680,60682,60683,60684,60685,60686,60687,60688,60689,60692);
$want=array();


$Titles=array(10009);
$want=array();

// Memoirs of the San Diego Society of Natural History
$Titles=array(3234);
$want=array();

// Sitzungsberichte der Kaiserlichen Akademie der Wissenschaften. Mathematisch-Naturwissenschaftliche Classe.
$Titles=array(6776);
$want=array();

// Münchner koleopterologische Zeitschrift
$Titles=array(51516);
$want=array();

// Annals of the New York Academy of Sciences
$Titles=array(51004);
$want=array();


// Brotéria. Série zoológica.
$Titles=array(7952);
$want=array();

// The Danish Ingolf-Expedition
$Titles=array(6547);
$want=array();

// Herpetological type-specimens in the University of Illinois Museum of Natural History
$Titles=array(50204);
$want=array();

// Proceedings of the Linnean Society of New South Wales
$Titles=array(6525);
$want=array(111306,
111086,
111697,
121639);

// Annals of the Entomological Society of America
$Titles=array(9426);
$want=array();

// Tijdschrift voor entomologie
$Titles=array(10088);
$want=array();

// Memoirs of the Queensland Museum
$Titles=array(60751);
$want=array();

// Bulletin of the British Ornithologists' Club
$Titles=array(46639);
$want=array();

// Archivos do Museu Nacional do Rio de Janeiro
$Titles = array(6524);
$want=array();

// Chironomides de Belgique et spécialement de la zone des Flandres
$Titles = array(52331);
$want=array();

// Two new species of Ontario spiders
$Titles = array(60744);
$want=array();

// Memoirs of the Queensland Museum
$Titles=array(60751);
$want=array(123802,123808,123809,123807);

// The Entomologist's monthly magazine.
$Titles = array(8646);
$want=array();

// The Natural history of Juan Fernandez and Easter Island / edited by Carl Skottsberg
$Titles = array(41367);
$want=array();

// The natural history of Juan Fernandez and Easter Island / edited by Carl Skottsberg.
$Titles = array(25662);
$want=array();

// Miscellaneous publication - University of Kansas, Museum of Natural History.
$Titles=array(4050);
$want=array(24613);


// Bulletin of the British Ornithologists' Club
$Titles=array(46639);
$want=array(123804);

// Proceedings Dorset Natural History and Archaeological Society 
$Titles=array(17361);
$want=array();

// Memoirs of the Queensland Museum
$Titles=array(60751);
$want=array(123914,123907,123908,123910,123913,123909,123906);

// The Ohio journal of science
$Titles=array(3616);
$want=array();

$Titles=array(10088);
$want=array(88930);

// The Journal of parasitology
$Titles=array(36618);
$want=array();

// Occasional papers of the Museum of Zoology, University of Michigan
$Titles=array(14051);
$want=array();

// Bulletin du Musée royal d'histoire naturelle de Belgique = Mededeelingen van het Korinklijk Natuurhistorisch Museum van België
$Titles=array(8453);
$want=array();

// Zoologische Annalen
$Titles=array(50900);
$want=array();

// Természetrajzi Füzetek kiadja a Magyar nemzeti Muzeum
$Titles=array(13503);
$want=array();

// Memoirs of the Queensland Museum
$Titles=array(60751);
$want=array(123990,123998);

// Gayana
$Titles=array(39988);
$want=array();



// Mémoires couronnés et autres mémoires publiés par l'Académie royale des sciences, des lettres et des beaux-arts de Belgique
$Titles=array(6369);
$want=array();

// Journal of the East Africa Natural History Society : official publication of the Coryndon Memorial Museum (Museum Trustees of Kenya).
$Titles=array(53426);
$want=array();

// Transactions of the Kentucky Academy of Science
$Titles=array(49170);
$want=array();

// Transactions of the Maryland Academy of Science and Literature
$Titles=array(5612);
$want=array();

$Titles=array(2087);
$want=array(122833,122834,122838,122839,122840,122841,122842,122843,122844,122845,122846,122855,122857,122865,122866,122867,122961,122962,122983,122997,123007,123008,123019,123020,123021,123022,123025,123026,123027,123028,123165,123166,123167,123168,123269,123316,123317,123321,123322,123323,123329,123330,123331);


$Titles=array(7519);
$want=array(114064);

// Memoirs of the American Entomological Society
$Titles=array(6193);
$want=array();

$Titles=array(3622);
$want=array(109905);

// Zoologische Jahrbücher. Abteilung für Anatomie und Ontogenie der Tiere
$Titles=array(11437);
$want=array();

// Memoirs of the Queensland Museum
$Titles=array(60751);
$want=array(124162);

// Die Fauna südwest-Australiens. Ergebnisse der Hamburger südwest-australischen Forschungsreise 1905. Hrsg. von prof. dr. W. Michaelsen und dr. R. Hartmeyer...
$Titles=array(7416);
$want=array();

$Titles=array(10088);
$want=array(89680);

$Titles=array(10088);
$want=array(95697);

// Russkoe entomologicheskoe obozrenie = Revue russe d'entomologie
$Titles=array(11807);
$want=array();

$Titles=array(16274);
$want=array();

// Transactions of the American Entomological Society
$Titles=array(5795);
$want=array();

$Titles=array(60751);
$want=array(124460);

// Bulletin - United States National Museum
$Titles=array(7548);
$want=array(32603);

$Titles=array(13352);
$want=array(47888);



// Bulletin of the Museum of Comparative Zoology at Harvard College
$Titles=array(2803);
$want=array(91660);

// Nova Guinea : résultats de l'expédition scientifique néerlandaise à la Nouvelle-Guinée en 1903[-1920] 
$Titles=array(61266);
$want=array();

// Alaska / Harriman Alaska Expedition
$Titles=array(27846);
$want=array();

// Zoopathologica
$Titles=array(60099);
$want=array();

// Memoirs of the Queensland Museum 46(1)
$Titles=array(61449);
$want=array();


$Titles=array(6525);
$want=array(111306);

// Proceedings of the Royal Society of Queensland.
$Titles=array(14019);
$want=array();

// The coccid genera Chionaspis and Hemichionaspis / [by] R. A. Cooley
$Titles=array(32969);
$want=array();

// Die Cephalopoden, von Carl Chun
$Titles=array(13499);
$want=array();

// On aquatic carnivorous Coleoptera or Dytiscid. By David Sharp
$Titles=array(9530);
$want=array();

// The Scientific transactions of the Royal Dublin Society
$Titles=array(14895);
$want=array();

$Titles=array(6525);
$want=array(108605);

$Titles=array(16211);
$want=array();

// The Scientific proceedings of the Royal Dublin Society
$Titles=array(44062);
$want=array();

// Annuario del Museo Zoologico della Università di Napoli
$Titles=array(10903);
$want=array();

// Memoirs of the Queensland Museum 46(1)
$Titles=array(61449);
$want=array(125112,125113,125114,125115,125117,125118);


// Proceedings of the California Academy of Sciences. 3d ser., Zoology
$Titles=array(45400);
$want=array();

/*
// Memorie della Reale accademia delle scienze di Torino
$Titles=array(6366);
$want=array();
*/

// Palaeontographia Italica : Memorie di paleontologia
$Titles=array(48618);
$want=array();


$Titles=array(3622);
$want=array(111599,111796,120622,123310);

$Titles=array(43423);
$want=array();

// The transactions of the Entomological Society of New South Wales. v. 1-2, [1863-73]
$Titles=array(9427);
$want=array();

// Boletin de la Academia Nacional de Ciencias en Córdoba, República Argentina
$Titles=array(3645);
$want=array();

// The Journal of the Linnean Society of London. Zoology
$Titles=array(45411);
$want=array();

$Titles=array(61449);
//$want=array(125112,125113,125114,125115,125117,125118);
$want=array(125183,125181,125185,125180);


$Titles=array(46639);
$want=array(123798,123799,123804,123885,123887,123890,124973,124974,125287,125289,125290,125291,125292,125298,125299,125300,125301,125305,125306,125307,125339,125340,125341,125354,125355);

// Mémoires.
// Société royale d'entomologie de Belgique
$Titles=array(10279);
$want=array();

// Fauna hawaiiensis
$Titles=array(4628);
$want=array();

// The victorian naturalist
$Titles=array(60605);
$want=array();

// Abhandlungen der K. K. Zool.-Botan. Gesellschaft in Wien
$Titles=array(6249);
$want=array();

// Zoological science
$Titles=array(61647);
$want=array();

$Titles=array(2202);
$want=array(19500,125484);

// Solem
$Titles=array(2553,2554,3015,3204);
$want=array();

// Mémoires de la Société zoologique de France
$Titles=array(9485);
$want=array();

// ...Scientific results of the cruises of the yachts "Eagle" and "Ara", 1921-1928, William K. Vanderbilt, commanding. Crustacea... By Lee Boone.
$Titles=array(4467);
$want=array();

$Titles=array(52566);
$want=array(113555);

// Wissenschaftliche ergebnisse der Deutschen Zentral-Africa-Expedition, 1907-1908 : unter Führung Adolf Friedrichs, herzogs zu Mecklenburg
$Titles=array(7048);
$want=array();

// Oversigt over det Kongelige Danske videnskabernes selskabs forhandlinger = Bulletin de l'Académie royale des sciences et des lettres de Danemark.
$Titles=array(42570);
$want=array();

$Titles=array(6525);
$want=array(109059);

// Wissenschaftliche ergebnisse der zweiten Deutschen Zentral-Africa-Expedition, 1910-1911, unter Führung Adolf Friedrichs.
$Titles=array(6486);
$want=array();


// Senckenbergiana
$Titles=array(43789);
$want=array();

// The fauna and geography of the Maldive and Laccadive archipelagoes : being the account of the work carried on and of the collections made by an expedition during the years 1899 and 1900 / Ed. by J. Stanley Gardiner.
$Titles=array(10215);
$want=array();

// New names introduced by H. A. Pilsbry in the Mollusca and Crustacea, by William J. Clench and Ruth D. Turner
$Titles=array(6489);
$want=array();

// The Journal of malacology
$Titles=array(16176);
$want=array();

// Proc Zool Soc Lond
$Titles=array(44963);
$want=array();

// Memoirs : Australian Museum
$Titles=array(7408);
$want=array();

$Titles=array(58640);
$want=array(121122);


// Bulletin of the British Museum (Natural History). Zoology
$Titles=array(2202);
$want=array(125591, 125642);

$Titles=array(61647);
$want=array(125561,125560,125557,125554,125539);

$Titles = array(59721);
$want=array();

// Recherches sur la faune de Madagascar et de ses dépendances / d'après les découvertes de François P.L. Pollen et D.C. van Dam
$Titles = array(46191);
$want=array();

// Verslagen en mededeelingen der Koninklijke Akademie van Wetenschappen
$Titles = array(58722);
$want=array();

// Timehri : the journal of the Royal Agricultural and Commercial Society of British Guiana.
$Titles = array(58209);
$want=array();


$Titles=array(43746,61442,60605);
$want=array();


$Titles=array(61449);
//$want=array(125112,125113,125114,125115,125117,125118);
//$want=array(125183,125181,125185,125180);
$want = array(125724,
125725,
125727,
125729,
125730
);

// Report of the Australian Association for the advancement of Science
$Titles=array(14641);
$want=array();

$Titles=array(10088);
$want=array(40952);

// Publications in biological oceanography
$Titles=array(52116);
$want=array();

// Proceedings of the Royal Zoological Society of New South Wales
$Titles=array(58245);
$want=array();

// Zoological results of the fishing experiments carried on by F.I.S. "Endeavour," 1909-14 under H.C. Dannevig, commonwealth director of fisheries. Volume 1-5. Published by direction of the Ministers for Trade and Customs, ... .
$Titles=array(11387,29284,13854);
$want=array();

// Dobutsugaku zasshi
$Titles=array(9498);
$want=array();

// Report of work of the Experiment Station of the Hawaiian Sugar Planters' Association. Division of Entomology bulletin.
$Titles=array(15692);
$want=array();

// Rhynchota ... by W. L. Distant.
$Titles=array(9193);
$want=array();

// The fauna of British India, including Ceylon and Burma ... ed. by W.T. Blanford.
$Titles=array(48423);
$want=array();

// Annales de biologie lacustre
$Titles=array(6227);
$want=array();

// Records of the South Australian Museum 
$Titles=array(61893);
$want=array();

// Transactions and proceedings and report of the Royal Society of South Australia (Incorporated).
$Titles=array(16190);
$want=array();

// Bulletin of the Illinois State Laboratory of Natural History.
$Titles=array(8196);
$want=array();

// Helicinenstudien
$Titles=array(61796);
$want=array();

// to do
// Sitzungsberichte to do
$Titles = array(59721);
$want=array(126003);

// 	Bulletin / Peabody Museum of Natural History.
$Titles = array(10011);
$want=array();

// The fishes of the Indo-Australian Archipelago
$Titles = array(28679);
$want=array();

// The fishes of the Indo-Australian Archipelago ... by Dr. Max Weber ... and Dr. L.F. de Beaufort.
// vols 1-4
$Titles = array(12497);
$want=array();

// Denkschriften der Medicinisch-Naturwissenschaftlichen Gesellschaft zu Jena
$Titles=array(53760);
$want=array();

$Titles=array(53716);
$want=array();

// Veröffentlichungen der Zoologischen Staatssammlung München
$Titles=array(39971);
$want=array();


$Titles=array(61449);
$want=array(
126027,
126028,
126031,
126034,
126035,
126057
);

// Annals of the South African Museum. Annale van die Suid-Afrikaanse Museum
$Titles=array(6928);
$want=array();

// The American journal of science
$Titles=array(60982);
$want=array();


// The Canadian field-naturalist
$Titles=array(39970);
$want=array();

$Titles=array(6928);
$want=array(
126226,
126228,
126229,
126247,
126248,
126249,
126250,
126255
);

// Fam. Empididae / bearbeitet von Professor M. Bezzi.
$Titles=array(9448);
$want=array();

$Titles=array(12266);
$want=array();

// Ann Soc ent Belge
$Titles=array(11933);
$want=array(110435,
110222,
110100,
110227,
110226,
111455,
110236);

// Occasional papers of the Natural History Society of Wisconsin
$Titles=array(45019);
$want=array();

// Proceedings of the California Academy of Sciences, 4th series.
$Titles=array(3943);
$want=array(103002,
126211,
126212,
126213,
126214,
126225,
126227,
126251,
126252,
126256,
126257,
126260
);

$want=array(126261);

// Fieldiana. Zoology.
$Titles=array(42256);
$want=array();

// Bulletin of the Liverpool Museums under the City Council.
$Titles=array(12566);
$want=array();

// Annals of the South African Museum. Annale van die Suid-Afrikaanse Museum
$Titles=array(6928);
$want=array(126362,126363,126364,126365,126378,126379,126383,126384,126385,126386,126387,126388,126389,126390,126391,126392,126393,126394,126395,126396,126397,126398,126399,126400,126401,126403,126404,126406,126408,126426,126427,126438,126439,126440,126441,126442,126443,126444,126445,126446,126447,126448,126449,126450,126451,126452,126453,126454,126455,126456,126457,126458,126459,126460,126462,126463,126464,126465);

$Titles=array(7410);
$want=array(
126373,
126371,
126374,
126372,
126368,
126376);
$want = array();

$Titles=array(
16190,
16197,
50715,
16186);
$want=array();

// Records of the South Australian Museum
$Titles=array(61893);
$want=array();

$Titles=array(6928);
$want=array(126514,
126516,
126518,
126520,
126521,
126526
);

// Revue suisse de zoologie
$Titles=array(62174);
$want=array();

// Revue suisse de zoologie
$Titles=array(8981);
$want=array();

// Természetrajzi Füzetek kiadja a Magyar nemzeti Muzeum
$Titles=array(13503);
$want=array();

$Titles=array(2087);
$want=array(123165);

$Titles=array(53542);
$want=array();

// Occasional papers of the Natural History Museum, the University of Kansas, Lawrence, Kansas
$Titles=array(5584);
$want=array();


$Titles=array(3622);
$want=array(107515);

// Ibis
$Titles=array(8115);
$want=array();

// Revue suisse de zoologie
$Titles=array(62174);
$want=array(126656,
126657,
126658,
126659,
126660,
126678,
126692,
126693,
126694,
126695
);

// The vertebrate fauna of the Selma Formation of Alabama. Rainer Zangerl.
$Titles=array(30785);
$want=array();

// Bullettino della Società entomologica italiana
$Titles=array(9612);
$want=array();

// Nachrichtsblatt der Deutschen Malakozoologischen Gesellschaft.
$Titles=array(14716);
$want=array();


// Beschalte weichthiere Deutsch-Ost-Afrikas.
$Titles=array(12943);
$want=array();

// Mitteilungen aus der zoologischen Sammlung des Museums für Naturkunde in Berlin
$Titles=array(42661);
$want=array();

// The journal of the Natural History Society of Siam
$Titles=array(11908);
$want=array();

// The snakes of the Philippine Islands
$Titles=array(55346);
$want=array();


$Titles=array(3943);
$want=array(126405,
126486,
126494,
126495,
126496,
126505,
126513
);

// Casopis Ceské spolecnosti entomologické = Acta Societatis Entomologicae Bohemiae
$Titles=array(11932);
$want=array();

// Transactions
$Titles=array(37912);
$want=array();

// Report of the Commissioner for ... / United States Commission of Fish and Fisheries.
$Titles=array(15220);
$want=array();

// Archivio zoologico : pubblicato sotto gli auspicii della unione zoologica italiana.
$Titles=array(46199);
$want=array();

$Titles=array(62174);
$want=array(
126811,
126812,
126814,
126816,
126837,
126882
);

// The Australian zoologist.
$Titles=array(57946);
$want=array(123198);

// Bulletin de l'Institut botanique de Buitenzorg
$Titles=array(4865);
$want=array();

// Advances in herpetology and evolutionary biology
$Titles=array(53661);
$want=array();

// Geological Magazine
//$Titles=array(44230);
//$want=array();

$Titles=array(3882);
$want=array(21973);

// Jahrbücher des Nassauischen Vereins für Naturkunde
$Titles=array(7007);
$want=array();

// Jahreshefte des Vereins für vaterländische Naturkunde in Württem
$Titles=array(7923);
$want=array();

// Revista argentina de historia natural
$Titles=array(37903);
$want=array();


// Nyt magazin for naturvidenskaberne / udgives af den Physiographiske forening i Christiania
$Titles=array(8076);
$want=array();

// Journal of Hymenoptera research
$Titles=array(2680);
$want=array();

$Titles=array(6928);
$want=array(
126582,
126862,
126964,
126965,
126992,
126993,
126994,
126995,
127012,
127013,
127015,
127016,
127017,
127018,
127019,
127020,
127021,
127022
);

// Trematodes of the Pacific Northwest, an annotated catalog [by] Ivan Pratt [and] James E. McCauley.
$Titles=array(7307);
$want = array();


// Ergebnisse der in dem Atlantischen Ocean von Mitte Juli bis Anfang November 1889 ausgeführten Plankton-Expedition der Humboldt-Stiftung. Auf Frund von gemeinschaftlichen Untersuchungen einer Reihe von Fach-Forschern.
$Titles=array(2167);
$want = array();

// Iheringia. Série zoologia.
$Titles=array(50228);
$want=array(109985);

$Titles=array(7422);
$want=array(95661);

// Transactions of the American Entomological Society and proceedings of the Entomological Section of the Academy of Natural Sciences
$Titles=array(7549);
$want=array();

// Annals of the South African Museum
$Titles=array(6928);
$want=array(
127101,
127102,
127104,
127105,
127106
);

// BMNH (Ent)
$Titles=array(53882);
$want=array(
127049,
127086,
127088,
127087,
127089,
127090,
127091,
127094,
127092,
127093,
127037,
127096,
127032,
127103
);


// BBOC
$Titles=array(62378);
$want=array();


// Ezhegodnik
// Annuaire du Musée zoologique 
$Titles=array(8097);
$want=array();


// Zoologiska bidrag från Uppsala.
$Titles=array(53537);
$want=array();

// Ornithologische Monatsberichte
$Titles=array(46941);
$want=array();

// Journal für Ornithologie
$Titles=array(47027);
$want=array();

// Jahrbücher des Nassauischen Vereins für Naturkunde
$Titles=array(7007);
$want=array();

// Journal of entomology and zoology
$Titles=array(8150);
$want=array();

// to do
// Physis
$Titles=array(51727);
$want=array();

// Bulletin of the British Museum (Natural History). Entomology. supplement
$Titles=array(62492);
$want=array();


$Titles=array(6928);
$want=array(
127133,
127145,
127146,
127147,
127148,
127149,
127150,
127151,
127190,
127191,
127193,
127217,
127218,
127219,
127236,
127237,
127242,
127243,
127256,
127257,
127258
);


$Titles=array(62174);
$want=array(127132);



//
$Titles=array(62174);
$want=array(127363);

$Titles=array(53882);
$want=array(127419,
127420,
127421,
127431,
127432);

$Titles=array(12597);
$want=array();

$Titles=array(2803);
$want=array(87759);

$Titles=array(15728);
$want=array();

$Titles=array(13271);
$want=array(87990);

$Titles=array(3989);
$want=array(35595);

$Titles=array(51854);
$want=array();

$Titles=array(13353);
$want=array();

$Titles=array(61449);
$want=array(125543,126533,125724,125730);

$Titles=array(62642);
$want=array();

// Journal of the Lepidopterists' Society.
$Titles=array(62643);
$want=array();

// Bulletin of the British Museum (Natural History). Entomology
$Titles=array(53882);
$want=array(
127336,
127337,
127419,
127420,
127421,
127431,
127432,
127476,
127492);

// Fauna exotica; : mitteilungen aus dem gebiete der exotischen insektenwelt
$Titles=array(60999);
$want=array();

// Bulletin of the British Museum (Natural History). Entomology
$Titles=array(53882);
$want=array(
127598,
127597,
127596
);

$Titles=array(8074);
$want=array();

// Anatomischer Anzeiger
$Titles=array(11400);
$want=array();

// Jornal de sciências mathemáticas, físicas e naturais /
$Titles=array(59321);
$want=array();

$Titles=array(6301);
$want=array();


$Titles=array(53882);
$want=array(
127713,
127714,
127715,
127716,
127738,
127739,
127740,
127741,
127742,
127743,
127744,
127745,
127781
);

$Titles=array(62643);
$want=array(
127762,
127718,
127736,
127735,
127734
);

// Revue critique de paléozoologie et de paléophytologie
$Titles=array(49829);
$want=array();

// Special occasional publication / Department of Mollusks, Museum of Comparative Zoology, Harvard University.
$Titles=array(5370);
$want=array();

// Records of the South Australian Museum
$Titles=array(61893);
$want=array();


// Transactions of the Royal Society of South Australia
$Titles=array(62638);
$want=array();

$Titles=array(15793);
$want=array();


$Titles=array(10507);
$want=array();

// Wissenschaftliche Ergebnisse der Deutschen Tiefsee-Expedition auf dem Dampfer "Valdivia" 1898-1899. Im Auftrage des Reichsamtes des Innern hrsg. von Carl Chun [et al.]
$Titles=array(2171);
$want=array();

// Zitteliana
$Titles=array(39972);
$want=array();

// Archiv für mikroskopische Anatomie
$Titles=array(13307);
$want=array(49533);

// Bulletin of the British Museum (Natural History). Zoology
$Titles=array(62642);
$want = array(
128103,
128105,
128102,
128106,
128104
);

// to do:

// Revue suisse de zoologie
$Titles=array(8981);
$want=array(128115,128116,128117,128116,127885);

// Johnsonia
//$Titles=array(62834);
//$want=array();

/*
$Titles=array(62643);
$want=array(
127884,
128065,
128066,
128067,
128068,
128069,
128070,
128071,
128072,
128073,
128074,
128075,
128076,
128077,
128078,
128079,
128080,
128081,
128118
);
*/

/*
// Transactions of the Royal Society of South Australia, Incorporated.
$Titles=array(62638);
$want=array(
128034,
128035,
128033,
128036,
128038,
128037,
128039,
128053,
128096,
128099
);
*/

//Breviora
//$Titles=array(3989);
//$want=array(127898,128235,127899,35572,110338,110327,113961);

// Verhandelingen van het Bataviaasch Genootschap der Kunsten en Wetenschappen
$Titles=array(7371);
$want=array(128083);

$Titles=array(62815);
$want=array();

//Breviora
$Titles=array(3989);
$want=array(128236);


$Titles=array(13192);
$want=array();

// Revue suisse de zoologie
$Titles=array(8981);
$want=array(128299,128300,128309,128310,128314,128315,128347,128348,128349,128350,128351,128352,128353,128354,128355,128356,128357,128358,128359,128360,128361,128485,128486,128487,128489,128490,128491,128492,128493,128494,128495,128496,128497,128498,128500,128501,128502,128574,128586,128598,128625,128633,128634,128635,128636,128637,128642,128643,128644,128645,128646,128650,128668,128670,128671,128672,128673,128674);


// Journal of the Lepidopterists' Society.
$Titles=array(62643);
$want=array(
128428,
128488);

$Titles=array(62815);
$want=array(128445);

// Transactions of the Royal Society of South Australia, Incorporated
$Titles=array(62638);
$want=array(128619,128623,128638);

$Titles=array(50204);
$want=array();


$Titles=array(46639);
$items=array(
125530,
125531,
125532,
125588,
125593,
125657,
125660,
125661,
125662,
125663,
125664,
125665,
125666,
125672,
125673,
125674,
125675,
125676,
125677,
125678
);

$Titles=array(50720);
$want=array(
107444,
107543,
107976,
108005,
108260,
108730,
111648,
111780
);

$Titles=array(5551);
$want=array();

$Titles=array(3622);
$want=array(107497);


$Titles=array(44800);
$want=array();

// Nachrichtenblatt der Bayerischen Entomologen
$Titles=array(41410);
$want=array();


$Titles=array(52225);
$want=array();

$Titles=array(
62638,

62815, // Annals of the South African Museum. Annale van die Suid-Afrikaanse Museum

8981

);
$want=array(
128846,
128847,
128848,
128849,
128851,
128852,
128850,
128817,

128843,

128827,
128833,
128836,
128837
);

/*

//Nota lepidopterologica
$Titles=array(63275);
$want=array();
*/

$Titles=array(8981);
$want=array(
128789,
128791,
128795,
128796,
128799
);

$Titles=array(9277);
$want=array();

// Acta Societatis Scientiarum Fennicae / Suomen Tiedeseura
$Titles=array(13498);
$want=array();

// Boletín del Museo Nacional de Chile
$Titles=array(45466);
$want=array();

$Titles=array(2202);
$want=array(84546);

$Titles=array(62638);
$want=array(
128920,
128921,
128922,
128923,
128924,
128927
);

$Titles=array(8981);
$want=array(
128869,
128870,
128872,
128873,
128876,
128925
);

$Titles=array(60746);
$want=array();


$Titles=array(7519);
$want=array(32426);

$Titles=array(2803);
$want=array(
127813,
128180
);



$Titles=array(42324);
$want=array();



$Titles=array(
62642,
63275,
62638
);

$want=array(
128950,

129113,
129114,
129151,
129149,
129112,
129153,


128949,
129115,
129116

);


$Titles=array(63352);
$want=array();


$Titles=array(16085);
$want=array();

// Memoirs and proceedings of the Manchester Literary & Philosophical Society
$Titles=array(9535);
$want=array(111987);


$Titles=array(7519,9535);
$want=array(53694,111987);

$Titles=array(7422);
$want=array();

// Entomologische Nachrichten.
$Titles=array(9698);
$want=array();

$Titles=array(51004);
$want=array(109890);

// Proceedings of the American Philosophical Society held at Philadelphia for promoting useful knowledge
$Titles=array(7023);
$want=array();

$Titles=array(7400);
$want=array();

// Denkschriften der Kaiserlichen Akademie der Wissenschaften / Mathematisch-Naturwissenschaftliche Classe.
$Titles=array(6733);
$want=array();

// Records of the South Australian Museum
$Titles=array(42375);
$want=array();


$Titles=array(50176);
$want=array();


$Titles=array(44302);
$want=array();


$Titles=array(44302);
$want=array();


$Titles=array(6082);
$want=array();


// Revista del Museo de La Plata
$Titles=array(8796);
$want=array();



$Titles=array(8973);
$want=array();

$Titles=array(61164);
$want=array();


// Yale North India Expedition
$Titles=array(9586);
$want=array();


$Titles=array(4627);
$want=array();

// Atoll research bulletin
$Titles=array(50708);
$want=array();


$Titles=array(59381);
$want=array();

// Anales del Museo Nacional de Historia Natural de Buenos Aires
$Titles=array(5597);
$want=array();


$Titles=array(8981,63275);
$want=array(129385,129387,129386);

$Titles=array(6956);
$want=array();

// Zoölogical bulletin
$Titles=array(1792);
$want=array();

// Occasional papers of the Museum of Natural History, the University of Kansas
$Titles=array(4672);
$want=array();

// Copeia and Auk
$Titles=array(15799,15959);
$want=array();


$Titles=array(16190);
$want=array();


$Titles=array(15056);
$want=array();


$Titles=array(2633);
$want=array();


$Titles=array(58805);
$want=array();

// Mitteilungen des Naturwissenschaftlichen Vereines für Steiermark
$Titles=array(42384);
$want=array();

// Mitteilungen der Bayerischen Staatssammlung für Paläontologie und Histor. Geologie
$Titles=array(40815);
$want=array();

//52093

// 
$Titles=array(11392,52093);
$want=array();

//
$Titles=array(6790);
$want=array();


//13352

// Annalen des Naturhistorischen Museums in Wien
$Titles=array(5560);
$want=array();

// 16235


$Titles=array(8597);
$want=array();


$Titles=array(6522);
$want=array();

$Titles=array(6557);
$want=array();

$Titles=array(48691);
$want=array();


$Titles=array(8599,8322,6414);
$want=array();

// Mittheilungen der Schweizer. entomologischen Gesellschaft
$Titles=array(9650);
$want=array();


$Titles=array(51711);
$want=array();


// Mémoires du Muséum d'histoire naturelle.
$Titles=array(50067);
$want=array();

// Societas entomologica.
$Titles=array(8647);
$want=array();


// Transactions of the Geological Society
$Titles=array(52118);
$want=array();

// The Edinburgh new philosophical journal.
$Titles=array(33987);
$want=array();

$Titles=array(49897);
$want=array();

$Titles=array(63773);
$want=array();

$Titles=array(3622);
$want=array(107599);

// Occasional papers of Bernice P. Bishop Museum.
$Titles=array(6999);
$want=array();

$Titles=array(61893);
$want=array(129803);

$Titles=array(60556);
$want=array();



$Titles=array(5124);
$want=array();


$Titles=array(7396);
$want=array();

// Ticks, a monograph of the Ixodoidea
$Titles=array(24074);
$want=array();

// Transactions of the American Entomological Society
$Titles=array(7830);
$want=array();

// Ergebnisse der Hamburger Magalhaenischen Sammelreise
$Titles=array(63772);
$want=array();

// Transactions of the Royal Society of South Australia, Incorporated.
$Titles=array(63906);
$want=array();

// Transactions of the Royal Society of South Australia, Incorporated.
$Titles=array(63905);
$want=array();


$Titles=array(58067);
$want=array();

$Titles=array(10305);
//$want=array(110755);
$want=array(110744);

$Titles=array(46831,45973);
$want=array();

$Titles=array(33377,58331);
$want=array();



$Titles=array(8584);
$want=array();

// Archives entomologiques, ou, Recueil contenant des illustrations d'insectes nouveaux ou rares
$Titles=array(11206);
$want=array();

$Titles=array(10598);
$want=array();

// Recherches pour servir à l'histoire des insectes fossiles des temps primaires
$Titles=array(34754);
$want=array();

// Les Insectes fossiles des terrains primaires
$Titles=array(36372);
$want=array();

// University of California publications in zoology
$Titles=array(42552);
$want=array();

// Die geographische Verbreitung der Oligochaeten 2026
// Oligochaeta
// Oligochaeta
$Titles=array(2026, 1108, 11605);
$want=array();


$Titles=array(49772,11164);
$want=array();

// Science
$Titles=array(44793);
$want=array();

$Titles=array(51129);
$want=array();

$Titles=array(10556);
$want=array();

// Abhandlungen der Königlichen Akademie der Wissenschaften zu Berlin
$Titles=array(41825);
$want=array();

// Compte rendu des séances de la Société de physique et d'histoire naturelle de Genève
$Titles=array(59528);
$want=array();

$Titles=array(58640);
$want=array(130017);

// Miscellaneous publication - University of Kansas, Museum of Natural History
$Titles=array(4050);
$want=array();

// Boletim da Sociedade Broteriana.
$Titles=array(5931,7952,7861);
$want=array();

// Atlas zu der Reise im nördlichen Afrika
$Titles=array(53779);
$want=array();

// Occasional papers / Tulane University, Museum of Natural History
$Titles=array(6365);
$want=array();

$Titles=array(53882);
$want=array(130257);



$Titles=array(52227);
$want=array();

$Titles=array(46639);
$want=array(130382); 

$Titles=array(48522);
$want=array(112817);

$Titles=array(48522);
$want=array(120607);

// Checklist of Palaearctic and Indian mammals 1758 to 1946 /
$Titles=array(8595);
$want=array();


$Titles=array(32671,44848);
$want=array();


$Titles=array(2471,50546, 50608,50611,50303,50606);
$want=array();

$Titles=array(43408);
$want=array(106450);

$Titles=array(64575,7533,50688);
$want=array();

// Jordfundne og nulevende gnavere (Rodentia) fra Lagoa Santa, Minas Geraes, Brasilien :
$Titles=array(14696);
$want=array();

$Titles=array(14696,52062,12195);
$want=array();

$Titles=array(39807);
$want=array();

$Titles=array(46639,5939);
$want=array(131321,131327,131326,131325,131324,131323,131322,131288,131289);

//$Titles=array(64840);
//$want=array();

$Titles=array(50688);
$want=array(
131280,
131394,
131395,
131396,
131397,
131398,
131399,
131400,
131401,
131402,
131403,
131404,
131405,
131406,
131407,
131408,
131409,
131410
);

$Titles=array(46191);
$want=array();


$Titles=array(9578);
$want=array();


$Titles=array(39672);
$want=array();

$Titles=array(10305);
$want=array(110745);

$Titles=array(2461);
$want=array();

$Titles=array(2001);
$want=array();



$Titles=array(7541);
$want=array();

// to do
// Smithsonian contributions to the marine sciences
$Titles=array(64787);
$want=array(131277);

// Revista chilena de historia natural.
$Titles=array(14373);
$want=array();


// Verhandlungen der Ornithologischen Gesellschaft in Bayern.
$Titles=array(8608);
$want=array();

// Annales de la Société Linnéenne de Lyon.
$Titles=array(4372);
$want=array();

// Acta Societatis pro Fauna et Flora Fennica
$Titles=array(13345);
$want=array();

// Bulletin de l'Académie impériale des sciences de St.-Pétersbourg
$Titles=array(49351);
$want=array();

// University studies of the University of Nebraska
$Titles=array(42866);
$want=array();

// Il Naturalista siciliano
$Titles=array(10663);
$want=array();

// Bollettino della Società dei naturalisti in Napoli.
$Titles=array(6440);
$want=array();

$Titles=array(62638);
$want=array(129834);

$Titles=array(62638);
$want=array(129844);

// Anzeiger der Kaiserlichen Akademie der Wissenschaften, Mathematisch-Naturwissenschaftliche Classe
$Titles=array(6335);
$want=array();

// Trabajos del Museo de Ciencias Naturales.
$Titles=array(13508);
$want=array();

// A monograph of the Membracidæ
$Titles=array(34753);
$want=array();

// A monograph of oriental Cicadidæ
$Titles=array(8536);
$want=array();


$Titles=array(49898,49060);
$want=array();

$Titles=array(41515);
$want=array();

// General catalogue of the Hemiptera
$Titles=array(6822);
$want=array();


// Report on the Cephalopoda collected by H. M. S. Challenger during the years 1873-76 /
$Titles=array(46542);
$want=array();


$Titles=array(3622);
$want=array(19534,24060);

// Reise der österreichischen Fregatte Novara
$Titles=array(1597);
$want=array();

// Atti della Società italiana di scienze naturali
$Titles=array(60455);
$want=array();

// A revision of the catsharks, family Scyliorhinidae 
$Titles=array(63029);
$want=array();

$Titles = array(8079);
$want=array();

// British National Antarctic Expedition 
$Titles=array(14923);
$want=array();

// Report of the British Association for the Advancement of Science
$Titles=array(2276);
$want=array();

// The insect world
$Titles=array(50805);
$want=array();

// Hymenoptera of America north of Mexico
$Titles=array(4124);
$want=array();

// Hymenoptera of America north of Mexico : synoptic catalog, first supplement /
$Titles=array(63729);
$want=array();

// Journal of the Straits Branch of the Royal Asiatic Society
$Titles=array(64180);
$want=array();


$Titles=array(43789);
$want=array();


$Titles=array(65905);
$want=array();

// Report on the scientific results of the voyage of H.M.S. Challenger during the years 1873-76
$Titles=array(6513);
$want=array();

// A monograph on the Polychaeta of southern Africa
$Titles=array(8596);
$want=array();

// Annals of the Lyceum of Natural History of New York
$Titles=array(15987);
$want=array();

// Mittheilungen des Münchener Entomologischen Vereins
$Titles=array(12373);
$want=array();

$Titles=array(62852);
$want=array();

$Titles=array(50810,50811,50719,35666,50718);
$want=array();

// Archives des missions scientifiques et littéraires
$Titles=array(50068);
$want=array();

$Titles=array(60779,52084,60745);
$want=array();

// Revision of the Tachinidae of America north of Mexico
$Titles=array(9264,8077);
$want=array();

$Titles=array(66304,63880);
$want=array();


$Titles=array(64180);
$want=array(133377);


$Titles=array(66304,66681,66685,66573);
$want=array();

$Titles=array(65057);
$want=array();


// Abstract of the proceedings of the Linnaean Society of New York.
$Titles=array(51522);
$want=array();

$Titles=array(27080);
$want=array();


$Titles=array(53624);
$want=array();


// Bulletin of the Museum of Comparative Zoology at Harvard College
$Titles=array(2803);
$want=array();

$Titles=array(66841);
$want=array();


$Titles=array(11455);
$want=array();

// Die Asteriden der Siboga-Expedition
$Titles=array(11319);
$want=array();

$Titles=array(16334);
$want=array();

$Titles = array(17344);
$want=array();


$Titles = array(52117);
$want=array();

$Titles = array(10088);
$want=array(89796);

$Titles=array(66841);
$want=array();

$Titles=array(52289);
$want=array(120999);

// Deutsche Südpolar-Expedition, 1901-1903, im Auftrage des Reichsamtes des Innern, hrsg. von Erich von Drygalski.
$Titles=array(2166);
$want = array();

$Titles=array(2290);
$want = array();

$Titles=array(42254);
$want=array(20489);

// Veron
$Titles=array(
60554,
60634,
60555,
60617,
60631,
60646,
60641,
58097
);
$want=array();

$Titles = array(57804);
$want = array();

$Titles = array(22427);
$want = array();

// Memoirs and proceedings of the Manchester Literary & Philosophical Society
$Titles=array(9535);
$want=array(106322);


$Titles = array(5943);
$want = array(27194);

$Titles = array(8097);
$want = array();

// Carnegie Institution of Washington publication
$Titles=array(5800);
$want=array(18234);


// Austral avian record
$Titles = array(49028);
$want = array();

// Die Thiewelt Ost-Afrikas und der Nachbargebiete
$Titles = array(13742);
$want = array();

// Catalogue of the fresh-water fishes of Africa in the British museum (Natural history) 
$Titles = array(8869);
$want = array();

$Titles = array(11454);
$want = array();

$Titles=array(13573);
$want = array();

// Transactions of the Linnean Society of London
$Titles=array(683);
$want=array(13692);

// Acta Societatis pro Fauna et Flora Fennica
$Titles=array(13345);
$want=array(28168);


$Titles=array(48522);
$want=array(111870);


$Titles=array(55341);
$want = array();


$Titles=array(42231);
$want = array();

// Mollusques de l'Afrique équatoriale : de Moguedouchou a Bagamoyo et de Bagamoyo au Tanganika / par m. J.R. Bourguignat.
$Titles=array(12884);
$want = array();

// British journal of entomology and natural history
$Titles=array(42578);
$want = array();

// Mollusques terrestres et fluviatiles
$Titles=array(46557);
$want = array();


$Titles=array(2804);
$want = array(21131);


$Titles=array(48440);
$want = array();


$Titles=array(7519);
$want=array(32569);

$Titles=array(65514);
$want = array();

$Titles=array(52324);
$want = array();


$Titles=array(33987);
$want = array();


$Titles=array(

68439, 

68615,
68616,
68617,
68618,
68619

);

$Titles=array(

68600,
68601,
68602,
68603,

68604,
68605,
68606,

68608,
68609,

68611,
68612,
68613,

68621,
68622,
68624,
68625,
68629,
68630

);



$want = array();

$Titles=array(2511);
$want=array(136745);

$Titles=array(6733);
$want=array(109857);


$Titles=array(8256);
$want=array();

$Titles=array(1599);
$want=array();


$Titles=array(12678);
$want=array();


$Titles=array(8408);
$want=array();


$Titles=array(50810,50811,50719,35666,50718);
$want=array();

$Titles = array(10088);
$want=array(40987);

$Titles=array(12678);
$want=array();

// Mémoires presentés a L'Institut des Sciences, Lettres et Arts,
$Titles=array(4363);
$want=array(23218);

// Revisio generum plantarum
$Titles=array(327);
$want=array();

// Bulletin de la Société impériale des naturalistes de Moscou.
$Titles=array(4951);
$want=array();

$Titles=array(68619);
$want=array();


$Titles=array(68672);
$want=array();


$Titles=array(11807);
$want=array();


//Mission du Service Geographique de l'Armée pour la mesure d'un arc de méridien équatorial en Amérique du Sud sous le contrôle scientifique de l'Académie des Sciences, 1899-1906
$Titles=array(980);
$want=array();


// Journal of the Academy of Natural Sciences of Philadelphia
$Titles=array(34362);
$want=array();

// Veliger
$Titles=array(69283);
$want=array();


$Titles=array(69296);
$want=array();

// Emu
$Titles=array(16355);
$want=array();

// Revue internationale des sciences
$Titles=array(14371);
$want=array();

$Titles=array(13710);
$want=array();

// Entomologiske meddelelser
$Titles=array(11664);
$want=array();

$Titles=array(61820);
$want=array();


$Titles=array(13307);
$want=array(49499);

$Titles=array(7422);
$want=array();

$Titles=array(42254);
$want=array();

$Titles=array(3622);
$want=array(107520);

//-----

// Zeitschrift für Entomologie
$Titles=array(11685);
$want=array();

// Zeitschrift für wissenschaftliche Zoologie
//$Titles=array(67398);
//$want=array();

// $Titles=array(68619);
// $want=array(138516,138515,138514,138513);


// Done
//$Titles=array(61449);
//$want=array(138500,138499);

//$Titles=array(69641);
//$want=array();

// Revue suisse de zoologie
//$Titles=array(69643);
//$want=array();



$Titles=array(6366,69641,69637,15728);
$want=array();

$Titles=array(53760);
$want=array();

// Casopis Ceské spolecnosti entomologické
$Titles=array(11932,14009);
$want=array();


$Titles=array(15503);
$want=array();

// Bulletins of American paleontology
$Titles=array(39837);
$want=array();

$Titles = array(68022);
$want=array();


$Titles = array(69643);
$want=array(138635);

// to do
$Titles = array(68619,68672,67398);
$want=array();

// to do
$Titles = array(44478);
$want=array();


$Titles = array(42204);
$want=array();

// Catalogue of the lizards in the British museum (Natural history)
$Titles = array(53974);
$want=array();


$Titles = array(21097);
$want=array();


$Titles = array(14019);
$want=array();


$Titles=array(2744);
$want=array();


$Titles=array(16008);
$want=array();


$Titles=array(10427,23702,10240);
$want=array();

$Titles=array(10920);
$want=array();

$Titles=array(68439,67398,68672);
$want=array();


$Titles=array(46541);
$want=array();


$Titles=array(9698);
$want=array();

$Titles=array(6369,15291);
$want=array();



$Titles=array(49864,49876,49727);
$want=array();

// Trudy Russkago entomologicheskago obshchestva. Horae Societatis entomologicae rossicae, variis semonibus in Russia usitatis editae
$Titles=array(12032);
$want=array();


$Titles=array(3622);
$want=array(107743);


$Titles=array(66841);
$want=array();


$Titles=array(14206);
$want=array();

$Titles=array(47024);
$want=array();


$Titles=array(14939);
$want=array();


$Titles=array(45410);
$want=array();


$Titles=array(15952);
$want=array();

// Iheringia. Série zoologia.
$Titles=array(50228);
$want=array();

$Titles=array(3622);
$want=array(107523);


$Titles=array(62169);
$want=array();


$Titles=array(14678);
$want=array();

$Titles = array(51678);
$want=array();



$Titles = array(42256);
$want=array(21502);


$Titles=array(45481);
$want=array();


$Titles=array(45022);
$want=array();

$Titles=array(61449);
$want=array();


$Titles=array(65730);
$want=array();


$Titles=array(69286);
$want=array();


$Titles=array(62170,10719,49690);
$want=array();

$Titles=array(7352);
$want=array();

$Titles=array(6870);
$want=array();

$Titles=array(48522);
$want=array();

$Titles=array(15811,15282,15812,15814,15283,15291);
$want=array();

$Titles=array(12019);
$want=array();


$Titles=array(63990);
$want=array();

// Revista del Museo de La Plata
$Titles=array(8796);
$want=array();


$Titles=array(5766);
$want=array();


$Titles=array(13258,16329);
$want=array();


$Titles=array(6516);
$want=array();


$Titles=array(39837);
$want=array();


$Titles=array(66841);
$want=array();

$Titles=array(31560);
$want=array();


$Titles=array(28679);
$want=array();


$Titles=array(15993);
$want=array();



$Titles=array(2035);
$want=array();

$Titles=array(12269);
$want=array();


$Titles=array(53710);
$want=array();


$Titles=array(480);
$want=array();

$Titles=array(66716,70709);
$want=array();

$Titles=array(54131,20903);
$want=array();


$Titles=array(7371);
$want=array();


$Titles=array(53661);
$want=array();



$Titles=array(38930);
$want=array();

$Titles=array(51416);
$want=array();


$Titles=array(40487);
$want=array();



$Titles=array(4050);
$want=array(130018);


$Titles=array(5376,1597,9173);
$want=array();

// Journal of Economic Ebtomology
$Titles=array(9028);
$want=array();


// Journal of ethnobiology
$Titles=array(47071);
$want=array();


$Titles=array(57881);
$want=array();

// Iberus : revista de la Sociedad Española de Malacología
$Titles=array(49914);
$want=array();

$Titles=array(6525);
$want=array(123081);


$Titles=array(15380);
$want=array();

// Gayana. Botánica.
$Titles=array(40896);
$want=array();

// Contributions from the United States National Herbarium
$Titles=array(687);
$want=array();


// Botanische Jahrbücher für Systematik, Pflanzengeschichte und Pflanzengeographie
$Titles=array(68683);
$want=array();

// Bulletin of the Natural History Museum.
$Titles=array(53883);
$want=array();

//$Titles=array(12678);
//$want=array();



$Titles=array(2198);
$want=array();

// Novon
$Titles=array(744);
$want=array();


// Broteria, Neue Beiträge zur systematischen Insektenkunde, 
// Sitzungsberichte der Gesellschaft Naturforschender Freunde zu Berlin
$Titles=array(70839,70893,7922);
$want=array();

// Annals of the Royal Botanic Garden, Calcutta. NOT DJVU :(
$Titles=array(16621);
$want=array();


$Titles=array(61808);
$want=array();

$Titles=array(58574,13797,35877);
$want=array();

// Journal de conchyliologie.
$Titles=array(14924);
$want=array();



$Titles=array(49077);
$want=array();

$Titles=array(49442);
$want=array();


$Titles=array(7928);
$want=array(33386);

$Titles=array(69698);
$want=array();


$Titles=array(7922);
$want = array(148305);


$Titles=array(68672);
$want=array();

$Titles=array(68619);
$want =array(148209);


$Titles=array(77408);
$want=array();

$Titles=array(7922);
$want = array(148030);


$Titles=array(70893,77357,7056,77306);
$want=array();


$Titles=array(12260);
$want = array();

$Titles=array(45401);
$want = array();

$Titles=array(8115);
$want = array();


$Titles=array(59799);
$want = array();


$Titles=array(2087);
$want = array();

$Titles=array(42247,42246);
$want = array();

// Bulletin de la Société impériale des naturalistes de Moscou.
$Titles=array(4951);
$want=array();

$Titles=array(4683);
$want=array();

// Ruwenzori Expedition 1934-1935
$Titles=array(68672);
$want=array();


$Titles=array(63894);
$want=array();

// Botanische Zeitung
// Bulletin du Muséum National d'Histoire Naturelle Section B,Adansonia, botanique, phytochimie
// Botanische Jahrbücher fur Systematik, Pflanzengeschichte und Pflanzengeographie (see also 68683 )
$Titles=array(4948,13855,60);
$want=array();

// Arnaldoa : revista del Herbario HAO
// Rhodora
// SIDA, contributions to botany
// Wrightia
// Phytoneuron
$Titles=array(61808,721,8113,895,61654);
$want=array();

$Titles=array(53008);
$want=array();

// Bulletin de l'Herbier Boissier
$Titles=array(49730);
$want=array();

// Notulae systematicae 0374-9223
$Titles=array(314);
$want=array();

// Repertorium specierum novarum regni vegetabilis
// Repertorium specierum novarum regni vegetabilis. Beihefte
$Titles=array(276,6505);
$want=array();

// Revue suisse de zoologie

$Titles=array(8981,77508,60556);
$want=array();

// Annales de la Société entomologique de Belgique
$Titles=array(11933);
$want=array(110226);

$Titles=array(7922);
$want=array();

$Titles=array(15369);
$want=array();

// Oesterreichische botanische Zeitschrift
$Titles=array(40988);
$want=array();

// Catalogus Hymenopterorum hucusque descriptorum systematicus et synonymicus 
$Titles=array(10348);
$want=array();

// Bothalia
$Titles=array(66550);
$want=array();


// Journal of botany :being a second series of the Botanical miscellany.
// Journal of botany, British and foreign
// Journal of Indian botany
// The London journal of botany : containing figures and descriptions of such plants as recommend themselves by their novelty, rarity, history, or uses : together with botanical notices and information and and occasional portraits and memoirs of eminent botanists.
$Titles=array(234,15787,9050,235);
$want=array();

$Titles=array(8981);
$want=array(
148878,
148880,
148881,
148884,
148885,
148886,
148888,
148889,
149462,
149483,
149492,
149506,
149509,
149511
);


$Titles=array(9197);
$want=array(133906,133890);

$Titles=array(359);
$want = array();


$Titles=array(12310);
$want = array();

$Titles=array(2276,50608);
$want = array();


$Titles=array(70397);
$want = array();



$Titles=array(51127);
$want = array();


// Proceedings of the Entomological Society of Ontario
$Titles=array(70397);
$want = array(139960);


$Titles=array(77508);
$want=array();



$Titles=array(11810);
$want=array();


$Titles=array(34934);
$want=array();



$Titles=array(52116);
$want=array();

$Titles=array(8740);
$want=array(37358);
	
$Titles=array(10663);
$want=array();

$Titles=array(15727);
$want=array();


$Titles=array(15832);
$want=array();

$Titles=array(52237);
$want=array();


$Titles=array(10923);
$want=array();


$Titles=array(62901);
$want=array();

// Journal für Ornithologie
$Titles=array(47027);
$want=array();

// Geological magazine
$Titles=array(44230);
$want=array();

// Aquila
$Titles=array(8350);
$want=array();


$Titles=array(717);
$want=array();


$Titles=array(12187);
$want=array();


$Titles=array(42);
$want=array();

// 

$Titles = array(
	1076,58689
	);

$Titles=array(46530);

$Titles=array(79076);

$Titles=array(50489);
$Titles=array(63275);
$Titles=array(42231);

$Titles=array(9197);

$Titles=array(8149);

$Titles=array(3220);

$Titles=array(6337);

$Titles=array(58103);

$Titles=array(52840);

$Titles=array(12292,64405);

$Titles=array(13271);

$Titles=array(41507);

$Titles=array(3622);
$want = array(24077);

// Anales del Museo Nacional de Chile
$Titles=array(51049);
$want = array();



$Titles=array(50242);
$want = array();

$Titles=array(79357);
$want = array();



$Titles=array(13324);
$want = array();


$Titles=array(40761);
$want = array();


$Titles=array(10705);
$want = array();

$Titles=array(44570);
$want = array();


$Titles=array(11977);
$want = array();


$Titles=array(6733);
$want = array(31609);


$Titles=array(60982);
$want = array(124754);

$Titles=array(13353);
$want = array();


$Titles=array(79640);
$want = array();


$Titles=array(59335);
$want = array();



$Titles=array(9578);
$want = array();

// Catalogue of Lepidoptera Phalaenae in the British Museum
$Titles=array(21046);
$want = array();

// Catalogue of Lepidoptera Phalaenae in the British Museum
$Titles=array(9243);
$want = array();


$Titles=array(47000);
$want = array();

// Marquesan insects
$Titles=array(82130);
$want = array();

// 

$Titles=array(55607);
$want = array();



$Titles=array(51973);
$want = array();



$Titles=array(6733);
$want = array(110762);


// Det Kongelige Danske videnskabernes selskabs skrifter.
$Titles=array(7155);
$want = array();


// Izviestiia Imperatorskoi akademii nauk = Bulletin de l'Académie impériale des sciences de St.-Pétersbourg.
$Titles=array(42575);
$want = array();



$Titles=array(7408);
$want = array();


$Titles=array(8763);
$want = array();



$Titles=array(11824);
$want = array();



$Titles=array(15416);
$want = array();


$Titles=array(6082);
$want = array();


$Titles=array(62508);
$want = array();


$Titles=array(46350);
$want = array();


$Titles=array(2179);
$want = array();


$Titles=array(42570);
$want = array();


$Titles=array(7414);
$want = array(156167);

$Titles=array(8279);
$want = array();

$Titles=array(9704,39891,69282,14640);
$want = array();

// Jahreshefte des Vereins für vaterländische Naturkunde in Württem
$Titles=array(7923);
//$want=array(33517);
$want = array();


$Titles=array(7541);
$want = array(32297);

// new 
$Titles=array(82449,63274,81674);
$want = array();


$Titles=array(4694);
$want = array();




$Titles=array(101249);
$want = array();


$Titles=array(15900);
$want = array();



$Titles=array(6512);
$want = array();



$Titles=array(82240);
$want = array();


// Bonn zoological bulletin
// 0302-671X Bonner zoologische Monographien
$Titles=array(82521,82295);
$want = array();

/*
$Titles=array(82240);
$want=array(
156592,
156605,
156622,
156668,
157020
);
*/

$Titles=array(42310);
$want = array(133652);

$Titles=array(82521);
$want = array(156976);


$Titles=array(14581);
$want = array();


$Titles=array(12566);
$want = array();


$Titles=array(82296);
$want = array();



$Titles=array(14671);
$want = array();

$Titles=array(5746);
$want = array();

$Titles=array(82521,82295,83593,83566,81674);
$want = array(

159038,

159026,
159032,
159041,


158986,

158898,

159055,
158972

);

$Titles=array(82240);
$want=array(159045);


$Titles=array(683);
$want=array(13715);


$Titles=array(59815);
$want=array();


$Titles=array(12914);
$want=array();


$Titles=array(58475);
$want=array();

// 130568
$Titles=array(2290);
$want=array();


$Titles=array(7547);
$want=array();

$Titles=array(6733);
$want=array();

// Rendiconti
$Titles=array(81596);
$want=array();


$Titles=array(82240);
$wants=array(159300);


$Titles=array(2185);
$wants=array();

$Titles=array(10240);
$wants=array();


$Titles=array(64047);
$wants=array();


// Watsonia
$Titles=array(83809);
$want=array();

$Titles=array(82240);
$wants=array(159808);
$want=array();


$Titles=array(16083);
$want=array();

// Memoirs of the San Diego Society of Natural History
$Titles=array(3234);
$want=array();

// Bulletin de l'Institut botanique de Buitenzorg
$Titles=array(4865);
$want=array();


// The British fern gazette
$Titles=array(5824);
$want=array();


$Titles=array(43746);
$want=array();


// Contributions from the University of Michigan Herbarium
$Titles=array(12411);
$want=array();



$Titles=array(894);
$want=array();

// Leaflets of Philippine botany
$Titles=array(259);
$want=array();

// Bulletin du Muséum National d'Histoire Naturelle Sér. 3, Botanique
$Titles=array(12908);
$want=array();

// Flora Vitiensis nova
$Titles=array(44033);
$want=array();

// Flora Malesiana.
$Titles=array(40744);
$want=array();


$Titles=array(53007); // 2
$want=array();


$Titles=array(13855);
$want=array();


$Titles=array(
53005,  // 4
53006, // 1
53009, // 6
53024,
53031,
53032,
53034,
53035,
53043,
53044,
53047,
53048,
53049,
53050,
53051,
53053,
53055,
53056,
53062,
53064,
83589
);
$want=array();


$Titles=array(60455);
$want=array();

$Titles=array(51603);
$want=array(151941);


$Titles=array(61097);
$want=array();


$Titles=array(66304);
$want=array();


$Titles=array(77508);
$want=array();


$Titles=array(77386);
$want=array();

$Titles=array(50688);
$want=array();


$Titles=array(66071);
$want=array();




$Titles=array(60996);
$want=array();


$Titles=array(85187);
$want=array();


$Titles=array(66573);
$want=array();


$Titles=array(53331);
$want=array();

// latest is 163341
$Titles=array(85187);
$want=array(163746,
163747,
163993,
163994
);

$Titles=array(15728);
$want=array();


$Titles=array(62140);
$want=array();




$Titles=array(50818);
$want=array();

$Titles=array(8981);
$want=array(129621);


$Titles=array(8577);
$want=array();



$Titles=array(46639);
$want=array(164184);

$Titles=array(64405);
$want=array();

// to do
$Titles=array(
86994,
86995,
87009,
87018,
87017
);
$want=array();

$Titles=array(16197);
$want=array(129831);


$Titles=array(7537);
$want=array(25689,91624);

// Indiana Studies
$Titles=array(84823);
$want=array();

$Titles=array(7023);
$want=array();


$Titles=array(7541);
$want=array(48122);


$Titles=array(86350);
$want=array();

$Titles=array(53542);
$want=array();


$Titles=array(9494,85187,87617);
$want=array(
38542,
164993,
164987,
87620,
164984,
164940
);

$Titles=array(7411);
$want=array();


$Titles=array(57946);
$want=array();


$Titles=array(7063);
$want=array();

$Titles=array(79165);
$want=array();

$Titles=array(12266);
$want=array();


$Titles=array(59881);
$want=array();


$Titles=array(15800);
$want=array();


$Titles=array(5550);
$want=array();


$Titles=array(48720);
$want=array();


$Titles=array(47027);
$want=array();


$Titles=array(68650);
$want=array();


$Titles=array(40777);
$want=array();


$Titles=array(77508);
$want=array(172166);


$Titles=array(94437);
$want=array();

$Titles=array(3989);
$want=array(155138);


$Titles=array(
94769,
94759,
94758
);
$want=array();


$Titles=array(61272);
$want=array();


$Titles=array(3882);
$want=array(22863);


$Titles=array(15283);
$want=array();


$Titles=array(94758);
$want=array();



$Titles=array(95222);
$want=array();



$Titles=array(94758,94759,94769,86350);
$want=array(173089,173245,173085,173086,173243,173088);


$Titles=array(12203);
$want=array();

$Titles=array(58221);
$want=array();

$Titles=array(94758);
$want=array(173329);


$Titles=array(9007,27867);
$want=array();

$Titles=array(52133);
$want=array();


$Titles=array(8740);
$want=array(37388);


$Titles=array(16093,46992,61132);
$want=array();


$Titles=array(62798);
$want=array();


$Titles=array(14183);
$want=array();


$Titles=array(46722);
$want=array();

$Titles=array(50545);
$want=array(111325);


$Titles=array(94758,87620);
$want=array(173338,173344);

$Titles=array(8646);
$want=array();

$Titles=array(68262);
$want=array();


$Titles=array(10011);
$want=array();

$Titles=array(7519);
$want=array(53447,31809);



$Titles=array(10641);
$want=array();


$Titles=array(14082);
$want=array();


$Titles=array(69);
$want=array(89402);


$Titles=array(58534);
$want=array();


$Titles=array(79636);
$want=array();

// 
$Titles=array(683);
$want=array(13691);

$Titles=array(94759);
$want=array(173697,173722,173723);


$Titles=array(77508);
$want=array();


$Titles=array(58803);
$want=array();


$Titles=array(5473);
$want=array();


$Titles=array(11813);
$want=array();


$Titles=array(96150,52324);
$want=array();


$Titles=array(86329);
$want=array();


$Titles=array(51282);
$want=array();

// 
$Titles=array(50226);
$want=array();

$Titles=array(16172);
$want=array();


$Titles=array(86350);
$want=array(174370);

$Titles=array(51990);
$want=array();


$Titles=array(12276,12260,13390);
$want=array();


$Titles=array(2172);
$want=array();


$Titles=array(13508);
$want=array();


$Titles=array(8815);
$want=array();


$Titles=array(64180);
$want=array();


$Titles=array(8233);
$want=array();

$Titles=array(69819);
$want=array();


$Titles=array(82240);
$want=array(159300);


$Titles=array(50754);
$want=array();

$Titles=array(42579);
$want=array();


$Titles=array(48540);
$want=array();



$Titles=array(5595);
$want=array();


$Titles=array(8237);
$want=array();



$Titles=array(12938);
$want=array();



$Titles=array(44718,45848,44719,9580);
$want=array();


$Titles=array(8408);
$want=array();


$Titles=array(5652);
$want=array();

$Titles=array(8323);
$want=array();

$Titles=array(9535);
$want=array(39417);


$Titles=array(2185);
$want=array();



$Titles=array(7415);
$want=array(22203);


$Titles=array(42256);
$want=array(20739);


$Titles=array(13677);
$want=array();




$Titles=array(10088);
$want=array(40988);



$Titles=array(16197);
$want=array(129820);


$Titles=array(29427);
$want=array();


$Titles=array(5384);
$want=array();

$Titles=array(15774);
$want=array(53364);


$Titles=array(11627);
$want=array();

$Titles=array(11627);
$want=array();

// to do

$Titles=array(98298);
$want=array();

//$Titles=array(77508);
//$want=array(176773);

//$Titles=array(2185);
//$want=array(176801);


$Titles=array(12292);
$want=array();



$Titles=array(2290);
$want=array(130558);



$Titles=array(6513);
$want=array();


$Titles=array(23582);
$want=array();


$Titles=array(10413);
$want=array();


$Titles=array(2167);
$want=array();


$Titles=array(8981);
$want=array(177005);



$Titles=array(7952);
$want=array();


$Titles=array(13855,359,7533);
$want=array();

$Titles=array(6600);
$want=array();

// Memoires de la Société de physique et d'histoire naturelle de Genève
$Titles=array(13710);
$want=array();



$Titles=array(98946);
$want=array();

$Titles=array(49512);
$want=array(177708);


$Titles=array(50009);
$want=array();

$Titles=array(13477);
$want=array();

$Titles=array(98900);
$want=array();

$Titles=array(98362);
$want=array();


$Titles=array(6622);
$want=array();


$Titles=array(66841);
$want=array(134672);


$Titles=array(43746);
$want=array(178977);

//$Titles=array(99905,99906);
//$want=array();


$Titles=array(52324);
$want=array(175859);



$Titles=array(9191);
$want=array();

$Titles=array(5550);
$want=array(111256);


$Titles=array(45190);
$want=array();


$Titles=array(15061);
$want=array();


$Titles=array(79076);
$want=array(179105,179107);


$Titles=array(6366);
$want=array(112852);



$Titles=array(5943);
$want=array(27175);

$Titles=array(50688);
$want=array(131396);


$Titles=array(100766);
$want=array();


$Titles=array(79076);
$want=array();


$Titles=array(43746);
$want=array();


$Titles=array(2087);
$want=array(122833);

$Titles=array(50688);
$want=array(131400);


$Titles=array(100809);
$want=array(180274);

$Titles=array(100875);
$want=array();


$Titles=array(42253);
$want=array(25053);


$Titles=array(11627);
$want=array();

$Titles=array(7519);
$want=array(53488);

// 
$Titles=array(101455);
$want=array();

$Titles=array(101455);
$want=array(181475);



$Titles=array(6525);
$want=array(30437);


$Titles=array(101603);
$want=array();


$Titles=array(51678);
$want=array(137988);

$Titles=array(51678);
$want=array();

$Titles=array(21368);
$want=array(61592);


$Titles=array(61132,50104);
$want=array();


$Titles=array(52226);
$want=array();

$Titles=array(46639);
$want=array(131341);


$Titles=array(43405);
$want=array();

$Titles=array(1488);
$want=array();


$Titles=array(8608);
$want=array(101651);


$Titles=array(102724,7414);
$want=array(182902,182875,182873);


$Titles=array(14261);
$want=array();

$Titles=array(9494);
$want=array(84613);

// BZN
$Titles=array(51603);
$want=array(44460);

$Titles=array(48540);
$want=array(83343);

$Titles=array(5551,9494);
$want=array(28068,38347);

// Bombay
$Titles=array(7414);
$want=array();

// Madroño; a West American journal of botany.
$Titles=array(65344);
$want=array();

$Titles=array(9197);
$want=array(133890,133906);


$Titles=array(7519);
$want=array(32327);

// Manual of the New Zealand Coleoptera
$Titles=array(9559);
$want=array();


foreach ($Titles as $TitleID)
{
 //$want=array();

$use_bhl_au = false;
//$use_bhl_au = true;

if ($use_bhl_au)
{
	// BHL AU
	$url = 'http://bhl.ala.org.au/api/rest?op=GetTitleMetadata&titleid=' . $TitleID . '&items=true&apikey=' . $config['bhl_api_key'] . '&format=json';
}
else
{
	// BHL
	$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetTitleMetadata&titleid=' . $TitleID . '&items=true&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';
}

//echo $url . "\n";

$json = get($url);
$title_obj = json_decode($json);

//print_r($title_obj);
//exit();



if ($title_obj->Status == 'ok')
{

	$sql = "REPLACE INTO bhl_title(TitleID, FullTitle, ShortTitle) VALUES ("
		. $title_obj->Result->TitleID . ",'" . addslashes($title_obj->Result->FullTitle) . "','" . addslashes($title_obj->Result->ShortTitle) . "');";
	echo $sql . "\n";

	foreach ($title_obj->Result->Items as $item_obj)
	{
		$go = false;
		if (count($want) == 0)
		{
			$go = true;
		}
		else
		{
			if (in_array($item_obj->ItemID, $want))
			{
				$go = true;
			}
		}
		if ($go)
		{
		
			if ($use_bhl_au)
			{
				$url = 'http://bhl.ala.org.au/api/rest?op=GetItemMetadata&itemid=' . $item_obj->ItemID . '&pages=t&apikey=' . $config['bhl_api_key'] . '&format=json';
			}
			else
			{
				$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetItemMetadata&itemid=' . $item_obj->ItemID . '&pages=t&apikey=' . '0d4f0303-712e-49e0-92c5-2113a5959159' . '&format=json';
			}
			
			$json = get($url);
			
			$obj = json_decode($json);
			
			//print_r($obj);
			//exit();
			
			if (($obj->Status == 'ok') && ($obj->Result != '')) // ok doesn't mean we have anything :(
			{
				
				$djvu_string .= "'" . $obj->Result->ItemID . "' => '" .  $obj->Result->SourceIdentifier . "',\n";
			
				
				// Item
				$sql = "DELETE FROM bhl_item WHERE ItemID=" . $obj->Result->ItemID . ";";
				echo $sql . "\n";
				
				$sql = "INSERT INTO bhl_item(ItemID,TitleID,VolumeInfo) VALUES("
				. $obj->Result->ItemID . ',' . $obj->Result->PrimaryTitleID . ",'" . addslashes($obj->Result->Volume) . "');";
				
				echo $sql . "\n";
				
				$sql = 'DELETE FROM bhl_page WHERE ItemID=' . $obj->Result->ItemID . ";";
				echo $sql;
		
		
				// Pages
				foreach ($obj->Result->Pages as $k => $v)
				{
					// Metadata about pages
					$keys = array();
					$values = array();
					
					// PageID
					$keys[] = 'PageID';
					$values[] = $v->PageID;
					
					// ItemID
					$keys[] = 'ItemID';
					$values[] = $v->ItemID;
			
					// Is page numbered?
					if (count($v->PageNumbers) > 0)
					{
						$keys[] = 'PagePrefix';
						$values[] = '"' . $v->PageNumbers[0]->Prefix . '"';
			
						$keys[] = 'PageNumber';
						$values[] = '"' . $v->PageNumbers[0]->Number . '"';
					}
			
					if (count($v->PageTypes) > 0)
					{
						$keys[] = 'PageTypeName';
						$values[] = '"' . $v->PageTypes[0]->PageTypeName . '"';
					}
					//$sql = 'DELETE FROM bhl_page WHERE PageID=' . $v->PageID . ';';
					//echo $sql . "\n";
					$sql = 'INSERT INTO bhl_page (' . implode (",", $keys) . ') VALUES (' . implode (",", $values) . ');';
					echo $sql . "\n";
				
				
					// Order of pages
					// pages has PageID as primary key
					$sql = 'REPLACE INTO page (PageID,ItemID,FileNamePrefix,SequenceOrder) VALUES ('
						.        $v->PageID
						. ','  . $v->ItemID
						. ',"' . $obj->Result->SourceIdentifier . sprintf("_%04d",  ($k+1)) . '"'
						. ','  . ($k+1)
						. ');';
						
					echo $sql . "\n";
						
				} 
			}
		}
	}	
}

}

file_put_contents($TitleID . 'djvu.txt', $djvu_string);

?>