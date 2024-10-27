<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AdsRevShare_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Revenue Sharing Settings', 'adsense-revenue-sharing' ),
            __( 'Revenue Sharing', 'adsense-revenue-sharing' ),
            'manage_options',
            'adsrevshare-settings',
            array( $this, 'render_settings_page' ),
            'dashicons-chart-area'
        );
    }

    public function enqueue_admin_assets($hook) {
        if ($hook != 'toplevel_page_adsrevshare-settings') {
            return;
        }

        wp_enqueue_style('adsrevshare-admin-style', ADSREVSHARE_PLUGIN_URL . 'assets/admin/css/admin-style.css', array(), ADSREVSHARE_PLUGIN_VERSION);
        wp_enqueue_script('adsrevshare-admin-script', ADSREVSHARE_PLUGIN_URL . 'assets/admin/js/admin-script.js', array('jquery'), ADSREVSHARE_PLUGIN_VERSION, true);
    }
  
  
    public function register_settings() {
        register_setting( 'adsrevshare-settings-group', 'adsrevshare_member_ad_percentage', array( $this, 'sanitize_percentage' ) );
        register_setting( 'adsrevshare-settings-group', 'adsrevshare_admin_publisher_id', 'sanitize_text_field' );
        register_setting( 'adsrevshare-settings-group', 'adsrevshare_admin_custom_channel_id', 'sanitize_text_field' );
        register_setting( 'adsrevshare-settings-group', 'adsrevshare_admin_ad_slot', 'sanitize_text_field' );
        register_setting( 'adsrevshare-settings-group', 'adsrevshare_manual_ads_txt', 'wp_kses_post' );
        register_setting( 'adsrevshare-settings-group', 'adsrevshare_website_url', array( $this, 'sanitize_website_url' ) );

        $ad_positions = ['top', 'bottom', 'custom1', 'custom2', 'custom3', 'custom4', 'footer'];

        foreach ($ad_positions as $position) {
            register_setting('adsrevshare-settings-group', "adsrevshare_ad_{$position}_enabled", 'absint');
            register_setting('adsrevshare-settings-group', "adsrevshare_ad_{$position}_type", 'sanitize_text_field');
            register_setting('adsrevshare-settings-group', "adsrevshare_ad_{$position}_custom_code", array($this, 'sanitize_custom_code'));
            if (strpos($position, 'custom') === 0) {
                register_setting('adsrevshare-settings-group', "adsrevshare_ad_{$position}_paragraph", 'absint');
            }
        }
    }
  
  

    public function sanitize_custom_code($input) {
        return wp_unslash($input); 
    }


    private function get_custom_code($option_name) {
        return get_option($option_name, ''); 
    }



    public function sanitize_percentage( $input ) {
        return absint( min( 100, max( 0, $input ) ) );
    }

    public function sanitize_website_url( $input ) {
        $url = preg_replace( '#^https?://#', '', $input );
        $url = strtok($url, '/');
        return sanitize_text_field( $url );
    }
      private function get_website_url() {
        $url = get_option( 'adsrevshare_website_url', '' );
        return $this->sanitize_website_url( $url );
    }

public function render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['settings-updated'])) {
        add_settings_error('adsrevshare_messages', 'adsrevshare_message', __('Settings saved successfully.', 'adsense-revenue-sharing'), 'updated');
    }

    settings_errors('adsrevshare_messages');
    ?>
    <div class="adsrevshare-settings-page wrap">
        <h1 class="adsrevshare-page-title"><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('adsrevshare-settings-group');
            do_settings_sections('adsrevshare-settings-group');
            $this->render_settings_fields();
            submit_button(__('Save Settings', 'adsense-revenue-sharing'), 'primary', 'submit', true, array('class' => 'adsrevshare-save-button'));
            ?>
        </form>
    </div>
    <?php
}

    private function render_settings_fields() {
        ?>
        <div class="settings-container">
            <div class="field-group member-ad-percentage">
                <div class="field-title" for="adsrevshare_member_ad_percentage"><?php esc_html_e( 'Member Ad Percentage (%)', 'adsense-revenue-sharing' ); ?></div>
                <input type="number" name="adsrevshare_member_ad_percentage" 
                       value="<?php echo esc_attr( get_option( 'adsrevshare_member_ad_percentage', 50 ) ); ?>" 
                       min="0" max="100" />
                <p class="description">
                    <?php esc_html_e( 'Enter a percentage between 0 and 100.', 'adsense-revenue-sharing' ); ?>
                </p>
            </div>

            <div class="field-group admin-publisher-id">
                <div class="field-title" for="adsrevshare_admin_publisher_id"><?php esc_html_e( 'AdSense Publisher ID', 'adsense-revenue-sharing' ); ?></div>
                <input type="text" name="adsrevshare_admin_publisher_id" 
                       value="<?php echo esc_attr( get_option( 'adsrevshare_admin_publisher_id' ) ); ?>" 
                       class="regular-text" />
                <p class="description">
                    <?php esc_html_e( 'Enter your AdSense Publisher ID (pub-1234567891234567).', 'adsense-revenue-sharing' ); ?>
                </p>
            </div>

            <div class="field-group admin-custom-channel-id">
                <div class="field-title" for="adsrevshare_admin_custom_channel_id"><?php esc_html_e( 'Custom Channel ID (Optional)', 'adsense-revenue-sharing' ); ?></div>
                <input type="text" name="adsrevshare_admin_custom_channel_id" 
                       value="<?php echo esc_attr( get_option( 'adsrevshare_admin_custom_channel_id' ) ); ?>" 
                       class="regular-text" />
                <p class="description">
                    <?php esc_html_e( 'Enter your Custom Channel ID.', 'adsense-revenue-sharing' ); ?>
                </p>
            </div>

            <div class="field-group admin-ad-slot-id">
                <div class="field-title" for="adsrevshare_admin_ad_slot"><?php esc_html_e( 'Ad Slot ID (Optional)', 'adsense-revenue-sharing' ); ?></div>
                <input type="text" name="adsrevshare_admin_ad_slot" 
                       value="<?php echo esc_attr( get_option( 'adsrevshare_admin_ad_slot' ) ); ?>" 
                       class="regular-text" />
                <p class="description">
                    <?php esc_html_e( 'Enter your Ad Slot ID.', 'adsense-revenue-sharing' ); ?>
                </p>
            </div>

            <div class="field-group website-url">
                <div class="field-title" for="adsrevshare_website_url"><?php esc_html_e( 'Website URL (Optional)', 'adsense-revenue-sharing' ); ?></div>
                <input type="text" name="adsrevshare_website_url" 
                       value="<?php echo esc_attr( $this->get_website_url() ); ?>" 
                       class="regular-text" />
                <p class="description">
                    <?php esc_html_e( 'Enter your website URL (example.com).', 'adsense-revenue-sharing' ); ?>
                </p>
            </div>

            <div class="field-group manual-ads-txt">
                <div class="field-title" for="adsrevshare_manual_ads_txt"><?php esc_html_e( 'Manual ads.txt Entries', 'adsense-revenue-sharing' ); ?></div>
                <textarea name="adsrevshare_manual_ads_txt" rows="10" cols="50" class="large-text code"><?php echo esc_textarea( get_option( 'adsrevshare_manual_ads_txt', '' ) ); ?></textarea>
                <p class="description">
                    <?php esc_html_e( 'Add additional lines to your ads.txt file (one entry per line).', 'adsense-revenue-sharing' ); ?>
                </p>
                <p class="description">
                    <?php esc_html_e( 'Example: google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0', 'adsense-revenue-sharing' ); ?>
                </p>
            </div>

            <h2><?php esc_html_e('Ad Positions', 'adsense-revenue-sharing'); ?></h2>
        <div class="ad-tabs">
            <?php
            $positions = [
                'top' => __('Top of the article', 'adsense-revenue-sharing'),
                'custom1' => __('Custom Ad 1', 'adsense-revenue-sharing'),
                'custom2' => __('Custom Ad 2', 'adsense-revenue-sharing'),
                'custom3' => __('Custom Ad 3', 'adsense-revenue-sharing'),
                'custom4' => __('Custom Ad 4', 'adsense-revenue-sharing'),
                'bottom' => __('Bottom of the article', 'adsense-revenue-sharing'),
                'footer' => __('Footer Ad', 'adsense-revenue-sharing'),
            ];

            foreach ($positions as $position => $label) :
            ?>
                <div class="ad-tab" data-tab="ad-<?php echo esc_attr($position); ?>"><?php echo esc_html($label); ?></div>
            <?php endforeach; ?>
        </div>

        <?php
        foreach ($positions as $position => $label) :
            $enabled_option = "adsrevshare_ad_{$position}_enabled";
            $type_option = "adsrevshare_ad_{$position}_type";
            $custom_code_option = "adsrevshare_ad_{$position}_custom_code";
            $paragraph_option = "adsrevshare_ad_{$position}_paragraph";
        ?>
            <div id="ad-<?php echo esc_attr($position); ?>" class="ad-content">
                <h3><?php echo esc_html($label); ?></h3>
                
                <div class="ad-enable">
                    <input type="checkbox" id="<?php echo esc_attr($enabled_option); ?>" name="<?php echo esc_attr($enabled_option); ?>" value="1" <?php checked(get_option($enabled_option), 1); ?> />
                    <label for="<?php echo esc_attr($enabled_option); ?>"><?php esc_html_e('Enable', 'adsense-revenue-sharing'); ?></label>
                </div>
                
                <?php if (strpos($position, 'custom') === 0) : ?>
                    <div class="ad-paragraph">
                        <label for="<?php echo esc_attr($paragraph_option); ?>"><?php esc_html_e('Display after paragraph number:', 'adsense-revenue-sharing'); ?></label>
                        <input type="number" id="<?php echo esc_attr($paragraph_option); ?>" name="<?php echo esc_attr($paragraph_option); ?>" value="<?php echo esc_attr(get_option($paragraph_option, 3)); ?>" min="1" />
                    </div>
                <?php endif; ?>

                <div class="ad-type">
                    <div class="ad-choice-item">
                        <input type="radio" id="<?php echo esc_attr($type_option); ?>_adsense" name="<?php echo esc_attr($type_option); ?>" value="adsense" <?php checked(get_option($type_option, 'adsense'), 'adsense'); ?> />
                        <label for="<?php echo esc_attr($type_option); ?>_adsense"><?php esc_html_e('Use AdSense ad', 'adsense-revenue-sharing'); ?></label>
                    </div>
                    <div class="ad-choice-item">
                        <input type="radio" id="<?php echo esc_attr($type_option); ?>_custom" name="<?php echo esc_attr($type_option); ?>" value="custom" <?php checked(get_option($type_option), 'custom'); ?> />
                        <label for="<?php echo esc_attr($type_option); ?>_custom"><?php esc_html_e('Use custom ad code', 'adsense-revenue-sharing'); ?></label>
                    </div>
                </div>

                <div class="ad-custom-code">
                    <label for="<?php echo esc_attr($custom_code_option); ?>"><?php esc_html_e('Custom Ad Code:', 'adsense-revenue-sharing'); ?></label>
                    <textarea id="<?php echo esc_attr($custom_code_option); ?>" name="<?php echo esc_attr($custom_code_option); ?>" rows="5" cols="50" class="large-text code"><?php echo esc_textarea(get_option($custom_code_option, '')); ?></textarea>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}