<?php

require_once('../config.inc.php');
require_once('../db.php');

$items=array(
'56621' => 'danishingolfexpe06daniuoft',
'62448' => 'danishingolfex3cpt8daniuoft',
'56619' => 'danishingolfex5cpt9daniuoft',
'62443' => 'danishingolfex1pt1a2daniuoft',
'62444' => 'danishingolfex2apt1a5daniuoft',
'62445' => 'danishingolfex2bpt6a7daniuoft',
'62449' => 'danishingolfex3dpt13daniuoft',
'62446' => 'danishingolfex3apt1a4daniuoft',
'62447' => 'danishingolfex3bpt5a7daniuoft',
'56616' => 'danishingolfex4cpt11daniuoft',
'56615' => 'danishingolfex4apt1a2daniuoft',
'56620' => 'danishingolfex5dpt12daniuoft',
'56617' => 'danishingolfex5apt1a4daniuoft',
'56618' => 'danishingolfex5bpt5a8daniuoft'
);

//$items=array('62449' => 'danishingolfex3dpt13daniuoft');

/*
$items=array(
'111977' => 'publicationsinna11nati',
'111781' => 'publicationsinna21nati',
'111577' => 'publicationsinna31nati',
'111567' => 'publicationsinna41nati',
'111572' => 'publicationsinna51nati',
'111563' => 'publicationsinna61nati'
);
*/

$items=array('89796' => 'tijdschriftvoore1938nede');

$items=array(
'134373' => 'veliger331990cali',
'134482' => 'veliger341991cali',
'134377' => 'veliger351992cali',
'134374' => 'veliger361993cali',
'134372' => 'veliger371994cali',
'134376' => 'veliger381995cali',
'134483' => 'veliger401997cali',
'134378' => 'veliger411998cali',
'134364' => 'veliger421999cali',
'134363' => 'veliger432000cali',
'134129' => 'veliger519621963cali',
'134153' => 'veliger619631964cali',
'134249' => 'veliger719641965cali',
'134168' => 'veliger1019671968cali',
'134126' => 'veliger1119681969cali',
'134248' => 'veliger1319581961cali',
'134167' => 'veliger1419711972cali',
'134124' => 'veliger1519721973cali',
'134247' => 'veliger1619731974cali',
'134154' => 'veliger1919761977cali',
'134250' => 'veliger2019771978cali',
'134165' => 'veliger2319801981cali',
'134152' => 'veliger2419811982cali',
'134166' => 'veliger2619831984cali',
'134487' => 'veliger2719841985cali',
'134485' => 'veliger2819851986cali',
'134379' => 'veliger3019871988cali'
);

$items=array('120999' => 'zoologischeerge03webe');

$items=array(
'41409' => 'deutschesdpola02deut',
'29202' => 'deutschesdpola09deut',
'29067' => 'deutschesdpola10deut',
'30760' => 'deutschesdpola11deut',
'41929' => 'deutschesdpola1911deut',
'41931' => 'deutschesdpola1210deut',
'31195' => 'deutschesdpola12deut',
'18731' => 'deutschesdpola13deut',
'41930' => 'deutschesdpola1311deut',
'41846' => 'deutschesdpola81deut',
'41847' => 'deutschesdpola82deut',
'41897' => 'deutschesdpola83deut',
'29849' => 'deutschesdpola1401deut',
'18639' => 'deutschesdpola15deut',
'18599' => 'deutschesdpola16deut'
);

//$items=array('18639' => 'deutschesdpola15deut');

$items=array(
'19839' => 'transactionsofro01roya',
'125778' => 'transactionsofro02murr',
'125769' => 'transactionsofro03murr',
'125781' => 'transactionsofro04murr',
'125770' => 'transactionsofro05murr',
'125780' => 'transactionsofro06murr',
'125779' => 'transactionsofro07murr',
'125985' => 'transactionsofro08roya',
'125966' => 'transactionsofro09roya',
'19725' => 'transactionsofro10roya',
'19836' => 'transactionsofro11roya',
'19874' => 'transactionsofro12roya',
'125980' => 'transactionsofro13royal',
'126259' => 'transactionsofro14royal',
'19892' => 'transactionsofro15roya',
'126152' => 'transactionsofro16roy',
'126258' => 'transactionsofro171roya',
'126153' => 'transactionsofro18roya',
'126048' => 'transactionsofro191roya',
'126047' => 'transactionsofro20royal',
'126375' => 'transactionsofro21royal',
'126380' => 'transactionsofro22royal',
'126377' => 'transactionsofro23royal',
'126499' => 'transactionsofro24roya',
'126701' => 'transactionsofro25royal',
'126703' => 'transactionsofro26royal',
'126705' => 'transactionsofro27royal',
'126566' => 'transactionsofro28roya',
'129312' => 'transactionsofro291880roy',
'129311' => 'transactionsofro301883roy',
'129313' => 'transactionsofro311888roy',
'129320' => 'transactionsofro321887roy',
'129321' => 'transactionsofro331888roy',
'129401' => 'transactionsofro341890roy',
'129362' => 'transactionsofro3511889ro',
'129394' => 'transactionsofro3521890ro',
'129395' => 'transactionsofro3531890ro',
'129360' => 'transactionsofro3541890ro',
'129570' => 'transactionsofro36189192r',
'129440' => 'transactionsofro37189395r',
'129472' => 'transactionsofro38189697r',
'129441' => 'transactionsofro391898190',
'130139' => 'transactionsofro40roya',
'130137' => 'transactionsofro41roya',
'130138' => 'transactionsofro42roya',
'130551' => 'transactionsofro43roya',
'130569' => 'transactionsofro44roya',
'130552' => 'transactionsofro45roya',
'130380' => 'transactionsofro46roya',
'130381' => 'transactionsofro47roya',
'130568' => 'transactionsofro48roya',
'130558' => 'transactionsofro49roya',
'130557' => 'transactionsofro50roya',
'130747' => 'transactionsofro51roya'
);

$items=array('130557' => 'transactionsofro50roya');

$items=array('20489' => 'contributiontoic36meek');

$items=array(
'123245' => 'BiogeographicDa00Vero',
'123354' => 'Hermatypiccoral00JENV',
'123246' => 'Hermatypiccoral00Vero',
'123325' => 'ScleractiniaEas00JENVB',
'123348' => 'ScleractiniaEas00JENV',
'123393' => 'ScleractiniaEas00JENVD',
'123353' => 'ScleractiniaEas00JENVA',
'123379' => 'ScleractiniaEas00JENVC',
'123348' => 'ScleractiniaEas00JENV',
'123393' => 'ScleractiniaEas00JENVD',
'123379' => 'ScleractiniaEas00JENVC',
'119218' => 'worldatlasofcora01spal'
);

$items=array('118827' => 'cu31924023226180');

$items=array('62967' => 'britishantarctic12britrich',
'94976' => 'britishantarctic11914shac',
'94900' => 'britishantarctic21916shac'
);

$items=array('106322' => 'memoirsprocee51190607manc');

$items=array('27194' => 'bulletindumusumn14musu');

$items=array(
'36003' => 'ezhegodnik01zool',
'35244' => 'ezhegodnik02zool',
'120817' => 'ezhegodnikzoolog18972impe',
'120818' => 'ezhegodnikzoolog18973impe',
'35243' => 'ezhegodnik03zool',
'122172' => 'ezhegodnikzoolog11898impe',
'120819' => 'ezhegodnikzoolog18982impe',
'121133' => 'ezhegodnikzoolo189834impe',
'34426' => 'ezhegodnik04zool',
'120820' => 'ezhegodnikzoolog18991impe',
'120836' => 'ezhegodnikzoolog18992impe',
'120835' => 'ezhegodnikzoolog18994impe',
'35242' => 'ezhegodnik05zool',
'121137' => 'ezhegodnikzool1900512impe',
'120816' => 'ezhegodnikzoolo190053impe',
'122312' => 'ezhegodnikzoolo190054impe',
'36237' => 'ezhegodnik06zool',
'121132' => 'ezhegodnikzoolo641901impe',
'35273' => 'ezhegodnik07zool',
'121129' => 'ezhegodnikzool7121902impe',
'34427' => 'ezhegodnik08zool',
'121134' => 'ezhegodnikzool8341903impe',
'37023' => 'ezhegodnik09zool',
'120810' => 'ezhegodnikzoolo931904impe',
'35257' => 'ezhegodnik10zool',
'120844' => 'ezhegodnikzoo10121905impe',
'121130' => 'ezhegodnikzoo10341905impe',
'35241' => 'ezhegodnik11zool',
'120815' => 'ezhegodnikzoolo111906impe',
'121131' => 'ezhegodnikzoolog1906impe',
'36241' => 'ezhegodnik12zool',
'121138' => 'ezhegodnikzool1211907impe',
'120814' => 'ezhegodnikzool1221907impe',
'120813' => 'ezhegodnikzool1231907impe',
'37010' => 'ezhegodnik13zool',
'36240' => 'ezhegodnik14zool',
'33796' => 'ezhegodnik15zool',
'34698' => 'ezhegodnik16zool',
'34810' => 'ezhegodnik17zool',
'34819' => 'ezhegodnik18zool',
'121128' => 'ezhegodnikzoolog1913impe',
'105349' => 'ezhegodnik191914zool',
'111673' => 'ezhegodnikzool201916impe',
'34699' => 'ezhegodnik21zool',
'34689' => 'ezhegodnik22zool',
'34691' => 'ezhegodnik23zool'
);

$items=array('34819' => 'ezhegodnik18zool');

$items=array('18234' => 'carnegieinstitut291carn');

$items=array(
'111060' => 'australavianreco511922ma',
'104096' => 'australavianre1191213math',
'108185' => 'australavianre2191315math',
'103880' => 'australavianre3191519math',
'103879' => 'australavianre4192022math'
);

$items=array('48544' => 'diethieweltostaf14mb');	

$items=array(
'36773' => 'catalogueoffresh00brit',
'91114' => 'catalogueoffresh21909brit',
'90544' => 'catalogueoffresh31915brit',
'57864' => 'catalogueoffresh04boulrich'
);

$items=array('43590' => 'fishesofwesterns00eige');

$items=array('48241' => 'gemeinntzigesh01glog');

$items=array('13692' => 'mobot31753002433594');

$items=array('28168' => 'actasocprofaunae29soci');
$items=array('28259' => 'actasocprofaunae36soci');

$items=array('44923' => 'proceedingsofasi1871asia');

$items=array('111870' => 'monatsberichtede1880knig');

$items=array('116007' => 'cu31924024782991');

$items=array('125554' => 'zoologicalscienc11984niho');

$items = array('46610' => 'mollusquesdelafr00bour');

$items=array(
'94842' => 'britishjournalof1988brit',
'93932' => 'britishjournalof1989brit',
'94838' => 'britishjournalof1990brit',
'110945' => 'britishjournal4121991brit',
'93236' => 'britishjournalof199143brit',
'93235' => 'britishjournalof199144brit',
'94841' => 'britishjournalof1992brit',
'122004' => 'britishjournalof64brit',
'94840' => 'britishjournalof199394brit',
'121980' => 'britishjournalof724brit',
'94526' => 'britishjournalof199596brit',
'111852' => 'britishjourn1014199798bri',
'111101' => 'britishjournalof1111998br',
'93239' => 'britishjournalof1998112brit',
'111032' => 'britishjournalof11341999b',
'111031' => 'britishjournalof111998bri',
'94410' => 'britishjournalof1999brit',
'111635' => 'britishjour13132000brit',
'122003' => 'britishjournalof134brit'
);


$items=array('100685' => 'mollusquesterres00germ');

$items=array('21157' => 'asiaticherpetolo08asia');
$items=array('21131' => 'asiaticherpetolo09asia');

$items=array(
'20793' => 'hylidfrogsofmidd01duel',
'20794' => 'hylidfrogsofmidd02duel',
'25783' => 'readingsinmammal00jone',
'103564' => 'distributionofma31972arms',
'23034' => 'edwardhtaylorrec00tayl',
'54630' => 'selectedreadings00jone',
'22683' => 'northamericanqua00nowa',
'21252' => 'southamericanher00herp',
'34931' => 'taxonomyidentifi00live'
);

//$items=array('23034' => 'edwardhtaylorrec00tayl');


$items=array('113060' => 'memoriedellareal247real');

$items=array('120813' => 'ezhegodnikzool1231907impe');

$items=array('114064' => 'proceedingsofuni10719unit');

$items=array(
//'89402' => 'mobot31753002580550',
'88959' => 'mobot31753002580543'
);


$items=array('132258' => 'Pithecanthropus00Dubo');

$items=array(
'125382' => 'journalproceedin11867roya',
'125380' => 'journalproceedin21868roya',
'125469' => 'journalproceedin31869roya',
'125381' => 'journalproceedin41870roya',
'125467' => 'journalproceedin51871roya',
'125468' => 'journalproceedin61872roya',
'125471' => 'journalproceedin71873roya',
'125470' => 'journalproceedin81874roya',
'129914' => 'journalproceedin91875roya',
'112017' => 'journalproceedi101876roya',
'127376' => 'mobot31753002623947',
'129368' => 'mobot31753002623954',
'126818' => 'mobot31753002623962',
'129187' => 'mobot31753002623970',
'128881' => 'mobot31753002623988',
'129186' => 'mobot31753002623996',
'129366' => 'mobot31753002624002',
'126798' => 'mobot31753002624010',
'126971' => 'mobot31753002624028',
'126817' => 'mobot31753002624036',
'131307' => 'journalproceedi211887roya',
'130949' => 'journalproceedi221888roya',
'131299' => 'journalproceedi241890roya',
'133954' => 'journalproceedin25roya',
'130362' => 'journalproceedi261892roya',
'130154' => 'journalproceedi271893roya',
'130363' => 'journalproceedi281894roya',
'131951' => 'mobot31753002624093',
'131279' => 'mobot31753002624135',
'130955' => 'mobot31753002624127',
'130745' => 'journalproceedi321898roya',
'131872' => 'journalproceedi331899roya',
'130743' => 'journalproceedi341900roya',
'130388' => 'journalproceedi351901roya',
'130595' => 'journalproceedi361902roya',
'130744' => 'journalproceedi371903roya',
'129863' => 'journalproceedi381904roya',
'130556' => 'journalproceedi391905roya',
'129860' => 'journalproceedi401906roya',
'129859' => 'journalproceedi411907roya',
'129864' => 'journalproceedi421908roya',
'130041' => 'journalproceedi431909roya',
'131392' => 'mobot31753002605605',
'130364' => 'journalproceedi451911roya',
'130136' => 'journalproceedi461912roya',
'130132' => 'journalproceedi471913roya',
'130133' => 'journalproceedi481914roya',
'130155' => 'journalproceedi491915roya',
'130152' => 'journalproceedi501916roya',
'130151' => 'journalproceedi511917roya',
'129666' => 'journalproceedi521918roya',
'129690' => 'journalproceedi531919roya',
'131087' => 'journalproceedi541920roya',
'129688' => 'journalproceedi551921roya',
'129637' => 'journalproceedi561922roya'
);

$items=array('130152' => 'journalproceedi501916roya');

$items=array(
'19999' => 'edinburghnewphil01edin',
'81926' => 'edinburghnewphil01edinuoft',
'20148' => 'edinburghnewphil02edin',
'81985' => 'edinburghnewphil02edinuoft',
'81986' => 'edinburghnewphil03edinuoft',
'20131' => 'edinburghnewphil03edin',
'20042' => 'edinburghnewphil04edin',
'81987' => 'edinburghnewphil04edinuoft',
'80089' => 'ssedinburghnewph05edinuoft',
'20014' => 'edinburghnewphil05edin',
'20135' => 'edinburghnewphil06edin',
'81988' => 'edinburghnewphil06edinuoft',
'81989' => 'edinburghnewphil07edinuoft',
'52294' => 'edinburghnewphil07edin',
'52302' => 'edinburghnewphil08edin',
'78766' => 'edinburghnewphil08edinuoft',
'82199' => 'edinburghnewphil09edinuoft',
'52307' => 'edinburghnewphil09edin',
'79124' => 'edinburghnewphil10edinuoft',
'20013' => 'edinburghnewphil10edin',
'52309' => 'edinburghnewphil11edin',
'80046' => 'edinburghnewphil11edinuoft',
'20048' => 'edinburghnewphil12edin',
'81990' => 'edinburghnewphil12edinuoft',
'81991' => 'edinburghnewphil13edinuoft',
'20028' => 'edinburghnewphil13edin',
'20101' => 'edinburghnewphil14edin',
'20058' => 'edinburghnewphil15edin',
'20073' => 'edinburghnewphil16edin',
'20108' => 'edinburghnewphil17edin',
'52306' => 'edinburghnewphil18edin',
'52304' => 'edinburghnewphil19edin',
'20066' => 'edinburghnewphil20edin',
'20063' => 'edinburghnewphil21edin',
'20016' => 'edinburghnewphil22edin',
'52030' => 'edinburghnewphil23edin',
'52048' => 'edinburghnewphil24edin',
'20074' => 'edinburghnewphil25edin',
'20077' => 'edinburghnewphil62edin',
'52064' => 'edinburghnewphil27edin',
'52033' => 'edinburghnewphil28edin',
'20052' => 'edinburghnewphil29edin',
'52042' => 'edinburghnewphil30edin',
'24359' => 'edinburghnewphil31edin',
'52027' => 'edinburghnewphil32edin',
'20006' => 'edinburghnewphil33edin',
'45319' => 'edinburghnewphil34jour',
'52032' => 'edinburghnewphil34edin',
'20032' => 'edinburghnewphil35edin',
'20172' => 'edinburghnewphil36edin',
'20109' => 'edinburghnewphil37edin',
'52031' => 'edinburghnewphil38edin',
'52029' => 'edinburghnewphil39edin',
'20064' => 'edinburghnewphil40edin',
'20021' => 'edinburghnewphil41edin',
'20017' => 'edinburghnewphil42edin',
'20059' => 'edinburghnewphil43edin',
'52405' => 'edinburghnewphil44edin',
'52411' => 'edinburghnewphil45edin',
'20170' => 'edinburghnewphil46edin',
'52402' => 'edinburghnewphil47edin',
'52419' => 'edinburghnewphil48edin',
'20061' => 'edinburghnewphil49edin',
'20128' => 'edinburghnewphil50edin',
'20040' => 'edinburghnewphil51edin',
'20022' => 'edinburghnewphil52edin',
'131100' => 'edinburghnewphil531852',
'131111' => 'edinburghnewphil541852185',
'131106' => 'edinburghnewphil551853',
'131103' => 'edinburghnewphil561853185',
'131109' => 'edinburghnewphil571854',
'131108' => 'edinburghnewphil11855',
'80091' => 'nsedinburghnewph02edinuoft',
'79851' => 'nsedinburghnphil03edinuoft',
'79850' => 'nsedinburghnewph04edinuoft',
'80051' => 'edinburghnewphil05edinuoft',
'82022' => 'nsedinburghnewph06edinuoft',
'79880' => 'nsedinburghnewph07edinuoft',
'82023' => 'nsedinburghnewph08edinuoft',
'82024' => 'nsedinburghnewph09edinuoft',
'82198' => 'nsedinburghnewph10edinuoft',
'82030' => 'nsedinburghnewph11edinuoft',
'82031' => 'nsedinburghnewph12edinuoft',
'82032' => 'nsedinburghnewph13edinuoft',
'131107' => 'edinburghnewphil141861',
'80049' => 'nsedinburghnewph15edinuoft',
'82033' => 'nsedinburghnewph16edinuoft',
'82034' => 'nsedinburghnewph17edinuoft',
'131101' => 'edinburghnewphil181863'
);

$items=array(
'80091' => 'nsedinburghnewph02edinuoft',
'79851' => 'nsedinburghnphil03edinuoft'
);

$items=array(
'136735' => 'insectsofsamoaot06inst',
'136736' => 'insectsofsamoaot04univ',

'136738' => 'insectsofsamoaot04inte',
'136739' => 'insectsofsamoaot05unse',
'136740' => 'insectsofsamoaot01unse',

'136714' => 'catalogueoftypes01brit',
'136734' => 'catalogueoftypes02brit',
'136528' => 'catalogueoftypes03brit',
'136720' => 'catalogueoftypes04brit',
'136758' => 'catalogueoftypes05brit'
);


$items=array(
'136713' => 'descriptionsofne00sayt',
'136715' => 'insectsofsamoaot03inte',
'136716' => 'insectsofsamoaot02unse_1',
'136718' => 'catalogueoftypes06fran',
'136719' => 'insectsofsamoaot03soci',
'136721' => 'insectsofsamoaot01take',
'136722' => 'insectsofsamoaot02asoc',
'136725' => 'insectsofsamoaot03impe',
'136726' => 'insectsofsamoaot01unse_1',
'136728' => 'insectsofsamoaot02unse_2',
'136729' => 'insectsofsamoaot03univ',
'136730' => 'insectsofsamoaot01cana',
'136743' => 'insectsofsamoaot02unse',
'136744' => 'insectsofsamoaot03sono',
'136747' => 'insectsofsamoaot07euro',
'136748' => 'insectsofsamoaot01hort',
'136756' => 'insectsofsamoaot04unse',
'136757' => 'insectsofsamoaot02unse_0'
);

$items=array('109857' => 'denkschriftens841909akad');

$items=array('97669' => 'proceedingsofzoo19064631052zool');

$items=array(
'35091' => 'transactionsofka18117kans',
'34920' => 'transactionsofka19kans',
'35329' => 'transactionsofka201kans',
'37839' => 'transactionsofka202kans',
'34355' => 'transactionsofka21kans',
'35117' => 'transactionsofka22kans',
'35092' => 'transactionsofka2324kans',
'35093' => 'transactionsofka25kans',
'34356' => 'transactionsofka26kans',
'35094' => 'transactionsofka27kans',
'35095' => 'transactionsofka28kans',
'35096' => 'transactionsofka29kans'
);

$items = array('100494' => 'fversigtafkong301873kung');

$items=array(
'122560' => 'histoirephysique01gran',
'122920' => 'histoirephysiqu33gran',
'122561' => 'histoirephysique04gran',
'122650' => 'histoirephysiqu04gran',
'122681' => 'histoirephysique05gran',
'122674' => 'histoirephysique06gran',
'122950' => 'histoirephysiqu911875gran',
'122948' => 'histoirephysiq1021890gran',
'122651' => 'histoirephysiqu12gran',
'122562' => 'histoirephysique12gran',
'122982' => 'histoirephysiqu131876gran',
'122564' => 'histoirephysique14gran',
'122563' => 'histoirephysique15gran',
'122559' => 'histoirephysiqu15gran',
'48542' => 'histoirephysique16gran',
'48688' => 'histoirephysique16grandid',
'122565' => 'histoirephysique17gran',
'122566' => 'histoirephysique18gran',
'122567' => 'histoirephysique19gran',
'122675' => 'histoirephysique1819gran',
'123006' => 'histoirephysiqu201890gran',
'37660' => 'histoirenaturell01saus',
'42495' => 'histoirenaturell02saus',
'122568' => 'histoirephysique20gran',
'122918' => 'histoirephysique2122gran',
'21293' => 'histoirephysiquept23pt1gran',
'44338' => 'histoirephysique225gran',
'16576' => 'histoirephysique00gran',
'19992' => 'histoirephysique00pt2gran',
'7266' => 'mobot31753000483054',
'41106' => 'mobot31753002672050',
'41108' => 'mobot31753002672068',
'50798' => 'mobot31753003575997',
'41107' => 'mobot31753002671912',
'122676' => 'histoirephysique30gran',
'122669' => 'histoirephysique33gran',
'122668' => 'histoirephysique234gran',
'122670' => 'histoirephysique35gran',
'122677' => 'histoirephysique36gran',
'122678' => 'histoirephysique39gran',
'10373' => 'mobot31753002098744',
'10374' => 'mobot31753002098751'
);

$items=array('42495' => 'histoirenaturell02saus');

$items=array(
'48981' => 'phytologia01glea',
'47303' => 'phytologia02glea',
'46705' => 'phytologia03glea',
'47070' => 'phytologia04glea',
'47042' => 'phytologia05glea',
'46901' => 'phytologia06glea',
'47462' => 'phytologia07glea',
'50818' => 'mobot31753002573464',
'55371' => 'mobot31753002573456',
'46702' => 'phytologia10glea',
'50235' => 'mobot31753002573431',
'50236' => 'mobot31753002573423',
'50642' => 'mobot31753002573415',
'51598' => 'mobot31753003548283',
'47065' => 'phytologia15glea',
'46709' => 'phytologia16glea',
'46703' => 'phytologia17glea',
'46719' => 'phytologia18glea',
'46981' => 'phytologia19glea',
'47031' => 'phytologia20glea',
'47413' => 'phytologia21glea',
'48967' => 'phytologia22glea',
'46349' => 'phytologia23glea',
'47307' => 'phytologia24glea',
'47048' => 'phytologia25glea',
'47391' => 'phytologia26glea',
'47027' => 'phytologia27glea',
'47049' => 'phytologia28glea',
'48965' => 'phytologia29glea',
'48964' => 'phytologia30glea',
'47028' => 'phytologia31glea',
'46777' => 'phytologia32glea',
'47086' => 'phytologia33glea',
'46302' => 'phytologia34glea',
'47393' => 'phytologia35glea',
'48956' => 'phytologia36glea',
'46294' => 'phytologia37glea',
'47385' => 'phytologia38glea',
'47392' => 'phytologia39glea',
'46706' => 'phytologia40glea',
'47696' => 'phytologia41glea',
'46978' => 'phytologia42glea',
'49718' => 'phytologia43glea',
'47046' => 'phytologia44glea',
'48954' => 'phytologia45glea',
'47043' => 'phytologia46glea',
'47388' => 'phytologia47glea',
'47067' => 'phytologia48glea',
'111297' => 'phytologia4911981plai',
'47386' => 'phytologia50glea',
'46817' => 'phytologia491glea',
'46846' => 'phytologia492glea',
'47019' => 'phytologia493glea',
'46828' => 'phytologia494glea',
'46890' => 'phytologia495glea',
'47029' => 'phytologia51glea',
'47384' => 'phytologia52glea',
'46857' => 'phytologia531glea',
'46826' => 'phytologia532glea',
'46847' => 'phytologia533glea',
'46840' => 'phytologia534glea',
'46833' => 'phytologia535glea',
'46852' => 'phytologia536glea',
'46834' => 'phytologia537glea',
'46790' => 'phytologia54glea',
'46285' => 'phytologia55glea',
'47412' => 'phytologia56glea',
'47044' => 'phytologia57glea',
'46881' => 'phytologia581glea',
'46837' => 'phytologia582glea',
'46870' => 'phytologia583glea',
'46860' => 'phytologia584glea',
'46841' => 'phytologia585glea',
'46883' => 'phytologia586glea',
'46865' => 'phytologia587glea',
'47069' => 'phytologia59glea',
'48955' => 'phytologia60glea',
'47050' => 'phytologia61glea',
'47368' => 'phytologia621glea',
'47364' => 'phytologia622glea',
'47365' => 'phytologia623glea',
'48960' => 'phytologia624glea',
'47366' => 'phytologia625glea',
'48963' => 'phytologia626glea',
'47378' => 'phytologia631glea',
'48958' => 'phytologia632glea',
'48959' => 'phytologia633glea',
'48961' => 'phytologia634glea',
'48962' => 'phytologia635glea',
'48957' => 'phytologia636glea',
'46855' => 'phytologia641glea',
'46823' => 'phytologia642glea',
'46821' => 'phytologia643glea',
'46831' => 'phytologia644glea',
'46809' => 'phytologia645glea',
'46819' => 'phytologia646glea',
'46836' => 'phytologia651glea',
'47454' => 'phytologia652glea',
'46814' => 'phytologia653glea',
'47456' => 'phytologia654glea',
'46830' => 'phytologia655glea',
'48976' => 'phytologia656glea',
'47408' => 'phytologia661glea',
'47410' => 'phytologia662glea',
'47409' => 'phytologia663glea',
'47411' => 'phytologia664glea',
'47405' => 'phytologia6651989glea',
'47403' => 'phytologia666glea',
'47407' => 'phytologia671glea',
'47406' => 'phytologia672glea',
'47404' => 'phytologia673glea',
'47110' => 'phytologia674glea',
'47091' => 'phytologia675glea',
'47111' => 'phytologia676glea',
'46309' => 'phytologia681glea',
'46307' => 'phytologia682glea',
'46308' => 'phytologia683glea',
'47092' => 'phytologia684glea',
'47153' => 'phytologia685glea',
'47112' => 'phytologia686glea',
'46835' => 'phytologia691glea',
'46816' => 'phytologia692glea',
'46808' => 'phytologia693glea',
'46794' => 'phytologia694glea',
'46798' => 'phytologia695glea',
'46792' => 'phytologia696glea',
'46800' => 'phytologia701glea',
'46804' => 'phytologia702glea',
'46811' => 'phytologia703glea',
'46793' => 'phytologia704glea',
'47399' => 'phytologia705glea',
'47377' => 'phytologia706glea',
'47375' => 'phytologia711glea',
'47373' => 'phytologia712glea',
'47376' => 'phytologia713glea',
'47400' => 'phytologia714glea',
'47402' => 'phytologia715glea',
'47401' => 'phytologia716glea',
'47374' => 'phytologia721glea',
'47381' => 'phytologia722glea',
'46854' => 'phytologia723glea',
'46848' => 'phytologia724glea',
'46827' => 'phytologia725glea',
'46843' => 'phytologia726glea',
'46832' => 'phytologia731glea',
'46838' => 'phytologia732glea',
'46861' => 'phytologia733glea',
'48977' => 'phytologia734glea',
'46839' => 'phytologia735glea',
'46862' => 'phytologia736glea',
'47126' => 'phytologia741glea',
'47468' => 'phytologia742glea',
'47121' => 'phytologia743glea',
'47107' => 'phytologia744glea',
'48982' => 'phytologia745glea',
'47120' => 'phytologia746glea',
'81265' => 'phytologia751glea',
'47083' => 'phytologia752glea',
'81266' => 'phytologia753glea',
'48972' => 'phytologia754glea',
'47148' => 'phytologia755glea',
'47081' => 'phytologia756glea',
'47134' => 'phytologia761glea',
'47151' => 'phytologia762glea',
'48999' => 'phytologia763glea',
'47115' => 'phytologia764glea',
'49000' => 'phytologia765glea',
'46687' => 'phytologia766glea',
'48971' => 'phytologia771glea',
'47150' => 'phytologia772glea',
'46740' => 'phytologia773glea',
'48969' => 'phytologia774glea',
'47116' => 'phytologia775glea',
'47129' => 'phytologia776glea',
'47435' => 'phytologia781glea',
'47154' => 'phytologia782glea',
'46842' => 'phytologia783glea',
'46863' => 'phytologia784glea',
'46845' => 'phytologia785glea',
'46874' => 'phytologia786glea',
'46849' => 'phytologia791glea',
'46853' => 'phytologia792glea',
'46871' => 'phytologia793glea',
'46844' => 'phytologia794glea',
'46796' => 'phytologia795glea',
'46813' => 'phytologia796glea',
'46812' => 'phytologia801glea',
'46801' => 'phytologia802glea',
'46805' => 'phytologia803glea',
'47108' => 'phytologia804glea',
'47114' => 'phytologia805glea',
'47127' => 'phytologia806glea',
'47166' => 'phytologia811glea',
'46897' => 'phytologia812glea',
'46905' => 'phytologia813glea',
'47160' => 'phytologia814glea',
'46902' => 'phytologia815glea',
'46369' => 'phytologia816glea',
'47163' => 'phytologia821glea',
'46899' => 'phytologia822glea',
'46934' => 'phytologia823glea',
'47161' => 'phytologia824glea',
'47466' => 'phytologia825glea',
'46937' => 'phytologia826glea',
'47470' => 'phytologia831glea',
'47119' => 'phytologia832glea',
'47471' => 'phytologia833glea',
'47139' => 'phytologia834glea',
'47467' => 'phytologia835glea',
'47157' => 'phytologia836glea',
'47469' => 'phytologia841glea',
'47142' => 'phytologia842glea',
'47453' => 'phytologia843glea',
'46824' => 'phytologia844glea',
'46795' => 'phytologia845glea',
'47473' => 'phytologia846glea',
'47162' => 'phytologia851glea',
'47457' => 'phytologia852glea',
'46903' => 'phytologia853glea',
'47465' => 'phytologia854glea',
'47137' => 'phytologia855glea',
'48975' => 'phytologia856glea',
'46859' => 'phytologia861glea',
'126509' => 'phytologia8622004plai',
'46799' => 'phytologia863glea',
'47472' => 'phytologia871glea',
'47159' => 'phytologia872glea',
'46895' => 'phytologia873glea',
'46936' => 'phytologia881glea',
'48984' => 'phytologia882glea',
'46856' => 'phytologia883glea',
'47117' => 'phytologia891glea',
'48983' => 'phytologia892glea',
'47140' => 'phytologia893glea',
'47118' => 'phytologia901glea',
'46815' => 'phytologia902glea',
'47146' => 'phytologia903glea',
'90888' => 'phytologia9101glea',
'91351' => 'phytologia9102glea',
'90480' => 'phytologia9103glea'
);

$items=array(
'31596' => 'sitzungsberichte97kais',
'110528' => 'sitzungsbericht981889kais',
'109481' => 'sitzungsbericht991890kais',
'111266' => 'sitzungsberich1001891kais',
'110046' => 'sitzungsberich1011892kais',
'110171' => 'sitzungsberi1021893kais',
'110602' => 'sitzungsberich1031894kais',
'120550' => 'sitzungsberich1041895kais',
'110210' => 'sitzungsberic1051896kais',
'31595' => 'sitzungsberichte106kais',
'31090' => 'sitzungsberichte107kais',
'31093' => 'sitzungsberichte108kais',
'108478' => 'sitzungsberich1091900kais',
'31089' => 'sitzungsberichte110kais',
'110031' => 'sitzungsberich1111902kais',
'112027' => 'sitzungsberich1121903kais',
'31081' => 'sitzungsberichte113kais',
'31082' => 'sitzungsberichte114kais',
'110873' => 'sitzungsberich1151906kais',
'110704' => 'sitzungsberi1171908kais',
'110591' => 'sitzungsberich1181909kais',
'109498' => 'sitzungsberich1191910kais',
'111232' => 'sitzungsberic1201911kais',
'111179' => 'sitzungsberich1211912kais',
'111185' => 'sitzungsberich1221913kais',
'120253' => 'sitzungsberic1231914kais',
'100821' => 'sitzungsberichte1241915kais',
'111180' => 'sitzungsberich1251916kais',
'111233' => 'sitzungsberich1261917kais',
'111181' => 'sitzungsberich1271918kais',
'111174' => 'sitzungsberich1281919kais',
'110270' => 'sitzungsberich1291920kais',
'109489' => 'sitzungsberich1301921kais',
'109977' => 'sitzungsberich1311922kais',
'109540' => 'sitzungsberich1321923kais',
'31621' => 'sitzungsberichte1160105kais',
'31227' => 'sitzungsberichte97100kais',
'31226' => 'sitzungsberichte101105kais'
);

//$items=array('110873' => 'sitzungsberich1151906kais');

$items=array(
'98480' => 'analesdelasocied121876soci',
'26209' => 'analesdelasocied02soci',
'21765' => 'analesdelasocied03soci',
'21763' => 'analesdelasocied04soci',
'121811' => 'analesdelasocie05soci',
'121826' => 'analesdelasocie06soci',
'98435' => 'analesdelasocied71879soci',
'26177' => 'analesdelasocied08soci',
'98005' => 'analesdelasocied8918791880soci',
'100695' => 'analesdelasocied101880soci',
'23026' => 'analesdelasocied11soci',
'98057' => 'analesdelasocied121881soci',
'21766' => 'analesdelasocied13soci',
'26183' => 'analesdelasocied14soci',
'26191' => 'analesdelasocied15soci',
'97434' => 'analesdelasocied161883soci',
'110005' => 'analesdelasocied1884soci',
'110296' => 'analesdelasocied1884soci2',
'98481' => 'analesdelasocied19201885soci',
'97987' => 'analesdelasocied211886soci',
'26187' => 'analesdelasocied22soci',
'23029' => 'analesdelasocied23soci',
'21767' => 'analesdelasocied25soci',
'26181' => 'analesdelasocied26soci',
'26193' => 'analesdelasocied27soci',
'110293' => 'analesdelasocied2889soci',
'26150' => 'analesdelasocied29soci',
'98482' => 'analesdelasocied29301890soci',
'97297' => 'analesdelasocied311891soci',
'108771' => 'analesdelasoci321891soci',
'97299' => 'analesdelasocied331892soci',
'97744' => 'analesdelasocied343518921893soci',
'98624' => 'analesdelasocied361893soci',
'98434' => 'analesdelasocied371894soci',
'97745' => 'analesdelasocied381894soci',
'100363' => 'analesdelasocied39401895soci',
'97300' => 'analesdelasocied140soci',
'99807' => 'analesdelasocied41421896soci',
'97746' => 'analesdelasocied43441897soci',
'98456' => 'analesdelasocied45461898soci',
'108766' => 'analesdelasocied471899so',
'97333' => 'analesdelasocied481899soci',
'97798' => 'analesdelasocied49501900soci',
'97747' => 'analesdelasocied51521901soci',
'21764' => 'analesdelasocied54soci',
'97748' => 'analesdelasocied55561903soci',
'98463' => 'analesdelasocied571904soci',
'97832' => 'analesdelasocied585919041905soci',
'26163' => 'analesdelasocied60soci',
'98730' => 'analesdelasocied601905soci',
'97811' => 'analesdelasocied611906soci',
'98431' => 'analesdelasocied626319061907soci',
'99232' => 'analesdelasocied646519071908soci',
'97988' => 'analesdelasocied666719081909soci',
'97298' => 'analesdelasocied686919091910soci',
'97812' => 'analesdelasocied707119101911soci',
'26168' => 'analesdelasocied72soci',
'26208' => 'analesdelasocied74soci',
'26194' => 'analesdelasocied76soci',
'26199' => 'analesdelasocied78soci',
'26212' => 'analesdelasocied80soci',
'99376' => 'analesdelasocied811916soci',
'114406' => 'analesdelasocie821916soci',
'108778' => 'analesdelasocied821916s',
'26203' => 'analesdelasocied84soci',
'97813' => 'analesdelasocied851918soci',
'98432' => 'analesdelasocied861918soci',
'26206' => 'analesdelasocied88soci',
'97404' => 'analesdelasocied891920soci',
'108776' => 'analesdelasoci901920soci',
'97814' => 'analesdelasocied91921921soci',
'26211' => 'analesdelasocied94soci'
);

$force = false;
//$force = true;

foreach ($items as $ItemID => $SourceIdentifier)
{
	// Images are cached in folders with the ItemID as the name
	$cache_namespace = $config['cache_dir']. "/" . $ItemID;
	
	// Ensure cache subfolder exists for this item
	if (!file_exists($cache_namespace))
	{
		$oldumask = umask(0); 
		mkdir($cache_namespace, 0777);
		umask($oldumask);
		
		// Thumbnails are in a subdirectory
		$oldumask = umask(0); 
		mkdir($cache_namespace . '/thumbnails', 0777);
		umask($oldumask);
	}
	
	echo $ItemID . ' ' . $SourceIdentifier . "\n";


	// fetch source
	$djvu_filename = $cache_namespace . '/' . $SourceIdentifier . ".djvu";
	
	//$go = true;
	$go = false;
	
	if ($force || !file_exists($djvu_filename)) // don't fetch again if we don't need to
	{
		$url = 'http://www.archive.org/download/' . $SourceIdentifier . '/' . $SourceIdentifier . '.djvu';

		//$url = 'http://cluster.biodiversitylibrary.org/' . $SourceIdentifier{0} . '/' . $SourceIdentifier . '/' . $SourceIdentifier . '.djvu';
		$command = "curl";
		
		if ($config['proxy_name'] != '')
		{
			$command .= " --proxy " . $config['proxy_name'] . ":" . $config['proxy_port'];
		}
		$command .= " --location " . $url . " > " . $djvu_filename;
		echo $command . "\n";
		system ($command);
		
		$go = true;
	}
	
	if ($go)
	{
		
		// Get pages
		$pages = bhl_retrieve_item_pages($ItemID);
		
		//print_r($pages);
		//exit();
		
		foreach ($pages as $page)
		{
			$filename = $cache_namespace . "/" . $page->FileNamePrefix . '.jpg'; 
			
			if ($force || !file_exists($filename)) // don't over write
			{
			
				// Image filename
				$tiff_filename = $cache_namespace . "/" . $page->FileNamePrefix . '.tiff'; 
				
				$command = "ddjvu -format=tiff -page=" . $page->page_order . " -size=800x2000 "
				 . $djvu_filename . " " . $tiff_filename;
				echo $command . "\n";
				system($command);
				
				
				// Convert to JPEG
				$command = $config['convert'] . " " . $tiff_filename . " " . $filename;
				echo $command . "\n";
				system($command);
				
				
				
				if (0)
				{
					// Try and remove background colour
					$command = $config['convert'] . " " . $filename . " -channel all -normalize " . $filename;
					echo $command . "\n";
					system($command);
				}	
				
				// Thumbnail
				$thumbnail_filename = $cache_namespace . "/thumbnails/" . $page->FileNamePrefix . '.gif'; 
				$command = $config['convert']  . ' -thumbnail 100 ' . $filename . ' ' . $thumbnail_filename;
				echo $command . "\n";
				system($command);
					
				
				// Kill TIFF
				unlink ($tiff_filename);
			}
				
			//exit();
			
		}
	}


}

?>