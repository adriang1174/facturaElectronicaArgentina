<?php

class WSFE {

  const CUIT = "20177397203";                 # CUIT del emisor de las facturas
  const TA =    "xmlgenerados/TA.xml";        # Archivo con el Token y Sign
  const WSDL = "wsfe.wsdl";                   # The WSDL corresponding to WSFE
  const CERT = "keys/ghf.crt";                # The X.509 certificate in PEM format
  const PRIVATEKEY = "keys/ghf.key";          # The private key correspoding to CERT (PEM)
  const PASSPHRASE = "";                      # The passphrase (if any) to sign
  const PROXY_ENABLE = false;
  const LOG_XMLS = false;                     # For debugging purposes
  const WSFEURL = "https://wswhomo.afip.gov.ar/wsfev1/service.asmx"; // testing
  //const WSFEURL = "?????????????????"; // produccion  

  
  /*
   * el path relativo, terminado en /
   */
  private $path = './';
  
  /*
   * manejo de errores
   */
  public $error = '';
  
  /**
   * Cliente SOAP
   */
  private $client;
  
  /**
   * objeto que va a contener el xml de TA
   */
  private $TA;
  
  /**
   * tipo_cbte defije si es factura A = 1 o B = 6
   */
  private $tipo_cbte = '1';
  
  /*
   * Constructor
   */
  public function __construct($path = './') 
  {
    $this->path = $path;
    
    // seteos en php
    ini_set("soap.wsdl_cache_enabled", "0");    
    
    // validar archivos necesarios
    if (!file_exists($this->path.self::WSDL)) $this->error .= " Failed to open ".self::WSDL;
    
    if(!empty($this->error)) {
      throw new Exception('WSFE class. Faltan archivos necesarios para el funcionamiento');
    }        
    
    $this->client = new SoapClient($this->path.self::WSDL, array( 
              'soap_version' => SOAP_1_2,
              'location'     => self::WSFEURL,
              'exceptions'   => 0,
              'trace'        => 1)
    ); 
  }
  
  /**
   * Chequea los errores en la operacion, si encuentra algun error falta lanza una exepcion
   * si encuentra un error no fatal, loguea lo que paso en $this->error
   */
  private function _checkErrors($results, $method)
  {
    if (self::LOG_XMLS) {
      file_put_contents("xmlgenerados/request-".$method.".xml",$this->client->__getLastRequest());
      file_put_contents("xmlgenerados/response-".$method.".xml",$this->client->__getLastResponse());
    }
    
    if (is_soap_fault($results)) {
      throw new Exception('WSFE class. FaultString: ' . $results->faultcode.' '.$results->faultstring);
    }
    
    if ($method == 'FEDummy') {return;}
    
    $XXX=$method.'Result';
    if ($results->$XXX->RError->percode != 0) {
        $this->error = "Method=$method errcode=".$results->$XXX->RError->percode." errmsg=".$results->$XXX->RError->perrmsg;
    }
    
    return $results->$XXX->RError->percode != 0 ? true : false;
  }

  /**
   * Abre el archivo de TA xml,
   * si hay algun problema devuelve false
   */
  public function openTA()
  {
    $this->TA = simplexml_load_file($this->path.self::TA);
    
    return $this->TA == false ? false : true;
  }
  
  /**
   * Retorna la cantidad maxima de registros de detalle que 
   * puede tener una invocacion al FEAutorizarRequest
   */
  public function recuperaQTY()
  {
    $results = $this->client->FECompTotXRequest(
      array('Auth'=>array('Token' => $this->TA->credentials->token,
                              'Sign' => $this->TA->credentials->sign,
                              'Cuit' => self::CUIT)));
    
    $e = $this->_checkErrors($results, 'FECompTotXRequest');
        
    return $e == false ? $results->FECompTotXRequestResult->RegXReq : false;
  }


  
  /*
   * Retorna el ultimo comprobante autorizado para el tipo de comprobante /cuit / punto de venta ingresado.
   */ 
  public function recuperaLastCMP ($ptovta)
  {
    $results = $this->client->FECompUltimoAutorizado(
     array('Auth' =>  array('Token'    => $this->TA->credentials->token,
                                'Sign'     => $this->TA->credentials->sign,
                                'Cuit'     => self::CUIT),
             'PtoVta'   => $ptovta,
             'CbteTipo' => $this->tipo_cbte));

    //var_dump($results);
    $e = $this->_checkErrors($results, 'FECompUltimoAutorizado');

    return $e == false ? $results->FECompUltimoAutorizadoResult->CbteNro : false;
  }
  
  /*
   * Obtiene los tipos de Documentos
   */
  public function getTiposDoc()
  {
    $params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = self::CUIT;
    $results = $this->client->FEParamGetTiposDoc($params);
    
    //this->_checkErrors($results, 'FEParamGetTiposDoc');
    print_r($results);
    //fclose($fh);
  }

  /*
   * Obtiene los tipos de Comprobante
   */
  public function getTiposCbte()
  {
    $params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = self::CUIT;
    $results = $this->client->FEParamGetTiposCbte($params);
    
    //this->_checkErrors($results, 'FEParamGetTiposDoc');
    print_r($results);
    //fclose($fh);
  }

  /*
   * Obtiene los tipos de Concepto
   */
  public function getTiposConcepto()
  {
    $params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = self::CUIT;
    $results = $this->client->FEParamGetTiposConcepto($params);
    
    //this->_checkErrors($results, 'FEParamGetTiposDoc');
    print_r($results);
    //fclose($fh);
  }
  
  /*
   * Obtiene los tipos de IVA
   */
  public function getTiposIva()
  {
    $params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = self::CUIT;
    $results = $this->client->FEParamGetTiposIva($params);
    
    //this->_checkErrors($results, 'FEParamGetTiposDoc');
    print_r($results);
    //fclose($fh);
  }
  
  /*
   * Obtiene los tipos de Monedas
   */
  public function getTiposMonedas()
  {
    $params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = self::CUIT;
    $results = $this->client->FEParamGetTiposMonedas($params);
    
    print_r($results);
  }
  /*
   * Obtiene los tipos de Tributos
   */
  public function getTiposTributos()
  {
    $params->Auth->Token = $this->TA->credentials->token;
    $params->Auth->Sign = $this->TA->credentials->sign;
    $params->Auth->Cuit = self::CUIT;
    $results = $this->client->FEParamGetTiposTributos($params);
    
    print_r($results);
  }

  /**
   * Setea el tipo de comprobante
   * A = 1
   * B = 6
   */
  public function setTipoCbte($tipo) 
  {
    switch($tipo) {
      case 'a': case 'A': case '1':
        $this->tipo_cbte = 1;
      break;
      
      case 'b': case 'B': case 'c': case 'C': case '6':
        $this->tipo_cbte = 6;
      break;
      
      default:
        return false;
    }

    return true;
  }

  // Dado un lote de comprobantes retorna el mismo autorizado con el CAE otorgado.
  public function aut( $cantreg, $cbtetipo, $ptovta, $regfac)
  { 
    
    $results = $this->client->FECAESolicitar(
      array('Auth' => array(
               'Token' => $this->TA->credentials->token,
               'Sign'  => $this->TA->credentials->sign,
               'Cuit'  => self::CUIT),
            'FeCAEReq' => array(
               'FeCabReq' => array(
                  'CantReg' => $cantreg, 
                  'PtoVta' => $ptovta,
                  'CbteTipo' => $cbtetipo,
                  ),
               'FeDetReq' => array(
                   'FECAEDetRequest' => array(
                     'Concepto' => 1,
                     'DocTipo' => $regfac['DocTipo'],
                     'DocNro' => $regfac['DocNro'],
	                 'CbteDesde' => $regfac['CbteDesde'],
                     'CbteHasta' => $regfac['CbteHasta'],
                     'CbteFch' => date('Ymd'),
                     'ImpTotal' => $regfac['ImpTotal'],
                     'ImpTotConc' => $regfac['ImpTotConc'],
                     'ImpNeto' => $regfac['ImpNeto'],
                     'ImpOpEx' => $regfac['ImpOpEx'],
                     'ImpIVA' => $regfac['ImpIVA'],
                     'ImpTrib' => $regfac['ImpTrib'],
                     'FchVtoPago' => $regfac['FchVtoPago'],
                     'MonId' => 'PES',
                     'MonCotiz' => 1,
	                
                     )//FECAEDetRequest
                 )//FeDetReq
       		)//FECAEReq
     )//FECAESolicitar
     );
    
    //$e = $this->_checkErrors($results, 'FECAESolicitar');
        
    //return $e == false ? Array( 'cae' => $results->FECAESolicitarResult->FeDetResp->FEDetResponse->CAE, 'fecha_vencimiento' => $results->FECAESolicitarResult->FeDetResp->FEDetResponse->CAEFchVto ): false;
    return $results;
  }

} // class

?>
