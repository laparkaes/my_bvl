<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use LupeCode\phpTraderNative\TALib\Enum\MovingAverageType;
use LupeCode\phpTraderNative\Trader;

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
		$company = $this->gm->unique("company", "company_id", $company_id);
		if (!$company){
			$success_msgs = $error_msgs = [];
			$error_msgs[] = "Empresa no existe.";
			
			$msgs = ["success_msgs" => $success_msgs, "error_msgs" => $error_msgs];
			$this->session->set_flashdata('msgs', $msgs);
			redirect("/company");
		}
		
		$company->sector = $this->gm->unique("sector", "sector_id", $company->sector_id);
		$memories = $this->gm->filter("memory", ["companyName" => $company->companyName], null, null, [["date", "desc"]]);
		$stocks = $this->gm->filter("stock", ["nemonico" => $company->stock], null, null, [["date", "desc"]]);
		
		$data = [
			"companyCode" => "",
			"inputCompany" => $company->stock,
			"sector" => "",
			"today" => false,
		];
		
		$offers = ["buy" => 0, "sell" => 0];
		$last_stock = $this->exec_curl("https://dataondemand.bvl.com.pe/v1/stock-quote/market", $data, true);
		if ($last_stock){
			$last_stock = $last_stock->content[0];
			$offers = ["buy" => $last_stock->buy, "sell" => $last_stock->sell];
			
			if (strtotime($last_stock->lastDate) > strtotime($stocks[0]->date)){
				$last_stock = $this->convert_today_to_record($last_stock);
				array_unshift($stocks, clone $last_stock);
			}else $last_stock = clone $stocks[0];
		}else $last_stock = clone $stocks[0];
		
		$last_stock->var_per = $this->get_var_per($last_stock);
		foreach($stocks as $s) $s->var_per = $this->get_var_per($s);
		
		$data = [
			"company" => $company,
			"memories" => $memories,
			"offers" => $offers,
			"last_stock" => $last_stock,
			"stocks" => $stocks,
			"main" => "company/detail",
		];
		$this->load->view('layout', $data);
	}
	
	private function get_var_per($stock){
		if ($stock->close and $stock->yesterdayClose){
			$value = ($stock->close - $stock->yesterdayClose) * 100 / $stock->yesterdayClose;
			switch(true){
				case ($value > 0): $ic = "+"; $bi = "up"; $color = "success"; break;
				case ($value == 0): $ic = ""; $bi = ""; $color = ""; break;
				case ($value < 0): $ic = "-"; $bi = "down"; $color = "danger"; break;
			}	
		}else{$ic = ""; $bi = ""; $color = ""; $value = 0;}
		
		
		return ["ic" => $ic, "bi" => $bi, "color" => $color, "value" => number_format(abs($value), 2)];
	}
	
	private function convert_today_to_record($today){
		$record = new stdClass;
		$record->stock_id = 0;
		$record->nemonico = $today->nemonico;
		$record->date = date("Y-m-d", strtotime($today->lastDate));
		$record->open = $today->opening;
		$record->close = $today->last;
		$record->high = $today->maximun;
		$record->low = $today->minimun;
		$record->average = 0;//no use data
		$record->quantityNegotiated = $today->negotiatedQuantity;
		$record->solAmountNegotiated = $today->negotiatedNationalAmount;
		$record->dollarAmountNegotiated = ($today->currency === "S/") ? $today->negotiatedAmount / 3.8 : $today->negotiatedAmount;
		$record->yesterday = $today->previousDate;
		$record->yesterdayClose = $today->previous;
		$record->currencySymbol = $today->currency;
		
		return $record;
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
	
	public function update_indicators($company_id){
		$company = $this->gm->unique("company", "company_id", $company_id);
		if (!$company){
			$success_msgs = $error_msgs = [];
			$error_msgs[] = "Empresa no existe.";
			
			$msgs = ["success_msgs" => $success_msgs, "error_msgs" => $error_msgs];
			$this->session->set_flashdata('msgs', $msgs);
			redirect("/company");
		}
		
		$dates = $closes = $highs = $lows = $negos = [];
		$stocks = $this->gm->filter("stock", ["nemonico" => $company->stock], null, null, [["date", "asc"]]);
		foreach($stocks as $s){
			if ($s->close){
				$dates[] = $s->date;
				$closes[] = $s->close;
				$highs[] = $s->high;
				$lows[] = $s->low;
				$negos[] = $s->quantityNegotiated;
			}
		}
		
		/*
		print_r($dates); echo "<br/><br/>";
		print_r($closes); echo "<br/><br/>";
		print_r($highs); echo "<br/><br/>";
		print_r($lows); echo "<br/><br/>";
		print_r($negos); echo "<br/><br/>";
		*/
		
		//adx
		$adx = $this->get_adx($highs, $lows, $closes, 14);
		$adx_pdi = $adx["pdi"];
		$adx_mdi = $adx["mdi"];
		$adx = $adx["adx"];
		
		//atr
		$atr = $this->get_atr($highs, $lows, $closes, 14);
		
		//bollinger band
		$bb = $this->get_bollinger($closes, 20, 2, 2, true);
		$bb_u = $bb["uppers"];
		$bb_m = $bb["middles"];
		$bb_l = $bb["lowers"];
		
		//cci
		$cci = $this->get_cci($highs, $lows, $closes, 20);
		
		//ema
		$ema_5 = $this->get_ema($closes, 5);
		$ema_20 = $this->get_ema($closes, 20);
		$ema_60 = $this->get_ema($closes, 60);
		$ema_120 = $this->get_ema($closes, 120);
		$ema_200 = $this->get_ema($closes, 200);
		
		//envelope
		$env = $this->get_envelope($closes, 20, 0.15);
		$env_u = $env["uppers"];
		$env_l = $env["lowers"];
		
		//ichomoku cloud
		$ich = $this->get_icloud($closes);
		$ich_a = $ich["span_a"];
		$ich_b = $ich["span_b"];
		
		echo count($negos); echo "<br/><br/>";
		echo count($ich_a); echo "<br/><br/>";
		print_r($ich_a);
	}
	
	private function blank_array($count){
		$arr = [];
		for($i = 0; $i < $count; $i++) $arr[] = null;
		return $arr;
	}
	
	private function get_adx($highs, $lows, $closes, $period = 14){
		$pdi = Trader::plus_di($highs, $lows, $closes, $period);
		$mdi = Trader::minus_di($highs, $lows, $closes, $period);
		$adx = Trader::adx($highs, $lows, $closes, $period);
		
		if ($pdi){
			$arr = $this->blank_array(count($closes) - count($pdi));
			$pdi = array_merge($arr, $pdi);
			$mdi = array_merge($arr, $mdi);
		}else $pdi = $mdi = $this->blank_array(count($closes));
		
		if ($adx) $adx = array_merge($this->blank_array(count($closes) - count($adx)), $adx);
		else $adx = $this->blank_array(count($closes));
		
		return ["pdi" => $pdi, "mdi" => $mdi, "adx" => $adx];
	}
	
	private function get_atr($highs, $lows, $closes, $period = 14){
		$atr = Trader::atr($highs, $lows, $closes, $period);
		if ($atr) return array_merge($this->blank_array(count($closes) - count($atr)), $atr);
		else return $this->blank_array(count($closes));
	}
	
	private function get_bollinger($closes, $period = 20, $mupper = 2, $mlower = 2, $is_sma = true){
		if ($is_sma) $avg_type = MovingAverageType::SMA; else $avg_type = MovingAverageType::EMA;
		$bollinger_general = Trader::bbands($closes, $period, $mupper, $mlower, $avg_type);
		if ($bollinger_general){
			$arr = $this->blank_array(count($closes) - count($bollinger_general["UpperBand"]));
			$uppers = array_merge($arr, $bollinger_general["UpperBand"]);
			$middles = array_merge($arr, $bollinger_general["MiddleBand"]);
			$lowers = array_merge($arr, $bollinger_general["LowerBand"]);
		}else $uppers = $middles = $lowers = $this->blank_array(count($closes));
		
		return ["uppers" => $uppers, "middles" => $middles, "lowers" => $lowers];
	}
	
	private function get_cci($highs, $lows, $closes, $period = 20){
		$cci = Trader::cci($highs, $lows, $closes, $period);
		if ($cci) return array_merge($this->blank_array(count($closes) - count($cci)), $cci);
		else return $this->blank_array(count($closes));
	}
	
	private function get_ema($closes, $period){
		$ema = Trader::ema($closes, $period);
		if ($ema) return array_merge($this->blank_array(count($closes) - count($ema)), $ema);
		else return $this->blank_array(count($closes));
	}
	
	private function get_envelope($closes, $period = 20, $diff = 0.15){
		$sma_20 = $this->get_sma($closes, $period);
		$top = 1 + $diff;
		$bottom = 1 - $diff;
		
		$uppers = $lowers = [];
		foreach($sma_20 as $item){
			array_push($uppers, $item * $top);
			array_push($lowers, $item * $bottom);
		}
		
		return ["uppers" => $uppers, "lowers" => $lowers];
	}
	
	private function get_icloud($closes){
		$span_a = $span_b = [];
		if ($closes){
			$max_9 = Trader::max($closes, 9);
			$min_9 = Trader::min($closes, 9);
			if ($max_9){
				$arr = $this->blank_array(count($closes) - count($max_9));
				$max_9 = array_merge($arr, $max_9);
				$min_9 = array_merge($arr, $min_9);
			}else $max_9 = $min_9 = $this->blank_array(count($closes));
			
			$max_26 = Trader::max($closes, 26);
			$min_26 = Trader::min($closes, 26);
			if ($max_26){
				$arr = $this->blank_array(count($closes) - count($max_26));
				$max_26 = array_merge($arr, $max_26);
				$min_26 = array_merge($arr, $min_26);
			}else $max_26 = $min_26 = $this->blank_array(count($closes));
			
			$max_52 = Trader::max($closes, 52);
			$min_52 = Trader::min($closes, 52);
			if ($max_52){
				$arr = $this->blank_array(count($closes) - count($max_52));
				$max_52 = array_merge($arr, $max_52);
				$min_52 = array_merge($arr, $min_52);
			}else $max_52 = $min_52 = $this->blank_array(count($closes));
			
			for($i = 0; $i < 26; $i++){
				$span_a[$i] = 0;
				$span_b[$i] = 0;
			}
			
			foreach($closes as $i => $value){
				$conversion_line = ($min_9[$i] + $max_9[$i]) / 2;
				$base_line = ($min_26[$i] + $max_26[$i]) / 2;
				
				$span_a[$i + 26] = ($conversion_line + $base_line) / 2;
				$span_b[$i + 26] = ($min_52[$i] + $max_52[$i]) / 2;
			}
		}
		
		return ["span_a" => $span_a, "span_b" => $span_b];
	}
	
	private function get_macd($closes, $fast_period, $slow_period, $signal_period){
		$macd_general = Trader::macd($closes, $fast_period, $slow_period, $signal_period);
		if ($macd_general){
			$arr = $this->blank_array(count($closes) - count($macd_general["MACD"]));
			$macd = array_merge($arr, $macd_general["MACD"]);
			$macd_signal = array_merge($arr, $macd_general["MACDSignal"]);
			$macd_divergence = array_merge($arr, $macd_general["MACDHist"]);
		}else $macd = $macd_signal = $macd_divergence = $this->blank_array(count($closes));
		
		return array("macd" => $macd, "macd_sig" => $macd_signal, "macd_div" => $macd_divergence);
	}
	
	private function get_mfi($highs, $lows, $closes, $negos, $period){
		$mfi = Trader::mfi($highs, $lows, $closes, $negos, $period);
		if ($mfi) return array_merge($this->blank_array(count($closes) - count($mfi)), $mfi);
		else return $this->blank_array(count($closes));
	}
	
	private function get_mom($closes, $period, $period_signal){
		$mom = Trader::mom($closes, $period);
		if ($mom){
			$mom = array_merge($this->blank_array(count($closes) - count($mom)), $mom);
			$mom_signal = $this->get_sma($mom, $period_signal);
		}else $mom = $mom_signal = $this->blank_array(count($closes));
		
		return array("mom" => $mom, "mom_signal" => $mom_signal);
	}
	
	private function get_parabolic_sar($highs, $lows, $acceleration, $maximum){
		$parabolic_sar = Trader::sar($highs, $lows, $acceleration, $maximum);
		if ($parabolic_sar) return array_merge($this->blank_array(count($highs) - count($parabolic_sar)), $parabolic_sar);
		else return $this->blank_array(count($highs));
	}
	
	private function get_ppo($closes, $fast_period, $slow_period, $is_sma){
		if ($is_sma) $ma = MovingAverageType::SMA; else $ma = MovingAverageType::EMA;
		$ppo = Trader::ppo($closes, $fast_period, $slow_period, $ma);
		if ($ppo) return array_merge($this->blank_array(count($closes) - count($ppo)), $ppo);
		else return $this->blank_array(count($closes));
	}
	
	private function get_price_channel($highs, $lows, $period){
		$uppers = Trader::max($highs, $period);
		$lowers = Trader::min($lows, $period);
		if ($uppers){
			$arr = $this->blank_array(count($highs) - count($uppers));
			$uppers = array_merge($arr, $uppers);
			$lowers = array_merge($arr, $lowers);
		}else $uppers = $lowers = $this->blank_array(count($highs));
		
		return array("uppers" => $uppers, "lowers" => $lowers);
	}
	
	private function get_rsi($closes, $period){
		$rsi = Trader::rsi($closes, $period);
		if ($rsi) return array_merge($this->blank_array(count($closes) - count($rsi)), $rsi);
		else return $this->blank_array(count($closes));
	}
	
	private function get_sma($closes, $period){
		$sma = Trader::sma($closes, $period);
		if ($sma) return array_merge($this->blank_array(count($closes) - count($sma)), $sma);
		else return $this->blank_array(count($closes));
	}
	
	private function get_stochastic($highs, $lows, $closes, $fk_period, $sk_period, $is_k_sma, $d_period, $is_d_sma){
		if ($is_k_sma) $k_ma = MovingAverageType::SMA; else $k_ma = MovingAverageType::EMA;
		if ($is_d_sma) $d_ma = MovingAverageType::SMA; else $d_ma = MovingAverageType::EMA;
		$stochastic =  Trader::stoch($highs, $lows, $closes, $fk_period, $sk_period, $k_ma, $d_period, $d_ma);
		if ($stochastic){
			$arr = $this->blank_array(count($highs) - count($stochastic["SlowK"]));
			$k = array_merge($arr, $stochastic["SlowK"]);
			$d = array_merge($arr, $stochastic["SlowD"]);
		}else $k = $d = $this->blank_array(count($closes));
		
		return array("k" => $k, "d" => $d);
	}
	
	private function get_trix($closes, $period, $period_signal){
		$trix = Trader::trix($closes, $period);
		if ($trix){
			$trix = array_merge($this->blank_array(count($closes) - count($trix)), $trix);
			$trix_signal = $this->get_sma($trix, $period_signal);
		}else $trix = $trix_signal = $this->blank_array(count($closes));
		
		return array("trix" => $trix, "trix_signal" => $trix_signal);
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
