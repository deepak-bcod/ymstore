</div>
</div>



<div id="FBCUserCommonModal" class="modal fade" tabindex="" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true"  data-backdrop="static" data-keyboard="false" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="fullWidthModalLabel">Modal Heading</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <center><div class="spinner-border text-primary" role="status"></div></center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div><!-- /.modal-content -->
        <input type="hidden" name="booklist_item_id" id="booklist_item_id" value="">
        <input type="hidden" name="subject_id" id="subject_id" value="">
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="FBCUserSecondaryModal" class="modal fade" tabindex="" role="dialog" aria-labelledby="fullWidthModalLabel" aria-hidden="true"  data-backdrop="static" data-keyboard="false" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modal-content-second">
            <div class="modal-header">
                <h4 class="modal-title" id="fullWidthModalLabel">Modal Heading</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <center><div class="spinner-border text-primary" role="status"></div></center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div><!-- /.modal-content -->
        <input type="hidden" name="booklist_item_id" id="booklist_item_id" value="">
        <input type="hidden" name="subject_id" id="subject_id" value="">
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!--<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
      <script>window.jQuery || document.write('<script src="../assets/js/vendor/jquery.slim.min.js"><\/script>')</script><script src="../assets/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.9.0/feather.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js"></script>-->
		
		<script type="text/javascript">
		$(document).ready(function () {
			$( ".ms-options label" ).append( "<span class='check'></span>" );
		});
		</script>
        
		<script src="<?php echo SKIN_JS; ?>common.js?v=<?php echo CSSJS_VERSION; ?>"></script>
		
</body>
</html>
<?php
$CI =& get_instance();
$site_lang = $CI->session->userdata('site_lang') ?: 'english';
$CI->lang->load('datatables', $site_lang);
$CI->lang->load('content', $site_lang);
?>
<script>
var DT_LANG = {
    emptyTable: <?= json_encode(lang('datatable_empty_table') ?: ($site_lang == 'french' ? "Aucune donnée disponible dans le tableau" : "No data available in table")); ?>,
    zeroRecords: <?= json_encode(lang('datatable_zero_records') ?: ($site_lang == 'french' ? "Aucun enregistrement correspondant trouvé" : "No matching records found")); ?>,
    search: <?= json_encode(lang('datatable_search') ?: ($site_lang == 'french' ? "Rechercher" : "Search")); ?>,
    processing: <?= json_encode(lang('datatable_processing') ?: ($site_lang == 'french' ? "Traitement en cours..." : "Processing...")); ?>,
    info: <?= json_encode(lang('datatable_info') ?: ($site_lang == 'french' ? "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments" : "Showing _START_ to _END_ of _TOTAL_ entries")); ?>,
    infoEmpty: <?= json_encode(lang('datatable_info_empty') ?: ($site_lang == 'french' ? "Affichage de l'élément 0 à 0 sur 0 élément" : "Showing 0 to 0 of 0 entries")); ?>,
    infoFiltered: <?= json_encode(lang('datatable_info_filtered') ?: ($site_lang == 'french' ? "(filtré de _MAX_ éléments au total)" : "(filtered from _MAX_ total entries)")); ?>,
    lengthMenu: <?= json_encode(lang('datatable_length_menu') ?: ($site_lang == 'french' ? "Afficher _MENU_ entrées" : "Show _MENU_ entries")); ?>,
    searchPlaceholder: <?= json_encode(lang('datatable_search_placeholder') ?: ($site_lang == 'french' ? "Rechercher..." : "Search...")); ?>,
    paginate: {
        next: <?= json_encode(lang('datatable_paginate_next') ?: ($site_lang == 'french' ? "Suivant" : "Next")); ?>,
        previous: <?= json_encode(lang('datatable_paginate_previous') ?: ($site_lang == 'french' ? "Précédent" : "Previous")); ?>,
        first: <?= json_encode(lang('datatable_paginate_first') ?: ($site_lang == 'french' ? "Premier" : "First")); ?>,
        last: <?= json_encode(lang('datatable_paginate_last') ?: ($site_lang == 'french' ? "Dernier" : "Last")); ?>
    }
};

if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable) {
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            emptyTable: DT_LANG.emptyTable,
            zeroRecords: DT_LANG.zeroRecords,
            processing: DT_LANG.processing,
            info: DT_LANG.info,
            infoEmpty: DT_LANG.infoEmpty,
            infoFiltered: DT_LANG.infoFiltered,
            lengthMenu: DT_LANG.lengthMenu,
            search: DT_LANG.search || "",
            searchPlaceholder: DT_LANG.searchPlaceholder,
            paginate: {
                next: (DT_LANG.paginate && DT_LANG.paginate.next) ? DT_LANG.paginate.next : '<i class="fas fa-angle-right"></i>',
                previous: (DT_LANG.paginate && DT_LANG.paginate.previous) ? DT_LANG.paginate.previous : '<i class="fas fa-angle-left"></i>',
                first: (DT_LANG.paginate && DT_LANG.paginate.first) ? DT_LANG.paginate.first : 'Premier',
                last: (DT_LANG.paginate && DT_LANG.paginate.last) ? DT_LANG.paginate.last : 'Dernier'
            }
        }
    });
}
</script>