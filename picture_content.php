<div id="picture_obsah">       

<?php



	/*  Rocnikovy projekt RS kody - demonstracia schopnosti RS kodov na obrazku  */

	/* (C) 2003 Miroslav œurËÌk */



	include "./ReedSolomon.php";

	include "./funcs.php";





define('FARBA_BOD_CHYBA', 0xFFFFFF );

define('FARBA_BOD_OK', 0x000000 );





define ('IMAGE_MODE_GRAYSCALE', 1); 	// len otiene sede - podla N

define ('IMAGE_MODE_INDEXCOLOR', 2);	// indexovane farby - pocet je N

define ('IMAGE_MODE_FULLCOLOR', 3);	// truecolor farby - R,G,B zlozky su osobitne kodovane s N stupnami



define ('PRIMITIV_MAX_BITS', 7 ); // kolko pri kontrole bude brat najviac biov pre mitivny polynom



$_SESSION['image_mode'] = IMAGE_MODE_GRAYSCALE;





// ak sme vstupili na tuto stranku tak nech je prvy krok..

if (empty($_GET['step'])) 

	$step = 1;

else

	$step = $_GET['step'];



//print_r($_REQUEST);



// ziskanie udajov z formularov

if (!empty($_POST['img']))

	$_SESSION['source_pic'] = $GLOBALS['rs_pics'][key($_POST['img'])]; // ulozit nazov zdrojoveho obrazku		

if (!empty($_POST['pocetbitov'])) {

	$_SESSION['pocetbitov'] = $_POST['pocetbitov'];		

	if (!empty($_POST['doublewords'])) 

		$_SESSION['doublewords'] = 'yes';

	else

		$_SESSION['doublewords'] = 'no';

}

if (!empty($_POST['pocetchyb']))

	$_SESSION['pocetchyb'] = $_POST['pocetchyb'];

if (!empty($_POST['polynom']))

	$_SESSION['polynom'] = $_POST['polynom'];



if (empty($_SESSION['pocetbitov']))

{

	$_SESSION['pocetbitov'] = 6;

	$_SESSION['pocetchyb'] = 5;

	$_SESSION['polynom'] = 'x6+x+1';

	$_SESSION['doublewords'] = 'no';

}



// kontrola ci aktualne nastavenie da dobry valid koder, ak nie tak zober pocet bitov, a zober  prvy platny prim.pol.

if ( $_SESSION['pocetbitov'] > PRIMITIV_MAX_BITS ) $_SESSION['pocetbitov'] =  PRIMITIV_MAX_BITS;

if ($_SESSION['polynom']{1} != $_SESSION['pocetbitov'])

{

	// zoberieme pocet bitov...

	$primitivy = GetGFPolys($_SESSION['pocetbitov']);	

	if ($_SESSION['pocetchyb'] > (((1<<$_SESSION['pocetbitov'])/2)-2) ) $_SESSION['pocetchyb'] = 1;

	if ($primitivy!==false)

	{

		$_SESSION['polynom'] = $primitivy[0];

		$_SESSION['pocetchyb'] = 1;

	}	

}







$_SESSION['local_orig_file'] = PROJEKT_DIR.TEMP_DIR.'orig_'.$PHPSESSID.'.png';

$_SESSION['local_encoded_file'] = PROJEKT_DIR.TEMP_DIR.'encoded_'.$PHPSESSID.'.png';

$_SESSION['local_errored_file'] = PROJEKT_DIR.TEMP_DIR.'errored_'.$PHPSESSID.'.png';

$_SESSION['local_decodedv_file'] = PROJEKT_DIR.TEMP_DIR.'decodedv_'.$PHPSESSID.'.png';

$_SESSION['local_decodedh_file'] = PROJEKT_DIR.TEMP_DIR.'decodedh_'.$PHPSESSID.'.png';



$_SESSION['local_differe_file'] = PROJEKT_DIR.TEMP_DIR.'differe_'.$PHPSESSID.'.png';

$_SESSION['local_differv_file'] = PROJEKT_DIR.TEMP_DIR.'differv_'.$PHPSESSID.'.png';

$_SESSION['local_differh_file'] = PROJEKT_DIR.TEMP_DIR.'differh_'.$PHPSESSID.'.png';





// ak nie je spravny submit tak uprav step

if (($step==5)&&(isset($_POST['submit_obnov']))) $step = 2;



if ($step==1)  /* VYBRATIE OBRAZKA */

	{

		/* prvotne vybratie obrazka, ktory nasledne zakodujeme */

	/*	// vynulovat niektore session premenne

		unset($_SESSION['source_pic']);

		unset($_SESSION['dest_pic']);

		unset($_SESSION['pocetbitov']);

		unset($_SESSION['pocetchyb']);

		unset($_SESSION['polynom']);

		unset($_SESSION['cposun']);

	*/			

		// zobrazenie stranky so zoznamom obrazkov na vybratie

		echo '<form id="zdrojove_obrazky" name="obrazok_form" METHOD="POST" ACTION="index.php?section='.$section.'&amp;step=2">';

		$jj=0;		

		foreach($GLOBALS['rs_pics'] as $obraz)

		{			

			echo '<div class="image_input"><input type="image" name="img['.$jj.']" value="'.$obraz.'" src="'.SOURCE_DIR.$obraz.'" ></div>';

			$jj++;

		}

		echo "</form>"; 

		// nasleduje text vedla obrazkov na zakodovanie		

		?><DIV id="picture_intro">

		<H2>"Zaöumen˝ obr·zok"</h2>

		<p class="odstavec">Schopnosti R&amp;S kÛdov detekovaù a opravovaù chyby v pren·öanej inform·cii sa daj˙ veæmi n·zorne 

		uk·zaù aj na obr·zkoch. <BR><BR>

		<strong>UK¡éKA:</strong> Vami zvolen˝ obr·zok bude najprv zakÛdovan˝ pomocou zvolenÈho R&amp;S kÛdu. KÛdovanie 

		sa uskutoËÚuje v dvoch smeroch - horizont·lne, n·sledne aj vertik·lne. œalej sa virtu·lne prenesie 

		cez zaöumen˝ prenosov˝ kan·l, ËÌm sa viac Ëi menej pokazÌ. Na koniec trochu m·gie - dekÛdovanie a opravenie

		 poökoden˝ch bodov. To Ëi na konci bude obr·zok vyzeraù rovnako ako pÙvodn˝ z·visÌ len od ˙rovne 

		 zaöumenia a schopnosti zvolenÈho kÛdu tieto chyby n·jsù a opraviù.</p>

		<span>Uk·ûka zaËne kliknutÌm na jeden z obr·zkov na æavo.</span>

		</div>		

	<?php 			

	} // case 1

if ($step==2)	

{

	if (empty($_SESSION['source_pic'])) // existuje zdrojovy obrazok ?

		echo "<p class=\"chyba\">Nevybrali ste obrazok!</p>";

	else

	{

		echo "<DIV id=\"volba_kodu\">\n";		

		echo '<h3>Voæba kÛdu pouûitÈho v procese</h3>';		

		// zaciatok formulara

		echo '<FORM name="volbakodu" METHOD="POST" ACTION="index.php?section='.$section.'&amp;step=5"><FIELDSET><LEGEND>Voæba kÛdu:</legend>';

		echo "<table>\n";

		

		/* VOLBA ROZMEROV SPRAC.OBRAZKA */

		echo '<TR><TD>Vyberte dÂûku kÛdovÈho slova:</td><td><SELECT name="pocetbitov" onchange="submit_obnov.click()">'

		//.'<OPTION value="8" '.($_SESSION['pocetbitov']==8 ? 'selected':'').' >255 (8 bitov/prvok)'

		.'<OPTION value="7" '.($_SESSION['pocetbitov']==7 ? 'selected':'').' >127 (7 bitov/prvok)'

		.'<OPTION value="6" '.($_SESSION['pocetbitov']==6 ? 'selected':'').' >63 (6 bitov/prvok)'

		.'<OPTION value="5" '.($_SESSION['pocetbitov']==5 ? 'selected':'').' >31 (5 bitov/prvok)'

		.'</select></td></tr>'."\n";	

	

		/* VOLBA POCTU BITOV */	

		// step 3									

		echo '<tr><td>Vyberte poËet moûn˝ch chybn˝ch prvkov:</td><td><SELECT name="pocetchyb" onchange="submit_obnov.click()">';

		// kolko je max. pocet chyb ?

		$maxx = ((1<<$_SESSION['pocetbitov']) -2) / 4 ;		

		for ($f=1; $f <= $maxx ; $f++) 

		{

			// kolko ostane na informaciu bitov ?

			$inform = (1<<$_SESSION['pocetbitov']) - 1 - ($f*2);

			echo '<OPTION value="'.$f.'" '.($_SESSION['pocetchyb']=="$f" ? "selected":"").' >'.$f.' ('.$inform.' informaËn˝ch prvkok)';

		}

		echo '</select></td></tr>'."\n";

		

	 	/* step 4 VYBER KONKRETNEHO PRIMITIVNEHO POLYNOMU */	

		echo '<tr><td>Vyberte konkrÈtny primitÌvny polynÛm:</td><td><SELECT name="polynom" onchange="submit_obnov.click()" >';

		// zisti vsetky primitivne polynomy podla zadanych udajov - stupen

		$polys = getGFPolys($_SESSION['pocetbitov']);		

		foreach($polys as $poly)									

			echo '<OPTION value="'.$poly.'" '.(strcmp($_SESSION['polynom'],$poly)==0 ? "selected" : "" ).' >'.$poly;			

		echo '</select></td></tr>';

		

		// volitelne zduplikovanie poctu kodovych slov.. na 4 vlastne.. 2krat po vyske aj sirke

		echo '<tr><td class="popis"></td><td><input class="checkbox" type="checkbox" name="doublewords" value="yes" '.($_SESSION['doublewords']=='yes' ? 'checked' : '').'  >2 kÛdovÈ slov· horizont·lne aj vertik·lne</td></tr>';



		// submit	

		echo '<tr><td></td><td><INPUT TYPE="submit" name="submit_obnov" value="Obnov"><INPUT TYPE="submit" name="submit_code" value="Priprav obr·zky"></td></tr>'."\n";		

		

		

		// koniec formulara

		echo '</table></fieldset></form></div>';

		

		// info o aktualne vybranom type kodu

		$rsk =  & new ReedSolomon($_SESSION['polynom'], 0, $_SESSION['pocetchyb']);

		if ($rsk->isValid == false)

			echo "<p class=\"chyba\">Chyba vytvorenia RS kodu! (zle parametre ?)</p>";

		else

		echo "<DIV id=\"kod_info\">

		<h3>INFO o vybranom kÛde</h3>\n

		<TABLE>

		<TR><TD class=\"popis\">DÂûka kÛdovÈho slova:</td><TD class=\"prava\">".($rsk->n)."</td></tr>\n

		<TR><TD class=\"popis\">PoËet informaËn˝ch prvkov:</td><TD class=\"prava\">".($rsk->k)."</td></tr>\n

		<TR><TD class=\"popis\">Max.poËet moûn˝ch chybn˝ch prvkov:</td><TD class=\"prava\">".($rsk->t)."</td></tr>\n

		<TR><TD class=\"popis\">Prim.polynÛm na generovanie GF(q):</td><TD class=\"prava\">".FormatPolynom($_SESSION['polynom'])."</td></tr>\n

		<TR><TD class=\"popis\">Generuj˙ci polynÛm:</td></tr><TR><TD COLSPAN=\"2\">".nl2br(FormatPolynom(wordwrap(  $rsk->genPolyString, 40, "\n", true)))."</td></tr>\n

		</table></div>\n

		";

				

	} // exitsuje zdrojovy obrazok

} // step 4

	if ($step==5) /* priprava zdrojoveho a cieloveho obrazka */

	{

		$_SESSION['nn'] = (1<<$_SESSION['pocetbitov']) -1;

		$_SESSION['tt'] = $_SESSION['pocetchyb'];

		$_SESSION['kk'] = $_SESSION['nn'] - $_SESSION['tt']*2;		

			

		// podla poctu bitov kodu vytvor masku pre farby

		$_SESSION['cmaska'] = $_SESSION['nn'];

		// o kolko posuvat		

		$_SESSION['cposun'] = 8 - $_SESSION['pocetbitov'];		

		

		// 0. natiahnut povodny obrazok a zistit jeho rozmery

		if (empty($_SESSION['source_pic']))

		{

			echo "<p class=\"chyba\">Zdrojov˝ obr·zok nebol definovan˝!</p>"; die;

		}

		$rozmery = getimagesize (SOURCE_DIR.$_SESSION['source_pic']);

		$src_width = $rozmery[0];

		if ($src_width==0) echo "<p class=\"chyba\">Sirka je neplatna!</p>";

		$src_height = $rozmery[1];

		if ($src_height==0) echo "<p class=\"chyba\">Vyska je neplatna!</p>";

		$img_src = imagecreatefrompng  (SOURCE_DIR.$_SESSION['source_pic'] ); // tento sa natiahne originalne..


		if ($img_src==false) echo "<p class=\"chyba\">Zdrojovy obrazok je neplatny!</p>";

		

		// co treba spravit ?

		// 1. vytvorit obrazok o K x K bitov, kde K je pocet informacnych bitov a prekopirovat do temp.

		if ($_SESSION['doublewords']=='yes')

			$img_orig = imagecreatetruecolor (2*$_SESSION['kk'], 2*$_SESSION['kk']);		

		else

			$img_orig = imagecreatetruecolor ($_SESSION['kk'], $_SESSION['kk']);		

			

		if ($img_orig==false) echo "<p class=\"chyba\">Povodny obrazok je neplatny!</p>";

		

		if ($_SESSION['doublewords']=='yes') imagecopyresampled ( $img_orig, $img_src, 0, 0, 0, 0, 2*$_SESSION['kk'], 2*$_SESSION['kk'], $src_width, $src_height);

		else imagecopyresampled ( $img_orig, $img_src, 0, 0, 0, 0, $_SESSION['kk'], $_SESSION['kk'], $src_width, $src_height);

		

		// 1.5 uprav farby na zdrojovom obrazku... podla moznosti kodu

		DegradeImage($img_orig);

				

		// 2. vytvorit obrazok o rozmeroch N x N kde N je dlzka kodoveho slova

		//  a potom treba hned donho nakopirovat v lavo hore obrazok ten s K x K bitov, cim vznikne okaj o N-K bitov

		if ($_SESSION['image_mode'] == IMAGE_MODE_INDEXCOLOR)

		{	// index colors

			$img_encoded = imagecreatetruecolor($_SESSION['nn'], $_SESSION['nn']);

			imagetruecolortopalette($img_encoded, false, $_SESSION['nn']);

			imagepalettecopy($img_encoded,$img_orig);

		}		

		else //cernobiely mod ci RGB true color 

		{

			if ($_SESSION['doublewords']=='yes')

				$img_encoded = imagecreatetruecolor (2*$_SESSION['nn'], 2*$_SESSION['nn']);

			else			

				$img_encoded = imagecreatetruecolor ($_SESSION['nn'], $_SESSION['nn']);

		}

		



		//  uloz prazdne obrazky ostatne		

//DDD		imagepng($img_encoded, $_SESSION['local_errored_file'] );
$_SESSION['s_img_errored'] = serialize( GetPNGData($img_encoded));

//DDD		imagepng($img_encoded, $_SESSION['local_decodedv_file'] );
$_SESSION['s_img_decodedv'] = serialize(GetPNGData($img_encoded));

//DDD		imagepng($img_encoded, $_SESSION['local_decodedh_file'] );
$_SESSION['s_img_decodedh'] = serialize(GetPNGData($img_encoded));

		

		if ($img_encoded==false) echo "<p class=\"chyba\">Encoded obrazok je neplatny!</p>";

		if ($_SESSION['doublewords']=='yes')

			imagecopyresized ( $img_encoded, $img_orig, 0, 0, 0, 0, 2*$_SESSION['kk'], 2*$_SESSION['kk'],2* $_SESSION['kk'], 2*$_SESSION['kk']);

		else

			imagecopyresized ( $img_encoded, $img_orig, 0, 0, 0, 0, $_SESSION['kk'], $_SESSION['kk'], $_SESSION['kk'], $_SESSION['kk']);



		// 5. ulozit oba vytvorene obrazky		+ nazov aj pre differ. obrazok

//DDD		if (!(imagepng($img_orig, $_SESSION['local_orig_file'] )))
//DDD			echo "<p class=\"chyba\">Nepodarilo sa ulozit img_orig!</p>";
$_SESSION['s_img_orig'] = serialize(GetPNGData($img_orig));

//DDD		if (!(imagepng($img_encoded, $_SESSION['local_encoded_file'])) )
//DDD			echo "<p class=\"chyba\">nepodarilo sa ulozit img_encoded!</p>";
$_SESSION['s_img_encoded'] = serialize(GetPNGData($img_encoded));

		



		// 6. zobraz oba obrazky

		echo '<h3>PrÌprava obr·zkov</h3>';

		ObsahStranky(5, true, 'encoded', '', "submit", 'index.php?section='.$section.'&amp;step=6', "ZakÛduj horizont·lne", "" );

		echo '<p class="odstavec">Zvolen˝ obr·zok bol pouûit˝ na vytvorenie <strong>"pÙvodnÈho"</strong> obr·zku, ktor˝ bude sl˙ûiù na porovn·vanie 

		obr·zkov v ÔalöÌch krokoch - v˝poËet poËtu rozdielnych bodov. Obr·zok napravo je rozmermi prispÙsoben˝

		zvolenÈmu kÛdu: rozmery s˙ <strong>'.($_SESSION['nn'] * ( $_SESSION['doublewords']=='yes' ? 2 : 1 ) ).'</strong>x<strong>'.($_SESSION['nn'] * ( $_SESSION['doublewords']=='yes' ? 2 : 1 )).'</strong> bodov'.($_SESSION['doublewords']=='yes' ? ' (2 kÛdovÈ slov· na jeden riadok, stÂpec)' : '' ).', priËom kaûd˝ bod mÙûe nadob˙daù aû <strong>'.($_SESSION['nn']+1).'</strong> moûn˝ch farieb. 

		Rozmery pÙvodnÈho obr·zka boli kvÙli schopnosti kÛdu opraviù aû <strong>'.$_SESSION['tt'].'</strong> ch˝b v kaûdom kÛdovom slove 

		upravenÈ na <strong>'.($_SESSION['kk'] * ( $_SESSION['doublewords']=='yes' ? 2 : 1 )).'</strong>x<strong>'.($_SESSION['kk'] * ( $_SESSION['doublewords']=='yes' ? 2 : 1 )).'</strong> bodov.</p>';

		

				

	} // case 5

	if ($step==6) /* ZAKODOVANIE OBRAZKA - po riadkoch */

	{

		// co treba spravit ?					

		// 1. otvor cielovy obrazok

//DD		$img_encoded = imagecreatefrompng ( $_SESSION['local_encoded_file'] );
$img_encoded = imagecreatefromstring(unserialize($_SESSION['s_img_encoded']));

		

		// 3. zakodovat obrazok horizontalne

			// kazdy riadok ci stlpec budu vlasstne az tri kodove slova kedze mame RGB farby			

		$rsk =  & new ReedSolomon($_SESSION['polynom'], 0, $_SESSION['tt']);

		if (($rsk->isValid) == false)		

		{

			echo "<p class=\"chyba\">Vytvorenie RS objektu zlyhalo! (zle parametre?)</p>";

			die;

		}

		

			// prejdi postupne vsetky riadky

			$riadpo = $_SESSION['kk'];

			if ($_SESSION['doublewords']=='yes') $riadpo *=2;

			for ($riadok = 0; $riadok < $riadpo; $riadok++)

			{				

				// zakoduj vsetky farby tohto riadku

				$encoded_r = ZakodujRS($rsk, $img_encoded, $riadok);

				$encoded_g = true;//$encoded_g = & ZakodujRS($rsk, $pole_g);

				$encoded_b = true;//$encoded_b = & ZakodujRS($rsk, $pole_b);

				if (($encoded_r==false) || ($encoded_g==false) || ($encoded_b==false)) {

					echo "<p class=\"chyba\">Nepodarilo sa zakodovat farby! riadok ".$riadok."</p>";

					die;

				}												

				if ($_SESSION['doublewords']=='yes') {

					// zakoduj vsetky farby tohto riadku -  druhe slovo

					$encoded_r = ZakodujRS($rsk, $img_encoded, $riadok, false, true);

					$encoded_g = true;//$encoded_g = & ZakodujRS($rsk, $pole_g);

					$encoded_b = true;//$encoded_b = & ZakodujRS($rsk, $pole_b);

					if (($encoded_r==false) || ($encoded_g==false) || ($encoded_b==false)) {

						echo "<p class=\"chyba\">Nepodarilo sa zakodovat farby! riadok ".$riadok."</p>";

						die;

					}																	

				}				

			}// for riadok		

			

		

		// 4. zakodovat obrazok vertikalne

			

		// 5. uloz pozmeneny zakodovany obrazok		

//DDD		imagepng($img_encoded, $_SESSION['local_encoded_file'] );				
$_SESSION['s_img_encoded'] = serialize(GetPNGData( $img_encoded));
		

		// 6. a nakoniec ich zobrazit aj..

		echo '<h3>KÛdovanie - po riadkoch, stÂpcoch</h3>';

		ObsahStranky(6, false, 'encoded', '', "encode_horizontal", 'index.php?section='.$section.'&amp;step=7', "ZakÛduj vertik·lne", "" );

		echo '<p class="odstavec">Prebehla prv· f·za kÛdovania - po riadkoch. Kaûd˝ riadok predstavuje '.( $_SESSION['doublewords']=='yes' ? 'dve kÛdovÈ slov·' : 'jedno kÛdovÈ slovo').', priËom öÌrka 

		"pÙvodnÈho" obr·zka (<strong>'.($_SESSION['kk'] * ($_SESSION['doublewords']=='yes' ? 2 : 1)  ).'</strong> bodov) je informaËn· Ëasù a zvyöok je nadbytoËnosù z dÙvodu zvolenÈho 

		kÛdu ('.($_SESSION['doublewords']=='yes' ? '2x' : '').'2x'.$_SESSION['tt'].'  ch˝b = <strong>'.(2*$_SESSION['tt']* ($_SESSION['doublewords']=='yes' ? 2 : 1)  ).'</strong> bodov). Pojem <strong>"bod"</strong> je v tejto uk·ûke potrebnÈ ch·paù 

		ako <strong>symbol</strong> kÛdovÈho slova, znaËen˝ ako <span class="math"><strong>a</strong></span>. Aby v zakÛdovanom obr·zku bolo moûnÈ vidieù 

		priamo akoûe pÙvodn˝ obr·zok, bol pouûit˝ systematick˝ kÛd.'.( $_SESSION['doublewords']=='yes' ? ' Kaûd˝ riadok obsahuje dve kÛdovÈ slov·, priËom z æava sa najprv zapÌsali informaËnÈ bity z 

		prvÈho kÛdovÈ slova, druhÈho kÛdovÈ slova a aû za t˝m zabezpeËovacie bity obidvoch kÛdov˝ch slov.' : '' ).'</p>';

				

	} // case 6

	if ($step==7) /* ZAKODOVANIE OBRAZKA - po stlpcoch */

	{

		// co treba spravit ?			

		if (isset($_POST['encode_horizontal'])) // aby sa kliknutim na tento krok dalo dodatocne zasumit este

		{

			// 1. otvor cielovy obrazok

//DD			$img_encoded = imagecreatefrompng ( $_SESSION['local_encoded_file'] );
$img_encoded = imagecreatefromstring(unserialize($_SESSION['s_img_encoded']));

			

			// 3. zakodovat obrazok horizontalne

				// kazdy riadok ci stlpec budu vlasstne az tri kodove slova kedze mame RGB farby			

			$rsk =  & new ReedSolomon($_SESSION['polynom'], 0, $_SESSION['tt']);

			if (($rsk->isValid) == false)		

			{

				echo "<p class=\"chyba\">Vytvorenie RS objektu zlyhalo! (zle parametre?)</p>";

				die;

			}

			

				// prejdi postupne vsetky stlpce - zakoduj aj nadbytocnost z predosleho kroku								

				$stlppo = $_SESSION['nn'];

				if ($_SESSION['doublewords']=='yes') $stlppo*=2;

				for ($stlpec = 0; $stlpec < $stlppo; $stlpec++)

				{				

					// zakoduj vsetky farby tohto riadku

					$encoded_r = ZakodujRS($rsk, $img_encoded, $stlpec, true);

					$encoded_g = true;//$encoded_g = & ZakodujRS($rsk, $pole_g);

					$encoded_b = true;//$encoded_b = & ZakodujRS($rsk, $pole_b);

					if (($encoded_r==false) || ($encoded_g==false) || ($encoded_b==false)) {

						echo "<p class=\"chyba\">Nepodarilo sa zakodovat farby! stlpec ".$stlpec."</p>";

						die;

					}												

					// zakoduj vsetky farby tohto riadku - druhe slovo

					$encoded_r = ZakodujRS($rsk, $img_encoded, $stlpec, true, true);

					$encoded_g = true;//$encoded_g = & ZakodujRS($rsk, $pole_g);

					$encoded_b = true;//$encoded_b = & ZakodujRS($rsk, $pole_b);

					if (($encoded_r==false) || ($encoded_g==false) || ($encoded_b==false)) {

						echo "<p class=\"chyba\">Nepodarilo sa zakodovat farby! stlpec ".$stlpec."</p>";

						die;

					}												



				}// for stlpec

				

				

			// 5. uloz pozmeneny zakodovany obrazok		

//DDD			imagepng($img_encoded, $_SESSION['local_encoded_file'] );				
$_SESSION['s_img_encoded'] = serialize( GetPNGData($img_encoded));

		}

				

		// 6. a nakoniec ich zobrazit aj..

		echo '<h3>Zaöumenie obr·zka "poËas prenosu"</h3>';

		$dopl = 'PoËet chybn˝ch bodov:&nbsp;&nbsp;<INPUT TYPE="TEXT" name="chbodov"><BR>alebo bitov· chybovosù BER:&nbsp;&nbsp;<input type="text" name="ber">'

			.'<BR>priËom zakÛdovan˝ obr·zok m· '.($_SESSION['nn'] * ( $_SESSION['doublewords']=='yes' ? 2 : 1 ) ).' x '.($_SESSION['nn'] * ( $_SESSION['doublewords']=='yes' ? 2 : 1 )).' = '.($_SESSION['nn']*$_SESSION['nn'] * ( $_SESSION['doublewords']=='yes' ? 4 : 1 )).' bodov<BR><BR>';

		ObsahStranky(7, false, 'encoded', '', "submit", 'index.php?section='.$section.'&amp;step=8', "Pokaz obr·zok - zaöum", $dopl );

		echo '<p class="odstavec">Obr·zok je zakÛdovan˝ a pripraven˝ na prenos cez virtu·lny kan·l. KÛdovanie vo vertik·lnom smere

		 prebehlo podobne ako v predch·dzaj˙com kroku s t˝m rozdielom, ûe kÛdovÈ slov· neboli riadky ale stÂpce. Naviac v tomto kroku boli zakÛdovanÈ aj tie 

		 stÂpce, ktorÈ neobsaholi d·ta z obr·zku ale pr·ve nadbytoËnosù vytvoren˙ v predch·dzaj˙com kroku. 

		 Prenos odötartujete zadanÌm poËtu pokazen˝ch bodov poËas prenosu, alebo Bit Error Rate.</p>';

				

	} // case 7

	if ($step==8) // pridanie nahodnych chyb do zakodovaneho obrazku

	{

		// 1. zisti kolko treba spravit chybiciek

		$_SESSION['chbodov'] = $_POST['chbodov'];

		$_SESSION['ber'] = $_POST['ber'];

		

		// 2. otvor zakodovany obrazok

//DD		$img_encoded = imagecreatefrompng ( $_SESSION['local_encoded_file'] );
$img_encoded = imagecreatefromstring(unserialize($_SESSION['s_img_encoded']));

		

		// 3. vygeneruj potrebny pocet chybiciek

		if (!empty($_SESSION['chbodov']))

		    ZasumChyb($img_encoded);

		if (!empty($_SESSION['ber']))

		    ZasumBER($img_encoded, $_SESSION['ber']);			

						

		

		// 4. uloz zmeny do errored obrazka

//DDD		imagepng($img_encoded, $_SESSION['local_errored_file'] );				
$_SESSION['s_img_errored'] = serialize( GetPNGData($img_encoded));

						

		

		// 6. zobraz vsetky obrazky - povodny, uz prijaty s chybami, rozdielovy

		echo '<h3>Toto nie je mÙj obr·zok!</h3>';

		ObsahStranky(8, true, 'errored', 'differe', "submit", 'index.php?section='.$section.'&amp;step=9', "DekÛduj vertik·lne", "");

		echo '<p class="odstavec">Obr·zok bol ˙speöne prenesen˝ cez virtu·lny prenosov˝ kan·l. <strong>Naozaj ˙speöne?</strong> Ako vidno, viac Ëi menej poökoden˝. 

		PoËet poökoden˝ch bodov je moûnÈ zistiù presunom kurzora myöky nad prav˝ <strong>"rozdielov˝"</strong> obr·zok. 

		<strong>»ierny</strong> bod znamen·, ûe ten bod je rovnak˝ v pÙvodnom ako aj prijatom "zaöumenom" obr·zku. <strong>Biely</strong> bod 

		je naopak rozdielny. 

		A pr·ve v tejto chvÌli nastupuje sila R&amp;S kÛdu, ktor˝ sa v nasleduj˙cich dvoch krokoch pok˙si sp‰tne 

		prijatÈ stÂpce a riadky dekÛdovaù, vzniknutÈ chybnÈ body lokalizovaù a opraviù.</p>';

						

	} // case 8

	if ($step==9) /* D E K O D O V A N I E   V E R T I K A L N E */

	{

		// 1. otvor zakodovany obrazok z errored ( ale ak je to opakovane dekodovanie tak z decodedh )

		if (empty($again)) {

//DD			$img_encoded = imagecreatefrompng ( $_SESSION['local_errored_file'] );
$img_encoded = imagecreatefromstring(unserialize($_SESSION['s_img_errored']));
        }
		else
{
//DD			$img_encoded = imagecreatefrompng ( $_SESSION['local_decodedh_file'] );                                                                          
    $img_encoded = imagecreatefromstring(unserialize($_SESSION['s_img_decodedh']));
       }

	

		// 2. vytvor RS objekt

		$rsk =  & new ReedSolomon($_SESSION['polynom'], 0, $_SESSION['tt']);

		if (($rsk->isValid) == false)		

		{

			echo "<p class=\"chyba\">Vytvorenie RS objektu zlyhalo! (zle parametre?)</p>";

			die;

		}

		

		// 3. prejdi postupne vsetky stlpce ( dekoduj aj zakodovany overhead riadkov )

		$stlp = $_SESSION['nn'];

		if ($_SESSION['doublewords']=='yes') $stlp*=2;

		for ($stlpec = 0; $stlpec < $stlp; $stlpec++)

		{				

			// odkoduj vsetky farby tohto riadku

			$decoded_r = OdkodujRS($rsk, $img_encoded, $stlpec, true);

			if (($decoded_r==false)) {

				echo "<p class=\"chyba\">Nepodarilo sa odkodovat farby! stlpec ".$stlpec."</p>";

				die;

			}												

			if ($_SESSION['doublewords']=='yes') { // odkoduj druhe slovo    			

    			$decoded_r = OdkodujRS($rsk, $img_encoded, $stlpec, true, true);

    			if (($decoded_r==false)) {

    				echo "<p class=\"chyba\">Nepodarilo sa odkodovat farby! stlpec ".$stlpec."</p>";

    				die;

    			}															    

			}

		}// for stlpec			

	

		// 4. uloz zmeny

//DDD		imagepng($img_encoded, $_SESSION['local_decodedv_file'] );				
$_SESSION['s_img_decodedv'] = serialize(GetPNGData($img_encoded));

				

		// 5. vygeneruj obrazok ktory zobrazuje pocet rozdielov oproti originalnemu obrazku

		

	

		// 6. zobraz vsetky obrazky - povodny, uz prijaty s chybami, rozdielovy

		echo '<h3>DekÛdovanie - po stÂpcoch, riadkoch</h3>';

		ObsahStranky(9, true, 'decodedv', 'differv', "submit", 'index.php?section='.$section.'&amp;step=10', "DekÛduj horizont·lne", "");

		echo '<p class="odstavec">StÂpce, alias horizont·lne kÛdovÈ slov· obr·zka boli ˙speöne dekÛdovanÈ. Pokiaæ je zvolen˝ kÛd dostatoËn˝, 

		poËet chybn˝ch bodov by mal byù teraz menöÌ ak nie priamo rovn˝ nule. Ak eöte neboli opravenÈ vöetky chybnÈ body, 

		dekÛdovanie po riadkoch prin·öa Ôalöiu öancu. </p>';

				

	} // case 9

	if ($step==10) /* D E K O D O V A N I E   H O R I Z O N T A L N E */

	{

		// 1. otvor zakodovany obrazok

//DD		$img_encoded = imagecreatefrompng ( $_SESSION['local_decodedv_file'] );
$img_encoded = imagecreatefromstring(unserialize($_SESSION['s_img_decodedv']));

	

		// 2. vytvor RS objekt

		$rsk =  & new ReedSolomon($_SESSION['polynom'], 0, $_SESSION['tt']);

		if (($rsk->isValid) == false)		

		{

			echo "<p class=\"chyba\">Vytvorenie RS objektu zlyhalo! (zle parametre?)</p>";

			die;

		}

		

		// 3. prejdi postupne vsetky stlpce

		$riad = $_SESSION['kk'];

		if ($_SESSION['doublewords']=='yes') $riad*=2;

		for ($riadok = 0; $riadok < $riad; $riadok++)

		{				

			// zakoduj vsetky farby tohto riadku

			$decoded_r = OdkodujRS($rsk, $img_encoded, $riadok, false);

			if (($decoded_r==false)) {

				echo "<p class=\"chyba\">Nepodarilo sa odkodovat farby! riadok ".$riadok."</p>";

				die;

			}	

			if ($_SESSION['doublewords']=='yes') { // dekoduj aj druhe slovo ak sa pouzivaju dve kodove slova na mikroriadok

    			$decoded_r = OdkodujRS($rsk, $img_encoded, $riadok, false, true);

    			if (($decoded_r==false)) {

    				echo "<p class=\"chyba\">Nepodarilo sa odkodovat farby! riadok ".$riadok."</p>";

    				die;

    			}				    

			}

														

		}// for riadok	

		// 4. uloz zmeny

//DDD		imagepng($img_encoded, $_SESSION['local_decodedh_file'] );										
$_SESSION['s_img_decodedh'] = serialize(GetPNGData( $img_encoded));

			

		// 6. zobraz vsetky obrazky - povodny, uz prijaty s chybami, rozdielovy

		echo '<h3>Obr·zok skoro ako nov˝</h3>';

		ObsahStranky(10, true, 'decodedh', 'differh', "submit", 'index.php?section='.$section.'&amp;step=11', "Vyhodnotenie", "");

		echo '<p class="odstavec">A je tu koniec. DekÛdovanie ukonËenÈ aj vo vodorovnom smere. Pri dobre zvolenom kÛde pre urËit˝ 

		poËet ch˝b pri prenose by tieto dve dekÛdovania mali staËiù na to, aby  boli opravenÈ ˙plne vöetky chybnÈ body (z informaËnej Ëasti). 

		Ak chyby predsa len nejakÈ zostali, prepojenosù riadkov a stÂpcov <A href="?section=picture&amp;step=9&amp;again=1">umoûÚuje proces dekÛdovania zopakovaù</a>. 

		Je to ale veæk˝ risk! OpakovanÌm dekÛdovacieho procesu sa obr·zok buÔ mÙûe ˙plne opraviù, alebo naopak 

		˙plne <strong>pokaziù na nerozoznanie!</strong> </p>';

	} // case 10

	

	if ($step==11)

	{ // vyhodnotenie

?>	<H3>Vyhodnotenie - ako to prebiehalo</h3>

	<table id="vyhodnotenie">

	<tr>

	  <th>rozdielov˝ obr·zok</th>

	  <th>analyzovan˝ obr·zok</th>

	  <th>popis</th>

	  <th>poËet rozdielnych bodov / BER</th>

	</tr>

	<tr>

	  <td><img src="pic.php?pic=orig" title="PÙvodn˝ obr·zok" alt="PÙvodn˝ obr·zok"></td>

	  <td><img src="pic.php?pic=encoded" alt="ZakÛdovan˝ obr·zok" title="ZakÛdovan˝ obr·zok"></td>	  

	  <td class="text">Zdrojov˝ a zakÛdovan˝ obr·zok</td>

	  <td class="text2">identickÈ</td>

	</tr>

	<tr>

	<?php $altt = 'Chybn˝ch bodov: '.$_SESSION['rozdielov']['errored'].' ('.round(100*$_SESSION['rozdielov']['errored']/($_SESSION['kk']*$_SESSION['kk']*($_SESSION['doublewords']=='yes' ? 4 : 1)),2).

		'%), BER:&nbsp;'.$_SESSION['berber']['errored'];  ?>

	  <td><img src="pic.php?pic=differe&amp;juj=<?php echo time() ?>" ALT="<?php echo $altt; ?>" title="<?php echo $altt; ?>" ></td>	

	  <td><img src="pic.php?pic=errored&amp;juj=<?php echo time() ?>" alt="Zaöumen˝ obr·zok" title="Zaöumen˝ obr·zok"></td>

	  <td class="text">Zaöumen˝ obr·zok</td>

	  <td class="text2"><?php echo $altt; ?></td>

	</tr>

	<tr>	  

	  <?php $altt = 'Chybn˝ch bodov: '.$_SESSION['rozdielov']['decodedv'].' ('.round(100*$_SESSION['rozdielov']['decodedv']/($_SESSION['kk']*$_SESSION['kk']*($_SESSION['doublewords']=='yes' ? 4 : 1)),2).

		'%), BER:&nbsp;'.$_SESSION['berber']['decodedv']; ?>

	  <td><img src="pic.php?pic=differv&amp;juj=<?php echo time() ?>" alt="<?php echo $altt; ?>" title="<?php echo $altt; ?>" ></td>

	  <td><img src="pic.php?pic=decodedv&amp;juj=<?php echo time() ?>" alt="DekÛdovan˝ vertik·lne" title="DekÛdovan˝ vertik·lne"></td>	  

	  <td class="text">Obr·zok dekÛdovan˝ vertik·lne</td>

	  <td class="text2"> <?php echo $altt; ?> </td>

	</tr>

	<tr>	  

	  <?php $altt = 'Chybn˝ch bodov: '.$_SESSION['rozdielov']['decodedh'].' ('.round(100*$_SESSION['rozdielov']['decodedh']/($_SESSION['kk']*$_SESSION['kk']*($_SESSION['doublewords']=='yes' ? 4 : 1)),2).

		'%), BER:&nbsp;'.$_SESSION['berber']['decodedh']; ?>

	  <td><img src="pic.php?pic=differh&amp;juj=<?php echo time() ?>" alt="<?php echo $altt; ?>" title="<?php echo $altt; ?>" ></td>

	  <td><img src="pic.php?pic=decodedh&amp;juj=<?php echo time() ?>" alt="DekÛdovan˝ horizont·lne" title="DekÛdovan˝ horizont·lne"></td>	  

	  <td class="text">Obr·zok dekÛdovan˝ horizont·lne</td>

	  <td class="text2"> <?php echo $altt; ?> </td>

	</tr>	

	</table>

<?php			

	} // case 11 - vyhodnotenie

	

/* F U N K C I E */



/*

// zasum obrazok cez pocet chyb

function ZasumChyb (& $img_enc)

{    

		//$chybiek  = $_SESSION['nn']*$_SESSION['nn']*( $_SESSION['doublewords']=='yes' ? 4 : 1 )*$_SESSION['promile']/1000;

		$chybiek = $_SESSION['chbodov'];

		settype($chybiek, 'integer');    

        $nnn = $_SESSION['nn'];

        if ($_SESSION['doublewords']=='yes')

          $nnn = 2*$_SESSION['nn'];

        $pokial = $nnn*$nnn;          

        if ($chybiek>$pokial) $chybiek=$pokial;      

		mt_srand (mkseed());

		$chybky=array();		

		$stopper=0;						

		//echo "Chybiek: ".$chybiek." nn: $nn";

		for ($chb = 1; $chb <= $chybiek; $chb++)

		{

			// vygeneruj nahodny riadok a stlpec od 0 po N

			if ($_SESSION['doublewords']=='yes'){

				$nahoda_riadok = mt_rand(0, 2*$_SESSION['nn']-1);

				$nahoda_stlpec = mt_rand(0, 2*$_SESSION['nn']-1);				

			}

			else {

				$nahoda_riadok = mt_rand(0, $_SESSION['nn']-1);

				$nahoda_stlpec = mt_rand(0, $_SESSION['nn']-1);

			}

									

			$chyy = 'r'.$nahoda_riadok.'s'.$nahoda_stlpec;			

			if (in_array($chyy, $chybky, true)) {

			    if ($stopper<=15) {

			        $chb--;

			        $stopper++;

			    }

			}			

			else {

			    $chybky[] = $chyy;

			    $stopper=0;			

    			// vygeneruj nahodnu hodnotu v ramci Alfa hranic

			    $zla_farba_r = (mt_rand(0, $_SESSION['nn']))<<($_SESSION['cposun']);

			    $zla_farba = ($zla_farba_r<<16) + ($zla_farba_r<<8) + ($zla_farba_r);// ciernobiele

			    //echo $zla_farba."<BR>";

			    // nastav tuto farbu pre nahodny bod

			    imagesetpixel($img_enc, $nahoda_riadok, $nahoda_stlpec, $zla_farba); 

			} // if

		}       

}

*/



// zasum obrazok cez pocet chyb

function ZasumChyb (& $img_enc)

{    

		$chybiek = $_SESSION['chbodov'];

		settype($chybiek, 'integer');    

        $nnn = $_SESSION['nn'];

        if ($_SESSION['doublewords']=='yes')

          $nnn = 2*$_SESSION['nn'];

        $pokial = $nnn*$nnn;          

        if ($chybiek>$pokial) $chybiek=$pokial;      

        

        // inicializuj generator

		mt_srand (mkseed());

		

		// napln zdrojovu mnozinu

		$mnozina=array();		

		for ($xx = 0; $xx< $nnn; $xx++) {

		    for ($yy = 0; $yy<$nnn; $yy++)

		    {

		        $mnozina[] = $xx.'|'.$yy;		        

		    }

		}

		    

		$mnozina_prvkov = $pokial;

								

		for ($chb = 1; $chb <= $chybiek; $chb++)

		{

			// vygeneruj nahodne cislo od 0 po (mnozina_prvkov-1) // vyberazm z tych ktore este zostali

			$pokusov = 10;

			$nahoda = mt_rand(0, $mnozina_prvkov -1);

			$surad = explode('|', $mnozina[$nahoda]);

			$farba_povodna = ((imagecolorat($img_enc, $surad[0], $surad[1]) >>16 )& 0xFF )>>$_SESSION['cposun'] ;

			while ($pokusov) {			    		        								

			    // vygeneruj nahodnu hodnotu v ramci Alfa hranic			    			    

		        $zla_farba_r = (mt_rand(0, $_SESSION['nn']))<<($_SESSION['cposun']);		        		        	     

	            $pokusov--;

	            if ($zla_farba_r!=$farba_povodna) break;

	        }

		    // nastav tuto farbu pre nahodny bod

		    $zla_farba = ($zla_farba_r<<16) + ($zla_farba_r<<8) + ($zla_farba_r);// ciernobiele

		    imagesetpixel($img_enc, $surad[0], $surad[1], $zla_farba); 

		    

		    // odstran tento prvok z mnoziny		    

		    eliminarElementoArreglo($mnozina, $nahoda);

	        $mnozina_prvkov--;

		}       

}



// alex.chacon@terra.com

function eliminarElementoArreglo (& $array, $indice)

{

if (array_key_exists($indice, $array))

{

    // ziskaj hodnotu posledneho prvku, posledny prvok aj vymaz    

    $temp = array_pop($array);

    if ($temp!=NULL) {

        // hodnotu na potrebnej pozii nahrad tou z posledneho , ktory sme vymazali

        $array[$indice] = $temp;

    }

}

return $array;

}



function ZasumBER(& $img_encoded, $ber)

{

    mt_srand (mkseed());	

    if ($_SESSION['doublewords']=='yes')

        $pokial = 2*$_SESSION['nn'];

    else

        $pokial = $_SESSION['nn'];

        

	// chod cez vsetky riadky

	for ($riadok = 0; $riadok< $pokial; $riadok++)

	{

	    // cez vsetky stlpce, cize body v riadku

	    for ($stlpec=0; $stlpec< $pokial; $stlpec++)

	    {

	        // nacitaj bod

	        $farba = imagecolorat ($img_encoded, $stlpec, $riadok); // po riadkoch...

	        $farba_r = ((($farba >> 16) & 0xFF))>>$_SESSION['cposun']; // ke je len tato farba tak to bude ciernobiele

	  	    

	  	    // cez vsetky bity bodu

	  	    for ($bit=0; $bit<$_SESSION['pocetbitov']; $bit++)

	  	    {

	  	        $nahoda = mt_rand(0, (1/$ber)-1); // 0 znamena ze nastane preklopenie bitu

	  	        

	  	        if ($nahoda==0)

	  	        { // preklopenie bitu

	  	          

	  	            if ( $farba_r & ( 1<<$bit )) // nastaveny bit

	  	                $farba_r &= (0xFF - (1<<$bit));

	  	            else // nenastaveny bit

	  	                $farba_r |= (1<<$bit);

	  	        }

	  	        

	  	        

	  	    } // cez vsetky bity

	  	    

	  	    

	  	    // zapis zmeneny bod

            $farba = (($farba_r)<<(16+$_SESSION['cposun']) ) + (($farba_r)<<(8+$_SESSION['cposun']) ) + ((($farba_r)<<($_SESSION['cposun'])));  // ciernobiele            

            imagesetpixel($img_encoded, $stlpec, $riadok, $farba); // po stlpcoch

           	  	    

	        

	    }// cez vsetky body v riadku	    	

	    

	}// cez vsetky riadky

	

    

    

    

}



    



// degraduj farby obrazok dla nastaveni

function DegradeImage(& $image )

{	

	$im_width = $_SESSION['kk'];

	$im_height = $_SESSION['kk'];

	if ($_SESSION['doublewords']=='yes') { $im_width*=2; $im_height*=2; }

	

	if ($_SESSION['image_mode']==IMAGE_MODE_INDEXCOLOR)

	{

		imagetruecolortopalette ( $image, false, $_SESSION['nn']+1 );

	}

	else

	{

		for ($riadok = 0; $riadok < $im_height; $riadok++)

		{

			for ($bod=0; $bod < $im_width; $bod++)

			{

				$farba = imagecolorat ($image, $bod, $riadok);

				$farba_r = ((($farba >> 16) & 0xFF))>>$_SESSION['cposun'];

				$farba_g = ((($farba >> 8) & 0xFF))>>$_SESSION['cposun'];
				$farba_b = ((($farba >> 0) & 0xFF))>>$_SESSION['cposun'];

				switch($_SESSION['image_mode'])

				{

					case IMAGE_MODE_GRAYSCALE: {

						$farba = (($farba_r)<<(16+$_SESSION['cposun']) ) + (($farba_r)<<(8+$_SESSION['cposun']) ) + ((($farba_r)<<($_SESSION['cposun']))); 

						break;

					}				

					case IMAGE_MODE_FULLCOLOR: {

						$farba = (($farba_r)<<(16+$_SESSION['cposun']) ) + (($farba_g)<<(8+$_SESSION['cposun']) ) + ((($farba_b)<<($_SESSION['cposun']))); 

						break;

					}

					case IMAGE_MODE_INDEXCOLOR: {

						// ?? este neviem ...

						break;

					}				

				}

				imagesetpixel($image, $bod, $riadok, $farba);				

			}

		}	

	}

}





// zakoduje rskodom vstupne hodnoty

// rs - je pole RS koderov , riadok je index riadku ci stlpca, posledne je ci namiesto po riadkoch ma kodovat po stlpcoch

function & ZakodujRS( & $rs, & $image, $riadok, $riadok_as_stlpec = false, $secondword = false)

{

              /* vytvor polynom z potrebnych znakov */

              $nDestIndex = 0;$nDestBit = 0;$nSourceIndex = 0;$nSourceBit = 0;

              for ($idcko=0; $idcko < ($rs->k); $idcko++)

	       	$rs->OutputAlfa( -1, $sourceBuf, $nSourceIndex, $nSourceBit);// vynuluj zdrojovy buffer

              for ($idcko=0; $idcko < ($rs->n); $idcko++)

               	$rs->OutputAlfa( -1, $destBuf, $nDestIndex, $nDestBit); // vynuluj cielovy buffer              

               

              $nSourceIndex = 0;$nSourceBit = 0;

		

	      $od = 0;

  	      $po = $rs->k; 

  	      if ($secondword) { $od = $rs->k; $po=(2*$rs->k); }

              for ($hj=$od; $hj< $po ; $hj++) {

              	if ( $_SESSION['image_mode'] == IMAGE_MODE_INDEXCOLOR ) {

              		// indexovane farby	

              		if ($riadok_as_stlpec)

              			$farba_i = imagecolorat ($image, $riadok, $hj);

              		else

              			$farba_i = imagecolorat ($image, $hj, $riadok);

			$rs->OutputAlfa($farba_i -1 , $sourceBuf, $nSourceIndex, $nSourceBit);              		

              	}

              	elseif($_SESSION['image_mode'] == IMAGE_MODE_GRAYSCALE) {

              		// ciernobiele

	              	if ($riadok_as_stlpec)

        	      		$farba = imagecolorat ($image, $riadok, $hj); // po stlpcoch..

	              	else

				$farba = imagecolorat ($image, $hj, $riadok); // po riadkoch...

			$farba_r = ((($farba >> 16) & 0xFF))>>$_SESSION['cposun']; // ke je len tato farba tak to bude ciernobiele              	

			$rs->OutputAlfa($farba_r -1 , $sourceBuf, $nSourceIndex, $nSourceBit);

		}	

                

              }



              /* zakoduj */

              $nDestIndex = 0; $nDestBit = 0; $nSourceIndex = 0; $nSourceBit = 0;

              $zak_r = $rs->EncodeSystematic($sourceBuf, $destBuf, $nSourceIndex, $nDestIndex, $nSourceBit, $nDestBit );              		

              

              if ($zak_r)

              {

                  // a teraz tie binarne data naspat prehod

                 $nDestIndex = 0; $nDestBit = 0; $encoded=array();

                 $idod = 0;

                 $idpo = $rs->n;

                 if ($secondword) { $idod = $rs->n; $idpo = 2*$rs->n; }

                 for ($idcko=$idod; $idcko< $idpo; $idcko++) 

                 {      

                 	$idcko_n = $idcko;

                 	if ($_SESSION['doublewords']=='yes') {

                 		if ($secondword) {

                 			$idcko_n-= ($rs->t *2 );	

                 			if ($idcko_n>=(2*$rs->k)) $idcko_n+=(2*$rs->t);

                 		}

                 		else {

                 			if ($idcko_n>=$rs->k) $idcko_n+=$rs->k;

                 		}                 		                 		

                 	}

                 	                 	

                 	//if (($_SESSION['doublewords']=='yes')&&($secondword==false)&&($idcko>=($rs->k))) $idcko_n+=$rs->n; // aby v druhom slove nadbyt.bola na konci

                 	

                 	if ($_SESSION['image_mode'] == IMAGE_MODE_INDEXCOLOR) {

                 		// indexovane farby

                 		$farba_i = 1+ $rs->InputAlfa($destBuf, $nDestIndex, $nDestBit);

				if ($riadok_as_stlpec)

					imagesetpixel($image, $riadok, $idcko_n, $farba_i); // po stlpcoch

				else

					imagesetpixel($image, $idcko_n, $riadok, $farba_i); // po riadkoch                   		

                 	}

                 	elseif ($_SESSION['image_mode'] == IMAGE_MODE_GRAYSCALE) {

                 		// true color RBG farby

                 		$farba_r = 1+ $rs->InputAlfa($destBuf, $nDestIndex, $nDestBit);

                  		$farba = (($farba_r)<<(16+$_SESSION['cposun']) ) + (($farba_r)<<(8+$_SESSION['cposun']) ) + ((($farba_r)<<($_SESSION['cposun'])));  // ciernobiele

				if ($riadok_as_stlpec)

					imagesetpixel($image, $riadok, $idcko_n, $farba); // po stlpcoch

				else

					imagesetpixel($image, $idcko_n, $riadok, $farba); // po riadkoch  

			}

                 }

                 return true; 

              }

              else              

                 return false;              

}







// Odkoduj RS kodom riadok ci stlpec obrazka

function & OdkodujRS( & $rs, & $image, $riadok, $riadok_as_stlpec = false, $secondword = false  )

{

    /* vytvor polynom z potrebnych znakov */

    $nDestIndex = 0;$nDestBit = 0;$nSourceIndex = 0;$nSourceBit = 0;

    for ($idcko=0; $idcko < ($rs->n); $idcko++)

        $rs->OutputAlfa( -1, $sourceBuf, $nSourceIndex, $nSourceBit);// vynuluj zdrojovy buffer

    for ($idcko=0; $idcko < ($rs->n); $idcko++)

        $rs->OutputAlfa( -1, $destBuf, $nDestIndex, $nDestBit); // vynuluj cielovy buffer

                

    $nSourceIndex = 0;$nSourceBit = 0;

	$od = 0;

	$po = $rs->n;

	if ($secondword) { $od=$rs->n; $po=2*$rs->n; }

    for ($hj=$od; $hj< $po ; $hj++) {

        $idcko_n = $hj;

       	if ($_SESSION['doublewords']=='yes') {

       		if ($secondword) {

       			$idcko_n-= ($rs->t *2 );	

       			if ($idcko_n>=(2*$rs->k)) $idcko_n+=(2*$rs->t);

       		}

       		else {

       			if ($idcko_n>=$rs->k) $idcko_n+=$rs->k;

       		}                 		                 		

       	}

                                    

        if ($riadok_as_stlpec)

            $farba = imagecolorat ($image, $riadok, $idcko_n); // po stlpcoch..

        else

		    $farba = imagecolorat ($image, $idcko_n, $riadok); // po riadkoch...

	    $farba_r = ((($farba >> 16) & 0xFF))>>$_SESSION['cposun']; // ke je len tato farba tak to bude ciernobiele

	    //$farba_g = ((($farba >> 8) & 0xFF))>>$GLOBALS['cposun'];

	    //$farba_b = (($farba & 0xFF))>>$GLOBALS['cposun'];

          	

        $rs->OutputAlfa($farba_r -1 , $sourceBuf, $nSourceIndex, $nSourceBit);              

    }



    /* odkoduj */

    $nDestIndex = 0; $nDestBit = 0; $nSourceIndex = 0; $nSourceBit = 0;

    $chybiek = 0;// kolko opravil chyb

    if ($rs->DecodeSystematic($sourceBuf, $destBuf, $nSourceIndex, $nDestIndex, $nSourceBit, $nDestBit, $chybiek, true)) // true lebo citame aj opraveny overhead

    {

        // a teraz tie binarne data naspat prehod

        $nDestIndex = 0; $nDestBit = 0; $encoded=array();

        $idod = 0;

        $idpo = $rs->n;

        if ($secondword) { $idod = $rs->n; $idpo = 2*($rs->n); }

        for ($idcko=$idod; $idcko< $idpo; $idcko++) 

        {                                             

     	    $idcko_n = $idcko;

     	    if ($_SESSION['doublewords']=='yes') {

         		if ($secondword) {

         			$idcko_n-= ($rs->t *2 );	

     	    		if ($idcko_n>=(2*$rs->k)) $idcko_n+=(2*$rs->t);

         		}

         		else {

     	    		if ($idcko_n>=$rs->k) $idcko_n+=$rs->k;

         		}                 		                 		

         	}                    

                    	                 	

      	    $farba_r = 1+ $rs->InputAlfa($destBuf, $nDestIndex, $nDestBit);



            //$farba = (($farba_r)<<(16+$GLOBALS['cposun']) ) + (($farba_g)<<(8+$GLOBALS['cposun']) ) + ((($farba_b)<<($GLOBALS['cposun']))); // farebne

            $farba = (($farba_r)<<(16+$_SESSION['cposun']) ) + (($farba_r)<<(8+$_SESSION['cposun']) ) + ((($farba_r)<<($_SESSION['cposun'])));  // ciernobiele

            if ($riadok_as_stlpec)

                imagesetpixel($image, $riadok, $idcko_n, $farba); // po stlpcoch

            else

		    	imagesetpixel($image, $idcko_n, $riadok, $farba); // po riadkoch   	

        }

        return true; 

    }

    else              

        return false;              

}





function mkseed()

   {

       $hash = md5(microtime());

       $loWord = substr($hash, -8);

       $seed = hexdec($loWord);

       $seed &= 0x7fffffff;



       return $seed;

   }



/* vytvor diferencny obrazok a spocitaj rozdielnych bodov (len v rozmedzi velkosti zdrojoveho suboru) */

/* berko - sem sa zapise priemerne BER zistene */

function CountDiffs($compare, & $berko)

{

	$differ = 0;

	

	switch ($compare)

	{

		case 'encoded': { 

			$file2 = $_SESSION['local_encoded_file'];

			$differfile = ''; // nema differ file

            $namko = 'differo';
			break;

		}

		case 'errored': {

			$file2 = $_SESSION['local_errored_file'];

			$differfile = $_SESSION['local_differe_file'];
            $pnamko = 'errored';
            $namko = 'differe';
			break;

		}

		case 'decodedv': {

			$file2 = $_SESSION['local_decodedv_file'];

			$differfile = $_SESSION['local_differv_file'];
            $pnamko = 'decodedv';
            $namko = 'differv';
			break;

		}

		case 'decodedh': {

			$file2 = $_SESSION['local_decodedh_file'];

			$differfile = $_SESSION['local_differh_file'];
            $pnamko = 'decodedh';
            $namko = 'differh';
			break;

		}

		

	}

	

	

	// 2. otvor povodny a prijaty subor + vytvor diferencny subor

//DD	$img_orig = imagecreatefrompng ( $_SESSION['local_orig_file'] );
    $img_orig = imagecreatefromstring(unserialize($_SESSION['s_img_orig']));

//DD	$img_encoded = imagecreatefrompng ( $file2 );
    $img_encoded = imagecreatefromstring(unserialize($_SESSION['s_img_'.$pnamko]));

	

	$berko = 0;

	$kkk = $_SESSION['kk'];

	$bitov_spolu = $_SESSION['kk'] * $_SESSION['kk'] * $_SESSION['pocetbitov'];

	$bitov_zlych = 0;

	if ($_SESSION['doublewords']=='yes') {

	    $kkk*=2;

	    $bitov_spolu *= 4;

	}

	

	

	$img_differ = imagecreatetruecolor ($kkk, $kkk);	

	

	// 3. prejdi postupne vsetky riadky ( ale len od 0 po kk !)

	for ($riadok = 0; $riadok < $kkk; $riadok++)

	{

		for ($stlpec = 0; $stlpec < $kkk; $stlpec++)

		{

			// zisti farby v povodnom a zmenenom obrazku

			$farba_orig = imagecolorat ($img_orig, $riadok, $stlpec);

			$farba_encoded = imagecolorat ($img_encoded, $riadok, $stlpec);			

			imagesetpixel($img_differ, $riadok, $stlpec, FARBA_BOD_OK); // zmen farbu ze je bod ok.

			// porovnaj ich

			if ($farba_orig != $farba_encoded)

			{

				$differ++; // zvys pocet chyb

				imagesetpixel($img_differ, $riadok, $stlpec, FARBA_BOD_CHYBA); // vyznac v differ. subore bod ze je tam chyba

				

				// porovnaj po bitoch				

			    $farba_orig = ((($farba_orig >> 16) & 0xFF))>>$_SESSION['cposun']; // ciernobiele

       			$farba_encoded = ((($farba_encoded >> 16) & 0xFF))>>$_SESSION['cposun']; // ke je len tato farba tak to bude ciernobiele              							

				for ($bitt = 0; $bitt< $_SESSION['pocetbitov']; $bitt++)

				{			    				    				    

				    if ( (($farba_orig >> $bitt) & 0x1) != (($farba_encoded >> $bitt)& 0x1)) {

				        $bitov_zlych++;				        

				    }

				    

				}												

				

			}			

		}	// for sltpce		

	} // for riadok

	

	// 4. zapis zmeny do diferencneho suboru

//DDD	imagepng($img_differ, $differfile );					
$_SESSION['s_img_'.$namko] = serialize(GetPNGData($img_differ));

	

	// 5. vrat pocet chyb

	unset($img_orig);

	unset($img_encoded);

	unset($img_differ);	

	$_SESSION['rozdielov'][$compare] = $differ;

	

	settype($berko, 'double');

	$berko = round( $bitov_zlych / $bitov_spolu, 8);

	$_SESSION['berber'][$compare] = $berko;

	//echo "zl: $bitov_zlych<BR>vs: $bitov_spolu<BR>ber: $berko<BR>differ: $differ<BR>\n";

	

	return $differ;

}





/* GENERUJ OBSAH STRANKY KROKU NIEKTOREHO */

// parametre pic_orig je true/false, 'pic_encoded' su  encoded,errored,decodedv, decodedh,

//  pic_differ je differe, differv, differh  , $other - string pridany do tabulky (dalsie polia napriklad)

function ObsahStranky($step, $pic_orig=false, $pic_encoded='', $pic_differ='', $submit_name, $submit_action, $submit_value, $other)

{	

	$kkk = $_SESSION['kk'];

	$nnn = $_SESSION['nn'];	

		

	echo '<DIV class="images_form_table">'."\n";

	if ($pic_orig) 

		echo '<IMG title="PÙvodn˝ obr·zok" SRC="pic.php?pic=orig&amp;juj='.time().'" WIDTH="'.(2*$kkk).'" HEIGHT="'.(2*$kkk).'">';

	if ($pic_encoded!='') 

		echo '<IMG title="Obr·zok" SRC="pic.php?pic='.$pic_encoded.'&amp;juj='.time().'" WIDTH="'.(2*$nnn).'" HEIGHT="'.(2*$nnn).'" >';

	if ($pic_differ!='') {

		// zisti pocet rozdielov najprv

		$berka=0; // pre zistenie BER

		$nasiel_chyb = CountDiffs($pic_encoded, $berka);

//		$altt = 'Rozdielov: '.$nasiel_chyb.' ('.round(1000*$nasiel_chyb/($kkk*$kkk*($_SESSION['doublewords']=='yes' ? 4 : 1)),2).

		' promile z obsahu pov.obr.)';

		$altt = 'Chybn˝ch bodov: '.$nasiel_chyb.' ('.round(100*$nasiel_chyb/($_SESSION['kk']*$_SESSION['kk']*($_SESSION['doublewords']=='yes' ? 4 : 1)),2).

		'%), BER: '.$berka;

		echo '<IMG SRC="pic.php?pic='.$pic_differ.'&amp;juj='.time().'" title="'.$altt.'" ALT="'.$altt.'" WIDTH="'.(2*$kkk).'" HEIGHT="'.(2*$kkk).'" >';

	}	

		

	if (!empty($submit_name))  {

		echo '<FORM name="step'.$step.'" METHOD="POST" ACTION="'.$submit_action.'">';

		if (!empty($other)) echo $other."\n";

			echo '<INPUT TYPE="submit" name="'.$submit_name.'" value="'.$submit_value.'">';

		echo "</FORM>\n";

	}

	

	echo "</div>\n";

	

}



function GetPNGData($image)
{
    ob_start();
    imagepng($image);
    $image_data = ob_get_contents();
    ob_end_clean();
    return $image_data;
}



?>

</div>