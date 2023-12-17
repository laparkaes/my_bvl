<div class="pagetitle mb-3">
	<div class="d-flex justify-content-between">
		<h1><?= $company->companyName ?></h1>
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
						<div class="dropdown">
							<button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Indicadores</button>
							<ul class="dropdown-menu">
								<li><button class="dropdown-item" type="button">Action</button></li>
								<li><button class="dropdown-item" type="button">Another action</button></li>
								<li><button class="dropdown-item" type="button">Something else here</button></li>
							</ul>
						</div>
					</div>
					<div id="chart_block" style="max-height: 300px; overflow: hidden;">
						<?php print_r($stocks); ?>
					</div>
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
							<?php foreach($stocks as $s){ $vp = $s->var_per; ?>
							<tr>
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