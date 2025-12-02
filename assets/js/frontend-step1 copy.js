(function ($) {
  "use strict";

  /* -----------------------------
   Helpers: Session Storage
  ------------------------------*/
  function getFormState() {
    try {
      return JSON.parse(sessionStorage.getItem("tm_form") || "{}");
    } catch (e) {
      return {};
    }
  }

  function saveFormState(data) {
    sessionStorage.setItem("tm_form", JSON.stringify(data));
  }

  /* -----------------------------
   Trademark Type Switch
  ------------------------------*/
  function setActiveType(type) {
    if (!type) type = "word";

    $(".tm-type-card").removeClass("is-active");
    $(".tm-type-card[data-type='" + type + "']")
      .addClass("is-active")
      .find("input[type='radio']")
      .prop("checked", true);

    // text input visible for word + combined
    if (type === "figurative") $("#tm-text-field").hide();
    else $("#tm-text-field").show();

    // logo input visible for figurative + combined
    if (type === "word") $("#tm-logo-field").hide();
    else $("#tm-logo-field").show();

    calcPrice();
  }

  $(document).on("click", ".tm-type-card", function () {
    setActiveType($(this).data("type"));
  });

  /* -----------------------------
   Logo Upload + Save to session
  ------------------------------*/
  const $uploadBox = $("#tm-upload-box");
  const $fileInput = $("#tm-logo-file");
  const $previewWrap = $("#tm-upload-preview");
  const $previewImg = $("#tm-logo-preview-img");

  function showPreview(file) {
    const url = URL.createObjectURL(file);
    $previewImg.attr("src", url);
    $(".tm-upload-inner").hide();
    $previewWrap.show();
  }

  function resetPreview() {
    $previewImg.attr("src", "");
    $previewWrap.hide();
    $(".tm-upload-inner").show();
    $fileInput.val("");

    let state = getFormState();
    state.logo = "";
    saveFormState(state);
  }

  $uploadBox.on("click keypress", function (e) {
    if (e.type === "keypress" && e.key !== "Enter") return;
    $fileInput.trigger("click");
  });

  $fileInput.on("change", function () {
    const file = this.files && this.files[0];
    if (!file) return;

    showPreview(file);

    const reader = new FileReader();
    reader.onload = function (e) {
      let state = getFormState();
      state.logo = e.target.result; // base64
      saveFormState(state);
    };
    reader.readAsDataURL(file);
  });

  $uploadBox.on("dragenter dragover", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $uploadBox.addClass("is-dragover");
  });

  $uploadBox.on("dragleave", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $uploadBox.removeClass("is-dragover");
  });

  $uploadBox.on("drop", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $uploadBox.removeClass("is-dragover");

    const file = e.originalEvent.dataTransfer.files[0];
    if (!file) return;

    $fileInput[0].files = e.originalEvent.dataTransfer.files;
    showPreview(file);

    const reader = new FileReader();
    reader.onload = function (ev) {
      let state = getFormState();
      state.logo = ev.target.result;
      saveFormState(state);
    };
    reader.readAsDataURL(file);
  });

  $("#tm-remove-logo").on("click", function (e) {
    e.preventDefault();
    resetPreview();
  });

  /* -----------------------------
   Price Calculation (Step 1)
  ------------------------------*/
  function calcPrice() {
    const type = $("input[name='tm-type']:checked").val();

    $.post(
      TM_GLOBAL.ajax_url,
      {
        action: "tm_calc_price",
        nonce: TM_GLOBAL.nonce,
        country: TM_GLOBAL.country_id,
        type,
        step: 1,
        classes: 1,
      },
      function (resp) {
        if (!resp || !resp.success) {
          $("#tm-price-summary").html(
            "<div class='tm-summary-box tm-error'>Price not available.</div>"
          );
          return;
        }

        $("#tm-price-summary").html(`
          <div class="tm-summary-box">
            <strong>Total:</strong> ${resp.data.total.toFixed(2)} ${
          resp.data.currency
        }
          </div>
        `);

        let s = getFormState();
        s.total_price = resp.data.total;
        s.currency = resp.data.currency;
        s.classes = 1;
        saveFormState(s);
      }
    );
  }

  /* -------------------------------------------------------
      Logo Upload (WP Media Upload)
  ------------------------------------------------------- */

  function showPreviewUrl(url) {
    $previewImg.attr("src", url);
    $(".tm-upload-inner").hide();
    $previewWrap.show();
  }

  function resetPreview() {
    $previewImg.attr("src", "");
    $previewWrap.hide();
    $(".tm-upload-inner").show();
    $fileInput.val("");

    let st = getFormState();
    st.logo_id = "";
    st.logo_url = "";
    saveFormState(st);
  }

  function uploadLogoToWP(file) {
    const fd = new FormData();
    fd.append("action", "tm_upload_logo");
    fd.append("nonce", TM_GLOBAL.nonce);
    fd.append("logo", file);

    $.ajax({
      url: TM_GLOBAL.ajax_url,
      type: "POST",
      data: fd,
      processData: false,
      contentType: false,
      success: function (resp) {
        if (!resp.success) {
          alert(resp.data.message || "Upload failed.");
          resetPreview();
          return;
        }

        let st = getFormState();
        st.logo_id = resp.data.id;
        st.logo_url = resp.data.url;
        saveFormState(st);

        showPreviewUrl(resp.data.url);
      },
      error: function () {
        alert("Upload error");
        resetPreview();
      },
    });
  }

  // STOP bubbling on file input (this fixes the infinite loop error)
  $("#tm-logo-file").on("click", function (e) {
    e.stopPropagation();
  });

  // Click = open file browser
  $("#tm-upload-box")
    .off("click")
    .on("click", function (e) {
      if ($(e.target).is("#tm-remove-logo")) return;
      $("#tm-logo-file").trigger("click");
    });

  $fileInput.on("change", function () {
    const file = this.files[0];
    if (file) uploadLogoToWP(file);
  });

  // Drag & Drop upload
  $uploadBox.on("dragenter dragover", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $uploadBox.addClass("is-dragover");
  });

  $uploadBox.on("dragleave", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $uploadBox.removeClass("is-dragover");
  });

  $uploadBox.on("drop", function (e) {
    e.preventDefault();
    e.stopPropagation();
    $uploadBox.removeClass("is-dragover");

    const file = e.originalEvent.dataTransfer.files[0];
    if (file) uploadLogoToWP(file);
  });

  $("#tm-remove-logo").on("click", function (e) {
    e.preventDefault();
    resetPreview();
  });

  /* -----------------------------
   Continue → Step 2
   + ADD TO CART in Step 1
  ------------------------------*/
  $("#tm-step1-next").on("click", function () {
    const type = getCurrentType();
    const text = $("#tm-text").length ? $("#tm-text").val().trim() : "";
    const tm_from = $("#tm_from").length ? $("#tm_from").val().trim() : "";
    const goods = $("#tm-goods").length ? $("#tm-goods").val().trim() : "";

    const classes = getCurrentClasses();

    const st = getFormState();

    const logo_id = st.logo_id || 0;
    const logo_url = st.logo_url || "";

    // VALIDATION
    if (type === "word" && !text) {
      return alert("Trademark name is required for Word Mark.");
    }
    if (type === "figurative" && !logo_url) {
      return alert("Logo is required for Figurative Mark.");
    }
    if (type === "combined") {
      if (!text) return alert("Trademark name is required.");
      if (!logo_url) return alert("Logo is required.");
    }

    // build payload
    let state = {};
    state.country_id = $("#tm-country-id").val();
    state.country_iso = $("#tm-country-iso").val();
    state.trademark_type = type;
    state.mark_text = text;
    state.tm_from = tm_from;
    state.goods = goods;
    state.classes = classes;
    state.logo_id = logo_id;
    state.logo_url = logo_url;

    // Word mark → remove image
    if (type === "word") {
      state.logo_id = 0;
      state.logo_url = "";
    }

    // Figurative → remove text
    if (type === "figurative") {
      state.mark_text = "";
    }

    saveFormState(state);

    $.post(
      TM_GLOBAL.ajax_url,
      {
        action: "tm_add_to_cart_step1",
        nonce: TM_GLOBAL.nonce,
        data: state,
      },
      function (resp) {
        if (resp && resp.success) {
          window.location.href =
            TM_GLOBAL.step2_url ||
            "/tm/trademark-registration/order-form?country=" +
              TM_GLOBAL.country_iso;
        } else {
          alert(resp?.data?.message || "Error adding to cart.");
        }
      }
    );
  });

  $(document).ready(function () {
    setActiveType($("input[name='tm-type']:checked").val() || "word");
  });
})(jQuery);
