$(document).ready(function () {

$('#sku').change(function(){        
      var product_id= $("#product_id").val();
      // console.log(product_id);
      $.ajax({
          type: "POST",
          url: BASE_URL+"WebshopController/get_webshop_price",
          data:{"product_id":product_id},
          datatype : "json",
          success: function(response) 
          {
            var obj=$.parseJSON(response);
            // console.log(obj.webshop_price.webshop_price);
            $("#webshop_price").val(obj.webshop_price.webshop_price);
              
          }
      });
});


$('#special_price').change(function () {
    if (parseFloat($('#special_price').val()) >= parseFloat($('#webshop_price').val())) {
        swal({
            title: "",
            icon: "error",
            text: LANG.special_price_error,
            buttons: false
        },
        function () {
            location.reload();
        });
    }
});

$('#to_date').change(function () {
    if ($('#to_date').val() <= $('#from_date').val()) {
        swal({
            title: "",
            icon: "error",
            text: LANG.to_date_error,
            buttons: false
        },
        function () {
            location.reload();
        });
    }
});

   

  $("#special_pricing_form").on("submit", function (e) {
    e.preventDefault();
    $('#sku1, #from_date_error1, #special_price_error1, #to_date_error1').hide();

    if ($('#sku').val() == "") {
      $('#sku1').show();
      return false;
    }else if ($('#from_date').val() == "") {
      $('#from_date_error1').show();
      return false;
    }else if ($('#special_price').val() == "") {
      $('#special_price_error1').show();
      return false;
    }else if ($('#to_date').val() == "") {
      $('#to_date_error1').show();
      return false;
    }else{
      $('#sku1, #from_date_error1, #special_price_error1, #to_date_error1').hide();
      var formData = new FormData($("#special_pricing_form")[0]);
      $.ajax({
        type: "POST",
        url: $(this).attr("action"),
        dataType: "json",
        data: formData,
        processData: false,
        contentType: false,

        success: function (response) {
          console.log(response);
          if (response.flag == "1") {

            Swal.fire({
              title: "",
              text: response.msg,
              icon: "success",
              showConfirmButton: false,
              timer: 1500
            }).then(() => {
              window.location = response.redirect;
            });

          } else {

            console.log(response.msg);

            Swal.fire({
              title: "",
              html: response.msg,
              icon: "error",
              showConfirmButton: false,
              timer: 6000
            }).then(() => {
              location.reload();
            });

          }
        },
      });
    }
  });
});

// $("#special_pricing_form").on("submit", function (e) {
//   e.preventDefault();

//   if ($(this).valid()) {
//     var formData = new FormData($("#special_pricing_form")[0]);
//     $.ajax({
//       type: "POST",
//       url: $(this).attr("action"),
//       dataType: "json",
//       data: formData,
//       processData: false,
//       contentType: false,

//       success: function (response) {
//         // console.log(response);
//         alert(response.msg);
//         if (response.msg == "Success") {
//           swal(
//             {
//               title: "",
//               icon: "success",
//               text: response.msg,
//               buttons: false,
//             },
//             function () {
//               window.location = response.redirect;
//             }
//           );
//         } else {
//           console.log(response.msg);
//           swal(
//             {
//               title: "",
//               html: true,
//               icon: "error",
//               text: response.msg,
//               buttons: false,
//             },
//             function () {
//               location.reload();
//             }
//           );
//         }
//       },
//     });
//   }
// });
