(function ($) {
  "use strict";

  const ajaxUrl = TM_COND_AJAX;
  const nonce = TM_COND_NONCE;

  const $modal = $("#tm-condition-modal");
  const $title = $("#tm-condition-modal-title");

  function resetForm() {
    $("#tm-condition-id").val(0);
    $("#tm-condition-country").val("");

    const editor = tinyMCE.get("tm_condition_editor");
    if (editor) editor.setContent("");

    $("#tm_condition_editor").val("");
  }

  function openModal() {
    $modal.fadeIn(150);
  }
  function closeModal() {
    $modal.fadeOut(150, resetForm);
  }

  $("#tm-add-condition-btn").on("click", function () {
    resetForm();
    $title.text("Add Service Condition");
    openModal();
  });

  $(document).on("click", ".tm-modal-close", closeModal);

  // EDIT
  $(document).on("click", ".tm-edit-condition", function () {
    const id = $(this).data("id");

    $.post(
      ajaxUrl,
      {
        action: "tm_get_service_condition",
        nonce,
        id,
      },
      function (resp) {
        if (!resp.success) {
          alert(resp.data.message);
          return;
        }

        const d = resp.data;

        $("#tm-condition-id").val(d.id);
        $("#tm-condition-country").val(d.country_id);

        const editor = tinyMCE.get("tm_condition_editor");
        if (editor) editor.setContent(d.content);

        $("#tm_condition_editor").val(d.content);

        $title.text("Edit Service Condition");
        openModal();
      }
    );
  });

  // SAVE
  $("#tm-save-condition").on("click", function () {
    const id = $("#tm-condition-id").val();
    const country = $("#tm-condition-country").val();

    let content = "";
    const editor = tinyMCE.get("tm_condition_editor");
    content = editor ? editor.getContent() : $("#tm_condition_editor").val();

    if (!country) {
      alert("Country is required.");
      return;
    }

    $.post(
      ajaxUrl,
      {
        action: "tm_save_service_condition",
        nonce,
        id,
        country,
        content,
      },
      function (resp) {
        if (!resp.success) {
          alert(resp.data.message);
          return;
        }
        location.reload();
      }
    );
  });

  // DELETE
  $(document).on("click", ".tm-delete-condition", function () {
    if (!confirm("Delete this condition?")) return;

    const id = $(this).data("id");

    $.post(
      ajaxUrl,
      {
        action: "tm_delete_service_condition",
        nonce,
        id,
      },
      function (resp) {
        if (!resp.success) {
          alert(resp.data.message);
          return;
        }
        location.reload();
      }
    );
  });
})(jQuery);
