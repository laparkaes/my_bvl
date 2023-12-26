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
			$offers["buy"] = (property_exists($last_stock, 'buy')) ? $last_stock->buy : "";
			$offers["sell"] = (property_exists($last_stock, 'sell')) ? $last_stock->sell : "";
			if (strtotime($last_stock->createdDate) > strtotime($stocks[0]->date)){
				if (property_exists($last_stock, 'previousDate')){
					if (strtotime($last_stock->previousDate) > strtotime($stocks[0]->date)){
						//update all stock records
						if ($last_stock) $from = $stocks[0]->date; else $from = "1999-01-01";
						$this->update_stocks_from_bvl($company->stock, $from);
						//update indicators if there is new record
						if ($this->gm->filter("stock", ["nemonico" => $company->stock, "is_calculated" => 0, "close >" => 0]))
							$this->update_indicators($company->company_id);
						
						$stocks = $this->gm->filter("stock", ["nemonico" => $company->stock], null, null, [["date", "desc"]]);
					}
					
					$last_stock = $this->convert_today_to_record($last_stock);
					if ($last_stock->close) array_unshift($stocks, $last_stock);
					else $last_stock = clone $stocks[0];
					
					if (!$last_stock->is_calculated){
						$stocks_aux = array_reverse($this->gm->filter("stock", ["nemonico" => $company->stock, "close >" => 0], null, null, [["date", "desc"]], 500, 0));//today value has 499 as index
						
						$stocks_aux[] = $last_stock;
						$result = $this->calculate_indicators($stocks_aux);
						$result_a = $this->indicator_analysis($stocks_aux, $result);
						$last_i = count($stocks_aux) - 1;
						
						
						//assign all indicators to $last_stock
						$last_stock->is_calculated = true;
						$last_stock->adx = round($result["adx"]["adx"][$last_i], 3);
						$last_stock->adx_pdi = round($result["adx"]["pdi"][$last_i], 3);
						$last_stock->adx_mdi = round($result["adx"]["mdi"][$last_i], 3);
						$last_stock->atr = round($result["atr"][$last_i], 3);
						$last_stock->bb_u = round($result["bb"]["uppers"][$last_i], 3);
						$last_stock->bb_m = round($result["bb"]["middles"][$last_i], 3);
						$last_stock->bb_l = round($result["bb"]["lowers"][$last_i], 3);
						$last_stock->cci = round($result["cci"][$last_i], 3);
						$last_stock->ema_5 = round($result["ema"]["ema_5"][$last_i], 3);
						$last_stock->ema_20 = round($result["ema"]["ema_20"][$last_i], 3);
						$last_stock->ema_60 = round($result["ema"]["ema_60"][$last_i], 3);
						$last_stock->ema_120 = round($result["ema"]["ema_120"][$last_i], 3);
						$last_stock->ema_200 = round($result["ema"]["ema_200"][$last_i], 3);
						$last_stock->env_u = round($result["env"]["uppers"][$last_i], 3);
						$last_stock->env_l = round($result["env"]["lowers"][$last_i], 3);
						$last_stock->ich_a = round($result["ich"]["span_a"][$last_i], 3);
						$last_stock->ich_b = round($result["ich"]["span_b"][$last_i], 3);
						$last_stock->macd = round($result["macd"]["macd"][$last_i], 3);
						$last_stock->macd_sig = round($result["macd"]["macd_sig"][$last_i], 3);
						$last_stock->macd_div = round($result["macd"]["macd_div"][$last_i], 3);
						$last_stock->mfi = round($result["mfi"][$last_i], 3);
						$last_stock->mom = round($result["mom"]["mom"][$last_i], 3);
						$last_stock->mom_sig = round($result["mom"]["mom_signal"][$last_i], 3);
						$last_stock->psar = round($result["psar"][$last_i], 3);
						$last_stock->pch_u = round($result["pch"]["uppers"][$last_i], 3);
						$last_stock->pch_l = round($result["pch"]["lowers"][$last_i], 3);
						$last_stock->ppo = round($result["ppo"][$last_i], 3);
						$last_stock->rsi = round($result["rsi"][$last_i], 3);
						$last_stock->sma_5 = round($result["sma"]["sma_5"][$last_i], 3);
						$last_stock->sma_20 = round($result["sma"]["sma_20"][$last_i], 3);
						$last_stock->sma_60 = round($result["sma"]["sma_60"][$last_i], 3);
						$last_stock->sma_120 = round($result["sma"]["sma_120"][$last_i], 3);
						$last_stock->sma_200 = round($result["sma"]["sma_200"][$last_i], 3);
						$last_stock->sto_k = round($result["sto"]["k"][$last_i], 3);
						$last_stock->sto_d = round($result["sto"]["d"][$last_i], 3);
						$last_stock->trix = round($result["trix"]["trix"][$last_i], 3);
						$last_stock->trix_sig = round($result["trix"]["trix_signal"][$last_i], 3);
						$last_stock->last_year_min = $result["last_year"]["min"][$last_i];
						$last_stock->last_year_max = $result["last_year"]["max"][$last_i];
						$last_stock->last_year_per = $result["last_year"]["per"][$last_i];
						$last_stock->buy_signal = $result_a["buy_signals"][$last_i];
						$last_stock->buy_signal_qty = count($last_stock->buy_signal);
						$last_stock->sell_signal = $result_a["sell_signals"][$last_i];
						$last_stock->sell_signal_qty = count($last_stock->sell_signal);
					}	
				}else $last_stock = clone $stocks[0];
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
			"ic_fav" => $this->gm->filter("favorite", ["company_id" => $company->company_id]) ? "-fill" : "",
			"main" => "company/detail",
		];
		$this->load->view('layout', $data);
	}
	
	//usado en: detail
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
	
	//usado en: detail
	private function convert_today_to_record($today){
		$record = $this->gm->structure("stock");
		$record->stock_id = 0;
		$record->nemonico = $today->nemonico;
		$record->date = date("Y-m-d", strtotime($today->createdDate));
		$record->open = (property_exists($today, 'opening')) ? $today->opening : null;
		$record->close = (property_exists($today, 'last')) ? $today->last : null;
		$record->high = (property_exists($today, 'maximun')) ? $today->maximun : null;
		$record->low = (property_exists($today, 'minimun')) ? $today->minimun : null;
		$record->average = 0;//no use data
		$record->quantityNegotiated = (property_exists($today, 'negotiatedQuantity')) ? $today->negotiatedQuantity : null;
		$record->solAmountNegotiated = (property_exists($today, 'negotiatedNationalAmount')) ? $today->negotiatedNationalAmount : null;
		$record->dollarAmountNegotiated = (property_exists($today, 'negotiatedAmount')) ? ($today->currency === "S/") ? $today->negotiatedAmount / 3.8 : $today->negotiatedAmount : null;
		$record->yesterday = $today->previousDate;
		$record->yesterdayClose = $today->previous;
		$record->currencySymbol = $today->currency;
		
		return $record;
	}
	
	//usado en: home/index, company/detail
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
	
	//usado en: update_stocks_from_bvl
	public function update_indicators($stock){
		$stocks = $this->gm->filter("stock", ["nemonico" => $stock, "close > " => 0], null, null, [["date", "asc"]]);
		$result = $this->calculate_indicators($stocks);
		$result_a = $this->indicator_analysis($stocks, $result);
		
		$indicators = [];
		foreach($stocks as $i => $s){
			if (!$s->is_calculated){
				$indicators[] = [
					"stock_id" => $s->stock_id,
					"is_calculated" => true,
					"adx" => round($result["adx"]["adx"][$i], 3),
					"adx_pdi" => round($result["adx"]["pdi"][$i], 3),
					"adx_mdi" => round($result["adx"]["mdi"][$i], 3),
					"atr" => round($result["atr"][$i], 3),
					"bb_u" => round($result["bb"]["uppers"][$i], 3),
					"bb_m" => round($result["bb"]["middles"][$i], 3),
					"bb_l" => round($result["bb"]["lowers"][$i], 3),
					"cci" => round($result["cci"][$i], 3),
					"ema_5" => round($result["ema"]["ema_5"][$i], 3),
					"ema_20" => round($result["ema"]["ema_20"][$i], 3),
					"ema_60" => round($result["ema"]["ema_60"][$i], 3),
					"ema_120" => round($result["ema"]["ema_120"][$i], 3),
					"ema_200" => round($result["ema"]["ema_200"][$i], 3),
					"env_u" => round($result["env"]["uppers"][$i], 3),
					"env_l" => round($result["env"]["lowers"][$i], 3),
					"ich_a" => round($result["ich"]["span_a"][$i], 3),
					"ich_b" => round($result["ich"]["span_b"][$i], 3),
					"macd" => round($result["macd"]["macd"][$i], 3),
					"macd_sig" => round($result["macd"]["macd_sig"][$i], 3),
					"macd_div" => round($result["macd"]["macd_div"][$i], 3),
					"mfi" => round($result["mfi"][$i], 3),
					"mom" => round($result["mom"]["mom"][$i], 3),
					"mom_sig" => round($result["mom"]["mom_signal"][$i], 3),
					"psar" => round($result["psar"][$i], 3),
					"pch_u" => round($result["pch"]["uppers"][$i], 3),
					"pch_l" => round($result["pch"]["lowers"][$i], 3),
					"ppo" => round($result["ppo"][$i], 3),
					"rsi" => round($result["rsi"][$i], 3),
					"sma_5" => round($result["sma"]["sma_5"][$i], 3),
					"sma_20" => round($result["sma"]["sma_20"][$i], 3),
					"sma_60" => round($result["sma"]["sma_60"][$i], 3),
					"sma_120" => round($result["sma"]["sma_120"][$i], 3),
					"sma_200" => round($result["sma"]["sma_200"][$i], 3),
					"sto_k" => round($result["sto"]["k"][$i], 3),
					"sto_d" => round($result["sto"]["d"][$i], 3),
					"trix" => round($result["trix"]["trix"][$i], 3),
					"trix_sig" => round($result["trix"]["trix_signal"][$i], 3),
					"last_year_min" => $result["last_year"]["min"][$i],
					"last_year_max" => $result["last_year"]["max"][$i],
					"last_year_per" => $result["last_year"]["per"][$i],
					"buy_signal" => implode(",", $result_a["buy_signals"][$i]),
					"buy_signal_qty" => count($result_a["buy_signals"][$i]),
					"sell_signal" => implode(",", $result_a["sell_signals"][$i]),
					"sell_signal_qty" => count($result_a["sell_signals"][$i]),
				];
			}
		}
		
		if ($indicators) $this->gm->update_multi("stock", $indicators, "stock_id");
	}
	
	//usado en: update_indicators
	private function calculate_indicators($stocks){
		$result = [];
		
		$dates = $closes = $highs = $lows = $negos = [];
		foreach($stocks as $s){
			$dates[] = $s->date;
			$closes[] = $s->close;
			$highs[] = $s->high;
			$lows[] = $s->low;
			$negos[] = $s->quantityNegotiated;
		}
		
		$result["adx"] = $this->get_adx($highs, $lows, $closes, 14);//adx
		$result["atr"] = $this->get_atr($highs, $lows, $closes, 14);//atr
		$result["bb"] = $this->get_bollinger($closes, 20, 2, 2, true);//bollinger band
		$result["cci"] = $this->get_cci($highs, $lows, $closes, 20);//cci
		$result["ema"] = [
			"ema_5" => $this->get_ema($closes, 5),
			"ema_20" => $this->get_ema($closes, 20),
			"ema_60" => $this->get_ema($closes, 60),
			"ema_120" => $this->get_ema($closes, 120),
			"ema_200" => $this->get_ema($closes, 200),
		];//ema
		$result["env"] = $this->get_envelope($closes, 20, 0.15);//envelope
		$result["ich"] = $this->get_icloud($closes);//ichomoku cloud
		$result["macd"] = $this->get_macd($closes, 12, 26, 9);//macd
		$result["mfi"] = $this->get_mfi($highs, $lows, $closes, $negos, 14);//mfi
		$result["mom"] = $this->get_mom($closes, 10, 9);//mom
		$result["psar"] = $this->get_parabolic_sar($highs, $lows, 0.02, 0.2);//parabolic sar
		$result["pch"] = $this->get_price_channel($highs, $lows, 20);//price channel
		$result["ppo"] = $this->get_ppo($closes, 9, 20, true);//ppo
		$result["rsi"] = $this->get_rsi($closes, 20);//rsi
		$result["sma"] = [
			"sma_5" => $this->get_sma($closes, 5),
			"sma_20" => $this->get_sma($closes, 20),
			"sma_60" => $this->get_sma($closes, 60),
			"sma_120" => $this->get_sma($closes, 120),
			"sma_200" => $this->get_sma($closes, 200),
		];//sma
		$result["sto"] = $this->get_stochastic($highs, $lows, $closes, 6, 10, true, 6, true);//stochastic
		$result["trix"] = $this->get_trix($closes, 12, 9);//trix
		$result["last_year"] = $this->get_last_year($dates, $closes);//last_year
		
		return $result;
	}
	
	//usado en: update_indicators
	private function indicator_analysis($stocks, $result){
		$adx = $result["adx"]["adx"];
		$adx_pdi = $result["adx"]["pdi"];
		$adx_mdi = $result["adx"]["mdi"];
		
		$bb_u = $result["bb"]["uppers"];
		$bb_m = $result["bb"]["middles"];
		$bb_l = $result["bb"]["lowers"];
		
		$cci = $result["cci"];

		$env_u = $result["env"]["uppers"];
		$env_l = $result["env"]["lowers"];

		$ich_a = $result["ich"]["span_a"];
		$ich_b = $result["ich"]["span_b"];

		$macd = $result["macd"]["macd"];
		$macd_sig = $result["macd"]["macd_sig"];
		$macd_div = $result["macd"]["macd_div"];

		$mfi = $result["mfi"];

		$mom = $result["mom"]["mom"];
		$mom_sig = $result["mom"]["mom_signal"];

		$psar = $result["psar"];

		$pch_u = $result["pch"]["uppers"];
		$pch_l = $result["pch"]["lowers"];

		$ppo = $result["ppo"];

		$rsi= $result["rsi"];

		$sto_k = $result["sto"]["k"];
		$sto_d = $result["sto"]["d"];

		$trix = $result["trix"]["trix"];
		$trix_sig = $result["trix"]["trix_signal"];
		
		$buy_signals_all = $sell_signals_all = [];
		foreach($stocks as $i => $s){
			$buy_signals = $sell_signals = [];
			
			//adx
			if (($adx[$i] > $adx_pdi[$i]) and ($adx_mdi[$i] > $adx_pdi[$i])) $buy_signals[] = "adx";
			elseif (($adx[$i] > $adx_mdi[$i]) and ($adx_pdi[$i] > $adx_mdi[$i])) $sell_signals[] = "adx";
			
			//atr => solo da que tanto varia precio. no da senial de compra o venta
			
			//bb
			if ((0 < $bb_l[$i]) and ($bb_l[$i] >= $s->close)) $buy_signals[] = "bb";
			elseif ((0 < $bb_u[$i]) and ($bb_u[$i] <= $s->close)) $sell_signals[] = "bb";
			
			//cci
			if ($cci[$i] < -100) $buy_signals[] = "cci";
			elseif ($cci[$i] > 100) $sell_signals[] = "cci";
			
			//ema => solo para grafico
			
			//env
			if ($env_l[$i] >= $s->close) $buy_signals[] = "env";
			elseif ($env_u[$i] <= $s->close) $sell_signals[] = "env";
			
			//macd
			if (0 < $i){
				if (($macd_div[$i] < 0) and ($macd_div[$i-1] < $macd_div[$i])) $buy_signals[] = "macd";
				elseif (($macd_div[$i] > 0) and ($macd_div[$i-1] > $macd_div[$i])) $sell_signals[] = "macd";
			}
			
			//mfi
			if ($mfi[$i] < 20) $buy_signals[] = "mfi";
			elseif ($mfi[$i] > 80) $sell_signals[] = "mfi";
			
			//mom
			if ($this->is_golden_cross($mom, $mom_sig, $i)) $buy_signals[] = "mom";
			elseif ($this->is_dead_cross($mom, $mom_sig, $i)) $sell_signals[] = "mom";
			
			//psar
			if ($i > 0){
				if (($psar[$i-1] >= $stocks[$i-1]->close) and ($psar[$i] < $s->close)) $buy_signals[] = "psar";
				elseif (($psar[$i-1] <= $stocks[$i-1]->close) and ($psar[$i] > $s->close)) $sell_signals[] = "psar";
			}				
			//ppo
			if ($ppo[$i] < 0) $buy_signals[] = "ppo";
			elseif ($ppo[$i] > 0) $sell_signals[] = "ppo";
			
			//pch
			if ($pch_l[$i] >= $s->close) $buy_signals[] = "pch";
			elseif ($pch_u[$i] <= $s->close) $sell_signals[] = "pch";
			
			//rsi
			if ($rsi[$i] < 30) $buy_signals[] = "rsi";
			elseif ($rsi[$i] > 70) $sell_signals[] = "rsi";
			
			//sma => solo para grafico
			
			//sto
			if (($sto_k[$i] < 20) and ($sto_d[$i] < 20)) $buy_signals[] = "sto";
			elseif (($sto_k[$i] > 80) and ($sto_d[$i] > 80)) $sell_signals[] = "sto";
			
			//trix
			if ($this->is_golden_cross($trix, $trix_sig, $i)) $buy_signals[] = "trix";
			elseif ($this->is_dead_cross($trix, $trix_sig, $i)) $sell_signals[] = "trix";
			
			$buy_signals_all[$i] = $buy_signals;
			$sell_signals_all[$i] = $sell_signals;
		}
		
		return ["buy_signals" => $buy_signals_all, "sell_signals" =>$sell_signals_all];
	}
	
	//usado en: update_stock
	private function update_stocks_from_bvl($code, $from = "", $to = ""){
		//1. preparacion de los datos iniciales
		if (!$from) $from = "2000-01-01";
		if (!$to) $to = date('Y-m-d');
		
		$code = str_replace("/", "%2F", $code);
		$from_history = date('Y-m-d', strtotime($from));
		$to_history = date('Y-m-d', strtotime($to));
		
		//2. cargar registros desde bvl
		$datas = [];
		$url = "https://dataondemand.bvl.com.pe/v1/issuers/stock/".$code."?startDate=".$from_history."&endDate=".$to_history;
		$res = $this->exec_curl($url, null, false);
		if ($res) foreach($res as $item){
			if ($item->quantityNegotiated or $item->close){
				if (!trim($item->currencySymbol)) $item->currencySymbol = "S/"; else $item->currencySymbol = "US$";
				$datas[] = $item;
			}
		}
		
		//3. ordenar por fecha en orden ascendiente
		usort($datas, function($a, $b){ return $a->date < $b->date; });
		
		//4. obtener ultima fecha de registro en DB
		$last_stock = $this->gm->filter("stock", ["nemonico" => $code], null, null, [["date", "desc"]], 1, 0);
		if ($last_stock) $last_date = $last_stock[0]->date; else $last_date = "1999-01-01";
		$last_date = strtotime($last_date);
		
		//5. preparar un arreglo con datos no duplicados
		$new_records = [];
		foreach($datas as $d){
			if ($last_date < strtotime($d->date)){
				unset($d->id);
				$new_records[] = $d;
			}
		}
		
		//6. insertar a la base de datos
		$qty = $this->gm->insert_multi("stock", $new_records);
		
		//7. actualizar cantidad de registros total, de este y de ultimo anio
		$this_year = date("Y");
		$this_year_f = ["nemonico" => $code, "date >=" => $this_year."-01-01", "date <=" => $this_year."-12-31"];
		
		$last_year = $this_year - 1; 
		$last_year_f = ["nemonico" => $code, "date >=" => $last_year."-01-01", "date <=" => $last_year."-12-31"];
		
		$data = [
			"qty_total" => $this->gm->qty("stock", ["nemonico" => $code]),
			"qty_this_year" => $this->gm->qty("stock", $this_year_f),
			"qty_last_year" => $this->gm->qty("stock", $last_year_f),
		];
		
		$this->gm->update("company", ["stock" => $code], $data);
		
		//8. actualizar los indicadores
		$this->update_indicators($code);
		
		//9. retornar cantidad de nuevos registros
		return $qty;
	}
	
	//usado en: home/index_update
	public function update_stock(){
		$data = $this->input->post();
		$qty = $this->update_stocks_from_bvl($data["stock"], $data["date"]);
		
		echo ($data["stock"]." (".number_format($qty).")");
	}
	
	//usado en: update_stocks_from_bvl
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

	/* stock functions*/
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
	
	private function get_macd($closes, $fast_period = 12, $slow_period = 26, $signal_period = 9){
		$macd_general = Trader::macd($closes, $fast_period, $slow_period, $signal_period);
		if ($macd_general){
			$arr = $this->blank_array(count($closes) - count($macd_general["MACD"]));
			$macd = array_merge($arr, $macd_general["MACD"]);
			$macd_signal = array_merge($arr, $macd_general["MACDSignal"]);
			$macd_divergence = array_merge($arr, $macd_general["MACDHist"]);
		}else $macd = $macd_signal = $macd_divergence = $this->blank_array(count($closes));
		
		return ["macd" => $macd, "macd_sig" => $macd_signal, "macd_div" => $macd_divergence];
	}
	
	private function get_mfi($highs, $lows, $closes, $negos, $period = 14){
		$mfi = Trader::mfi($highs, $lows, $closes, $negos, $period);
		if ($mfi) return array_merge($this->blank_array(count($closes) - count($mfi)), $mfi);
		else return $this->blank_array(count($closes));
	}
	
	private function get_mom($closes, $period = 10, $period_signal = 9){
		$mom = Trader::mom($closes, $period);
		if ($mom){
			$mom = array_merge($this->blank_array(count($closes) - count($mom)), $mom);
			$mom_signal = $this->get_sma($mom, $period_signal);
		}else $mom = $mom_signal = $this->blank_array(count($closes));
		
		return ["mom" => $mom, "mom_signal" => $mom_signal];
	}
	
	private function get_parabolic_sar($highs, $lows, $acceleration = 0.02, $maximum = 0.2){
		$parabolic_sar = Trader::sar($highs, $lows, $acceleration, $maximum);
		if ($parabolic_sar) return array_merge($this->blank_array(count($highs) - count($parabolic_sar)), $parabolic_sar);
		else return $this->blank_array(count($highs));
	}
	
	private function get_price_channel($highs, $lows, $period = 20){
		$uppers = Trader::max($highs, $period);
		$lowers = Trader::min($lows, $period);
		if ($uppers){
			$arr = $this->blank_array(count($highs) - count($uppers));
			$uppers = array_merge($arr, $uppers);
			$lowers = array_merge($arr, $lowers);
		}else $uppers = $lowers = $this->blank_array(count($highs));
		
		return ["uppers" => $uppers, "lowers" => $lowers];
	}
	
	private function get_ppo($closes, $fast_period = 9, $slow_period = 20, $is_sma = true){
		if ($is_sma) $ma = MovingAverageType::SMA; else $ma = MovingAverageType::EMA;
		$ppo = Trader::ppo($closes, $fast_period, $slow_period, $ma);
		if ($ppo) return array_merge($this->blank_array(count($closes) - count($ppo)), $ppo);
		else return $this->blank_array(count($closes));
	}
	
	private function get_rsi($closes, $period = 20){
		$rsi = Trader::rsi($closes, $period);
		if ($rsi) return array_merge($this->blank_array(count($closes) - count($rsi)), $rsi);
		else return $this->blank_array(count($closes));
	}
	
	private function get_sma($closes, $period){
		$sma = Trader::sma($closes, $period);
		if ($sma) return array_merge($this->blank_array(count($closes) - count($sma)), $sma);
		else return $this->blank_array(count($closes));
	}
	
	private function get_stochastic($highs, $lows, $closes, $fk_period = 6, $sk_period = 10, $is_k_sma = true, $d_period = 6, $is_d_sma = true){
		if ($is_k_sma) $k_ma = MovingAverageType::SMA; else $k_ma = MovingAverageType::EMA;
		if ($is_d_sma) $d_ma = MovingAverageType::SMA; else $d_ma = MovingAverageType::EMA;
		$stochastic =  Trader::stoch($highs, $lows, $closes, $fk_period, $sk_period, $k_ma, $d_period, $d_ma);
		if ($stochastic){
			$arr = $this->blank_array(count($highs) - count($stochastic["SlowK"]));
			$k = array_merge($arr, $stochastic["SlowK"]);
			$d = array_merge($arr, $stochastic["SlowD"]);
		}else $k = $d = $this->blank_array(count($closes));
		
		return ["k" => $k, "d" => $d];
	}
	
	private function get_trix($closes, $period = 12, $period_signal = 9){
		$trix = Trader::trix($closes, $period);
		if ($trix){
			$trix = array_merge($this->blank_array(count($closes) - count($trix)), $trix);
			$trix_signal = $this->get_sma($trix, $period_signal);
		}else $trix = $trix_signal = $this->blank_array(count($closes));
		
		return ["trix" => $trix, "trix_signal" => $trix_signal];
	}
	
	private function get_last_year($dates, $closes){
		$mins = $maxs = $pers = [];
		foreach($closes as $i => $close){
			$min = $max = $close;
			
			$j = $i;//start from
			$limit = strtotime(date('Y-m-d', strtotime($dates[$i].' -1 year')));//limit date to last year
			while((0 <= $j) and ($limit <= strtotime($dates[$j]))){
				if ($max < $closes[$j]) $max = $closes[$j];
				if ($min > $closes[$j]) $min = $closes[$j];
				$j--;
			}
			
			$mins[$i] = $min;
			$maxs[$i] = $max;
			$pers[$i] = (($max - $min) > 0) ? round((($close - $min) / ($max - $min)), 2) : null;
		}
		
		return ["min" => $mins, "max" => $maxs, "per" => $pers];
	}	
	
	private function is_golden_cross($ind1, $ind2, $i){
		if ($i > 0) return (($ind1[$i - 1] <= $ind2[$i - 1]) and ($ind1[$i] > $ind2[$i]));
		else return false;
	}
	
	private function is_dead_cross($ind1, $ind2, $i){
		if ($i > 0) return (($ind1[$i - 1] >= $ind2[$i - 1]) and ($ind1[$i] < $ind2[$i]));
		else return false;
	}
}
