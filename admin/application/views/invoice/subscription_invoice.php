<!doctype html>
<html lang="en">

<head>

	<meta charset="utf-8">

	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<meta name="description" content="">

	<meta name="author" content="">

	<meta name="generator" content="Jekyll v4.1.1">

	<title>Subscription Invoice - <?php echo $order['id'] ?></title>

	<!-- Bootstrap core CSS -->
	<link rel="stylesheet" type="text/css" href="https://mu.yellowmarkets.com/public/css/bootstrap.min.css"
		media="all">
	<script src="https://code.jquery.com/jquery-2.2.0.min.js" type="text/javascript"></script>
	<script src="https://mu.yellowmarkets.com/public/js/bootstrap.min.js"></script>
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;500;600;700;800&display=swap"
		rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="https://mu.yellowmarkets.com/public/css/all.css"
		media="all">
	<style>
		@media print {
			#noprint {
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
			font-size: 12px;
			margin: 10px 0 0 0;
			text-align: left;
			padding: 0;
			border: 1px solid #dee2e6;
			border-collapse: collapse;
			width: 100%;
		}

		table.table-style th {
			border: 1px solid #dee2e6;
			padding: 8px 5px;
			vertical-align: middle;
			font-weight: 600;
			text-transform: uppercase;
			border-bottom: 2px solid #dee2e6;
			font-size: 11px;
			color: #ffffff;
			text-align: center;
			word-wrap: break-word;
			white-space: normal;
		}

		table.table-style thead {
			background: linear-gradient(90deg, rgb(203, 31, 83) 0%, rgb(115, 16, 91) 84.41%);
			color: rgb(255, 255, 255);
		}

		table.table-style td {
			border: 1px solid #dee2e6;
			padding: 8px 5px;
			vertical-align: middle;
			font-weight: 500;
			color: #212529;
			font-size: 11px;
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
	style="margin:0; padding:0;vertical-align:top; font-family: 'Montserrat', sans-serif; font-size: 14px; background:#fff; font-weight:400;color:#444444;">

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
													<div class="logo-print">
														<img src="<?php echo SITE_LOGO; ?>" width="120" style="max-width: 100%;">
													</div>
												</td>

												<td style="padding:0;vertical-align: middle; text-align: right;"
													width="50%">

													<strong style="font-size: 16px;">Subscription Invoice</strong>

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
														style="font-weight: 600;font-size: 16px;line-height: 20px;letter-spacing: 0.05em;text-transform: capitalize;color: #444444;font-family: 'Montserrat', sans-serif; margin:0 0 20px 0;">
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
														style="font-weight:600;display: block;">Merchant</span>
													<p
														style="padding:5px 0 0 0;margin:0;vertical-align: top; text-align: left;font-size: 13px;line-height: 16px;color: #333333;">
														<?php echo isset($order['publisher']['publication_name']) ? $order['publisher']['publication_name'] : 'N/A'; ?></p>
												</td>

												<td style="padding:0 0 20px 0;vertical-align: top; text-align: left;font-weight: 500;line-height: 18px;color: #333333;"
													width="50%">

													<p style="margin:0 0 5px 0;"> <span
															style="font-weight:600; display: inline-block; width: 80px;">Invoice ID</span> : <?php echo $order['id'] ?> </p>

													<p style="margin:0 0 5px 0;"> <span
															style="font-weight:600; display: inline-block;  width: 80px;">subscription Date</span> :
														<?php echo date('d-M-Y', strtotime($order['created_at'])); ?> </p>

													<p style="margin:0;"> <span
															style="font-weight:600; display: inline-block; width: 80px;">Status</span> : <?php echo ucfirst($order['status']); ?> </p>

                                                            <?php if($order['status'] == 'paid') { 
                                                                $expiry_date = date('d-M-Y', strtotime($order['created_at'] . ' +1 year'));
                                                            ?>
                                                            <p style="margin:0;"> <span
															style="font-weight:600; display: inline-block; width: 80px;">Expiry Date</span> : <?php echo $expiry_date; ?> </p>
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
														style="font-weight: 600;font-size: 14px;line-height: 18px;letter-spacing: 0.05em;text-transform: capitalize;color: #444444;font-family: 'Montserrat', sans-serif; margin: 0;">
														Subscription Information</h2>

												</td>

											</tr>

										</tbody>

									</table>

									<table class="table-style">
										<thead>
											<tr>
												<th style="width: 35%;">Plan Name</th>
												<th style="width: 35%;">Amount</th>
												<th style="width: 30%;">Payment Method</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td><?php echo isset($order['plan']['name']) ? $order['plan']['name'] : 'N/A'; ?></td>
												<td>MUR <?php echo number_format($order['amount'], 2); ?></td>
												<td>My.T Money</td>
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