(function ($) {
  "use strict";

  const countryISO = {
    afghanistan: "AF",
    albania: "AL",
    algeria: "DZ",
    andorra: "AD",
    angola: "AO",
    "antigua and barbuda": "AG",
    argentina: "AR",
    armenia: "AM",
    australia: "AU",
    austria: "AT",
    azerbaijan: "AZ",
    bahamas: "BS",
    bahrain: "BH",
    bangladesh: "BD",
    barbados: "BB",
    belarus: "BY",
    belgium: "BE",
    belize: "BZ",
    benin: "BJ",
    bhutan: "BT",
    bolivia: "BO",
    "bosnia and herzegovina": "BA",
    botswana: "BW",
    brazil: "BR",
    brunei: "BN",
    bulgaria: "BG",
    "burkina faso": "BF",
    burundi: "BI",
    "cabo verde": "CV",
    cambodia: "KH",
    cameroon: "CM",
    canada: "CA",
    "central african republic": "CF",
    chad: "TD",
    chile: "CL",
    china: "CN",
    colombia: "CO",
    comoros: "KM",
    congo: "CG",
    "costa rica": "CR",
    croatia: "HR",
    cuba: "CU",
    cyprus: "CY",
    "czech republic": "CZ",
    "democratic republic of the congo": "CD",
    denmark: "DK",
    djibouti: "DJ",
    dominica: "DM",
    "dominican republic": "DO",
    ecuador: "EC",
    egypt: "EG",
    "el salvador": "SV",
    "equatorial guinea": "GQ",
    eritrea: "ER",
    estonia: "EE",
    eswatini: "SZ",
    ethiopia: "ET",
    fiji: "FJ",
    finland: "FI",
    france: "FR",
    gabon: "GA",
    gambia: "GM",
    georgia: "GE",
    germany: "DE",
    ghana: "GH",
    greece: "GR",
    grenada: "GD",
    guatemala: "GT",
    guinea: "GN",
    "guinea-bissau": "GW",
    guyana: "GY",
    haiti: "HT",
    honduras: "HN",
    hungary: "HU",
    iceland: "IS",
    india: "IN",
    indonesia: "ID",
    iran: "IR",
    iraq: "IQ",
    ireland: "IE",
    israel: "IL",
    italy: "IT",
    "ivory coast": "CI",
    jamaica: "JM",
    japan: "JP",
    jordan: "JO",
    kazakhstan: "KZ",
    kenya: "KE",
    kiribati: "KI",
    kosovo: "XK",
    kuwait: "KW",
    kyrgyzstan: "KG",
    laos: "LA",
    latvia: "LV",
    lebanon: "LB",
    lesotho: "LS",
    liberia: "LR",
    libya: "LY",
    liechtenstein: "LI",
    lithuania: "LT",
    luxembourg: "LU",
    madagascar: "MG",
    malawi: "MW",
    malaysia: "MY",
    maldives: "MV",
    mali: "ML",
    malta: "MT",
    "marshall islands": "MH",
    mauritania: "MR",
    mauritius: "MU",
    mexico: "MX",
    micronesia: "FM",
    moldova: "MD",
    monaco: "MC",
    mongolia: "MN",
    montenegro: "ME",
    morocco: "MA",
    mozambique: "MZ",
    myanmar: "MM",
    namibia: "NA",
    nauru: "NR",
    nepal: "NP",
    netherlands: "NL",
    "new zealand": "NZ",
    nicaragua: "NI",
    niger: "NE",
    nigeria: "NG",
    "north korea": "KP",
    "north macedonia": "MK",
    norway: "NO",
    oman: "OM",
    pakistan: "PK",
    palau: "PW",
    panama: "PA",
    "papua new guinea": "PG",
    paraguay: "PY",
    peru: "PE",
    philippines: "PH",
    poland: "PL",
    portugal: "PT",
    qatar: "QA",
    romania: "RO",
    russia: "RU",
    rwanda: "RW",
    "saint kitts and nevis": "KN",
    "saint lucia": "LC",
    "saint vincent and the grenadines": "VC",
    samoa: "WS",
    "san marino": "SM",
    "sao tome and principe": "ST",
    "saudi arabia": "SA",
    senegal: "SN",
    serbia: "RS",
    seychelles: "SC",
    "sierra leone": "SL",
    singapore: "SG",
    slovakia: "SK",
    slovenia: "SI",
    "solomon islands": "SB",
    somalia: "SO",
    "south africa": "ZA",
    "south korea": "KR",
    "south sudan": "SS",
    spain: "ES",
    "sri lanka": "LK",
    sudan: "SD",
    suriname: "SR",
    sweden: "SE",
    switzerland: "CH",
    syria: "SY",
    taiwan: "TW",
    tajikistan: "TJ",
    tanzania: "TZ",
    thailand: "TH",
    "timor-leste": "TL",
    togo: "TG",
    tonga: "TO",
    "trinidad and tobago": "TT",
    tunisia: "TN",
    turkey: "TR",
    turkmenistan: "TM",
    tuvalu: "TV",
    uganda: "UG",
    ukraine: "UA",
    "united arab emirates": "AE",
    "united kingdom": "GB",
    "united states": "US",
    uruguay: "UY",
    uzbekistan: "UZ",
    vanuatu: "VU",
    "vatican city": "VA",
    venezuela: "VE",
    vietnam: "VN",
    yemen: "YE",
    zambia: "ZM",
    zimbabwe: "ZW",

    /* ---------- Special Territories ---------- */
    "hong kong": "HK",
    macau: "MO",
    "puerto rico": "PR",
    greenland: "GL",
    bermuda: "BM",
    curaçao: "CW",
    "faroe islands": "FO",
    gibraltar: "GI",
    guam: "GU",
    "french polynesia": "PF",
    "cayman islands": "KY",
    "american samoa": "AS",

    /* ---------- UK Sub-countries ---------- */
    england: "GB-ENG",
    scotland: "GB-SCT",
    wales: "GB-WLS",
    "northern ireland": "GB-NIR",
  };

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
  $("#tm-country-input").on("input", function () {
    const typed = $(this).val().trim().toLowerCase();

    if (countryISO[typed]) {
      $("#tm-iso-input").val(countryISO[typed]);
    } else {
      $("#tm-iso-input").val("");
    }
  });

  /* ========================================================
   ADD COUNTRY (AJAX) — FULL FIX
======================================================== */
  $("#tm-save-country").on("click", function () {
    const data = {
      action: "tm_add_country",
      nonce: tmCountriesNonce,

      name: $("#tm-country-input").val().trim(),
      iso: $("#tm-iso-input").val().trim(),

      madrid_member: $("#tm-is-madrid").val(),
      poa_required: $("#tm-poa-required").val(),
      multi_class: $("#tm-multiclass").val(),
      evidence_required: $("#tm-evidence").val(),

      registration_time: $("#tm-registration-time").val(),
      opposition_period: $("#tm-opposition").val(),
      protection_term: $("#tm-protection-term").val(),

      general_remarks: $("#tm-remark-type").val(),
      other_remarks: $("#tm-other-remarks").val(),

      belt_road: $("#tm-belt-road").val(),
    };

    if (!data.name || !data.iso) {
      alert("Please enter both country name and ISO code.");
      return;
    }

    $.post(tmCountriesAjax, data, function (response) {
      if (!response.success) {
        alert(response.data.message);
        return;
      }

      closeModal("#tm-add-modal");

      // reload the page
      return location.reload();

      //       const c = response.data.country;

      //       const row = `
      // <tr class="tm-row"
      //     data-id="${c.id}"
      //     data-name="${c.name}"
      //     data-iso="${c.iso}"
      //     data-madrid="${c.madrid_member}"
      //     data-registration="${c.registration_time}"
      //     data-opposition="${c.opposition_period}"
      //     data-poa="${c.poa_required}"
      //     data-multiclass="${c.multi_class}"
      //     data-evidence="${c.evidence_required}"
      //     data-protection="${c.protection_term}"
      //     data-remark="${c.general_remarks}"
      //     data-other="${c.other_remarks}"
      //     data-beltroad="${c.belt_and_road}"
      //     data-status="${c.status}"
      // >
      //     <td>${c.name}</td>
      //     <td>${c.iso}</td>
      //     <td>${c.madrid_member == 1 ? "Yes" : "No"}</td>
      //     <td>${c.opposition_period || "—"}</td>
      //     <td>${c.poa_required || "—"}</td>
      //     <td>${c.multi_class || "—"}</td>
      //     <td>${c.evidence_required || "—"}</td>
      //     <td>${c.protection_term || "—"}</td>
      //     <td>${c.belt_and_road == 1 ? "Yes" : "No"}</td>
      //     <td><span class="tm-status-active">Active</span></td>
      //     <td>
      //         <button class="button tm-edit">Edit</button>
      //         <button class="button tm-delete" data-id="${c.id}">Delete</button>
      //     </td>
      // </tr>
      // `;

      //       $("#tm-country-list").append(row);
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

  /* ================================================
   OPEN EDIT COUNTRY MODAL — ALL VALUES LOADED
================================================ */
  $(document).on("click", ".tm-edit", function () {
    const row = $(this).closest("tr");

    $("#tm-edit-id").val(row.data("id"));
    $("#tm-edit-name").val(row.data("name"));
    $("#tm-edit-iso").val(row.data("iso"));

    $("#tm-edit-is-madrid").val(row.data("madrid"));
    $("#tm-edit-poa-required").val(row.data("poa"));
    $("#tm-edit-multiclass").val(row.data("multi"));
    $("#tm-edit-evidence").val(row.data("evidence"));

    $("#tm-edit-registration-time").val(row.data("registration"));
    $("#tm-edit-opposition").val(row.data("opposition"));
    $("#tm-edit-protection-term").val(row.data("protection"));

    $("#tm-edit-remark-type").val(row.data("remark"));
    $("#tm-edit-other-remarks").val(row.data("other"));

    $("#tm-edit-belt-road").val(row.data("belt"));
    $("#tm-edit-status").val(row.data("status"));

    openModal("#tm-edit-modal");
  });

  /* ========================================================
       UPDATE COUNTRY
    ======================================================== */
  $("#tm-update-country").on("click", function () {
    $.ajax({
      url: tmCountriesAjax,
      type: "POST",
      data: {
        action: "tm_update_country",
        nonce: tmCountriesNonce,

        id: $("#tm-edit-id").val(),
        name: $("#tm-edit-name").val(),
        iso: $("#tm-edit-iso").val(),
        status: $("#tm-edit-status").val(),

        madrid_member: $("#tm-edit-is-madrid").val(),
        registration_time: $("#tm-edit-registration-time").val(),
        opposition_period: $("#tm-edit-opposition").val(),
        poa_required: $("#tm-edit-poa-required").val(),
        multi_class: $("#tm-edit-multiclass").val(),
        evidence_required: $("#tm-edit-evidence").val(),
        protection_term: $("#tm-edit-protection-term").val(),

        general_remarks: $("#tm-edit-remark-type").val(),
        other_remarks: $("#tm-edit-other-remarks").val(),
        belt_road: $("#tm-edit-belt-road").val(),
      },

      success: function (res) {
        if (!res.success) {
          alert(res.data.message);
          return;
        }

        location.reload(); // safest fix
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
