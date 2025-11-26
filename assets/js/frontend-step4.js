(function ($) {
  "use strict";

  function getFormState() {
    try {
      return JSON.parse(sessionStorage.getItem("tm_form") || "{}");
    } catch (e) {
      return {};
    }
  }

  function renderSummary() {
    const s = getFormState();
    if (!s || !s.country_iso || !s.trademark_type) {
      $("#tm-summary-view").html(
        "<p class='tm-error'>Session expired or data missing. Please restart the form.</p>"
      );
      return;
    }

    $("#tm-summary-view").html(`
      <div class="tm-summary-box">
        <h3>Trademark Summary</h3>
        <p><strong>Country:</strong> ${s.country_iso}</p>
        <p><strong>Step:</strong> ${s.service_step}</p>
        <p><strong>Type:</strong> ${s.trademark_type}</p>
        <p><strong>Mark Text:</strong> ${s.mark_text || ""}</p>
        <p><strong>Classes:</strong> ${s.classes || 1}</p>
        <p><strong>Goods / Services:</strong> ${s.goods || ""}</p>
        <hr>
        <h3>Owner</h3>
        ${
          s.owner
            ? `<p><strong>Name:</strong> ${s.owner.name}</p>
               <p><strong>Email:</strong> ${s.owner.email}</p>
               <p><strong>Address:</strong> ${s.owner.address}, ${s.owner.city}, ${s.owner.country}</p>`
            : "<p>No owner info found.</p>"
        }
        <hr>
        <h3>Total</h3>
        <p><strong>${(s.total_price || 0).toFixed(2)} ${
      s.currency || ""
    }</strong></p>
      </div>
    `);
  }

  $(document).ready(renderSummary);

  $("#tm-submit-order").on("click", function () {
    const s = getFormState();

    if (!s || !s.total_price || !s.currency) {
      alert("Price information missing. Please redo Step 2.");
      return;
    }

    $.post(
      TM_GLOBAL.ajax_url,
      {
        action: "tm_add_to_cart",
        nonce: TM_GLOBAL.nonce,
        country: s.country_iso,
        type: s.trademark_type,
        classes: s.classes || 1,
        total: s.total_price,
        currency: s.currency,
        owner: s.owner || {},
        goods: s.goods || "",
        logo: s.logo || "",
        steps: s || {},
      },
      function (resp) {
        if (!resp || !resp.success) {
          alert(
            (resp && resp.data && resp.data.message) || "Error sending to cart."
          );
          return;
        }
        window.location.href = resp.data.redirect;
      }
    );
  });
})(jQuery);
