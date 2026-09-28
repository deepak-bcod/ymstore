<?php $this->load->view('common/fbc-user/header'); ?>

<main role="main" class="main-box col-md-9 ml-sm-auto col-lg-10 px-md-4 dashboard-page">
   <ul class="nav nav-pills">
      <li><a href="<?= base_url('attribute') ?>">Attributes</a></li>
      <li><a href="<?= base_url('attribute/add-attribute') ?>">Add New</a></li>
      <li class="active"><a href="<?= base_url('attribute/bulk-upload') ?>">Bulk Upload</a></li>
   </ul>

   <div class="main-inner min-height-480" style="padding: 20px 25px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-top: 15px;">
      
      <!-- Page Title & Overview -->
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 12px;">
         <div>
            <h1 class="head-name" style="font-size: 22px; font-weight: 700; color: #333; margin: 0;">Global Unique Attribute / Variant Bulk Upload</h1>
            <p style="color: #777; margin: 4px 0 0 0; font-size: 13px;">Create and maintain global master records for Attributes and Variants with strict global uniqueness on <code>attr_code</code>.</p>
         </div>
         <div>
            <a href="<?= base_url('attribute/download-sample-template') ?>" class="btn btn-outline-primary purple-btn-outline" style="border: 1px solid #7c3293; color: #7c3293; padding: 8px 16px; border-radius: 4px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; background: transparent;">
               <i class="fa fa-download"></i> Download Sample CSV
            </a>
         </div>
      </div>

      <!-- Upload Form Section -->
      <div class="row" style="margin-top: 20px;">
         <div class="col-md-6">
            <div class="card" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; background: #fdfdfd;">
               <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 15px; color: #2d3748;">
                  <i class="fa fa-cloud-upload" style="color: #7c3293; margin-right: 6px;"></i> Step 1: Upload Attribute / Variant Master File
               </h4>

               <form id="attributeBulkUploadForm" enctype="multipart/form-data">
                  <div class="form-group" style="margin-bottom: 18px;">
                     <label style="font-weight: 600; color: #4a5568; font-size: 13px;">Select File (.CSV or .XLSX) <span style="color: red;">*</span></label>
                     <div class="custom-file" style="position: relative;">
                        <input type="file" name="bulk_file" id="bulk_file" class="form-control" accept=".csv, .xlsx, .xls" required style="padding: 8px; border: 1px dashed #cbd5e0; background: #fff; height: auto;">
                     </div>
                     <small style="color: #718096; font-size: 12px; display: block; margin-top: 4px;">Supported formats: <b>.CSV, .XLSX</b> (Max size: 10MB)</small>
                  </div>

                  <div style="background: #f0fff4; border: 1px solid #c6f6d5; border-radius: 6px; padding: 12px; margin-bottom: 18px;">
                     <div style="font-size: 12.5px; color: #22543d; font-weight: 700; margin-bottom: 4px;">
                        <i class="fa fa-check-circle" style="color: #38a169;"></i> Global Master Rules
                     </div>
                     <p style="font-size: 11.5px; color: #2d3748; margin: 0; line-height: 1.4;">
                        • <code>attr_code</code> is globally unique across all attributes and variants.<br>
                        • Existing records are <b>never modified or duplicated</b> (strictly skipped).<br>
                        • No category dependency or category mapping required.
                     </p>
                  </div>

                  <div style="display: flex; gap: 10px; align-items: center;">
                     <button type="submit" id="btnValidateUpload" class="purple-btn" style="background: #7c3293; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa fa-check-circle"></i> Validate & Preview
                     </button>
                     <span id="uploadSpinner" style="display: none; color: #7c3293; font-size: 13px; font-weight: 600;">
                        <i class="fa fa-spinner fa-spin"></i> Validating global master records...
                     </span>
                  </div>
               </form>
            </div>
         </div>

         <!-- Instructions / Guide Box -->
         <div class="col-md-6">
            <div class="card" style="border: 1px solid #bee3f8; border-radius: 8px; padding: 18px; background: #ebf8ff;">
               <h4 style="font-size: 15px; font-weight: 700; color: #2b6cb0; margin-bottom: 10px;">
                  <i class="fa fa-info-circle" style="margin-right: 5px;"></i> Column Mapping & Types Guide
               </h4>
               <ul style="padding-left: 18px; font-size: 12.5px; color: #2d3748; line-height: 1.6; margin-bottom: 0;">
                  <li><b>Attribute Type:</b> <code>Attribute (1)</code> or <code>Variant (2)</code>.</li>
                  <li><b>Attribute Properties:</b>
                     <code>Text (1)</code>, <code>Textarea (2)</code>, <code>Date (3)</code>, <code>Yes/No (4)</code>, <code>Dropdown (5)</code>, <code>Multiselect (6)</code>.
                  </li>
                  <li><b>Options:</b> For Dropdown and Multiselect, provide comma-separated values in <i>Attribute Values (English)</i> and optional <i>Attribute Values (French)</i>.</li>
                  <li><b>Option Deduplication:</b> Existing options for an attribute are automatically skipped.</li>
                  <li><b>Strict No-Update:</b> If <code>attr_code</code> exists in <code>eav_attributes</code>, the record is safely skipped without altering existing values.</li>
               </ul>
            </div>
         </div>
      </div>

      <!-- Step 2: Validation Preview Screen (Hidden by default) -->
      <div id="validationPreviewSection" style="display: none; margin-top: 30px; border-top: 2px solid #edf2f7; padding-top: 25px;">
         
         <div class="d-flex justify-content-between align-items-center mb-3" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div>
               <h3 style="font-size: 18px; font-weight: 700; color: #2d3748; margin: 0;">
                  <i class="fa fa-table" style="color: #7c3293; margin-right: 6px;"></i> Step 2: Validation & Import Preview
               </h3>
               <p id="previewFileName" style="font-size: 13px; color: #718096; margin: 3px 0 0 0;"></p>
            </div>
            <div style="display: flex; gap: 10px;">
               <button type="button" id="btnDownloadErrorReport" class="btn btn-outline-danger btn-sm" style="border: 1px solid #e53e3e; color: #e53e3e; background: #fff; font-weight: 600; padding: 6px 14px; border-radius: 4px; display: none;">
                  <i class="fa fa-exclamation-triangle"></i> Download Error Report
               </button>
               <button type="button" id="btnConfirmImport" class="btn btn-success btn-sm" style="background: #38a169; border: none; color: #fff; font-weight: 700; padding: 8px 20px; border-radius: 4px; font-size: 14px; box-shadow: 0 2px 6px rgba(56,161,105,0.3);">
                  <i class="fa fa-upload"></i> Import Records Now
               </button>
            </div>
         </div>

         <!-- Summary Metric Badges -->
         <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-2 col-sm-4 col-xs-6" style="margin-bottom: 10px;">
               <div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; text-align: center;">
                  <div style="font-size: 11px; font-weight: 700; color: #718096; text-transform: uppercase;">Total Rows</div>
                  <div id="statTotal" style="font-size: 22px; font-weight: 800; color: #2d3748; margin-top: 4px;">0</div>
               </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6" style="margin-bottom: 10px;">
               <div style="background: #ebf8ff; border: 1px solid #bee3f8; border-radius: 6px; padding: 12px; text-align: center;">
                  <div style="font-size: 11px; font-weight: 700; color: #3182ce; text-transform: uppercase;">New Attributes</div>
                  <div id="statNewAttr" style="font-size: 22px; font-weight: 800; color: #2a4365; margin-top: 4px;">0</div>
                  <small style="color: #718096; font-size: 10.5px;">Existing: <span id="statExistAttr">0</span></small>
               </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6" style="margin-bottom: 10px;">
               <div style="background: #e6fffa; border: 1px solid #b2f5ea; border-radius: 6px; padding: 12px; text-align: center;">
                  <div style="font-size: 11px; font-weight: 700; color: #234e52; text-transform: uppercase;">New Variants</div>
                  <div id="statNewVar" style="font-size: 22px; font-weight: 800; color: #285e61; margin-top: 4px;">0</div>
                  <small style="color: #718096; font-size: 10.5px;">Existing: <span id="statExistVar">0</span></small>
               </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6" style="margin-bottom: 10px;">
               <div style="background: #faf5ff; border: 1px solid #e9d8fd; border-radius: 6px; padding: 12px; text-align: center;">
                  <div style="font-size: 11px; font-weight: 700; color: #805ad5; text-transform: uppercase;">New Options</div>
                  <div id="statNewOptions" style="font-size: 22px; font-weight: 800; color: #44337a; margin-top: 4px;">0</div>
                  <small style="color: #718096; font-size: 10.5px;">Existing: <span id="statExistOptions">0</span></small>
               </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6" style="margin-bottom: 10px;">
               <div style="background: #fffaf0; border: 1px solid #feebc8; border-radius: 6px; padding: 12px; text-align: center;">
                  <div style="font-size: 11px; font-weight: 700; color: #dd6b20; text-transform: uppercase;">Duplicate in File</div>
                  <div id="statDupUpload" style="font-size: 22px; font-weight: 800; color: #7b341e; margin-top: 4px;">0</div>
               </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6" style="margin-bottom: 10px;">
               <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; padding: 12px; text-align: center;">
                  <div style="font-size: 11px; font-weight: 700; color: #e53e3e; text-transform: uppercase;">Invalid Rows</div>
                  <div id="statInvalid" style="font-size: 22px; font-weight: 800; color: #742a2a; margin-top: 4px;">0</div>
               </div>
            </div>
         </div>

         <!-- Table Filter Tabs -->
         <div style="display: flex; gap: 8px; margin-bottom: 12px;">
            <button type="button" class="btn btn-sm btn-light preview-filter-btn active" data-filter="all" style="font-size: 12px; font-weight: 600; border: 1px solid #cbd5e0; padding: 4px 12px;">All Rows (<span id="tabCountAll">0</span>)</button>
            <button type="button" class="btn btn-sm btn-light preview-filter-btn" data-filter="valid" style="font-size: 12px; font-weight: 600; border: 1px solid #cbd5e0; padding: 4px 12px;">Ready to Import (<span id="tabCountValid">0</span>)</button>
            <button type="button" class="btn btn-sm btn-light preview-filter-btn" data-filter="error" style="font-size: 12px; font-weight: 600; border: 1px solid #cbd5e0; padding: 4px 12px;">Errors Only (<span id="tabCountErrors">0</span>)</button>
         </div>

         <!-- Validation Preview Table -->
         <div class="table-responsive" style="max-height: 480px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px;">
            <table class="table table-bordered table-hover" id="previewTable" style="margin-bottom: 0; font-size: 12.5px;">
               <thead style="background: #f7fafc; position: sticky; top: 0; z-index: 2;">
                  <tr>
                     <th style="width: 45px; text-align: center;">#</th>
                     <th>Attribute Name</th>
                     <th>French Name</th>
                     <th>Code</th>
                     <th>Type</th>
                     <th>Property</th>
                     <th>Values / Options</th>
                     <th>Status</th>
                     <th>Action / Result</th>
                  </tr>
               </thead>
               <tbody id="previewTableBody">
                  <!-- Populated via JS -->
               </tbody>
            </table>
         </div>

      </div>

      <!-- Step 3: Upload History Section -->
      <div style="margin-top: 40px; border-top: 2px solid #edf2f7; padding-top: 25px;">
         <h3 style="font-size: 17px; font-weight: 700; color: #2d3748; margin-bottom: 15px;">
            <i class="fa fa-history" style="color: #7c3293; margin-right: 6px;"></i> Import History
         </h3>

         <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 6px;">
            <table class="table table-bordered table-striped" style="margin-bottom: 0; font-size: 12.5px;">
               <thead style="background: #f7fafc;">
                  <tr>
                     <th style="width: 45px;">#</th>
                     <th>Upload Date</th>
                     <th>Uploaded By</th>
                     <th>File Name</th>
                     <th>Total</th>
                     <th>New Created</th>
                     <th>Existing Skipped</th>
                     <th>New Options</th>
                     <th>Failed</th>
                     <th>Status</th>
                     <th style="text-align: center; width: 140px;">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  <?php if (!empty($history)): ?>
                     <?php foreach ($history as $idx => $h): ?>
                        <tr>
                           <td><?= $idx + 1 ?></td>
                           <td><?= date('d M Y, h:i A', $h['created_at']) ?></td>
                           <td><?= htmlspecialchars($h['admin_name'] ?: ($h['admin_username'] ?: 'Admin #' . $h['created_by'])) ?></td>
                           <td><b><?= htmlspecialchars($h['file_name']) ?></b></td>
                           <td><?= $h['total_records'] ?></td>
                           <td><span class="badge" style="background: #3182ce; color: #fff;"><?= $h['new_attributes'] ?></span></td>
                           <td><span class="badge" style="background: #dd6b20; color: #fff;"><?= $h['existing_attributes'] ?></span></td>
                           <td><span class="badge" style="background: #805ad5; color: #fff;"><?= $h['new_options'] ?></span></td>
                           <td><span class="badge" style="background: <?= $h['failed_records'] > 0 ? '#e53e3e' : '#a0aec0' ?>; color: #fff;"><?= $h['failed_records'] ?></span></td>
                           <td>
                              <?php if ($h['status'] === 'Completed'): ?>
                                 <span class="badge" style="background: #c6f6d5; color: #22543d; padding: 4px 8px;">Completed</span>
                              <?php elseif ($h['status'] === 'Partial'): ?>
                                 <span class="badge" style="background: #feebc8; color: #7b341e; padding: 4px 8px;">Partial</span>
                              <?php else: ?>
                                 <span class="badge" style="background: #fed7d7; color: #742a2a; padding: 4px 8px;">Failed</span>
                              <?php endif; ?>
                           </td>
                           <td style="text-align: center;">
                              <?php if (!empty($h['error_log'])): ?>
                                 <a href="<?= base_url('attribute/download-error-report/' . $h['id']) ?>" class="btn btn-xs btn-outline-danger" title="Download Error Log" style="padding: 2px 6px; font-size: 11px; border: 1px solid #e53e3e; color: #e53e3e;">
                                    <i class="fa fa-download"></i> Errors
                                 </a>
                              <?php else: ?>
                                 <span style="color: #a0aec0; font-size: 11px;">-</span>
                              <?php endif; ?>
                              <button type="button" class="btn btn-xs btn-outline-secondary btn-delete-history" data-id="<?= $h['id'] ?>" title="Delete Record" style="padding: 2px 6px; font-size: 11px; margin-left: 4px;">
                                 <i class="fa fa-trash"></i>
                              </button>
                           </td>
                        </tr>
                     <?php endforeach; ?>
                  <?php else: ?>
                     <tr>
                        <td colspan="11" style="text-align: center; color: #a0aec0; padding: 20px;">No attribute bulk upload history records found.</td>
                     </tr>
                  <?php endif; ?>
               </tbody>
            </table>
         </div>
      </div>

   </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
   var validatedRowsCache = [];

   // Submit validation form
   $('#attributeBulkUploadForm').on('submit', function(e) {
      e.preventDefault();

      var fileInput = $('#bulk_file')[0];
      if (!fileInput.files || fileInput.files.length === 0) {
         alert('Please select a CSV or Excel file.');
         return;
      }

      var formData = new FormData(this);
      $('#btnValidateUpload').prop('disabled', true);
      $('#uploadSpinner').show();
      $('#validationPreviewSection').slideUp();

      $.ajax({
         url: '<?= base_url("attribute/validate-bulk-upload") ?>',
         type: 'POST',
         data: formData,
         processData: false,
         contentType: false,
         dataType: 'json',
         success: function(response) {
            $('#btnValidateUpload').prop('disabled', false);
            $('#uploadSpinner').hide();

            if (response.status) {
               validatedRowsCache = response.rows || [];
               renderValidationPreview(response);
            } else {
               alert('Validation Error: ' + (response.message || 'Unknown error occurred.'));
            }
         },
         error: function() {
            $('#btnValidateUpload').prop('disabled', false);
            $('#uploadSpinner').hide();
            alert('An error occurred during file upload and validation.');
         }
      });
   });

   function renderValidationPreview(response) {
      var summary = response.summary || {};
      $('#statTotal').text(summary.total_rows || 0);
      $('#statNewAttr').text(summary.new_attributes || 0);
      $('#statExistAttr').text(summary.existing_attributes || 0);
      $('#statNewVar').text(summary.new_variants || 0);
      $('#statExistVar').text(summary.existing_variants || 0);
      $('#statNewOptions').text(summary.new_options || 0);
      $('#statExistOptions').text(summary.existing_options || 0);
      $('#statDupUpload').text(summary.duplicate_rows_in_upload || 0);
      $('#statInvalid').text(summary.invalid_rows || 0);

      $('#tabCountAll').text(summary.total_rows || 0);
      $('#tabCountValid').text(summary.valid_rows || 0);
      $('#tabCountErrors').text(summary.invalid_rows || 0);

      $('#previewFileName').text('File: ' + response.filename);

      if ((summary.invalid_rows || 0) > 0) {
         $('#btnDownloadErrorReport').show();
      } else {
         $('#btnDownloadErrorReport').hide();
      }

      if ((summary.valid_rows || 0) > 0) {
         $('#btnConfirmImport').prop('disabled', false).show();
      } else {
         $('#btnConfirmImport').prop('disabled', true);
      }

      renderTableRows(response.rows || []);
      $('#validationPreviewSection').slideDown();
      $('html, body').animate({
         scrollTop: $('#validationPreviewSection').offset().top - 40
      }, 400);
   }

   function renderTableRows(rows) {
      var tbody = $('#previewTableBody');
      tbody.empty();

      if (!rows || rows.length === 0) {
         tbody.append('<tr><td colspan="9" style="text-align: center; color: #a0aec0; padding: 15px;">No rows to display.</td></tr>');
         return;
      }

      rows.forEach(function(row) {
         var rowClass = row.is_valid ? 'row-valid' : 'row-error';

         var typeBadge = row.attr_type == 2 
            ? '<span class="badge" style="background: #e6fffa; color: #234e52; border: 1px solid #b2f5ea;">Variant</span>'
            : '<span class="badge" style="background: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8;">Attribute</span>';

         var statusBadge = row.status == 1 
            ? '<span class="badge" style="background: #c6f6d5; color: #22543d;">Active</span>' 
            : '<span class="badge" style="background: #fed7d7; color: #742a2a;">Inactive</span>';

         // Options preview
         var optionsPreview = '';
         if (row.options && row.options.length > 0) {
            var optTags = row.options.map(function(opt) {
               var bg = opt.action === 'new' ? '#f0fff4; color: #22543d; border: 1px solid #c6f6d5;' : '#edf2f7; color: #718096;';
               return '<span class="badge" style="background:' + bg + ' margin: 1px;">' + escapeHtml(opt.name) + ' (' + opt.label + ')</span>';
            });
            optionsPreview = '<div style="display: flex; flex-wrap: wrap; gap: 2px;">' + optTags.join('') + '</div>';
         } else if (row.default_value) {
            optionsPreview = '<span style="color: #4a5568; font-size: 11.5px;">' + escapeHtml(row.default_value) + '</span>';
         } else {
            optionsPreview = '<span style="color: #a0aec0;">-</span>';
         }

         var resultCol = '';
         if (row.is_valid) {
            if (row.action === 'new') {
               resultCol = '<span style="color: #38a169; font-weight: 700;"><i class="fa fa-plus-circle"></i> Ready to Import (' + row.type_label + ')</span>';
            } else if (row.action === 'skip_existing') {
               resultCol = '<span style="color: #c05621; font-weight: 600;"><i class="fa fa-info-circle"></i> Already Exists - Skipped</span>';
            } else {
               resultCol = '<span style="color: #742a2a; font-weight: 600;"><i class="fa fa-ban"></i> Duplicate in Upload - Skipped</span>';
            }
         } else {
            resultCol = '<span style="color: #e53e3e; font-weight: 700;"><i class="fa fa-times-circle"></i> Error</span><br><small style="color: #e53e3e;">' + (row.errors || []).join('<br>') + '</small>';
         }

         var tr = $('<tr class="' + rowClass + '"></tr>');
         tr.append('<td style="text-align: center; font-weight: 600;">' + row.row_number + '</td>');
         tr.append('<td><b>' + escapeHtml(row.attr_name) + '</b></td>');
         tr.append('<td>' + escapeHtml(row.lang_attr_name || '-') + '</td>');
         tr.append('<td><code>' + escapeHtml(row.attr_code || '-') + '</code></td>');
         tr.append('<td>' + typeBadge + '</td>');
         tr.append('<td><span class="badge" style="background: #edf2f7; color: #4a5568;">' + (row.property_label || 'Text') + '</span></td>');
         tr.append('<td>' + optionsPreview + '</td>');
         tr.append('<td>' + statusBadge + '</td>');
         tr.append('<td>' + resultCol + '</td>');

         tbody.append(tr);
      });
   }

   // Filtering preview table
   $('.preview-filter-btn').on('click', function() {
      $('.preview-filter-btn').removeClass('active');
      $(this).addClass('active');

      var filter = $(this).data('filter');
      if (filter === 'valid') {
         $('#previewTableBody tr').hide();
         $('#previewTableBody tr.row-valid').show();
      } else if (filter === 'error') {
         $('#previewTableBody tr').hide();
         $('#previewTableBody tr.row-error').show();
      } else {
         $('#previewTableBody tr').show();
      }
   });

   // Confirm Import Button Click
   $('#btnConfirmImport').on('click', function() {
      if (!confirm('Are you sure you want to import the validated attributes and variants into the master database?')) {
         return;
      }

      var btn = $(this);
      btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Importing Master Records...');

      $.ajax({
         url: '<?= base_url("attribute/import-bulk-attributes") ?>',
         type: 'POST',
         dataType: 'json',
         success: function(res) {
            btn.prop('disabled', false).html('<i class="fa fa-upload"></i> Import Records Now');

            if (res.status) {
               var sm = res.summary || {};
               var msg = 'Import Completed Successfully!\n\n' +
                         '• New Attributes Created: ' + (sm.new_attributes_created || 0) + '\n' +
                         '• New Variants Created: ' + (sm.new_variants_created || 0) + '\n' +
                         '• Existing Attributes Skipped: ' + (sm.existing_attributes_skipped || 0) + '\n' +
                         '• Existing Variants Skipped: ' + (sm.existing_variants_skipped || 0) + '\n' +
                         '• Duplicate Rows Skipped: ' + (sm.duplicate_rows_skipped || 0) + '\n' +
                         '• New Options Created: ' + (sm.new_options_created || 0) + '\n' +
                         '• Existing Options Skipped: ' + (sm.existing_options_skipped || 0) + '\n' +
                         '• Failed Rows: ' + (sm.failed_rows || 0);

               alert(msg);
               window.location.reload();
            } else {
               alert('Import Failed: ' + (res.message || 'An error occurred during database import.'));
            }
         },
         error: function() {
            btn.prop('disabled', false).html('<i class="fa fa-upload"></i> Import Records Now');
            alert('An unexpected network error occurred while importing.');
         }
      });
   });

   // Download Error Report
   $('#btnDownloadErrorReport').on('click', function() {
      window.location.href = '<?= base_url("attribute/download-error-report") ?>';
   });

   // Delete History Record
   $('.btn-delete-history').on('click', function() {
      var id = $(this).data('id');
      if (!confirm('Delete this upload history record?')) {
         return;
      }

      var row = $(this).closest('tr');
      $.ajax({
         url: '<?= base_url("attribute/delete-bulk-upload-history") ?>',
         type: 'POST',
         data: { history_id: id },
         dataType: 'json',
         success: function(res) {
            if (res.status) {
               row.fadeOut(300, function() { $(this).remove(); });
            } else {
               alert(res.message || 'Could not delete history record.');
            }
         }
      });
   });

   function escapeHtml(text) {
      if (!text) return '';
      return String(text)
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
   }
});
</script>

<style>
.preview-filter-btn.active {
   background: #7c3293 !important;
   color: #fff !important;
   border-color: #7c3293 !important;
}
.row-error {
   background-color: #fff5f5 !important;
}
.row-valid {
   background-color: #fff !important;
}
.purple-btn {
   background: #7c3293;
   color: #fff;
   border: none;
   padding: 8px 18px;
   border-radius: 4px;
   font-weight: 600;
   transition: background 0.2s ease;
}
.purple-btn:hover {
   background: #652378;
   color: #fff;
}
</style>
