(function ($) {
  "use strict";

  const ajaxUrl = TM_COND_AJAX;
  const nonce = TM_COND_NONCE;

  const $modal = $("#tm-condition-modal");
  const $title = $("#tm-condition-modal-title");

  function resetForm() {
    $("#tm-condition-id").val(0);
    $("#tm-condition-country").val("");
    $("#tm-condition-step").val("1");
    $("#tm-condition-content").val("");
  }

  function openModal() {
    $modal.fadeIn(150);
  }

  function closeModal() {
    $modal.fadeOut(150, resetForm);
  }

  // Open modal for create
  $("#tm-add-condition-btn").on("click", function (e) {
    e.preventDefault();
    resetForm();
    $title.text("Add Service Condition");
    openModal();
  });

  // Close modal
  $(document).on("click", ".tm-modal-close", function (e) {
    e.preventDefault();
    closeModal();
  });

  // Edit
  $(document).on("click", ".tm-edit-condition", function (e) {
    e.preventDefault();

    const id = $(this).data("id");

    $.post(
      ajaxUrl,
      {
        action: "tm_get_service_condition",
        nonce: nonce,
        id: id,
      },
      function (resp) {
        if (!resp || !resp.success) {
          alert(
            resp && resp.data && resp.data.message
              ? resp.data.message
              : "Error loading condition."
          );
          return;
        }

        const d = resp.data;

        $("#tm-condition-id").val(d.id);
        $("#tm-condition-country").val(d.country_id);
        $("#tm-condition-step").val(d.step_number);
        $("#tm-condition-content").val(d.content);

        $title.text("Edit Service Condition");
        openModal();
      }
    );
  });

  // Save (create or update)
  $("#tm-save-condition").on("click", function (e) {
    e.preventDefault();

    const id = $("#tm-condition-id").val();
    const country = $("#tm-condition-country").val();
    const step = $("#tm-condition-step").val();
    const content = $("#tm-condition-content").val();

    if (!country || !step) {
      alert("Country and Step are required.");
      return;
    }

    $.post(
      ajaxUrl,
      {
        action: "tm_save_service_condition",
        nonce: nonce,
        id: id,
        country: country,
        step: step,
        content: content,
      },
      function (resp) {
        if (!resp || !resp.success) {
          alert(
            resp && resp.data && resp.data.message
              ? resp.data.message
              : "Error saving condition."
          );
          return;
        }

        // simplest: reload table
        location.reload();
      }
    );
  });

  // Delete
  $(document).on("click", ".tm-delete-condition", function (e) {
    e.preventDefault();

    if (!confirm("Delete this condition?")) return;

    const id = $(this).data("id");

    $.post(
      ajaxUrl,
      {
        action: "tm_delete_service_condition",
        nonce: nonce,
        id: id,
      },
      function (resp) {
        if (!resp || !resp.success) {
          alert(
            resp && resp.data && resp.data.message
              ? resp.data.message
              : "Error deleting condition."
          );
          return;
        }

        location.reload();
      }
    );
  });
})(jQuery);
