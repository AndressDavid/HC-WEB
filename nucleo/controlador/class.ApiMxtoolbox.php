<?php

namespace NUCLEO;

class ApiMxtoolbox
{
	private $aTokens = [];
	private $aURL = ['usage'=>'https://api.mxtoolbox.com/api/v1/Usage', 'monitor'=>'https://api.mxtoolbox.com/api/v1/Monitor'];
	private $aInfo = [];
	private $nConsultados = 0;

    function __construct($tcTokens=''){
		$this->inicializar($tcTokens);
    }
	
	function inicializar($tcTokens=''){
		$this->aTokens = $laTokens = explode(',',$tcTokens);
		$lnResult =0;
		
		foreach($this->aTokens as $lnToken => $lcToken){
			$this->aInfo[$lnToken] = ['id'=>$lcToken];
			foreach($this->aURL as $lcType => $lcTypeUrl){
				$loCURL = curl_init();
						
				$llResult = false;
				$lcResult = '';
				$laHeader = ['Accept: application/json','content-type: application/json', 'Authorization: '.$lcToken];
				$this->aInfo[$lnToken][$lcType]=['correct'=>false, 'status'=>'', 'request'=>'', 'data'=>[], 'error'=>''];

				curl_setopt($loCURL, CURLOPT_CUSTOMREQUEST, 'GET');
				curl_setopt($loCURL, CURLOPT_POST, false);
				curl_setopt($loCURL, CURLOPT_URL, $lcTypeUrl);
				curl_setopt($loCURL, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($loCURL, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($loCURL, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($loCURL, CURLOPT_HEADER, false);
				curl_setopt($loCURL, CURLOPT_HTTPHEADER,$laHeader);
				

				if(!$lcResult = curl_exec($loCURL)) {
					$this->aInfo[$lnToken][$lcType]['data']=[];
					$this->aInfo[$lnToken][$lcType]['error'] = curl_error($loCURL);
					$this->aInfo[$lnToken][$lcType]['status'] = curl_getinfo($loCURL, CURLINFO_HTTP_CODE);
				}else{
					$lnResult+=1;
					$llResult = true;
					$this->aInfo[$lnToken][$lcType]['data']=json_decode($lcResult);
					$this->aInfo[$lnToken][$lcType]['error']=''; 
					$this->aInfo[$lnToken][$lcType]['status']='';
				}
				$this->aInfo[$lnToken][$lcType]['correct']=$llResult;
				curl_close($loCURL);
			}
		}
		$this->nConsultados = $lnResult ;
		
		return $lnResult;
	}

	function getInfo(){
		return $this->aInfo;
	}
	
	function setToken($tcTokens=''){
		$this->aTokens = $laTokens = explode(',',$tcTokens);
	}
}