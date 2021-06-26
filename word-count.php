<?php

/**
 * Plugin Name: Word Count
 * Description: A plugin to count words and the time to read for posts (single pages)
 * Version: 0.1
 * Author: nbebaw
 * Author URI: https://github.com/nbebaw
 * Text Domain: wcpdomain 
 * Domain Path: /languages
 */

 if (! defined('ABSPATH')) exit; // Exit if accessed directly

 class WordCountAndTimePlugin {
    function __construct() {
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
        add_action('init', array($this, 'languages'));
    }

    function languages() {
        load_plugin_textdomain('wcpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function ifWrap($content) {
        if ((is_main_query() AND is_single()) AND (get_option('wcp_wordcount', '1') OR get_option('wcp_charcount', '1') OR get_option('wcp_readtime', '1'))) {
            return $this->createHTML($content);
        }
        return $content;
    }

    function createHTML($content) {
        $html = '<h3>' . get_option('wcp_headline', 'Post Statistics') . '</h3><p>';
        // get word count once because both wordcount and read time will need it.
        if (get_option('wcp_wordcount', '1') OR get_option('wcp_readtime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if (get_option('wcp_wordcount', '1')) {
            $html .= esc_html__('This post has', 'wcpdomain') . ' ' . $wordCount . ' ' . __('words.', 'wcpdomain') . '<br>';
        }

        if (get_option('wcp_charcount', '1')) {
            $html .= 'This post has ' . strlen(strip_tags($content)) . ' characters.<br>';
        }

        if (get_option('wcp_readtime', '1')) {
            $html .= 'This post will take about ' . round($wordCount/255) . ' minute(s) to read.<br>';
        }

        $html .= '</p>';

        if (get_option('wcp_location', '0') == '0') {
            return $html . $content;
        }
        return $content . $html;
    }

    function settings() {
        add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');

        // Display Location
        add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

        // Headline text
        add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_headline', array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

        // Word count checkbox
        add_settings_field('wcp_wordcount', 'Word Count', array($this, 'wordcountHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_wordcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        // Character count
        add_settings_field('wcp_charcount', 'Character count', array($this, 'charcountHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_charcount', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        // Read time
        add_settings_field('wcp_readtime', 'Read time', array($this, 'readtimeHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_readtime', array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        // Color
        add_settings_field('wcp_color', 'Pick a color', array($this, 'colorHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_color', array('sanitize_callback' => 'sanitize_text_field', 'default' => '#ffffff'));
    }

    function sanitizeLocation($input) {
        if ($input != '0' AND $input != '1') {
            add_settings_error('wcp_location', 'wcp_location_error', 'Value of (Display Location) is not correct.');
            return get_option('wcp_location');
        }
        return $input;
    }

    function colorHTML() { ?>
        <input type="color" name="wcp_color" value="#ffffff">
    <?php }

    function readtimeHTML() { ?>
        <input type="checkbox" name="wcp_readtime" value="1" <?php checked(get_option('wcp_readtime'), '1');?>>
    <?php }

    function charcountHTML() { ?>
        <input type="checkbox" name="wcp_charcount" value="1" <?php checked(get_option('wcp_charcount'), '1');?>>
    <?php }

    function wordcountHTML() { ?>
        <input type="checkbox" name="wcp_wordcount" value="1" <?php checked(get_option('wcp_wordcount'), '1');?>>
    <?php }

    function headlineHTML() { ?>
        <input type="text" name="wcp_headline" value="<?php echo get_option('wcp_headline'); ?>">
    <?php }

    function locationHTML() { ?>
        <select name="wcp_location">
            <option value="0" <?php selected(get_option('wcp_location'), '0');?>>Beginnig of post</option>
            <option value="1" <?php selected(get_option('wcp_location'), '1');?>>End of Post</option>
        </select>
    <?php }

    function adminPage() {
        add_options_page('Word Count Settings', __('Word Count', 'wcpdomain'), 'manage_options', 'word-count-settings-page', array($this, 'settingsHTML'));
    }
   
    function settingsHTML() { ?>
       <div class="wrap">
            <h1>Word Count Settings</h1>
            <form action="options.php" method="POST">
                <?php
                    settings_fields('wordcountplugin');
                    do_settings_sections('word-count-settings-page');
                    submit_button();
                ?>
            </form>
       </div>
    <?php }
 }

 $wordCountAndTimePlugin = new WordCountAndTimePlugin();