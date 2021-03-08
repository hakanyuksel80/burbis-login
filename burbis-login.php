<?php 

/*
Plugin Name:  BURBİS WORDPRESS LOGİN EKLENTİSİ
Plugin URI:   http://hakanyuksel.com.tr
Description:  BUYÖN Login Formu Eklentisi
Version:      20181003
Author:       Hakan YÜKSEL
Author URI:   http://www.hakanyuksel.com.tr
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
Domain Path:  /
*/




class BurbisLoginPlugin {
	
	public $plugin_name = "burbis-login";

    function __construct() {
        
    	add_action("wp_enqueue_scripts", array($this,'login_script_enqueue'));
		
		add_action('widgets_init',array($this, 'register_login_widget') );
		
		//Ajax postuna cevap verecek fonksiyonu olay listesine ekleyelim		
		add_action('wp_ajax_nopriv_buyon_login', array($this,'login_ajax_handler'));
		
		add_action('wp_ajax_buyon_logout', array($this,'logout_ajax_handler'));

		//Ayarlar
		add_action('admin_menu', array( $this, 'addPluginAdminMenu' ), 9);  

		add_action('admin_init', array( $this, 'registerAndBuildFields' ));

		add_action('wp_logout',array($this,'unlog'));


	}

	function unlog(){
		wp_redirect( site_url() );
		exit();
	}	
	
	public function addPluginAdminMenu() {
		//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page(  $this->plugin_name, 'Burbis Login', 'administrator', $this->plugin_name, array( $this, 'displayPluginAdminDashboard' ), 'dashicons-chart-area', 26 );
		
		//add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		add_submenu_page( $this->plugin_name, 'Burbis Login Ayarları', 'Ayarlar', 'administrator', $this->plugin_name.'-settings', array( $this, 'displayPluginAdminSettings' ));
	}
	
	public function displayPluginAdminDashboard() {
		
		require_once 'partials/'.$this->plugin_name.'-admin.php';
	}
	  

	public function displayPluginAdminSettings() {
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		if(isset($_GET['error_message'])){
			add_action('admin_notices', array($this,'SettingsMessages'));
			do_action( 'admin_notices', $_GET['error_message'] );
		}
		require_once 'partials/'.$this->plugin_name.'-admin-settings-display.php';

		
	}

	public function SettingsMessages($error_message){

		echo "$error_message";
		switch ($error_message) {
			case '1':
				$message = __( 'There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'my-text-domain' );                 
				$err_code = esc_attr( 'plugin_name_example_setting' );                 
				$setting_field = 'plugin_name_example_setting';                 
				break;
		}
		$type = 'error';
		add_settings_error(
			   $setting_field,
			   $err_code,
			   $message,
			   $type
		   );
	}

	public function registerAndBuildFields() {

		
		/**
		 * First, we add_settings_section. This is necessary since all future settings must belong to one.
		 * Second, add_settings_field
		 * Third, register_setting
		 */     
		add_settings_section(
			// ID used to identify this section and with which to register options
			'burbis_login_general_section', 
			// Title to be displayed on the administration page
			'',  
			// Callback used to render the description of the section
			array( $this, 'burbis_login_display_general_account' ),    
			// Page on which to add this section of options
			'burbis_login_general_settings'                   
		);

		unset($args);
		$args = array (
					'type'      => 'input',
					'subtype'   => 'text',
					'id'    => 'burbis_login_proje_id',
					'name'      => 'burbis_login_proje_id',
					'required' => 'true',
					'get_options_list' => '',
					'value_type'=>'normal',
					'wp_data' => 'option'
				);

		add_settings_field(
			'burbis_login_proje_id',
			'Proje Id',
			array( $this, 'render_settings_field' ),
			'burbis_login_general_settings',
			'burbis_login_general_section',
			$args
		);

		register_setting(
			'burbis_login_general_settings',
			'burbis_login_proje_id'
			);

		unset($args);
		$args = array (
			'type'      => 'input',
			'subtype'   => 'text',
			'id'    => 'buyon_login_sonra_url',
			'name'      => 'buyon_login_sonra_url',
			'required' => 'true',
			'get_options_list' => '',
			'value_type'=>'normal',
			'wp_data' => 'option'
		);

		add_settings_field(
			'buyon_login_sonra_url',
			'Girişten sonra yönlenilecek sayfa(URL)',
			array( $this, 'render_settings_field' ),
			'burbis_login_general_settings',
			'burbis_login_general_section',
			$args
		);
		register_setting(
			'burbis_login_general_settings',
			'buyon_login_sonra_url'
			);
		// --- Use ReCapthca
		unset($args);
		$args = array (
			'type'      => 'input',
			'subtype'   => 'checkbox',
			'id'    => 'buyon_login_recaptcha_use',
			'name'      => 'buyon_login_recaptcha_use',
			'required' => 'false',
			'get_options_list' => '',
			'value_type'=>'normal',
			'wp_data' => 'option'
		);

		add_settings_field(
			'buyon_login_recaptcha_use',
			'Google ReCAPTHCHA Kullan',
			array( $this, 'render_settings_field' ),
			'burbis_login_general_settings',
			'burbis_login_general_section',
			$args
		);
		register_setting(
			'burbis_login_general_settings',
			'buyon_login_recaptcha_use'
			);
		// ---  ReCapthca Site Key
		unset($args);
		$args = array (
			'type'      => 'input',
			'subtype'   => 'text',
			'id'    => 'buyon_login_recaptcha_site_key',
			'name'      => 'buyon_login_recaptcha_site_key',
			'required' => 'false',
			'get_options_list' => '',
			'value_type'=>'normal',
			'wp_data' => 'option'
		);

		add_settings_field(
			'buyon_login_recaptcha_site_key',
			'ReCAPTHCHA Site Anahtarı(Site Key)',
			array( $this, 'render_settings_field' ),
			'burbis_login_general_settings',
			'burbis_login_general_section',
			$args
		);
		register_setting(
			'burbis_login_general_settings',
			'buyon_login_recaptcha_site_key'
			);
		// ---  ReCapthca secret Key
		unset($args);
		$args = array (
			'type'      => 'input',
			'subtype'   => 'text',
			'id'    => 'buyon_login_recaptcha_secret_key',
			'name'      => 'buyon_login_recaptcha_secret_key',
			'required' => 'false',
			'get_options_list' => '',
			'value_type'=>'normal',
			'wp_data' => 'option'
		);

		add_settings_field(
			'buyon_login_recaptcha_secret_key',
			'ReCAPTHCHA Gizli Anahtar(Secret Key)',
			array( $this, 'render_settings_field' ),
			'burbis_login_general_settings',
			'burbis_login_general_section',
			$args
		);

		register_setting(
			'burbis_login_general_settings',
			'buyon_login_recaptcha_secret_key'
			);

	}

	public function render_settings_field($args) {
				/* EXAMPLE INPUT
						'type'      => 'input',
						'subtype'   => '',
						'id'    => $this->plugin_name.'_example_setting',
						'name'      => $this->plugin_name.'_example_setting',
						'required' => 'required="required"',
						'get_option_list' => "",
							'value_type' = serialized OR normal,
				'wp_data'=>(option or post_meta),
				'post_id' =>
				*/     
		if($args['wp_data'] == 'option'){
			$wp_data_value = get_option($args['name']);
		} elseif($args['wp_data'] == 'post_meta'){
			$wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
		}

		switch ($args['type']) {

			case 'input':
				$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
				if($args['subtype'] != 'checkbox'){
					$prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">'.$args['prepend_value'].'</span>' : '';
					$prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
					$step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
					$min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
					$max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
					if(isset($args['disabled'])){
						// hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
						echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
					} else {
						echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
					}
					/*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/

				} else {
					$checked = ($value) ? 'checked' : '';
					echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
				}
				break;
			default:
				# code...
				break;
		}
	}

	public function burbis_login_display_general_account($a) {

		
		//echo '<p>These settings apply to all Plugin Name functionality.</p>';
	  } 

	function activate() {
       //generate a CPT
       // flush rewrite rules
       // flush_rewrite_rules();
		
		global $wpdb;
		
	}
	
	 function deactivate() {
        // flush rewrite rules
    }
    
    // function uninstall() {
    //     //Delete CPT
    //     //Delete all plugin data from DB	
    // }
	
	function register_login_widget() {
			//register_widget( 'Burbis_Login_Widget' );
	}
	
	function login_script_enqueue(){
		
		
		 wp_register_script('burbis-login-ajax', 
                        plugins_url() .'/burbis-login/js/burbis_login.js',   //
                        array ('jquery'),                  //depends on these, however, they are registered by core already, so no need to enqueue them.
						false, true);	
						
		
		
		$title_nonce = wp_create_nonce( 'my_title_example');	
		
		wp_enqueue_script('burbis-login-ajax');	
		
		wp_localize_script( 'burbis-login-ajax', 'my_ajax_obj_login', array(
							'ajax_url' => admin_url( 'admin-ajax.php' ),
							'nonce'    => $title_nonce, // It is common practice to comma after
							
							) );// the last array item for easier maintenance

							
		
        wp_register_style('BurbisLoginPluginStylesheet',plugins_url( '/css/style.css', __FILE__ ));
        
		wp_enqueue_style('BurbisLoginPluginStylesheet' );
	}
	
	
	function BurbisConnect($username,$password)
	{
		$params = array(
			'id' => 51,
			'kadi'         => $username,
			'sifre'         => $password,
		);
		
		try {
	
			$client = new SoapClient($url,$options);
			$result = $client->__soapCall("kullanicigiris", array("kullanicigiris" => $params), null,$header);
			
			return $result;
		}
		catch (Exception $e) 
		{
			echo "<h2>Exception Error!</h2>";
			echo $e->getMessage();
			die();
		}
		
	}
	
	// Login düğmesine basıldığında çalışsacak ajax kodu
	function login_ajax_handler() {
		// Handle the ajax request
		 global $wpdb;
		
		$username = $_POST["user"];
		$password = $_POST["pass"];
		
		// BurbisKontrol		
		
		$burbis = new Burbis_Connect();
		
		$s = $burbis->buyon_login_with_bursis($username,$password);		
		
		echo $s;
		
		wp_die(); // All ajax handlers die when finished
	}
	
	function logout_ajax_handler() {
		
		wp_logout();
		
		//
		//$redirect_to = $_SERVER['REQUEST_URI'];
		//$url = site_url();
		//wp_safe_redirect($url);
							
		echo 0;
		
		wp_die();
	}

	


}



if (class_exists('BurbisLoginPlugin'))
{
	$aLogin = new BurbisLoginPlugin();
	
	//activate
	register_activation_hook(__FILE__, array($aLogin,'activate'));

	//deactivate
	register_deactivation_hook(__FILE__, array($aLogin,'deactivate'));

	//uninstall
	//register_uninstall_hook(__FILE__, array($aLogin,'uninstall'));

}


function register_my_setting() {
    $args = array(
            'type' => 'string', 
            'sanitize_callback' => 'sanitize_text_field',
            'default' => NULL,
            );
    register_setting( 'general', 'my_option_name', $args ); 
} 

add_action( 'admin_init', 'register_my_setting' );

require_once("burbis_login_shortcut.php");