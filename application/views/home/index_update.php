<div class="pagetitle mb-3">
	<h1>Actualización de Registros</h1>
</div>
<section class="section contact">
	<div class="row">
		<div class="col-md-4">
			<div class="info-box card">
				<i class="bi bi-sign-stop"></i>
				<h3>Esperando..</h3>
				<?php foreach($updates as $i => $u){ ?>
				<div class="text-end" id="r_<?= $u["stock"] ?>">
					<div class="d-none update_datas"><?= json_encode($u) ?></div>
					<span><?= $u["stock"] ?> desde <?= $u["date"] ?></span>
				</div>
				<?php } ?>
			</div>
		</div>
		<div class="col-md-4">
			<div class="info-box card">
				<i class="bi bi-signpost"></i>
				<h3>Actualizando..</h3>
				<div class="text-end" id="bl_updating"></div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="info-box card">
				<i class="bi bi-check-circle"></i>
				<h3>Finalizados</h3>
				<div class="text-end" id="bl_finished"></div>
			</div>
		</div>
	</div>
</section>