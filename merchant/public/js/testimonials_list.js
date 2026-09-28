console.log("loaded......... = Testimonials List");
$(document).ready(function () {
	if ($.fn.dataTable.isDataTable("#testimonialLists")) {
		table = $("#testimonialLists").DataTable({
			order: [
				[0, "desc"],
				[1, "desc"],
			],
			pageLength: 20,
			lengthMenu: [
				[5, 10, 20, -1],
				[5, 10, 20, "All"],
			],
			searching: true,
			info: false,
			lengthChange: true,
			language: {
				emptyTable: DT_LANG.emptyTable,
				zeroRecords: DT_LANG.zeroRecords,
				paginate: {
					previous: '<i class="fas fa-angle-left"></i>',
					next: '<i class="fas fa-angle-right"></i>',
				},
				search: "",
				searchPlaceholder: DT_LANG.searchPlaceholder,
			},
		});
	} else {
		table = $("#testimonialLists").DataTable({
			serverSide: true,
			processing: true,
			scrollCollapse: true,
			language: {
				emptyTable: DT_LANG.emptyTable,
				zeroRecords: DT_LANG.zeroRecords,
				processing: DT_LANG.processing,
				paginate: {
					previous: '<i class="fas fa-angle-left"></i>',
					next: '<i class="fas fa-angle-right"></i>',
				},
				search: "",
				searchPlaceholder: DT_LANG.searchPlaceholder,
			},
			language: { search: "", searchPlaceholder: DT_LANG.searchPlaceholder },
			paging: true,
			searching: true,
			info: false,
			blengthChange: true,
			stateSave: true,
			order: [],
			pageLength: 10,
			iDisplayLength: 10,
			lengthMenu: [
				[10, 20, -1],
				[10, 20, "All"],
			],
			ajax: {
				url: BASE_URL + "TestimonialController/loadTestimonialsAjax",
				type: "POST",
				data: {},
			},
		});
	}
});
