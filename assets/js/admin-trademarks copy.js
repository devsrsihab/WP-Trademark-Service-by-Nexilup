(function ($) {
  $(document).on("click", ".tm-view-details", function () {
    let id = $(this).data("id");

    $("#tm-admin-trademark-modal").fadeIn(200);

    $.post(
      TM_ADMIN_TRADEMARK_AJAX,
      {
        action: "tm_admin_get_trademark",
        id: id,
        nonce: TM_ADMIN_TRADEMARK_NONCE,
      },
      function (res) {
        if (res.success) {
          $("#tm-trademark-detail-content").html(res.data.html);
        } else {
          alert(res.data.message);
        }
      }
    );
  });

  $(".tm-close").on("click", function () {
    $("#tm-admin-trademark-modal").fadeOut(200);
  });

  $(document).on("click", "#tm-admin-save-status", function () {
    let id = $("#tm-admin-status").data("id");
    let newStatus = $("#tm-admin-status").val();

    $.post(
      TM_ADMIN_TRADEMARK_AJAX,
      {
        action: "tm_admin_update_status",
        id: id,
        status: newStatus,
        nonce: TM_ADMIN_TRADEMARK_NONCE,
      },
      function (res) {
        if (!res.success) {
          alert(res.data.message);
          return;
        }

        $("#tm-admin-status-msg").html(
          "<span style='color:green;'>Status updated successfully!</span>"
        );

        // Update badge in table instantly
        $(`tr[data-id='${id}'] .tm-status-badge`)
          .removeClass()
          .addClass("tm-status-badge tm-status-" + newStatus)
          .text(newStatus.replace("_", " ").toUpperCase());
      }
    );
  });

  // Upload document
  $(document).on("click", "#tm-doc-upload-btn", function () {
    let file = $("#tm-doc-file")[0].files[0];
    let docType = $("#tm-doc-type").val();
    let id = $("#tm-admin-status").data("id");

    if (!file) {
      alert("Please select a file.");
      return;
    }

    let formData = new FormData();
    formData.append("action", "tm_admin_upload_doc");
    formData.append("nonce", TM_ADMIN_TRADEMARK_NONCE);
    formData.append("file", file);
    formData.append("type", docType);
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

  // load docs automatically when modal opens
  $(document).on("click", ".tm-view-details", function () {
    setTimeout(() => loadDocs($(this).data("id")), 300);
  });
})(jQuery);
