(function ($) {
  let modal = $("#tm-price-modal");
  let close = $("#tm-close-price");

  function openModal() {
    modal.fadeIn(150);
    $("body").addClass("modal-open");
  }

  function closeModal() {
    modal.fadeOut(150);
    $("body").removeClass("modal-open");
  }

  close.on("click", closeModal);

  $("#tm-add-price-btn").on("click", function () {
    $("#tm-modal-title").text("Add Country Price");
    $("#tm-edit-mode").val(0);
    $("#tm-price-country").val("");
    $("#tm-price-type").val("word");
    $("#tm-price-currency").val("USD");
    $("#priority_claim_fee").val(0);
    $("#poa_late_fee").val(0);

    $("#s1_one, #s1_add, #s2_one, #s2_add, #s3_one, #s3_add").val("");

    openModal();
  });

  /* ----------------------------
        SAVE PRICE (CREATE OR UPDATE)
    -----------------------------*/
  $("#tm-save-price").on("click", function () {
    let mode = $("#tm-edit-mode").val();
    let country = $("#tm-price-country").val();
    let type = $("#tm-price-type").val();
    let currency = $("#tm-price-currency").val();

    let s1_one = $("#s1_one").val();
    let s1_add = $("#s1_add").val();
    let s2_one = $("#s2_one").val();
    let s2_add = $("#s2_add").val();
    let s3_one = $("#s3_one").val();
    let s3_add = $("#s3_add").val();

    if (!country) {
      alert("Please select a country");
      return;
    }

    $.post(
      TM_PRICE_AJAX,
      {
        action: "tm_save_country_price",
        nonce: TM_PRICE_NONCE,
        mode: mode,
        country: country,
        type: type,
        currency: currency,
        s1_one,
        s1_add,
        s2_one,
        s2_add,
        s3_one,
        s3_add,
        // NEW FIELDS
        priority_claim_fee: $("#priority_claim_fee").val(),
        poa_late_fee: $("#poa_late_fee").val(),
      },
      function (res) {
        if (!res.success) {
          alert(res.data.message);
          return;
        }

        location.reload();
      }
    );
  });

  /* ----------------------------
        DELETE PRICE
    -----------------------------*/
  $(document).on("click", ".tm-delete-price", function () {
    if (!confirm("Delete all prices for this country + type?")) return;

    let country = $(this).data("country");
    let type = $(this).data("type");

    $.post(
      TM_PRICE_AJAX,
      {
        action: "tm_delete_country_price",
        nonce: TM_PRICE_NONCE,
        country: country,
        type: type,
      },
      function (res) {
        if (!res.success) {
          alert(res.data.message);
          return;
        }

        location.reload();
      }
    );
  });

  /* ----------------------------
        EDIT PRICE LOAD
    -----------------------------*/
  $(document).on("click", ".tm-edit-price", function () {
    let country = $(this).data("country");
    let type = $(this).data("type");

    $.post(
      TM_PRICE_AJAX,
      {
        action: "tm_get_country_price",
        nonce: TM_PRICE_NONCE,
        country: country,
        type: type,
      },
      function (res) {
        if (!res.success) {
          alert(res.data.message);
          return;
        }

        let d = res.data;

        $("#tm-edit-mode").val(1);
        $("#tm-modal-title").text("Edit Country Price");
        $("#tm-price-country").val(country);
        $("#tm-price-type").val(type);
        $("#tm-price-currency").val(d.currency);

        $("#s1_one").val(d.s1_one);
        $("#s1_add").val(d.s1_add);
        $("#s2_one").val(d.s2_one);
        $("#s2_add").val(d.s2_add);
        $("#s3_one").val(d.s3_one);
        $("#s3_add").val(d.s3_add);

        // NEW FIELDS
        $("#priority_claim_fee").val(d.priority_claim_fee);
        $("#poa_late_fee").val(d.poa_late_fee);

        openModal();
      }
    );
  });
})(jQuery);
