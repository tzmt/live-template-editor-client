<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Triggers {
	
	/**
	 * The single instance of LTPLE_Client_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;	
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {

		$this->parent = $parent;
		
		add_action('user_register', array( $this, 'trigger_after_user_register'), 10, 1);
		
		if( $this->parent->user->loggedin ){

			if( $this->parent->user->last_seen == 0 ){
				
				// schedule user registration emails

				$this->parent->email->schedule_trigger( 'user-registration',  $this->parent->user);
			}
			elseif(( date('Y.m.d',$this->parent->user->last_seen) != date('Y.m.d') )){

				do_action( 'ltple_first_log_today' );
			}
			
			update_user_meta( $this->parent->user->ID, $this->parent->_base . '_last_seen', $this->parent->_time);
		}
	}
	
	public function trigger_after_user_register( $user_id ){

		// the new user just registered but never logged in yet
		add_user_meta($user_id, $this->parent->_base . '_last_seen', 'false');
	}
	
	/**
	 * Main LTPLE_Client_Triggers Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Triggers is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Triggers instance
	 */
	public static function instance ( $parent ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $parent );
		}
		
		return self::$_instance;
		
	} // End instance()
	
	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()
}
