<?php
if (!defined('ABSPATH')) {
    exit;
}

class AdsRevShare_Ads_Txt {
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }

    public function init() {
        register_activation_hook(ADSREVSHARE_PLUGIN_FILE, array($this, 'generate_ads_txt'));
        add_action('personal_options_update', array($this, 'update_ads_txt_on_save'));
        add_action('edit_user_profile_update', array($this, 'update_ads_txt_on_save'));
        add_action('update_option_adsrevshare_admin_publisher_id', array($this, 'generate_ads_txt'));
        add_action('update_option_adsrevshare_manual_ads_txt', array($this, 'generate_ads_txt'));
    }

    public function sanitize_website_url($input) {
        $input = preg_replace('#^https?://#', '', $input);
        $input = preg_replace('/^www\./', '', $input);
        return sanitize_text_field($input);
    }

    public function generate_adsense_code($publisher_id, $ad_slot, $custom_channel_id, $website_url) {
        $ad_container = '<div class="adsrevshare-container">';
        $ad_container .= '<ins class="adsbygoogle" style="display:block"';
        $ad_container .= ' data-ad-client="' . esc_attr($publisher_id) . '"';
        if (!empty($ad_slot)) {
            $ad_container .= ' data-ad-slot="' . esc_attr($ad_slot) . '"';
        }
        $ad_container .= ' data-ad-format="auto"';
        $ad_container .= ' data-full-width-responsive="true"';
        
        if (!empty($website_url)) {
            $cleaned_url = $this->sanitize_website_url($website_url);
            $ad_container .= ' data-page-url="' . esc_attr($cleaned_url) . '"';
        }
        if ($custom_channel_id) {
            $ad_container .= ' data-ad-channel="' . esc_attr($custom_channel_id) . '"';
        }
        $ad_container .= '></ins>';
        $ad_container .= '</div>';
        return $ad_container;
    }

    public function generate_ads_txt() {
        $ads_txt_content = "";
        $admin_publisher_id = get_option('adsrevshare_admin_publisher_id');
        if ($admin_publisher_id) {
            $cleaned_admin_publisher_id = $this->clean_publisher_id($admin_publisher_id);
            $ads_txt_content .= $this->get_ads_txt_line($cleaned_admin_publisher_id);
        }

        $users = get_users(array('fields' => array('ID')));
        foreach ($users as $user) {
            $member_publisher_id = get_user_meta($user->ID, 'adsrevshare_publisher_id', true);
            if ($member_publisher_id) {
                $cleaned_member_publisher_id = $this->clean_publisher_id($member_publisher_id);
                $ads_txt_content .= $this->get_ads_txt_line($cleaned_member_publisher_id);
            }
        }

        $manual_entries = get_option('adsrevshare_manual_ads_txt', '');
        $ads_txt_content .= wp_kses_post($manual_entries);

        $this->write_ads_txt_file($ads_txt_content);
    }

    private function clean_publisher_id($publisher_id) {
        return sanitize_text_field(str_replace('ca-', '', $publisher_id));
    }

    private function get_ads_txt_line($publisher_id) {
        return sprintf("google.com, %s, DIRECT, f08c47fec0942fa0\n", $publisher_id);
    }

    private function write_ads_txt_file($content) {
        $upload_dir = wp_upload_dir();
        $ads_txt_path = trailingslashit($upload_dir['basedir']) . 'ads.txt';

        if (wp_mkdir_p(dirname($ads_txt_path)) && !file_exists($ads_txt_path)) {
            $result = file_put_contents($ads_txt_path, $content);
            if ($result === false) {
                error_log('Failed to write ads.txt file.');
            }
        } else {
            error_log('Unable to create ads.txt file or file already exists.');
        }
    }

    public function update_ads_txt_on_save($user_id) {
        if (current_user_can('edit_user', $user_id)) {
            $this->generate_ads_txt();
        }
    }
}