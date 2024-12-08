<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Load_bvl extends CI_Controller {

	public function __construct(){
		parent::__construct();
		set_time_limit(0);
		$this->start_time = microtime(true);
		$this->load->model('general_model','gen_m');
	}
	
	public function company(){
		$url = "https://dataondemand.bvl.com.pe/v1/issuers/search";
		$data = [
			"companyName" => "",
			"firstLetter" => "",
			"sectorCode" => "",
		];
		
		$companies = [];
		$res = $this->exec_curl($url, $data, true);
		foreach($res as $item){
			unset($item->memory);
			unset($item->fixedValues);
			unset($item->index);
			
			if ($item->stock) foreach($item->stock as $stock){
				if ($stock){
					$com = [
						'code' => $item->companyCode,
						'name' => $item->companyName,
						'sector_code' => $item->sectorCode,
						'sector_desc' => $item->sectorDescription,
						'nemonico' => $stock,
					];
					
					if (!$this->gen_m->filter("company", $com)) $companies[] = $com;
				}
			}
		}
		
		$qty_new = $this->gen_m->insert_multi("company", $companies);
		echo number_format($qty_new)." new companies created. (".number_format(microtime(true) - $this->start_time, 2)." sec)";
	}
	
	public function last_stock(){
		$url = "https://dataondemand.bvl.com.pe/v1/stock-quote/market";
		$data = [
			"companyCode" => "",
			"inputCompany" => "",
			"sector" => "",
			"today" => false,
		];
		
		$res = $this->exec_curl($url, $data, true);
		
		$qty_up = $res->up;
		$qty_down = $res->down;
		$qty_equal = $res->equal;
		
		$stocks = $res->content;
		foreach($stocks as $item){
			print_r($item);
			echo "<br/><br/>";
		}
	}

	public function index(){
		//1. cargar ultimos registros de cada empresa
		$dates = [];
		
		//2. ordenar en un $dates[nemonico][last_date] = fecha
		$last_stocks = $this->stock->get_last_stocks();//cargar ultimos registros de cada empresa desde DB
		foreach($last_stocks as $ls) $dates[$ls->nemonico]["last_date"] = $ls->last_date;
		
		//3. cargar movimientos de hoy desde bvl
		$stocks_d = $stocks_f = []; //arreglos para guardar empresas nacionales y extranjeras
		$stocks_now = $this->get_now(true);//cargar registros de hoy desde bvl
		$stocks = $stocks_now->content;//en content guardan los registros
		foreach($stocks as $s){
			if (property_exists($s, 'sectorCode')) $stocks_d[] = $s;//nacional
			else $stocks_f[] = $s;//extranjera
		}
		
		//4. ordenar en el mismo $dates[nemonico][previousDate] = valor
		foreach($stocks_d as $s){//solo trabajare con nacionales
			//debe existir propiedad dia anterior. En caso contrario, es primer dia que se presenta movimiento
			if (property_exists($s, 'previousDate')) $dates[$s->nemonico]["previousDate"] = $s->previousDate;
			//else unset($dates[$s->nemonico]);
		}
		
		//5. armar arreglo de actualizaciones en $updates = ["stock" =>, "date" =>]
		$updates = [];
		if ($this->input->get("all_update")){//variable que manda si quiere actualizacion general. ?all_update=1
			$companies = $this->gm->filter("company", null, null, null, [["companyName", "asc"]]);
			foreach($companies as $c) if ($c->stock) $updates[] = ["stock" => $c->stock, "date" => ""];	
		}else{
			foreach($dates as $stock => $ms)
				if (array_key_exists('previousDate', $ms))
					if (strtotime($ms["last_date"]) < strtotime($ms["previousDate"]))
						$updates[] = ["stock" => $stock, "date" => $ms["last_date"]];	
		}
		
		//6. evaluar cantidad de elementos de $companies
		if (count($updates) > 0){
			//6.1. si  > 0, cargar vista index_update
			$data = [
				"updates" => $updates,
				"main" => "home/index_update",
			];
		}else{
			//6.2. en caso contrario, cargar vista de resumen del dia separando lista de favoritos y general
			
			//6.2.1. set favorites
			$favorites = [];
			$favorites_rec = $this->gm->all("favorite");
			foreach($favorites_rec as $f) $favorites[] = $f->company_id;
			
			$data = [
				"favorites" => $favorites,
				"companies" => $this->set_today_companies($stocks_d),
				"main" => "home/index",
			];
		}
		
		$this->load->view('layout', $data);
	}

	//usado en: index
	public function set_today_companies($companies){
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
		
		return $companies;
	}
	
	//usado en: index
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
	
	//usado en: get_now, update_company
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
