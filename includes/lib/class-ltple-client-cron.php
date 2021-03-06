<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Cron {
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;

		add_filter('cron_schedules', array($this,'add_cron_schedules'));
	}
	
	public function event_exists($event){
		
		$crons = _get_cron_array();
		
		foreach($crons as $cron){
			
			if( isset($cron[$event]) ){
				
				return true;
			}
		}
		
		return false;
	}
	
	public function remove_event($event){
		
		$crons = _get_cron_array();
		
		foreach($crons as $cron){
			
			if( isset($cron[$event]) ){
				
				foreach($cron[$event] as $e){
				
					if(!empty($e["args"])){
						
						wp_clear_scheduled_hook( $event, $e["args"] );
					}
					else{
						
						wp_clear_scheduled_hook( $event );
					}
				}
			}
		}
	}
	
	public function add_cron_schedules($schedules){
		
		$i = 1;
		
		while(!isset($schedules[$i."min"]) && $i < 60){

			$schedules[$i."min"] = array(
				'interval' => $i*60,
				'display' => __('Every '.$i.' minutes'));			
			
			$i++;
		}

		return $schedules;
	}

	/**
	 * Main LTPLE_Client_Cron Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Cron is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Cron instance
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