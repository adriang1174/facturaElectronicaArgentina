<?php

require 'exceptionhandler.php';
require 'wsaa.class.php';
require 'wsfe.class.php';

/**********************
 * Ejemplo WSAA
 * ********************/

$wsaa = new WSAA('./'); 


if($wsaa->get_expiration() < date("Y-m-d h:m:i")) {
  if ($wsaa->generar_TA()) {
    echo 'obtenido nuevo TA';  
  } else {
    echo 'error al obtener el TA';
  }
} else {
  echo $wsaa->get_expiration();
};



/**********************
 * Ejemplo WSFE
 * ********************
 */

$wsfe = new WSFE('./');
 
 
// Carga el archivo TA.xml
if($wsfe->openTA())
	echo "WSFE open TA OK";
else
	echo "WSFE open TA Error";

//$wsfe->getTiposCbte();
//$wsfe->getTiposConcepto();
//$wsfe->getTiposIva();
//$wsfe->getTiposMonedas();
//$wsfe->getTiposTributos();
//$wsfe->getTiposDoc();

 
 /* 
// devuelve el cae
$ptovta = 1; 
$tipocbte = 1;
                   
// registro con los datos de la factura
$regfac['tipo_doc'] = 80;
$regfac['nro_doc'] = 60362;
$regfac['imp_total'] = 121.67;
$regfac['imp_tot_conc'] = 0;
$regfac['imp_neto'] = 100.55;
$regfac['impto_liq'] = 21.12;
$regfac['impto_liq_rni'] = 0.0;
$regfac['imp_op_ex'] = 0.0;
$regfac['fecha_venc_pago'] = date('Ymd');

//$nro = $wsfe->ultNro();
//if($nro == false) echo "erorrrrrrr ultNro";

$cmp = $wsfe->recuperaLastCMP($ptovta, $tipocbte);
if($cmp == false) echo "erorrrrrrr cmppp";

*/

$regfac['DocTipo'] = 80; //El cuit del comprador 
$regfac['DocNro']  = 23111111112;
$regfac['CbteDesde'] = 1;
$regfac['CbteHasta'] = 1;
$regfac['ImpTotal'] = 121.67; //La suma de todos los totales
$regfac['ImpTotConc'] = 0; 
$regfac['ImpNeto'] = 100.55;
$regfac['ImpOpEx'] = 0.0;
$regfac['ImpIVA'] = 21.12;
$regfac['ImpTrib'] = 0.0;
$regfac['FchVtoPago'] = '20150709';

$cbtes = array(
				array(
                     'Concepto' => 1,
                     'DocTipo' => $regfac['DocTipo'],
                     'DocNro' => $regfac['DocNro'],
	                 'CbteDesde' => 1,
                     'CbteHasta' => 1,
                     'CbteFch' => date('Ymd'),
                     'ImpTotal' => $regfac['ImpTotal'],
                     'ImpTotConc' => $regfac['ImpTotConc'],
                     'ImpNeto' => $regfac['ImpNeto'],
                     'ImpOpEx' => $regfac['ImpOpEx'],
                     'ImpIVA' => $regfac['ImpIVA'],
                     'ImpTrib' => $regfac['ImpTrib'],
                     'MonId' => 'PES',
                     'MonCotiz' => 1,
                     'Iva' => array( 'AlicIva' => array( 'Id' => 5, //21%
                     									'BaseImp' => $regfac['ImpNeto'],
                     									'Importe' => $regfac['ImpIVA']
                     									)
                     				)
					 ),
				array(
                     'Concepto' => 1,
                     'DocTipo' => $regfac['DocTipo'],
                     'DocNro' => $regfac['DocNro'],
	                 'CbteDesde' => 2,
                     'CbteHasta' => 2,
                     'CbteFch' => date('Ymd'),
                     'ImpTotal' => $regfac['ImpTotal'],
                     'ImpTotConc' => $regfac['ImpTotConc'],
                     'ImpNeto' => $regfac['ImpNeto'],
                     'ImpOpEx' => $regfac['ImpOpEx'],
                     'ImpIVA' => $regfac['ImpIVA'],
                     'ImpTrib' => $regfac['ImpTrib'],
                     'MonId' => 'PES',
                     'MonCotiz' => 1,
                     'Iva' => array( 'AlicIva' => array( 'Id' => 5, //21%
                     									'BaseImp' => $regfac['ImpNeto'],
                     									'Importe' => $regfac['ImpIVA']
                     									)
                     				)                     
					 )					 
             );

$cae = $wsfe->aut( 2, 1, 1, $cbtes);

//if($cae == false) echo "erorrrrrrr Caeee";

print_r($cae);

?>