<?php
	$picc = $_GET['pic'];	
	session_start();		
	switch ($picc)
	{
		case 'orig':
		{
			$filee = $_SESSION['local_orig_file'];
            $data = $_SESSION['s_img_orig'];
			break;
		}
		case 'encoded':
		{
			$filee = $_SESSION['local_encoded_file'];
            $data = $_SESSION['s_img_encoded'];
			break;
		}
		case 'errored':
		{
			$filee = $_SESSION['local_errored_file'];
            $data = $_SESSION['s_img_errored'];
			break;
		}
		case 'decodedv':
		{
			$filee = $_SESSION['local_decodedv_file'];
            $data = $_SESSION['s_img_decodedv'];
			break;
		}
		case 'decodedh':
		{
			$filee = $_SESSION['local_decodedh_file'];
            $data = $_SESSION['s_img_decodedh'];
			break;
		}
		
		case 'differe':
		{	
			$filee = $_SESSION['local_differe_file'];
            $data = $_SESSION['s_img_differe'];
			break;
		}	
		case 'differv':
		{	
			$filee = $_SESSION['local_differv_file'];
            $data = $_SESSION['s_img_differv'];
			break;
		}		
		case 'differh':
		{	
			$filee = $_SESSION['local_differh_file'];
            $data = $_SESSION['s_img_differh'];
			break;
		}		
		case 'reg_non':
		{
		    $filee = $_SESSION['local_reg_non'];
            $data = $_SESSION['s_img_reg_non'];
		    break;
		}
		case 'reg_sys':
		{
		    $filee = $_SESSION['local_reg_sys'];
            $data = $_SESSION['s_img_reg_sys'];
		    break;
		}
		case 'reg_div':
		{
		    $filee = $_SESSION['local_reg_div'];
            $data = $_SESSION['s_img_reg_div'];
		    break;
		}
		
		default:
			$filee="DD";
	}
	
	//echo "dd:" .$file;
	if ($filee!="DD") 
	{		
        	
//DD		$fp = fopen($filee, 'rb');
//DD		if ($fp==false)
//DD		{
//DD			// pockaj chvilku a skus znovu
//DD			sleep(2);
//DD			$fp = fopen($filee, 'rb');
//DD		}
	/*DD	if ($fp==false)
		{
			$obrr = imagecreatetruecolor(200,200);
			imagestring($obrr, 5, 0, 0, "Error !!", 0xFF0000);
			if ($filee=="")
				imagestring($obrr, 1,0, 40, "Nepodarilo sa ziskat nazov zo session!", 0xFF0000);
			else
				imagestring($obrr, 1,0, 40, "cannot open file: ".$filee, 0xFF0000);
			imagestring($obrr, 5,0, 100, "ses:".$PHPSESSID, 0xFF0000);
			imagepng($obrr);
			die;
		}
		else
		{	
			$dlz = filesize($filee);		
			$prec = fread($fp, $dlz);
			fclose($fp);
DD */						
			/* natahovanie obrazkov - zakazanie cache */
			
			
			header('Content-type: image/png');
			//header('Pragma: no-cache');
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
			//header('<meta http-equiv="Cache-Control" content="no-cache">');
			//header('<meta http-equiv="Pragma" content="no_cache">');		
//DD			echo $prec;

echo( unserialize( $data));
            
            
            //$inacesta = basename($filee);
            //$rr = 'Location: http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/rs_temp/'.$inacesta;
            //echo $rr;            
            //header($rr);            
            die;
            			
		}
//DD	}	
?>	