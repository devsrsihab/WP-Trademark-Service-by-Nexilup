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

  function isAdditionalClassMode() {
    return parseInt(TM_GLOBAL.tm_additional_class, 10) === 1;
  }

  // function getCurrentStep() {
  //   return parseInt($("#tm-step-number").val(), 10) || 1;
  // }
  function getCurrentStep() {
    // If URL has tm_additional_class=1 → always use Step 2 pricing
    if (
      typeof TM_GLOBAL !== "undefined" &&
      parseInt(TM_GLOBAL.tm_additional_class, 10) === 1
    ) {
      return 2;
    }

    // Fallback: hidden input / default step 1
    return parseInt($("#tm-step-number").val(), 10) || 1;
  }

  function getCurrentClasses() {
    // ADDITIONAL CLASS MODE → count rows dynamically
    if (isAdditionalClassMode()) {
      const count = $("#tm-class-list .tm-class-row").length;
      return count > 0 ? count : 1;
    }

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
        let total = d.total;

        // pull fees from global PHP (set via wp_localize_script)
        // let priority_fee = TM_GLOBAL.priority_fee || 0;
        // let poa_fee = TM_GLOBAL.poa_fee || 0;

        let priority_fee = d.priority_claim_fee || 0;
        let poa_fee = d.poa_late_fee || 0;

        console.log("d.priority_claim_fee", d.priority_claim_fee);
        console.log("d.poa_late_fee", d.poa_late_fee);
        console.log("resp.data", resp.data);

        // get user selection
        let priority_selected = $("input[name='tm_priority']:checked").val();
        let poa_selected = $("input[name='tm_poa']:checked").val();

        /* -------------------------------------------------------
            STEP-2 PRICING LOGIC (tm_additional_class = 1)
           ------------------------------------------------------- */
        let firstClass = d.one;
        let extraClassFee = d.add;

        let extraCount = Math.max(0, classes - 1);
        total = firstClass + extraCount * extraClassFee;

        // Add Priority Fee
        if (priority_selected == "1" && priority_fee > 0) {
          total += priority_fee;
        }

        // Add POA Late Fee
        if (poa_selected === "late" && poa_fee > 0) {
          total += poa_fee;
        }

        $("#tm-price-summary").html(`
          <div class="tm-summary-box">

          <div class="tm-sum-row"><span>Base Total:</span>
              <strong>$${(firstClass + extraCount * extraClassFee).toFixed(
                2
              )} </strong>
          </div>

            ${
              priority_fee > 0
                ? `<div class="tm-sum-row"><span>Priority Claim:</span>
              <strong>$${
                priority_selected == "1" ? priority_fee.toFixed(2) : "0.00"
              } </strong></div>`
                : ""
            }

            ${
              poa_fee > 0
                ? `<div class="tm-sum-row"><span>POA Late Filing:</span>
              <strong>$${
                poa_selected == "late" ? poa_fee.toFixed(2) : "0.00"
              } </strong></div>`
                : ""
            }

            <div class="tm-sum-row tm-sum-total">
              <span>Grand Total:</span>
              <strong>$${total.toFixed(2)} </strong>
            </div>
          </div>
        `);

        let s = getFormState();
        // s.total_price = d.total;
        s.total_price = total;

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
  // $("#tm-step1-next").on("click", function (e) {
  //   e.preventDefault();
  //   console.log("[TM] Step1 Next clicked");

  //   const type = getCurrentType();
  //   const textField = $("#tm-text");
  //   const text = textField.length ? textField.val().trim() : "";
  //   const tm_from = $("#tm_from").length ? $("#tm_from").val().trim() : "";
  //   const goods = $("#tm-goods").length ? $("#tm-goods").val().trim() : "";

  //   const classes = getCurrentClasses();

  //   const st = getFormState();

  //   const logo_id = st.logo_id || 0;
  //   const logo_url = st.logo_url || "";

  //   // VALIDATION
  //   if (type === "word" && !text) {
  //     alert("Trademark name is required for Word Mark.");
  //     return;
  //   }
  //   if (type === "figurative" && !logo_url) {
  //     alert("Logo is required for Figurative Mark.");
  //     return;
  //   }
  //   if (type === "combined") {
  //     if (!text) {
  //       alert("Trademark name is required.");
  //       return;
  //     }
  //     if (!logo_url) {
  //       alert("Logo is required.");
  //       return;
  //     }
  //   }

  //   let state = {};
  //   state.country_id = $("#tm-country-id").val();
  //   state.country_iso = $("#tm-country-iso").val();

  //   state.trademark_type = type;
  //   state.mark_text = text;
  //   state.tm_from = tm_from;
  //   state.goods = goods;
  //   state.classes = classes;
  //   state.logo_id = logo_id;
  //   state.logo_url = logo_url;

  //   // Word mark → remove image
  //   if (type === "word") {
  //     state.logo_id = 0;
  //     state.logo_url = "";
  //   }

  //   // Figurative → remove text
  //   if (type === "figurative") {
  //     state.mark_text = "";
  //   }

  //   saveFormState(state);

  //   let payload = {
  //     action: "tm_add_to_cart_step1",
  //     nonce: TM_GLOBAL.nonce,
  //   };

  //   Object.keys(state).forEach((key) => {
  //     payload[`data[${key}]`] = state[key];
  //   });

  //   console.log("[TM] Sending tm_add_to_cart_step1 payload:", payload);

  //   $.post(TM_GLOBAL.ajax_url, payload, function (resp) {
  //     console.log("[TM] tm_add_to_cart_step1 response:", resp);

  //     if (resp && resp.success) {
  //       window.location.href =
  //         TM_GLOBAL.step2_url ||
  //         "/tm/trademark-registration/order-form?country=" +
  //           TM_GLOBAL.country_iso;
  //     } else {
  //       alert(
  //         (resp && resp.data && resp.data.message) ||
  //           "Error adding to cart (check console)."
  //       );
  //     }
  //   });
  // });

  $("#tm-step1-next").on("click", function (e) {
    e.preventDefault();

    const type = getCurrentType();
    const isExtra = isAdditionalClassMode();

    const text = $("#tm-text").length ? $("#tm-text").val().trim() : "";
    const tm_from = $("#tm_from").length ? $("#tm_from").val().trim() : "";
    const goods = $("#tm-goods").length ? $("#tm-goods").val().trim() : "";
    const classesCount = getCurrentClasses();

    const st = getFormState();
    const logo_id = st.logo_id || 0;
    const logo_url = st.logo_url || "";

    /* --- VALIDATION --- */
    if (type === "word" && !text) {
      alert("Trademark name is required for Word Mark.");
      return;
    }
    if (type === "figurative" && !logo_url) {
      alert("Logo is required for Figurative Mark.");
      return;
    }
    if (type === "combined") {
      if (!text) {
        alert("Trademark name is required.");
        return;
      }
      if (!logo_url) {
        alert("Logo is required.");
        return;
      }
    }

    /* --- BUILD STATE OBJECT --- */
    let state = {};
    state.country_id = $("#tm-country-id").val();
    state.country_iso = $("#tm-country-iso").val();

    state.trademark_type = type;
    state.mark_text = text;
    state.tm_from = tm_from;
    state.tm_additional_class = isExtra ? 1 : 0;

    state.classes = classesCount;
    state.logo_id = logo_id;
    state.logo_url = logo_url;

    /* FIX trademarks */
    if (type === "word") {
      state.logo_id = 0;
      state.logo_url = "";
    }
    if (type === "figurative") {
      state.mark_text = "";
    }

    /* ==================================
        EXTRA CLASS MODE
    ================================== */
    if (isExtra) {
      let classNumbers = [];
      let classDetails = [];

      $("#tm-class-list .tm-class-row").each(function () {
        const cls = $(this).find(".tm-class-select").val();
        const desc = $(this).find(".tm-class-desc").val().trim();

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

      // Priority + POA
      state.tm_priority = $("input[name='tm_priority']:checked").val() || "0";
      state.tm_poa = $("input[name='tm_poa']:checked").val() || "normal";
    } else {
      state.goods = goods;
    }

    saveFormState(state);

    /* ==================================
        SEND AJAX → ADD TO CART
    ================================== */
    let payload = {
      action: "tm_add_to_cart_step1",
      nonce: TM_GLOBAL.nonce,
    };

    // Proper array encoding
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

  /* -------------------------------------------------------
      INIT
  ------------------------------------------------------- */
  $(document).ready(function () {
    const st = getFormState();
    if (st.classes) setClassCountUI(st.classes);

    setActiveType(getCurrentType());
    calcPrice();
  });

  /* -------------------------------------------------------
      Dynamic Trademark Classes (UI only)
  ------------------------------------------------------- */
  $(document).on("click", "#tm-add-class", function () {
    const $list = $("#tm-class-list");
    const $first = $list.find(".tm-class-row").first();
    const $clone = $first.clone();

    $clone.find("select").val("1");
    $clone.find("textarea").val("");

    $list.append($clone);
    calcPrice(); // NEW
  });

  $(document).on("click", ".tm-class-remove", function (e) {
    e.preventDefault();
    const $rows = $("#tm-class-list .tm-class-row");

    if ($rows.length === 1) {
      // only one row left → just reset
      $rows.find("textarea").val("");
      $rows.find("select").val("1");
    } else {
      $(this).closest(".tm-class-row").remove();
    }

    calcPrice(); // NEW
  });

  /* Priority / POA cards active style */
  $(document).on("change", "input[name='tm_priority']", function () {
    calcPrice();
    $("input[name='tm_priority']")
      .closest(".tm-choice-card")
      .removeClass("is-active");
    $(this).closest(".tm-choice-card").addClass("is-active");
  });

  $(document).on("change", "input[name='tm_poa']", function () {
    calcPrice();
    $("input[name='tm_poa']")
      .closest(".tm-choice-card")
      .removeClass("is-active");
    $(this).closest(".tm-choice-card").addClass("is-active");
  });

  // Prevent selecting the same class twice
  jQuery(document).on("change", ".tm-class-select", function () {
    const currentVal = jQuery(this).val();
    if (!currentVal) return;

    let count = 0;
    jQuery(".tm-class-select").each(function () {
      if (jQuery(this).val() === currentVal) {
        count++;
      }
    });

    if (count > 1) {
      alert(
        "You have already added this class. Please choose a different class."
      );
      jQuery(this).val(""); // reset the duplicate selection
    }
  });
})(jQuery);
