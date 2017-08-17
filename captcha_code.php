<?php
	session_start();

	$ranStr = md5(microtime());
	
	$ranStr = hexdec( crc32($ranStr));

	$ranStr = chuoingaunhien(6,4);

	$_SESSION['cap_code'] = $ranStr;
	//echo $_SESSION['cap_code'];die;
	$newImage = imagecreatefromjpeg("images/bg3.jpg");

	$txtColor = imagecolorallocate($newImage, 255, 255, 255);

	imagestring($newImage, 5, 3, 8, $ranStr, $txtColor);

	header("Content-type: image/jpeg");

	imagejpeg($newImage);

	function chuoingaunhien($sokytu,$kieu=3){ 
	    if($kieu==1){$chuoi="ABCDEFGHIJKLMNOPQRSTUVWXYZWabcdefghijklmnopqrstuvwxyzw";}
	    elseif($kieu==2){$chuoi="0123456789";}
	    elseif($kieu==3){$chuoi="ABCDEFGHIJKLMNOPQRSTUVWXYZWabcdefghijklmnopqrstuvwxyzw0123456789";}
	    elseif($kieu==4){$chuoi="ABCDEFGHIJKLMNOPQRSTUVWXYZW0123456789";}
	    for ($i=0; $i < $sokytu; $i++){
	        $vitri = mt_rand(0,strlen($chuoi));
	        $giatri= $giatri.substr($chuoi,$vitri,1 );
	    }
	    return $giatri;
	}