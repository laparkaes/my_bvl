<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title>My BVL 1.0</title>
	<meta content="my_bvl" name="description">
	<meta content="my_bvl" name="keywords">
	<link href="<?= base_url() ?>assets/img/favicon.svg" rel="icon">
	<link href="<?= base_url() ?>assets/img/apple-touch-icon.svg" rel="apple-touch-icon">
	<link href="https://fonts.gstatic.com" rel="preconnect">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
	<link href="<?= base_url() ?>assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= base_url() ?>assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
	<link href="<?= base_url() ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="toggle-sidebar">
	<header id="header" class="header fixed-top d-flex align-items-center">
		<div class="d-flex align-items-center justify-content-between">
			<a href="<?= base_url() ?>" class="logo d-flex align-items-center">
				<img src="<?= base_url() ?>assets/img/logo.svg" alt="" style="width:50px;">
				<span class="d-none d-lg-block">Mailing Sys 1.0</span>
			</a>
		</div>
		<nav class="header-nav ms-auto">
			<ul class="d-flex align-items-center">
				<li class="nav-item">
					<a class="nav-link nav-icon" href="<?= base_url() ?>">
						<i class="bi bi-send"></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link nav-icon" href="<?= base_url() ?>home/sender">
						<i class="bi bi-envelope"></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link nav-icon" href="<?= base_url() ?>home/content">
						<i class="bi bi-file-earmark-text"></i>
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link nav-icon" href="<?= base_url() ?>home/email_db">
						<i class="bi bi-database"></i>
					</a>
				</li>
			</ul>
		</nav>
	</header>
	<main id="main" class="main">
		<?php $this->load->view($main); ?>
	</main>
	<input type="hidden" id="base_url" value="<?= base_url() ?>">
	<script src="<?= base_url() ?>assets/vendor/jquery-3.7.0.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="<?= base_url() ?>assets/js/func.js"></script>
</body>
</html>