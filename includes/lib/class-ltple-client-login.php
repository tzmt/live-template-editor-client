<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Login {
	
	var $parent;
	
	/**
	 * Constructor function
	 */
	 
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
	
		add_filter( 'login_url', array($this, 'set_login_url'), 10, 3 );
	
		add_filter( 'register_url', array($this, 'set_register_url'), 10, 3 );
		
		add_action( 'template_redirect', array( $this, 'enqueue_login_scripts' ));

		add_filter( 'body_class', function( $classes ) {
			
			return array_merge( $classes, array( 'login', 'login-action-login', 'login-action-login', 'wp-core-ui' ) );
		});		
		
		add_filter( 'login_redirect', array($this, 'set_login_redirect_url'), 10, 3 );		
		
		add_shortcode( 'ltple-client-login', array($this , 'add_shortcode_login' ) );
		
		add_filter( 'login_form_bottom', array($this, 'get_login_form_bottom'));

		add_filter( 'registration_errors', array($this, 'handle_custom_registration'), 10, 3 );
	
		if( !is_admin() ){
			
			if( !empty($_GET['action']) && $_GET['action'] == 'rp' ){
				
				if( !empty($_GET['redirect_to']) ){
					
					$_SESSION['redirect_to']= $_GET['redirect_to'];
				}
			}
		}
	}
	
	public function handle_custom_registration( $errors = NULL, $sanitized_user_login = NULL, $user_email = NULL ){
		
		if( !empty($errors) ){
		
			$success = NULL;
			
			// check email
		
			if( !empty( $errors->errors['email_exists'] ) ){

				if( $user = get_user_by( 'email', $user_email ) ){
					
					if( !empty($user->data) ){
						
						$user = $user->data;
					
						// check if email imported

						$user->last_seen = intval( get_user_meta( $user->ID, $this->parent->_base . '_last_seen',true) );
						
						if( $user->last_seen === 0 ){

							// send new user notification
							
							wp_new_user_notification( $user->ID, NULL, 'user' );
							
							// output success message
							
							$success = 'A confirmation email has been sent to <b>'.$user_email.'</b>';
						}
					}
				}
			}
			elseif( !empty( $errors->errors['empty_username'] ) ){
				
				// add new user
				
				if( $user = $this->parent->email->insert_user($user_email) ){
					
					// send new user notification
							
					wp_new_user_notification( $user['id'], NULL, 'user' );					
					
					// output success message
					
					$success = 'A confirmation email has been sent to <b>'.$user_email.'</b>';
				}
			}
			
			// unset empty username message
			
			unset($errors->errors['empty_username']);			
			
			// store message in session 
			
			if(!session_id()) {
				
				session_start();
			}
	
			if( !empty($success) ){
				
				$_SESSION['success'] = $success;
			}
			else{
				
				$_SESSION['errors'] = $errors;
			}
		}
		
		// redirect to login page
		
		$login_url = add_query_arg( array(
		
			'redirect_to' 	=> ( isset($_GET['redirect_to']) ? $_GET['redirect_to'] : ''),
			'action' 		=> 'register',
			
		), $this->parent->urls->login );			
		
		wp_redirect($login_url);
		exit;
	}
	
	public function set_login_url( $login_url, $redirect, $force_reauth ) {
		
		$login_url = home_url( '/login/' );
		
		if( !empty($redirect) ){
			
			$login_url = add_query_arg( 'redirect_to', $redirect, $login_url );
		}
		
		return $login_url;
	}
	
	public function get_register_url( $register_url ) {

		$register_url = add_query_arg( array(

			'action' 		=> 'register',
			
		), $register_url );		
		
		if( !empty($_GET['redirect_to']) ){
		
			$register_url = add_query_arg( array(
			
				'redirect_to' 	=> urlencode( $_GET['redirect_to'] ),
				
			), $register_url );
		}
		
		if( !empty($_GET['loe']) ){

			$register_url = add_query_arg( array(

				'loe' 	=> $_GET['loe'],
				
			), $register_url );
		}	

		return $register_url;
	}

	public function set_register_url( $register_url ) {

		return $register_url = $this->get_register_url($register_url);
	}
	
	public function set_login_redirect_url( $redirect_to, $request, $user ) {

		// set $redirect_to default value
		
		/*
		if( get_user_meta( $user->ID , 'has_subscription', true) === 'true'){
			
			return $this->parent->urls->editor;
		}
		else{
			
			return $this->parent->urls->plans;
		}
		*/
		
		return $this->parent->urls->profile . $user->ID . '/';
	}
		
	public function add_shortcode_login(){
		
		include($this->parent->views . '/login.php');
	}
	
	public function get_login_form_bottom() {
	
		//return 'test';
	}
	
	public function enqueue_login_scripts(){
		
		if( is_page() && get_queried_object()->post_name == 'login' ){
			
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		}
	}
	
	/**
	 * Load login CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		
		wp_register_style( $this->parent->_token . '-login', esc_url( $this->parent->assets_url ) . 'css/login.css', array(), $this->parent->_version );
		wp_enqueue_style( $this->parent->_token . '-login' );
	} // End enqueue_styles ()

	/**
	 * Load login Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		wp_register_script( $this->parent->_token . '-login', esc_url( $this->parent->assets_url ) . 'js/login' . $this->parent->script_suffix . '.js', array( 'jquery' ), $this->parent->_version );
		wp_enqueue_script( $this->parent->_token . '-login' );
		
	} // End enqueue_scripts ()	
} 