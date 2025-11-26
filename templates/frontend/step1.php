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
          <input type="text" id="tm-text" placeholder="Enter your trademark">
        </div>


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

        <!-- Goods -->
        <div class="tm-field">
          <label class="tm-field-label">Goods and Services</label>
          <small>
            Please describe the goods or services that your trademark will be used in connection with.
          </small>
          <textarea id="tm-goods" rows="4" placeholder="Describe goods/services"></textarea>
          <p class="tm-note">
            <strong>Note:</strong> If you are familiar with trademark classes and have already identified the appropriate class
            for your application, you may specify it using this Trademark Class Selector.
          </p>
        </div>

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
  <input type="hidden" id="tm-step-number" value="1">

</div>
