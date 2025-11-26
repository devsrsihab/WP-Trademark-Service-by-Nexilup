    <!-- =====================================================
         PRICES MODAL (FULL DYNAMIC NOMINUS STYLE)
    ====================================================== -->
    <div id="tm-prices-modal" class="tm-modal" aria-hidden="true">
        <div class="tm-modal-backdrop"></div>

        <div class="tm-modal-dialog" role="dialog" aria-modal="true">

            <span class="tm-modal-close" aria-label="Close">&times;</span>

            <h2 class="tm-modal-title">Trademark Registration Prices</h2>

            <p class="tm-modal-subtitle">
                Prices for trademark registration in
                <strong><?php echo esc_html($country->country_name); ?></strong>
            </p>

            <p class="tm-modal-currency-note">
                Prices are in <?php echo esc_html($currency); ?>
            </p>

            <!-- Tabs -->
            <div class="tm-modal-tabs">
                <span class="tm-prices-tab is-active" data-type="word">WORD MARK</span>
                <span class="tm-prices-tab" data-type="figurative">FIGURATIVE MARK</span>
                <span class="tm-prices-tab" data-type="combined">COMBINED MARK</span>
            </div>

            <!-- Panels -->
            <div class="tm-modal-body">

                <?php foreach (['word','figurative','combined'] as $type): ?>

                    <div class="tm-prices-panel <?php echo $type === 'word' ? 'is-active' : ''; ?>"
                        data-type="<?php echo esc_attr($type); ?>">

                        <?php
                        $has_price = false;

                        for ($step = 1; $step <= 3; $step++):
                            $row = $steps_filtered[$type][$step] ?? null;
                            if (!$row) continue;

                            // Hide step card if both prices <= 0
                            $one = (float)$row->price_one_class;
                            $add = (float)$row->price_add_class;

                            if ($one <= 0 && $add <= 0) continue;

                            $has_price = true;
                        ?>

                        <div class="tm-prices-step-card">
                            <h3>
                                Step <?php echo $step; ?> —
                                <?php echo esc_html($by_step[$step]->step_title ?? 'Trademark Step'); ?>
                            </h3>

                            <div class="tm-prices-step-table">

                                <div class="tm-prices-row">
                                    <span>One Class</span>
                                    <strong><?php echo tm_format_price($row->price_one_class); ?></strong>
                                </div>

                                <div class="tm-prices-row">
                                    <span>Add. Class</span>
                                    <strong><?php echo tm_format_price($row->price_add_class); ?></strong>
                                </div>

                            </div>
                        </div>

                        <?php endfor; ?>

                        <?php if (!$has_price): ?>
                            <p class="tm-no-prices">No price data available for this mark type.</p>
                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

            <?php if ($has_any_sc): ?>
                <p class="tm-modal-footnote">
                    Please review <a href="#" class="tm-open-service-conditions">Service Conditions</a>
                </p>
            <?php endif; ?>

        </div>
    </div>



    <!-- ===========================================
         SERVICE CONDITIONS MODAL (DYNAMIC)
    =========================================== -->
    <div id="tm-service-conditions-modal" class="tm-modal" aria-hidden="true">
        <div class="tm-modal-backdrop"></div>

        <div class="tm-modal-dialog" role="dialog" aria-modal="true">

            <button class="tm-modal-close" aria-label="Close">&times;</button>

            <h2 class="tm-modal-title">Service Conditions</h2>

            <div class="tm-service-conditions-body">

                <?php if ($has_any_sc): ?>

                    <?php if (is_array($sc_items)): ?>
                        <?php foreach ($sc_items as $sc): ?>
                            <?php $sn = (int)$sc->step_number; ?>
                            <h3><?php echo esc_html($default_titles[$sn] ?? ("Step " . $sn)); ?></h3>
                            <div class="tm-sc-block">
                                <?php echo wp_kses_post($sc->content); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php echo wp_kses_post($service_conditions); ?>
                    <?php endif; ?>

                <?php else: ?>
                    <p>No service conditions available for this country.</p>
                <?php endif; ?>

            </div>

            <div class="tm-service-conditions-footer">
                <button class="tm-btn-primary tm-close-service-conditions">OK</button>
            </div>

        </div>
    </div>