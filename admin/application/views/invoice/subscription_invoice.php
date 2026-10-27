<!doctype html>
<html lang="en">

<head>

	<meta charset="utf-8">

	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<meta name="description" content="">

	<meta name="author" content="">

	<meta name="generator" content="Jekyll v4.1.1">

	<title>Subscription Invoice - <?php echo $order['id'] ?></title>

<?php
$logo_image_src = '';
$candidate_logos = [
	rtrim(FCPATH, '/\\') . '/../merchant/public/images/yellow-markets-logo-yellow-white.png',
	rtrim(FCPATH, '/\\') . '/public/images/yellow-markets-logo-yellow-white.png',
	rtrim(FCPATH, '/\\') . '/../uploads/yellow-markets-logo.png',
	rtrim(FCPATH, '/\\') . '/uploads/yellow-markets-logo.png',
	dirname(FCPATH) . '/uploads/yellow-markets-logo.png',
	'c:/xampp/htdocs/ymstore/merchant/public/images/yellow-markets-logo-yellow-white.png',
	'c:/xampp/htdocs/ymstore/uploads/yellow-markets-logo.png'
];
foreach ($candidate_logos as $c_logo) {
	if (file_exists($c_logo) && filesize($c_logo) > 0) {
		$ext = pathinfo($c_logo, PATHINFO_EXTENSION);
		$logo_image_src = 'data:image/' . ($ext ?: 'png') . ';base64,' . base64_encode(file_get_contents($c_logo));
		break;
	}
}
if (empty($logo_image_src) && !empty($logo_src) && strpos($logo_src, 'data:image') === 0) {
	$logo_image_src = $logo_src;
}
?>
	<style>
		@media print {
			#noprint, .no-print {
				display: none !important;
			}
		}

		body {
			margin: 0 !important;
			padding: 0 !important;
		}

		.bd-placeholder-img {
			font-size: 1.125rem;
			text-anchor: middle;
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
		}

		@media (min-width: 768px) {
			.bd-placeholder-img-lg {
				font-size: 3.5rem;
			}
		}

		table.table-style {
			font-family: 'Montserrat', sans-serif;
			font-size: 13px;
			margin: 10px 0 30px 0;
			text-align: left;
			padding: 0;
			border: 1px solid #dee2e6;
			border-collapse: collapse;
			width: 100%;
		}

		table.table-style th {
			background-color: #ffd200 !important;
			color: #000000 !important;
			border: 1px solid #dee2e6;
			padding: 10px 12px;
			vertical-align: middle;
			font-weight: 700;
			text-transform: uppercase;
			font-size: 11.5px;
			letter-spacing: 0.5px;
			text-align: center;
			word-wrap: break-word;
			white-space: normal;
		}

		table.table-style thead {
			background-color: #ffd200 !important;
			color: #000000 !important;
		}

		table.table-style td {
			background-color: #ffffff !important;
			border: 1px solid #dee2e6;
			padding: 12px 12px;
			vertical-align: middle;
			font-weight: 500;
			color: #212529;
			font-size: 12.5px;
			text-align: center;
			word-wrap: break-word;
			white-space: normal;
		}

		.form-control {
			font-weight: 400;
			line-height: 1.5;
			color: #495057;
			background-color: #fff;
			background-clip: padding-box;
			border: 1px solid #ced4da;
			border-radius: .25rem;
			transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
			padding: .375rem .75rem;
			width: 270px;
			height: 35px;
			font-size: 14px;
		}

		i.fa.fa-fw.fa-sort {
			margin-top: 4px;
		}
	</style>
</head>

<body
	style="margin:0; padding:0;vertical-align:top; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, 'Montserrat', sans-serif; font-size: 14px; background:#fff; font-weight:400;color:#444444;">

<?php if (!empty($is_html_fallback)): ?>
	<div id="noprint" style="background:#202938;color:#ffffff;padding:12px 20px;text-align:center;font-family:Arial,sans-serif;font-size:14px;margin-bottom:15px;">
		<strong>Invoice #<?php echo $order['id']; ?></strong> &nbsp;|&nbsp; 
		<button onclick="window.print();" style="background:#cb1f53;color:#ffffff;border:none;padding:6px 16px;border-radius:4px;cursor:pointer;font-weight:bold;margin-left:10px;">Print / Save as PDF</button>
	</div>
<?php endif; ?>

	<table cellpadding="0" cellspacing="0"
		style="font-family:'Montserrat', sans-serif;font-size: 14px;margin:0 auto;text-align:left;padding: 0px;background-color:#f5f5f5;"
		width="100%" align="center">

		<tbody>

			<tr>

				<td style="padding: 10px;">

					<table cellpadding="0" cellspacing="0"
						style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:0 auto;text-align:left;padding: 0;background-color:#ffffff; max-width: 100%;"
						width="100%" align="center">

						<tbody>

							<tr>

								<td style="padding:0 20px;">

									<table cellpadding="0" cellspacing="0"
										style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:30px 0 0 0;text-align:left;padding: 0;"
										width="100%" align="center">

										<tbody>

											<tr>

												<td style="padding:0;vertical-align: middle; text-align: left;"
													width="50%">
													<table cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin: 0; background-color: #23282d; border-radius: 4px;">
														<tr>
															<td style="background-color: #23282d; padding: 10px 16px; border-radius: 4px; border: none; text-align: center; vertical-align: middle;">
																<img src="<?php echo $logo_image_src; ?>" width="135" style="display: block; max-width: 100%; height: auto; border: 0;">
															</td>
														</tr>
													</table>
												</td>

												<td style="padding:0;vertical-align: middle; text-align: right;"
													width="50%">

													<strong style="font-size: 17px; font-weight: 700; color: #212529; font-family: 'Montserrat', sans-serif;">Subscription Invoice</strong>

												</td>

											</tr>

										</tbody>

									</table>

									<table cellpadding="0" cellspacing="0"
										style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:30px 0 0 0;text-align:left;padding: 0;"
										width="100%" align="center">

										<tbody>

											<tr>

												<td style="padding:0;vertical-align: middle; text-align: left;"
													width="100%">

													<h1
														style="font-weight: 700;font-size: 16px;line-height: 22px;letter-spacing: 0.05em;color: #333333;font-family: 'Montserrat', sans-serif; margin:0 0 20px 0;">
														Subscription Details</h1>

												</td>

											</tr>

										</tbody>

									</table>

									<table cellpadding="0" cellspacing="0"
										style="font-family: 'Montserrat', sans-serif;font-size: 13px;margin:0;text-align:left;padding: 0;"
										width="100%" align="center">

										<tbody>

											<tr>
												<td style="padding:0 0 20px 0;vertical-align: top; text-align: left;font-weight: 500;line-height: 18px;color: #333333;"
													width="50%">
													<span
														style="font-weight:600;display: block; font-size: 13px; color: #333333;">Merchant</span>
													<p
														style="padding:5px 0 0 0;margin:0;vertical-align: top; text-align: left;font-size: 13px;line-height: 18px;color: #333333;">
														<?php echo isset($order['publisher']['publication_name']) ? $order['publisher']['publication_name'] : (!empty($order['publisher']['name']) ? $order['publisher']['name'] : 'N/A'); ?></p>
												</td>

												<td style="padding:0 0 20px 0;vertical-align: top; text-align: left;font-weight: 500;line-height: 20px;color: #333333; font-size: 13px;"
													width="50%">

													<p style="margin:0 0 6px 0;"> <span
															style="font-weight:600; display: inline-block; width: 120px; color: #333333;">Invoice ID</span> : <?php echo $order['id'] ?> </p>

													<p style="margin:0 0 6px 0;"> <span
															style="font-weight:600; display: inline-block; width: 120px; color: #333333;">subscription Date</span> :
														<?php echo date('d-M-Y', strtotime($order['created_at'])); ?> </p>

													<p style="margin:0 0 6px 0;"> <span
															style="font-weight:600; display: inline-block; width: 120px; color: #333333;">Status</span> : <?php echo ucfirst($order['status']); ?> </p>

                                                            <?php if($order['status'] == 'paid') { 
                                                                $expiry_date = !empty($order['expiry_date']) ? $order['expiry_date'] : date('d-M-Y', strtotime($order['created_at'] . ' +1 year'));
                                                            ?>
                                                            <p style="margin:0;"> <span
															style="font-weight:600; display: inline-block; width: 120px; color: #333333;">Expiry Date</span> : <?php echo $expiry_date; ?> </p>
                                                          <?php  } ?>

												</td>

											</tr>

										</tbody>

									</table>

									<table cellpadding="0" cellspacing="0"
										style="font-family: 'Montserrat', sans-serif;font-size: 14px;margin:30px 0 0 0;text-align:left;padding: 0;"
										width="100%" align="center">

										<tbody>

											<tr>

												<td style="padding:0 0 15px 0;vertical-align: middle; text-align: left;"
													width="100%">

													<h2
														style="font-weight: 700;font-size: 15px;line-height: 20px;letter-spacing: 0.05em;color: #333333;font-family: 'Montserrat', sans-serif; margin: 0;">
														Subscription Information</h2>

												</td>

											</tr>

										</tbody>

									</table>

									<table class="table-style" style="width: 100%; border: 1px solid #dee2e6; border-collapse: collapse; font-family: 'Montserrat', sans-serif; margin-top: 10px; margin-bottom: 30px;">
										<thead>
											<tr style="background-color: #ffd200;">
												<th style="width: 35%; background-color: #ffd200; color: #000000; border: 1px solid #dee2e6; padding: 10px 12px; font-weight: 700; font-size: 11.5px; text-transform: uppercase; text-align: center;">Plan Name</th>
												<th style="width: 35%; background-color: #ffd200; color: #000000; border: 1px solid #dee2e6; padding: 10px 12px; font-weight: 700; font-size: 11.5px; text-transform: uppercase; text-align: center;">Amount</th>
												<th style="width: 30%; background-color: #ffd200; color: #000000; border: 1px solid #dee2e6; padding: 10px 12px; font-weight: 700; font-size: 11.5px; text-transform: uppercase; text-align: center;">Payment Method</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td style="background-color: #ffffff; color: #212529; border: 1px solid #dee2e6; padding: 12px 12px; font-size: 12.5px; text-align: center;"><?php echo isset($order['plan']['name']) ? $order['plan']['name'] : 'N/A'; ?></td>
												<td style="background-color: #ffffff; color: #212529; border: 1px solid #dee2e6; padding: 12px 12px; font-size: 12.5px; text-align: center;">MUR <?php echo number_format($order['amount'], 2); ?></td>
												<td style="background-color: #ffffff; color: #212529; border: 1px solid #dee2e6; padding: 12px 12px; font-size: 12.5px; text-align: center;">My.T Money</td>
											</tr>
										</tbody>
									</table>

								</td>

							</tr>

						</tbody>

					</table>

				</td>

			</tr>

		</tbody>

	</table>

</body>

</html>