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

	/* (C) 2003 Miroslav �ur��k */



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

			echo '<h3>Kone�n� pole (trieda GF)</h3>

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

                                $gf_addg_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� mocnina &alpha; prvku</span>';

                            elseif (($second_addg < -1)||($second_addg > ($gf->q -2)))

                                $gf_addg_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� mocnina &alpha; prvku</span>';

                            else                                                        

                                $gf_addg_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($gf->AddByGrade($first_addg, $second_addg)).'</sup></span>';

                            break;

                        }

                        case 'MultiplyByGrade': {

                            settype($first_mult,'integer');settype($second_mult,'integer');

                            if (($first_mult < -1)||($first_mult > ($gf->q -2)))

                                $gf_mult_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� mocnina &alpha; prvku</span>';

                            elseif (($second_mult < -1)||($second_mult > ($gf->q -2)))

                                $gf_mult_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� mocnina &alpha; prvku</span>';                                                        

                            else

                                $gf_mult_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($gf->MultiplyByGrade($first_mult, $second_mult)).'</sup></span>';

                            break;

                        }

                        case 'DivideByGrade': {

                            settype($up_div,'integer');settype($down_div,'integer');

                            if (($up_div < -1)||($up_div > ($gf->q -2)))

                                $gf_div_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� mocnina &alpha; prvku</span>';

                            elseif (($down_div < -1)||($down_div > ($gf->q -2)))

                                $gf_div_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� mocnina &alpha; prvku</span>';

                            else

                                $gf_div_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($gf->DivideByGrade($up_div, $down_div)).'</sup></span>';

                            break;

                        }

                        case 'SolvePolynom': {

                            settype($unknown_solve,'integer');	settype($unknown_poly,'string');

                            if (($unknown_solve < -1)||($unknown_solve > ($gf->q -2)))

                                $gf_solve_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� mocnina &alpha; prvku</span>';

                            else {

                                $poly_solveSLL = & $gf->PolynomToSll($poly_solve);

                                if ($poly_solveSLL==false)

                                    $gf_solve_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� polyn�m</span>';

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

			<li>'.FuncClick('GF').'&nbsp;(<em class="param">'.FirstKlik($fieldgenerator,'primit�vny polyn�m', 'fieldgenerator').'</em>)'.$gf_res.'

				<BR>Objekt je vytvoren� zadan�m primit�vneho polyn�mu, ktor� dok�e vytvori�

				GF(q) s po�adovan�m po�tom a rozmiestnen�m <span class="math">a</span> prvkov.</li>

			</ul>';						

			echo '<p class="podsub">Oper�cie nad kone�n�m po�om</p>

			<ul>									

			<li>'.FuncClick('Add').'&nbsp; (<em class="param">'.FirstKlik($first_add,'first','first_add').'</em>, <em class="param">'.FirstKlik($second_add,'second','second_add').'</em>)'.$gf_add_res.'

				<BR>Spo��ta napr�klad 6 + 1 = 7 (bit po bite) .</li>

			<li>'.FuncClick('AddByGrade').'&nbsp;(<em class="param">'.FirstKlik($first_addg,'first','first_addg').'</em>, <em class="param">'.FirstKlik($second_addg,'second','second_addg').'</em>)'.$gf_addg_res.'

				<BR>S��et pod�a mocniny, mus� taktie� n�js� v�sledn� <span class="math">a</span>.</li>

			<li>'.FuncClick('MultiplyByGrade').'&nbsp; (<em class="param">'.FirstKlik($first_mult,'first','first_mult').'</em>, <em class="param">'.FirstKlik($second_mult,'second','second_mult').'</em>)'.$gf_mult_res.'

				<BR>N�sobenie, �i�e s��tanie mocn�n, �prava pri prete�en�.</li>

			<li>'.FuncClick('DivideByGrade').'&nbsp; (<em class="param">'.FirstKlik($up_div,'up','up_div').'</em>, <em class="param">'.FirstKlik($down_div,'down','down_div').'</em>)'.$gf_div_res.'

				<BR>Delenie, �i�e od��tanie mocn�n, �prava pri prete�en�.</li>

			<li>'.FuncClick('SolvePolynom').'&nbsp;(<em class="param">'.FirstKlik($unknown_solve,'nezn�ma','unknown_solve').'</em>, <em class="param">'.FirstKlik($poly_solve,'polyn�m','poly_solve').'</em>)'.$gf_solve_res.'

				<BR>Dosadenie za X do polyn�mu nejak� konkr�tne <span class="math">a</span>.</li>

			<li>'.FuncClick('TestDistinct').'&nbsp;()'.$gf_test_res.'

				<BR>Test, �i sa niektor� <span class="math">a</span> prvky bin�rne nezhoduj�. Podmienkou pou�ite�nosti kone�n�ho po�a

				vytvoren�ho po�adovan�m primit�vnym polyn�mom pre R&amp;S k�dy je, aby sa �iaden prvok neopakoval.</li>

			</ul>

			<p class="podsub">Pomocn� funkcie</p>

			<ul>

			<li><em class="func">PolynomToSll</em>&nbsp;(<em class="param">polyn�m</em>)

				<BR>Analyzuje polyn�m(re�azec) a prevedie ho do svojej internej formy v podobe SLL �trukt�ry</li>

			<li><em class="func">SLLToPolynom</em>&nbsp;(<em class="param">SLL objekt</em>, <em class="param">cielovypolynom</em>)

				<BR>Sp�tn� vytvorenie polyn�mu-re�azca z internej �trukt�ry SLL.</li>

			<li><em class="func">PrintDebug()</em>&nbsp;

				<BR>Vyp�e z�kladn� inform�cie o vytvorenom kone�nom poli.</li>

			<li><em class="func">PrintBinaryDebug </em>&nbsp;(<em class="param">binar</em>, <em class="param">bitcount</em>)

				<BR>Vyp�e ��slo binar v podobe n�l a jedni�ie - prv�ch bitcount bitov.</li>

			</ul>

			<p class="podsub">Parametre a intern� premenn� kone�n�ho po�a</p>			

			<table>

			<tr><td><em class="prem">array:</em>&nbsp;</td><td>pole integer premenn�ch bin�rnej reprezent�cie <span class="math">a</span>lfa prvkov</td></tr>

			<tr><td><em class="prem">array_inverse:</em>&nbsp;</td><td>inverzn� pole, teda index je bin�rna reprezent�cia a obsah je <span class="math">a</span>lfa</td></tr>

			<tr><td><em class="prem">generator:'.( isset($_POST['submitt']) ? '<span class="msg_ok">&nbsp;=&nbsp;'.$gf->generator.'&nbsp;(0x'.GetBinary($gf->generator, $gf->m+1 ).')</span>'  : '' ).'</em>&nbsp;</td><td>primit�vny polyn�m ktor� vygeneroval toto GF(q) v bin�rnom z�pise</td></tr>

			<tr><td><em class="prem">m:'.( isset($_POST['submitt']) ? '<span class="msg_ok">&nbsp;=&nbsp;'.$gf->m."</span>"  : '' ).'</em>&nbsp;</td><td>mocnina v GF(2<sup>m</sup>)</td></tr>

			<tr><td><em class="prem">q:'.( isset($_POST['submitt']) ? '<span class="msg_ok">&nbsp;=&nbsp;'.$gf->q."</span>"  : '' ).'</em>&nbsp;</td><td>q = 2<sup>m</sup></td></tr>

			<tr><td><em class="prem">isValid:'.( isset($_POST['submitt']) ? '<span class="msg_ok">&nbsp;=&nbsp;'.$gf->isValid."</span>"  : '' ).'</em>&nbsp;</td><td>po �spe�nom vytvoren� GF(q) sa v kon�truktore nastav� na true.</td></tr>

			</table>

			<inputt type="hidden" name="submitt" value="nic">

			</form>

			';

		    

			break;	

		}

		

		case 'register': { /* Register */

			echo '<h3>Posuvn� register (trieda Register)</h3><form name="formm" method="POST" action="index.php?section=rsparts&amp;sub=register">';

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

			                    $reg_isclear_res = '&nbsp;=&nbsp;<span class="msg_ok">'.($reg->isClear() ? '�no' : 'Nie').'</span>';    

			                    break;

			                }

			                case 'PushDivision': {

			                    settype($alfa_pushdiv, 'integer');

                                if (($alfa_pushdiv < -1)||($alfa_pushdiv > ($gf->q -2)))

                                    $reg_pushdiv_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� mocnina &alpha; prvku</span>';

                                else                                                        

                                    $reg_pushdiv_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($reg->PushDivision($alfa_pushdiv)).'</sup></span>';    

			                    break;

			                }

			                case 'PushNormal': {

			                    settype($alfa_pushnon, 'integer');

                                if (($alfa_pushnon < -1)||($alfa_pushnon > ($gf->q -2)))

                                    $reg_pushnon_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� mocnina &alpha; prvku</span>';

                                else                                                        

                                    $reg_pushnon_res = '&nbsp;=&nbsp;<span class="msg_ok">&alpha;<sup>'.($reg->PushNormal($alfa_pushnon)).'</sup></span>';                                

			                    break;

			                }

			                case 'PushSystematic': {

			                    settype($alfa_pushsys, 'integer');

                                if (($alfa_pushsys < -1)||($alfa_pushsys > ($gf->q -2)))

                                    $reg_pushsys_res = '&nbsp;=&nbsp;<span class="msg_bad">Zl� mocnina &alpha; prvku</span>';

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

			            $reg_res = '&nbsp;=&nbsp;<span class="msg_bad">Chyba - pri vytv�ran� registra.</span>';

			    }

			    else

			        $reg_res = '&nbsp;=&nbsp;<span class="msg_bad">Chyba - zl� primit�vny polyn�m na vytvorenie GF.</span>';

			        

			        

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

			<br>Vytvor� register s po�tom polo�iek=size, za pomoci kone�n�ho po�a field pre RS k�d s generuj�cim polyn�mom codeGenerator.</li>

			</ul>';			

			echo '<p class="podsub">Funkcie registra </p>

			<ul>

			<li><em class="func">'.FuncClick('Clear').'</em>&nbsp;()'.$reg_clear_res.'

			<BR>Vy�istenie registra.</li>

			<li><em class="func">'.FuncClick('isClear').'</em>&nbsp;()'.$reg_isclear_res.'

			<br>Zisti �i je register �ist�. Vhodn� na zistenie nulov�ho zvy�ku pri dek�dovan�</li>

			<li><em class="func">'.FuncClick('PushDivision').'</em>&nbsp;(<em class="param">'.FirstKlik($alfa_pushdiv,'alfa','alfa_pushdiv').'</em>)'.$reg_pushdiv_res.'

			<br>

			Vtla�enie alfa prvku v �t�le: delenie

			<BR><img src="pic.php?pic=reg_div&amp;juj='.time().'" title="Register pre delenie" alt="Register pre delenie">

			</li>

			<li><em class="func">'.FuncClick('PushNormal').'</em>&nbsp;(<em class="param">'.FirstKlik($alfa_pushnon,'alfa','alfa_pushnon').'</em>)'.$reg_pushnon_res.'

			<br>Vtla�enie alfa prvku v �t�le: nesystematick� k�dovanie

			<BR><img src="pic.php?pic=reg_non&amp;juj='.time().'" title="Register pre nesystematick� k�dovanie" alt="Register pre nesystematick� k�dovanie">

			</li>

			<li><em class="func">'.FuncClick('PushSystematic').'</em>&nbsp;(<em class="param">'.FirstKlik($alfa_pushsys,'alfa','alfa_pushsys').'</em>)'.$reg_pushsys_res.'

			<br>Vtla�enie alfa prvku v �t�le: systematick� k�dovanie. Neobsahuje prep�na�e, preto zabezpe�ovacie prvky pri k�dovan� sa z�skaj� funkciou <em>FlushRegister</em> po vlo�en� <em>k</em> informa�n�ch prvkov.

			<BR><img src="pic.php?pic=reg_sys&amp;juj='.time().'" title="Register pre systematick� k�dovanie" alt="Register pre systematick� k�dovanie">

			</li>

		    	<li><em class="func">'.FuncClick('FlushRegister').'</em>&nbsp;()'.$reg_flush_res.'

		    	<br>Vytla�enie jedn�ho prvku z registra. Pokia� nieko�ko bitov vtla��me predo�l�mi funkciami a je potrebn� vytiahnu� bity, ktor� ostali v registri.		    			    	

		    	</li>

			</ul>

			<p class="podsub">Parametre a intern� premenn� registra</p>

			<table>

			<tr><td><em class="prem">regSize:</em>&nbsp;</td><td>rozmer po�a registra</td></tr>

			<tr><td><em class="prem">genField:</em>&nbsp;</td><td>objekt triedy GF (potrebn� na oper�cie s <span class="math">a</span>lfa prvkami)</td></tr>

			<tr><td><em class="prem">genPoly:</em>&nbsp;</td><td>generuj�ci polyn�m k�du vyu��vaj�ceho tento register</td></tr>

			<tr><td><em class="prem">registre:</em>&nbsp;</td><td>v�sledn� �trukt�ra vytvoren�ch registrov</td></tr>			

			</table></form>';

			break;

		}

		

		case 'matrix': { /* matica */

			echo '<h3>Maticov� kalkula�ka (trieda GFMatrix)</h3>

			<p class="podsub">Vytvorenie objektu</p>

			<ul>

			<li><em class="func">GFMatrix</em>&nbsp;(<em class="param">kone�n� pole</em>, <em class="param">riadkov</em>, <em class="param">st�pcov</em>)

			<br>Vytvorenie matice.</li>

			</ul>

			<p class="podsub">Funkcie maticovej kalkula�ky</p>

			<ul>

			<li><em class="func">SwapRows</em>&nbsp;(<em class="param">riadok1</em>, <em class="param">riadok2</em>)

			<br>Vymen� dva riadky matice.</li>

			<li><em class="func">Solve</em>&nbsp;()

			<br>Vyrie�enie matice.</li>

			<li><em class="func">InitFromRSSyndroms</em>&nbsp;(<em class="param">syndr�my</em>, <em class="param">po�et</em>, <em class="param">po�iato�n� index</em>)

			<br>Na��ta do matice syndr�my pre RS dek�dovanie.</li>

			<li><em class="func">InitForRSValues</em>&nbsp;(<em class="param">syndr�my</em>, <em class="param">lok�tory</em>, <em class="param">po�iato�n� index</em>)

			<br>Na��tanie d�t do matice pre v�po�et hodn�t na poz�ci�ch lok�torov.</li>

			<li><em class="func">Reset</em>&nbsp;()

			<br>Vynulovanie celej matice.</li>

			</ul>

			<p class="podsub">Pomocn� funkcie</p>

			<ul>			

			<li><em class="func">DebugPrintState</em>&nbsp;()

			<br>Vyp�e stav matice na �tandardn� v�stup.</li>

			</ul>

			<p class="podsub">Parametre a intern� premenn� matice</p>

			<table>

			<tr><td><em class="prem">isValid:</em>&nbsp;</td><td>

			po �spe�nom vytvoren� objektu sa nastav� na "true"</td></tr>

			<tr><td><em class="prem">rows:</em>&nbsp;</td><td>

			po�et riadkov matice</td></tr>

			<tr><td><em class="prem">cols:</em>&nbsp;</td><td>

			po�et st�pcov matice</td></tr>

			<tr><td><em class="prem">matrix:</em>&nbsp;</td><td>

			pole hodn�t matice bez pravej strany</td></tr>

			<tr><td><em class="prem">rights:</em>&nbsp;</td><td>

			pole hodn�t pravej strany matice</td></tr>

			<tr><td><em class="prem">genField:</em>&nbsp;</td><td>

			kone�n� pole (objekt triedy GF)</td></tr>

			</table>';				

			break;

		}

		

		case 'rs': { /* Reed Solomon */

			echo '<h3>K�der Reed&amp;Solomon (trieda ReedSolomon)</h3>

			<p class="podsub">Vytvorenie objektu</p>

			<ul>

			<li><em class="func">ReedSolomon</em>&nbsp;(<em class="param">fieldGenerator</em>, <em class="param">firstIndex</em>, <em class="param">errors</em>)

			<br>Vytvorenie platn�ho RS k�dera pomocou primit�cneho polyn�mu, ktor� vytvor� objekt triedy GF, po�iato�n�ho indexu (zvy�ajne 0)

			 a po�tu opravite�n�ch ch�b. Z t�chto troch parametrov vypl�vaj� v�etky parametre, k�du, ktor�m bude novovytvoren� k�der

			 k�dova� a dek�dova� inform�ciu.</li>

			</ul>

			<p class="podsub">Funkcie RS k�dera</p>

			<ul>

			<li><em class="func">EncodeSystematic</em>&nbsp;( <em class="param">sourceBuf</em>, <em class="param">destBuf</em>, <em class="param">nSourceIndex</em>, <em class="param">nDestIndex</em>, <em class="param">nSourceBit</em>, <em class="param">nDestBit</em> )

			<br>Systematick� zak�dovanie bin�rnych d�t - oktetov.</li>

			<li><em class="func">DecodeSystematic</em>&nbsp;(<em class="param">sourceBuf</em>, <em class="param">destBuf</em>, <em class="param">nSourceIndex</em>, <em class="param">nDestIndex</em>, <em class="param">nSourceBit</em>, <em class="param">nDestBit</em>, <em class="param">errorsRepaired</em>, <em class="param">return_n</em>)

			<br>Systematick� dek�dovanie bin�rnych d�t - oktetov.</li>

			<li><em class="func">EncodeSystematicPoly</em>&nbsp;(<em class="param">sourcePoly</em>, <em class="param">encodedPoly</em>)

			<br>Systematick� zak�dovanie inform�cie zadan�ho vo forme polyn�mu.</li>

			<li><em class="func">DecodeSystematicPoly</em>&nbsp;(<em class="param">receivedPoly</em>, <em class="param">decodedPoly</em>, <em class="param">errorsRepaired</em>)

			<br>Systematick� dek�dovanie k�dov�ho slova zadan�ho vo forme polyn�mu.</li>

			<li><em class="func">CreateGeneratorPolynomial</em>&nbsp;(<em class="param">firstIndex</em>, <em class="param">errors</em>)

			<br>Funckia na vytvorenie generuj�ceho polyn�mu - Single Linked List (SLL).</li>

			<li><em class="func">GeneratorPolynomToString</em>&nbsp;(<em class="param">gener</em>, <em class="param">buff</em>)

			<br>Prevedie vytvoren� generuj�ci polyn�m na re�azec.</li>

			</ul>						

			<p class="podsub">Pomocn� funkcie<br>

			</p>

			<ul>			

			<li><em class="func">BinaryToPolynom</em>&nbsp;(<em class="param">binary</em>, <em class="param">polynom</em>, <em class="param">maxgrade</em>)

			<br>Funkcia na prevod polyn�mu v SLL reprezent�cii na re�azec.</li>

			<li><em class="func">PolyToBinary</em>&nbsp;(<em class="param">polynom</em>, <em class="param">binary</em>, <em class="param">maxgrade</em>)

			<br>Funkcia na prevod polyn�mu vo forme re�azca na SLL �trukt�ru.</li>

			<li><em class="func">InputAlfa</em>&nbsp;(<em class="param">sourceBuf</em>, <em class="param">nSourceIndex</em>, <em class="param">nSourceBit</em>)

			<br>Na��ta "m" bitov z bin�rneho zdrojov�ho buffra a vr�ti rovno Alfa prvok, ktor� reprezentuje pre��tan� bity.</li>

			<li><em class="func">OutputAlfa</em>&nbsp;(<em class="param">outAlfa</em>, <em class="param">destBuf</em>, <em class="param">nDestIndex</em>, <em class="param">nDestBit</em>)

			<br>Zap�e do v�stupn�ho bin�rneho buffra jeden Alfa prvok v bin�rnej reprezent�cii.</li>

			</ul>

			<p class="podsub">Parametre a intern� premenn� k�dera</p>

			<table>

			<tr><td><em class="prem">t:</em>&nbsp;</td><td>

			po�et ch�b ktor� k�d dok�e opravi�</td></tr>

			<tr><td><em class="prem">j:</em>&nbsp;</td><td>

			po�iato�n� index na vytvorenie generuj�ceho polyn�mu</td></tr>

			<tr><td><em class="prem">n:</em>&nbsp;</td><td>

			celkov� po�et bitov k�dov�ch slov</td></tr>

			<tr><td><em class="prem">k:</em>&nbsp;</td><td>

			po�et informa�n�ch bitov k�dov�ch slov</td></tr>

			<tr><td><em class="prem">hadErrors:</em>&nbsp;</td><td>

			�i pri poslednom dek�dovan� boli opraven� chyby 

			</td></tr>

			<tr><td><em class="prem">isValid:</em>&nbsp;</td><td>

			po �spe�nom vytvoren� objektu sa nastav� na "true"</td></tr>

			<tr><td><em class="prem">genField:</em>&nbsp;</td><td>

			kone�n� pole nad ktor�m funguje k�d (objekt triedy GF)</td></tr>

			<tr><td><em class="prem">matrix:</em>&nbsp;</td><td>

			matica na v�po�et polyn�mu lok�torov pri RS dek�dovan� (objekt triedy GFMatrix)</td></tr>

			<tr><td><em class="prem">virtRegister:</em>&nbsp;</td><td>

			virtu�lny HW register na delenie polyn�mov (objekt triedy Register)</td></tr>			

		  	<tr><td><em class="prem">genPoly:</em>&nbsp;</td><td>

		  	generuj�ci polyn�m - SLL reprezent�cia</td></tr>			

			<tr><td><em class="prem">genPolyString:</em>&nbsp;</td><td>

			Obsahuje generuj�ci polyn�m vo forme re�azca</td></tr>	

			<tr><td><em class="prem">syndroms:</em>&nbsp;</td><td>

			pole pre dek�dovan� syndr�my</td></tr>

			<tr><td><em class="prem">locators_cache:</em>&nbsp;</td><td>

			pole pre v�po�et lok�torov Chienov�m algoritmom</td></tr>

			<tr><td><em class="prem">locators:</em>&nbsp;</td><td>

			pole pre lok�tory z Chienovho algoritmu</td></tr>			

			</table>';

			break;	

		}				

		

		default: { // hlana stranka komponentov

			echo '<h2>Komponenty pre k�dovanie a dek�dovanie</h2>';

			

			echo '

			<p class="odstavec">K�dy ktor�m s� tieto str�nky venovan�, ako je vysvetlen� v �asti "Nie�o o RS k�doch", 

			s� zalo�en� na �istej matematike. Av�ak v praxi je potrebn�, aby bol k�der �i dek�der 

			jednoducho implementovate�n�, po�et potrebn�ch oper�ci� v procese �o najmen��, �o do zlo�itosti 

			a ceny v�sledn�ho IC �o najlacnej��. Takisto aj softv�rov� modely k�dera sa vo v��ine pr�padov 

			sna�ia kop�rova� spr�vanie HW obvodu.</p>

	        <p class="odstavec2">V�etko z�vis� od po�iadaviek ak� s� kladen� konkr�tnu implement�ciu. Be�ne vyr�ban� IC 

	        rie�enia RS k�derov maj� fixn� d�ku k�dov�ho slova napr�klad na 255 (nad GF(2<sup>8</sup>)), pri�om je mo�n� 

	        voli� len po�et ch�b ktor� k�d dok�e opravi�. D�vodom je pr�ve potrebn� r�chlos� a jednoduchos� 

	        obvodu, pevn� �trukt�ra. Jedn� sa o problematiku n�vrhu logick�ch obvodov.

	Model k�dera pou�it�ho na t�chto str�nkach sa odli�uje od HW implement�cie. Nutnos� dok�za� produkova� 

	k�dov� slov� s taker �ubovo�n�m k�dom, nad takmer �ubovo�n�m kone�n�m po�om (v�etko v zmysle princ�pov 

	RS k�dov a obmedzeniach implementa�n�ho jazyka), n�zornos�, prieh�adnos�, v�eobecnos�.</p> 

	V�sledkom je objektov� model so 4 triedami objektov:

	<ul>

    <li><a href="index.php?section=rsparts&amp;sub=gf">GF</a>: R&S k�dy s� nebin�rne k�dy, v�etky oper�cie s� vykon�van� nad prvkami kone�n�ho po�a. 

    Nad prvkami s� definovan� z�kladn� oper�cie ako plus, m�nus, kr�t a deleno. V�etky primit�vne 

    oper�cie nad prvkami sa realizuj� cez objekt tejto triedy. Tak�to od�lenenie GF oper�ci�je dobr� len 

    ak nie je prvoradou po�iadavkou r�chlos�.</li>

	<li><a href="index.php?section=rsparts&amp;sub=register">Register</a>: Matematick� z�pis k�dovania (n�sobenie polyn�mov) a dek�dovanie (delenie polyn�mov) je 

	v HW praxi reprezentovan� posuvn�m HW registrom o d�ke 2 kr�t po�et mo�n�ch ch�b, pri�om tieto oper�cie

	 potrebuj� register s r�znou logikou posunu a vz�jomn�ho n�sobenia prvkov. Prvky s� op� z kone�n�ho 

	 po�a GF(q) - vyu��vaj�c objekt triedy GF.</li>

	<li><a href="index.php?section=rsparts&amp;sub=matrix">GFMatrix</a>: Dek�dovanie RS k�dov nie je tak jednoduch� ako je dek�dovanie a oprava ch�b pri oby�ajn�ch bin�rnych 

	cyklick�ch �i blokov�ch k�doch. Je potrebn� z�ska� polyn�m lok�torov, zisti� lok�tory a chybov� hodnoty

	 na poz�ci�ch lok�torov v k�dovom slove. To si vy�aduje minim�lne jedno rie�enie syst�mu s�stavy rovn�c pomocou

	  matice, dve rie�enia ak sa nepou�ije efekt�vnej�ia met�da (Berlekemp-Messay, Peterson-Gorenstein-Zierler).

	   Trieda GFMatrix je v podstate maticov� kalkula�ka na rie�enie s�stavy rovn�c - a to priamo na��tan�m 

	   hodn�t matice pre obidve oper�cie.</li>

	<li><a href="index.php?section=rsparts&amp;sub=rs">ReedSolomon</a>: Posledn� trieda ReedSolomon k�duje a dek�duje zadan� inform�cie, k�dov� slov�, v bin�rnej alebo 

	polynomi�lnej forme.</li>

    </ul>

    

	<p class="odstavec2">Kliknut�m na n�zov triedy sa m��ete viacej do��ta� o funkci�ch t�chto tried. Funkcie triedy GF, Register je

	 mo�n� si aj priamo vysk��a�. Ako prv� je potrebn� najprv zada� potrebn� parametre pre kon�truktor,

	  a potom ak je to potrebn� aj pre t� ktor� funkciu a klikn�� na jej n�zov. Pri triede Register s� aj priamo

	   dynamicky zobrazovan� registre v troch verzi�ch tak ako by boli logicky hardv�rovo realizovan� pri dan�ch

	    vstupn�ch parametroch. Zvy�n� dve triedy je mo�n� vysk��a� v �asti "testy". </p>

	 <p class="odstavec_poznamka">Pre v�etky �asti prezenet�cie plat�, �e &alpha;<sup>-1</sup> je z�pis pre prvok &alpha;<sup>-&infin;</sup>, �o plat� hlavne pre vstupn� polia,

	     neexistuje toti� kl�vesa pre nekone�no. Nie je doporu�ovan� kop�rova� form�tovan� polyn�my do textov�ch editovac�ch pol��ok,

	      ke�e interne sa kop�ruj� aj nevidite�n� zna�ky, ktor� naru�uj� korektn� priebeh oper�cie.</p>



			

			

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