<?php $this->load->view('common/fbc-user/header'); ?>
<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <ul class="nav nav-pills">
      <li class="active"><a href="<?= base_url('variants') ?>">Variants</a></li>
      <li><a href="<?= base_url('variants/add-variants') ?>">Add New</a></li>
      <!--li><a href="<?= base_url('attribute/bulk-upload') ?>">Bulk Upload</a></li-->
   </ul>
   <div class="main-inner min-height-480">
    <div class="tab-content">
        <div id="variants" class="tab-pane fade in active " style="opacity:1;">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3" style="display: flex; justify-content: space-between; align-items: center;">
               <h1 class="head-name">Variants List </h1>
               <div style="display: flex; gap: 8px;">
                  <!--a href="<?php echo base_url()?>attribute/bulk-upload"> <button class="purple-btn" style="background: #4a5568;"><i class="fa fa-cloud-upload"></i> Bulk Upload</button></a-->
                  <a href="<?php echo base_url()?>variants/add-variants"> <button class="purple-btn"><i class="fa fa-plus"></i> Create New</button></a>
               </div>
            </div>
        <!-- form -->
        <div class="content-main form-dashboard">
               <div class="table-responsive text-center">
                  <table  class="table table-bordered table-style" id="datatableattribute">
                  <thead>
                        <tr>
                            <th>VARIANTS </th>
                            <th>VARIANTS CODE  </th>
                            <th>STATUS </th>
                            <th>DETAILS </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($getAttribute as $attribute ) {?>
                        <tr>                        
                        <td><?php echo $attribute['attr_name']; ?></td>
                        <td><?php echo $attribute['attr_code']; ?></td>
                            <td>
                                <?php if($attribute['status'] == 1){
                                    echo "Active";
                                }else{
                                    echo "In Active";
                                }?>    
                            </td>
                            <td>
                                <?php if($attribute['is_default'] != 1) {?>
                                <a class="link-purple" href="<?= base_url('VariantsController/editVariant/').$attribute['id'] ?>">
                                View</a>
                            <?php }else{
                                echo "-";
                            } ?>
                            </td>
                        </tr>
                        <?php  }?>
                    </tbody>
                </table>
            </div>
        </div>
        <!--end form-->
    </div>
    </div><!-- add new tab -->
</div>
  </div>
</main>
<?php $this->load->view('common/fbc-user/footer'); ?>
<script type="text/javascript">
    $(document).ready( function () {
        $("#datatableattribute").dataTable({
            "language": {
            "infoFiltered": "",
            "search": '',
            "searchPlaceholder": "Search ",
            "paginate": {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        });
    });
</script>