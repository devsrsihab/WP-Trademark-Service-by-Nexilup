<p>Hello <?php echo esc_html($user->display_name); ?>,</p>

<p>Your trademark order (ID: <?php echo $t->id; ?>) has been updated to:</p>

<h2><?php echo ucfirst(str_replace('_', ' ', $status)); ?></h2>

<p>Country: <?php echo esc_html($country->country_name); ?></p>

<p>Trademark: <?php echo esc_html($t->mark_text ?: 'N/A'); ?></p>

<br>

<p>Thank you,<br>Trademark Support Team</p>
