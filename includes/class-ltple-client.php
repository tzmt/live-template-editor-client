<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client {

	/**
	 * The single instance of LTPLE_Client.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;
	
	public $_dev = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;
	public $_base;
	
	public $_time;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	public $server;
	public $user;
	public $layer;
	public $message;
	public $dialog;
	public $triggers;
	
	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	 
	public function __construct ( $file = '', $version = '1.0.0' ) {
		
		$this->_version = $version;
		$this->_token 	= 'ltple';
		$this->_base 	= 'ltple_';
		$this->dialog 	= new stdClass();	
		
		if( isset($_GET['_']) && is_numeric($_GET['_']) ){
			
			$this->_time = intval($_GET['_']);
		}
		else{
			
			$this->_time = time();
		}

		$this->message = '';
		
		// Load plugin environment variables
		$this->file 		= $file;
		$this->dir 			= dirname( $this->file );
		$this->views   		= trailingslashit( $this->dir ) . 'views';
		$this->vendor  		= WP_CONTENT_DIR . '/vendor';
		$this->assets_dir 	= trailingslashit( $this->dir ) . 'assets';
		$this->assets_url 	= esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		//$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$this->script_suffix = '';

		register_activation_hook( $this->file, array( $this, 'install' ) );
		
		// Handle localisation
		$this->load_plugin_textdomain();

		add_action( 'init', array( $this, 'load_localisation' ), 0 );			
		
		if(isset($_POST['imgData']) && isset($_POST["submitted"])&& isset($_POST["download_image_nonce_field"]) && $_POST["submitted"]=='true'){
			
			// dowload meme image
			
			//wp_verify_nonce($_POST["download_image_nonce_field"], "download_image_nonce");
			
			$data = sanitize_text_field($_POST['imgData']);
			
			list($type, $data) = explode(';', $data);
			list(, $data)      = explode(',', $data);
			
			header('Content-Description: File Transfer');
			header("Content-type: application/octet-stream");
			header("Content-disposition: attachment; filename= ltple_meme_image.png");
			
			exit(base64_decode($data));
		}
		else{		
			
			$this->client 		= new LTPLE_Client_Client( $this );
			$this->request 		= new LTPLE_Client_Request( $this );
			$this->urls 		= new LTPLE_Client_Urls( $this );
			$this->programs 	= new LTPLE_Client_Programs( $this );
			$this->stars 		= new LTPLE_Client_Stars( $this );
			$this->login 		= new LTPLE_Client_Login( $this );
			$this->rights 		= new LTPLE_Client_Rights( $this );
			
			// Load API for generic admin functions
			
			$this->admin 	= new LTPLE_Client_Admin_API( $this );
			$this->cron 	= new LTPLE_Client_Cron( $this );
			$this->email 	= new LTPLE_Client_Email( $this );
			$this->campaign = new LTPLE_Client_Campaign( $this );
				
			$this->api 		= new LTPLE_Client_Json_API( $this );
			$this->server 	= new LTPLE_Client_Server( $this );
			 
			$this->apps 	= new LTPLE_Client_Apps( $this );			
			$this->whois 	= new LTPLE_Client_Whois( $this );
			
			$this->leads 	= new LTPLE_Client_Leads( $this );
			
			$this->layer 	= new LTPLE_Client_Layer( $this );			
			$this->plan 	= new LTPLE_Client_Plan( $this );
			$this->product 	= new LTPLE_Client_Product( $this );

			$this->image 	= new LTPLE_Client_Image( $this );
			$this->domain 	= new LTPLE_Client_Domain( $this );
			$this->bookmark = new LTPLE_Client_Bookmark( $this );
			
			$this->users 	= new LTPLE_Client_Users( $this );			
			$this->channels = new LTPLE_Client_Channels( $this );			
			$this->profile 	= new LTPLE_Client_Profile( $this );
			
			if( is_admin() ) {
							
				add_action( 'init', array( $this, 'init_backend' ));	
			}
			else{
				
				add_action( 'init', array( $this, 'init_frontend' ));
			}			
		}

	} // End __construct ()
	
	private function ltple_get_secret_iv(){
		
		//$secret_iv = md5( $this->user_agent . $this->user_ip );
		//$secret_iv = md5( $this->user_ip );
		$secret_iv = md5( 'another-secret' );	

		return $secret_iv;
	}	
	
	private function ltple_encrypt_str($string){
		
		$output = false;

		$encrypt_method = "AES-256-CBC";
		
		$secret_key = md5( $this->client->key );
		
		$secret_iv = $this->ltple_get_secret_iv();
		
		// hash
		$key = hash('sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
		$output = $this->base64_urlencode($output);

		return $output;
	}
	
	private function ltple_decrypt_str($string){
		
		$output = false;

		$encrypt_method = "AES-256-CBC";
		
		$secret_key = md5( $this->client->key );
		
		$secret_iv = $this->ltple_get_secret_iv();

		// hash
		$key = hash( 'sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr( hash( 'sha256', $secret_iv ), 0, 16);

		$output = openssl_decrypt($this->base64_urldecode($string), $encrypt_method, $key, 0, $iv);

		return $output;
	}
	
	public function ltple_encrypt_uri($uri,$len=250,$separator='/'){
		
		$uri = wordwrap($this->ltple_encrypt_str($uri),$len,$separator,true);
		
		return $uri;
	}
	
	public function ltple_decrypt_uri($uri,$separator='/'){
		
		$uri = $this->ltple_decrypt_str(str_replace($separator,'',$uri));
		
		return $uri;
	}
	
	public function base64_urlencode($inputStr=''){

		return strtr(base64_encode($inputStr), '+/=', '-_,');
	}

	public function base64_urldecode($inputStr=''){

		return base64_decode(strtr($inputStr, '-_,', '+/='));
	}
	
	public function init_frontend(){
		
		// Load frontend JS & CSS
		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		add_action( 'wp_head', array( $this, 'get_header') );
		add_action( 'wp_footer', array( $this, 'get_footer') );				

		// add editor shortcodes
		
		add_shortcode('ltple-client-editor', array( $this , 'get_editor_shortcode' ) );

		// Custom default layer template

		add_filter('template_include', array( $this, 'editor_templates'), 1 );
		
		add_action('template_redirect', array( $this, 'editor_output' ));
	
		add_filter( 'pre_get_posts', function($query) {

			if ($query->is_search ) {
				
				$query->set('post_type',array('post','page'));
			}

			return $query;
		});	
	
		//get current user
		
		if( $this->request->is_remote ){

			$this->user = wp_set_current_user( get_user_by( 'id', $this->ltple_decrypt_str($_SERVER['HTTP_X_FORWARDED_USER'])));
		}
		elseif(1==2 && !empty($this->_dev) ){
			
			//debug user session
			
			$this->user = wp_set_current_user(15);
		}
		else{
			
			$this->user = wp_get_current_user();
		}
		
		$this->user->loggedin = is_user_logged_in();		
		
		if($this->user->loggedin){
		
			// get is admin
			
			$this->user->is_admin = current_user_can( 'administrator', $this->user->ID );
			
			// get user last seen
			
			$this->user->last_seen = intval( get_user_meta( $this->user->ID, $this->_base . '_last_seen',true) );
			
			// get user layers
			
			$this->user->layers = get_posts(array(
			
				'author'      => $this->user->ID,
				'post_type'   => 'user-layer',
				'post_status' => 'publish',
				'numberposts' => -1
			));	
			
			// get user stars
			
			$this->user->stars = $this->stars->get_count($this->user->ID);
			
			// get user programs
		
			$this->user->programs = json_decode( get_user_meta( $this->user->ID, $this->_base . 'user-programs',true) );
			
			// get user affiliate
			
			$this->user->is_affiliate = $this->programs->has_program('affiliate', $this->user->ID, $this->user->programs);
			
			if( $this->user->is_affiliate ){

				$this->user->affiliate_clicks 		= $this->programs->get_affiliate_counter($this->user->ID, 'clicks');
				$this->user->affiliate_referrals 	= $this->programs->get_affiliate_counter($this->user->ID, 'referrals');
				$this->user->affiliate_commission 	= $this->programs->get_affiliate_counter($this->user->ID, 'commission');
			}
					
			// get user ref id
			
			$this->user->refId = $this->ltple_encrypt_uri( 'RI-' . $this->user->ID );	
			
			// get user rights
			
			$this->user->rights = json_decode( get_user_meta( $this->user->ID, $this->_base . 'user-rights',true) );

			//get user layer
			
			if( $this->layer->type == 'user-layer' ){
				
				if( $this->user->is_admin ){
				
					$q = get_posts(array(
					
						'name'        => $this->layer->slug,
						'post_type'   => 'user-layer',
						'post_status' => 'publish',
						'numberposts' => 1
					));						
				}
				else{
					
					$q = $this->user->layers;				
				}
				
				//var_dump( $q );exit;
				
				if(isset($q[0])){
					
					$this->user->layer=$q[0];
				}
				
				unset($q);
			}
		}
		
		// newsletter unsubscription
		
		if(!empty($_GET['unsubscribe'])){
		
			$unsubscriber_id = $this->ltple_decrypt_uri(sanitize_text_field($_GET['unsubscribe']));
			
			if(is_numeric($unsubscriber_id)){
				
				update_user_meta(intval($unsubscriber_id), $this->_base . '_can_spam', 'false');

				$this->message ='<div class="alert alert-success">';

					$this->message .= '<b>Congratulations</b>! You successfully unsbuscribed from the newsletter';

				$this->message .='</div>';
			}
		}
		
		// loaded hook
		
		do_action( 'ltple_loaded');
	}
	
	
	public function init_backend(){
		
		// Load admin JS & CSS
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
		
		add_filter( 'page_row_actions', array($this, 'remove_custom_post_quick_edition'), 10, 2 );
		add_filter( 'post_row_actions', array($this, 'remove_custom_post_quick_edition'), 10, 2 );

		// add email-campaign
		
		add_filter("email-campaign_custom_fields", array( $this, 'add_campaign_trigger_custom_fields' ));
		
		// add user-image
	
		add_filter('manage_user-image_posts_columns', array( $this, 'set_user_image_columns'));
		add_action('manage_user-image_posts_custom_column', array( $this, 'add_user_image_column_content'), 10, 2);

		//get current user
		
		$this->user = wp_get_current_user();
		
		// get is admin
		
		$this->user->is_admin = current_user_can( 'administrator', $this->user->ID );

		// get user rights
		
		$this->user->rights = json_decode( get_user_meta( $this->user->ID, $this->_base . 'user-rights',true) );
				
		// get user stars
		
		$this->user->stars = $this->stars->get_count($this->user->ID);		
		
		// get user programs
		
		$this->user->programs = json_decode( get_user_meta( $this->user->ID, $this->_base . 'user-programs',true) );
		
		// get user affiliate info
		
		$this->user->affiliate_clicks 		= $this->programs->get_affiliate_counter($this->user->ID, 'clicks');
		$this->user->affiliate_referrals 	= $this->programs->get_affiliate_counter($this->user->ID, 'referrals');
		$this->user->affiliate_commission 	= $this->programs->get_affiliate_counter($this->user->ID, 'commission');

		// get user referrals
		
		$this->user->referrals = get_user_meta($this->user->ID,$this->_base . 'referrals',true);

		if(strpos($_SERVER['SCRIPT_NAME'],'user-edit.php') > 0 && isset($_REQUEST['user_id']) ){
			
			// get editedUser data
			
			$this->editedUser 						= get_userdata(intval($_REQUEST['user_id']));
			$this->editedUser->rights   			= json_decode( get_user_meta( $this->editedUser->ID, $this->_base . 'user-rights',true) );
			$this->editedUser->stars 				= $this->stars->get_count($this->editedUser->ID);
			$this->editedUser->programs 			= json_decode( get_user_meta( $this->editedUser->ID, $this->_base . 'user-programs',true) );
			$this->editedUser->affiliate_clicks 	= $this->programs->get_affiliate_counter($this->editedUser->ID, 'clicks');
			$this->editedUser->affiliate_referrals 	= $this->programs->get_affiliate_counter($this->editedUser->ID, 'referrals');
			$this->editedUser->affiliate_commission = $this->programs->get_affiliate_counter($this->editedUser->ID, 'commission');
			$this->editedUser->referrals 			= get_user_meta($this->editedUser->ID,$this->_base . 'referrals',true);
		}
		else{
			
			$this->editedUser = $this->user;
		}

		// loaded hook
		
		do_action( 'ltple_loaded');
	}
	
	public function remove_custom_post_quick_edition( $actions, $post ){

		if( $post->post_type != 'page' && $post->post_type != 'post' ){
		
			//unset( $actions['edit'] );
			//unset( $actions['view'] );
			unset( $actions['trash'] );
			unset( $actions['inline hide-if-no-js'] );
		}
		
		return $actions;
	}		

	public function change_subscription_plan_menu_classes($classes, $item){
		
		global $post;
		
		if(get_post_type($post) == 'subscription-plan'){
			
			$page = get_page_by_path('editor');

			if($page->ID == get_post_meta( $item->ID, '_menu_item_object_id', true )){
				
				$classes = str_replace( 'menu-item-'.$item->ID, 'menu-item-'.$item->ID.' current-menu-item', $classes ); // add the current_page_parent class to the page you want
			}
			else{
				
				$classes = str_replace( array('current-menu-item','current_page_parent'), '', $classes ); // remove all current_page_parent classes			
			}
		}
		
		return $classes;
	}

	public function get_app_types(){
		
		return array(
		
			'networks'  => [],
			'images'	=> [],
			'videos' 	=> [],
			'blogs' 	=> [],
			'payment' 	=> [],
		);
	}
	
	// Add campaign trigger custom fields

	public function add_campaign_trigger_custom_fields(){
		
		$fields=[];
		
		//get post id
		
		$post_id=get_the_ID();
		
		//get image types

		$terms=get_terms( array(
				
			'taxonomy' => 'campaign-trigger',
			'hide_empty' => false,
		));
		
		$options=[];
		
		foreach($terms as $term){
			
			$options[$term->slug]=$term->name;
		}
		
		//get current email campaign
		
		$terms = wp_get_post_terms( $post_id, 'campaign-trigger' );
		
		$default='';

		if(isset($terms[0]->slug)){
			
			$default = $terms[0]->slug;
		}
		
		$fields[]=array(
			"metabox" =>
				array('name'=>"tagsdiv-campaign-trigger"),
				'id'=>"new-tag-campaign-trigger",
				'name'=>'tax_input[campaign-trigger]',
				'label'=>"",
				'type'=>'select',
				'options'=>$options,
				'selected'=>$default,
				'description'=>''
		);
		
		// get email models
		
		$q = get_posts(array(
		
			'post_type'   => 'email-model',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby' 	  => 'title',
			'order' 	  => 'ASC'
		));
		
		$email_models=['' => 'no email model selected'];
		
		if(!empty($q)){
			
			foreach( $q as $model ){
				
				$email_models[$model->ID] = $model->post_title;
			}
		}
		
		//var_dump($email_models);exit;
		
		$fields[]=array(
		
			"metabox" =>
				array('name'=> "email_series"),
				'type'				=> 'email_series',
				'id'				=> 'email_series',
				'label'				=> '',
				'email-models' 		=> $email_models,
				'model-selected'	=> '',
				'days-from-sub' 	=> 0,
				'description'		=> ''
		);
		
		return $fields;
	}
	
	public function set_user_image_columns($columns){

		// Remove description, posts, wpseo columns
		$columns = [];
		
		$columns['cb'] 					= '<input type="checkbox" />';
		$columns['title'] 				= 'Title';
		$columns['author'] 				= 'Author';
		$columns['taxonomy-app-type'] 	= 'App';
		$columns['image'] 				= 'Image';
		$columns['date'] 				= 'Date';

		return $columns;		
	}
	
	public function add_user_image_column_content($column_name, $post_id){

		if($column_name === 'image') {
			
			$post = get_post($post_id);
			
			echo '<img src="' . $post->post_content . '" style="width:100px;" />';
		}		
	}
	
	public function editor_templates( $template_path ){

		if( isset($_GET['pr']) && is_numeric($_GET['pr']) ){
			
			$template_id = get_user_meta( intval($_GET['pr']) , 'ltple_profile_template', true );
			
			$template_id = floatval($template_id);
			
			if( ( $template_id > 0 && isset($this->profile->layer->ID) ) || $template_id == -2 ){
				
				$template_path = $this->views . $this->_dev . '/layer-profile.php';
			}
		}	
		elseif( is_single() ) {
			
			$post_type	 = get_post_type();
			$post_id	 = get_the_ID();
			$post_author = intval(get_post_field( 'post_author', $post_id ));
			
			$path = $template_path;
			
			if( isset( $_SERVER['HTTP_X_REF_KEY'] ) ){
				
				if( $_SERVER['HTTP_X_REF_KEY'] ){ //TODO improve ref rey validation via header
					
					$path = $this->views . $this->_dev .'/layer.php';
				}
				else{
					
					echo 'Malformed layer headers...';
					exit;
				}
			}
			elseif( $post_type == 'cb-default-layer' ){
				
				$visibility = get_post_meta( $post_id, 'layerVisibility', true );
				
				if( $visibility == 'anyone' ){
					
					$path = $this->views . $this->_dev .'/layer.php';
				}
				elseif( $visibility == 'registered' && $this->user->loggedin ){
					
					$path = $this->views . $this->_dev .'/layer.php';
				}
				elseif( $this->plan->user_has_layer( $post_id ) === true && $this->user->loggedin ){
					
					$path = $this->views . $this->_dev .'/layer.php';
				}
				else{
					
					$path = $this->views . $this->_dev .'/preview.php';
				}					
			}
			elseif( $post_type == 'user-layer' ){
				
				if( $this->user->loggedin && ( $this->user->is_admin || $post_author == $this->user->ID )){
				
					$path = $this->views . $this->_dev .'/layer.php';
				}
				else{
					
					echo 'You don\'t have access to this template...';
					exit;
				}				
			}
			elseif( file_exists($this->views . $this->_dev .'/'.$post_type.'.php') ){
				
				$path = $this->views . $this->_dev .'/'.$post_type.'.php';
			}
			
			if( file_exists( $path ) ) {

				$template_path = $path;
			}
		}
		
		return $template_path;
	}
	
	public function editor_output() {

		$this->all = new stdClass();
		
		// get all layer types
		
		$this->all->layerType = get_terms( array(
				
			'taxonomy' => 'layer-type',
			'hide_empty' => true,
		));
		
		// get all layer ranges
		
		$this->all->layerRange = get_terms( array(
				
			'taxonomy' => 'layer-range',
			'hide_empty' => true,
		));
			
		// get layer type
				
		//$terms = wp_get_object_terms( $this->layer->id, 'layer-type' );
		//$this->layer->type = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0] : '');

		// get layer range
				
		$terms = wp_get_object_terms( $this->layer->id, 'layer-range' );
		$this->layer->range = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0] : '');
		
		// get layer price
		
		$this->layer->price = ( !empty($this->layer->range) ? intval( get_option('price_amount_' . $this->layer->range->slug) ) : 0 );
		
		// get user connected apps
		
		$this->user->apps = $this->apps->getUserApps($this->user->ID);
		
		// get triggers
 		
		$this->triggers = new LTPLE_Client_Triggers( $this );

		// get user profile
			
		$this->user->profile = new LTPLE_Client_User_Profile( $this );
		
		// get user domains
			
		$this->user->domains = new LTPLE_Client_User_Domains( $this );
					
		// get user marketing channel
		
		$terms = wp_get_object_terms( $this->user->ID, 'marketing-channel' );
		$this->user->channel = ( ( !isset($terms->errors) && isset($terms[0]->slug) ) ? $terms[0]->slug : '');

		// get user plan

		$this->user->plan 		= $this->plan->get_user_plan_info( $this->user->ID );
		
		$this->user->has_layer 	= $this->plan->user_has_layer( $this->layer->id, $this->layer->type );
		
		// count user templates
			
		$this->user->layerCount = intval( count_user_posts( $this->user->ID, 'user-layer' ) );
		
		// Custom default layer post
		
		if($this->layer->type != '' && $this->layer->slug != ''){
			
			remove_all_filters('content_save_pre');
			remove_filter( 'the_content', 'wpautop' );

			// update user layer
			
			$this->update_user_layer();
		}
		
		if( $this->user->loggedin ){
		
			//update user channel
			
			$this->update_user_channel($this->user->ID);			
			
			//update user image
			
			$this->update_user_image();
			
			//get user plan
			
			$this->plan->update_user();
		}
		
		// get editor iframe

		if( $this->user->loggedin===true && $this->layer->slug!='' && $this->layer->type!='' && $this->layer->key!='' && $this->server->url!==false ){
			
			if( $this->layer->key == md5( 'layer' . $this->layer->uri . $this->_time )){
				
				//include( $this->views . $this->_dev .'/editor-iframe.php' );
				include( $this->views . $this->_dev .'/editor-proxy.php' );
			}
			else{
				
				echo 'Malformed iframe request...';
				exit;					
			}
		}
		
		// Custom outputs
		
		if( isset( $_GET['output']) && $_GET['output'] == 'widget' ){
			
			include( $this->views . $this->_dev .'/widget.php' );
		}
		elseif( isset($_GET['api']) ){

			include($this->views . $this->_dev .'/api.php');
		}			
	}

	public function get_header(){

		//echo '<link rel="stylesheet" href="https://raw.githubusercontent.com/dbtek/bootstrap-vertical-tabs/master/bootstrap.vertical-tabs.css">';	
		
		?>
		<!-- Facebook Pixel Code -->
		
		<script>
		!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
		n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
		document,'script','https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '135366043652148'); // Insert your pixel ID here.
		fbq('track', 'PageView');
		</script>
		<noscript><img height="1" width="1" style="display:none"
		src="https://www.facebook.com/tr?id=135366043652148&ev=PageView&noscript=1"
		/></noscript>
		
		<!-- End Facebook Pixel Code -->
		
		<?php
	
	}	
	
	public function get_footer(){
		
		?>
		<script> 
		
			<!-- Google Analytics Code -->
		
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

			ga('create', '<?php echo $this->settings->options->analyticsId; ?>', 'auto');
			ga('send', 'pageview');
			
			<!-- End Google Analytics Code -->
			
		</script>

		<?php
	}
	
	public function get_editor_shortcode(){
		
		// vertical tab styling
		
		echo '<style>';
			echo '.pgheadertitle{display:none;}.tabs-left,.tabs-right{border-bottom:none;padding-top:2px}.tabs-left{border-right:0px solid #ddd}.tabs-right{border-left:0px solid #ddd}.tabs-left>li,.tabs-right>li{float:none;margin-bottom:2px}.tabs-left>li{margin-right:-1px}.tabs-right>li{margin-left:-1px}.tabs-left>li.active>a,.tabs-left>li.active>a:focus,.tabs-left>li.active>a:hover{border-left: 5px solid #F86D18;border-top:0;border-right:0;border-bottom:0; }.tabs-right>li.active>a,.tabs-right>li.active>a:focus,.tabs-right>li.active>a:hover{border-bottom:0px solid #ddd;border-left-color:transparent}.tabs-left>li>a{border-radius:4px 0 0 4px;margin-right:0;display:block}.tabs-right>li>a{border-radius:0 4px 4px 0;margin-right:0}.sideways{margin-top:50px;border:none;position:relative}.sideways>li{height:20px;width:120px;margin-bottom:100px}.sideways>li>a{border-bottom:0px solid #ddd;border-right-color:transparent;text-align:center;border-radius:4px 4px 0 0}.sideways>li.active>a,.sideways>li.active>a:focus,.sideways>li.active>a:hover{border-bottom-color:transparent;border-right-color:#ddd;border-left-color:#ddd}.sideways.tabs-left{left:-50px}.sideways.tabs-right{right:-50px}.sideways.tabs-right>li{-webkit-transform:rotate(90deg);-moz-transform:rotate(90deg);-ms-transform:rotate(90deg);-o-transform:rotate(90deg);transform:rotate(90deg)}.sideways.tabs-left>li{-webkit-transform:rotate(-90deg);-moz-transform:rotate(-90deg);-ms-transform:rotate(-90deg);-o-transform:rotate(-90deg);transform:rotate(-90deg)}';
			echo 'span.htitle, .captionicons, .colorarea, .mainthemebgcolor, .dropdown-menu>li>a:hover, .dropdown-menu>li>a:focus, .dropdown-menu>.active>a:hover, .dropdown-menu>.active>a:focus, .icon-box-top i:hover, .grey-box-icon:hover .fontawesome-icon.circle-white, .grey-box-icon.active .fontawesome-icon.circle-white, .active i.fontawesome-icon, .widget_tag_cloud a, .tagcloud a, #back-top a:hover span, .add-on, #commentform input#submit, .featured .wow-pricing-per, .featured .wow-pricing-cost, .featured .wow-pricing-button .wow-button, .buttoncolor, ul.social-icons li, #skill i, .btn-primary, .pagination .current, .ui-tabs-active, .totop, .totop:hover, .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active, .open .dropdown-toggle.btn-primary {background-color: #F86D18;border: 1px solid #FF5722;}';
		echo '</style>';	
		
		if($this->user->loggedin){		

			include( $this->views . $this->_dev .'/navbar.php' );	
			
			if( empty( $this->user->channel ) && !isset($_POST['marketing-channel']) ){
				
				include($this->views . $this->_dev .'/channel-modal.php');
			}			

			if( isset($_GET['pr']) && !isset($this->profile->layer->ID) ){

				include($this->views . $this->_dev .'/profile.php');
			}				
			elseif( isset($_GET['media']) ){
				
				include($this->views . $this->_dev .'/media.php');
			}
			elseif( isset($_GET['app']) || !empty($_SESSION['app']) ){

				include($this->views . $this->_dev .'/apps.php');
			}
			elseif( isset($_GET['affiliate']) ){

				include($this->views . $this->_dev .'/affiliate.php');
			}
			elseif( isset($_GET['domain']) || !empty($_SESSION['domain']) ){

				include($this->views . $this->_dev .'/domains.php');
			}				
			elseif( isset($_GET['rank']) ){
				
				include($this->views . $this->_dev .'/ranking.php');
			}
			elseif( isset($_GET['my-profile']) ){
				
				include($this->views . $this->_dev .'/settings.php');
			}			
			elseif( $this->layer->uri != ''){
			
				if( $this->user->has_layer ){
					
					include( $this->views . $this->_dev .'/editor.php' );
				}
				else{
					
					include($this->views . $this->_dev .'/upgrade.php');
					include($this->views . $this->_dev .'/gallery.php');					
				}
			}
			else{
				
				include($this->views . $this->_dev .'/gallery.php');		
			}			
		}
		elseif( isset($_GET['pr']) && !isset($this->profile->layer->ID) ){

			include($this->views . $this->_dev .'/profile.php');
		}
		elseif( isset($_GET['rank']) ){
			
			include($this->views . $this->_dev .'/ranking.php');
		}
		else{
			
			echo'<div style="font-size:20px;padding:20px;margin:0;" class="alert alert-warning">';
				
				echo'You need to log in first...';
				
				echo'<div class="pull-right">';

					echo'<a style="margin:0 2px;" class="btn-lg btn-success" href="'. wp_login_url( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) .'">Login</a>';
					
					echo'<a style="margin:0 2px;" class="btn-lg btn-info" href="'. wp_login_url( $this->urls->editor ) .'&action=register">Register</a>';
				
				echo'</div>';
				
			echo'</div>';		
			
			include($this->views . $this->_dev .'/gallery.php');
		}
	}

	public function ltple_get_dropdown_posts( $args ){
		
		$defaults = array(
		
			'post_type' 		=> 'post', 
			'show_option_none'  => 'Select a post', 
			'name' 				=> null, 
			'selected' 			=> '',
			'style' 			=> '', 
			'echo' 				=> true, 
			'orderby' 			=> 'title', 
			'order' 			=> 'ASC' 
		);

		$args = array_merge($defaults, $args);
		
		$posts = get_posts(
			array(
			
				'post_type'  	=> $args['post_type'],
				'numberposts' 	=> -1,
				'orderby'		=> $args['orderby'], 
				'order' 		=> $args['order']
			)
		);
		 
		$dropdown = '';
		
		if( $posts ){
			
			if( !is_string($args['name']) ){
				
				$args['name'] = $args['post_type'].'_select';
			}
			
			$dropdown .= '<select' . ( !empty($args['style']) ? ' style="' . $args['style'] . '"' : '' ).' id="'.$args['name'].'" name="'.$args['name'].'">';
				
				$dropdown .= '<option value="-1">'.$args['show_option_none'].'</option>';
				
				$args['selected'] = intval($args['selected']);
				
				foreach( $posts as $p ){
					
					$selected = '';
					if( $p->ID == $args['selected'] ){
						
						$selected = ' selected';
					}
					
					$dropdown .= '<option value="' . $p->ID . '"'.$selected.'>' . esc_html( $p->post_title ) . '</option>';
				}
			
			$dropdown .= '</select>';			
		}
		
		if($args['name'] === false){
			
			return $dropdown;
		}
		else{
			
			echo $dropdown;
		}
	}
	
	public function update_user_layer(){	
		
		if( $this->user->loggedin ){

			if( $this->layer->type == 'user-layer' && empty( $this->user->layer ) ){
				
				//--------cannot be found --------
				
				$this->message ='<div class="alert alert-danger">';

					$this->message .= 'This layer cannot be found...';

				$this->message .='</div>';
				
				include( $this->views . $this->_dev .'/message.php' );					
			}
			elseif( $this->layer->type == 'user-layer' && $this->user->layer->post_author != $this->user->ID && !$this->user->is_admin ){
				
				//--------permission denied--------
				
				$this->message ='<div class="alert alert-danger">';

					$this->message .= 'You don\'t have the permission to edit this template...';

				$this->message .='</div>';
				
				include( $this->views . $this->_dev .'/message.php' );					
			}
			elseif( $this->layer->type == 'user-layer' && isset($_GET['postAction'])&& $_GET['postAction']=='delete' ){
					
				//--------delete layer--------
				
				//wp_delete_post( $this->user->layer->ID, false );
				
				wp_trash_post( $this->user->layer->ID );
				
				$this->layer->id = -1;
					
				$this->message ='<div class="alert alert-success">';

					$this->message .= 'Template successfully deleted!';

				$this->message .='</div>';
				
				//include( $this->views . $this->_dev .'/message.php' );

				//redirect page
				
				$parsed = parse_url($this->urls->editor .'?'. $_SERVER['QUERY_STRING']);

				parse_str($parsed['query'], $params);

				unset($params['uri'],$params['postAction']);
				
				$url = $this->urls->editor;
				
				$query = http_build_query($params);
				
				if( !empty($query) ){
					
					$url .= '?'.$query;		
				}

				wp_redirect($url);
				exit;
			}
			elseif( isset($_POST['postContent']) && !empty($this->layer->type) ){
				
				// get post content
				
				$post_content 	= $this->layer->sanitize_content( $_POST['postContent'] );
				$post_css 		= ( !empty($_POST['postCss']) 	? stripcslashes( $_POST['postCss'] ) : '' );
				$post_title 	= ( !empty($_POST['postTitle']) ? wp_strip_all_tags( $_POST['postTitle'] ) : '' );
				$post_name 		= $post_title;			

				if( $_POST['postAction'] == 'update' && $this->user->is_admin ){
					
					//update layer
					
					if( $this->layer->type == 'user-layer' ){
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, $this->layer->type);
					}
					else{
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, 'cb-default-layer');
					}
					
					if(!empty($layer)){
					
						$layerId	= intval( $layer->ID );

						if( is_int($layerId) && $layerId !== -1 ){
						
							global $wpdb;
						
							$wpdb->update( $wpdb->posts, array( 'post_content' => $post_content), array( "ID" => $layerId));
						
							update_post_meta($layerId, 'layerCss', $post_css);
						}
					}
				}
				elseif( $_POST['postAction'] == 'duplicate' && $this->user->is_admin ){
					
					//duplicate layer
					
					if( $this->layer->type == 'user-layer' ){
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, $this->layer->type);
					}
					else{
						
						$layer	= get_page_by_path( $this->layer->slug, OBJECT, 'cb-default-layer');
					}
					
					if(!empty($layer)){
					
						$layerId = intval( $layer->ID );

						if( is_int($layerId) && $layerId !== -1 ){
						
							$post_information = array(
								
								'post_author' 	=> $this->user->ID,
								'post_title' 	=> $post_title,
								'post_name' 	=> $post_name,
								'post_content' 	=> $layer->post_content,
								'post_type' 	=> $layer->post_type,
								'post_status' 	=> 'publish'
							);
							
							$post_id = wp_insert_post( $post_information );

							if( is_numeric($post_id) ){
								
								// duplicate all post meta
								
								$layerMeta = get_post_meta($layerId);
						
								foreach($layerMeta as $name => $value){
									
									if( isset($value[0]) ){
										
										update_post_meta( $post_id, $name, $value[0] );
									}
								}
								
								// duplicate all taxonomies
								
								$taxonomies = get_object_taxonomies($layer->post_type);
								
								foreach ($taxonomies as $taxonomy) {
									
									$layerTerms = wp_get_object_terms($layerId, $taxonomy, array('fields' => 'slugs'));
									
									wp_set_object_terms($post_id, $layerTerms, $taxonomy, false);
								}					
								
								//redirect to user layer

								$layer_url = $this->urls->editor . '?uri=' . $this->layer->type . '/' . get_post_field( 'post_name', $post_id ) . '/';
								
								//var_dump($layer_url);exit;
								
								wp_redirect($layer_url);
								echo 'Redirecting editor...';
								exit;
							}							
						}
					}
				}
				elseif( $_POST['postAction'] == 'save'){				
					
					//save layer
					
					$post_id = '';
					$defaultLayerId = -1;
					
					if( $this->layer->type == 'user-layer' ){
						
						$post_id		= $this->user->layer->ID;
						$post_author	= $this->user->layer->post_author;
						$post_title		= $this->user->layer->post_title;
						$post_name		= $this->user->layer->post_name;
						$defaultLayerId	= intval(get_post_meta( $post_id, 'defaultLayerId', true));
					}
					else{
						
						$defaultLayer = get_page_by_path( $this->layer->slug, OBJECT, 'cb-default-layer');
						
						if( !empty($defaultLayer) ){
						
							if( empty($post_title) ){
							
								$post_title = $defaultLayer->post_title;
							}
							
							$post_author = $this->user->ID;
							
							if( $this->user->layerCount + 1 > $this->user->plan['info']['total_storage']['templates'] ){
								
								$this->message ='<div class="alert alert-danger">';
								
									if( $this->user->plan['info']['total_storage']['templates'] == 1 ){
										
										$this->message .= 'You can\'t save more than ' . $this->user->plan['info']['total_storage']['templates'] . ' template with the current plan...';
									}
									elseif( $this->user->plan['info']['total_storage']['templates'] == 0 ){
										
										$this->message .= 'You can\'t save templates with the current plan...';
									}
									else{
										
										$this->message .= 'You can\'t save more than ' . $this->user->plan['info']['total_storage']['templates'] . ' templates with the current plan...';
									}

								$this->message .='</div>';
								
								include( $this->views . $this->_dev .'/message.php' );
							}

							$defaultLayerId	= intval( $defaultLayer->ID );
						}
						else{
							
							http_response_code(404);
							
							$this->message ='<div class="alert alert-danger">';
									
								$this->message .= 'This default layer doesn\'t exists...';

							$this->message .='</div>';
							
							include( $this->views . $this->_dev .'/message.php' );							
						}
					}

					if( $post_title!='' && $post_content!='' && is_int($defaultLayerId) && $defaultLayerId !== -1 ){
						
						$post_information = array(
							
							'ID' 			=> $post_id,
							'post_author' 	=> $post_author,
							'post_title' 	=> $post_title,
							'post_name' 	=> $post_name,
							'post_content' 	=> $post_content,
							'post_type' 	=> 'user-layer',
							'post_status' 	=> 'publish'
						);
						
						$post_id = wp_update_post( $post_information );

						if( is_numeric($post_id) ){
							
							update_post_meta($post_id, 'defaultLayerId', $defaultLayerId);
							
							update_post_meta($post_id, 'layerCss', $post_css);
							
							//redirect to user layer
							
							$user_layer_url = $this->urls->editor . '?uri=' . 'user-layer/' .  get_post_field( 'post_name', $post_id) . '/';
							
							//var_dump($user_layer_url);exit;
							
							wp_redirect($user_layer_url);
							echo 'Redirecting editor...';
							exit;
						}
					}
					else{
						
						http_response_code(404);
						
						$this->message ='<div class="alert alert-danger">';
								
							$this->message .= 'Error saving user layer...';

						$this->message .='</div>';
						
						include( $this->views . $this->_dev .'/message.php' );
					}
				}
				else{
					
					http_response_code(404);
					
					$this->message ='<div class="alert alert-danger">';
							
						$this->message .= 'This action doesn\'t exists...';

					$this->message .='</div>';
					
					include( $this->views . $this->_dev .'/message.php' );					
				}
			}			
		}
	}

	public function update_user_channel( $user_id, $name = '' ){	
		
		$taxonomy = 'marketing-channel';

		// get term_id
		
		if( isset($_POST[$taxonomy]) &&  is_numeric($_POST[$taxonomy]) ){
			
			$term_id = intval($_POST[$taxonomy]);
		}
		elseif( !empty($name) ){
			
			$term = get_term_by('name', $name, $taxonomy);
			
			if( !empty($term->term_id) ){
				
				$term_id = intval($term->term_id);
			}
			elseif( strtolower($name) == 'friend recommendation' ){
				
				$term = wp_insert_term(
				
					ucfirst($name),
					$taxonomy,
					array(
					
						'description'	=> '',
						'slug' 			=> str_replace(' ','-',$name),
					)
				);

				$term_id = intval($term->term_id);
			}
		}
		
		if(!empty($term_id)){
			
			//-------- save channel --------
			
			$response = wp_set_object_terms( $user_id, $term_id, $taxonomy);
			
			clean_object_term_cache( $user_id, $taxonomy );	

			if( empty($response) ){

				echo 'Error saving user channel...';
				exit;
			}				
		}			
	}
	
	public function update_user_image(){	
		
		if( $this->user->loggedin ){

			if( isset($_GET['imgAction']) && $_GET['imgAction']=='delete' ){
				
				//--------delete image--------
				
				wp_delete_post( $this->image->id, true );
				
				$this->image->id = -1;
					
				$this->message ='<div class="alert alert-success">';

					$this->message .= 'Image url successfully deleted!';

				$this->message .='</div>';
				
			}
			elseif( isset($_POST['imgAction']) &&  $_POST['imgAction']=='upload' && isset($_POST['imgHost'])){
				
				// valid host
				
				$app_title = wp_strip_all_tags( $_POST['imgHost'] );
				
				$app_item = get_page_by_title( $app_title, OBJECT, 'user-app' );
				
				if( empty($app_item) || ( intval( $app_item->post_author ) != $this->user->ID && !in_array_field($app_item->ID, 'ID', $this->apps->mainApps)) ){
					
					echo 'This image host doesn\'t exists...';
					exit;
				}
				elseif(!empty($_FILES)) {
					
					foreach ($_FILES as $file => $array) {
						
						if($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
							
							echo "upload error : " . $_FILES[$file]['error'];
							exit;
						}
						else{
							
							$mime=explode('/',$_FILES[$file]['type']);
							
							if($mime[0] !== 'image') {
								
								echo 'This is not a valid image type...';
								exit;							
							}
							
							if( $data = file_get_contents($_FILES[$file]['tmp_name'])){
								
								// rename file
								
								$_FILES[$file]['name'] = md5($data) . '.' . $mime[1];

								// get current app
								
								$app = explode(' - ', $app_title );
								
								// set session
								
								$_SESSION['app'] 	= $app[0];
								$_SESSION['action'] = 'upload';
								$_SESSION['file'] 	= $_FILES[$file]['name'];
																		
								//check if image exists
								
								$img_exists = false;
								
								$q = new WP_Query(array(
									
									'post_author' => $this->user->ID,
									'post_type' => 'user-image',
									'numberposts' => -1,
								));

								while ( $q->have_posts() ) : $q->the_post(); 
							
									global $post;
									
									if( $post->post_title == $_FILES[$file]['name'] ){
										
										$img_exists = true;
										break;
									}
									
								endwhile; wp_reset_query();
								
								if( !$img_exists ){
									
									//require the needed files
									
									require_once(ABSPATH . "wp-admin" . '/includes/image.php');
									require_once(ABSPATH . "wp-admin" . '/includes/file.php');
									require_once(ABSPATH . "wp-admin" . '/includes/media.php');
									
									//upload image
									
									$attach_id = media_handle_upload( $file, 0 );
									
									if(is_numeric($attach_id)){
									
										// get image url
										
										$image_url = wp_get_attachment_url( $attach_id );
										
										// add local image	
										
										/*
										if($post_id = wp_insert_post( array(
											
											'post_author' 	=> $this->user->ID,
											'post_title' 	=> $_FILES[$file]['name'],
											'post_name' 	=> $_FILES[$file]['name'],
											'post_content' 	=> $image_url,
											'post_type'		=> 'user-image',
											'post_status' 	=> 'publish'
										))){
											
										}
										*/
										
										// upload image to host
										
										$appSlug = $app[0];
										
										if( !isset( $this->apps->{$appSlug} ) ){
											
											$this->apps->includeApp($appSlug);
										}

										if($this->apps->{$appSlug}->appUploadImg( $app_item->ID, $image_url )){
											
											// output success message
											
											$this->message ='<div class="alert alert-success">';
													
												$this->message .= 'Congratulations! Image succefully uploaded to your library.';

											$this->message .='</div>';											
										}
										else{
											
											// output error message
											
											$this->message ='<div class="alert alert-danger">';
													
												$this->message .= 'Oops, something went wrong...';

											$this->message .='</div>';													
										}
										
										// remove image from local library
										
										wp_delete_attachment( $attach_id, $force_delete = true );
									}
									else{
										
										echo 'Error handling upload...';
										exit;											
									}
								}
								else{
									
									// output warning message
									
									$this->message ='<div class="alert alert-warning">';
											
										$this->message .= 'This image already exists...';

									$this->message .='</div>';										
								}
							}
							else{
								
								echo 'Error uploading your image...';
								exit;									
							}
						}
					}   
				}				
			}
			elseif( isset($_POST['imgAction']) &&  $_POST['imgAction']=='save' && isset($_POST['imgTitle']) && isset($_POST['imgUrl']) ){
				
				//-------- save image --------
				
				$img_id = $img_title = $img_name = $img_content = '';
				
				if($_POST['imgTitle']!=''){

					$img_title = $img_name = wp_strip_all_tags( $_POST['imgTitle'] );
				}
				else{ 
					
					echo 'Empty image title...';
					exit;
				}

				if($_POST['imgUrl']!=''){
				
					$img_content=wp_strip_all_tags( $_POST['imgUrl'] );
				}
				else{
					
					echo 'Empty image url...';
					exit;
				}
				
				if( $img_title!='' && $img_content!=''){
					
					$img_valid = true;
					
					if($img_valid === true){
						
						// check if is valid url
						
						if (filter_var($img_content, FILTER_VALIDATE_URL) === FALSE) {
							
							$img_valid = false;
						}
					}
					
					if($img_valid === true){
						
						// check if image exists
						
						$q = new WP_Query(array(
							
							'post_author' => $this->user->ID,
							'post_type' => 'user-image',
							'numberposts' => -1,
						));
						
						//var_dump($q);exit;
						
						while ( $q->have_posts() ) : $q->the_post(); 
					
							global $post;
							
							if( $post->post_content == $img_content ){
								
								$img_valid = false;
								break;
							}
							
						endwhile; wp_reset_query();	
					}
					
					if( $img_valid === true ){
					
						if($post_id = wp_insert_post( array(
							
							'post_author' 	=> $this->user->ID,
							'post_title' 	=> $img_title,
							'post_name' 	=> $img_name,
							'post_content' 	=> $img_content,
							'post_type'		=> 'user-image',
							'post_status' 	=> 'publish'
						))){
							
							$this->message ='<div class="alert alert-success">';
									
								$this->message .= 'Congratulations! Image url succefully added to your library.';

							$this->message .='</div>';						
						}						
					}
					else{

						$this->message ='<div class="alert alert-danger">';
								
							$this->message .= 'This image url already exists...';

						$this->message .='</div>';
					}
				}
				else{
					
					$this->message ='<div class="alert alert-danger">';
							
						$this->message .= 'Error saving user image...';

					$this->message .='</div>';
				}
			}			
		}
	}
	
	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new LTPLE_Client_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new LTPLE_Client_Taxonomy( $this, $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {

		wp_register_style( $this->_token . '-jquery-ui', esc_url( $this->assets_url ) . 'css/jquery-ui.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-jquery-ui' );		
	
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	
		wp_register_style( $this->_token . '-bootstrap-table', esc_url( $this->assets_url ) . 'css/bootstrap-table.min.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-bootstrap-table' );	
		
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		wp_enqueue_script('jquery-ui-dialog');
		
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
		
		wp_register_script($this->_token . '-lazyload', esc_url( $this->assets_url ) . 'js/lazyload.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-lazyload' );	

		wp_register_script($this->_token . '-sprintf', esc_url( $this->assets_url ) . 'js/sprintf.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-sprintf' );		
		
		wp_register_script($this->_token . '-bootstrap-table', esc_url( $this->assets_url ) . 'js/bootstrap-table.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-bootstrap-table' );

		wp_register_script($this->_token . '-bootstrap-table-export', esc_url( $this->assets_url ) . 'js/bootstrap-table-export.js', array( 'jquery', $this->_token . 'sprintf' ), $this->_version);
		wp_enqueue_script( $this->_token . '-bootstrap-table-export' );
		
		wp_register_script($this->_token . '-table-export', esc_url( $this->assets_url ) . 'js/tableExport.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-table-export' ); 
		
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
		
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		wp_enqueue_script('jquery-ui-sortable');
		
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );

		wp_register_script($this->_token . '-lazyload', esc_url( $this->assets_url ) . 'js/lazyload.min.js', array( 'jquery' ), $this->_version);
		wp_enqueue_script( $this->_token . '-lazyload' );			
		
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'live-template-editor-client', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
	    $domain = 'live-template-editor-client';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

	/**
	 * Main LTPLE_Client Instance
	 *
	 * Ensures only one instance of LTPLE_Client is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client instance
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
		
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	}

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public static function install() {
		
		// store version number
		
		//$this->_log_version_number();
	}

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		
		update_option( $this->_token . '_version', $this->_version );
	}
}
