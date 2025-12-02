(function ($) {
  "use strict";

  function refreshTotal(newTotalHtml) {
    $(".tm-summary-total").html("Total: " + newTotalHtml);
  }

  // ------------------------------
  // EDIT MODE OPEN
  // ------------------------------
  $(document).on("click", ".tm-edit-item", function () {
    const $card = $(this).closest(".tm-cart-card");

    $card.find(".tm-cart-view").hide();
    $card.find(".tm-cart-editbox").show();

    $card.find(".tm-edit-input").focus();

    $card
      .find(".tm-remove-item")
      .addClass("tm-item-show")
      .removeClass("tm-item-hide");

    $(this).addClass("tm-item-hide").removeClass("tm-item-show");
  });

  // ------------------------------
  // CANCEL EDIT MODE
  // ------------------------------
  $(document).on("click", ".tm-edit-cancel", function () {
    const $card = $(this).closest(".tm-cart-card");

    $card.find(".tm-cart-editbox").hide();
    $card.find(".tm-cart-view").show();

    $card
      .find(".tm-remove-item")
      .addClass("tm-item-hide")
      .removeClass("tm-item-show");

    $card
      .find(".tm-edit-item")
      .addClass("tm-item-show")
      .removeClass("tm-item-hide");
  });

  // ------------------------------
  // SAVE EDIT TITLE
  // ------------------------------
  $(document).on("click", ".tm-edit-save", function () {
    const $card = $(this).closest(".tm-cart-card");
    const cartKey = $card.data("cart-key");
    const newTitle = $card.find(".tm-edit-input").val().trim();

    if (!newTitle) {
      alert("Title cannot be empty.");
      return;
    }

    console.log("Saving title:", newTitle, "Cart key:", cartKey);

    $.post(
      TM_GLOBAL.ajax_url,
      {
        action: "tm_update_cart_title",
        nonce: TM_GLOBAL.nonce,
        cart_key: cartKey,
        title: newTitle,
      },
      function (resp) {
        console.log("Title save response:", resp);

        if (!resp || !resp.success) {
          alert(resp?.data?.message || "Update failed.");
          return;
        }

        $card.find(".tm-header-subtitle").text(newTitle);

        $card.find(".tm-cart-editbox").hide();
        $card.find(".tm-cart-view").show();

        refreshTotal(resp.data.total_html);
      }
    );
  });

  // ------------------------------
  // REMOVE ITEM
  // ------------------------------
  $(document).on("click", ".tm-remove-item", function () {
    if (!confirm("Remove this item?")) return;

    const $card = $(this).closest(".tm-cart-card");
    const cartKey = $card.data("cart-key");

    console.log("Removing cart item:", cartKey);

    $.post(
      TM_GLOBAL.ajax_url,
      {
        action: "tm_remove_cart_item",
        nonce: TM_GLOBAL.nonce,
        cart_key: cartKey,
      },
      function (resp) {
        console.log("Remove response:", resp);

        if (!resp || !resp.success) {
          alert(resp?.data?.message || "Remove failed.");
          return;
        }

        $card.fadeOut(200, function () {
          $(this).remove();

          if ($(".tm-cart-card").length === 0) {
            $(".tm-order-details-box").append(
              "<p class='tm-error'>You have no items in your shopping cart.</p>"
            );
          }
        });

        refreshTotal(resp.data.total_html);
      }
    );
  });

  // ------------------------------
  // PROCEED TO CHECKOUT
  // ------------------------------
  $("#tm-proceed-checkout").on("click", function () {
    let gateway = $("input[name='tm_payment_gateway']:checked").val();

    console.log("Checkout clicked. Gateway:", gateway);

    if (!gateway) {
      alert("Please select a payment method.");
      return;
    }

    const $btn = $(this);
    $btn.prop("disabled", true).text("Processing...");

    $.post(
      TM_GLOBAL.ajax_url,
      {
        action: "tm_place_order",
        nonce: TM_GLOBAL.nonce,
        gateway: gateway,
      },
      function (resp) {
        console.log("Place order response:", resp);

        if (!resp || !resp.success) {
          $btn.prop("disabled", false).text("Proceed to Checkout");
          alert(resp?.data?.message || "Order failed.");
          return;
        }

        if (resp.data.redirect) {
          window.location.href = resp.data.redirect;
        }
      }
    );
  });

  jQuery(function ($) {
    $("input[name='tm_payment_gateway']").on("change", function () {
      let gateway = $(this).val();

      $("#tm-payment-fields").html("<p>Loading payment details...</p>");

      $.post(
        tm_ajax.ajax_url,
        {
          action: "tm_load_payment_fields",
          gateway: gateway,
          nonce: tm_ajax.nonce,
        },
        function (res) {
          console.log("res", res);

          if (res.success) {
            $("#tm-payment-fields").html(res.data.html);
          } else {
            $("#tm-payment-fields").html(
              "<p style='color:red'>" + res.data.message + "</p>"
            );
          }
        }
      );
    });
  });

  jQuery(document).ready(function ($) {
    $(document).on("change", "input[name='tm_payment_gateway']", function () {
      let gateway = $(this).val();

      $(".tm-gateway-fields").hide(); // hide all
      $("#tm-gateway-" + gateway).show(); // show selected
    });

    // Show first gateway on load
    let first = $("input[name='tm_payment_gateway']").first().val();
    if (first) {
      $("#tm-gateway-" + first).show();
    }
  });
})(jQuery);
