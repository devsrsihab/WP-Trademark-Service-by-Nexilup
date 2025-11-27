jQuery(function ($) {
  function runFilter() {
    let data = {
      action: "tm_filter_country_table",
      nonce: tm_ajax.nonce,
      country: $("#tm-country").val(),
      type: $("input[name='type']:checked").val(),
    };

    $.post(tm_ajax.ajax_url, data, function (res) {
      if (res.success) {
        $(".tm-scroll-x").html(res.data.html); // Replace table HTML
      } else {
        console.log("Error:", res);
      }
    });
  }

  // Click search button
  $("#tm-filter-form").on("submit", function (e) {
    e.preventDefault();
    runFilter();
  });

  // Auto filter when selecting trademark type
  $("input[name='type']").on("change", function () {
    runFilter();
  });

  // Auto filter when selecting country
  $("#tm-country").on("change", function () {
    runFilter();
  });
});
