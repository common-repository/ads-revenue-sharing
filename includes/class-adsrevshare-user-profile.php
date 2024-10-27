<?php
if (!defined('ABSPATH')) {
    exit;
}

class AdsRevShare_User_Profile {
    public function __construct() {
        // Add fields to user profile
        add_action('show_user_profile', array($this, 'add_user_adsense_fields'));
        add_action('edit_user_profile', array($this, 'add_user_adsense_fields'));

        // Save user profile fields
        add_action('personal_options_update', array($this, 'save_user_adsense_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_adsense_fields'));
    }

    public function add_user_adsense_fields($user) {
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }

        wp_nonce_field('adsrevshare_user_adsense_nonce', 'adsrevshare_user_adsense_nonce');
        ?>
        <h3><?php esc_html_e('AdSense Settings', 'adsense-revenue-sharing'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="adsrevshare_publisher_id"><?php esc_html_e('AdSense Publisher ID', 'adsense-revenue-sharing'); ?></label></th>
                <td>
                    <input type="text" name="adsrevshare_publisher_id" id="adsrevshare_publisher_id" 
                           value="<?php echo esc_attr(get_user_meta($user->ID, 'adsrevshare_publisher_id', true)); ?>" 
                           class="regular-text" />
                    <p class="description">
                        <?php esc_html_e('Enter your AdSense Publisher ID (pub-1234567891234567).', 'adsense-revenue-sharing'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="adsrevshare_custom_channel_id"><?php esc_html_e('Custom Channel ID (Optional)', 'adsense-revenue-sharing'); ?></label></th>
                <td>
                    <input type="text" name="adsrevshare_custom_channel_id" id="adsrevshare_custom_channel_id" 
                           value="<?php echo esc_attr(get_user_meta($user->ID, 'adsrevshare_custom_channel_id', true)); ?>" 
                           class="regular-text" />
                    <p class="description">
                        <?php esc_html_e('Enter your Custom Channel ID.', 'adsense-revenue-sharing'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="adsrevshare_ad_slot"><?php esc_html_e('Ad Slot ID (Optional)', 'adsense-revenue-sharing'); ?></label></th>
                <td>
                    <input type="text" name="adsrevshare_ad_slot" id="adsrevshare_ad_slot" 
                           value="<?php echo esc_attr(get_user_meta($user->ID, 'adsrevshare_ad_slot', true)); ?>" 
                           class="regular-text" />
                    <p class="description">
                        <?php esc_html_e('Enter your Ad Slot ID.', 'adsense-revenue-sharing'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><label for="adsrevshare_website_url"><?php esc_html_e('Website URL (Optional)', 'adsense-revenue-sharing'); ?></label></th>
                <td>
                    <input type="text" name="adsrevshare_website_url" id="adsrevshare_website_url" 
                           value="<?php echo esc_attr(get_user_meta($user->ID, 'adsrevshare_website_url', true)); ?>" 
                           class="regular-text" />
                    <p class="description">
                        <?php esc_html_e('Enter your website URL (example.com).', 'adsense-revenue-sharing'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_user_adsense_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        if (!isset($_POST['adsrevshare_user_adsense_nonce']) || 
            !wp_verify_nonce($_POST['adsrevshare_user_adsense_nonce'], 'adsrevshare_user_adsense_nonce')) {
            return false;
        }

        if (isset($_POST['adsrevshare_publisher_id'])) {
            update_user_meta($user_id, 'adsrevshare_publisher_id', sanitize_text_field($_POST['adsrevshare_publisher_id']));
        }

        if (isset($_POST['adsrevshare_custom_channel_id'])) {
            update_user_meta($user_id, 'adsrevshare_custom_channel_id', sanitize_text_field($_POST['adsrevshare_custom_channel_id']));
        }

        if (isset($_POST['adsrevshare_ad_slot'])) {
            update_user_meta($user_id, 'adsrevshare_ad_slot', sanitize_text_field($_POST['adsrevshare_ad_slot']));
        }

        if (isset($_POST['adsrevshare_website_url'])) {
            $website_url = sanitize_text_field($_POST['adsrevshare_website_url']);
            $website_url = preg_replace('#^https?://#', '', $website_url);
            update_user_meta($user_id, 'adsrevshare_website_url', $website_url);
        }
    }
}
