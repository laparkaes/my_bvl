<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends CI_Controller {

	public function __construct(){
		parent::__construct();
		set_time_limit(0);
		$this->load->model('general_model','gm');
		//$this->load->model('stock_model','stock');
	}

	public function index(){
		//load companies with one stock record at least
		$companies = $this->gm->filter("company", ["qty_total >" => 0], null, null, [["qty_this_year", "desc"], ["companyName", "asc"]]);
		
		//load sectors
		$sectors = [];
		$sectors_rec = $this->gm->all("sector");
		foreach($sectors_rec as $s) $sectors[$s->sector_id] = $s->sectorDescription;
		
		//load favorites
		$favorites = [];
		$favorites_rec = $this->gm->all("favorite");
		foreach($favorites_rec as $f) $favorites[] = $f->company_id;
		
		$data = [
			"sectors" => $sectors,
			"favorites" => $favorites,
			"companies" => $companies,
			"main" => "company/index",
		];
		$this->load->view('layout', $data);
	}
	
	public function detail($company_id){
		$success_msgs = $error_msgs = [];
		
		$company = $this->gm->unique("company", "company_id", $company_id);
		if (!$company){
			$error_msgs[] = "Empresa no existe.";
			
			$msgs = ["success_msgs" => $success_msgs, "error_msgs" => $error_msgs];
			$this->session->set_flashdata('msgs', $msgs);
			redirect("/company");
		}
		
		$company->sector = $this->gm->unique("sector", "sector_id", $company->sector_id);
		$memories = $this->gm->filter("memory", ["companyName" => $company->companyName], null, null, [["date", "desc"]]);
		$stocks = $this->gm->filter("stock", ["nemonico" => $company->stock], null, null, [["date", "desc"]]);
		
		print_r($company); echo "<br/><br/>";
		
		print_r($memories); echo "<br/><br/>";
		
		print_r($stocks); echo "<br/><br/>";
	}
	
	public function update_reg_qty(){
		//updated at 2023-12-16
		$companies = $this->gm->all("company");
		
		$this_year = date("Y");
		$this_year_f = ["date >=" => $this_year."-01-01", "date <=" => $this_year."-12-31"];
		
		$last_year = $this_year - 1; 
		$last_year_f = ["date >=" => $last_year."-01-01", "date <=" => $last_year."-12-31"];
		
		foreach($companies as $c){
			$this_year_f["nemonico"] = $last_year_f["nemonico"] = $c->stock;
			
			$data = [
				"qty_total" => $this->gm->qty("stock", ["nemonico" => $c->stock]),
				"qty_this_year" => $this->gm->qty("stock", $this_year_f),
				"qty_last_year" => $this->gm->qty("stock", $last_year_f),
			];
			
			$this->gm->update("company", ["company_id" => $c->company_id], $data);
			echo $c->companyName." - ".$c->stock." ... ok.<br/>";
		}
		
		echo "<br/>------------------<br/>#Reg.Qty actualizados.";
	}
	
	public function favorite_control(){
		$data = ["company_id" => $this->input->post("company_id")];
		if ($this->gm->filter("favorite", $data)){
			$this->gm->delete("favorite", ["company_id" => $this->input->post("company_id")]);
			$data["type"] = "removed";
		}else{
			$this->gm->insert("favorite", ["company_id" => $this->input->post("company_id")]);
			$data["type"] = "inserted";
		}
		
		header('Content-Type: application/json');
		echo json_encode($data);
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
