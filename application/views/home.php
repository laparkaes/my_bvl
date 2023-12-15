<div class="pagetitle mb-3">
	<h1>Auto Mailing</h1>
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
					<h5 class="card-title">Parameters</h5>
					<!--form class="row g-3 mb-0" method="post" action="<?= base_url() ?>home/send_emails" target="_blank" -->
					<form class="row g-3 mb-0" id="form_send_email">
						<div class="col-md-4 col-12">
							<label class="form-label">Sender</label>
							<select class="form-select" name="sender_id">
								<option value="">Select...</option>
								<?php foreach($senders as $s){ ?>
								<option value="<?= $s->sender_id ?>"><?= $s->title ?> (<?= $s->smtp_user ?>)</option>	
								<?php } ?>
							</select>
						</div>
						<div class="col-md-4 col-12">
							<label class="form-label">Content</label>
							<select class="form-select" name="content_id">
								<option value="">Select...</option>
								<?php foreach($contents as $c){ ?>
								<option value="<?= $c->content_id ?>"><?= $c->title ?></option>	
								<?php } ?>
							</select>
						</div>
						<div class="col-md-4 col-12">
							<label class="form-label">Email list</label>
							<select class="form-select" name="list_id">
								<option value="">Select...</option>
								<?php foreach($email_lists as $e){ ?>
								<option value="<?= $e->list_id ?>"><?= $e->list ?></option>	
								<?php } ?>
							</select>
						</div>
						<div class="text-center pt-3">
							<button type="button" class="btn btn-primary" id="btn_start">Start</button>
							<button type="button" class="btn btn-danger d-none" id="btn_stop">Stop</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Result</h5>
					<div id="bl_mailing_result"></div>
				</div>
			</div>
		</div>
	</div>
</section>