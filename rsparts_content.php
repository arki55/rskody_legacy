<script language="JavaScript">

	function FuncKlik(valuee) {	 

	    //valuee = 'rr';   	    

	    

	    document.formm.submitt.value=valuee;

	    

	    document.formm.submit(valuee); 

	    

	   }

	</script>

<div id="rsparts_obsah">

<?php



	/*  Rocnikovy projekt RS kody - vysvetlenie a testovanie jednotlivych blokov RS kodera  */

	/* (C) 2003 Miroslav Ïurèík */



	include "./ReedSolomon.php";

	include "./funcs.php";

	

	$sub = $_GET['sub']; // zisti ktory komponent chce zobrazit

	  

    /* precitat POST premenne ak su nastavit ich do session premennych  */

    if (isset($_POST['fieldgenerator'])) $_SESSION['p_fieldgenerator'] = $_POST['fieldgenerator'];

    if (isset($_POST['first_add'])) $_SESSION['p_first_add'] = $_POST['first_add'];

    if (isset($_POST['second_add'])) $_SESSION['p_second_add'] = $_POST['second_add'];

    if (isset($_POST['first_addg'])) $_SESSION['p_first_addg'] = $_POST['first_addg'];

    if (isset($_POST['second_addg'])) $_SESSION['p_second_addg'] = $_POST['second_addg'];

    if (isset($_POST['first_mult'])) $_SESSION['p_first_mult'] = $_POST['first_mult'];

    if (isset($_POST['second_mult'])) $_SESSION['p_second_mult'] = $_POST['second_mult'];

    if (isset($_POST['up_div'])) $_SESSION['p_up_div'] = $_POST['up_div'];

    if (isset($_POST['down_div'])) $_SESSION['p_down_div'] = $_POST['down_div'];

    if (isset($_POST['unknown_solve'])) $_SESSION['p_unknown_solve'] = $_POST['unknown_solve'];

    if (isset($_POST['poly_solve'])) $_SESSION['p_poly_solve'] = $_POST['poly_solve'];

    

    /* POST premenne registrovej casti */

    if (isset($_POST['regsize'])&&(isset($_POST['codegenerator']))&&(isset($_POST['fieldgenerator'])))

    { // ak doslo k zmene tak znic nastavenie registra v session

        if (( $_POST['regsize']!=$_SESSION['p_regsize'] )||( $_POST['codegenerator']!=$_SESSION['p_codegenerator'] )||( $_POST['fieldgenerator']!=$_SESSION['p_fieldgenerator'] ))

            unset($_SESSION['p_regarray']);			               

    }

    if (isset($_POST['regsize'])) $_SESSION['p_regsize'] = $_POST['regsize'];

    if (isset($_POST['codegenerator'])) $_SESSION['p_codegenerator'] = $_POST['codegenerator'];

    if (isset($_POST['alfa_pushdiv'])) $_SESSION['p_alfa_pushdiv'] = $_POST['alfa_pushdiv'];    

    if (isset($_POST['alfa_pushnon'])) $_SESSION['p_alfa_pushnon'] = $_POST['alfa_pushnon'];    

    if (isset($_POST['alfa_pushsys'])) $_SESSION['p_alfa_pushsys'] = $_POST['alfa_pushsys'];    

    

    //if (isset($_POST['firstindex'])) $_SESSION['p_firstindex'] = $_POST['firstindex'];

    if (isset($_POST['firstindex'])) $_SESSION['p_firstindex'] = 0 ;

	if (isset($_POST['gfbits'])) $_SESSION['p_gfbits'] = $_POST['gfbits'];

	if (isset($_POST['errors'])) $_SESSION['p_errors'] = $_POST['errors'];

	        

    // nazvy obrazkov

    $_SESSION['local_reg_div'] = PROJEKT_DIR.TEMP_DIR.'reg_div_'.$PHPSESSID.'.png';

    $_SESSION['local_reg_sys'] = PROJEKT_DIR.TEMP_DIR.'reg_sys_'.$PHPSESSID.'.png';

    $_SESSION['local_reg_non'] = PROJEKT_DIR.TEMP_DIR.'reg_non_'.$PHPSESSID.'.png';

    

    

    //echo $_POST['submitt'];    

	

	function FirstKlik($premenna, $value, $name)

	{	  

	    $ch = '<input type="text" class="param" name="'.$name.'" ';

	    if (((strlen($premenna)<=0)||(strcmp($premenna,$value)==0)))

	        $ch .= ' SIZE="'.strlen($value).'" VALUE="'.$value.'" onclick="JavaScript: document.formm.'.$name.'.value='."'".''."'".' " ';    

	    else

	        $ch .= ' VALUE="'.$premenna.'" SIZE="'.strlen($premenna).'" ';	        	

	    $ch .= '>';

	    return $ch;

	}	

	

	function FuncClick($value){

	    return '<input type="submit" name="submitt" class="func_click" value="'.$value.'">';

	    //return ' onclick="FuncKlik('."'".$value."'".')" ';    

	}

		

	/* KTORA CAST SA MA ZOBRAZIT */

	switch ($sub) {

		

		case 'gf': { /* Konecne  Pole  */

			echo '<h3>Koneèné pole (trieda GF)</h3>

			<form name="formm" method="POST" action="index.php?section=rsparts&amp;sub=gf">';

			

			// ak je nastaveny fieldgenerator tak vytvor rs

			$gf_res = ""; $gf_add_res='';$gf_addg_res='';$gf_mult='';$gf_div_res='';$gf_solve_res='';$gf_test_res='';									

			$GLOBALS['inp_ok']=false;

            

			if (isset($_SESSION['p_fieldgenerator'])&&isset($_POST['submitt'])) {

			    $subm = $_POST['submitt'];

			    

			    $gf = new GF($_SESSION['p_fieldgenerator']);

			    if ($gf->isValid) {

			        $gf_res = '&nbsp;=&nbsp;<span class="msg_ok">OK</span>';

			        $GLOBALS['inp_ok']=true;



    			    switch($subm) {

                        case 'Add':  {

                            settype( $first_add,'integer');settype($second_add,'integer');

                            $rr = $gf->Add($first_add, $second_add);

                            $gf_add_res = '&nbsp;=&nbsp;<span class="msg_ok">'.sprintf("%d",($rr)).'&nbsp;(0x'.GetBinary($rr,16).')</span>';

                            break;

                        }

                        case 'AddByGrade': {

                            settype($first_addg,'integer');settype($second_addg,'integer');                            

                            if (($first_addg < -1)||($first_addg > ($gf->q -2)))

                                $gf_addg_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlá mocnina &alpha; prvku</span>';

                            elseif (($second_addg < -1)||($second_addg > ($gf->q -2)))

                                $gf_addg_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlá mocnina &alpha; prvku</span>';

                            else                                                        

                                $gf_addg_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($gf->AddByGrade($first_addg, $second_addg)).'</sup></span>';

                            break;

                        }

                        case 'MultiplyByGrade': {

                            settype($first_mult,'integer');settype($second_mult,'integer');

                            if (($first_mult < -1)||($first_mult > ($gf->q -2)))

                                $gf_mult_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlá mocnina &alpha; prvku</span>';

                            elseif (($second_mult < -1)||($second_mult > ($gf->q -2)))

                                $gf_mult_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlá mocnina &alpha; prvku</span>';                                                        

                            else

                                $gf_mult_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($gf->MultiplyByGrade($first_mult, $second_mult)).'</sup></span>';

                            break;

                        }

                        case 'DivideByGrade': {

                            settype($up_div,'integer');settype($down_div,'integer');

                            if (($up_div < -1)||($up_div > ($gf->q -2)))

                                $gf_div_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlá mocnina &alpha; prvku</span>';

                            elseif (($down_div < -1)||($down_div > ($gf->q -2)))

                                $gf_div_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlá mocnina &alpha; prvku</span>';

                            else

                                $gf_div_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($gf->DivideByGrade($up_div, $down_div)).'</sup></span>';

                            break;

                        }

                        case 'SolvePolynom': {

                            settype($unknown_solve,'integer');	settype($unknown_poly,'string');

                            if (($unknown_solve < -1)||($unknown_solve > ($gf->q -2)))

                                $gf_solve_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlá mocnina &alpha; prvku</span>';

                            else {

                                $poly_solveSLL = & $gf->PolynomToSll($poly_solve);

                                if ($poly_solveSLL==false)

                                    $gf_solve_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlı polynóm</span>';

                                else

                                    $gf_solve_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($gf->SolvePolynom($unknown_solve, $poly_solveSLL)).'</sup></span>';

                            }                                   

                            break;

                        }

                        case 'TestDistinct': {

                            $gf_test_res = '&nbsp;=&nbsp;<span class="msg_ok">'.($gf->TestDistinct()).'</span>';

                            break;

                        }

                        

    			    }	 // switch		    

			        			        

			    }  // is valid

			    else $gf_res = '&nbsp;=&nbsp;<span class="msg_bad">Chyba</span>';			    

			    

			}

						

			

			echo '<p class="podsub">Vytvorenie objektu</p>

			<ul>

			<li>'.FuncClick('GF').'&nbsp;(<em class="param">'.FirstKlik($fieldgenerator,'primitívny polynóm', 'fieldgenerator').'</em>)'.$gf_res.'

				<BR>Objekt je vytvorenı zadaním primitívneho polynómu, ktorı dokáe vytvori

				GF(q) s poadovanım poètom a rozmiestnením <span class="math">a</span> prvkov.</li>

			</ul>';						

			echo '<p class="podsub">Operácie nad koneènım po¾om</p>

			<ul>									

			<li>'.FuncClick('Add').'&nbsp; (<em class="param">'.FirstKlik($first_add,'first','first_add').'</em>, <em class="param">'.FirstKlik($second_add,'second','second_add').'</em>)'.$gf_add_res.'

				<BR>Spoèíta napríklad 6 + 1 = 7 (bit po bite) .</li>

			<li>'.FuncClick('AddByGrade').'&nbsp;(<em class="param">'.FirstKlik($first_addg,'first','first_addg').'</em>, <em class="param">'.FirstKlik($second_addg,'second','second_addg').'</em>)'.$gf_addg_res.'

				<BR>Súèet pod¾a mocniny, musí taktie nájs vısledné <span class="math">a</span>.</li>

			<li>'.FuncClick('MultiplyByGrade').'&nbsp; (<em class="param">'.FirstKlik($first_mult,'first','first_mult').'</em>, <em class="param">'.FirstKlik($second_mult,'second','second_mult').'</em>)'.$gf_mult_res.'

				<BR>Násobenie, èie sèítanie mocnín, úprava pri preteèení.</li>

			<li>'.FuncClick('DivideByGrade').'&nbsp; (<em class="param">'.FirstKlik($up_div,'up','up_div').'</em>, <em class="param">'.FirstKlik($down_div,'down','down_div').'</em>)'.$gf_div_res.'

				<BR>Delenie, èie odèítanie mocnín, úprava pri preteèení.</li>

			<li>'.FuncClick('SolvePolynom').'&nbsp;(<em class="param">'.FirstKlik($unknown_solve,'neznáma','unknown_solve').'</em>, <em class="param">'.FirstKlik($poly_solve,'polynóm','poly_solve').'</em>)'.$gf_solve_res.'

				<BR>Dosadenie za X do polynómu nejaké konkrétne <span class="math">a</span>.</li>

			<li>'.FuncClick('TestDistinct').'&nbsp;()'.$gf_test_res.'

				<BR>Test, èi sa niektoré <span class="math">a</span> prvky binárne nezhodujú. Podmienkou pouite¾nosti koneèného po¾a

				vytvoreného poadovanım primitívnym polynómom pre R&amp;S kódy je, aby sa iaden prvok neopakoval.</li>

			</ul>

			<p class="podsub">Pomocné funkcie</p>

			<ul>

			<li><em class="func">PolynomToSll</em>&nbsp;(<em class="param">polynóm</em>)

				<BR>Analyzuje polynóm(reazec) a prevedie ho do svojej internej formy v podobe SLL štruktúry</li>

			<li><em class="func">SLLToPolynom</em>&nbsp;(<em class="param">SLL objekt</em>, <em class="param">cielovypolynom</em>)

				<BR>Spätné vytvorenie polynómu-reazca z internej štruktúry SLL.</li>

			<li><em class="func">PrintDebug()</em>&nbsp;

				<BR>Vypíše základné informácie o vytvorenom koneènom poli.</li>

			<li><em class="func">PrintBinaryDebug </em>&nbsp;(<em class="param">binar</em>, <em class="param">bitcount</em>)

				<BR>Vypíše èíslo binar v podobe núl a jednièie - prvıch bitcount bitov.</li>

			</ul>

			<p class="podsub">Parametre a interné premenné koneèného po¾a</p>			

			<table>

			<tr><td><em class="prem">array:</em>&nbsp;</td><td>pole integer premennıch binárnej reprezentácie <span class="math">a</span>lfa prvkov</td></tr>

			<tr><td><em class="prem">array_inverse:</em>&nbsp;</td><td>inverzné pole, teda index je binárna reprezentácia a obsah je <span class="math">a</span>lfa</td></tr>

			<tr><td><em class="prem">generator:'.( isset($_POST['submitt']) ? '<span class="msg_ok">&nbsp;=&nbsp;'.$gf->generator.'&nbsp;(0x'.GetBinary($gf->generator, $gf->m+1 ).')</span>'  : '' ).'</em>&nbsp;</td><td>primitívny polynóm ktorı vygeneroval toto GF(q) v binárnom zápise</td></tr>

			<tr><td><em class="prem">m:'.( isset($_POST['submitt']) ? '<span class="msg_ok">&nbsp;=&nbsp;'.$gf->m."</span>"  : '' ).'</em>&nbsp;</td><td>mocnina v GF(2<sup>m</sup>)</td></tr>

			<tr><td><em class="prem">q:'.( isset($_POST['submitt']) ? '<span class="msg_ok">&nbsp;=&nbsp;'.$gf->q."</span>"  : '' ).'</em>&nbsp;</td><td>q = 2<sup>m</sup></td></tr>

			<tr><td><em class="prem">isValid:'.( isset($_POST['submitt']) ? '<span class="msg_ok">&nbsp;=&nbsp;'.$gf->isValid."</span>"  : '' ).'</em>&nbsp;</td><td>po úspešnom vytvorení GF(q) sa v konštruktore nastaví na true.</td></tr>

			</table>

			<inputt type="hidden" name="submitt" value="nic">

			</form>

			';

		    

			break;	

		}

		

		case 'register': { /* Register */

			echo '<h3>Posuvnı register (trieda Register)</h3><form name="formm" method="POST" action="index.php?section=rsparts&amp;sub=register">';

			$GLOBALS['inp_ok'] = false;

			

		    // ak je tam genpoly=auto tak z rs kodu vygeneruj spravny gener.polynom

   		    if ($_GET['genpoly']=='auto') {   		            

    	        // vytvor rs kod

	            $rsk = & new ReedSolomon($_SESSION['p_fieldgenerator'], $_SESSION['p_firstindex'], $_SESSION['p_regsize']/2 );

                if ($rsk->isValid==true) {

                       // echo $rsk->genPolyString;

                     $_SESSION['p_codegenerator']=$rsk->genPolyString;

                }                            

	        }			

			

			$reg_res='';$reg_clear_res='';$reg_isclear_res='';$reg_pushdiv_res='';$reg_pushnon_res='';$reg_pushsys_res='';$reg_flush_res='';

			// ak su nastavene session premenne tak vytvor co treba

						

			if(isset($_SESSION['p_fieldgenerator'])&&isset($_SESSION['p_regsize'])&&isset($_POST['submitt'])&&isset($_SESSION['p_codegenerator']))

			{

			    $gf = & new GF($_SESSION['p_fieldgenerator']);

                if ($gf->isValid) {

			        $reg_res = '&nbsp;=&nbsp;<span class="msg_ok">OK</span>';

			        			        

			        

			        // oki.. a teraz vytvorenie registra			        

            		$generat = 	& $gf->PolynomToSll($_SESSION['p_codegenerator']);

           			$reg = new Register($_SESSION['p_regsize'], $gf, $generat);

			        if ($reg!=NULL)

			        {

			            // vsetko ok.. mozeme pracovat

			            

			            // skus vytiahnut pole prvkov zo session, musia byt rovnako velke

			            if(is_array($_SESSION['p_regarray']))

			            {

			                $reg->registre = $_SESSION['p_regarray'];

			            }

			            $GLOBALS['inp_ok'] = true;

			            

			            // co treba zrobit ?

			            $subm = $_POST['submitt'];

			            switch ($subm) {

			                case 'Register': {

			                    

			                    break;

			                }

			                case 'Clear': {

			                    $reg->Clear();

			                    $reg_clear_res = '&nbsp;=&nbsp;<span class="msg_ok">OK</span>';    

			                    break;

			                }

			                case 'isClear': {

			                    $reg_isclear_res = '&nbsp;=&nbsp;<span class="msg_ok">'.($reg->isClear() ? 'Áno' : 'Nie').'</span>';    

			                    break;

			                }

			                case 'PushDivision': {

			                    settype($alfa_pushdiv, 'integer');

                                if (($alfa_pushdiv < -1)||($alfa_pushdiv > ($gf->q -2)))

                                    $reg_pushdiv_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlá mocnina &alpha; prvku</span>';

                                else                                                        

                                    $reg_pushdiv_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($reg->PushDivision($alfa_pushdiv)).'</sup></span>';    

			                    break;

			                }

			                case 'PushNormal': {

			                    settype($alfa_pushnon, 'integer');

                                if (($alfa_pushnon < -1)||($alfa_pushnon > ($gf->q -2)))

                                    $reg_pushnon_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlá mocnina &alpha; prvku</span>';

                                else                                                        

                                    $reg_pushnon_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($reg->PushNormal($alfa_pushnon)).'</sup></span>';                                

			                    break;

			                }

			                case 'PushSystematic': {

			                    settype($alfa_pushsys, 'integer');

                                if (($alfa_pushsys < -1)||($alfa_pushsys > ($gf->q -2)))

                                    $reg_pushsys_res = '&nbsp;=&nbsp;<span class="msg_bad">Zlá mocnina &alpha; prvku</span>';

                                else                                                        

                                    $reg_pushsys_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($reg->PushSystematic($alfa_pushsys)).'</sup></span>';                            

			                    break;

			                }

			                case 'FlushRegister': {

			                    $reg_flush_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($reg->FlushRegister()).'</sup></span>';    

			                    break;

			                }			                			               			                

			            }// switch

			            

			            

			            // koniec prace

			        }

			        else

			            $reg_res = '&nbsp;=&nbsp;<span class="msg_bad">Chyba - pri vytváraní registra.</span>';

			    }

			    else

			        $reg_res = '&nbsp;=&nbsp;<span class="msg_bad">Chyba - zlı primitívny polynóm na vytvorenie GF.</span>';

			        

			        

                // uloz stav  registra

                $_SESSION['p_regarray'] = $reg->registre;			        

			} // if			

			

						

			// vytvor objekt - dlzka je 2 * t - zalozny.. ak nieje platny od usera

			if (empty($reg)||($reg==NULL)) {

			    $gf = & new GF('x3+x+1');    

			    $genpol = 'x4+a2x3+a5x2+a5x1+a6';

                $generat = 	& $gf->PolynomToSll($genpol);			    

			    $regt = & new Register(4, $gf, $generat);

    			// standardne obrazky

			    CreateImageDiv($_SESSION['local_reg_div'], $regt);

                CreateImageNon($_SESSION['local_reg_non'], $regt);

                CreateImageSys($_SESSION['local_reg_sys'], $regt);			    

			}

			else

			{

                // vytvor obrazky - podla toho ake zadaludaje user			

			    CreateImageDiv($_SESSION['local_reg_div'], $reg, true);

                CreateImageNon($_SESSION['local_reg_non'], $reg, true);

                CreateImageSys($_SESSION['local_reg_sys'], $reg, true);    			    

			}

			

						echo '

			<p class="podsub">Vytvorenie objektu</p>

			<ul>

			<li><em class="func">'.FuncClick('Register').'</em>&nbsp;(<em class="param">'.FirstKlik($regsize,'regsize','regsize').'</em>, <em class="param">'.FirstKlik($_SESSION['p_fieldgenerator'],'fieldgenerator','fieldgenerator').'</em>, <em class="param">'.FirstKlik($_SESSION['p_codegenerator'],'codegenerator','codegenerator').'</em>)'.$reg_res.'

			<br>Vytvorí register s poètom poloiek=size, za pomoci koneèného po¾a field pre RS kód s generujúcim polynómom codeGenerator.</li>

			</ul>';			

			echo '<p class="podsub">Funkcie registra </p>

			<ul>

			<li><em class="func">'.FuncClick('Clear').'</em>&nbsp;()'.$reg_clear_res.'

			<BR>Vyèistenie registra.</li>

			<li><em class="func">'.FuncClick('isClear').'</em>&nbsp;()'.$reg_isclear_res.'

			<br>Zisti èi je register èistı. Vhodné na zistenie nulového zvyšku pri dekódovaní</li>

			<li><em class="func">'.FuncClick('PushDivision').'</em>&nbsp;(<em class="param">'.FirstKlik($alfa_pushdiv,'alfa','alfa_pushdiv').'</em>)'.$reg_pushdiv_res.'

			<br>

			Vtlaèenie alfa prvku v štıle: delenie

			<BR><img src="pic.php?pic=reg_div&amp;juj='.time().'" title="Register pre delenie" alt="Register pre delenie">

			</li>

			<li><em class="func">'.FuncClick('PushNormal').'</em>&nbsp;(<em class="param">'.FirstKlik($alfa_pushnon,'alfa','alfa_pushnon').'</em>)'.$reg_pushnon_res.'

			<br>Vtlaèenie alfa prvku v štıle: nesystematické kódovanie

			<BR><img src="pic.php?pic=reg_non&amp;juj='.time().'" title="Register pre nesystematické kódovanie" alt="Register pre nesystematické kódovanie">

			</li>

			<li><em class="func">'.FuncClick('PushSystematic').'</em>&nbsp;(<em class="param">'.FirstKlik($alfa_pushsys,'alfa','alfa_pushsys').'</em>)'.$reg_pushsys_res.'

			<br>Vtlaèenie alfa prvku v štıle: systematické kódovanie. Neobsahuje prepínaèe, preto zabezpeèovacie prvky pri kódovaní sa získajú funkciou <em>FlushRegister</em> po vloení <em>k</em> informaènıch prvkov.

			<BR><img src="pic.php?pic=reg_sys&amp;juj='.time().'" title="Register pre systematické kódovanie" alt="Register pre systematické kódovanie">

			</li>

		    	<li><em class="func">'.FuncClick('FlushRegister').'</em>&nbsp;()'.$reg_flush_res.'

		    	<br>Vytlaèenie jedného prvku z registra. Pokia¾ nieko¾ko bitov vtlaèíme predošlımi funkciami a je potrebné vytiahnu bity, ktoré ostali v registri.		    			    	

		    	</li>

			</ul>

			<p class="podsub">Parametre a interné premenné registra</p>

			<table>

			<tr><td><em class="prem">regSize:</em>&nbsp;</td><td>rozmer po¾a registra</td></tr>

			<tr><td><em class="prem">genField:</em>&nbsp;</td><td>objekt triedy GF (potrebnı na operácie s <span class="math">a</span>lfa prvkami)</td></tr>

			<tr><td><em class="prem">genPoly:</em>&nbsp;</td><td>generujúci polynóm kódu vyuívajúceho tento register</td></tr>

			<tr><td><em class="prem">registre:</em>&nbsp;</td><td>vısledná štruktúra vytvorenıch registrov</td></tr>			

			</table></form>';

			break;

		}

		

		case 'matrix': { /* matica */

			echo '<h3>Maticová kalkulaèka (trieda GFMatrix)</h3>

			<p class="podsub">Vytvorenie objektu</p>

			<ul>

			<li><em class="func">GFMatrix</em>&nbsp;(<em class="param">koneèné pole</em>, <em class="param">riadkov</em>, <em class="param">ståpcov</em>)

			<br>Vytvorenie matice.</li>

			</ul>

			<p class="podsub">Funkcie maticovej kalkulaèky</p>

			<ul>

			<li><em class="func">SwapRows</em>&nbsp;(<em class="param">riadok1</em>, <em class="param">riadok2</em>)

			<br>Vymení dva riadky matice.</li>

			<li><em class="func">Solve</em>&nbsp;()

			<br>Vyriešenie matice.</li>

			<li><em class="func">InitFromRSSyndroms</em>&nbsp;(<em class="param">syndrómy</em>, <em class="param">poèet</em>, <em class="param">poèiatoènı index</em>)

			<br>Naèíta do matice syndrómy pre RS dekódovanie.</li>

			<li><em class="func">InitForRSValues</em>&nbsp;(<em class="param">syndrómy</em>, <em class="param">lokátory</em>, <em class="param">poèiatoènı index</em>)

			<br>Naèítanie dát do matice pre vıpoèet hodnôt na pozíciách lokátorov.</li>

			<li><em class="func">Reset</em>&nbsp;()

			<br>Vynulovanie celej matice.</li>

			</ul>

			<p class="podsub">Pomocné funkcie</p>

			<ul>			

			<li><em class="func">DebugPrintState</em>&nbsp;()

			<br>Vypíše stav matice na štandardnı vıstup.</li>

			</ul>

			<p class="podsub">Parametre a interné premenné matice</p>

			<table>

			<tr><td><em class="prem">isValid:</em>&nbsp;</td><td>

			po úspešnom vytvorení objektu sa nastaví na "true"</td></tr>

			<tr><td><em class="prem">rows:</em>&nbsp;</td><td>

			poèet riadkov matice</td></tr>

			<tr><td><em class="prem">cols:</em>&nbsp;</td><td>

			poèet ståpcov matice</td></tr>

			<tr><td><em class="prem">matrix:</em>&nbsp;</td><td>

			pole hodnôt matice bez pravej strany</td></tr>

			<tr><td><em class="prem">rights:</em>&nbsp;</td><td>

			pole hodnôt pravej strany matice</td></tr>

			<tr><td><em class="prem">genField:</em>&nbsp;</td><td>

			koneèné pole (objekt triedy GF)</td></tr>

			</table>';				

			break;

		}

		

		case 'rs': { /* Reed Solomon */

			echo '<h3>Kóder Reed&amp;Solomon (trieda ReedSolomon)</h3>

			<p class="podsub">Vytvorenie objektu</p>

			<ul>

			<li><em class="func">ReedSolomon</em>&nbsp;(<em class="param">fieldGenerator</em>, <em class="param">firstIndex</em>, <em class="param">errors</em>)

			<br>Vytvorenie platného RS kódera pomocou primitícneho polynómu, ktorı vytvorí objekt triedy GF, poèiatoèného indexu (zvyèajne 0)

			 a poètu opravite¾nıch chıb. Z tıchto troch parametrov vyplıvajú všetky parametre, kódu, ktorım bude novovytvorenı kóder

			 kódova a dekódova informáciu.</li>

			</ul>

			<p class="podsub">Funkcie RS kódera</p>

			<ul>

			<li><em class="func">EncodeSystematic</em>&nbsp;( <em class="param">sourceBuf</em>, <em class="param">destBuf</em>, <em class="param">nSourceIndex</em>, <em class="param">nDestIndex</em>, <em class="param">nSourceBit</em>, <em class="param">nDestBit</em> )

			<br>Systematické zakódovanie binárnych dát - oktetov.</li>

			<li><em class="func">DecodeSystematic</em>&nbsp;(<em class="param">sourceBuf</em>, <em class="param">destBuf</em>, <em class="param">nSourceIndex</em>, <em class="param">nDestIndex</em>, <em class="param">nSourceBit</em>, <em class="param">nDestBit</em>, <em class="param">errorsRepaired</em>, <em class="param">return_n</em>)

			<br>Systematické dekódovanie binárnych dát - oktetov.</li>

			<li><em class="func">EncodeSystematicPoly</em>&nbsp;(<em class="param">sourcePoly</em>, <em class="param">encodedPoly</em>)

			<br>Systematické zakódovanie informácie zadaného vo forme polynómu.</li>

			<li><em class="func">DecodeSystematicPoly</em>&nbsp;(<em class="param">receivedPoly</em>, <em class="param">decodedPoly</em>, <em class="param">errorsRepaired</em>)

			<br>Systematické dekódovanie kódového slova zadaného vo forme polynómu.</li>

			<li><em class="func">CreateGeneratorPolynomial</em>&nbsp;(<em class="param">firstIndex</em>, <em class="param">errors</em>)

			<br>Funckia na vytvorenie generujúceho polynómu - Single Linked List (SLL).</li>

			<li><em class="func">GeneratorPolynomToString</em>&nbsp;(<em class="param">gener</em>, <em class="param">buff</em>)

			<br>Prevedie vytvorenı generujúci polynóm na reazec.</li>

			</ul>						

			<p class="podsub">Pomocné funkcie<br>

			</p>

			<ul>			

			<li><em class="func">BinaryToPolynom</em>&nbsp;(<em class="param">binary</em>, <em class="param">polynom</em>, <em class="param">maxgrade</em>)

			<br>Funkcia na prevod polynómu v SLL reprezentácii na reazec.</li>

			<li><em class="func">PolyToBinary</em>&nbsp;(<em class="param">polynom</em>, <em class="param">binary</em>, <em class="param">maxgrade</em>)

			<br>Funkcia na prevod polynómu vo forme reazca na SLL štruktúru.</li>

			<li><em class="func">InputAlfa</em>&nbsp;(<em class="param">sourceBuf</em>, <em class="param">nSourceIndex</em>, <em class="param">nSourceBit</em>)

			<br>Naèíta "m" bitov z binárneho zdrojového buffra a vráti rovno Alfa prvok, ktorı reprezentuje preèítané bity.</li>

			<li><em class="func">OutputAlfa</em>&nbsp;(<em class="param">outAlfa</em>, <em class="param">destBuf</em>, <em class="param">nDestIndex</em>, <em class="param">nDestBit</em>)

			<br>Zapíše do vıstupného binárneho buffra jeden Alfa prvok v binárnej reprezentácii.</li>

			</ul>

			<p class="podsub">Parametre a interné premenné kódera</p>

			<table>

			<tr><td><em class="prem">t:</em>&nbsp;</td><td>

			poèet chıb ktoré kód dokáe opravi</td></tr>

			<tr><td><em class="prem">j:</em>&nbsp;</td><td>

			poèiatoènı index na vytvorenie generujúceho polynómu</td></tr>

			<tr><td><em class="prem">n:</em>&nbsp;</td><td>

			celkovı poèet bitov kódovıch slov</td></tr>

			<tr><td><em class="prem">k:</em>&nbsp;</td><td>

			poèet informaènıch bitov kódovıch slov</td></tr>

			<tr><td><em class="prem">hadErrors:</em>&nbsp;</td><td>

			èi pri poslednom dekódovaní boli opravené chyby 

			</td></tr>

			<tr><td><em class="prem">isValid:</em>&nbsp;</td><td>

			po úspešnom vytvorení objektu sa nastaví na "true"</td></tr>

			<tr><td><em class="prem">genField:</em>&nbsp;</td><td>

			koneèné pole nad ktorım funguje kód (objekt triedy GF)</td></tr>

			<tr><td><em class="prem">matrix:</em>&nbsp;</td><td>

			matica na vıpoèet polynómu lokátorov pri RS dekódovaní (objekt triedy GFMatrix)</td></tr>

			<tr><td><em class="prem">virtRegister:</em>&nbsp;</td><td>

			virtuálny HW register na delenie polynómov (objekt triedy Register)</td></tr>			

		  	<tr><td><em class="prem">genPoly:</em>&nbsp;</td><td>

		  	generujúci polynóm - SLL reprezentácia</td></tr>			

			<tr><td><em class="prem">genPolyString:</em>&nbsp;</td><td>

			Obsahuje generujúci polynóm vo forme reazca</td></tr>	

			<tr><td><em class="prem">syndroms:</em>&nbsp;</td><td>

			pole pre dekódované syndrómy</td></tr>

			<tr><td><em class="prem">locators_cache:</em>&nbsp;</td><td>

			pole pre vıpoèet lokátorov Chienovım algoritmom</td></tr>

			<tr><td><em class="prem">locators:</em>&nbsp;</td><td>

			pole pre lokátory z Chienovho algoritmu</td></tr>			

			</table>';

			break;	

		}				

		

		default: { // hlana stranka komponentov

			echo '<h2>Komponenty pre kódovanie a dekódovanie</h2>';

			

			echo '

			<p class="odstavec">Kódy ktorım sú tieto stránky venované, ako je vysvetlené v èasti "Nieèo o RS kódoch", 

			sú zaloené na èistej matematike. Avšak v praxi je potrebné, aby bol kóder èi dekóder 

			jednoducho implementovate¾nı, poèet potrebnıch operácií v procese èo najmenší, èo do zloitosti 

			a ceny vısledného IC èo najlacnejší. Takisto aj softvérové modely kódera sa vo väèšine prípadov 

			snaia kopírova správanie HW obvodu.</p>

	        <p class="odstavec2">Všetko závisí od poiadaviek aké sú kladené konkrétnu implementáciu. Bene vyrábané IC 

	        riešenia RS kóderov majú fixnú dåku kódového slova napríklad na 255 (nad GF(2<sup>8</sup>)), prièom je moné 

	        voli len poèet chıb ktoré kód dokáe opravi. Dôvodom je práve potrebná rıchlos a jednoduchos 

	        obvodu, pevná štruktúra. Jedná sa o problematiku návrhu logickıch obvodov.

	Model kódera pouitého na tıchto stránkach sa odlišuje od HW implementácie. Nutnos dokáza produkova 

	kódové slová s taker ¾ubovo¾nım kódom, nad takmer ¾ubovo¾nım koneènım po¾om (všetko v zmysle princípov 

	RS kódov a obmedzeniach implementaèného jazyka), názornos, prieh¾adnos, všeobecnos.</p> 

	Vısledkom je objektovı model so 4 triedami objektov:

	<ul>

    <li><a href="index.php?section=rsparts&amp;sub=gf">GF</a>: R&S kódy sú nebinárne kódy, všetky operácie sú vykonávané nad prvkami koneèného po¾a. 

    Nad prvkami sú definované základné operácie ako plus, mínus, krát a deleno. Všetky primitívne 

    operácie nad prvkami sa realizujú cez objekt tejto triedy. Takéto odèlenenie GF operáciíje dobré len 

    ak nie je prvoradou poiadavkou rıchlos.</li>

	<li><a href="index.php?section=rsparts&amp;sub=register">Register</a>: Matematickı zápis kódovania (násobenie polynómov) a dekódovanie (delenie polynómov) je 

	v HW praxi reprezentované posuvnım HW registrom o dåke 2 krát poèet monıch chıb, prièom tieto operácie

	 potrebujú register s rôznou logikou posunu a vzájomného násobenia prvkov. Prvky sú opä z koneèného 

	 po¾a GF(q) - vyuívajúc objekt triedy GF.</li>

	<li><a href="index.php?section=rsparts&amp;sub=matrix">GFMatrix</a>: Dekódovanie RS kódov nie je tak jednoduché ako je dekódovanie a oprava chıb pri obyèajnıch binárnych 

	cyklickıch èi blokovıch kódoch. Je potrebné získa polynóm lokátorov, zisti lokátory a chybové hodnoty

	 na pozíciách lokátorov v kódovom slove. To si vyaduje minimálne jedno riešenie systému sústavy rovníc pomocou

	  matice, dve riešenia ak sa nepouije efektívnejšia metóda (Berlekemp-Messay, Peterson-Gorenstein-Zierler).

	   Trieda GFMatrix je v podstate maticová kalkulaèka na riešenie sústavy rovníc - a to priamo naèítaním 

	   hodnôt matice pre obidve operácie.</li>

	<li><a href="index.php?section=rsparts&amp;sub=rs">ReedSolomon</a>: Posledná trieda ReedSolomon kóduje a dekóduje zadané informácie, kódové slová, v binárnej alebo 

	polynomiálnej forme.</li>

    </ul>

    

	<p class="odstavec2">Kliknutím na názov triedy sa môete viacej doèíta o funkciách tıchto tried. Funkcie triedy GF, Register je

	 moné si aj priamo vyskúša. Ako prvé je potrebné najprv zada potrebné parametre pre konštruktor,

	  a potom ak je to potrebné aj pre tú ktorú funkciu a kliknú na jej názov. Pri triede Register sú aj priamo

	   dynamicky zobrazované registre v troch verziách tak ako by boli logicky hardvérovo realizované pri danıch

	    vstupnıch parametroch. Zvyšné dve triedy je moné vyskúša v èasti "testy". </p>

	 <p class="odstavec_poznamka">Pre všetky èasti prezenetácie platí, e &alpha;<sup>-1</sup> je zápis pre prvok &alpha;<sup>-&infin;</sup>, èo platí hlavne pre vstupné polia,

	     neexistuje toti klávesa pre nekoneèno. Nie je doporuèované kopírova formátované polynómy do textovıch editovacích políèok,

	      keïe interne sa kopírujú aj nevidite¾né znaèky, ktoré narušujú korektnı priebeh operácie.</p>



			

			

			';

			

		} // hlavna stranka komponentov

		

	} // switch sub





	function GetBinary ($binar, $bitcount)

	{

	    $ret = '';

		$maska = 1 << $bitcount;	

		while ($bitcount>0)

		{

			$maska >>= 1;

			if ($maska & $binar)

				$ret.= "1";

			else $ret.= "0";

			$bitcount--;

		}

		return $ret;

	}



    /* nakresli obrazok stavu daneho registra  -  delenie*/

	function CreateImageDiv($file, & $reg, $dosad=false)

	{

	    $topp = 5;

	    $height = 50;

	    $width_block = 35 ;

	    $height_block = 20;

	    $width_space = 15;

	    $border_right = 20;

	    $border_left = 15;

	    $plus_diameter = 8;

	    

	    $left_ditch = 4;

	    

	    $width = ($reg->regSize)*$width_block + ($reg->regSize -1)*$width_space + $border_left + $border_right;

	    

	    $img = imagecreatetruecolor( $width, $height);  // vytvor obrazok

	    imagecolortransparent($img, 0);

	    imagefill($img,0,0,0xFFFFFF);

	    

	    

	    for ($f=0; $f< $reg->regSize; $f++)

	    {	        	        

	        // nakresli skatulky

	        $left = $border_left + $f*($width_block+$width_space) + $left_ditch;

	        $top = $height-$height_block-1;

	        imagepolygon ( $img, array($left,$top , $left+$width_block,$top , $left+$width_block,$top+$height_block , $left,$height_block+$top  ),4, 0x000000)   ;

	        

	        // prechod

	        $prech_left = $left-$width_space;

	        $prech_top = $top + $height_block/2;

	        imageline($img, $prech_left, $prech_top, $prech_left+$width_space, $prech_top, 0x000000);

	     

   	        // ciara hore

	        $cent_x = $left - $width_space/2;

	        $cent_x2 = $left + $width_block + $width_space/2;

	        imageline($img,$cent_x , $topp,$cent_x2 ,$topp , 0x000000);

	        // sipkavlavo

	        sipka($img, $left+$width_block/2-5, $topp, 'l');

	        

	        // pluska..

	        $sll = & $reg->genPoly;	        

	        while ($sll!=NULL) {	            

	            if (($sll->exx== $f)&&($sll->alfa != $reg->genField->ALFA_INFINITY))

	            {

	                // ciarka zhora

	                $cent_x = $left - $width_space/2;

	                $cent_y = $top + $height_block/2;	                

	                imageline($img,$cent_x , $cent_y,$cent_x ,$topp , 0x000000);

	                // mala bodka nasobicka

	                $cennt_y = ($cent_y-$topp)/3+3;

	                imageellipse($img, $cent_x, $cennt_y, 4 ,4,0x000000);	                

	                // pluska ...

           	        $cent_x = $left - $width_space/2;

        	        $cent_y = $top + $height_block/2;

	                pluska( $img, $cent_x, $cent_y);	                

        	        // pismenko x0..n alebo rovno nejaka alfa        	        

	                $x_left =$left-2; 

	                $x_top = $cennt_y-7;

	                if ($dosad) {

	                    $alfaa = $sll->alfa;

	                    settype($alfaa, 'string');

	                    imagestring($img,3, $x_left, $x_top, "a$alfaa", 0x000000);

	                }

	                else

	                    imagestring($img,3, $x_left, $x_top, "x$f", 0x000000);

	                // sipocka dole

	                $sipka_y = ($cent_y-$topp)*2.5/3;

	                sipka($img, $cent_x, $sipka_y, 'd');

	                break;

	            }

	            $sll = & $sll->next;

	        }// while

	        

	        // pismenko r0 az rn

	        if ($dosad) {

	            $alfaa = $reg->registre[$f];

	            settype($alfaa, 'string');

	            imagestring($img,3, $left+5, $top+2, "a$alfaa", 0x000000);

	        }

	        else

	            imagestring($img,3, $left+5, $top+2, "r$f", 0x000000);

	  

	                 

	    }// for



        // ukoncenie vpravo        

	    $cent_x2 = $left + $width_block + $width_space/2;

	    imageline($img,$cent_x2 , $topp,$cent_x2 ,$cent_y , 0x000000);

	    imageline($img,$left+$width_block , $cent_y,$width ,$cent_y , 0x000000);

	    

        //sipka vpravo

        sipka($img, $width, $cent_y, 'r');

        // sipka nalavo co je

        sipka($img, 5, $cent_y, 'r');

        // sipka vpravonahor

        sipka($img, $cent_x2, $top-11, 'u');

	    

	    //uloz obrazok

	    imagepng($img, $file);	     

	}

	

	/* nakresli obrazok stavu daneho registra  -  normal - nesystematicke*/

	function CreateImageNon($file, & $reg, $dosad=false)

	{

	    $topp = 5;

	    $height = 50;

	    $width_block = 35 ;

	    $height_block = 20;

	    $width_space = 15;

	    $border_right = 40;

	    $border_left = 15;

	    $plus_diameter = 8;

	    

	    $left_ditch = 4;

	    

	    $width = ($reg->regSize)*$width_block + ($reg->regSize -1)*$width_space + $border_left + $border_right;

	    

	    $img = imagecreatetruecolor( $width, $height);  // vytvor obrazok

	    imagecolortransparent($img, 0);

	    imagefill($img,0,0,0xFFFFFF);

	    for ($f=0; $f<= $reg->regSize; $f++)

	    {	       

            $left = $border_left + $f*($width_block+$width_space) + $left_ditch;

	        $top = $height-$height_block-1;

	        $prech_left = $left-$width_space;

	        $prech_top = $top + $height_block/2;

	        

	        if ($f< $reg->regSize) {

	            // nakresli skatulky

	        

	            imagepolygon ( $img, array($left,$top , $left+$width_block,$top , $left+$width_block,$top+$height_block , $left,$height_block+$top  ),4, 0x000000)   ;	        	        

	            imageline($img,  $prech_left+$width_space/2, $prech_top, $prech_left+$width_space, $prech_top, 0x000000);



	            // pismenko r0 az rn

	            if ($dosad) {

    	            $alfaa = $reg->registre[$f];

	                settype($alfaa, 'string');

	                imagestring($img,3, $left+5, $top+2, "a$alfaa", 0x000000);

	            }

	            else

    	            imagestring($img,3, $left+5, $top+2, "r$f", 0x000000);            



    	        // sipkavpravo

	            sipka($img, $left+$width_block/2+5, $topp, 'r');    	            

	        }

	        	        

	        // prechod	        	        	        

            imageline($img, $prech_left , $prech_top, $prech_left+$width_space/2, $prech_top, 0x000000);

	             

   	        // ciara hore

	        $cent_x = $left - $width_space/2;

	        $cent_x2 = $left + $width_block + $width_space/2;

	        imageline($img,$cent_x , $topp,$cent_x2 ,$topp , 0x000000);

	        

	        

	        // pluska..

	        $sll = & $reg->genPoly;	        

	        while ($sll!=NULL) {	            

	            if (($sll->exx== $f)&&($sll->alfa != $reg->genField->ALFA_INFINITY))

	            {

	                // ciarka zhora

	                $cent_x = $left - $width_space/2;

	                $cent_y = $top + $height_block/2;	                

	                imageline($img,$cent_x , $cent_y,$cent_x ,$topp , 0x000000);

	                // mala bodka nasobicka

	                $cennt_y = ($cent_y-$topp)/3+3;

	                imageellipse($img, $cent_x, $cennt_y, 4 ,4,0x000000);	                

	                // pluska ...

           	        $cent_x = $left - $width_space/2;

        	        $cent_y = $topp ;

        	        if ($f!=0)

	                    pluska( $img, $cent_x, $cent_y);	                

        	        // pismenko x0..n alebo rovno nejaka alfa        	        

	                $x_left =$left-2; 

	                $x_top = $cennt_y-7;

	                if ($dosad) {

	                    $alfaa = $sll->alfa;

	                    settype($alfaa, 'string');

	                    imagestring($img,3, $x_left, $x_top, "a$alfaa", 0x000000);

	                }

	                else

	                    imagestring($img,3, $x_left, $x_top, "x$f", 0x000000);

	                // sipocka hore

	                $sipka_y = ($cennt_y+$topp)+3;

	                sipka($img, $cent_x  , $sipka_y, 'u');

	                break;

	            }

	            

	            $sll = & $sll->next;

	        }// while

	        	  

	                 

	    }// for





        // ukoncenie vpravo        

	    $cent_x2 = $left + $width_block + $width_space/2;

	    imageline($img,$cent_x2 , $topp,$cent_x2 ,$cent_y , 0x000000);

	    imageline($img,$left+$width_block , $cent_y,$width ,$cent_y , 0x000000);

	    pluska( $img, $cent_x2 ,$cent_y);	                

	    

        //sipka vpravo

        sipka($img, $width, $cent_y, 'r');

        // sipka nalavo co je

        sipka($img, 5, $top+$height_block/2, 'r');

        // sipka vpravonadol tentoraz

        sipka($img, $cent_x2, $sipka_y, 'd');

	    

	    //uloz obrazok

	    imagepng($img, $file);	     

	}

	

	/* nakresli obrazok stavu daneho registra  -  systematicke */

	function CreateImageSys($file, & $reg, $dosad=false)

	{

	    $topp = 5;

	    $height = 50;

	    $width_block = 35 ;

	    $height_block = 20;

	    $width_space = 15;

	    $border_right = 20;

	    $border_left = 15;	    

	    $border_down = 15;

	    $left_ditch = 4;

	    

	    $width = ($reg->regSize)*$width_block + ($reg->regSize -1)*$width_space + $border_left + $border_right;

	    

	    $img = imagecreatetruecolor( $width, $height+$border_down);  // vytvor obrazok

	    imagecolortransparent($img, 0);

	    imagefill($img,0,0,0xFFFFFF);

	    

	    

	    for ($f=0; $f< $reg->regSize; $f++)

	    {	        	        

	        // nakresli skatulky

	        $left = $border_left + $f*($width_block+$width_space) + $left_ditch;

	        $top = $height-$height_block-1;

	        imagepolygon ( $img, array($left,$top , $left+$width_block,$top , $left+$width_block,$top+$height_block , $left,$height_block+$top  ),4, 0x000000)   ;

	        

	        // prechod

	        $prech_left = $left-$width_space;

	        $prech_top = $top + $height_block/2;

	        if ($f!=0)

	            imageline($img, $prech_left, $prech_top, $prech_left+$width_space/2, $prech_top, 0x000000);

            imageline($img, $prech_left+$width_space/2, $prech_top, $prech_left+$width_space, $prech_top, 0x000000);	        

	     

   	        // ciara hore

	        $cent_x = $left - $width_space/2;

	        $cent_x2 = $left + $width_block + $width_space/2;

	        imageline($img,$cent_x , $topp,$cent_x2 ,$topp , 0x000000);

	        // sipkavlavo

	        sipka($img, $left+$width_block/2-5, $topp, 'l');

	        

	        

	        // pluska..

	        $sll = & $reg->genPoly;	        

	        while ($sll!=NULL) {	            

	            if (($sll->exx== $f)&&($sll->alfa != $reg->genField->ALFA_INFINITY))

	            {

	                // ciarka zhora

	                $cent_x = $left - $width_space/2;

	                $cent_y = $top + $height_block/2;	                

	                imageline($img,$cent_x , $cent_y,$cent_x ,$topp , 0x000000);

	                // mala bodka nasobicka

	                $cennt_y = ($cent_y-$topp)/3+3;

	                imageellipse($img, $cent_x, $cennt_y, 4 ,4,0x000000);	                

	                // pluska ...

           	        $cent_x = $left -  $width_space/2;

        	        $cent_y = $top + $height_block/2;

	                if ($f>0) pluska( $img, $cent_x , $cent_y);	                

	                

        	        // pismenko x0..n alebo rovno nejaka alfa        	        

	                $x_left =$left-2; 

	                $x_top = $cennt_y-7;

	                if ($dosad) {

	                    $alfaa = $sll->alfa;

	                    settype($alfaa, 'string');

	                    imagestring($img,3, $x_left, $x_top, "a$alfaa", 0x000000);

	                }

	                else

	                    imagestring($img,3, $x_left, $x_top, "x$f", 0x000000);

	                // sipocka dole

	                $sipka_y = ($cent_y-$topp)*2.5/3;

	                sipka($img, $cent_x, $sipka_y, 'd');

	                break;

	            }

	            $sll = & $sll->next;

	        }// while

	        

	        // pismenko r0 az rn

	        if ($dosad) {

	            $alfaa = $reg->registre[$f];

	            settype($alfaa, 'string');

	            imagestring($img,3, $left+5, $top+2, "a$alfaa", 0x000000);

	        }

	        else

	            imagestring($img,3, $left+5, $top+2, "r$f", 0x000000);

	  

	                 

	    }// for



        // ukoncenie vpravo        

	    $cent_x2 = $left + $width_block + $width_space/2;

	    imageline($img,$cent_x2 , $topp,$cent_x2 ,$cent_y , 0x000000);

	    imageline($img,$left+$width_block , $cent_y,$width ,$cent_y , 0x000000);

	    

        //sipka vpravo

        sipka($img, $width, $cent_y, 'r');

        // sipka nalavo co je - vlastne dole uz

        sipka($img, $width - 30, $height+7, 'r');

        imageline($img, $width - 30 , $height+7, $cent_x2, $height+7, 0x000000 );

        imageline($img, $cent_x2, $height+7, $cent_x2, $cent_y, 0x000000);

        // sipka vpravonahor

        sipka($img, $cent_x2, $top-11, 'u');                

	    pluska($img, $cent_x2, $top+$height_block/2);

	    

	    

	    //uloz obrazok

	    imagepng($img, $file);	     

	}

	

	// znamienko plus ze sa scituju prvky z pritokov

	function pluska(& $img, $centerx, $centery)

	{

	    $plus_diameter = 9;

	    imagefilledellipse( $img, $centerx , $centery , $plus_diameter, $plus_diameter, 0xcccccc);	                    

        //imagefilledellipse( $img, $centerx , $centery , $plus_diameter-2, $plus_diameter-2, 0xFFFFFF);	                 

        imageline($img, $centerx-2, $centery, $centerx+2, $centery, 0xFF0000);

        imageline($img, $centerx, $centery-2, $centerx, $centery+2, 0xFF0000);	    

	}

	

	

	function sipka($img, $centerx, $centery, $direct)

	{

	    switch ($direct) {

	        case 'l': {

	            imageline($img, $centerx+5, $centery+5 , $centerx, $centery, 0x000000);

	            imageline($img, $centerx, $centery, $centerx+5, $centery-5, 0x000000);

	            break;	            

	        }    

	        case 'r': {

	            imageline($img, $centerx-5, $centery-5 , $centerx, $centery, 0x000000);

	            imageline($img, $centerx, $centery, $centerx-5, $centery+5, 0x000000);

	            break;

	        }

	        case 'u': {

	            imageline($img, $centerx-5, $centery+5 , $centerx, $centery, 0x000000);

	            imageline($img, $centerx, $centery, $centerx+5, $centery+5, 0x000000);	            
	            break;

	        }

	        case 'd': {

	            imageline($img, $centerx-5, $centery-5 , $centerx, $centery, 0x000000);

	            imageline($img, $centerx, $centery, $centerx+5, $centery-5, 0x000000);	            	            

	            break;

	        }	        	        

	        

	    }

	        

	}

	

?>

</div>