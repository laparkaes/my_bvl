<div class="pagetitle mb-3">
	<div class="d-flex justify-content-between">
		<h1><?= $company->companyName ?> <i class="bi bi-star<?= $ic_fav ?> ic_fav_control ic_fav_<?= $company->company_id ?>" value="<?= $company->company_id ?>"></i></h1>
		<h1>[<?= $company->stock ?>] [<?= $company->sector->sectorDescription ?>]</h1>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">Graficos</h5>
						<div>
							<div class="dropdown">
								<select class="form-select form-select-sm d-inline" id="chart_data_qty" style="width: 100px;">
									<option value="-100">100</option>
									<option value="-300" selected>300</option>
									<option value="-600">600</option>
									<option value="-1000">1000</option>
									<option value="-9999">Todos</option>
								</select>
								<a class="btn btn-success btn-sm" type="button" href="<?= base_url() ?>company/update_indicators/<?= $company->company_id ?>" target="_blank">Actualizar Indicadores</a>
								<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Elegir</button>
								<ul class="dropdown-menu">
									<li><button class="dropdown-item btn_chart active" type="button" value="ch_price_sma">Precio & SMA</button></li>
									<li><button class="dropdown-item btn_chart" type="button" value="ch_price_ema">Precio & EMA</button></li>
									<li><button class="dropdown-item btn_chart" type="button" value="ch_price_ly">Precio & Ult. Año</button></li>
									<li><button class="dropdown-item btn_chart" type="button" value="ch_indicators">Indicadores</button></li>
									<li><button class="dropdown-item btn_chart" type="button" value="ch_bands">Bandas</button></li>
								</ul>
							</div>
						</div>
					</div>
					<div id="chart_block"></div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between">
						<h5 class="card-title">Ultimo Movimiento</h5>
						<?php $vp = $last_stock->var_per; ?>
						<h5 class="card-title text-<?= $vp["color"] ?>"><?= $vp["ic"]." ".$vp["value"] ?>%</h5>
					</div>
					<div class="row g-3">
						<div class="col-md-4">
							<label class="form-label">Fecha</label>
							<div class="form-control"><?= $last_stock->date ?></div>
						</div>
						<div class="col-md-4">
							<label class="form-label">Precio</label>
							<div class="input-group">
								<span class="input-group-text"><?= $last_stock->currencySymbol ?></span>
								<div class="form-control"><?= $last_stock->close ?></div>
							</div>
						</div>
						<div class="col-md-4">
							<label class="form-label">#Nego.</label>
							<div class="form-control"><?= number_format($last_stock->quantityNegotiated) ?></div>
						</div>
						<div class="col-md-4">
							<label class="form-label">Fecha Anterior</label>
							<div class="form-control"><?= $last_stock->yesterday ?></div>
						</div>
						<div class="col-md-8">
							<label class="form-label">Precio Anterior</label>
							<div class="input-group">
								<span class="input-group-text"><?= $last_stock->currencySymbol ?></span>
								<div class="form-control"><?= $last_stock->yesterdayClose ?></div>
							</div>
						</div>
						<div class="col-md-6">
							<label class="form-label">Compra</label>
							<div class="input-group">
								<span class="input-group-text"><?= $last_stock->currencySymbol ?></span>
								<div class="form-control"><?= $offers["buy"] ?></div>
							</div>
						</div>
						<div class="col-md-6">
							<label class="form-label">Venta</label>
							<div class="input-group">
								<span class="input-group-text"><?= $last_stock->currencySymbol ?></span>
								<div class="form-control"><?= $offers["sell"] ?></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Memorias</h5>
					<div class="overflow-y-auto" style="max-height: 241px;">
						<table class="table">
							<thead>
								<tr>
									<th scope="col">Fecha</th>
									<th scope="col">Documento</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($memories as $m){ ?>
								<tr>
									<td><?= $m->date ?></td>
									<td><?= $m->document ?></td>
									<td>
										<a href="https://documents.bvl.com.pe<?= $m->path ?>" target="_blank">
											<i class="bi bi-file-earmark-pdf"></i>
										</a>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Historiales</h5>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">JW Factor</th>
								<th scope="col">Fecha</th>
								<th scope="col">Apertura</th>
								<th scope="col">Min</th>
								<th scope="col">Max</th>
								<th scope="col">Cierre</th>
								<th scope="col">Var%</th>
								<th scope="col">#Nego</th>
								<th scope="col">Anterior</th>
								<th scope="col">FechaAnt.</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$dates = $prices = $candles = $volumes = [];
							$sma_5 = $sma_20 = $sma_60 = $sma_120 = $sma_200 = [];
							$ema_5 = $ema_20 = $ema_60 = $ema_120 = $ema_200 = [];
							$last_year_min = $last_year_max = $last_year_per = [];
							$buy_sigs = $sell_sigs = [];
							$adx = $adx_pdi = $adx_mdi = [];
							$atr = [];
							$bb_u = []; $bb_m = []; $bb_l = [];
							$cci = [];
							$env_u = []; $env_l = [];
							$ich_a = []; $ich_b = [];
							$macd = []; $macd_sig = []; $macd_div = [];
							$mfi = [];
							$mom = []; $mom_sig = [];
							$psar = [];
							$pch_u = []; $pch_l = [];
							$ppo = [];
							$rsi = [];
							$sto_k = []; $sto_d = [];
							$trix = []; $trix_sig = [];
							foreach($stocks as $i => $s){ 
								$s->open = $s->open ? $s->open : null;
								$s->close = $s->close ? $s->close : null;
								$s->low = $s->low ? $s->low : null;
								$s->high = $s->high ? $s->high : null;
								$s->quantityNegotiated = $s->quantityNegotiated ? $s->quantityNegotiated : null;
							
								if ($s->close){
									$dates[] = $s->date;
									$prices[] = $s->close;
									$candles[] = [$s->open, $s->close, $s->low, $s->high, $s->quantityNegotiated];
									$volumes[] = [$i, $s->quantityNegotiated];
									$sma_5[] = ($s->sma_5 > 0) ? $s->sma_5 : null;
									$sma_20[] = ($s->sma_20 > 0) ? $s->sma_20 : null;
									$sma_60[] = ($s->sma_60 > 0) ? $s->sma_60 : null;
									$sma_120[] = ($s->sma_120 > 0) ? $s->sma_120 : null;
									$sma_200[] = ($s->sma_200 > 0) ? $s->sma_200 : null;
									$ema_5[] = ($s->ema_5 > 0) ? $s->ema_5 : null;
									$ema_20[] = ($s->ema_20 > 0) ? $s->ema_20 : null;
									$ema_60[] = ($s->ema_60 > 0) ? $s->ema_60 : null;
									$ema_120[] = ($s->ema_120 > 0) ? $s->ema_120 : null;
									$ema_200[] = ($s->ema_200 > 0) ? $s->ema_200 : null;
									$last_year_min[] = $s->last_year_min;
									$last_year_max[] = $s->last_year_max;
									$last_year_per[] = $s->last_year_per;
									$buy_sigs[] = $s->buy_signal_qty;
									$sell_sigs[] = $s->sell_signal_qty;
									$adx[] = $s->adx; $adx_pdi[] = $s->adx_pdi; $adx_mdi[] = $s->adx_mdi;
									$atr[] = $s->atr;
									$bb_u[] = $s->bb_u; $bb_m[] = $s->bb_m; $bb_l[] = $s->bb_l;
									$cci[] = $s->cci;
									$env_u[] = $s->env_u; $env_l[] = $s->env_l;
									$ich_a[] = $s->ich_a; $ich_b[] = $s->ich_b;
									$macd[] = $s->macd; $macd_sig[] = $s->macd_sig; $macd_div[] = $s->macd_div;
									$mfi[] = $s->mfi;
									$mom[] = $s->mom; $mom_sig[] = $s->mom_sig;
									$psar[] = $s->psar;
									$pch_u[] = $s->pch_u; $pch_l[] = $s->pch_l;
									$ppo[] = $s->ppo;
									$rsi[] = $s->rsi;
									$sto_k[] = $s->sto_k; $sto_d[] = $s->sto_d;
									$trix[] = $s->trix; $trix_sig[] = $s->trix_sig;
								}
								
								$vp = $s->var_per;
							?>
							<tr>
								<td><i class="bi bi-circle-fill text-<?= $s->color ?>" style="--bs-text-opacity: <?= $s->opacity ?>;"></i><?= ($s->close > 0) ? number_format($s->jw_factor, 2) : "" ?><?= ($s->opacity == 1) ? " xx" : "" ?></td>
								<td><?= $s->date ?></td>
								<td><?= ($s->open > 0) ? $s->currencySymbol." ".$s->open : "" ?></td>
								<td><?= ($s->low > 0) ? $s->currencySymbol." ".$s->low : "" ?></td>
								<td><?= ($s->high > 0) ? $s->currencySymbol." ".$s->high : "" ?></td>
								<td><strong><?= ($s->close > 0) ? $s->currencySymbol." ".$s->close : "" ?></strong></td>
								<td>
									<span class="text-<?= $vp["color"] ?>">
										<?= ($vp["value"] != 0) ? '<i class="bi bi-caret-'.$vp["bi"].'-fill"></i> '.$vp["value"]."%" : ""; ?>
									</span>
								</td>
								<td><?= number_format($s->quantityNegotiated) ?></td>
								<td><?= $s->currencySymbol." ".$s->yesterdayClose ?></td>
								<td><?= $s->yesterday ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
<div class="d-none">
	<div id="ch_dates"><?= json_encode(array_reverse($dates)) ?></div>
	<div id="ch_prices"><?= json_encode(array_reverse($prices)) ?></div>
	<div id="ch_candles"><?= json_encode(array_reverse($candles)) ?></div>
	<div id="ch_volumes"><?= json_encode(array_reverse($volumes)) ?></div>
	<div id="ch_sma_5"><?= json_encode(array_reverse($sma_5)) ?></div>
	<div id="ch_sma_20"><?= json_encode(array_reverse($sma_20)) ?></div>
	<div id="ch_sma_60"><?= json_encode(array_reverse($sma_60)) ?></div>
	<div id="ch_sma_120"><?= json_encode(array_reverse($sma_120)) ?></div>
	<div id="ch_sma_200"><?= json_encode(array_reverse($sma_200)) ?></div>
	<div id="ch_ema_5"><?= json_encode(array_reverse($ema_5)) ?></div>
	<div id="ch_ema_20"><?= json_encode(array_reverse($ema_20)) ?></div>
	<div id="ch_ema_60"><?= json_encode(array_reverse($ema_60)) ?></div>
	<div id="ch_ema_120"><?= json_encode(array_reverse($ema_120)) ?></div>
	<div id="ch_ema_200"><?= json_encode(array_reverse($ema_200)) ?></div>
	<div id="ch_last_year_min"><?= json_encode(array_reverse($last_year_min)) ?></div>
	<div id="ch_last_year_max"><?= json_encode(array_reverse($last_year_max)) ?></div>
	<div id="ch_last_year_per"><?= json_encode(array_reverse($last_year_per)) ?></div>
	<div id="ch_buy_sigs"><?= json_encode(array_reverse($buy_sigs)) ?></div>
	<div id="ch_sell_sigs"><?= json_encode(array_reverse($sell_sigs)) ?></div>
	<div id="ch_adx"><?= json_encode(array_reverse($adx)) ?></div>
	<div id="ch_adx_pdi"><?= json_encode(array_reverse($adx_pdi)) ?></div>
	<div id="ch_adx_mdi"><?= json_encode(array_reverse($adx_mdi)) ?></div>
	<div id="ch_atr"><?= json_encode(array_reverse($atr)) ?></div>
	<div id="ch_bb_u"><?= json_encode(array_reverse($bb_u)) ?></div>
	<div id="ch_bb_m"><?= json_encode(array_reverse($bb_m)) ?></div>
	<div id="ch_bb_l"><?= json_encode(array_reverse($bb_l)) ?></div>
	<div id="ch_cci"><?= json_encode(array_reverse($cci)) ?></div>
	<div id="ch_env_u"><?= json_encode(array_reverse($env_u)) ?></div>
	<div id="ch_env_l"><?= json_encode(array_reverse($env_l)) ?></div>
	<div id="ch_ich_a"><?= json_encode(array_reverse($ich_a)) ?></div>
	<div id="ch_ich_b"><?= json_encode(array_reverse($ich_b)) ?></div>
	<div id="ch_macd"><?= json_encode(array_reverse($macd)) ?></div>
	<div id="ch_macd_sig"><?= json_encode(array_reverse($macd_sig)) ?></div>
	<div id="ch_macd_div"><?= json_encode(array_reverse($macd_div)) ?></div>
	<div id="ch_mfi"><?= json_encode(array_reverse($mfi)) ?></div>
	<div id="ch_mom"><?= json_encode(array_reverse($mom)) ?></div>
	<div id="ch_mom_sig"><?= json_encode(array_reverse($mom_sig)) ?></div>
	<div id="ch_psar"><?= json_encode(array_reverse($psar)) ?></div>
	<div id="ch_pch_u"><?= json_encode(array_reverse($pch_u)) ?></div>
	<div id="ch_pch_l"><?= json_encode(array_reverse($pch_l)) ?></div>
	<div id="ch_ppo"><?= json_encode(array_reverse($ppo)) ?></div>
	<div id="ch_rsi"><?= json_encode(array_reverse($rsi)) ?></div>
	<div id="ch_sto_k"><?= json_encode(array_reverse($sto_k)) ?></div>
	<div id="ch_sto_d"><?= json_encode(array_reverse($sto_d)) ?></div>
	<div id="ch_trix"><?= json_encode(array_reverse($trix)) ?></div>
	<div id="ch_trix_sig"><?= json_encode(array_reverse($trix_sig)) ?></div>
</div>