<div class="pagetitle mb-3">
	<div class="d-flex justify-content-between">
		<h1><?= date("Y-m-d") ?></h1>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<?php
			$error_msgs = $this->session->flashdata('error_msgs');
			if ($error_msgs) foreach($error_msgs as $msg){ ?>
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
					<i class="bi bi-exclamation-octagon me-1"></i>
					<?= $msg ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			<?php } ?>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Favoritos</h5>
					<div class="table-responsive">
						<table class="table" style="font-size: 11px;">
							<thead>
								<tr>
									<th scope="col"></th>
									<th scope="col" style="width: 280px;">Empresa</th>
									<th scope="col" style="width: 110px;">Sector</th>
									<th scope="col">Nemonico</th>
									<th scope="col">#Registro</th>
									<th scope="col">Var%</th>
									<th scope="col">Moneda</th>
									<th scope="col">Compra</th>
									<th scope="col">Venta</th>
									<th scope="col">Apert.</th>
									<th scope="col">Ult</th>
									<th scope="col">Anterior</th>
									<th scope="col">FechaAnt.</th>
									<th scope="col">#Nego.</th>
									<th scope="col">Ult.Hora</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($companies as $c){ if (in_array($c->data->company_id, $favorites)){ ?>
								<tr class="table-<?= $c->row_color ?>">
									<td>
										<i class="bi bi-star<?= (in_array($c->data->company_id, $favorites) ? "-fill" : "") ?> ic_fav_control ic_fav_<?= $c->data->company_id ?>" value="<?= $c->data->company_id ?>"></i>
									</td>
									<td>
										<a href="<?= base_url() ?>company/detail/<?= $c->data->company_id ?>">
											<?= $c->companyName ?>
										</a>
									</td>
									<td><?= $c->sectorDescription ?></td>
									<td><?= $c->nemonico ?></td>
									<td><?= number_format($c->data->qty_total) ?></td>
									<td class="text-<?= $c->color ?>"><?= $c->percentageChange ?>%</td>
									<td><?= $c->currency ?></td>
									<td><?= $c->buy ?></td>
									<td><?= $c->sell ?></td>
									<td><?= $c->opening ?></td>
									<td><strong><?= $c->last ?></strong></td>
									<td><strong><?= $c->previous ?></strong></td>
									<td><?= $c->previousDate ?></td>
									<td><?= number_format($c->negotiatedQuantity) ?></td>
									<td><?= date("H:m:s", strtotime($c->lastDate)) ?></td>
								</tr>
								<?php }} ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">General</h5>
					<div class="table-responsive">
						<table class="table" style="font-size: 11px;">
							<thead>
								<tr>
									<th scope="col"></th>
									<th scope="col" style="width: 280px;">Empresa</th>
									<th scope="col" style="width: 110px;">Sector</th>
									<th scope="col">Nemonico</th>
									<th scope="col">#Registro</th>
									<th scope="col">Var%</th>
									<th scope="col">Moneda</th>
									<th scope="col">Compra</th>
									<th scope="col">Venta</th>
									<th scope="col">Apert.</th>
									<th scope="col">Ult</th>
									<th scope="col">Anterior</th>
									<th scope="col">FechaAnt.</th>
									<th scope="col">#Nego.</th>
									<th scope="col">Ult.Hora</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($companies as $c){ ?>
								<tr class="table-<?= $c->row_color ?>">
									<td>
										<i class="bi bi-star<?= (in_array($c->data->company_id, $favorites) ? "-fill" : "") ?> ic_fav_control ic_fav_<?= $c->data->company_id ?>" value="<?= $c->data->company_id ?>"></i>
									</td>
									<td>
										<a href="<?= base_url() ?>company/detail/<?= $c->data->company_id ?>">
											<?= $c->companyName ?>
										</a>
									</td>
									<td><?= $c->sectorDescription ?></td>
									<td><?= $c->nemonico ?></td>
									<td><?= number_format($c->data->qty_total) ?></td>
									<td class="text-<?= $c->color ?>"><?= $c->percentageChange ?>%</td>
									<td><?= $c->currency ?></td>
									<td><?= $c->buy ?></td>
									<td><?= $c->sell ?></td>
									<td><?= $c->opening ?></td>
									<td><strong><?= $c->last ?></strong></td>
									<td><strong><?= $c->previous ?></strong></td>
									<td><?= $c->previousDate ?></td>
									<td><?= number_format($c->negotiatedQuantity) ?></td>
									<td><?= date("H:m:s", strtotime($c->lastDate)) ?></td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>