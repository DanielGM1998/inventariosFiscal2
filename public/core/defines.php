<?php
	use Jose\Component\KeyManagement\JWKFactory;

	ini_set('display_errors',1);

	if(!defined('SITE_NAME')) define('SITE_NAME', 'Fiscal');
	if(!defined('URL_ROOT')) define('URL_ROOT',  'http://localhost/inventariosFiscal2/public');
	if(!defined('SITE_ROOT')) define('SITE_ROOT',  'http://localhost/inventariosFiscal2/public');
	if(!defined('URL_API')) define('URL_API',  'http://localhost/inventariosFiscal2/public/');
	if(!defined('URL_IMG_DEFAULT'))	define('URL_IMG_DEFAULT',  'http://localhost/inventariosFiscal2/public/assets/image/no_imagen.jpg');
	if(!defined('URL_DATA')) define('URL_DATA',  'http://localhost/inventariosFiscal2/data/');
	if(!defined('HOMEPAGE')) define('HOMEPAGE',  'usuarios');

	if (!isset($_SESSION)) session_start();
	date_default_timezone_set('America/Mexico_City');
	$_SESSION['mail_username'] = "notifica@ddsmedia.net";
	$_SESSION['mail_pwd'] = "3q#'sV$}KNRw$(kh";
	$_SESSION['admins'] = [1];
	if (!isset($_SESSION['clave'])) { 
		$_SESSION['clave'] = json_encode(JWKFactory::createOctKey(
			1024, // Size in bits of the key. We recommend at least 128 bits.
			[
				'alg' => 'A256KW', // This key must only be used with the A256KW algorithm
				'use' => 'enc' // This key is used for encryption/decryption operations
			]
		));
	}
	$_SESSION['cliente_general'] = 1;
	$_SESSION['metodo_pago_saldo_favor'] = 7;
	$_SESSION['metodo_pago_otros'] = 8;
	$_SESSION['sucursal'] = 1;
	$_SESSION['otro_gasto'] = 1;
	$_SESSION['otro_ingreso'] = 1;
?>