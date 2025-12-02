<?php
if ( ! defined('ABSPATH') ) exit;

$country_name = esc_html($country->country_name);
$country_iso  = esc_attr($country->iso_code);
?>
<div class="tm-order-page tm-step1">

  <!-- EXACT Centered 3-step Bar -->
  <div class="tm-progress">
      <div class="tm-progress-line"></div>

      <div class="tm-progress-step is-active">
          <span class="dot"></span>
          <span>Trademark Information</span>
      </div>

      <div class="tm-progress-step">
          <span class="dot"></span>
          <span>Confirm Order</span>
      </div>

      <div class="tm-progress-step">
          <span class="dot"></span>
          <span>Order Receipt</span>
      </div>
  </div>

  <div class="tm-step-layout">
    <!-- LEFT MAIN CARD -->
    <div class="tm-step-card">

      <div class="tm-step-header">
        <h2>
          Comprehensive Trademark Study - <?php echo $country_name; ?>
          <span class="tm-flag-inline flag-shadowed-<?php echo $country_iso; ?>"></span>
        </h2>
        <p>
          Thank you for choosing our service. To get started, kindly fill out the form below.
          Once your study is complete, we will inform you so you can download it directly from our website.
        </p>
      </div>

      <div class="tm-step-body">

        <!-- Trademark Type -->
        <div class="tm-field">
          <label class="tm-field-label">
            Trademark Type <span class="tm-info">?</span>
          </label>

          <div class="tm-type-grid" id="tm-type-grid">
            <!-- Word -->
            <label class="tm-type-card is-active" data-type="word">
              <input type="radio" name="tm-type" value="word" checked />
              <div class="tm-type-title">Word Mark</div>
              <div class="tm-type-preview word-preview">
                <div class="tm-preview-text">YOUR BRAND</div>
              </div>
            </label>

            <!-- Figurative -->
            <label class="tm-type-card" data-type="figurative">
              <input type="radio" name="tm-type" value="figurative" />
              <div class="tm-type-title">Figurative Mark</div>
              <div class="tm-type-preview figurative-preview">
                <img src="<?php echo esc_url(WP_TMS_NEXILUP_URL . 'assets/img/figurative-mark.png'); ?>" alt="Figurative">
              </div>
            </label>

            <!-- Combined -->
            <label class="tm-type-card" data-type="combined">
              <input type="radio" name="tm-type" value="combined" />
              <div class="tm-type-title">Combined Mark</div>
              <div class="tm-type-preview combined-preview">
                <img src="<?php echo esc_url(WP_TMS_NEXILUP_URL . 'assets/img/figurative-mark.png'); ?>" alt="Combined">
                <div class="tm-preview-text">YOUR BRAND</div>
              </div>
            </label>
          </div>
        </div>

        <!-- Trademark text -->
        <div class="tm-field tm-field-text" id="tm-text-field">
          <label class="tm-field-label">Your Trademark</label>
          <small>Enter the name or phrase you wish to register as a trademark.</small>
          <input type="text" id="tm-text" >
        </div>

        <?php if ( ! isset($_GET['tm_additional_class']) || intval($_GET['tm_additional_class']) !== 1 ) : ?>
          <div class="tm-field tm-goods-field">
              <label class="tm-field-label">Goods and Services</label>
            <div>
                <p class="tm-good-and-services-help1">
                    Please describe the goods or services that your trademark will be used in connection with. This will help us determine the appropriate trademark class for your registration.
        </p>
                <textarea id="tm-goods" rows="4" ></textarea>
            </div>

              <p class="tm-note">
                  <strong class="bold">Note:</strong> If you are familiar with trademark classes and have already identified the appropriate class for your application, you may specify it using this <a class="tm-class-selector-btn">Trademark Class Selector</a> . If not, our team will assist you in determining the appropriate class based on the goods and services you have described above.
              </p>
          </div>
        <?php endif; ?>

        <?php if ( ! isset($_GET['tm_additional_class']) || intval($_GET['tm_additional_class']) !== 1 ) : ?>
              <div class="tm-field tm-goods-field" id="tm-selected-classes-wrap" style="display:none;">
                  <label class="tm-field-label">Selected Classes</label>
                  <div>
                      <input type="text" id="tm-classes" readonly>
                  </div>
                  <p class="tm-note">
                      <strong class="bold">Note:</strong> If you need to edit or remove classes, please use the 
                      <a class="tm-class-selector-btn">Trademark Class Selector</a>
                  </p>
              </div>

        <?php endif; ?>



        <!-- Trademark tm_from -->
        <input value="Comprehensive Trademark Study Testing Baba"  type="hidden" id="tm_from" >


        <!-- Logo uploader -->
        <div class="tm-field tm-field-logo" id="tm-logo-field" style="display:none;">
          <label class="tm-field-label">Upload your Logo</label>

          <div class="tm-upload-box" id="tm-upload-box" role="button" tabindex="0">
            <input type="file" id="tm-logo-file" accept="image/*" hidden>

            <div class="tm-upload-inner">
              <div class="tm-upload-icon">⬆</div>
              <div class="tm-upload-text">
                <strong>Drag & drop your logo</strong>
                <span>or click to browse</span>
              </div>
              <div class="tm-upload-hint">PNG, JPG up to 5MB</div>
            </div>

            <div class="tm-upload-preview" id="tm-upload-preview" style="display:none;">
              <img id="tm-logo-preview-img" src="" alt="Logo Preview">
              <button type="button" class="tm-remove-logo" id="tm-remove-logo">Remove</button>
            </div>
          </div>
        </div>


          <?php if ( isset( $_GET['tm_additional_class'] ) && intval( $_GET['tm_additional_class'] ) === 1 ) :
            
            // Get price row for step2 to check priority/POA fees
          $price_row = TM_Country_Prices::get_price_row( $country->id, 'word', 2 ); 
          $priority_fee = $price_row ? floatval($price_row->priority_claim_fee) : 0;
          $poa_fee      = $price_row ? floatval($price_row->poa_late_fee)      : 0;
            
            
            ?>

          <!-- ============================
              TRADEMARK CLASSES
          ============================== -->
          <div class="tm-field tm-classes-section">
            <label class="tm-field-label">Trademark Classes</label>
            <small class="tm-field-help">
              Please select the appropriate trademark classes for the goods or services that your trademark
              will be used in connection with.
            </small>

            <div id="tm-class-list" class="tm-class-list">
              <div class="tm-class-row">
                  <div class="tm-class-col tm-class-select-col">
                    <label class="tm-small-label">Select Class</label>
                    <select class="tm-class-select">
                      <?php for ( $i = 1; $i <= 45; $i++ ) : ?>
                        <option value="<?php echo $i; ?>">Class <?php echo $i; ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>

                  <div class="tm-class-col tm-class-desc-col">
                    <label class="tm-small-label">
                      List the goods or services of this class that will be used in connection with your trademark.
                    </label>
                    <textarea class="tm-class-desc" rows="2"></textarea>
                  </div>

                  <button type="button" class="tm-class-remove" aria-label="Remove class">
                    <span>&minus;</span>
                  </button>
              </div>
            </div>

            <button type="button" id="tm-add-class" class="tm-btn-add-class">
              <span class="tm-plus">+</span> Add Class
            </button>
          </div>

          <!-- ============================
              PRIORITY CLAIM
          ============================== -->
          <?php if ( $priority_fee > 0 ) : ?>
          <div class="tm-field tm-priority-section">
            <label class="tm-field-label">Priority Claim <span class="tm-info">?</span></label>
            <div class="tm-choice-grid">
                <label class="tm-choice-card is-active">
                    <input type="radio" name="tm_priority" value="0" checked>
                    <div class="tm-choice-inner">
                        <div class="tm-choice-title">No Priority Claim</div>
                        <p>You have not filed the same trademark in any other countries in the last 6 months.</p>
                    </div>
                </label>

                <label class="tm-choice-card">
                    <input type="radio" name="tm_priority" value="1">
                    <div class="tm-choice-inner">
                        <div class="tm-choice-title">With Priority Claim</div>
                        <p>You have filed the same trademark in the last 6 months.</p>
                    </div>
                </label>
            </div>
          </div>
          <?php endif; ?>


          <!-- ============================
              POWER OF ATTORNEY (POA)
          ============================== -->
          <?php if ( $poa_fee > 0 ) : ?>
          <div class="tm-field tm-poa-section">
            <label class="tm-field-label">Power of Attorney (POA) <span class="tm-info">?</span></label>
            <div class="tm-choice-grid">

                <label class="tm-choice-card is-active">
                    <input type="radio" name="tm_poa" value="normal" checked>
                    <div class="tm-choice-inner">
                        <div class="tm-choice-title">Normal Filing</div>
                        <p>Application will wait until POA is received.</p>
                    </div>
                </label>

                <label class="tm-choice-card">
                    <input type="radio" name="tm_poa" value="late">
                    <div class="tm-choice-inner">
                        <div class="tm-choice-title">Late Filing of POA</div>
                        <p>Application will be filed immediately. Extra fee applies.</p>
                    </div>
                </label>

            </div>
          </div>
          <?php endif; ?>


<?php endif; ?>



      </div>
    </div>

    <!-- RIGHT SUMMARY CARD -->
    <div class="tm-summary-card">
      <div class="tm-summary-head">Order Summary</div>
      <div class="tm-summary-body">
        <div class="tm-summary-title">Comprehensive Trademark Study</div>
        <div class="tm-summary-country">
          <span class="tm-flag-inline flag-shadowed-<?php echo $country_iso; ?>"></span>
          <strong><?php echo $country_name; ?></strong>
        </div>

        <div id="tm-price-summary" class="tm-price-loading">
          Calculating price...
        </div>

        <button type="button" id="tm-step1-next" class="tm-btn-primary">
          Continue
        </button>
      </div>
    </div>

  </div>

  <!-- hidden meta -->
  <input type="hidden" id="tm-country-id" value="<?php echo (int) $country->id; ?>">
  <input type="hidden" id="tm-country-iso" value="<?php echo $country_iso; ?>">
  <!-- <input type="hidden" id="tm-step-number" value="1"> -->
<input type="hidden" id="tm-step-number" value="<?php echo isset($_GET['tm_additional_class']) && intval($_GET['tm_additional_class']) === 1 ? 2 : 1; ?>">


<!-- ==========================
     CLASS SELECTOR MODAL
=========================== -->
<div id="tm-class-modal" class="tm-class-modal" style="display:none;">
    <div class="tm-class-modal-content">

        <h3 class="tm-modal-title">Trademark Class Selector</h3>
        <p class="tm-modal-subtitle">
            Please select the appropriate trademark classes for your Comprehensive Trademark Study.
        </p>

        <div class="tm-class-grid">
            <?php for ($i = 1; $i <= 45; $i++): ?>
                <label class="tm-class-checkbox">
                    <input type="checkbox" class="tm-class-item" value="<?php echo $i; ?>">
                    <span><?php echo $i; ?></span>
                </label>
            <?php endfor; ?>
        </div>

        <div class="tm-class-modal-footer">
        <div class="tm-class-select-links">
              <a href="#" id="tm-select-all">Select All</a> |
              <a href="#" id="tm-deselect-all">Deselect All</a>
        </div>

           <div class="tm-class-modal-actions">
              <div>
                  <span class="tm-class-total" id="tm-class-total">$0.00</span>
                
              </div>  
            <div>
                <button type="button" id="tm-class-cancel" class="tm-btn-cancel">Cancel</button>
                <button type="button" id="tm-class-confirm" class="tm-btn-confirm">Confirm</button>
            </div>
           </div>
        </div>

    </div>
</div>


<script>
jQuery(function ($) {

    /* ---------------------------------------------------
       GLOBAL PRICE CACHE (so no repeated AJAX calls)
    --------------------------------------------------- */
    let TM_MODAL_PRICE = {
        first: 0,
        add: 0,
        loaded: false
    };

    function loadModalPrice() {
        if (TM_MODAL_PRICE.loaded) return; // prevent duplicate calls

        $.post(
            TM_GLOBAL.ajax_url,
            {
                action: "tm_calc_price",
                nonce: TM_GLOBAL.nonce,
                country: TM_GLOBAL.country_id,
                type: $("input[name='tm-type']:checked").val(),
                step: 1,         // 🔥 ALWAYS FILING LOGIC
                classes: 1
            },
            function (resp) {

                if (resp && resp.success) {
                    TM_MODAL_PRICE.first = parseFloat(resp.data.one) || 0;
                    TM_MODAL_PRICE.add   = parseFloat(resp.data.add) || 0;
                    TM_MODAL_PRICE.loaded = true;
                }
            }
        );
    }

    /* ---------------------------------------------------
       OPEN MODAL → LOAD PRICE ONCE
    --------------------------------------------------- */
    $(".tm-class-selector-btn").on("click", function (e) {
        e.preventDefault();
        loadModalPrice();
        $("#tm-class-modal").fadeIn(200);
    });

    /* ---------------------------------------------------
       CLOSE MODAL
    --------------------------------------------------- */
    $("#tm-class-cancel").on("click", function () {
        $("#tm-class-modal").fadeOut(200);
    });

    /* ---------------------------------------------------
       SELECT ALL / DESELECT ALL
    --------------------------------------------------- */
    $("#tm-select-all").on("click", function (e) {
        e.preventDefault();
        $(".tm-class-item").prop("checked", true);
        updateModalTotal();
    });

    $("#tm-deselect-all").on("click", function (e) {
        e.preventDefault();
        $(".tm-class-item").prop("checked", false);
        updateModalTotal();
    });

    /* ---------------------------------------------------
       UPDATE PRICE (NO AJAX)
    --------------------------------------------------- */
    $(document).on("change", ".tm-class-item", function () {
        updateModalTotal();
    });

    function updateModalTotal() {

        const count = $(".tm-class-item:checked").length;
        const first = TM_MODAL_PRICE.first;
        const add   = TM_MODAL_PRICE.add;

        // class logic:
        // 1 class = first fee
        // >1 classes = first fee + (count - 1) * add
        let extra = Math.max(0, count - 1);
        let total = (count === 0)
            ? 0
            : first + extra * add;

        $("#tm-class-total").text("$" + total.toFixed(2));
    }


    /* ---------------------------------------------------
       CONFIRM → SEND VALUES TO UI + REFRESH SUMMARY
    --------------------------------------------------- */
    $("#tm-class-confirm").on("click", function () {

        const selected = $(".tm-class-item:checked").map(function () {
            return $(this).val();
        }).get();

        if (selected.length) {
            $("#tm-selected-classes-wrap").show();
            $("#tm-classes").val(selected.join(" - "));
        } else {
            $("#tm-selected-classes-wrap").hide();
        }

        // dispatch event for summary recalculation
        const event = new Event("tmUpdatePrice");
        document.dispatchEvent(event);

        $("#tm-class-modal").fadeOut(200);
    });

});
</script>


</div>
