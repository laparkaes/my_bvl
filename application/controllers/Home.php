<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct(){
		parent::__construct();
		set_time_limit(0);
		$this->load->model('general_model','gm');
		$this->load->model('stock_model','stock');
	}

	public function index(){
		//update, load and filter domestic companies
		$companies = $this->daily_update();
		
		//sort buy var%
		usort($companies, function($a, $b){
			if (!property_exists($a, 'percentageChange')) $a->percentageChange = 0;
			if (!property_exists($b, 'percentageChange')) $b->percentageChange = 0;
			return $a->percentageChange < $b->percentageChange;
		});
		
		//check properties and set colors
		$properties = ["buy", "sell", "opening", "last", "minimun", "maximun", "previous", "previousDate"];
		foreach($companies as $c){
			foreach($properties as $p) if (!property_exists($c, $p)) $c->$p = "-";
			
			$c->row_color = "";
			switch(true){
				case $c->percentageChange > 0:
					if ($c->percentageChange > 5) $c->row_color = "success";
					$c->color = "success";
					break;
				case $c->percentageChange < 0:
					if ($c->percentageChange < -5) $c->row_color = "danger";
					$c->color = "danger"; 
					break;
				default: $c->color = "";
			}
			$c->data = $this->gm->unique("company", "stock", $c->nemonico);
		}
		
		//load favorites
		$favorites = [];
		$favorites_rec = $this->gm->all("favorite");
		foreach($favorites_rec as $f) $favorites[] = $f->company_id;
		
		$data = [
			"favorites" => $favorites,
			"companies" => $companies,
			"main" => "home",
		];
		$this->load->view('layout', $data);
	}
		
	private function get_now($is_today){
		$url = "https://dataondemand.bvl.com.pe/v1/stock-quote/market";
		$data = [
			"companyCode" => "",
			"inputCompany" => "",
			"sector" => "",
			"today" => $is_today,
		];
		
		return $this->exec_curl($url, $data, true);
	}
	
	private function daily_update(){
		$my_stocks = [];
		
		//get lastest stocks of each company of my db
		$last_stocks = $this->stock->get_last_stocks();
		foreach($last_stocks as $ls) $my_stocks[$ls->nemonico]["last_date"] = $ls->last_date;
		
		$stocks_d = $stocks_f = []; //domestic & foreign stocks
		$stocks_now = $this->get_now(true);//load from bvl db
		$stocks = $stocks_now->content;//filter stock records
		foreach($stocks as $s){
			if (property_exists($s, 'sectorCode')) $stocks_d[] = $s;//domestic stock
			else $stocks_f[] = $s;//foreign stock
		}
		
		//just work with domestic stocks
		foreach($stocks_d as $s){
			if (property_exists($s, 'previousDate')){
				$my_stocks[$s->nemonico]["previousDate"] = $s->previousDate;
			}else unset($my_stocks[$s->nemonico]);//no stock history. maybe today is first stock record
		}
		
		foreach($my_stocks as $stock => $ms){
			if (array_key_exists('previousDate', $ms)){
				if (strtotime($ms["last_date"]) < strtotime($ms["previousDate"])){
					//need to update new stock history
					$this->update_stocks($stock, $ms["last_date"]);
					
					//update stock record qty in company table
					$qty = $this->gm->qty("stock", ["nemonico" => $stock]);
					if ($qty > 0) $this->gm->update("company", ["stock" => $stock], ["qty_total" => $qty]);
				}else unset($my_stocks[$stock]);//already last version of stock records
			}else unset($my_stocks[$stock]);//no today record exists
		}
		
		return $stocks_d;//return domestic records of today
	}
	
	public function general_update(){
		$this->update_company();
		echo "<br/>";
		
		$companies = $this->gm->filter("company", ["stock !=" => null], null, null, $orders = [["stock", "asc"]], "", "");
		foreach($companies as $c) if ($c->stock){
			//load last stock
			$last_stock = $this->gm->filter("stock", ["nemonico" => $c->stock], null, null, [["date", "desc"]], 1, 0);
			if ($last_stock) $last_date = $last_stock[0]->date; else $last_date = "1999-01-01";
			
			//update stocks from last stock date to today
			$this->update_stocks($c->stock, $last_date);
			
			//update stocks record qty in company table
			$qty = $this->gm->qty("stock", ["nemonico" => $c->stock]);
			if ($qty > 0) $this->gm->update("company", ["company_id" => $c->company_id], ["qty_total" => $qty]);
		}
		echo "<br/>";
		
		echo "Fin de actualizacion de general.<br/>";
	}
	
	private function update_stocks($code, $from = "", $to = ""){
		if (!$from) $from = "2000-01-01";
		if (!$to) $to = date('Y-m-d');
		
		$code = str_replace("/", "%2F", $code);
		$from_history = date('Y-m-d', strtotime($from));
		$to_history = date('Y-m-d', strtotime('+1 day', strtotime($to)));
		echo "search ".$code.", ".$from_history." ~ ".$to_history."<br/>";
		
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
		
		echo $code." - Fin de actualizacion de stocks.<br/><br/>";
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
