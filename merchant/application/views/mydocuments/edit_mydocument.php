<?php 
$this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <div class="main-inner">
       <a href="javascript:history.back()" class="back-link" style="float:right"><?= lang('back_to_list'); ?></a>
    <h1 class="head-name mb-4"><?php echo lang('edit_document'); ?></h1>
    <div class="card card-section-new">
        <div class="card-body" style="padding:0">
           <form id="docForm" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $data->id; ?>">

                <div class="form-group">
                    <label for=""><?php echo lang('documents_name'); ?> <span class="text-danger">*</span></label>
                    <input type="text" id="document_name" name="document_name" 
                        class="form-control" value="<?php echo $data->document_name; ?>" required>
                </div>
                <div class="form-group form-setion-new">
                    <input type="file" id="document_file" name="document_file" class="form-control">
                    <p><?php echo lang('upload_note'); ?></p>
                    <small><?php echo lang('current_file'); ?> 
                        <a>
                            <?= $data->document_file ?>
                        </a>
                    </small>
                </div>
                <button type="button" id="saveBtn" class="btn btn-primary btn-new"><?php echo lang('update'); ?></button>
            </form>

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
            url: "<?php echo BASE_URL('mydocuments/update/'); ?>" + $("input[name=id]").val(),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",   // ✅ IMPORTANT
            success: function (response) {
                if (response.status == 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect_url; // redirect
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
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
