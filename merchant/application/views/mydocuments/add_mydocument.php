<?php 
$this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner">
        <button class="btn btn-primary btn-sm btn-back" style="float:right" onclick="history.back()"><?= lang('back'); ?></button>
        
    <h1 class="head-name mb-4"><?php echo lang('add_document'); ?></h1>
    <div class="card card-section-new">
        <div class="card-body" style="padding:0">
           <form id="docForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for=""><?php echo lang('documents_name'); ?> <span class="text-danger">*</span></label>
                    <input type="text" id="document_name" name="document_name" class="form-control form-setion-new" required>
                </div>
                <div class="form-group form-setion-new">
                    <input type="file" id="document_file" name="document_file" class="form-control">
                    <p><?php echo lang('upload_note'); ?></p>
                </div>
                <button type="button" id="saveBtn" class="btn btn-primary"><?php echo lang('save'); ?></button>
            </form>

            <div id="responseMsg"></div>


        </div>
    </div>
</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $("#saveBtn").on("click", function (e) {
        e.preventDefault();

        var formData = new FormData($("#docForm")[0]); // pick form with files

        $.ajax({
            url: "<?php echo BASE_URL('mydocuments/insert'); ?>",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",   // ✅ IMPORTANT
            success: function (response) {
                if (response.status == 200) {
                    Swal.fire({
                        icon: 'success',
                        title: '<?php echo lang('success'); ?>',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect_url; // redirect
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '<?php echo lang('error'); ?>',
                        html: response.message
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong!'
                });
            }
        });

    });

</script>
<?php $this->load->view('common/fbc-user/footer'); ?>
