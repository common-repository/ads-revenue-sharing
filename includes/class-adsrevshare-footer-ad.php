<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AdsRevShare_Footer_Ad {

    public function __construct() {
        add_action('wp_footer', array($this, 'display_footer_ad'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings() {
        // Register the settings related to footer ad.
        register_setting('adsrevshare-settings-group', 'adsrevshare_ad_footer_enabled', 'absint');
        register_setting('adsrevshare-settings-group', 'adsrevshare_ad_footer_type', 'sanitize_text_field');
        register_setting('adsrevshare-settings-group', 'adsrevshare_ad_footer_custom_code', array($this, 'sanitize_custom_code'));
    }

    public function sanitize_custom_code($input) {
        return wp_unslash($input); 
    }

    public function display_footer_ad() {
        if (!get_option('adsrevshare_ad_footer_enabled')) {
            return;
        }
        $ad_type = get_option('adsrevshare_ad_footer_type');

        if ($ad_type === 'adsense') {
            $this->display_adsense_ad(); 
        } elseif ($ad_type === 'custom') {
            $this->display_custom_ad(); 
        }
    }
  
      public function sanitize_website_url($input) {
        $input = preg_replace('#^https?://#', '', $input);
        $input = preg_replace('/^www\./', '', $input);
        return sanitize_text_field($input);
    }

    private function display_adsense_ad() {
    $post_author_id = get_post_field('post_author', get_the_ID());

    $member_publisher_id = get_user_meta($post_author_id, 'adsrevshare_publisher_id', true);
    $member_custom_channel_id = get_user_meta($post_author_id, 'adsrevshare_custom_channel_id', true);
    $member_ad_slot = get_user_meta($post_author_id, 'adsrevshare_ad_slot', true);
    $member_website_url = get_user_meta($post_author_id, 'adsrevshare_website_url', true);

    if (empty($member_publisher_id) || empty($member_ad_slot)) {
        $admin_publisher_id = get_option('adsrevshare_admin_publisher_id');
        $admin_custom_channel_id = get_option('adsrevshare_admin_custom_channel_id');
        $admin_ad_slot = get_option('adsrevshare_admin_ad_slot');
        $admin_website_url = get_option('adsrevshare_admin_website_url');

        if (!empty($admin_publisher_id) && !empty($admin_ad_slot)) {
            $member_publisher_id = $admin_publisher_id;
            $member_custom_channel_id = $admin_custom_channel_id;
            $member_ad_slot = $admin_ad_slot;
            $member_website_url = $admin_website_url;
        }
    }

    $ad_attributes = 'style="display:inline-block;width:336px;height:280px" ';
    $ad_attributes .= 'data-ad-client="' . esc_attr($member_publisher_id) . '" ';
    $ad_attributes .= 'data-ad-slot="' . esc_attr($member_ad_slot) . '" ';

    if (!empty($member_website_url)) {
        $cleaned_url = $this->sanitize_website_url($member_website_url);
        $ad_attributes .= 'data-page-url="' . esc_attr($cleaned_url) . '" ';
    }

    if (!empty($member_custom_channel_id)) {
        $ad_attributes .= 'data-ad-channel="' . esc_attr($member_custom_channel_id) . '" ';
    }

    ?>
    <div id="adsrevshare-footer-ad">
        <div style="position: relative; max-width: 336px; margin: 0 auto;">
            <button id="adsrevshare-close-ad">×</button>
            <ins class="adsbygoogle" <?php echo $ad_attributes; ?>></ins>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var adContainer = document.getElementById('adsrevshare-footer-ad');
        var closeButton = document.getElementById('adsrevshare-close-ad');
        var adShown = false;
        var adClosed = false;
        var lastScrollY = window.scrollY;

        if (!adContainer || !closeButton) {
            console.warn('Ad container or close button not found');
            return;
        }

        function checkScroll() {
            if (!adShown && !adClosed && window.scrollY > window.innerHeight * 0.5) {
                adContainer.style.display = 'block'; 
                adShown = true;
            }
            else if (adShown && window.scrollY < lastScrollY) {
                adContainer.style.display = 'none'; 
                adShown = false; 
            }
            lastScrollY = window.scrollY; 
        }

        window.addEventListener('scroll', checkScroll);

        closeButton.addEventListener('click', function() {
            adContainer.style.display = 'none';
            adClosed = true;
            adShown = false; 
            window.removeEventListener('scroll', checkScroll);
        });
    });
    </script>
    <?php
}



    private function display_custom_ad() {
        $custom_ad_code = get_option('adsrevshare_ad_footer_custom_code');
        if (!empty($custom_ad_code)) {
            echo $custom_ad_code; 
        } else {
            error_log('Custom ad code is empty.');
        }
    }
}
