(function ($) {
  "use strict";

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

  $("#tm-step3-next").on("click", function () {
    const name = $("#tm-owner-name").val().trim();
    const email = $("#tm-owner-email").val().trim();
    const addr = $("#tm-owner-address").val().trim();
    const city = $("#tm-owner-city").val().trim();
    const country = $("#tm-owner-country").val();

    if (!name || !email || !addr || !city) {
      alert("Please complete all owner fields.");
      return;
    }

    let state = getFormState();
    state.owner = {
      name,
      email,
      address: addr,
      city,
      country,
    };

    saveFormState(state);

    const params = new URLSearchParams(window.location.search);
    params.set("step", "4");
    window.location.href = window.location.pathname + "?" + params.toString();
  });
})(jQuery);
