<?php $this->load->view('common/fbc-user/header'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.min.js"></script>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
    <!-- Breadcrumbs -->
    <?php //$this->load->view('seller/products/breadcrums'); ?>
    <div class="tab-content">
        <!-- Add-Ons Services Tab -->
        <div id="addons-services" class="tab-pane fade show active">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success'); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <h1 class="head-name mb-4"><?= $this->lang->line('product_badges'); ?></h1>
            <p><?= $this->lang->line('pb_para_1'); ?></p>
            <p><?= $this->lang->line('pb_para_2'); ?></p>
            <p><?= $this->lang->line('pb_para_3'); ?></p>

            <?php if (!empty($pb_category)): ?>

                <?php $catIndex = 1; ?>

                <div class="accordion accordion-new" id="categoryAccordion">
                    <?php foreach ($pb_category as $category): ?>
                        <div class="card mb-3">
                            <div class="card-header category-title collapsed" data-toggle="collapse" data-target="#cat<?= $catIndex ?>" aria-expanded="false" aria-controls="cat<?= $catIndex ?>">
                               <h3 class="card-title card-new mb-0">
    <i class="fa fa-angle-right mr-2"></i> 
    <?php 
        // Logic: If French is selected and name_fr exists, show it; otherwise, default to English (name)
        echo ($this->session->userdata('site_lang') == 'french' && !empty($category['name_fr'])) 
             ? htmlspecialchars($category['name_fr']) 
             : htmlspecialchars($category['name']); 
    ?>
</h3>
                            </div>

                            <div id="cat<?= $catIndex ?>" 
                                class="collapse card-body" 
                                data-parent="#categoryAccordion">
                                <div class="row">
                                    <!-- Left side (tabs) -->
                                    <div class="col-md-3 tav-section-test">
                                        <ul class="nav flex-column nav-pills" id="categoryTabs-<?= $catIndex ?>" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="tab-cond-<?= $catIndex ?>-tab" data-toggle="pill" href="#tab-cond-<?= $catIndex ?>" role="tab">
                                                    <?= $this->lang->line('condition'); ?>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-apply-<?= $catIndex ?>-tab" data-toggle="pill" href="#tab-apply-<?= $catIndex ?>" role="tab">
                                                    <?= $this->lang->line('apply'); ?>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-app-link-<?= $category['id'] ?>" href="#tab-app-<?= $category['id'] ?>" data-cat-id="<?= $category['id'] ?>" data-toggle="pill" role="tab">
                                                    <?= $this->lang->line('application'); ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Right side (tab panes) -->
                                    <div class="col-md-9">
                                        <div class="tab-content" id="categoryTabsContent-<?= $catIndex ?>">
                                            <div class="tab-pane fade show active" id="tab-cond-<?= $catIndex ?>" role="tabpanel">
    <?php 
        if($this->session->userdata('site_lang') == 'french') {
            // Priority: French Column -> English Column -> Empty String
            echo $category['main_content_fr'] ?? $category['main_content'] ?? ''; 
        } else {
            echo $category['main_content'] ?? '';
        }
    ?>
</div>
                                            <div class="tab-pane fade" id="tab-apply-<?= $catIndex ?>" role="tabpanel">
                                                <form method="POST" action="<?= base_url('ProductBadges/submitApply') ?>" class="productBlockForm">
                                                    <div class="row">
                                                        <input type="hidden" name="prod_badge_cat_id" class="form-control" value="<?= $category['id'] ?>">
                                                        <input type="hidden" name="merchant_id" class="form-control" value="<?php echo $merchantDetails['id'] ?>">

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for=""><?= $this->lang->line('company_name'); ?>:</label>
                                                                <input type="text" name="company_name" class="form-control" value="<?php echo $merchantDetails['company_name'] ?>">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for=""><?= $this->lang->line('brn'); ?> :</label>
                                                                <input type="text" name="brn" class="form-control" value="<?php echo $merchantDetails['brn_no'] ?>">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for=""><?= $this->lang->line('contact_person'); ?> :</label>
                                                                <input type="text" name="contact_person" class="form-control" value="<?php echo $merchantDetails['vendor_name'] ?>">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for=""><?= $this->lang->line('contact_mobile'); ?> :</label>
                                                                <input type="number" maxlength="15" name="mobile" class="form-control" value="<?php echo $merchantDetails['phone_no'] ?>">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for=""><?= $this->lang->line('contact_email'); ?> :</label>
                                                                <input type="email" name="email" class="form-control" value="<?php echo $merchantDetails['email'] ?>">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for=""><?= $this->lang->line('production_location'); ?> :</label>
                                                                <input type="text" name="location" class="form-control production-section" value="<?php echo $merchantDetails['company_address'] ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="custom-multiselect mb-3">
                                                                <label for=""><?= $this->lang->line('product_names'); ?> :</label>
                                                                <button class="btn btn-light dropdown-toggle" type="button" data-toggle="dropdown">
                                                                    <?= $this->lang->line('select_products'); ?>
                                                                </button>
                                                                <div class="dropdown-menu p-2" style="max-height: 300px; overflow-y: auto; min-width: 300px;">
                                                                    
                                                                    <!-- 🔍 Search box -->
                                                                    <input type="text" class="form-control mb-2" placeholder="<?= $this->lang->line('search_products'); ?>" id="productSearch">

                                                                    <div id="productListContainer">
                                                                        <?php if (!empty($productList)): ?>
                                                                            <?php foreach ($productList as $val): ?>
                                                                                <?php 
                                                                                    $launchDate = is_numeric($val->launch_date) 
                                                                                        ? date("d-m-Y", $val->launch_date) 
                                                                                        : date("d-m-Y", strtotime($val->launch_date));

                                                                                    $checked = (!empty($products_arr) && in_array($val->id, $products_arr)) 
                                                                                        ? 'checked' 
                                                                                        : '';
                                                                                ?>
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input" type="checkbox" 
                                                                                        name="productList[]" 
                                                                                        id="product_<?= $val->id ?>" 
                                                                                        value="<?= htmlspecialchars($val->id) ?>" <?= $checked ?>>
                                                                                    <label class="form-check-label" for="product_<?= $val->id ?>">
                                                                                        <?= htmlspecialchars($val->name . ' - ' . $val->product_code) ?>
                                                                                    </label>
                                                                                </div>
                                                                            <?php endforeach; ?>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <div class="product-error-message text-danger small mt-1" style="display: none;">
                                                                    <?= $this->lang->line('product_field_required'); ?>
                                                                </div>
                                                            </div>

                                                            <div class="custom-multiselect mb-3">
                                                                <label>
    <?= $this->lang->line('document_names'); ?> :
    <span class="text-danger">*</span>
</label>

<button class="btn btn-light dropdown-toggle"
        type="button"
        data-toggle="dropdown">
    <?= $this->lang->line('select_documents'); ?>
</button>
                                                                <div class="dropdown-menu p-2">
                                                                    <?php if (!empty($documentList)): ?>
                                                                        <?php foreach ($documentList as $val): ?>
                                                                            <?php 
                                                                                $checked = (!empty($selectedDocuments) && in_array($val['id'], $selectedDocuments)) 
                                                                                            ? 'checked' 
                                                                                            : '';
                                                                            ?>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input document-checkbox" 
    type="checkbox" 
    name="documentList[]" 
    id="document_<?= $val['id'] ?>" 
    value="<?= htmlspecialchars($val['id']) ?>" <?= $checked ?>>
                                                                                <label class="form-check-label" for="document_<?= $val['id'] ?>">
                                                                                    <?= htmlspecialchars($val['document_name']) ?>
                                                                                </label>
                                                                            </div>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for=""><?= $this->lang->line('accept_terms'); ?> :</label>
                                                                <input type="text" name="terms" class="form-control" placeholder="<?= $this->lang->line('type_name_accept_terms'); ?>">
                                                                   <div class="product-error-message text-danger small mt-1" style="display: none;">
                                                                    <?= $this->lang->line('terms_field_required'); ?>
                                                                </div>
                                                            </div>
                                                            <button type="submit" id="saveBtn" class="btn btn-primary"><?= $this->lang->line('save'); ?></button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="tab-pane fade" id="tab-app-<?= $catIndex ?>" role="tabpanel">
                                                <div class="loader text-center py-3" style="display:none;"><?= $this->lang->line('loading'); ?></div>
                                                <table class="table table-bordered table-striped product-table-<?= $catIndex ?>">
                                                    <thead>
                                                        <tr>
                                                            <th><?= $this->lang->line('product_name'); ?></th>
                                                            <th><?= $this->lang->line('product_sku'); ?></th>
                                                            <th><?= $this->lang->line('product_status'); ?></th>
                                                            <th><?= $this->lang->line('details'); ?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $catIndex++; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info"><?= $this->lang->line('no_product_badges'); ?></div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php $this->load->view('common/fbc-user/footer'); ?>
<!-- Page-specific CSS -->
<style>
    .custom-multiselect {
        position: relative;
        width: 100%;
    }
    .custom-multiselect button {
        width: 100%;
        text-align: left;
    }
    .custom-multiselect .dropdown-menu {
        max-height: 250px;
        max-width: unset !important;
        overflow-y: auto;
        width: 100%;
    }
    .custom-multiselect .dropdown-toggle {
        white-space: normal; /* Allow multi-line text */
        text-align: left;
        width: 100%;
        max-height: 100px; /* Prevent overflow if many selected */
        overflow-y: auto;
    }
    .custom-multiselect .dropdown-toggle div {
        line-height: 1.3;
    }
</style>
<style>

.category-title {

    background: #f4f6f9;

    padding: 8px 12px;

    cursor: pointer;

    border-bottom: 1px solid #ddd;

    font-size: 16px;

    font-weight: 600;

}

.category-title h3 {

    margin: 0;

    font-size: inherit;

}

.category-title.collapsed i {

    transform: rotate(0deg);

    transition: transform 0.3s;

}

.category-title:not(.collapsed) i {

    transform: rotate(90deg);

    transition: transform 0.3s;

}



.service-card {

    border: 1px solid #ddd;

    border-radius: 6px;

    background: #fff;

    transition: 0.3s;

    height: 100%;

}

.service-card:hover {

    box-shadow: 0px 4px 12px rgba(0,0,0,0.1);

    transform: translateY(-3px);

}

.service-card img {

    border-radius: 6px;

    max-height: 140px;

    object-fit: cover;

}

.service-card h5 {

    font-size: 16px;

    font-weight: 600;

    margin-top: 10px;

}

.service-card p {

    font-size: 14px;

    color: #666;

    min-height: 40px;

}

.service-card strong {

    font-size: 15px;

    color: #222;

}

.service-card a.text-dark:hover {

    text-decoration: none;

}

</style>

<!-- Page-specific JS -->
<script>
    $(document).ready(function () {

        // 🔹 Update dropdown label when checkbox is toggled
        $(document).on('change', 'input[name="productList[]"], input[name="documentList[]"]', function () {
            updateDropdownLabel($(this));
        });

        function updateDropdownLabel(element) {
            let dropdown = element.closest('.custom-multiselect');
            let label = dropdown.find('label').text().trim();
            let button = dropdown.find('button');
            let selected = dropdown.find('input:checked').map(function () {
                return $(this).next('label').text().trim();
            }).get();

            if (selected.length === 0) {
                // Default button text
                button.html(label.includes('Product') ? 'Select Products' : 'Select Documents');
            } else {
                // Show each selected item on new line
                let formatted = selected.map(name => `<div>${name}</div>`).join('');
                button.html(formatted);
            }
        }

        // 🔹 Initialize dropdowns on page load
        $('input[name="productList[]"], input[name="documentList[]"]').each(function () {
            if ($(this).is(':checked')) {
                updateDropdownLabel($(this));
            }
        });

    });
</script>

<script>
$(document).ready(function () {

    // 🔹 Update dropdown label when a checkbox changes
    $(document).on('change', 'input[name="productList[]"], input[name="documentList[]"]', function () {
        updateDropdownLabel($(this));
    });

    // 🔹 Function to update the button label text
    function updateDropdownLabel(element) {
        let dropdown = element.closest('.custom-multiselect');
        let label = dropdown.find('label').text().trim();
        let button = dropdown.find('button');
        let selected = dropdown.find('input:checked').map(function () {
            return $(this).next('label').text().trim();
        }).get();

        if (selected.length === 0) {
            // No selection → show default
            button.text(label.includes('Product') ? 'Select Products' : 'Select Documents');
        } else if (selected.length <= 3) {
            // Show names if 3 or fewer selected
            button.text(selected.join(', '));
        } else {
            // Show count if many selected
            button.text(selected.length + ' selected');
        }
    }

    // 🔹 Initialize dropdown labels on page load (for pre-selected values)
    $('input[name="productList[]"], input[name="documentList[]"]').each(function () {
        if ($(this).is(':checked')) {
            updateDropdownLabel($(this));
        }
    });

});
</script>

<script>
$(document).ready(function () {
    // Listen for clicks on any "Application" tab by ID prefix
    $(document).on('click', '[id^="tab-app-link-"]', function (e) {
        e.preventDefault();
        const base_url = "<?= base_url(); ?>";

        let $tabLink = $(this);
        let catId = $tabLink.data('cat-id');     // ✅ actual category ID
        let tabId = $tabLink.attr('href');       // e.g. #tab-app-7
        let $tabPane = $(tabId);
        let $tableBody = $tabPane.find('tbody');
        let $loader = $tabPane.find('.loader');
        const productImageBase =  "<?= IMAGE_URL_SHOW . '/products/thumb/' ?>";
        // prevent duplicate load
        if ($tableBody.children().length > 0) {
            return;
        }

        $loader.show();

        $.ajax({
            url: "<?= base_url('ProductBadges/getAppliedProducts/') ?>" + catId,
            type: "GET",
            dataType: "json",
            success: function (response) {
                $loader.hide();
                if (response.length > 0) {
                    console.log(response);
                    $.each(response, function (i, prod) {
                         
                        let imageHtml = prod.base_image
                            ? `<img src="${productImageBase}${prod.base_image}" width="80">`
                            : '';


                        $tableBody.append(`
                            <tr>
                                <td>${prod.name}</td>
                                <td>${prod.sku}</td>
                                <td class="${
    prod.status === 'approve'
        ? 'green-text'
        : prod.status === 'reject'
            ? 'red-text'
            : prod.status === 'pending'
                ? 'blue-text'
                : ''
}">
                                    ${prod.status === 'approve' 
                                        ? 'Approved' 
                                        : prod.status === 'pending' 
                                            ? 'Pending' 
                                            : prod.status === 'reject' 
                                                ? 'Rejected' 
                                                : prod.status}
                                </td>
                                <td><a class="link-purple" href="${base_url}seller/product/edit/${prod.id}">Edit</a></td>

                            </tr>
                        `);
                    });
                } else {
                    $tableBody.append(`<tr><td colspan="5" class="text-center">No products found</td></tr>`);
                }
            },
            error: function () {
                $loader.hide();
                $tableBody.append(`<tr><td colspan="5" class="text-center text-danger">Error loading products</td></tr>`);
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("productSearch");
    const productListContainer = document.getElementById("productListContainer");

    searchInput.addEventListener("keyup", function () {
        const searchText = this.value.toLowerCase();
        const items = productListContainer.querySelectorAll(".form-check");

        items.forEach(item => {
            const label = item.innerText.toLowerCase();
            if (label.includes(searchText)) {
                item.style.display = "";
            } else {
                item.style.display = "none";
            }
        });
    });
});


$(function() {
    $('#productList').multiselect({
        includeSelectAllOption: true,
        enableFiltering: true,
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        nonSelectedText: 'Select Products',
        numberDisplayed: 2,
        maxHeight: 300
    });
});
</script>
<script>

$(document).ready(function(){	

    // Toggle arrow icon on collapse show/hide

    $('.category-title').on('click', function(){

        $(this).toggleClass('collapsed');

    });

});

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const input = document.querySelector('.production-section');

  if (!input) return;

  input.addEventListener('input', function () {
    let cursorPos = this.selectionStart;

    let words = this.value.split(' ');

    words = words.map(word => {
      if (word.length === 0) return '';
      return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
    });

    this.value = words.join(' ');

    // keep cursor position
    this.setSelectionRange(cursorPos, cursorPos);
  });
});
</script>

<!-- <script>
    $(document).ready(function () {
    $('#productBlockForm').on('submit', function (e) {
        let isValid = true;

        // 1. Check if at least one product is selected
        let productCount = $(this).find('input[name="productList[]"]:checked').length;
        let $productDropdown = $(this).find('.custom-multiselect .dropdown-toggle').first();
        let $productError = $(this).find('.product-error-message');

        if (productCount === 0) {
            e.preventDefault();
            $productError.show();
            $productDropdown.addClass('border-danger');
            isValid = false;
        } else {
            $productError.hide();
            $productDropdown.removeClass('border-danger');
        }

        // 2. Check if at least one document is selected
        let documentCount = $(this).find('.document-checkbox:checked').length;
        if (documentCount === 0) {
            e.preventDefault();
            alert('<?= $this->lang->line('please_select_at_least_one_document'); ?>');
            isValid = false;
        }

        return isValid;
    });

    // Hide product error message and remove border automatically when a product is checked
    $(document).on('change', 'input[name="productList[]"]', function () {
        if ($('input[name="productList[]"]:checked').length > 0) {
            $('.product-error-message').hide();
            $('.custom-multiselect .dropdown-toggle').removeClass('border-danger');
        }
    });

    // Prevent dropdown from closing when clicking inside search or list
    $('.dropdown-menu').on('click', function (e) {
        e.stopPropagation();
    });
});
    </script> -->
<script>
$(document).ready(function () {

    // Validate every badge form
    $(document).on('submit', '.productBlockForm', function (e) {

        let $form = $(this);
        let isValid = true;

        // --------------------------------
        // 1. Product validation
        // --------------------------------
        let productCount = $form.find('input[name="productList[]"]:checked').length;
        let $productDropdown = $form.find('.custom-multiselect .dropdown-toggle').first();
        let $productError = $form.find('.product-error-message').first();

        if (productCount === 0) {

            e.preventDefault();

            $productError.show();
            $productDropdown.addClass('border-danger');

            isValid = false;

        } else {

            $productError.hide();
            $productDropdown.removeClass('border-danger');
        }


        // --------------------------------
        // 2. Document validation
        // --------------------------------
        let documentCount = $form.find('.document-checkbox:checked').length;
        let $documentDropdown = $form.find('.custom-multiselect .dropdown-toggle').eq(1);

        if (documentCount === 0) {

            e.preventDefault();

            // SHOW ALERT
            alert('<?= $this->lang->line('please_select_at_least_one_document'); ?>');

            $documentDropdown.addClass('border-danger');

            isValid = false;

        } 


        // --------------------------------
        // 3. Terms validation
        // --------------------------------
        let $termsInput = $form.find('input[name="terms"]');

        if ($termsInput.length && $termsInput.val().trim() === '') {

            e.preventDefault();

            $termsInput.addClass('border-danger is-invalid');

            alert('<?= $this->lang->line("please_enter_terms_name"); ?>');

            isValid = false;

        } else {

            $termsInput.removeClass('border-danger is-invalid');
        }


        return isValid;
    });


    // --------------------------------
    // Clear product error
    // --------------------------------
    $(document).on('change', '.productBlockForm input[name="productList[]"]', function () {

        let $form = $(this).closest('.productBlockForm');

        if ($form.find('input[name="productList[]"]:checked').length > 0) {

            $form.find('.product-error-message').first().hide();

            $form.find('.custom-multiselect .dropdown-toggle')
                .first()
                .removeClass('border-danger');
        }
    });


    // --------------------------------
    // Clear document error
    // --------------------------------
    $(document).on('change', '.productBlockForm .document-checkbox', function () {

        let $form = $(this).closest('.productBlockForm');

        if ($form.find('.document-checkbox:checked').length > 0) {

            $form.find('.custom-multiselect .dropdown-toggle')
                .eq(1)
                .removeClass('border-danger');
        }
    });


    // --------------------------------
    // Clear terms error
    // --------------------------------
    $(document).on('input', '.productBlockForm input[name="terms"]', function () {

        if ($(this).val().trim() !== '') {

            $(this).removeClass('border-danger is-invalid');
        }
    });


    // --------------------------------
    // Prevent dropdown from closing
    // --------------------------------
    $('.dropdown-menu').on('click', function (e) {
        e.stopPropagation();
    });

});
</script>