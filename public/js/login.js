$(document).ready(function () {
  $.validator.addMethod(
    "mypassword",
    function (value, element) {
      return (
        this.optional(element) ||
        (value.match(/[a-zA-Z]/) &&
          value.match(/[0-9]/) &&
          value.match(/[!@#$%^&*():;?_~+=]/))
      );
    },
    function() { return (typeof LOGIN_LANG !== 'undefined' && LOGIN_LANG.pass_invalid) ? LOGIN_LANG.pass_invalid : 'Password must contain at least one alphabetic, one numeric and one special character.'; }
  );

  $.validator.addMethod(
    "validateEmail",
    function (value, element) {
      return (
        this.optional(element) ||
        value.match(
          /^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i
        )
      );
    },
    function() { return (typeof LOGIN_LANG !== 'undefined' && LOGIN_LANG.email_invalid) ? LOGIN_LANG.email_invalid : 'Please enter valid email address.'; }
  );

  $("#sigin-form").validate({
    rules: {
      email: {
        required: true,
        validateEmail: true
      },
      login_password: {
        required: true
      }
    },

    messages: {
      
      login_password: {
        required: (typeof LOGIN_LANG !== 'undefined' && LOGIN_LANG.pass_required) ? LOGIN_LANG.pass_required : 'Password is required.'
      }
    },
    submitHandler: function (form) {
      //  $("#ajax-spinner").show();

      var fd = new FormData($("#sigin-form")[0]);

      $.ajax({
        url: form.action,
        type: "ajax",
        method: form.method,
        dataType: "json",
        data: fd,
        processData: false,
        contentType: false,
        success: function (response) {
          $("#ajax-spinner").hide();
          if (response.flag == 1) {
            swal({
              title: "",
              icon: "success",
              text: response.msg,
              buttons: false,
            });

            setTimeout(function () {
              window.location.href = response.redirect;
            }, 1000);
          } else {

            if(response.msg == 'Your profile is currently under review. Please check back later.'){
              grecaptcha.reset();
              swal({
                title: "",
                icon: "",
                text: response.msg,
                //buttons: false,
                showCancelButton: true,
              });
            }else{
              grecaptcha.reset();
              swal({
                title: "",
                icon: "error",
                text: response.msg,
                //buttons: false,
                showCancelButton: true,
              });
            }
           
          }
        },
      });
    },
  });
});
