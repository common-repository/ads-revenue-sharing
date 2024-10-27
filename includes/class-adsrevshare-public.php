<?php
if (!defined('ABSPATH')) {
    exit;
}

class AdsRevShare_Public {
    public function __construct() {
        // Enqueue public scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));

        // Add ads to content
        add_filter('the_content', array($this, 'add_ads_to_content'));
    }

    public function enqueue_public_assets() {
        wp_enqueue_style('adsrevshare-public-style', ADSREVSHARE_PLUGIN_URL . 'assets/public/css/public-style.css', array(), ADSREVSHARE_PLUGIN_VERSION);
        wp_enqueue_script('adsrevshare-public-script', ADSREVSHARE_PLUGIN_URL . 'assets/public/js/public-script.js', array('jquery'), ADSREVSHARE_PLUGIN_VERSION, true);
        wp_localize_script('adsrevshare-public-script', 'adsrevshareData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('adsrevshare-ajax-nonce'),
            'adsenseUrl' => 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'
        ));
    }

    public function add_ads_to_content($content) {
        if (is_single() && !is_admin()) {
            $post_author_id = get_post_field('post_author', get_the_ID());
            
            // Fetch member data
            $member_publisher_id = get_user_meta($post_author_id, 'adsrevshare_publisher_id', true);
            $member_custom_channel_id = get_user_meta($post_author_id, 'adsrevshare_custom_channel_id', true);
            $member_ad_slot = get_user_meta($post_author_id, 'adsrevshare_ad_slot', true);
            $member_website_url = get_user_meta($post_author_id, 'adsrevshare_website_url', true);
            
            // Fetch admin data
            $admin_publisher_id = get_option('adsrevshare_admin_publisher_id');
            $admin_custom_channel_id = get_option('adsrevshare_admin_custom_channel_id');
            $admin_ad_slot = get_option('adsrevshare_admin_ad_slot');
            $admin_website_url = get_option('adsrevshare_website_url');
            
            // Determine dynamic website URL
            $website_url = $member_website_url ? $member_website_url : $admin_website_url;
            
            if (!$member_publisher_id || !$member_ad_slot) {
                $member_publisher_id = $admin_publisher_id;
                $member_custom_channel_id = $admin_custom_channel_id;
                $member_ad_slot = $admin_ad_slot;
                $website_url = $admin_website_url;
            }
            
            $ads_txt_instance = new AdsRevShare_Ads_Txt();
            
            // Generate ad code for member and admin
            $member_adsense_code = $ads_txt_instance->generate_adsense_code($member_publisher_id, $member_ad_slot, $member_custom_channel_id, $website_url);
            $admin_adsense_code = $ads_txt_instance->generate_adsense_code($admin_publisher_id, $admin_ad_slot, $admin_custom_channel_id, $admin_website_url);
            
            $new_content = '';
            
            // Add ad at the top of the article
            if (get_option('adsrevshare_ad_top_enabled')) {
                $new_content .= $this->get_ad_content('top', $member_adsense_code, $admin_adsense_code);
            }
            
            // Split content into paragraphs
            $paragraphs = explode('</p>', $content);
            $total_paragraphs = count($paragraphs);
            
            // Get custom ad positions
            $custom_ad_positions = array();
            for ($i = 1; $i <= 4; $i++) {
                if (get_option("adsrevshare_ad_custom{$i}_enabled")) {
                    $custom_ad_positions[$i] = get_option("adsrevshare_ad_custom{$i}_paragraph", $i * 3);
                }
            }
            
            foreach ($paragraphs as $index => $paragraph) {
                $new_content .= $paragraph;
                if ($index < $total_paragraphs - 1) {
                    $new_content .= '</p>';
                }
                
                // Add custom ads
                foreach ($custom_ad_positions as $ad_num => $paragraph_num) {
                    if ($index === ($paragraph_num - 1)) {
                        $new_content .= $this->get_ad_content("custom{$ad_num}", $member_adsense_code, $admin_adsense_code);
                        break;  // Only add one ad per paragraph
                    }
                }
            }
            
            // Add ad at the bottom of the article
            if (get_option('adsrevshare_ad_bottom_enabled')) {
                $new_content .= $this->get_ad_content('bottom', $member_adsense_code, $admin_adsense_code);
            }
            
            return $new_content;
        }
        return $content;
    }

    private function get_ad_content($position, $member_adsense_code, $admin_adsense_code) {
        $ad_type = get_option("adsrevshare_ad_{$position}_type", 'adsense');
        $custom_code = get_option("adsrevshare_ad_{$position}_custom_code", '');
        
        if ($ad_type === 'adsense') {
            $ad_content = $this->get_ad_based_on_percentage($member_adsense_code, $admin_adsense_code);
        } else {
            $ad_content = $custom_code;
        }
        
        return "\n<!-- Ad {$position} Start -->\n<div class='ad-{$position}'>{$ad_content}</div>\n<!-- Ad {$position} End -->\n";
    }

    private function get_ad_based_on_percentage($member_ad, $admin_ad) {
        $member_percentage = get_option('adsrevshare_member_ad_percentage', 50);
        return (mt_rand(1, 100) <= $member_percentage) ? $member_ad : $admin_ad;
    }
}