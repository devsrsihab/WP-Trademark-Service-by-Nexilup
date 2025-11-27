jQuery(function ($) {
  $(".tm-service-link").click(function (e) {
    e.preventDefault();

    let countryID = $(this).data("country-id");

    $.post(
      tm_ajax.ajax_url,
      {
        action: "tm_load_service_conditions",
        nonce: tm_ajax.nonce,
        country_id: countryID,
      },
      function (res) {
        if (res.success) {
          $("#tm-service-modal .tm-service-modal-content").html(res.data.html);
          $("#tm-service-modal").addClass("active");
        }
      }
    );
  });

  $(".tm-service-modal-bg, .tm-service-modal-close").click(function () {
    $("#tm-service-modal").removeClass("active");
  });
});
