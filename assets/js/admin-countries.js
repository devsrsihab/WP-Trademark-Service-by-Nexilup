(function ($) {
  "use strict";

  /* ========================================================
       PREVENT DOUBLE LOADING
    ======================================================== */
  if (window.tmCountriesLoaded) {
    console.warn("admin-countries.js already loaded — skipped.");
    return;
  }
  window.tmCountriesLoaded = true;

  /* ========================================================
       MODAL HELPERS
    ======================================================== */
  function openModal(id) {
    $(id).fadeIn(200);
  }

  function closeModal(id) {
    $(id).fadeOut(200);
  }

  /* ========================================================
       OPEN / CLOSE MODALS
    ======================================================== */
  $("#tm-add-country-btn").on("click", function () {
    $("#tm-country-select").val("");
    $("#tm-iso-input").val("");
    openModal("#tm-add-modal");
  });

  $("#tm-close-add").on("click", function () {
    closeModal("#tm-add-modal");
  });

  $("#tm-close-edit").on("click", function () {
    closeModal("#tm-edit-modal");
  });

  $("#tm-bulk-add-btn").on("click", function () {
    $("#tm-bulk-input").val("");
    openModal("#tm-bulk-modal");
  });

  $("#tm-close-bulk").on("click", function () {
    closeModal("#tm-bulk-modal");
  });

  /* ========================================================
       AUTO-FILL ISO FROM DROPDOWN
    ======================================================== */
  $("#tm-country-select").on("change", function () {
    const selected = $(this).find(":selected");
    $("#tm-iso-input").val(selected.data("iso") || "");
  });

  /* ========================================================
       ADD COUNTRY (AJAX)
    ======================================================== */
  $("#tm-save-country").on("click", function () {
    const country = $("#tm-country-select").val();
    const iso = $("#tm-iso-input").val();

    if (!country || !iso) {
      alert("Please select a valid country.");
      return;
    }

    $.ajax({
      url: tmCountriesAjax,
      type: "POST",
      data: {
        action: "tm_add_country",
        name: country,
        iso: iso,
        nonce: tmCountriesNonce,
      },
      success: function (response) {
        if (!response.success) {
          alert(response.data.message);
          return;
        }

        const c = response.data.country;

        const row = `
                    <tr data-id="${c.id}">
                        <td><div class="tm-flag flag-shadowed-${c.iso}"></div></td>
                        <td>${c.name}</td>
                        <td>${c.iso}</td>
                        <td><span class="tm-status-active">Active</span></td>
                        <td>
                            <button class="button tm-edit"
                                data-id="${c.id}"
                                data-name="${c.name}"
                                data-iso="${c.iso}"
                                data-status="1">Edit</button>

                            <button class="button tm-delete" 
                                data-id="${c.id}">Delete</button>
                        </td>
                    </tr>
                `;

        $("#tm-country-list").append(row);

        closeModal("#tm-add-modal");
      },
    });
  });

  /* ========================================================
       DELETE COUNTRY
    ======================================================== */
  $(document).on("click", ".tm-delete", function () {
    if (!confirm("Are you sure you want to delete this country?")) return;

    const id = $(this).data("id");
    const row = $(this).closest("tr");

    $.post(
      tmCountriesAjax,
      {
        action: "tm_delete_country",
        id: id,
        nonce: tmCountriesNonce,
      },
      function (response) {
        if (!response.success) {
          alert(response.data.message);
          return;
        }

        row.fadeOut(200, function () {
          $(this).remove();
        });
      }
    );
  });

  /* ========================================================
       OPEN EDIT MODAL
    ======================================================== */
  $(document).on("click", ".tm-edit", function () {
    $("#tm-edit-id").val($(this).data("id"));
    $("#tm-edit-name").val($(this).data("name"));
    $("#tm-edit-iso").val($(this).data("iso"));
    $("#tm-edit-status").val($(this).data("status"));

    openModal("#tm-edit-modal");
  });

  /* ========================================================
       UPDATE COUNTRY
    ======================================================== */
  $("#tm-update-country").on("click", function () {
    const id = $("#tm-edit-id").val();
    const name = $("#tm-edit-name").val();
    const iso = $("#tm-edit-iso").val();
    const status = $("#tm-edit-status").val();

    if (!name || !iso) {
      alert("Both fields are required.");
      return;
    }

    $.ajax({
      url: tmCountriesAjax,
      type: "POST",
      data: {
        action: "tm_update_country",
        id,
        name,
        iso,
        status,
        nonce: tmCountriesNonce,
      },
      success: function (response) {
        if (!response.success) {
          alert(response.data.message);
          return;
        }

        const row = $(`tr[data-id='${id}']`);

        row
          .find("td:nth-child(1) .tm-flag")
          .attr("class", "tm-flag flag-shadowed-" + iso);

        row.find("td:nth-child(2)").text(name);
        row.find("td:nth-child(3)").text(iso);

        row
          .find("td:nth-child(4)")
          .html(
            status == 1
              ? '<span class="tm-status-active">Active</span>'
              : '<span class="tm-status-inactive">Inactive</span>'
          );

        closeModal("#tm-edit-modal");
      },
    });
  });

  /* ========================================================
       BULK IMPORT
    ======================================================== */
  $("#tm-bulk-save").on("click", function () {
    let jsonString = $("#tm-bulk-input").val().trim();

    if (!jsonString) {
      alert("Please enter valid JSON format.");
      return;
    }

    $.post(
      tmCountriesAjax,
      {
        action: "tm_bulk_add_countries",
        json: jsonString,
        nonce: tmCountriesNonce,
      },
      function (response) {
        if (!response.success) {
          alert(response.data.message);
          return;
        }

        const list = response.data.added;

        list.forEach((c) => {
          const row = `
                        <tr data-id="${c.id}">
                            <td><div class="tm-flag flag-shadowed-${c.iso}"></div></td>
                            <td>${c.name}</td>
                            <td>${c.iso}</td>
                            <td><span class="tm-status-active">Active</span></td>
                            <td>
                                <button class="button tm-edit"
                                    data-id="${c.id}"
                                    data-name="${c.name}"
                                    data-iso="${c.iso}"
                                    data-status="1">Edit</button>

                                <button class="button tm-delete" data-id="${c.id}">Delete</button>
                            </td>
                        </tr>
                    `;
          $("#tm-country-list").append(row);
        });

        closeModal("#tm-bulk-modal");
      }
    );
  });
})(jQuery);
