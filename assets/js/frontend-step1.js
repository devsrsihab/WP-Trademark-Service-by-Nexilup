(function ($) {
  "use strict";

  /* -------------------------------------------------------
      Helpers: Session Storage
  ------------------------------------------------------- */
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

  /* -------------------------------------------------------
      Helpers
  ------------------------------------------------------- */
  function getCurrentType() {
    return $("input[name='tm-type']:checked").val() || "word";
  }

  function getCurrentStep() {
    const stepVal = $("#tm-step-number").val() || TM_GLOBAL.step_number || 1;
    return parseInt(stepVal, 10) || 1;
  }

  function getCurrentClasses() {
    const fromInput = parseInt($("#tm-class-count").val(), 10);
    if (!isNaN(fromInput) && fromInput > 0) return fromInput;

    const fromText = parseInt($(".tm-class-count").text(), 10);
    if (!isNaN(fromText) && fromText > 0) return fromText;

    const st = getFormState();
    if (st.classes && st.classes > 0) return parseInt(st.classes, 10);

    return 1;
  }

  function setClassCountUI(n) {
    n = Math.max(1, parseInt(n, 10) || 1);
    $("#tm-class-count").val(n);
    $(".tm-class-count").text(n);

    let st = getFormState();
    st.classes = n;
    saveFormState(st);
  }

  /* -------------------------------------------------------
      Price Calculation
  ------------------------------------------------------- */
  function calcPrice() {
    const type = getCurrentType();
    const step = getCurrentStep();
    const classes = getCurrentClasses();

    $.post(
      TM_GLOBAL.ajax_url,
      {
        action: "tm_calc_price",
        nonce: TM_GLOBAL.nonce,
        country: TM_GLOBAL.country_id,
        type,
        step,
        classes,
      },
      function (resp) {
        if (!resp || !resp.success) {
          $("#tm-price-summary").html(
            "<div class='tm-summary-box tm-error'>Price not available.</div>"
          );
          return;
        }

        const d = resp.data;

        $("#tm-price-summary").html(`
          <div class="tm-summary-box">
            <div class="tm-sum-row">
              <span>One class fee:</span>
              <strong>${d.one.toFixed(2)} ${d.currency}</strong>
            </div>
            <div class="tm-sum-row">
              <span>Additional class fee:</span>
              <strong>${d.add.toFixed(2)} ${d.currency}</strong>
            </div>
            <div class="tm-sum-row tm-sum-total">
              <span>Total (${d.classes} class${
          d.classes > 1 ? "es" : ""
        }):</span>
              <strong>${d.total.toFixed(2)} ${d.currency}</strong>
            </div>
          </div>
        `);

        let s = getFormState();
        s.total_price = d.total;
        s.currency = d.currency;
        s.classes = d.classes;
        saveFormState(s);
      }
    );
  }

  /* -------------------------------------------------------
      Trademark Type Switch
  ------------------------------------------------------- */
  function setActiveType(type) {
    if (!type) type = "word";

    $(".tm-type-card").removeClass("is-active");
    $(".tm-type-card[data-type='" + type + "']")
      .addClass("is-active")
      .find("input[type='radio']")
      .prop("checked", true);

    if (type === "figurative") $("#tm-text-field").hide();
    else $("#tm-text-field").show();

    if (type === "word") $("#tm-logo-field").hide();
    else $("#tm-logo-field").show();

    let st = getFormState();
    st.trademark_type = type;
    saveFormState(st);

    calcPrice();
  }

  $(document).on("click", ".tm-type-card", function () {
    setActiveType($(this).data("type"));
  });

  /* -------------------------------------------------------
      Class + / -
  ------------------------------------------------------- */
  $(document).on("click", "#tm-class-plus, .tm-class-plus", function () {
    const c = getCurrentClasses() + 1;
    setClassCountUI(c);
    calcPrice();
  });

  $(document).on("click", "#tm-class-minus, .tm-class-minus", function () {
    const c = Math.max(1, getCurrentClasses() - 1);
    setClassCountUI(c);
    calcPrice();
  });

  $(document).on("keyup change", "#tm-class-count", function () {
    let c = parseInt($(this).val(), 10);
    if (isNaN(c) || c < 1) c = 1;
    setClassCountUI(c);
    calcPrice();
  });

  /* -------------------------------------------------------
      Logo Upload (WP Media Upload)
  ------------------------------------------------------- */
  const $uploadBox = $("#tm-upload-box");
  const $fileInput = $("#tm-logo-file");
  const $previewWrap = $("#tm-upload-preview");
  const $previewImg = $("#tm-logo-preview-img");

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

  /* -------------------------------------------------------
      Continue → Step 2 (Add to cart step1)
  ------------------------------------------------------- */
  $("#tm-step1-next").on("click", function () {
    const type = getCurrentType();
    const text = $("#tm-text").val().trim();
    const tm_from = $("#tm_from").val().trim();
    const goods = $("#tm-goods").val().trim();
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

    let payload = {
      action: "tm_add_to_cart_step1",
      nonce: TM_GLOBAL.nonce,
    };

    Object.keys(state).forEach((key) => {
      payload[`data[${key}]`] = state[key];
    });

    $.post(TM_GLOBAL.ajax_url, payload, function (resp) {
      if (resp && resp.success) {
        window.location.href =
          TM_GLOBAL.step2_url ||
          "/tm/trademark-registration/order-form?country=" +
            TM_GLOBAL.country_iso;
      } else {
        alert(resp?.data?.message || "Error adding to cart.");
      }
    });
  });

  /* -------------------------------------------------------
      INIT
  ------------------------------------------------------- */
  $(document).ready(function () {
    const st = getFormState();
    if (st.classes) setClassCountUI(st.classes);

    setActiveType(getCurrentType());
    calcPrice();
  });
})(jQuery);
