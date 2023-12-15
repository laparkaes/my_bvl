<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct(){
		parent::__construct();
		set_time_limit(0);
		$this->load->model('general_model','gm');
	}

	public function index(){
		
		$data = [
			
			"main" => "home",
		];
		//$this->load->view('layout', $data);
		
		$this->general_update();
		
	}
	
	private function general_update(){
		$this->update_company();
		echo "<br/>";
		
		$companies = $this->gm->filter("company", ["stock !=" => null]);
		foreach($companies as $c) if ($c->stock) $this->update_stocks($c->stock);
		echo "<br/>";
		
		echo "Fin de actualizacion de general.<br/>";
	}
	
	private function update_stocks($code, $from = "", $to = ""){
		if (!$from) $from = "2000-01-01";
		if (!$to) $to = date('Y-m-d');
		
		$code = str_replace("/", "%2F", $code);
		$from_history = date('Y-m-d', strtotime('-1 day', strtotime($from)));
		$to_history = date('Y-m-d', strtotime('+1 day', strtotime($to)));
		
		$datas = [];
		$url = "https://dataondemand.bvl.com.pe/v1/issuers/stock/".$code."?startDate=".$from_history."&endDate=".$to_history;
		$res = $this->exec_curl($url, null, false);
		if ($res) foreach($res as $item){
			if ($item->quantityNegotiated or $item->close){
				if (!trim($item->currencySymbol)) $item->currencySymbol = "S/"; else $item->currencySymbol = "US$";
				$datas[] = $item;
			}
		}
		
		usort($datas, function($a, $b){ return $a->date < $b->date; });
		
		//get last date
		$last_stock = $this->gm->filter("stock", ["nemonico" => $code], null, null, [["date", "desc"]], 1, 0);
		if ($last_stock) $last_date = $last_stock[0]->date; else $last_date = "1999-01-01";
		$last_date = strtotime($last_date);
		
		foreach($datas as $d){
			echo "checking... ".$code." ".$d->date;
			if ($last_date < strtotime($d->date)){
				unset($d->id);
				$this->gm->insert("stock", $d);
				echo " ... inserted.";
			}else echo " ... already exists.";
			echo "<br/>";
		}
		
		echo $code." - Fin de actualizacion de stocks.<br/>";
	}
	
	public function update_company(){
		$url = "https://dataondemand.bvl.com.pe/v1/issuers/search";
		$data = [
			"companyName" => "",
			"firstLetter" => "",
			"sectorCode" => "",
		];
		
		$sectors_string = $companies = $memories = [];
		
		$result = $this->exec_curl($url, $data, true);
		foreach($result as $c){
			$sector = $this->gm->unique("sector", "sectorCode", $c->sectorCode);
			if (!$sector){
				$this->gm->insert("sector", ["sectorCode" => $c->sectorCode, "sectorDescription" => $c->sectorDescription]);
				$sector = $this->gm->unique("sector", "sectorCode", $c->sectorCode);
			}
			
			$company = [
				"companyCode" => $c->companyCode,
				"companyName" => $c->companyName,
				"sector_id" => $sector->sector_id,
			];
			
			if ($c->stock){
				$stocks = $c->stock;
				foreach($stocks as $s){
					if (!$s) $s = null;
					$company["stock"] = $s;
					if (!$this->gm->filter("company", $company)) $this->gm->insert("company", $company);
				}
			}else{
				$company["stock"] = null;
				if (!$this->gm->filter("company", $company)) $this->gm->insert("company", $company);
			}
			
			$memories_aux = $c->memory;
			if ($memories_aux) foreach($memories_aux as $m){
				$memory = [
					"rpjCode" => $m->rpjCode,
					"companyName" => $m->companyName,
					"year" => $m->year,
					"document" => $m->document,
					"date" => $m->date,
					"path" => $m->path,
				];
				if (!$this->gm->filter("memory", $memory)) $this->gm->insert("memory", $memory);
			}
		}
		
		echo "Fin de actualizacion de empresas.<br/>";
	}
	
	private function exec_curl($url, $datas = null, $is_post = false){
		if ($is_post){
			$datas = json_encode($datas);
			$header_data = array(
								"Content-Type: application/json",
								"Content-Length: ".strlen($datas)
							);
		}else $header_data = array("Content-Type: application/json");

		$ch = curl_init(); //curl 사용 전 초기화 필수(curl handle)

		curl_setopt($ch, CURLOPT_URL, $url); //URL 지정하기
		curl_setopt($ch, CURLOPT_POST, $is_post); //0이 default 값이며 POST 통신을 위해 1로 설정해야 함
		if ($is_post) curl_setopt ($ch, CURLOPT_POSTFIELDS, $datas); //POST로 보낼 데이터 지정하기
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data); //header 지정하기
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); //이 옵션이 0으로 지정되면 curl_exec의 결과값을 브라우저에 바로 보여줌. 이 값을 1로 하면 결과값을 return하게 되어 변수에 저장 가능(테스트 시 기본값은 1인듯?)

		$res = curl_exec($ch);
		curl_close($ch);
		
		if ($res) return json_decode($res); else return null;
	}
}
