jQuery(function ($) {
  // open modal
  function openModal() {
    $("#tm-user-modal").fadeIn(200);
  }

  // close modal
  $(".tm-close").on("click", function () {
    $("#tm-user-modal").fadeOut(200);
  });

  $(".tm-close").on("click", function () {
    $("#tm-admin-trademark-modal").fadeOut(200);
  });

  // click "View"
  $(document).on("click", ".tm-user-view", function () {
    let id = $(this).data("id");
    let nonce = $(this).data("nonce");

    $("#tm-user-modal-body").html("Loading...");
    openModal();

    $.post(
      TM_USER_TRADEMARK_AJAX,
      {
        action: "tm_user_view_trademark",
        id: id,
        nonce: nonce,
      },
      function (res) {
        if (!res.success) {
          $("#tm-user-modal-body").html("<p>Error loading details.</p>");
          return;
        }

        $("#tm-user-modal-body").html(res.data.html);

        // load docs after modal content is ready
        loadDocs(id, nonce);
      }
    );
  });

  function loadDocs(id, nonce) {
    $.post(
      TM_USER_TRADEMARK_AJAX,
      {
        action: "tm_user_get_docs",
        id: id,
        nonce: nonce,
      },
      function (res) {
        if (res.success) {
          $("#tm-user-doc-list").html(res.data.html);
        }
      }
    );
  }
});
