<?php $this->load->view('common/fbc-user/header'); ?>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.3/themes/ui-lightness/jquery-ui.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
	<?php $this->load->view('webshop/discount/breadcrums'); ?>

	<div class="tab-content">
		<div id="Special Pricing" class="tab-pane fade in active common-tab-section min-height-480" style="opacity:1;">

			<!-- form -->
			<form action="<?php echo base_url(); ?>WebshopController/save_special_pricing" method="POST" name="special_pricing_form" id="special_pricing_form">

				<div class="customize-add-section pad-t-20">
					<div class="row">

						<div class="left-form-sec">

							<div class="col-sm-6 customize-add-inner-sec">
								<input type="text"
									class="form-control"
									id="sku"
									name="sku"
									placeholder="<?= $this->lang->line('product_name'); ?>">
								<input type="hidden" class="form-control" id="product_id" name="product_id">
							</div>

							<div class="col-sm-6 customize-add-inner-sec">
								<label><?= $this->lang->line('eshop_price_label'); ?> :</label>
								<input type="text" class="form-control" readonly name="webshop_price" id="webshop_price">
							</div>

							<div class="col-sm-6 customize-add-inner-sec from-to">
								<label><?= $this->lang->line('from'); ?></label>
								<input class="form-control" type="date"  name="from_date" id="from_date">
                                <div class="alert alert-danger" id="from_date_error1" style="display:none"><?= lang('field_required'); ?></div>
							</div>

							<div class="col-sm-6 customize-add-inner-sec display-original">
								<label><?= $this->lang->line('display_original'); ?></label>
								<div class="switch-onoff">
									<label class="checkbox">
										<input type="checkbox"
											name="display_original"
											id="display_original"
											autocomplete="off"
											checked>
										<span class="checked"></span>
									</label>
								</div>
							</div>

						</div>

						<div class="right-form-sec coupon-code-select">

							<div class="col-sm-6 customize-add-inner-sec">
								&nbsp;
							</div>

							<div class="col-sm-6 customize-add-inner-sec">
								<label><?= $this->lang->line('special_price'); ?> :</label>
								<input class="form-control"
									type="text"
									name="special_price"
									step="0.01"
									id="special_price"
									
									placeholder="<?= $this->lang->line('special_price'); ?>">
                                <div class="alert alert-danger" id="special_price_error1" style="display:none"><?= lang('field_required'); ?></div>
							</div>

							<div class="col-sm-6 customize-add-inner-sec from-to">
								<label><?= $this->lang->line('to'); ?></label>
								<input class="form-control" type="date"  name="to_date" id="to_date">
                                <div class="alert alert-danger" id="to_date_error1" style="display:none"><?= lang('field_required'); ?></div>
							</div>

						</div>

					</div>
				</div>

				<div class="download-discard-small mar-top">
					<button class="white-btn"
							name="discard_btn"
							id="discard_btn"
							onclick="gotoLocation('<?= $special_pricing_link; ?>');">
						<?= $this->lang->line('discard'); ?>
					</button>

					<?php if(empty($this->session->userdata('userPermission')) || in_array('webshop/discounts/write',$this->session->userdata('userPermission'))){ ?>
						<button class="download-btn"
								name="save_special_pricing"
								id="save_special_pricing"
								value="save">
							<?= $this->lang->line('save'); ?>
						</button>
					<?php } ?>

				</div>

			</form>
			<!-- end form -->

		</div>
	</div>

	</main>
	<script>
    function decodeHtmlEntities(value) {
        if (value === null || value === undefined) {
            return '';
        }

        var textarea = document.createElement('textarea');
        textarea.innerHTML = value;
        return textarea.value;
    }

    $('#sku').autocomplete({
        minLength: 3,

        source: function(request, response) {
            $.getJSON(BASE_URL + "InboundController/getProductSku", {
                term: request.term,
                search_for_barcode_flag: 1
            }, function(data) {

                var array = data.error ? [] : $.map(data, function(m) {

                    var productName = decodeHtmlEntities(m.name);
                    var sku         = decodeHtmlEntities(m.sku);
                    var barcode     = decodeHtmlEntities(m.barcode);

                    var label = productName;

                    if (sku) {
                        label += " - " + sku;
                    }

                    if (barcode) {
                        label += " - " + barcode;
                    }

                    return {
                        label: label,
                        value: sku,
                        prod_id: m.id
                    };
                });

                response(array);
            });
        },

        select: function(event, ui) {

            $('#sku').val(ui.item.label);
            $('#product_id').val(
                ui.item ? ui.item.prod_id : ""
            );

            return false;
        },

        focus: function(event, ui) {

            $('#sku').val(ui.item.label);

            return false;
        },

        change: function(event, ui) {

            $('#sku').val(
                ui.item ? ui.item.label : ''
            );
        }
    });
</script>
	<script>
		const LANG = {
			error: "<?= lang('error'); ?>",
			field_required: "<?= lang('field_required'); ?>",
			product_id_error: "<?= lang('product_id_error'); ?>",
			from_date_error: "<?= lang('from_date_error'); ?>",
			special_price_error: "<?= lang('special_price_error'); ?>",
			$to_date_error: "<?= lang('to_date_error'); ?>",
		};
    </script>

	<script src="<?php echo SKIN_JS; ?>special_pricing_add.js?v=<?php echo CSSJS_VERSION; ?>"></script>
<?php $this->load->view('common/fbc-user/footer'); ?>