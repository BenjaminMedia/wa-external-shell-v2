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

    private static $instance;

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
     * @return mixed
     */
    public function getUserConfig()
    {
        return $this->user_config;
    }

    public function getOption($option, $locale = null){
        if(isset($this->getUserConfig()[$option])){
            return $this->getUserConfig()[$option];
        }
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if(Wa_External_Header_V2_Admin::languagesIsEnabled()){
            $locale = (!empty($locale)) ? $locale : $this->getCurrentLanguage();
            if(isset($this->getUserConfig()[$option.'_'. $locale])){
                return $this->getUserConfig()[$option.'_'.$locale];
            }
            return null;
        }
        return null;
    }

    /**
     * @param mixed $user_config
     */
    public function setUserConfig($user_config)
    {
        $this->user_config = $user_config;
    }

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

        $this->setUserConfig($this->get_plugin_configuration());
    }

    public function setShellProperties(){
        $wa_content = $this->get_white_album_content();

        if ($wa_content !== null) {
            $this->start_tag = print_r($wa_content->html->start_tag, true);
            $this->end_tag = print_r($wa_content->html->end_tag, true);
            $gtmRegex = '/<!-- Google Tag Manager -->.*<!-- End Google Tag Manager -->/is';
            preg_match($gtmRegex, $wa_content->html->body->header, $matches);
            $googleTagManager = !empty($matches[0]) ? $matches[0] : '';
            $this->head = print_r($wa_content->html->head, true) . $googleTagManager;
            // remove google tag manager from header
            $this->header = preg_replace($gtmRegex, '', $wa_content->html->body->header);
            $this->footer = print_r($wa_content->html->body->footer, true);
            $this->banners = print_r($wa_content->html->ad, true);
        }
    }

    public function getBanners(){
        return $this->banners;
    }

    public static function getInstance($plugin_name = 'wa-external-header-v2', $version = '1.0.0', $options_group_name = 'wa-external-header-v2_settings')
    {
        if (null === static::$instance) {
            static::$instance = new static($plugin_name, $version, $options_group_name);
        }

        return static::$instance;
    }

    public function wp_head()
    {
        $this->setShellProperties();
        $subname = $this->getOption('sub_name');
        $content_unit_category = $this->getOption('content_unit_category');
        $tns_path = $this->getOption('tns_tracking_path');
        $hideShell = !empty($this->getOption('bp_hide_shell')) ? true : false;

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
        
        echo
            $this->head.
            "<meta content=\"".$subname."\" name=\"bcm-sub\" />";
    }

    public function shell_header()
    {
        $this->setShellProperties();
        $subname = $this->getOption('sub_name');
        $content_unit_category = $this->getOption('content_unit_category');
        $tns_path = $this->getOption('tns_tracking_path');
        $hideShell = !empty($this->getOption('bp_hide_shell')) ? true : false;
        if ($this->header !== '' && !$hideShell) {
            $this->insert_header();
        }
        else if(!$hideShell){
            echo $this->remove_header($this->header);
        }
    }

    public function wp_footer()
    {
        $hideShell = isset($this->getUserConfig()['bp_hide_shell']);
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
        $locale = !empty($this->getCurrentLanguage()) ? $this->getCurrentLanguage() : null;
        //try to see if it's in the cache
        $responseCache = wp_cache_get( 'shell_response_body' . $locale, $this->options_group_name );
        if ( false === $responseCache ) {

            //if the cache is empty, try to fetch the shell
            $url = $this->get_white_album_api_url();
            $args = array(
                'headers' => $this->get_authentication_header()
            );

            $shellResponseBody = wp_remote_retrieve_body(wp_remote_get($url, $args));

            $cacheFileFolder = trailingslashit(WP_CONTENT_DIR) . 'cache/wa-shell/';
            $fileName = parse_url($url)['host'];
            $cacheFilePath = $cacheFileFolder . $fileName. '-' . $locale .'.json';

            if (!file_exists($cacheFileFolder)) {
                mkdir($cacheFileFolder, 0777, true);
            }

            if (!empty($shellResponseBody)) {
                // if the shell response is not empty (/ checking if it returns an error), then write the shell to our local file
                file_put_contents($cacheFilePath, $shellResponseBody);
            }
            if(!file_exists($cacheFilePath)){
                return;
            }
            //put the local file's content into cache, so that the response can be fetched faster
            $newresponseCache = file_get_contents($cacheFilePath);
            wp_cache_set( 'shell_response_body' . $locale, $newresponseCache, $this->options_group_name );
            return json_decode($newresponseCache);
        }

        return json_decode($responseCache);
    }

    /**
     * Get the current language by looking at the current HTTP_HOST
     *
     * @return null|PLL_Language
     */
    public function getCurrentLanguage()
    {
        if (Wa_External_Header_V2_Admin::languagesIsEnabled()) {
            return pll_current_language('locale');
        }
        return null;
    }

    private function get_white_album_api_url()
    {
        $locale = !empty($this->getCurrentLanguage()) ? $this->getCurrentLanguage() : null;
        $api_url = wp_cache_get( 'shell_api_url' . $locale, $this->options_group_name );
        if ( false === $api_url ) {
            $domain = $this->getOption('co_branding_domain');
            $host = "$domain";
            $showBanners = !empty( $this->getOption('bp_optional_banners')) ? 'false' : 'true';
            $fullShell = !empty( $this->getOption('bp_full_shell')) ? 'false' : 'true';
            $siteType = !empty( $this->getOption('site_type')) ? $this->getOption('site_type') : false;
            $bcmType = !empty( $this->getOption('overwrite_site_type')) ? '&bcm_type=' . $siteType : false;
            $compactMenu = !empty( $this->getOption('compact_menu')) ? "&menu_type=compact" : false;
            $api_url = "http://$host/api/v3/external_headers/?partial=" . $fullShell . "$compactMenu&without_ads=" . $showBanners . $bcmType;
            wp_cache_set( 'shell_api_url' . $this->getCurrentLanguage(), $api_url, $this->options_group_name );

            return $api_url;
        }

        return $api_url;
    }

    private function get_authentication_header()
    {
        return array(
            'Authorization' => 'Basic ' . base64_encode($this->getOption('wa_api_uid') . ':' . $this->getOption('wa_api_secret'))
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
        do_action('before_wa_shell_start');
        echo '
            <div class="'.$this->white_album_css_namespace.'">
            '.$this->afubar;
            do_action('before_wa_shell_header');
            echo $this->header."
            </div>";
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

    public function showShellBanners(){
        return isset($this->getUserConfig()['bp_optional_banners']);
    }

}
