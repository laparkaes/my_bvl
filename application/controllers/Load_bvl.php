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
	
	public function today(){
		$url = "https://dataondemand.bvl.com.pe/v1/stock-quote/market";
		$data = [
			"companyCode" => "",
			"inputCompany" => "",
			"sector" => "",
			"today" => true,
		];
		
		$res = $this->exec_curl($url, $data, true);
		
		$qty_up = $res->up;
		$qty_down = $res->down;
		$qty_equal = $res->equal;
		
		$data = [];
		$stocks = $res->content;
		foreach($stocks as $item){
			$stock = [
				'code' => property_exists($item, 'companyCode') ? $item->companyCode : null,
				'name' => property_exists($item, 'companyName') ? $item->companyName : null,
				'name_short' => property_exists($item, 'shortName') ? $item->shortName : null,
				'sector_code' => property_exists($item, 'sectorCode') ? $item->sectorCode : null,
				'sector_desc' => property_exists($item, 'sectorDescription') ? $item->sectorDescription : null,
				'nemonico' => property_exists($item, 'nemonico') ? $item->nemonico : null,
				'date_last' => property_exists($item, 'lastDate') ? $item->lastDate : null,
				'date_created' => property_exists($item, 'createdDate') ? $item->createdDate : null,
				'date_previous' => property_exists($item, 'previousDate') ? $item->previousDate : null,
				'previous' => property_exists($item, 'previous') ? $item->previous : null,
				'buy' => property_exists($item, 'buy') ? $item->buy : null,
				'sell' => property_exists($item, 'sell') ? $item->sell : null,
				'minimun' => property_exists($item, 'minimun') ? $item->minimun : null,
				'maximun' => property_exists($item, 'maximun') ? $item->maximun : null,
				'open' => property_exists($item, 'opening') ? $item->opening : null,
				'close' => property_exists($item, 'last') ? $item->last : null,
				'variation_per' => property_exists($item, 'percentageChange') ? $item->percentageChange : null,
				'currency' => property_exists($item, 'currency') ? $item->currency : null,
				'nego_qty' => property_exists($item, 'negotiatedQuantity') ? $item->negotiatedQuantity : null,
				'nego_amount' => property_exists($item, 'negotiatedAmount') ? $item->negotiatedAmount : null,
				'nego_amount_pen' => property_exists($item, 'negotiatedNationalAmount') ? $item->negotiatedNationalAmount : null,
				'num_operation' => property_exists($item, 'operationsNumber') ? $item->operationsNumber : null,
				'num_nego' => property_exists($item, 'numNeg') ? $item->numNeg : null,
				'exderecho' => property_exists($item, 'exderecho') ? $item->exderecho : null,
				'unity' => property_exists($item, 'unity') ? $item->unity : null,
				'segment' => property_exists($item, 'segment') ? $item->segment : null,
			];
			
			$data[] = $stock;
		}
		
		$this->gen_m->empty_t("today");
		
		$qty_new = $this->gen_m->insert_multi("today", $data);
		echo number_format($qty_new)." today stock records updated. (".number_format(microtime(true) - $this->start_time, 2)." sec)";
	}
	
	public function stock(){
		$companies = $this->gen_m->all("company");
		foreach($companies as $item){
			print_r($item);
			echo "<br/><br/>";
		}
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
