<?php
if ( ! defined('ABSPATH') ) exit;

$country_name = esc_html($country->country_name);
$country_iso  = esc_attr($country->iso_code);
$country_id   = (int) $country->id;

// store for override_dynamic_price fallback
update_option('tm_last_country_id', $country_id);
?>
<div class="tm-order-page tm-step2">

  <!-- Progress bar (Step2 active) -->
  <div class="tm-progress">
      <div class="tm-progress-line"></div>

      <div class="tm-progress-step is-active">
          <span class="dot"></span>
          <span>Trademark Information</span>
      </div>

      <div class="tm-progress-step is-active">
          <span class="dot"></span>
          <span>Confirm Order</span>
      </div>

      <div class="tm-progress-step">
          <span class="dot"></span>
          <span>Order Receipt</span>
      </div>
  </div>

  <div class="tm-confirm-layout">

    <!-- LEFT : ORDER DETAILS -->
    <div class="tm-confirm-card">

      <div class="tm-confirm-header">
        <h1>Confirm Your Order - <?php echo $country_name; ?></h1>
        <p>Please review your trademark details before proceeding to checkout.</p>
      </div>

      <div class="tm-order-details-box">
        <h3 class="tm-box-title">Order Details</h3>

        <?php if ( WC()->cart && !WC()->cart->is_empty() ): ?>
          
          <?php
          $grand_total = 0;
          ?>

          <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $item ):

            // var_dump($item['tm_logo_url']);
            // -------- GET TM DATA (nested OR flattened) ----------
            if (!empty($item['tm_data'])) {
                $tm = $item['tm_data'];
                $type        = strtolower($tm['type'] ?? '');
                $classes     = max(1, intval($tm['classes'] ?? 1));
                $step_num    = max(1, intval($tm['step'] ?? ($item['tm_step'] ?? 1)));
                $tm_title    = $tm['mark_text'] ?? '';
                $tm_from     = $tm['tm_from'] ?? '';
                $goods       = $tm['goods'] ?? '';
                $logo        = $tm['tm_logo_url'] ?? '';
                $fallback    = floatval($tm['total_price'] ?? 0);
            } else {
                $type        = strtolower($item['tm_type'] ?? '');
                $classes     = max(1, intval($item['tm_classes'] ?? 1));
                $step_num    = max(1, intval($item['tm_step'] ?? 1));
                $tm_title    = $item['tm_text'] ?? '';
                $tm_from     = $item['tm_from'] ?? '';
                $goods       = $item['tm_goods'] ?? '';
                $logo        = $item['tm_logo_url'] ?? '';
                $fallback    = floatval($item['tm_total'] ?? 0);
            }


            // label
            if ($type === 'word') $type_label = 'Word Mark';
            elseif ($type === 'figurative') $type_label = 'Figurative Mark';
            elseif ($type === 'combined') $type_label = 'Combined Mark';
            else $type_label = ucfirst($type);

            $mid_text = $type_label . " in " . $classes . " class" . ($classes > 1 ? "es" : "");

            // ------- DYNAMIC PRICE FETCH ----------
            // ------- USE WC CART LINE PRICE (already includes extra classes, priority, POA, etc.) ----------
            $price = floatval( $item['data']->get_price() );

            // Fallback to stored total if for some reason price is 0
            if (!$price && $fallback) {
                $price = $fallback;
            }

            $grand_total += $price;


            $editable_title = !empty($tm_from) ? $tm_from : $tm_title;
          ?>

<div class="tm-cart-card" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">

    <?php
      // Is this the "additional class" mode?
      $is_extra = intval($item['tm_additional_class'] ?? 0) === 1;

      // FIXED: use WC price OR tm_total_price fallback
      $line_price_from_cart = floatval(
          $item['data']->get_price()
          ?: ($item['tm_total_price'] ?? 0)
      );


        $logo = $item['tm_logo_url'] ?? ''; // <---- IMPORTANT

        $is_image_type = in_array($type, ['figurative', 'combined'], true);
        $has_image     = !empty($logo);

      // ---------------------------
      // EXTRA CLASS MODE
      // ---------------------------
      if ( $is_extra ) {

        // Class count & list from cart meta
        $class_count = intval( $item['tm_class_count'] ?? 1 );

        $class_list_json = stripslashes($item['tm_class_list']) ?? '[]';
        $class_list_arr  = json_decode( $class_list_json, true );
        if ( ! is_array( $class_list_arr ) ) {
            $class_list_arr = [];
        }

        // "10-19-17" style string
        $class_list_string = !empty($class_list_arr)
            ? implode( '-', $class_list_arr )
            : (string) $class_count;

        // Priority / POA selection from cart
        $tm_priority = $item['tm_priority'] ?? '0';
        $tm_poa      = $item['tm_poa'] ?? 'normal';

        // Country / type for DB lookup
        $country_id_for_price = intval( $item['country_id'] ?? 0 );
        $type_for_price       = sanitize_text_field( $item['tm_type'] ?? 'word' );

        // Get step=2 price row
        $row = TM_Country_Prices::get_price_row( $country_id_for_price, $type_for_price, 2 );

        $filing_price       = 0.0;
        $priority_fee_value = 0.0;
        $poa_fee_value      = 0.0;

        if ( $row ) {
            $one   = floatval( $row->price_one_class );
            $add   = floatval( $row->price_add_class );
            $extra = max( 0, $class_count - 1 );

            // Filing
            $filing_price = $one + ($extra * $add);

            // Priority / POA
            $priority_fee_value = floatval( $row->priority_claim_fee ?? 0 );
            $poa_fee_value      = floatval( $row->poa_late_fee ?? 0 );
        }

        // User selection
        $priority_price = ($tm_priority == '1') ? $priority_fee_value : 0.0;
        $poa_price      = ($tm_poa === 'late') ? $poa_fee_value : 0.0;

        // Final total
        $line_total = $filing_price + $priority_price + $poa_price;

        // Use WC fallback
        if ( !$line_total && $line_price_from_cart ) {
            $line_total = $line_price_from_cart;
        }

    }
    ?>

    <!-- remove icon -->
    <span class="tm-remove-item <?php echo $tm_title ? 'tm-item-hide' : '' ?>"
          title="Remove item"
          data-cart-key="<?php echo esc_attr($cart_item_key); ?>">×</span>

    <!-- edit icon -->
    <span class="tm-edit-item <?php echo $tm_title ? '' : 'tm-item-hide' ?>"
          title="Edit title"
          data-cart-key="<?php echo esc_attr($cart_item_key); ?>"></span>

    <?php if ( $is_extra ) : ?>

        <!-- HEADER: title + class list -->
        <div class="tm-confirm-header-row">
            <div class="tm-card-title"><?php echo esc_html( $editable_title ); ?></div>
            <div class="tm-card-classes"><strong>Class(es):</strong> <?php echo esc_html( $class_list_string ); ?></div>
        </div>

        <hr class="tm-divider">

        <div class="tm-cart-view">

            <!-- Filing -->
            <div class="tm-cart-row">
                <div class="tm-col tm-col-left">Trademark Application Filing - <?php echo esc_html($country_name); ?></div>
                <div class="tm-col tm-col-mid"><?php echo esc_html($type_label . ' in ' . $class_count . ' classes'); ?></div>
                <div class="tm-col tm-col-right"><?php echo wc_price($filing_price); ?></div>
            </div>

            <!-- Priority -->
            <div class="tm-cart-row">
                <div class="tm-col tm-col-left">Priority Claim - <?php echo esc_html($country_name); ?></div>
                <div class="tm-col tm-col-mid"><?php echo esc_html($type_label . ' in ' . $class_count . ' classes'); ?></div>
                <div class="tm-col tm-col-right"><?php echo wc_price($priority_price); ?></div>
            </div>

            <!-- POA -->
            <div class="tm-cart-row">
                <div class="tm-col tm-col-left">Late Filing of POA - <?php echo esc_html($country_name); ?></div>
                <div class="tm-col tm-col-mid"><?php echo esc_html($type_label . ' in ' . $class_count . ' classes'); ?></div>
                <div class="tm-col tm-col-right"><?php echo wc_price($poa_price); ?></div>
            </div>

            <!-- TOTAL -->
            <div class="tm-cart-total">
                <?php echo wc_price($line_total); ?>
            </div>

        </div>

    <?php else : ?>

        <!-- STANDARD ONE-CLASS TM (FIXED PRICE DISPLAY) -->

        <div class="tm-cart-view">

            <div class="tm-view-header <?php echo $has_image ? 'with-img' : 'no-img'; ?>">

                <?php if ($has_image): ?>
                    <div class="tm-img-box">
                        <img src="<?php echo esc_url($logo); ?>" alt="Trademark Image">
                    </div>
                <?php endif; ?>

                <div class="tm-header-text">
                    <div class="tm-header-title"><?php echo esc_html($type_label); ?></div>

                    <?php if ($tm_title): ?>
                        <div class="tm-header-subtitle"><?php echo esc_html($tm_title); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tm-cart-row">
                <div class="tm-col tm-col-left tm-title"><?php echo esc_html($editable_title); ?></div>
                <div class="tm-col tm-col-mid"><?php echo esc_html($mid_text); ?></div>

                <!-- FIXED PRICE DISPLAY -->
                <div class="tm-col tm-col-right"><?php echo wc_price($line_price_from_cart); ?></div>
            </div>

            <div class="tm-cart-total">
                <?php echo wc_price($line_price_from_cart); ?>
            </div>

        </div>

             <!-- edit mode -->
                <div class="tm-cart-editbox" style="display:none;">
                  <input type="text"
                        class="tm-edit-input"
                        value="<?php echo esc_attr($tm_title); ?>"
                        maxlength="120" />

                  <div class="tm-edit-actions">
                    <span class="tm-edit-cancel"
                          data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                      Cancel
                    </span>

                    <span class="tm-edit-save"
                          data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                      Save
                    </span>
                  </div>
                </div>


    <?php endif; ?>

</div>






            
          <?php endforeach; ?>

        <?php else: ?>
          <p class="tm-error">You have no items in your shopping cart.</p>
        <?php endif; ?>

      </div>

    </div>

    <!-- RIGHT SUMMARY / PAYMENT -->
     <?php if (isset($grand_total)):  ?>
      <div class="tm-summary-card">

        <div class="tm-summary-head">Order Summary</div>

        <div class="tm-summary-body">

          <div class="tm-summary-title">Trademark Service</div>

          <div class="tm-summary-country">
            <span class="tm-flag-inline flag-shadowed-<?php echo $country_iso; ?>"></span>
            <strong><?php echo $country_name; ?></strong>
          </div>

          <div class="tm-summary-total">
            Total: <?php echo WC()->cart->get_total(); ?>
          </div>


          <h4 class="tm-pay-title">Payment Options</h4>

          <div class="tm-pay-options">
            <?php
            $gateways = WC()->payment_gateways->get_available_payment_gateways();
            if (!empty($gateways)):
                foreach ($gateways as $gateway): ?>
                  <label class="tm-pay-option">
                    <input type="radio" name="tm_payment_gateway" value="<?php echo esc_attr($gateway->id); ?>">
                    <?php echo esc_html($gateway->get_title()); ?>
                  </label>
                <?php endforeach;
            else: ?>
                <p class="tm-error">No payment methods available.</p>
            <?php endif; ?>
          </div>

          <button type="button" id="tm-proceed-checkout" class="tm-btn-primary">
            Proceed to Checkout
          </button>

          <a class="tm-back-link"
            href="<?php echo esc_url( site_url('/tm/trademark-choose/order-form?country=' . $country_iso) ); ?>">
            ← Back to edit Trademark Information
          </a>

        </div>
      </div>
     <?php endif  ?>

  </div>

  <input type="hidden" id="tm-country-id" value="<?php echo $country_id; ?>">
  <input type="hidden" id="tm-country-iso" value="<?php echo $country_iso; ?>">
  <input type="hidden" id="tm-step-number" value="2">
</div>
