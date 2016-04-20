<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       bonnier.dk
 * @since      1.0.0
 *
 * @package    Wa_External_Header_V2
 * @subpackage Wa_External_Header_V2/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wa_External_Header_V2
 * @subpackage Wa_External_Header_V2/public
 * @author     Bonnier Interactive <interactive@bonnier.dk>
 */
class Wa_External_Header_V2_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The key used to save and load required options from the WordPress database.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The key used for the array that serializes the options in the database.
     */
    private $options_group_name;

    private $white_album_css_namespace,
        $afubar,
        $header,
        $footer,
        $user_config,
        $start_tag,
        $end_tag,
        $banners,
        $head;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @var      string $plugin_name The name of the plugin.
     * @var      string $version The version of this plugin.
     * @var      string $options_group_name The key used to save/load plugin-specific options from the database.
     */
    public function __construct($plugin_name, $version, $options_group_name)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options_group_name = $options_group_name;
        $this->white_album_css_namespace = 'bonnier-wrapper';

        $this->user_config = $this->get_plugin_configuration();

        $wa_content = $this->get_white_album_content();

        if ($wa_content !== null) {
            $this->start_tag = print_r($wa_content->html->start_tag, true);
            $this->end_tag = print_r($wa_content->html->end_tag, true);
            $this->head = print_r($wa_content->html->head, true);
            preg_match("/^(.*)(<header .*)$/s", $wa_content->html->body->header, $siteHeader);
            $this->afubar = $siteHeader[1];
            $this->header = $siteHeader[2];
            $this->footer = print_r($wa_content->html->body->footer, true);
            $this->banners = print_r($wa_content->html->banners, true);
        }
    }

    public function wp_head()
    {
        $content_unit_category = $this->user_config['content_unit_category'];
        $tns_path = $this->user_config['tns_tracking_path'];
        $hideShell = isset($this->user_config['bp_hide_shell']);

        if (!empty($tns_path)) {
            //<div data-tns-path="MMK/COSTUME/ExBlogger"></div>
            $this->afubar = preg_replace(
                "/<div data\-tns\-path=\"(.*?)\"><\/div>/",
                "<div data-tns-path=\"$tns_path\"></div>",
                $this->afubar
            );
            //<div data-tns-path="$tns_path></div>
        }

        if($hideShell){
            $this->head = preg_replace("/<link href=\".*?\" rel=\"(apple\-touch\-icon|shortcut icon)\" type=\"image\/png\" \/>/","",$this->head);
        }

        echo "
            <meta name=\"banner-category\" content=\"$content_unit_category\">
                $this->head
            ";

        if ($this->header !== '' && !$hideShell) {
            $this->insert_header();
        }
        else if(!$hideShell){
            echo $this->remove_header($this->header);
        }

    }

    public function wp_footer()
    {
        $hideShell = isset($this->user_config['bp_hide_shell']);
        if ($this->header !== '' && !$hideShell) {

            echo <<< HTML
        <div class="$this->white_album_css_namespace">
          $this->footer
        </div>
HTML;
        }
    }

    /**
     * Load FontAwesome here, instead of grabbing it from the WhiteAlbum API.
     *
     * @since    1.0.0
     */
    public function enqueue_fontawesome_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in White_Album_External_Header_Public_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The White_Album_External_Header_Public_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array(), $this->version, 'all');
    }


    private function get_white_album_content()
    {
        $url = $this->get_white_album_api_url();
        $args = array(
            'headers' => $this->get_authentication_header()
        );
        $response = wp_remote_retrieve_body(wp_remote_get($url, $args));

        if (is_wp_error($response)) return;

        return json_decode($response);
    }

    private function get_white_album_api_url()
    {
        $domain = $this->user_config['co_branding_domain'];
        $host = "$domain";
        $showBanners = isset($this->user_config['bp_optional_banners']) ? 'false' : 'true';
        $fullShell = isset($this->user_config['bp_full_shell']) ? 'false' : 'true';
        $api_url = "http://$host/api/v2/external_headers/?partial=".$fullShell."&without_banners=".$showBanners;
        return $api_url;
    }

    private function get_authentication_header()
    {
        return array(
            'Authorization' => 'Basic ' . base64_encode($this->user_config['wa_api_uid'] . ':' . $this->user_config['wa_api_secret'])
        );
    }

    private function replace_tns_tracking($new_path,$source)
    {
        return (preg_replace(
            "/<meta.*?name=\"tns-path\"[\s\/]+>/",
            "<meta content=\"$new_path\" name=\"tns-path\">",
            $source
        ));
    }

    private function insert_header()
    {
        echo '
            <div class="'.$this->white_album_css_namespace.'">
            '.$this->afubar;
        do_action('before_wa_shell_header');
        echo $this->header.'
            </div>';
    }

    private function remove_header($buffer)
    {

        $header = <<< HTML
        $this->header
HTML;
        return preg_replace("/<div class=\"bonnier\-wrapper\">.*<\/header> <\/div>/","",$header);
    }

    private function get_plugin_configuration()
    {
        return get_option($this->options_group_name);
    }

}
