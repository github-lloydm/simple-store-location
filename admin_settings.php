<?php
require_once(ABSPATH . "wp-includes/pluggable.php");

if (!defined('WPINC'))  die;
if (!defined('ABSPATH')) {
    exit;
}


class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('admin_head', array($this, 'MySettingsPage_style'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_libs'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Store Locations',
            'manage_options',
            'sl-admin-setting',
            array($this, 'create_admin_page')
        );
    }

    /**
     * Add Form styling
     */
    public function MySettingsPage_style()
    {
?>
        <style>
            .store-location-input {
                width: 60%;
            }

            .l_sl_gmarker {
                position: relative;
            }

            span.sl_marker_thumbnail {
                position: relative;
                top: -25px;
            }

            input#gmap_marker {
                position: absolute;
                top: 0;
                width: 53.6%;
                left: 95px;
            }

            input#image_url {
                position: absolute;
                width: 100%;
                border: 0px;
                background: #f2f2f2;
                box-shadow: none;
                font-size: 12px;
                font-style: italic;
            }
        </style>
    <?php
    }


    public function load_admin_libs()
    {
        wp_enqueue_media();
        wp_enqueue_script('wp-media-uploader', plugin_dir_url(__FILE__) . 'js/wp_media_uploader.js', array('jquery'), 1.0);
    }



    public function select_store()
    {
        $store = array();

        $query = new WP_Query('post_type=lam-store-locations&posts_per_page=-1');

        // echo '<pre>'. print_r( $query,true) .'</pre>';
        if ($query->have_posts()) {
            $ctr = 0;
            while ($query->have_posts()) {
                $query->the_post();
                $lat = get_post_meta(get_the_ID(), 'store_locator_latitude', TRUE);
                $lng = get_post_meta(get_the_ID(), 'store_locator_longitude', TRUE);
                $store[$ctr++] = array('ID' => get_the_ID(), 'title' => get_the_title(), 'latlng' => "$lat,$lng");
            }
            wp_reset_postdata();
        }
        //echo '<pre>'. print_r( $store,true) .'</pre>'; 
        return $store;
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('my_option_name');
    ?>
        <div class="wrap">

            <h1>Store Location Settings</h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('my_option_group');
                do_settings_sections('sl-admin-setting');
                submit_button();

                echo '<pre>' . print_r($this->options, true) . '</pre>';

                ?>
            </form>
        </div>
    <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'my_option_group', // Option group
            'my_option_name', // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            array($this, 'print_section_info'), // Callback
            'sl-admin-setting' // Page
        );

        add_settings_field(
            'gmap_api', // ID
            'Your Gmap API Key', // Title 
            array($this, 'api_key_callback'), // Callback
            'sl-admin-setting', // Page
            'setting_section_id' // Section           
        );


        add_settings_field(
            'gmap_marker',
            'Google Map Marker',
            array($this, 'title_callback'),
            'sl-admin-setting',
            'setting_section_id'
        );

        //SETTING THE MAIN STORE
        add_settings_field(
            'gmap_center_store',
            'Main Store',
            array($this, 'store_callback'),
            'sl-admin-setting',
            'setting_section_id'
        );

        // GOOGLE MAP TYPE
        add_settings_field(
            'gmap_type',
            'Google Map Type',
            array($this, 'gmap_type_callback'),
            'sl-admin-setting',
            'setting_section_id'
        );

        // GOOGLE MAP ZOOM
        add_settings_field(
            'gmap_zoom',
            'Google Map Zoom',
            array($this, 'gmap_zoom_callback'),
            'sl-admin-setting',
            'setting_section_id'
        );


        //// CHOOS WHERE TO DISABLE THIS MAP
        /*
             add_settings_field(
            'disable_gmap_js_lib', 
            'Do want to disable Google map in the post/page?', 
            array( $this, 'disable_gmapjslib' ), 
            'sl-admin-setting', 
            'setting_section_id'
        );
       */
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input['gmap_api']))
            $new_input['gmap_api'] = sanitize_text_field($input['gmap_api']);

        if (isset($input['gmap_marker']))
            $new_input['gmap_marker'] = sanitize_text_field($input['gmap_marker']);

        if (isset($input['gmap_center_store']))
            $new_input['gmap_center_store'] = sanitize_text_field($input['gmap_center_store']);

        if (isset($input['gmap_type']))
            $new_input['gmap_type'] = sanitize_text_field($input['gmap_type']);

        if (isset($input['gmap_zoom']))
            $new_input['gmap_zoom'] = sanitize_text_field($input['gmap_zoom']);




        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function api_key_callback()
    {
        printf(
            '<input type="text" class="store-location-input" id="gmap_api" name="my_option_name[gmap_api]" value="%s" />',
            isset($this->options['gmap_api']) ? esc_attr($this->options['gmap_api']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */

    public function title_callback()
    {

        echo '<div class="l_sl_gmarker">';

        echo (!empty($this->options['gmap_marker'])) ? '<span class="sl_marker_thumbnail"><img width="100" height="100" src="' . $this->options['gmap_marker'] . '" /></span>' : '';
        echo '<input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Upload Image">';

        printf(
            '<input type="hidden"  name="my_option_name[gmap_marker]" id="image_url" class="regular-text" value="' . $this->options['gmap_marker'] . '" />',
            isset($this->options['gmap_marker']) ? esc_attr($this->options['gmap_marker']) : ''
        );

        echo '</div>';
    }

    public function store_callback()
    {
        $data_ = MySettingsPage::select_store();

        extract($this->options);

        $opt = explode('|', $gmap_center_store);

        echo '<select name="my_option_name[gmap_center_store]" id="gmap_center_store" style="width:40%;">';

        echo '<option value="' . $opt[0] . '|' . $opt[1] . '">' . $opt[1] . '</option>';

        $ctr = 0;
        foreach ($data_ as $data => $value) {
            $val = $value['latlng'] . '|' . $value['title'];
            $val_ = explode('|', $val);

            echo '<option value="' . $value['latlng'] . '|' . $value['title'] . '">' . $val_[1] . '</option>';
        }


        echo '</select>';
    }

    public function gmap_type_callback()
    {
    ?>
        <select name="my_option_name[gmap_type]" id="gmap_type" style="width:40%;">
            <?php
            echo '<option>' . $this->options['gmap_type'] . '</option>';
            ?>
            <option value="ROADMAP">ROADMAP (normal, default 2D map)</option>
            <option value="SATELLITE">SATELLITE (photographic map)</option>
            <option value="HYBRID">HYBRID (photographic map + roads and city names)</option>
            <option value="TERRAIN">TERRAIN (map with mountains, rivers, etc.)</option>
        </select>
    <?php
    }


    public function gmap_zoom_callback()
    {
    ?>
        <select name="my_option_name[gmap_zoom]" id="gmap_zoom" style="width: 100px;">
            <?php
            echo '<option>' . $this->options['gmap_zoom'] . '</option>';
            for ($ctr = 20; $ctr > 0; $ctr--) {
                echo '<option value="' . $ctr . '">Zoom ' . $ctr . '</option>';
            }
            ?>
        </select>
<?php
    }


    /*
    public function disable_gmapjslib(){
        
        $wpposts = new WP_Query( array('post_type' => array('page','post'), 'parent' => 0, 'posts_per_page' => -1, 'post_status' => 'publish') );
        
        $chkbox = array();

        echo '<ul class="sl_pagelist">';
            
            foreach($wpposts->posts as $wppost){ 
                $checkbox = (!isset($chkbox['sl_pages']))? '':" checked='checked' ";
                echo '<li>';
                echo    '<input id="sl_pages" type="checkbox" name="my_option_name[sl_pages]" value="'.$wppost->post_name.'_'.$wppost->ID.'" '.$checkbox.'><span>'.$wppost->post_title.'</span>';
                echo '</li>';
            }
            wp_reset_postdata();

        echo '</ul>';    
    }
    */
}

new MySettingsPage();
