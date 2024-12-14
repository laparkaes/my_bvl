<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use LupeCode\phpTraderNative\TALib\Enum\MovingAverageType;
use LupeCode\phpTraderNative\Trader;

class Load_bvl extends CI_Controller {

	public function __construct(){
		parent::__construct();
		set_time_limit(0);
		$this->start_time = microtime(true);
		$this->load->model('general_model','gen_m');
	}
	
	public function general(){
		echo $this->company()."<br/>";
		echo $this->today()."<br/>";
		echo $this->history()."<br/>";
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
			"today" => false,
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
	
	public function history(){
		$to = date('Y-m-d');
		$qty_new = 0;
		
		$data = [];
		$companies = $this->gen_m->all("history_counter");
		foreach($companies as $i_com => $com){
			$load_bvl = true;
			$today = $this->gen_m->filter("today", ["nemonico" => $com->nemonico]);
			if ($today){
				if ($today[0]->date_previous === null) $load_bvl = false;
				elseif ($com->max_date) if ($com->max_date === $today[0]->date_previous) $load_bvl = false;
			}
			
			if ($load_bvl){
				$from = $com->max_date ? date("Y-m-d", strtotime("+1 day", strtotime($com->max_date))) : "2000-01-01";
				
				$url = "https://dataondemand.bvl.com.pe/v1/issuers/stock/".$com->nemonico."?startDate=".$from."&endDate=".$to;
				$res = $this->exec_curl($url, null, false);
				
				foreach($res as $stock){
					unset($stock->id);
					if ($stock->quantityNegotiated) $data[] = (array) $stock;
				}
				
				if (count($data) > 5000){
					$qty_new += $this->gen_m->insert_multi("history", $data);
					$data = [];
				}
			}
			
			if ($i_com > 10) break;
		}
		
		if ($data) $qty_new += $this->gen_m->insert_multi("history", $data);
		
		echo number_format($qty_new)." stock history records inserted. (".number_format(microtime(true) - $this->start_time, 2)." sec)";
	}
	
	public function technical(){
		$nemonico = "ENGIEC1";
		
		$tech = $this->update_indicators($nemonico);
		if ($tech){
			$this->gen_m->delete("technical_analysis", ["nemonico" => $nemonico]);
			$qty_new = $this->gen_m->insert_multi("technical_analysis", $tech);
			if ($qty_new) echo $nemonico.": ".number_format($qty_new)." records (".$tech[0]["date"]." ~ ".$tech[count($tech)-1]["date"].").<br/>";
		}
		
		foreach($tech as $item){
			print_r($item); echo "<br/><br/>";
		}
	}


	//usado en: update_stocks_from_bvl
	public function update_indicators($nemonico){
		$histories = $this->gen_m->filter("history", ["nemonico" => $nemonico, "close > " => 0], null, null, [["date", "asc"]]);
		$today = $this->gen_m->unique("today", "nemonico", $nemonico);
		
		if ($today->open){
			if ($today->date_previous === $histories[count($histories)-1]->date){
				$record = $this->gen_m->structure("history");
				$record->nemonico = $today->nemonico;
				$record->date = date("Y-m-d", strtotime($today->date_created));
				$record->open = (property_exists($today, 'open')) ? $today->open : null;
				$record->close = (property_exists($today, 'close')) ? $today->close : null;
				$record->high = (property_exists($today, 'maximun')) ? $today->maximun : null;
				$record->low = (property_exists($today, 'minimun')) ? $today->minimun : null;
				$record->average = 0;//no use data
				$record->quantityNegotiated = (property_exists($today, 'nego_qty')) ? $today->nego_qty : null;
				$record->solAmountNegotiated = (property_exists($today, 'nego_amount_pen')) ? $today->nego_amount_pen : null;
				$record->dollarAmountNegotiated = (property_exists($today, 'nego_amount')) ? ($today->currency === "S/") ? $today->nego_amount / 3.8 : $today->nego_amount : null;
				$record->yesterday = $today->date_previous;
				$record->yesterdayClose = $today->previous;
				$record->currencySymbol = $today->currency;
				
				$histories[] = $record;
			}
		}
		
		$values = $this->calculate_indicators($histories);
		$analysis = $this->indicator_analysis($histories, $values);
		
		$tech = [];
		foreach($histories as $i => $s){
			$tech[] = [
				"nemonico"			=> $nemonico,
				"date" 				=> $values["dates"][$i],
				"adx" 				=> round($values["adx"]["adx"][$i], 3),
				"adx_pdi" 			=> round($values["adx"]["pdi"][$i], 3),
				"adx_mdi" 			=> round($values["adx"]["mdi"][$i], 3),
				"atr" 				=> round($values["atr"][$i], 3),
				"bb_u" 				=> round($values["bb"]["uppers"][$i], 3),
				"bb_m" 				=> round($values["bb"]["middles"][$i], 3),
				"bb_l" 				=> round($values["bb"]["lowers"][$i], 3),
				"cci" 				=> round($values["cci"][$i], 3),
				"ema_5" 			=> round($values["ema"]["ema_5"][$i], 3),
				"ema_20" 			=> round($values["ema"]["ema_20"][$i], 3),
				"ema_60" 			=> round($values["ema"]["ema_60"][$i], 3),
				"ema_120" 			=> round($values["ema"]["ema_120"][$i], 3),
				"ema_200" 			=> round($values["ema"]["ema_200"][$i], 3),
				"env_u" 			=> round($values["env"]["uppers"][$i], 3),
				"env_l" 			=> round($values["env"]["lowers"][$i], 3),
				"ich_a" 			=> round($values["ich"]["span_a"][$i], 3),
				"ich_b" 			=> round($values["ich"]["span_b"][$i], 3),
				"macd" 				=> round($values["macd"]["macd"][$i], 3),
				"macd_sig" 			=> round($values["macd"]["macd_sig"][$i], 3),
				"macd_div" 			=> round($values["macd"]["macd_div"][$i], 3),
				"mfi" 				=> round($values["mfi"][$i], 3),
				"mom" 				=> round($values["mom"]["mom"][$i], 3),
				"mom_sig" 			=> round($values["mom"]["mom_signal"][$i], 3),
				"psar" 				=> round($values["psar"][$i], 3),
				"pch_u" 			=> round($values["pch"]["uppers"][$i], 3),
				"pch_l" 			=> round($values["pch"]["lowers"][$i], 3),
				"ppo" 				=> round($values["ppo"][$i], 3),
				"rsi" 				=> round($values["rsi"][$i], 3),
				"sma_5" 			=> round($values["sma"]["sma_5"][$i], 3),
				"sma_20" 			=> round($values["sma"]["sma_20"][$i], 3),
				"sma_60" 			=> round($values["sma"]["sma_60"][$i], 3),
				"sma_120" 			=> round($values["sma"]["sma_120"][$i], 3),
				"sma_200" 			=> round($values["sma"]["sma_200"][$i], 3),
				"sto_k" 			=> round($values["sto"]["k"][$i], 3),
				"sto_d" 			=> round($values["sto"]["d"][$i], 3),
				"trix" 				=> round($values["trix"]["trix"][$i], 3),
				"trix_sig" 			=> round($values["trix"]["trix_signal"][$i], 3),
				"last_year_min"		=> $values["last_year"]["min"][$i],
				"last_year_max"		=> $values["last_year"]["max"][$i],
				"last_year_per"		=> $values["last_year"]["per"][$i],
				"buy_signal"		=> implode(",", $analysis["buy_signals"][$i]),
				"buy_signal_qty"	=> count($analysis["buy_signals"][$i]),
				"sell_signal" 		=> implode(",", $analysis["sell_signals"][$i]),
				"sell_signal_qty" 	=> count($analysis["sell_signals"][$i]),
				"jw_factor" 		=> round(abs($values["last_year"]["per"][$i] - 0.5) * (count($analysis["sell_signals"][$i]) - count($analysis["buy_signals"][$i])) / 5, 2),
			];
		}
		
		return $tech;
	}
	
	//usado en: update_indicators
	private function calculate_indicators($histories){
		$result = [];
		
		$dates = $closes = $highs = $lows = $negos = [];
		foreach($histories as $s){
			$dates[] = $s->date;
			$closes[] = $s->close;
			$highs[] = $s->high;
			$lows[] = $s->low;
			$negos[] = $s->quantityNegotiated;
		}
		
		$result["dates"] = $dates;
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
	private function indicator_analysis($histories, $result){
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
		foreach($histories as $i => $s){
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
				if (($psar[$i-1] >= $histories[$i-1]->close) and ($psar[$i] < $s->close)) $buy_signals[] = "psar";
				elseif (($psar[$i-1] <= $histories[$i-1]->close) and ($psar[$i] > $s->close)) $sell_signals[] = "psar";
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

	/* start analysis functions */
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
	/* end analysis */
	
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
