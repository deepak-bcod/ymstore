<?php 
	$lang = $this->session->userdata('site_lang'); // or whatever key you use for language
	// echo "<pre>"; print_r($lang); 
?>
<style type="text/css">
	#dash {
		width: 400px;
		height: 60px;
		overflow: hidden;
	}

	#dash p {
		padding: 10px;
		margin: 0;
	}
</style>


<div class="static-page product-list-section">
    <div class="container">
        <h1><?= $this->lang->line('blog'); ?></h1>

        <div class="blogList">
            <div class="row"> <!-- Use row with gutter instead of d-flex -->
				<?php if (!empty($blogs)) {
					foreach ($blogs as $value) { ?>
						<div class="blog-item col-md-6 col-lg-4"> <!-- col-4 for large screens -->
							<h5>
								<a href="<?= BASE_URL . 'blogs/' . $value->url_key; ?>" class="text-dark">
									<?php if ($lang === 'french') : ?>
										<?= $value->lang_title; ?>
									<?php else : ?>
										<?= $value->title; ?>
									<?php endif; ?>
								</a>

							</h5>

							<a href="<?= BASE_URL.'blogs/'.$value->url_key; ?>" class="btn btn-primary">
								<?= $this->lang->line('read_more'); ?>
							</a>
						</div>
				<?php } } else { ?>
					<p><?= $this->lang->line('no_blogs_found'); ?></p>
				<?php } ?>

            </div>
        </div>

        <?= $PaginationLink ?>
    </div>
</div>


<script type="text/javascript">
	function sort_by(page = 1) {
		let page_size = 9; // Define it consistently
		$.ajax({
			type: "POST",
			url: BASE_URL + "BlogController/sort_by",
			data: {
				page: page,
				page_size: page_size
			},
			beforeSend: function () {
				$("#product-list-section").html('<div class="loading">Loading...</div>');
			},
			success: function (html) {
				$("#product-list-section").html(html);
			},
			error: function () {
				$("#product-list-section").html('<div class="error">Something went wrong. Please try again.</div>');
			}
		});
	}
</script>




