<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Email {
	
	var $parent;
	var $invitationForm;
	var $invitationMessage;
	var $imported;
	var $maxRequests = 100;
	
	/**
	 * Constructor function
	 */
	 
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		$this->parent->register_post_type( 'email-model', __( 'Email models', 'live-template-editor-client' ), __( 'Email model', 'live-template-editor-client' ), '', array(

			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'email-model',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> array('slug'=>'email-model'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array('title', 'editor'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));	
		
		$this->parent->register_post_type( 'email-campaign', __( 'Email Campaigns', 'live-template-editor-client' ), __( 'Email Campaign', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'email-campaign',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array('title'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));		
		
		$this->parent->register_post_type( 'email-invitation', __( 'User Invitations', 'live-template-editor-client' ), __( 'User Invitation', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu'		 	=> 'email-invitation',
			'show_in_nav_menus' 	=> true,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail','page-attributes' ),
			'supports' 				=> array( 'title', 'editor', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));	
		
		add_action( 'add_meta_boxes', function(){
		
			$this->parent->admin->add_meta_box (
			
				'email_series',
				__( 'Email series', 'live-template-editor-client' ), 
				array("subscription-plan", "email-campaign"),
				'advanced'
			);
				
			$this->parent->admin->add_meta_box (
			
				'tagsdiv-campaign-trigger',
				__( 'Campaign Trigger', 'live-template-editor-client' ), 
				array("email-campaign"),
				'advanced'
			);
		});		
			
		// add cron events
			
		add_action( $this->parent->_base . 'send_email_event', array( $this, 'send_model'),1,2);

		// setup phpmailer

		add_action( 'phpmailer_init', 	function( PHPMailer $phpmailer ) {
			
			$key_name = "key1";
			$urlparts = parse_url(site_url());		
			
			$phpmailer->SMTPOptions = array(
				'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			));

			$phpmailer->DKIM_domain 	= $urlparts ['host'];
			$phpmailer->DKIM_private 	= WP_CONTENT_DIR . "/keys/dkim_" . $key_name . ".ppk";
			$phpmailer->DKIM_selector 	= $key_name;
			$phpmailer->DKIM_passphrase = "";
			$phpmailer->DKIM_identifier = $phpmailer->From;

			$phpmailer->IsSMTP();
		});		
		
		// Custom default email address
		
		add_filter('wp_mail_from', function($old){
			
			$urlparts = parse_url(site_url());
			$domain = $urlparts ['host'];
			
			return 'please-reply@'.$domain;
		});
		
		add_filter('wp_mail_from_name', function($old) {
			
			return 'Live Editor';
		});
		
		add_filter('ltple_loaded', array( $this, 'init_email' ));
		
		add_action( 'ltple_users_bulk_imported', array( $this, 'schedule_invitations' ));
	}
	
	public function init_email(){
		
		if(!is_admin()){
			
			if($this->parent->user->is_admin){
				
				$this->maxRequests = 100;
			}
		
			if( !empty($_POST['importEmails']) ){
				
				if($this->parent->user->loggedin){
				
					$this->bulk_import_users($_POST['importEmails']);
				}
			}
		}
	}

	public function insert_user($email, $check_exists = true ){

		if( filter_var($email, FILTER_VALIDATE_EMAIL) && ( !$check_exists || !email_exists( $email ) ) ){
			
			if( is_plugin_active('wpforo/wpforo.php') ){
				
				//fix wpforo error
			
				global $wpforo;
				
				$wpforo->current_user_groupid = null;
			}
			
			// get username
			
			$username = strtok($email, '@');
			
			$username = str_replace(array('+','.','-','_'),' ',$username);
			
			$username = ucwords($username);
			
			$username = str_replace(' ','',$username);
			
			$i = '';
			
			do{

				if( empty($i) ){
					
					$i = 1;
				}
				else{
					
					++$i;
				}
				
			} while( username_exists( $username . $i ) !== false );

			if( $user_id = wp_insert_user( array(
			
				'user_login'	=>  $username . $i,
				'user_pass'		=>  NULL,
				'user_email'	=>  $email,
			))){
			
				$user = array(
				
					'id' 	=> $user_id,
					'name' 	=> $username . $i,
					'email' => $email,
				);
				
				return $user;
			}
		}	

		return false;
	}
	
	public function bulk_import_users( $csv ){
		
		// normalize csv
		
		$csv = preg_replace('#\s+#',',',trim($csv));
		
		// get emails
		
		$emails = explode(',',$csv);

		// parse emails
		
		foreach( $emails as $i => $email){
			
			$email = trim( $email );
			
			if( !empty( $email ) ){
			
				if( filter_var($email, FILTER_VALIDATE_EMAIL) ){
					
					if( $user = email_exists( $email ) ){

						$this->imported['already registered'][] = ['id' => $user, 'email' => $email ];
					}					
					else{
						
						if( $user = $this->insert_user($email, false) ){
							
							$this->parent->update_user_channel($user['id'],'User Invitation');
							
							$this->imported['imported'][] = $user;
						}
						else{
							
							$this->imported['errors'][] = $email;
						}
					}
				}
				else{
					
					$this->imported['are invalid'][] = $email;
				}
				
				if( $i == $this->maxRequests){
					
					break;
				}				
			}
		}
		
		do_action('ltple_users_bulk_imported');

		return true;
	}
	
	public function do_shortcodes( $str, $user=null){
		
		$shortcodes 	= [];
		$shortcodes[] 	= '*|DAY|*'; 		// today
		$shortcodes[] 	= '*|DATE:d/m/y|*'; // date
		$shortcodes[] 	= '*|DATE:y|*'; 	// year
		
		if( !is_null($user) ){
			
			$shortcodes[] 	= '*|FNAME|*';
			$shortcodes[] 	= '*|LNAME|*';
			$shortcodes[] 	= '*|EMAIL|*';			
		}
		
		$data 			= [];
		$data[]			= date( 'l', time());
		$data[]			= date( 'd/m/y', time());
		$data[]			= date( 'y'	 , time());
		
		if( !is_null($user) ){
			
			$data[] 		= ( $user->first_name !='' ? ucfirst($user->first_name) : ucfirst($user->user_nicename) );
			$data[]			= ( $user->last_name  !='' ? ucfirst($user->last_name ) : '' );
			$data[]			= 	$user->user_email;
		}
		
		$str = str_replace($shortcodes,$data,$str);
		
		return $str;
	}
	
	public function get_title( $title, $user=null ){
		
		$title = str_replace(array('–'),'-',$title);
		$title = explode('-',$title,2);

		if(isset($title[1])){
			
			$title = $title[1];
		}
		else{
			
			$title = $title[0];
		}
		
		$title = $this->do_shortcodes($title, $user);

		return $title;
	}
	
	public function send_model( $model_id, $user){
		
		if(is_numeric( $user )){
			
			$user = get_user_by( 'id', $user);
		}
		elseif(is_string($user)){
			
			$user = get_user_by( 'email', $user);
		}
		
		$can_spam = get_user_meta( $user->ID, $this->parent->_base . '_can_spam',true);

		if($can_spam !== 'false' && is_numeric($model_id)){
			
			if($model = get_post($model_id)){
				
				$urlparts = parse_url(site_url());
				$domain = $urlparts ['host'];				
				
				$Email_title = $this->get_title($model->post_title, $user);

				// get email slug
				
				$email_slug = sanitize_title($Email_title);
				
				// get email sent
				
				$emails_sent = get_user_meta($user->ID, $this->parent->_base . '_email_sent', true);
				
				if( empty($emails_sent) ){
					
					$emails_sent=[];
				}
				else{
					
					$emails_sent=json_decode($emails_sent,true);
				}
				
				if( !isset($emails_sent[$email_slug]) ){
					
					$sender_email 	= 'please-reply@'.$domain;
					
					$message 		= $model->post_content;
					$message	 	= $this->do_shortcodes($message, $user);
					
					$headers   = [];
					$headers[] = 'From: ' . get_bloginfo('name') . ' <'.$sender_email.'>';
					$headers[] = 'MIME-Version: 1.0';
					$headers[] = 'Content-type: text/html';
					
					$unsubscribeMessage = '<div style="text-align:center;"><a style="font-size: 11px;" href="' . $this->parent->urls->editor . '?unsubscribe=' . $this->parent->ltple_encrypt_uri($user->ID) . '">Unsubscribe from this Newsletter</a></div>';
					
					$preMessage = "<html><body><div style='width:700px;padding:5px;margin:auto;font-size:14px;line-height:18px'>" . apply_filters('the_content', $message) . "<div style='clear:both'></div>".$unsubscribeMessage."<div style='clear:both'></div></div></body></html>";
					
					if(!wp_mail($user->user_email, $Email_title, $preMessage, $headers)){
						
						global $phpmailer;
						
						wp_mail($this->parent->settings->options->emailSupport, 'Error sending email model id ' . $model_id . ' to ' . $user->user_email, print_r($phpmailer->ErrorInfo,true));
						
						var_dump($phpmailer->ErrorInfo);exit;				
					}
					else{
						
						// update email sent
						
						$emails_sent[$email_slug]=time();
						
						if( is_array($emails_sent) && !empty($emails_sent) ){
							
							arsort($emails_sent);
							$emails_sent = json_encode($emails_sent);

							update_user_meta($user->ID, $this->parent->_base . '_email_sent', $emails_sent);
						}
						else{
							
							echo 'Error storing email sent info...';
							exit;
						}
						
						return true;
					}				
				}
			}
		}
		
		return false;
	}
	
	public function schedule_trigger( $trigger_slug, $user){

		if( is_numeric($user) ){
			
			$user = get_user_by( 'id', $user );
		}	
	
		// schedule all campaigns linked to a trigger
	
		$q = get_posts(array(
		
			'post_type'   => 'email-campaign',
			'post_status' => 'publish',
			'numberposts' => -1,

			'tax_query' => array(
				array(
					'taxonomy' => 'campaign-trigger',
					'field' => 'slug',
					'terms' => $trigger_slug
			))
		));
		
		foreach( $q as $campaign){
			
			$this->schedule_campaign( $campaign->ID,  $user);					
		}	
	}
	
	public function schedule_campaign( $series_id, $user){
		
		if( is_numeric($user) ){
			
			$user = get_user_by( 'id', $user );
		}
			
		// schedule a campaign by id
			
		$email_series = get_post_meta( $series_id, 'email_series',true);

		// trigger register email

		if( isset( $email_series['model'] ) && isset( $email_series['days'] ) ){
			
			/*
			$emails_sent = get_user_meta($user->ID, $this->parent->_base . '_email_sent', true);
			
			if( empty($emails_sent) ){
				
				$emails_sent=[];
			}
			else{
				
				$emails_sent=json_decode($emails_sent,true);
			}
			*/

			foreach($email_series['model'] as $e => $model_id){
				
				if( is_numeric($model_id) ){
					
					$model_id = intval($model_id);
					
					if( $model_id > 0 ){
						
						if( intval($email_series['days'][$e]) == 0){
							
							wp_schedule_single_event( ( time() + ( 60 * 1 ) ) , $this->parent->_base . 'send_email_event' , [$model_id,$user->user_email] );
						}
						else{
							
							wp_schedule_single_event( ( time() + ( intval( $email_series['days'][$e] ) * 3600 * 24 ) ), $this->parent->_base . 'send_email_event', [$model_id,$user->user_email] );
						}									
					}
				}
			}
		}
	}
	
	public function get_invitation_form( $type='' ){
		
		$this->invitationForm = '';
		
		// get response message
		
		if( !empty($this->imported) ){
			
			$this->invitationForm .= '<div class="alert alert-info" style="padding:10px;">';
			
				foreach( $this->imported as $label => $data ){
					
					$count = count($data);
					
					if( $count == 1 ){
						
						$this->invitationForm .= $count . ' email ' . $label. '<br/>' ;
					}
					else{
						
						$this->invitationForm .= $count . ' emails ' . $label. '<br/>' ;
					}
				}
			
			$this->invitationForm .='</div>';
		}

		// get company name 
		
		$company = ucfirst(get_bloginfo('name'));
		
		// get default user message
		
		do_action('ltple_get_'.$type.'_message');
		
		if( empty($this->invitationMessage) ){
			
			$this->invitationMessage = 'Hello, ' . PHP_EOL . PHP_EOL;
			
			$this->invitationMessage .= 'I invite you to try ' . $company . ':' . PHP_EOL . PHP_EOL;
			
			$this->invitationMessage .= add_query_arg( array(
			
				'ri' =>	$this->parent->user->refId,
				
			), $this->parent->urls->editor ) . PHP_EOL . PHP_EOL;
			
			$this->invitationMessage .= 'Yours,' . PHP_EOL;
			$this->invitationMessage .= ucfirst( $this->parent->user->nickname ) . PHP_EOL;
		}		
		
		//output form			
			
		$this->invitationForm .= '<div class="well" style="display:inline-block;width:100%;">';
		
			$this->invitationForm .= '<div class="col-xs-12 col-md-6">';
			
				$this->invitationForm .= '<form action="' . $this->parent->urls->current . '" method="post">';
		
					$this->invitationForm .= '<input type="hidden" name="importType" value="'.$type.'" />';
					
					do_action('ltple_prepend_'.$type.'_form');
		
					$this->invitationForm .= '<h5 style="padding:15px 0 5px 0;font-weight:bold;">CSV list of emails</h5>';
				
					$this->invitationForm .= $this->parent->admin->display_field( array(
					
						'id' 			=> 'importEmails',
						'label'			=> 'Add emails',
						'description'	=> '<i style="font-size:11px;">Copy paste a list of max ' . $this->maxRequests . ' emails separated by comma or line break</i>',
						'placeholder'	=> 'example1@gmail.com' . PHP_EOL . 'example2@yahoo.com',
						'default'		=> ( !empty($_POST['importEmails']) ? $_POST['importEmails'] : ''),
						'type'			=> 'textarea',
						'style'			=> 'width:100%;height:100px;',
					), false, false );
				
					$this->invitationForm .= '<hr/>';
					
					$this->invitationForm .= '<h5 style="padding:15px 0 5px 0;font-weight:bold;">Add custom message</h5>';
					
					$this->invitationForm .= $this->parent->admin->display_field( array(
					
						'id' 			=> 'importMessage',
						'label'			=> 'Add custom message',
						'description'	=> '<i style="font-size:11px;">Use only text and line break, no HTML</i>',
						'placeholder'	=> 'Your custom message',
						'default'		=> ( !empty($_POST['importMessage']) ? $_POST['importMessage'] : $this->invitationMessage),
						'type'			=> 'textarea',
						'style'			=> 'width:100%;height:100px;',
					), false, false );
					
					do_action('ltple_append_invitation_form');
				
					$this->invitationForm .= '<hr/>';
				
					$this->invitationForm .= '<button style="margin-top:10px;" class="btn btn-xs btn-primary pull-right" type="submit">';
						
						$this->invitationForm .= 'Send';
						
					$this->invitationForm .= '</button>';
				
				$this->invitationForm .= '</form>';
			
			$this->invitationForm .= '</div>';
			
			$this->invitationForm .= '<div class="col-xs-12 col-md-6">';
			
				$this->invitationForm .= '<table class="table table-striped table-hover">';
				
					$this->invitationForm .= '<thead>';
						$this->invitationForm .= '<tr>';
							$this->invitationForm .= '<th><b>Information</b></th>';
						$this->invitationForm .= '</tr>';
					$this->invitationForm .= '</thead>';
					
					$this->invitationForm .= '<tbody>';
						$this->invitationForm .= '<tr>';
							$this->invitationForm .= '<td>Copy paste a list of emails separated by comma or line break that you want to invite.</td>';
						$this->invitationForm .= '</tr>';															
					$this->invitationForm .= '</tbody>';
					
				$this->invitationForm .= '</table>';			
			
			$this->invitationForm .= '</div>';
		
		$this->invitationForm .= '</div>';

		return $this->invitationForm;
	}
	
	public function schedule_invitations(){
			
		$response = false;
			
		$importType = '';
			
		if( !empty($_POST['importType']) ){
			
			$importType = sanitize_text_field($_POST['importType']);
		}
		
		//get time limit
		
		$max_execution_time = ini_get('max_execution_time'); 
		
		//remove time limit
		
		set_time_limit(0);

		//schedule_invitations
		
		if( !empty($importType) && method_exists($this->parent->{$importType},'schedule_invitations')){

			$response =  $this->parent->{$importType}->schedule_invitations();
		}
		else{
			
			// get users
					
			$users = array();			
			
			if(!empty($this->imported['imported'])){
				
				$users = $this->imported['imported'];
			}
			
			/*
			if(!empty($this->parent->email->imported['already registered'])){
			
				$users = array_merge($users,$this->parent->email->imported['already registered']);
			}
			*/

			if(!empty($users)){
				
				// get plan thumb
			
				$plan_thumb = get_option( $this->parent->_base . 'main_image' );
				
				// get company name
				
				$company = ucfirst(get_bloginfo('name'));
				
				// make invitations
				
				$m = 0;
				
				foreach($users as $i => $user){
					
					// get plan permalink
				
					$editor_url = add_query_arg( array(
						
						'ri' 	=> $this->parent->user->refId,
						
					), $this->parent->urls->editor ); 
					
					$can_spam = get_user_meta( $user['id'], $this->parent->_base . '_can_spam',true);

					if( $can_spam !== 'false' ){
					
						//get invitation title
						
						$invitation_title = 'User invitation - ' . ucfirst($this->parent->user->nickname) . ' is inviting you to try ' . $company . ' ';
						
						//check if invitation exists

						if( !$invitation = get_posts(array(
							
							'post_type' 	=> 'email-invitation',
							'author' 		=> $this->parent->user->ID,

							'meta_query' 	=> array(	
								array(
								
									'key' 		=> 'invited_user_email',
									'value' 	=> $user['email'],									
								),
							)
						
						))){

							//get invitation content
							
							$invitation_content = '<table style="width: 100%; max-width: 100%; min-width: 320px; background-color: #f1f1f1;margin:0;padding:40px 0 45px 0;margin:0 auto;text-align:center;border:0;">';
										
								$invitation_content .= '<tr>';
									
									$invitation_content .= '<td>';
										
										$invitation_content .= '<table style="width: 100%; max-width: 600px; min-width: 320px; background-color: #FFFFFF;border-radius:5px 5px 0 0;-moz-border-radius:5px 5px 0 0;-ms-border-radius:5px 5px 0 0;-o-border-radius:5px 5px 0 0;-webkit-border-radius:5px 5px 0 0;text-align:center;border:0;margin:0 auto;font-family: Arial, sans-serif;">';
											
											$invitation_content .= '<tr>';
												
												$invitation_content .= '<td style="text-align:center;background-color:#ffffff;border-radius:5px 5px 0 0;-moz-border-radius:5px 5px 0 0;-ms-border-radius:5px 5px 0 0;-o-border-radius:5px 5px 0 0;-webkit-border-radius:5px 5px 0 0;background-image: url('.$plan_thumb.');background-repeat:no-repeat;background-size:100% auto;background-position:top center;overflow:hidden;">';
													
													$invitation_content .= '<a href="'.$editor_url.'" target="_blank" title="'.$company.'" style="display:block;width:90%;height:350px;text-align:left;overflow:hidden;font-size:24px;color:#FFFFFF!important;text-decoration:none;font-weight:bold;padding:16px 14px 9px;font-family:Arial, Helvetica, sans-serif;position:reltive;margin:0 auto;">&nbsp;</a>';
													
												$invitation_content .= '</td>';
											
											$invitation_content .= '</tr>';
											
											$invitation_content .= '<tr>';
												
												$invitation_content .= '<td style="font-family: Arial, sans-serif;padding:10px 0 15px 0;font-size:19px;color:#888888;font-weight:bold;border-bottom:1px solid #cccccc;text-align:center;background-color:#FFFFFF;">';
													
													$invitation_content .= 'Friendly Invitation';
													
												$invitation_content .= '</td>';
											
											$invitation_content .= '</tr>';
											
											$invitation_content .= '<tr>';	

												$invitation_content .= '<td style="line-height: 25px;font-family: Arial, sans-serif;padding:20px;font-size:15px;color:#666666;text-align:left;font-weight: normal;border:0;background-color:#FFFFFF;">';
													
													$invitation_content .= 'Hello *|FNAME|*,' . PHP_EOL . PHP_EOL;
													
													$invitation_content .= ucfirst($this->parent->user->nickname) . ' is currently using <b>' . $company . '</b> and is inviting you to try it!' . PHP_EOL . PHP_EOL;
													
												$invitation_content .=  '</td>';
															
											$invitation_content .= '</tr>';
													
											if( !empty($_POST['importMessage']) ){
											
												$invitation_content .= '<tr>';	

													$invitation_content .= '<td style="line-height: 25px;font-family: Arial, sans-serif;padding:10px 20px ;font-size:15px;color:#666666;text-align:left;font-weight: normal;border:0;background-color:#FFFFFF;">';
																											
														$invitation_content .= 'Additional message from ' . ucfirst($this->parent->user->nickname) . ': ' . PHP_EOL;
															
													$invitation_content .=  '</td>';
															
												$invitation_content .= '</tr>';

												$invitation_content .= '<tr>';													
															
													$invitation_content .= '<td style="background: rgb(248, 248, 248);display:block;padding:20px;margin:20px;text-align:left;border-left: 5px solid #888;">';
															
														$invitation_content .= $_POST['importMessage'];
													
													$invitation_content .=  '</td>';
															
												$invitation_content .= '</tr>';														
											}

											$invitation_content .= '<tr>';	

												$invitation_content .= '<td style="font-family: Arial, sans-serif;height:150px;font-size:16px;color:#666666;text-align:center;border:0;background-color:#FFFFFF;">';
																												
													$invitation_content .=  '<a style="background: #ff9800;color: #fff;padding: 17px;text-decoration: none;border-radius: 5px;font-weight: bold;font-size: 20px;" href="'.$editor_url.'">Let\'s do it! </a>' . PHP_EOL . PHP_EOL;

												$invitation_content .=  '</td>';
											$invitation_content .=  '</tr>';
										$invitation_content .=  '</table>';
										
									$invitation_content .=  '<td>';
								$invitation_content .=  '<tr>';
							$invitation_content .=  '</table>';
							
							$invitation_content = str_replace(PHP_EOL,'<br/>',$invitation_content);
							
							//insert invitation
							
							if($invitation_id = wp_insert_post( array(
							
								'post_type'     	=> 'email-invitation',
								'post_title' 		=> $invitation_title,
								'post_content' 		=> $invitation_content,
								'post_status' 		=> 'publish',
								'menu_order' 		=> 0
							))){
								
								update_post_meta($invitation_id,'invited_user_email',$user['email']);
								
								if( $i == 0 ){
								
									$this->send_model($invitation_id,$user['email']);
								}
								else{
									
									wp_schedule_single_event( ( time() + ( 60 * $m ) ) , $this->parent->_base . 'send_email_event' , [$invitation_id,$user['email']] );
								}
								
								if ($i % 10 == 0) {
									
									++$m;
								}								
							}
						}
					}
				}
			}				
		}
		
		//reset time limit
		
		set_time_limit($max_execution_time);
		
		return $response;
	}
	
	/**
	 * Main LTPLE_Client_Email Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Email is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Email instance
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