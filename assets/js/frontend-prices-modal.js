(function ($) {
  "use strict";

  /* -------------------------------------------------
     GLOBAL HELPERS
  -------------------------------------------------- */

  function openModal($modal) {
    if (!$modal.length) return;

    $("body").addClass("tm-modal-open");
    $modal.attr("aria-hidden", "false").addClass("tm-modal-visible");
  }

  function closeModal($modal) {
    if (!$modal.length) return;

    $modal.attr("aria-hidden", "true").removeClass("tm-modal-visible");

    // Remove body lock only when ALL modals are closed
    if ($(".tm-modal-visible").length === 0) {
      $("body").removeClass("tm-modal-open");
    }
  }

  function setActiveType(type) {
    if (!type) type = "combined";

    // Tabs
    $(".tm-prices-tab").removeClass("is-active");
    $('.tm-prices-tab[data-type="' + type + '"]').addClass("is-active");

    // Panels
    $(".tm-prices-panel").removeClass("is-active");
    $('.tm-prices-panel[data-type="' + type + '"]').addClass("is-active");
  }

  /* -------------------------------------------------
     DOCUMENT READY
  -------------------------------------------------- */
  $(document).ready(function () {
    const $pricesModal = $("#tm-prices-modal");
    const $serviceModal = $("#tm-service-conditions-modal");

    /* -------------------------------------------------
       OPEN PRICES MODAL (clicked from Step sections)
    -------------------------------------------------- */
    $(document).on(
      "click",
      ".tm-open-prices-modal, .tm-nominus-prices-link",
      function (e) {
        e.preventDefault();

        // Get trademark type if button includes it
        const type = $(this).data("type") || "combined";

        setActiveType(type);
        openModal($pricesModal);
      }
    );

    /* -------------------------------------------------
       TAB SWITCHING INSIDE PRICES MODAL
    -------------------------------------------------- */
    $(document).on("click", ".tm-prices-tab", function () {
      const type = $(this).data("type");
      setActiveType(type);
    });

    /* -------------------------------------------------
       OPEN SERVICE CONDITIONS FROM PRICES MODAL
    -------------------------------------------------- */
    $(document).on("click", ".tm-open-service-conditions", function (e) {
      e.preventDefault();
      // closeModal($pricesModal);
      // openModal($serviceModal);
      openModal($serviceModal);
    });

    /* -------------------------------------------------
       CLOSE SERVICE CONDITIONS (OK button)
    -------------------------------------------------- */
    $(document).on("click", ".tm-close-service-conditions", function () {
      closeModal($serviceModal);
    });

    /* -------------------------------------------------
       CLOSE MODALS (Backdrop & X button)
    -------------------------------------------------- */
    $(document).on("click", ".tm-modal-backdrop, .tm-modal-close", function () {
      const $modal = $(this).closest(".tm-modal");
      closeModal($modal);
    });

    /* -------------------------------------------------
       ESC KEY CLOSE
    -------------------------------------------------- */
    $(document).on("keyup", function (e) {
      if (e.key === "Escape") {
        closeModal($pricesModal);
        closeModal($serviceModal);
      }
    });
  });
})(jQuery);
