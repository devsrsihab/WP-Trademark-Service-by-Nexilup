(function ($) {
  // OPEN MODAL
  $(document).on("click", ".tm-admin-view-btn", function () {
    let id = $(this).data("id");

    $(".tm-admin-modal-overlay").fadeIn(150);

    $("#tm-admin-modal-content").html("Loading…");

    $.post(
      TM_ADMIN_TRADEMARK_AJAX,
      {
        action: "tm_admin_get_trademark",
        id: id,
        nonce: TM_ADMIN_TRADEMARK_NONCE,
      },
      function (res) {
        if (res.success) {
          $("#tm-admin-modal-content").html(res.data.html);
        } else {
          $("#tm-admin-modal-content").html("<p>Error loading details.</p>");
        }
      }
    );

    // Load documents
    setTimeout(() => loadDocs(id), 300);
  });

  // CLOSE MODAL
  $(document).on("click", ".tm-admin-modal-close", function () {
    $(".tm-admin-modal-overlay").fadeOut(150);
  });

  // Update status
  $(document).on("click", "#tm-admin-save-status", function () {
    let id = $("#tm-admin-status").data("id");
    let status = $("#tm-admin-status").val();

    $.post(
      TM_ADMIN_TRADEMARK_AJAX,
      {
        action: "tm_admin_update_status",
        id: id,
        status: status,
        nonce: TM_ADMIN_TRADEMARK_NONCE,
      },
      function (res) {
        if (!res.success) {
          alert(res.data.message);
          return;
        }

        $("#tm-admin-status-msg").html(
          "<span style='color:green;'>Status updated!</span>"
        );

        // Update badge in table
        $(`tr[data-id='${id}'] .tm-status-badge`)
          .removeClass()
          .addClass("tm-status-badge tm-status-" + status)
          .text(status.replace("_", " ").toUpperCase());
      }
    );
  });

  // Upload Document
  $(document).on("click", "#tm-doc-upload-btn", function () {
    let file = $("#tm-doc-file")[0].files[0];
    let type = $("#tm-doc-type").val();
    let id = $("#tm-admin-status").data("id");

    if (!file) {
      alert("Select a file first.");
      return;
    }

    let formData = new FormData();
    formData.append("action", "tm_admin_upload_doc");
    formData.append("nonce", TM_ADMIN_TRADEMARK_NONCE);
    formData.append("file", file);
    formData.append("type", type);
    formData.append("id", id);

    $.ajax({
      url: TM_ADMIN_TRADEMARK_AJAX,
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        if (!res.success) {
          alert(res.data.message);
          return;
        }

        $("#tm-doc-msg").html("<span style='color:green;'>Uploaded!</span>");
        loadDocs(id);
      },
    });
  });

  function loadDocs(id) {
    $.post(
      TM_ADMIN_TRADEMARK_AJAX,
      {
        action: "tm_admin_get_docs",
        id: id,
        nonce: TM_ADMIN_TRADEMARK_NONCE,
      },
      function (res) {
        if (res.success) {
          $("#tm-admin-doc-list").html(res.data.html);
        } else {
          $("#tm-admin-doc-list").html("<p>No documents.</p>");
        }
      }
    );
  }
})(jQuery);
