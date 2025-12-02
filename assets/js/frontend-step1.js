(function ($) {
  "use strict";

  /* ============================================================
      SESSION HELPERS
  ============================================================ */
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

  /* ============================================================
      BASIC HELPERS
  ============================================================ */
  function getCurrentType() {
    return $("input[name='tm-type']:checked").val() || "word";
  }

  function isAdditionalClassMode() {
    return parseInt(TM_GLOBAL.tm_additional_class, 10) === 1;
  }

  function getCurrentStep() {
    if (isAdditionalClassMode()) return 2;
    return parseInt($("#tm-step-number").val(), 10) || 1;
  }

  function getCurrentClasses() {
    // Additional Class mode
    if (isAdditionalClassMode()) {
      const rows = $("#tm-class-list .tm-class-row").length;
      return rows > 0 ? rows : 1;
    }

    // CLASS SELECTOR MODAL MODE
    const modalClasses = $("#tm-classes").val(); // Example: "28 - 29 - 40"
    if (modalClasses && modalClasses.trim() !== "") {
      return modalClasses.split("-").length;
    }

    const inputVal = parseInt($("#tm-class-count").val(), 10);
    if (!isNaN(inputVal) && inputVal > 0) return inputVal;

    return 1;
  }

  function setClassCountUI(n) {
    n = Math.max(1, n);
    $("#tm-class-count").val(n);
    $(".tm-class-count").text(n);
  }

  /* ============================================================
      TRADEMARK TYPE SWITCH
  ============================================================ */
  function setActiveType(type) {
    $(".tm-type-card").removeClass("is-active");
    $(".tm-type-card[data-type='" + type + "']")
      .addClass("is-active")
      .find("input[type='radio']")
      .prop("checked", true);

    if (type === "word") $("#tm-logo-field").hide();
    else $("#tm-logo-field").show();

    if (type === "figurative") $("#tm-text-field").hide();
    else $("#tm-text-field").show();

    let st = getFormState();
    st.trademark_type = type;
    saveFormState(st);

    calcPrice();
  }

  $(document).on("click", ".tm-type-card", function () {
    setActiveType($(this).data("type"));
  });

  /* ============================================================
      PRICE CALCULATION
  ============================================================ */
  function calcPrice() {
    const step = getCurrentStep();
    const type = getCurrentType();
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

        let first = parseFloat(d.one) || 0;
        let add = parseFloat(d.add) || 0;
        let extra = Math.max(0, classes - 1);

        let totalBase = first + extra * add;
        let total = totalBase;

        if (isAdditionalClassMode()) {
          let prSelected = $("input[name='tm_priority']:checked").val();
          let poaSelected = $("input[name='tm_poa']:checked").val();

          let prFee = parseFloat(d.priority_claim_fee || 0);
          let poaFee = parseFloat(d.poa_late_fee || 0);

          if (prSelected == "1") total += prFee;
          if (poaSelected == "late") total += poaFee;
        }

        /* Render Summary */
        $("#tm-price-summary").html(`
          <div class="tm-summary-box">
            <div class="tm-sum-row"><span>Base Total:</span>
            <strong>$${totalBase.toFixed(2)}</strong></div>

            ${
              isAdditionalClassMode()
                ? `
            <div class="tm-sum-row"><span>Priority Claim:</span>
            <strong>${
              $("input[name='tm_priority']:checked").val() == "1"
                ? "$" + (d.priority_claim_fee || 0).toFixed(2)
                : "$0.00"
            }</strong></div>

            <div class="tm-sum-row"><span>POA Late Filing:</span>
            <strong>${
              $("input[name='tm_poa']:checked").val() == "late"
                ? "$" + (d.poa_late_fee || 0).toFixed(2)
                : "$0.00"
            }</strong></div>
            `
                : ""
            }

            <div class="tm-sum-row tm-sum-total">
              <span>Grand Total:</span>
              <strong>$${total.toFixed(2)}</strong>
            </div>
          </div>
        `);

        let st = getFormState();
        st.total_price = total;
        st.currency = d.currency;
        st.classes = classes;
        saveFormState(st);
      }
    );
  }

  /* ============================================================
      LOGO UPLOAD
  ============================================================ */
  const $fileInput = $("#tm-logo-file");
  const $previewWrap = $("#tm-upload-preview");
  const $previewImg = $("#tm-logo-preview-img");

  $("#tm-logo-file").on("click", function (e) {
    e.stopPropagation();
  });

  $("#tm-upload-box").on("click", function (e) {
    if ($(e.target).is("#tm-remove-logo")) return;
    $("#tm-logo-file").trigger("click");
  });

  function showPreview(url) {
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

  $fileInput.on("change", function () {
    const file = this.files[0];
    if (!file) return;

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
          alert(resp.data.message || "Upload failed");
          resetPreview();
          return;
        }

        let st = getFormState();
        st.logo_id = resp.data.id;
        st.logo_url = resp.data.url;
        saveFormState(st);

        showPreview(resp.data.url);
      },
    });
  });

  $("#tm-remove-logo").on("click", function (e) {
    e.preventDefault();
    resetPreview();
  });

  /* ============================================================
      ADDITIONAL CLASSES (dropdown mode)
  ============================================================ */
  $(document).on("click", "#tm-add-class", function () {
    const $list = $("#tm-class-list");
    const $first = $list.find(".tm-class-row").first();
    const $clone = $first.clone();

    $clone.find("select").val("1");
    $clone.find("textarea").val("");

    $list.append($clone);
    calcPrice();
  });

  $(document).on("click", ".tm-class-remove", function (e) {
    e.preventDefault();

    let rows = $("#tm-class-list .tm-class-row");
    if (rows.length === 1) {
      rows.find("textarea").val("");
      rows.find("select").val("1");
    } else {
      $(this).closest(".tm-class-row").remove();
    }

    calcPrice();
  });

  $(document).on("change", ".tm-class-select", function () {
    const val = $(this).val();
    let count = 0;

    $(".tm-class-select").each(function () {
      if ($(this).val() === val) count++;
    });

    if (count > 1) {
      alert("This class is already selected.");
      $(this).val("");
    }
  });

  /* ============================================================
      SUBMIT → ADD TO CART
  ============================================================ */
  $("#tm-step1-next").on("click", function (e) {
    e.preventDefault();

    const type = getCurrentType();
    const st = getFormState();

    const text = $("#tm-text").val()?.trim() || "";
    const goods = $("#tm-goods").val()?.trim() || "";
    const isExtra = isAdditionalClassMode();
    const classes = getCurrentClasses();

    const logo_id = st.logo_id || 0;
    const logo_url = st.logo_url || "";

    if (type === "word" && !text) return alert("Trademark name required.");
    if (type === "figurative" && !logo_url) return alert("Logo required.");
    if (type === "combined" && !text) return alert("Name required.");

    let state = {
      country_id: TM_GLOBAL.country_id,
      country_iso: TM_GLOBAL.country_iso,
      trademark_type: type,
      tm_additional_class: isExtra ? 1 : 0,
      classes,
      mark_text: type === "figurative" ? "" : text,
      logo_id: type === "word" ? 0 : logo_id,
      logo_url: type === "word" ? "" : logo_url,
    };

    /* -------------------------------
         EXTRA CLASS MODE
    -------------------------------- */
    if (isExtra) {
      let classNumbers = [];
      let classDetails = [];

      $("#tm-class-list .tm-class-row").each(function () {
        const cls = $(this).find(".tm-class-select").val();
        const desc = ($(this).find(".tm-class-desc").val() || "").trim();

        if (cls) {
          classNumbers.push(cls);
          classDetails.push({
            class: cls,
            goods: desc,
          });
        }
      });

      if (classNumbers.length === 0) {
        alert("Please select at least one class.");
        return;
      }

      state.classes = classNumbers.length;
      state.class_list = classNumbers;
      state.class_details = classDetails;

      state.tm_priority = $("input[name='tm_priority']:checked").val() || "0";
      state.tm_poa = $("input[name='tm_poa']:checked").val() || "normal";
    }

    /* -------------------------------
         NORMAL STEP 1 MODE (modal)
    -------------------------------- */
    if (!isExtra) {
      const modalClasses = $("#tm-classes").val();

      if (modalClasses && modalClasses.trim() !== "") {
        const list = modalClasses
          .split("-")
          .map((i) => i.trim())
          .filter(Boolean);

        state.class_list = list;
        state.classes = list.length;
      }
    }

    saveFormState(state);

    /* AJAX */
    let payload = {
      action: "tm_add_to_cart_step1",
      nonce: TM_GLOBAL.nonce,
    };

    Object.keys(state).forEach((key) => {
      payload[`data[${key}]`] =
        typeof state[key] === "object"
          ? JSON.stringify(state[key])
          : state[key];
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

  document.addEventListener("tmUpdatePrice", function () {
    calcPrice();
  });

  /* ============================================================
      INIT
  ============================================================ */
  $(document).ready(function () {
    const st = getFormState();
    setActiveType(getCurrentType());
    calcPrice();
  });
})(jQuery);
