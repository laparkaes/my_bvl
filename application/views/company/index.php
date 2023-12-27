<div class="pagetitle mb-3">
	<h1>Empresas</h1>
</div>
<section class="section">
	<div class="row">
		<div class="col">
			<?php
			$msgs = $this->session->flashdata('msgs');
			if ($msgs){
				$success_msgs = $msgs["success_msgs"];
				$error_msgs = $msgs["error_msgs"];
				if ($success_msgs) foreach($success_msgs as $msg){ ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					<i class="bi bi-check-circle me-1"></i>
					<?= $msg ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
				<?php } 
				if ($error_msgs) foreach($error_msgs as $msg){ ?>
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
					<i class="bi bi-exclamation-octagon me-1"></i>
					<?= $msg ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
				</div>
			<?php }
			} ?>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Lista</h5>
					hola como estas?
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Lista</h5>
					<div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th scope="col"></th>
									<th scope="col" class="w-50">Empresa</th>
									<th scope="col">Sector</th>
									<th scope="col">Nemonico</th>
									<th scope="col">#Reg.Total</th>
									<th scope="col">#Reg.<?= date("Y") ?></th>
									<th scope="col">#Reg.<?= date("Y") - 1 ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($companies as $c){ $c->row_color = ""; ?>
								<tr class="table-<?= $c->row_color ?>">
									<td>
										<i class="bi bi-star<?= (in_array($c->company_id, $favorites) ? "-fill" : "") ?> ic_fav_control ic_fav_<?= $c->company_id ?>" value="<?= $c->company_id ?>"></i>
									</td>
									<td>
										<a href="<?= base_url() ?>company/detail/<?= $c->company_id ?>">
											<?= $c->companyName ?>
										</a>
									</td>
									<td><?= $sectors[$c->sector_id] ?></td>
									<td><?= $c->stock ?></td>
									<td><?= number_format($c->qty_total) ?></td>
									<td><?= number_format($c->qty_this_year) ?></td>
									<td><?= number_format($c->qty_last_year) ?></td>
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