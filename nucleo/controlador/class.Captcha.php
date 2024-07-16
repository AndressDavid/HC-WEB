<?php
namespace NUCLEO;

class Captcha {
	protected $aBackgroundColors = [];
	protected $nBackgroundColors = 5;
	protected $nBackgroundRectangles = 500;
	protected $nBackgroundFactor = 10;
	protected $oBackgroundFillColor = null;
	protected $taConfig = [];
	protected $aColorsText	= [];
	protected $aFonts = [];
	protected $aFontSize = [22];
	protected $cAlphabet = 'ABCEFGHJKLMPQRSTUVWXYZ';
	protected $cCaptchaString = '';
	protected $cFontPath = '';
	protected $nBloques = 15;
	protected $nCaptchaLen = 4;
	protected $nColorBlue = 0; 	
	protected $nColorGreen = 0;
	protected $nColorRed = 0;
	protected $nImgHeight = 50;
	protected $nImgWidth = 200;
	protected $oColorBlack = null;
	protected $oColorRed = null;
	protected $oColorWhite = null;
	protected $cPathImageCreateFromGif = "/var/www/html/nucleo/publico/imagenes/captcha/captcha.gif";
	protected $cPathImageCreateFromPng = "/var/www/html/nucleo/publico/imagenes/captcha/captcha.png";

    function __construct($tcFontPath = '', $taFonts=['arialbd.ttf'], $taConfig=[]) {		
		$this->generar($tcFontPath, $taFonts, $taConfig, $taConfig);
	}

	public function generar($tcFontPath = '', $taFonts=['arialbd.ttf'], $taConfig=[]){
		$this->cCaptchaString = $this->generateCaptchString($this->cAlphabet, $this->nCaptchaLen);
		$this->cFontPath = $tcFontPath;
		$this->loadFonts($taFonts);
		$this->aConfig = $taConfig;
	}
	
	public function generarImagen(){

		$loImgCaptcha = @imagecreatetruecolor($this->nImgWidth, $this->nImgHeight);
		$llResult =  ((is_resource($loImgCaptcha) && get_resource_type($loImgCaptcha) === 'gd') || (is_object($loImgCaptcha) && $loImgCaptcha instanceof \GDImage));
		
		if($llResult==true){
			$this->oColorBlack = imagecolorallocate($loImgCaptcha, 0, 0, 0);
			$this->oColorRed = imagecolorallocate($loImgCaptcha, 255, 0, 0);
			$this->oColorWhite = imagecolorallocate($loImgCaptcha, 255, 255, 255);			
			$this->oColorBlack = imagecolorallocate($loImgCaptcha, 0, 0, 0);	
			$this->aColorsText = [$this->oColorWhite];
			
			$this->fillBackground($loImgCaptcha);
			$this->cCaptchaString = $this->generateCaptchString($this->cAlphabet, $this->nCaptchaLen);
				
			if(count($this->aFonts)>0){
				$this->putText($loImgCaptcha);
			}
			
			$this->putDistractor($loImgCaptcha);
		}
		
		return $loImgCaptcha;
	}
	
	private function fillBackground($toImgCaptcha){
		$this->nColorBlue = rand(125, 175); 	
		$this->nColorGreen = rand(125, 175);
		$this->nColorRed = rand(125, 175);	
		
		$this->aBackgroundColors = [];
		
		for($lnColor = 0; $lnColor < $this->nBackgroundColors; $lnColor++) {
			$this->aBackgroundColors[] = imagecolorallocate($toImgCaptcha, $this->nColorRed - $this->nBackgroundFactor*$lnColor, $this->nColorGreen - $this->nBackgroundFactor*$lnColor, $this->nColorBlue - $this->nBackgroundFactor*$lnColor);
		}
		
		$this->oBackgroundFillColor = $this->aBackgroundColors[0];
		imagefill($toImgCaptcha, 0, 0, $this->oBackgroundFillColor);

		for($lnRectangle = 0; $lnRectangle < $this->nBackgroundRectangles; $lnRectangle++) {
			imagesetthickness($toImgCaptcha, rand(2,5));
			$loRectangleColor = $this->aBackgroundColors[array_rand($this->aBackgroundColors)];	
			$lnY = rand(0, $this->nImgHeight);
			$lnX = rand(0, $this->nImgWidth);
			imagerectangle($toImgCaptcha, $lnX, $lnY, $lnX+1, $lnY+1, $loRectangleColor);
		}
	}
	
	private function loadFonts($taFonts = []){
		if(is_array($taFonts)){
			foreach($taFonts as $lcFont){
				$lcFont = $this->cFontPath.$lcFont;
				if(is_file($lcFont)==true){
					$this->aFonts[] = $lcFont;
				}
			}
		}
		return (count($this->aFonts)>0);
	}	
	
	private function putDistractor($toImgCaptcha){
		for($lnBloque = 0; $lnBloque < $this->nImgWidth/$this->nBloques; $lnBloque++) {
			imagesetthickness($toImgCaptcha, 2);
			$lnFix = rand(-2,2);
			$lnY = rand(2, $this->nImgHeight-2);
			$lnX = ($lnBloque*$this->nBloques)+$lnFix;
			imagerectangle($toImgCaptcha, $lnX, $lnY, $lnX+1, $lnY+1, $this->oColorBlack);
			imagerectangle($toImgCaptcha, $lnX, ($this->nImgHeight/2), $lnX+1, ($this->nImgHeight/2)+1, $this->oColorBlack);
			imagerectangle($toImgCaptcha, $lnX, $this->nImgHeight-$lnY, $lnX+1, ($this->nImgHeight-$lnY)+1, $this->oColorBlack);
		}		
	}
	
	private function putText($toImgCaptcha){
		for($lnCharacter = 0; $lnCharacter < $this->nCaptchaLen; $lnCharacter++) {
			$lnLetterWidth = $this->nImgWidth/$this->nCaptchaLen;
			$lnLetterInitialLeft = 10;
		   
			imagettftext($toImgCaptcha, $this->aFontSize[array_rand($this->aFontSize)], rand(-18,18), $lnLetterInitialLeft + $lnCharacter*$lnLetterWidth, rand(42, 40), $this->aColorsText[array_rand($this->aColorsText)], $this->aFonts[array_rand($this->aFonts)], $this->cCaptchaString[$lnCharacter]);
		}		
	}
	
	private function generateCaptchString($tcAlphabet, $tnCaptchaLen = 10) {
		$lcAlphabetLen = strlen($tcAlphabet);
		$lcCaptcha = '';
		
		for($lnCharacter = 0; $lnCharacter < $tnCaptchaLen; $lnCharacter++) {
			$lcCharacter = $tcAlphabet[mt_rand(0, $lcAlphabetLen - 1)];
			$lcCaptcha .= $lcCharacter;
		}
		
		return $lcCaptcha;
	}
	
	public function validCaptcha($tcCaptchaUser=''){
		$tcCaptchaUser = trim(mb_strtoupper(strval($tcCaptchaUser)));
		return ($tcCaptchaUser == $this->cCaptchaString);
	}

	public function getImage(){
		header('Content-type: image/png');
		$loImagen = $this->generarImagen();
		imagepng($loImagen);
		imagedestroy($loImagen);
	}

	public function getBackgroundColors($tcType='colors'){
		if(mb_strtolower(strval($tcType))=='count'){
			return $this->nBackgroundColors;
		}
		return $this->aBackgroundColors;
	}
	
	public function getBackgroundRectangles(){
		return $this->nBackgroundRectangles;
	}
	
	public function getBackgroundFactor(){
		return $this->nBackgroundFactor;
	}
	
	public function getColorsText(){
		return $this->aColorsText;
	}
	
	public function getFonts(){
		return $this->aFonts;
	}
	
	public function getFontSizes(){
		return $this->aFontSize;
	}
	
	public function getAlphabet(){
		return $this->cAlphabet;
	}
	
	public function getCaptchaString(){
		return $this->cCaptchaString;
	}
	
	public function getFontPath(){
		return $this->cFontPath;
	}
	
	public function getBloques(){
		return $this->nBloques ;
	}
	
	public function getCaptchaLen(){
		return $this->nCaptchaLen;
	}
	
	public function getImgHeight(){
		return $this->nImgHeight;
	}
	
	public function getImgWidth(){
		return $this->nImgWidth ;
	}
	
	public function getBackgroundFillColor(){
		return $this->oBackgroundFillColor;
	}
}

?>