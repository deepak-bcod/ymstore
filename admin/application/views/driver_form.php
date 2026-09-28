<?php $this->load->view('common/fbc-user/header'); ?> 

<main class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <h1 class="head-name"><?= isset($driver) ? 'Edit Driver' : 'Add New Driver' ?></h1>

    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= isset($driver) ? base_url('driver/edit/'.$driver['id']) : base_url('driver/add') ?>" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" class="form-control" 
                   value="<?= isset($driver) ? $driver['first_name'] : '' ?>" required>
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control" 
                   value="<?= isset($driver) ? $driver['last_name'] : '' ?>" required>
        </div>

        <div class="form-group">
            <label>Mobile No</label>

            <input 
                type="tel" 
                id="mobile_no" 
                name="mobile_no" 
                class="form-control" 
                placeholder="Enter mobile number"
                value="<?= isset($driver) ? htmlspecialchars($driver['mobile_no']) : '' ?>"
                pattern="[0-9]{8}" 
                maxlength="8"
                required
            >

            <!-- Hidden input to store full international number -->
            <input type="hidden" id="full_mobile" name="full_mobile">
        </div>


        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" 
                   value="<?= isset($driver) ? $driver['email'] : '' ?>" required>
        </div>

        <?php if (isset($driver)): ?>
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" id="change_password" class="form-check-input">
                <label for="change_password" class="form-check-label">Update Password</label>
            </div>
        </div>

        <div class="form-group" id="password_field" style="display: none;">
            <label>New Password</label>
            <input type="password" name="password" class="form-control">
        </div>
    <?php else: ?>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
    <?php endif; ?>

        <div class="form-group">
            <label>Profile Photo</label>
            <input type="file" name="profile_photo" class="form-control">
            <?php if(isset($driver) && $driver['profile_photo']): ?>
                <p>
                    <small>
                        Current: 
                        <img src="<?= base_url('admin/public/images/'.$driver['profile_photo']) ?>" width="60">
                    </small>
                </p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Driver Licence No</label>
            <input type="text" name="driver_licence_no" class="form-control" 
                   value="<?= isset($driver) ? $driver['driver_licence_no'] : '' ?>">
        </div>

        <div class="form-group">
            <label>Licence Plate No</label>
            <input type="text" name="licence_plate_no" class="form-control" 
                   value="<?= isset($driver) ? $driver['licence_plate_no'] : '' ?>">
        </div>

        <button type="submit" class="btn btn-primary"><?= isset($driver) ? 'Update' : 'Save' ?></button>
        <a href="<?= base_url('driver') ?>" class="btn btn-secondary">Cancel</a>
    </form>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('change_password');
        const passwordField = document.getElementById('password_field');

        if (checkbox) {
            checkbox.addEventListener('change', function () {
                passwordField.style.display = this.checked ? 'block' : 'none';
            });
        }
    });
</script>
<?php $this->load->view('common/fbc-user/footer'); ?> 
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/css/intlTelInput.min.css">
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/js/utils.js"></script>
<script>
      $(document).ready(function() {
            // ✅ Initialize with Mauritius as the default country
            // ✅ Initialize Mobile Number field with intl-tel-input
            const mobileInput = document.querySelector("#mobile_no");
            const itiMobile = window.intlTelInput(mobileInput, {
                initialCountry: "mu",  // Mauritius 🇲🇺
                nationalMode: false,
                formatOnDisplay: true,
                separateDialCode: true,
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.5.3/build/js/utils.js"
            });


            // ✅ Before form submit, store full international numbers in hidden inputs
            // $('form').on('submit', function (e) {
            //       e.preventDefault();

            //       const form = $(this);
            //       const formData = new FormData(this);

            //       $.ajax({
            //             url: form.attr('action'),
            //             type: form.attr('method'),
            //             data: formData,
            //             processData: false,
            //             contentType: false,
            //             dataType: 'json', // 👈 tells jQuery to expect JSON
            //             success: function (response) {
            //                   if (response.flag === 1) {
            //                         // alert(response.msg);
            //                         window.location.href = response.url; // redirect
            //                   } else {
            //                         alert(response.msg || "Something went wrong");
            //                   }
            //             },
            //             error: function (xhr) {
            //                   alert("Error: " + xhr.responseText);
            //             }
            //       });
            // });
      });
</script>