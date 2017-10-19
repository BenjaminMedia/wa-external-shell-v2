<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       bonnier.dk
 * @since      1.0.0
 *
 * @package    Wa_External_Header_V2
 * @subpackage Wa_External_Header_V2/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wa_External_Header_V2
 * @subpackage Wa_External_Header_V2/admin
 * @author     Bonnier Interactive <interactive@bonnier.dk>
 */
class Wa_External_Header_V2_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $options_group_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $options_group_name ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options_group_name = $options_group_name;

	}

	public static function languagesIsEnabled()
	{
		return function_exists('Pll') && PLL()->model->get_languages_list();
	}

	public function getLanguages()
	{
		if (self::languagesIsEnabled()) {
			return PLL()->model->get_languages_list();
		}
		return false;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wa_External_Header_V2_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wa_External_Header_V2_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wa-external-header-v2-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wa_External_Header_V2_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wa_External_Header_V2_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wa-external-header-v2-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_admin_menu() {
		add_options_page(
				'Bonnier co-branding settings',
				'Bonnier WA Shell',
				'manage_options',
				$this->plugin_name,
				array(&$this, 'options_page')
		);
	}

	public function init_admin_settings(  ) {
		register_setting( $this->plugin_name, $this->options_group_name );

		add_settings_section(
				($this->plugin_name . '_section'),
				__( 'Your section description', $this->plugin_name ),
				array(&$this, 'settings_section_callback'),
				$this->plugin_name
		);

		add_settings_section(
				($this->plugin_name . '_dfp_section'),
				__( 'DFP Fields', $this->plugin_name ),
				array(&$this, 'dfp_settings_section_callback'),
				$this->plugin_name
		);

		add_settings_field(
				'co_branding_domain',
				__( 'Co-branding domain <br><small>(domain only, eg. costume.no)</small>', $this->plugin_name ),
				array(&$this, 'co_branding_domain_render'),
				$this->plugin_name,
				($this->plugin_name . '_section')
		);

		add_settings_field(
				'content_unit_category',
				__( 'Emediate content unit category <br><small>(sometimes referred to as "<i>shortname</i>")</small>', $this->plugin_name ),
				array(&$this, 'content_unit_category_render'),
				$this->plugin_name,
				($this->plugin_name . '_section')
		);

		add_settings_field(
				'tns_tracking_path',
				__( 'TNS path for tracking', $this->plugin_name ),
				array(&$this, 'tns_tracking_path_render'),
				$this->plugin_name,
				($this->plugin_name . '_section')
		);
		add_settings_field(
				'bp_optional_banners',
				__( 'Show banners', $this->plugin_name ),
				array(&$this, 'bp_optional_banners_render'),
				$this->plugin_name,
				($this->plugin_name . '_section')
		);
		add_settings_field(
				'bp_full_shell',
				__( 'Full shell', $this->plugin_name ),
				array(&$this, 'bp_full_shell_render'),
				$this->plugin_name,
				($this->plugin_name . '_section')
		);
		add_settings_field(
				'bp_hide_shell',
				__( 'Hide Shell?', $this->plugin_name ),
				array(&$this, 'bp_hide_shell_render'),
				$this->plugin_name,
				($this->plugin_name . '_section')
		);
        add_settings_field(
            'compact_menu',
            __( 'Compact Header / menu?', $this->plugin_name ),
            array(&$this, 'compact_menu_render'),
            $this->plugin_name,
            ($this->plugin_name . '_section')
        );
		add_settings_field(
				'head_hook',
				__( 'Head Hook <br /><small>(only add this if you have something other than "wp_head")</small>', $this->plugin_name ),
				array(&$this, 'head_hook_render'),
				$this->plugin_name,
				($this->plugin_name . '_section')
		);
		/* --- WHITEALBUM V2 AUTHENTICATION FIELDS --- */
		add_settings_field(
				'wa_api_uid',
				__( 'WhiteAlbum API Token <br /><small>(V2 only)</small>', $this->plugin_name ),
				array(&$this, 'wa_api_uid_render'),
				$this->plugin_name,
				($this->plugin_name . '_section')
		);
		add_settings_field(
				'wa_api_secret',
				__( 'WhiteAlbum API secret <br /><small>(V2 only)</small>', $this->plugin_name ),
				array(&$this, 'wa_api_secret_render'),
				$this->plugin_name,
				($this->plugin_name . '_section')
		);
		/* --- WHITEALBUM V2 AUTHENTICATION FIELDS --- */

		/* --- V3 DFP FIELDS --- */
		add_settings_field(
				'overwrite_site_type',
				__( 'Overwrite Site Type?', $this->plugin_name ),
				array(&$this, 'wa_api_overwrite_site_type_render'),
				$this->plugin_name,
				($this->plugin_name . '_dfp_section')
		);

		add_settings_field(
				'site_type',
				__( 'Site Type', $this->plugin_name ),
				array(&$this, 'wa_api_site_type_render'),
				$this->plugin_name,
				($this->plugin_name . '_dfp_section')
		);

		add_settings_field(
				'sub_name',
				__( 'Sub Name (Name of Site)', $this->plugin_name ),
				array(&$this, 'wa_api_sub_name_render'),
				$this->plugin_name,
				($this->plugin_name . '_dfp_section')
		);
	}

	public function optional_banners_render(  ) {
		echo $this->build_settings_field('bp_optional_banners_render');
	}

	public function co_branding_domain_render(  ) {
		echo $this->build_settings_field('co_branding_domain');
	}

	public function content_unit_category_render(  ) {
		echo $this->build_settings_field('content_unit_category');
	}

	public function tns_tracking_path_render(  ) {
		echo $this->build_settings_field('tns_tracking_path');
	}

	public function bp_optional_banners_render(  ) {
		echo $this->build_settings_checkbox('bp_optional_banners');
	}

	public function bp_full_shell_render(  ) {
		echo $this->build_settings_checkbox('bp_full_shell');
	}

	public function bp_hide_shell_render(  ) {
		echo $this->build_settings_checkbox('bp_hide_shell');
	}

    public function compact_menu_render(){
        echo $this->build_settings_checkbox('compact_menu');
    }

	public function head_hook_render(){
		echo $this->build_settings_field('head_hook');
	}

	/* --- WHITEALBUM V2 AUTHENTICATION FIELDS --- */
	public function wa_api_uid_render(  ) {
		echo $this->build_settings_field('wa_api_uid');
	}

	public function wa_api_secret_render(  ) {
		echo $this->build_settings_field('wa_api_secret');
	}
	/* --- WHITEALBUM V2 AUTHENTICATION FIELDS --- */

	/* --- V3 DFP FIELDS --- */

	public function wa_api_overwrite_site_type_render(){
		echo $this->build_settings_checkbox('overwrite_site_type');
	}

	public function wa_api_site_type_render(){
		echo $this->build_settings_field('site_type');
	}

	public function wa_api_sub_name_render(){
		echo $this->build_settings_field('sub_name');
	}

	public function settings_section_callback(  ) {
		echo __( 'General Shell Settings', $this->plugin_name );
	}

	public function dfp_settings_section_callback(  ) {
		echo __( 'DFP fields added to the html head of the blog, to make banners more targeted.', $this->plugin_name );
	}

	public function options_page(  ) {
		include_once plugin_dir_path( dirname( __FILE__ ) ) . "admin/partials/$this->plugin_name-admin-display.php";
	}

	private function build_settings_field($option_key) {
		if(self::languagesIsEnabled()){
			$fields = '';
			foreach($this->getLanguages() as $language){
				$options = get_option( $this->options_group_name );
				$value = (isset($options[$option_key. '_' . $language->locale]) ? $options[$option_key . '_' . $language->locale] : '');
				 $fields .= $language->flag.'&nbsp;<input type="text" name="' . $this->options_group_name . '['. $option_key. '_' . $language->locale .']" value="' . $value . '"><br />';
			}
			return $fields;
		}
		$options = get_option( $this->options_group_name );
		$value = (isset($options[$option_key]) ? $options[$option_key] : '');

		return '<input type="text" name="' . $this->options_group_name . '['. $option_key .']" value="' . $value . '">';
	}

	private function build_settings_checkbox($option_key) {
		if(self::languagesIsEnabled()){
			$fields = '';
			foreach($this->getLanguages() as $language){
				$options = get_option( $this->options_group_name );
				$value = (isset($options[$option_key. '_' . $language->locale]) ? $options[$option_key . '_' . $language->locale] : '');
				$checked = ($value == 'true') ? 'checked="checked"' : '';

				$fields .= $language->flag.'&nbsp;<input type="checkbox" value="true" name="' . $this->options_group_name . '['. $option_key. '_' . $language->locale .']" '.$checked.'><br />';
			}
			return $fields;
		}
		$options = get_option( $this->options_group_name );
		$value = (isset($options[$option_key]) ? $options[$option_key] : '');

		$checked = ($value == 'true') ? 'checked="checked"' : '';

		return '<input type="checkbox" value="true" name="' . $this->options_group_name . '['. $option_key .']" '.$checked.'>';
	}

	private function build_settings_dropdown($option_key,$dropdown_options){
		if(self::languagesIsEnabled()){
			$fields = '';
			foreach($this->getLanguages() as $language){
				$options = get_option( $this->options_group_name );
				$dropdown_output = '<select name="'.$this->options_group_name.'['. $option_key . '_' . $language->locale.']">';

				foreach($dropdown_options as $dropdown_key => $dropdown_value){
					$selected = ($options[$option_key. '_' . $language->locale] === $dropdown_value) ? 'selected' : '';
					$dropdown_output .= $language->flag.'&nbsp;<option value="'.$dropdown_value.'" '.$selected.'>'.$dropdown_key.'</option>';
				}

				$dropdown_output .= "</select>";
				$fields .= $dropdown_output;
			}
			return $fields;
		}
		$options = get_option( $this->options_group_name );
		$dropdown_output = '<select name="'.$this->options_group_name.'['. $option_key .']">';

		foreach($dropdown_options as $dropdown_key => $dropdown_value){
			$selected = ($options[$option_key] === $dropdown_value) ? 'selected' : '';
			$dropdown_output .= '<option value="'.$dropdown_value.'" '.$selected.'>'.$dropdown_key.'</option>';
		}

		$dropdown_output .= "</select>";
		return $dropdown_output;
	}
}
