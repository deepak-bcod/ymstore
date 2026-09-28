<?php $this->load->view('common/header'); ?>
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

<div class="breadcrum-section">
	<div class="container">
		<ul class="breadcrumb">
			<li>
			<a href="<?= base_url(); ?>">
				<?= $this->lang->line('home'); ?>
			</a>

			</li>
			<li class="active">
				<?= $this->lang->line('blogs'); ?>
			</li>
		</ul>
	</div>
</div><!-- breadcrum section -->

<!-- BLOG LIST SECTION -->
<div id="product-list-section">
    <?php $this->load->view('blog/blog_list_content'); ?>
</div>


<?php $this->load->view('common/footer'); ?>


<script type="text/javascript">
    function sort_by(page = 1) {
		$.ajax({
			type: "POST",
			url: BASE_URL + "BlogController/sort_by",
			data: { page: page },
			beforeSend: function () {
				$('#product-list-section').html('<p>Loading...</p>');
			},
			success: function (html) {
				$('#product-list-section').html(html);
			}
		});
	}

</script>
