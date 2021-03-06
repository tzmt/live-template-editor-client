<?php

if ( ! defined( 'ABSPATH' ) ) exit;

	class LTPLE_Client_Admin_API {
		
		var $parent;
		var $html;
		
		/**
		 * Constructor function
		 */
		public function __construct ( $parent ) {
			
			$this->parent 	= $parent;
			
			add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 1 );
			
			add_shortcode('ltple-client-admin', array( $this , 'get_admin_frontend' ) );
						
			do_action( 'updated_option', array( $this, 'settings_updated' ), 10, 3 );
		}
		
		public function get_admin_frontend(){
			
			
		}

		/**
		 * Generate HTML for displaying fields
		 * @param  array   $field Field data
		 * @param  boolean $echo  Whether to echo the field HTML or return it
		 * @return void
		 */
		public function display_field ( $data = array(), $item = false, $echo = true ){

			// Get field info
			
			$field = ( isset( $data['field'] ) ? $data['field'] : $data );

			// Check for prefix on option name
			
			$option_name = ( isset( $data['prefix'] ) ? $data['prefix'] : '' );

			// Get saved data
			
			$data = '';
			
			if ( !empty( $field['data'] ) ) {
				
				$data = $field['data'];
				
				$option_name .= $field['id'];
			}
			elseif( !empty($field['callback']) ){
				
				add_filter('ltple_admin_api_get_' . $field['id'],$field['callback'],10,1);
				
				$data = apply_filters('ltple_admin_api_get_' . $field['id'],$item);
			}
			elseif ( !empty( $item->caps ) ) {
				
				// Get saved field data
				
				$option_name .= $field['id'];
				
				if( isset($item->{$field['id']}) ){
					
					$option = $item->{$field['id']};
					
				}
				else{
					
					$option = get_user_meta( $item->ID, $field['id'], true );
					
				}
				
				// Get data to display in field
				if ( isset( $option ) ) {
					
					$data = $option;
				}

			} 
			elseif ( !empty($item->ID) ) {

				// Get saved field data
				
				$option_name .= $field['id'];
				
				$option = get_post_meta( $item->ID, $field['id'], true );

				// Get data to display in field
				if ( isset( $option ) ) {
					$data = $option;
				}

			}
			elseif ( !empty($item->term_id) ) {

				// Get saved field data
				
				$option_name .= $field['id'];
				
				$option = get_term_meta( $item->term_id, $field['id'], true );

				// Get data to display in field
				if ( isset( $option ) ) {
					
					$data = $option;
				}

			} 
			else{

				// Get saved option
				
				$option_name .= $field['id'];
				
				$option = get_option( $option_name );

				// Get data to display in field
				
				if ( isset( $option ) ) {
					
					$data = $option;
				}
			}
			
			// get field id
			
			$id = esc_attr( str_replace(array('[',']'),array('_',''),$field['id']) );
			
			// get field style
			
			$style = '';
			
			if( !empty($field['style']) ){
				
				$style = ' style="'.$field['style'].'"';
			}
			
			// get field class
			
			$class = '';
			
			if( !empty($field['class']) ){
				
				$class = ' class="'.$field['class'].'"';
			}

			// Show default data if no option saved and default is supplied

			if( $data === '' && isset( $field['default'] ) ) {
				
				$data = $field['default'];
				
			} 
			elseif ( $data === false ) {
				
				$data = '';
			}
			
			$disabled = ( ( isset($field['disabled']) && $field['disabled'] === true ) ? ' disabled="disabled"' : '' );

			$required = ( ( isset($field['required']) && $field['required'] === true ) ? ' required="true"' : '' );
			
			$placeholder = ( isset($field['placeholder']) ? esc_attr($field['placeholder']) : '' );
			
			$html = '';

			switch( $field['type'] ) {

				case 'text':
				case 'url':
				case 'email':
					$html .= '<input' . $style . ' class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="' . esc_attr( $data ) . '" '.$required.$disabled.'/>' . "\n";
				break;
				
				case 'file':
				
					$html .= wp_nonce_field( $this->parent->file, $id . '_nonce',true,false);

					$html .= '<input' . $style . $class . ' class="form-control" id="' . $id . '" type="file" accept="'. ( !empty( $field['accept'] ) ? $field['accept'] : '' ) .'" name="' . esc_attr( $option_name ) . '" value="" '.$required.$disabled.'/>' . "\n";
				
					if( !empty($field['script']) ){
				
						$html .= '<script>' . $field['script'] . '</script>';
					}
					
				break;
				
				case 'message':
				
					$html .= '<div class="alert '. ( !empty( $field['class'] ) ? $field['class'] : 'alert-info' ) .'">'. $field['value'].'</div>';
					
				break;
				
				case 'slug':
					$html .= '<div' . $style . ' class="input-group">' . "\n";
					
						if( !empty($field['base']) ){
							
							$html .= '<span class="input-group-addon">'.$field['base'] . '</span>' . "\n";
						}
						else{
							
							$html .= '<span class="input-group-addon">' . $this->parent->urls->home . '/</span>' . "\n";
						}	
						 
						$html .= '<input class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="' . esc_attr( $data ) . '" '.$required.$disabled.'/>' . "\n";
						
						if( !isset($field['slash']) || $field['slash'] === true ){
							
							$html .= '<span class="input-group-addon">/</span>' . "\n";
						}
						
					$html .= '</div>' . "\n";
				break;
				
				case 'password':
					
					if ( isset( $field['show'] ) && $field['show'] === true ) {
					
						$html .= '<div class="input-group">';
					}
					
					$html .= '<input class="form-control" id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="' . esc_attr( $data ) . '"' . '/>' . "\n";
					
					if ( isset( $field['show'] ) && $field['show'] === true ) {
						
						$html .= '<span class="input-group-btn">';
					
							$html .= '<input type="submit" class="btn btn-default show-password" data-target="#'.$field['id'].'" value="Show" />';
						
						$html .= '</span>';
						
						$html .= '</div>';
					}
					
				break;
				
				case 'hidden':
					$html .= '<input class="form-control" id="' . esc_attr( $field['id'] ) . '" type="hidden" name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $data ) . '"' . '/>' . "\n";
				break;
				
				case 'number':
					$min = '';
					if ( isset( $field['min'] ) ) {
						$min = ' min="' . esc_attr( $field['min'] ) . '"';
					}

					$max = '';
					if ( isset( $field['max'] ) ) {
						$max = ' max="' . esc_attr( $field['max'] ) . '"';
					}
					$html .= '<input class="form-control" id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="' . esc_attr( $data ) . '"' . $min . '' . $max . '/>' . "\n";
				break;
				
				case 'text_secret':
					$html .= '<input class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="" '.$required.$disabled.'/>' . "\n";
				break;

				case 'textarea':
				 
					if( is_array($data) ){
						
						$data = json_encode($data, JSON_PRETTY_PRINT);
					}				 
				 
					if( !isset($field['stripcslashes']) || $field['stripcslashes'] == true ){

						$data = stripcslashes($data);
					}
					
					if( !isset($field['htmlentities']) || $field['htmlentities'] == true ){
						
						$data = htmlentities($data);
					}				
				
					$html .= '<textarea'.$style.' class="form-control" id="' . $id . '" style="width:100%;height:300px;" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '"'.$required.$disabled.'>' . $data . '</textarea>'. "\n";
				break;
				
				case 'switch':
					
					$checked = '';
					
					if ( $data && 'on' == $data ) {
						
						$checked = 'checked="checked"';
					
					}
					
					$html .= '<label class="switch">';
					
						$html .= '<input'.$style.' class="form-control" id="' . $id . '" type="checkbox" name="' . esc_attr( $option_name ) . '" ' . $checked . ''.$required.$disabled.'/>' . "\n";
						$html .= '<div class="slider round"></div>';
					
					$html .= '</label>';
					
				break;

				case 'checkbox':
					$checked = '';
					if ( $data && 'on' == $data ) {
						$checked = 'checked="checked"';
					}
					$html .= '<input'.$style.' class="form-control" id="' . $id . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . ''.$required.$disabled.'/>' . "\n";
				break;

				case 'checkbox_multi':
					
					$html .= '<div'.$style.' class="form-check">';
					
						foreach ( $field['options'] as $k => $v ) {
							
							$checked = false;
							if ( in_array( $k, (array) $data ) ) {
								$checked = true;
							}
							
							$html .= '<div for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="form-check-label checkbox_multi"><input class="form-check-input" type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" '.$required.$disabled.'/> ' . $v . '</div> ';
							//$html .= '<br>';
						}
					
					$html .= '</div>';
					
				break;
				
				case 'plan_value':
					
					$total_price_amount 	= $field['plan']['info']['total_price_amount'];
					$total_fee_amount 		= $field['plan']['info']['total_fee_amount'];
					$total_price_period		= $field['plan']['info']['total_price_period'];
					$total_fee_period		= $field['plan']['info']['total_fee_period'];
					$total_price_currency	= $field['plan']['info']['total_price_currency'];
					
					$html .= '<span style="color:red;font-weight:bold;font-size:20px;">';
					
						if( $total_fee_amount > 0 ){
							
							$html .= htmlentities(' ').round($total_fee_amount, 2).$total_price_currency.' '.$total_fee_period;
							$html .= '<br>+';
						}				
				
						$html .= round($total_price_amount, 2).$total_price_currency.' / '.$total_price_period;		
					
					$html .= '</span>';
					
				break;
				
				case 'edit_layer':
					
					$html .= '<div class="row">';
						
						$html .= '<div class="col-xs-6">';
						
							$html .= '<input' . $style . ' class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . $placeholder . '" value="' . esc_attr( $data ) . '" '.$required.'/>' . "\n";
						
						$html .= '</div>';
						
						$html .= '<div class="col-xs-6 text-center">';
						
							if( !empty($data) && is_numeric($data) ){
								
								$html .= '<a href="' . add_query_arg( 'action', 'ltple', $this->parent->urls->current ) . '" target="_self" class="button button-primary button-large">';
									
									$html .= 'Edit with LTPLE';
									
								$html .= '</a>';
							}
						
						$html .= '</div>';
						
					$html .= '</div>';
					
					//$html .= '<hr/>';
					
					if( empty($data) || !is_numeric($data) ){

						$layers = get_posts(array( 
					
							'post_type' 	=> 'cb-default-layer', 
							'posts_per_page'=> -1				
						));
						
						if( !empty( $layers ) ){
							
							$items = [];
							
							foreach( $layers as $layer ){
								
								$layer_type = $this->parent->layer->get_layer_type($layer);
								
								if( $layer_type->output == 'hosted-page' ){
									
									$item = '';
									
									$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4",$layer->ID) ) . '" id="post-' . $layer->ID . '">';
										
										$item.='<div class="panel panel-default">';
											
											$item.='<div class="thumb_wrapper" style="background:url(' . $this->parent->layer->get_thumbnail_url($layer) . ');height:120px;background-size:cover;background-repeat:no-repeat;background-position:top center;"></div>'; //thumb_wrapper
											
											$item.='<div class="panel-body" style="height:50px;overflow:hidden;">';
												
												$item.='<b>' . $layer->post_title . '</b>';
												
											$item.='</div>';
											
											$item.='<div class="panel-footer text-right">';

												if( intval($data) == $layer->ID ){

													$item.='<button type="button" class="btn btn-xs btn-success layer-selected" data-toggle="layer" data-target="'.$layer->ID.'">'.PHP_EOL;
														
														$item.='Selected'.PHP_EOL;
													
													$item.='</button>'.PHP_EOL;																			
												}
												else{
													
													$item.='<button type="button" class="btn btn-xs btn-warning" data-toggle="layer" data-target="'.$layer->ID.'">'.PHP_EOL;
														
														$item.='Select'.PHP_EOL;
													
													$item.='</button>'.PHP_EOL;										
												}

											$item.='</div>';
										
										$item.='</div>';
										
									$item.='</div>';

									$items[$layer_type->slug][]=$item;
								}
							}
							
							if( !empty($items) ){
								
								$html .= '<ul class="nav nav-tabs" role="tablist" style="margin-top:10px;">';

									$active=' class="active"';
									
									foreach($items as $type => $type_items){
										
										$html .= '<li role="presentation"'.$active.'><a href="#' . $type . '" aria-controls="' . $type . '" role="tab" data-toggle="tab">'.strtoupper(str_replace(array('-','_'),' ',$type)).'</a></li>';
										
										$active='';
									}

								$html .= '</ul>';	

								$html .= '<div class="tab-content row" style="margin-top:10px;">';

									$active=' active';
								
									foreach($items as $type => $type_items){
										
										$html .= '<div role="tabpanel" class="tab-pane'.$active.'" id="' . $type . '">';
										
										foreach($type_items as $item){

											$html .= $item;
										}
										
										$html .= '</div>';
										
										$active='';
									}
									
								$html .= '</div>';
								
								$html .= '<script>';
								
									$html .= ';(function($){';
										
										$html .= '$(document).ready(function(){';

											$html .= '$(\'[data-toggle="layer"]\').on(\'click\', function (e) {';
												
												$html .= '$(".layer-selected").html("Select").removeClass("btn-success layer-selected").addClass("btn-warning");';
												
												$html .= '$(this).html("Selected").removeClass("btn-warning").addClass("btn-success layer-selected");';
												
												$html .= '$("#defaultLayerId").val($(this).data(\'target\'));';
												
											$html .= '});';							
										
										$html .= '});';
										
									$html .= '})(jQuery);';								
								
								$html .= '</script>';
							}
						}
					}

				break;
				
				case 'checkbox_multi_plan_options':
					
					$total_price_amount 	= 0;
					$total_fee_amount 		= 0;
					$total_price_period		='month';
					$total_fee_period		='once';
					$total_price_currency	='$';
					
					$html .= '<table class="widefat fixed striped" style="border:none;">';
					
					foreach ( $field['options'] as $taxonomy => $terms ) {
						
						$html .= '<tr>';
							
							$html .= '<th style="width:150px;">';
								
								$html .= '<div for="' . $taxonomy . '">'.$taxonomy.'</div> ';
									
							$html .= '</th>';
							
							// attribute column
							
							$html .= '<td style="width:250px;">';
							
							foreach($terms as $term){

								$checked = false;
								
								if ( in_array( $term->slug, (array) $data ) ) {
									
									$checked = true;
								}
								
								$html .= '<span style="display:block;padding:1px 0;margin:0;">';
									
									$html .= '<div for="' . esc_attr( $field['id'] . '_' . $term->slug ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $term->slug ) . '" id="' . esc_attr( $field['id'] . '_' . $term->slug ) . '" /> ' . $term->name . '</div> ';
								
								$html .= '</span>';
							}
							
							$html .= '</td>';
							
							// storage column

							$html .= '<td>';
								
								foreach($terms as $term){
									
									$plan_options = (array) $data;
									
									if ( in_array( $term->slug, $plan_options ) ) {
										
										$total_fee_amount 	= $this->parent->plan->sum_total_price_amount( $total_fee_amount, $term->options, $total_fee_period);
										$total_price_amount = $this->parent->plan->sum_total_price_amount( $total_price_amount, $term->options, $total_price_period);
										$total_storage 		= $this->parent->plan->sum_total_storage( $total_storage, $term->options);
									}

									$html .= '<span style="display:block;padding:1px 0 3px 0;margin:0;">';
									
										if( $term->options['storage_unit'] == 'templates' ){
											
											if( $term->options['storage_amount'] == 1 ){
												
												$html .= '+'.$term->options['storage_amount'].' project';
											}
											elseif( $term->options['storage_amount'] > 0 ){
												
												$html .= '+'.$term->options['storage_amount'].' projects';
											}
											else{
												
												$html .= $term->options['storage_amount'].' projects';
											}
										}
										elseif($term->options['storage_amount']>0){
											
											$html .= '+'.$term->options['storage_amount'].' '.$term->options['storage_unit'];
										}	
										else{
											
											$html .= $term->options['storage_amount'].' '.$term->options['storage_unit'];
										}														
							
									$html .= '</span>';
								}
							
							$html .= '</td>';
							
							// price column
							
							$html .= '<td>';
							
							foreach($terms as $term){
								
								$html .= '<span style="display:block;padding:1px 0 3px 0;margin:0;">';
								
									$html .= $term->options['price_amount'].$term->options['price_currency'].' / '.$term->options['price_period'];							
							
								$html .= '</span>';
							}
							
							$html .= '</td>';
							
							// get addon options
							
							$this->html = '';
							
							do_action('ltple_api_layer_plan_option',$terms);
							
							$html .= $this->html;	
							
						$html .= '</tr>';
							
					}

					$html .= '<tr style="font-weight:bold;">';
						
						$html .= '<th style="width:200px;">';
							
							$html .= '<div style="font-weight:bold;" for="totals">TOTALS</div> ';
								
						$html .= '</th>';
						
						$html .= '<td style="width:250px;"></td>';
						
						// total storage
						
						$html .= '<td>';
							
							if(!empty($total_storage)){
								
								foreach($total_storage as $storage_unit => $total_storage_amount){
									
									$html .= '<span style="display:block;">';
									
										if($storage_unit=='templates'&&$total_storage_amount==1){
											
											$html .= '+'.$total_storage_amount.' template';
										}
										elseif($total_storage_amount>0){
											
											$html .= '+'.$total_storage_amount.' '.$storage_unit;
										}									
										else{
											
											$html .= $total_storage_amount.' '.$storage_unit;
										}
										
									$html .= '</span>';
								}							
							}
							
						$html .= '</td>'; 
						
						// total price
						
						$html .= '<td>';

							if( $total_fee_amount > 0 ){
								
								$html .= htmlentities(' ').round($total_fee_amount, 2).$total_price_currency.' '.$total_fee_period;
								$html .= '<br>+';
							}
			
							$html .= round($total_price_amount, 2).$total_price_currency.' / '.$total_price_period;

						$html .= '</td>';	

						// get addon options total
						
						$this->html = '';
						
						do_action('ltple_api_layer_plan_option_total',$field['options'], $plan_options);
						
						$html .= $this->html;							
					
					$html .= '</table>';
					
				break;
				
				case 'ux_flow_charts':
				
					$html .= '<div id="the-list">';
					
						$folders = glob( $this->parent->assets_dir . '/images/flow-charts/*', GLOB_ONLYDIR  );

						foreach( $folders as $folder ){
							
							$images = glob( $folder . '/*.{jpg,png,gif}', GLOB_BRACE  );
							
							$html .= '<h4 style="background:rgb(241, 241, 241);padding:10px;">' . ucfirst(basename($folder)) . '</h4>';
							
							$html .= '<div class="row">';

								foreach($images as $image){
									
									$url = $this->parent->assets_url . 'images/flow-charts/' . basename($folder) . '/' . basename($image);

									$html .= '<div class="col-xs-2">';
										
										$html .= '<img style="width:100%;" src="' . $url . '" />';
										
										$html .= '<input style="width:100%;margin-bottom:15px;" type="text" value="'.$url.'" />';
										
									$html .= '</div>';
								} 
								
							$html .= '</div>';
						}
					
					$html .= '</div>';
				
				break;
				
				case 'addon_plugins':
					
					$html .= '<div id="the-list">';
					
						foreach( $this->parent->settings->addons as $addon ){
					
							$html .= '<div class="panel panel-default plugin-card plugin-card-akismet">';
							
								$html .= '<div class="panel-body plugin-card-top">';
								
									$html .= '<h3>';
									
										$html .= '<a href="'.$addon['addon_link'].'" class="thickbox open-plugin-details-modal">';
											
											$html .= $addon['title'];	
											
										$html .= '</a>';
										
									$html .= '</h3>';
									
									$html .= '<p>'.$addon['description'].'</p>';
									$html .= '<p class="authors"> <cite>By <a target="_blank" href="'.$addon['author_link'].'">'.$addon['author'].'</a></cite></p>';
									
								$html .= '</div>';
								
								$html .= '<div class="panel-footer plugin-card-bottom text-right">';
									
									$plugin_file = $addon['addon_name'] . '/' . $addon['addon_name'] . '.php';
									
									if( !file_exists( WP_PLUGIN_DIR . '/' . $addon['addon_name'] . '/' . $addon['addon_name'] . '.php' ) ){
										
										$url = $addon['source_url'];
										
										$html .= '<a href="' . $url . '" class="button install-now" aria-label="Install">Install Now</a>';
									}
									else{
										
										if( !empty($_GET['action']) && !empty($_GET['plugin']) && file_exists( WP_PLUGIN_DIR . '/' . $_GET['plugin'] ) ){
											
											// do activation deactivation

											$is_activate = is_plugin_active( $_GET['plugin'] );
											
											if( $_GET['action'] == 'activate' && !$is_activate ){
												
												activate_plugin($_GET['plugin']);
											}
											elseif( $_GET['action'] == 'deactivate' && $is_activate ){
												
												deactivate_plugins($_GET['plugin']);
											}
										}
										
										// output button
										
										if( is_plugin_active( $addon['addon_name'] . '/' . $addon['addon_name'] . '.php' ) ){

											//$url = wp_nonce_url( 'http://ltple.recuweb.com/wp-admin/plugins.php?action=deactivate&plugin='.urlencode( $plugin_file ), 'deactivate-plugin_' . $plugin_file );
										
											$url = add_query_arg( array(
												'action' => 'deactivate',
												'plugin' => urlencode( $plugin_file ),
											), $this->parent->urls->current );
												
											$html .= '<a href="'.$url.'" class="button deactivate-now" aria-label="Deactivate">Deactivate</a>';
										}
										else{
											
											//$url = wp_nonce_url( 'http://ltple.recuweb.com/wp-admin/plugins.php?action=activate&plugin='.urlencode( $plugin_file ), 'activate-plugin_' . $plugin_file );
											
											$url = add_query_arg( array(
												'action' => 'activate',
												'plugin' => urlencode( $plugin_file ),
											), $this->parent->urls->current );									
											
											$html .= '<a href="'.$url.'" class="button activate-now" aria-label="Activate">Activate</a>';
										}
									}
								
								$html .= '</div>';
							
							$html .= '</div>';
						}
					
					$html .= '</div>';
				
				break;
				
				case 'email_series':
				
					if( isset($data['model']) && isset($data['days']) ){
						
						$email_series = $data;
					}
					else{
						
						$email_series = ['model' => [ 0 => '' ], 'days' => [ 0 => 0 ]];
					}
					
					$html .= '<div id="email_series" class="sortable">';
						
						$html .= ' <a href="#" class="add-input-group" data-target="'.$field['id'].'-row" style="line-height:40px;">Add email</a>';
					
						$html .= '<ul class="input-group ui-sortable">';
							
							foreach( $email_series['model'] as $e => $model) {
										
								if($e > 0){
									
									$class='input-group-row ui-state-default ui-sortable-handle';
								}
								else{
									
									$class='input-group-row ui-state-default ui-state-disabled';
								}

								$html .= '<li class="'.$class.' '.$field['id'].'-row">';
							
									$html .= 'Send  ';
									
									$html .= '<select style="width:350px;" name="email_series[model][]" id="plan_email_model">';

									foreach ( $field['email-models'] as $k => $v ) {
										
										$selected = false;
										
										if ( $k == $model ) {
											
											$selected = true;
										}
										elseif( isset($field['model-selected']) && $field['model-selected'] == $k ){
											
											$selected = true;
										}
										
										$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
									}
									
									$html .= '</select> ';

									$html .= ' + ';
									
									$html .= '<input type="number" step="1" min="0" max="1000" placeholder="0" name="email_series[days][]" id="plan_email_days" style="width: 50px;" value="'.$email_series['days'][$e].'">';
									
									$html .= ' day(s) after triggered ';
									
									if( $e > 0 ){
										
										$html .= '<a class="remove-input-group" href="#">[ x ]</a> ';
									}
									

								$html .= '</li>';						
							}
							
						$html .= '</ul>';
						
					$html .= '</div>';

				break;
				
				case 'key_value':

					if( !isset($data['key']) || !isset($data['value']) ){

						$data = ['key' => [ 0 => '' ], 'value' => [ 0 => '' ]];
					}

					if( !empty($field['inputs']) && is_string($field['inputs']) ){
						
						$inputs = [$field['inputs']];
					}
					elseif(empty($field['inputs'])||!is_array($field['inputs'])) {
						
						$inputs = ['string','text','number','password','url','parameter','xpath','attribute','folder','filename'];
					}				
					else{
					
						$inputs = $field['inputs'];
					}

					$html .= '<div id="'.$field['id'].'" class="sortable">';
						
						$html .= ' <a href="#" class="add-input-group" data-target="'.$field['id'].'-row" style="line-height:40px;">Add field</a>';
					
						$html .= '<ul class="input-group ui-sortable">';
							
							foreach( $data['key'] as $e => $key) {

								if($e > 0){
									
									$class='input-group-row ui-state-default ui-sortable-handle';
								}
								else{
									
									$class='input-group-row ui-state-default ui-state-disabled';
								}
							
								$value = str_replace('\\\'','\'',$data['value'][$e]);
										
								$html .= '<li class="'.$class.' '.$field['id'].'-row" style="display:inline-block;width:100%;">';
							
									$html .= '<select name="'.$option_name.'[input][]" style="float:left;">';

									foreach ( $inputs as $input ) {
										
										$selected = false;
										if ( isset($data['input'][$e]) && $data['input'][$e] == $input ) {
											
											$selected = true;
										}
										
										$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $input ) . '">' . $input . '</option>';
									}
									
									$html .= '</select> ';
							
									$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['key']) ? $field['placeholder']['key'] : 'key' ).'" name="'.$option_name.'[key][]" style="width:30%;float:left;" value="'.$data['key'][$e].'">';
									
									$html .= '<span style="float:left;"> => </span>';
									
									if(isset($data['input'][$e])){
										
										if($data['input'][$e] == 'number'){
											
											$html .= '<input type="number" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'number' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
										elseif($data['input'][$e] == 'password'){
											
											$html .= '<input type="password" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'password' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
										elseif($data['input'][$e] == 'text'){
											
											$html .= '<textarea placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'text' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;height:200px;">' . $value . '</textarea>';
										}										
										else{
											
											$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'value' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
									}
									else{
										
										$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'value' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
									}

									if( $e > 0 ){
										
										$html .= '<a class="remove-input-group" href="#">[ x ]</a> ';
									}

								$html .= '</li>';						
							}
						
						$html .= '</ul>';					
						
					$html .= '</div>';

				break;
				
				case 'values':
					
					if( empty($data) ){
						
						$data = [ '' => [0 => '' ] ];
					}

					$html .= '<div id="'.$field['id'].'" class="sortable">';
						
						$html .= ' <a href="#" class="add-input-group" data-target="'.$field['id'].'-row" style="line-height:40px;">Add field</a>';
					
						$html .= '<ul class="input-group ui-sortable">';
							
							$first = key($data);

							foreach( $data[$first] as $e => $v) {

								if($e > 0){
									
									$class='input-group-row ui-state-default ui-sortable-handle';
								}
								else{
									
									$class='input-group-row ui-state-default ui-state-disabled';
								}
							
								$html .= '<li class="'.$class.' '.$field['id'].'-row" style="display:inline-block;width:100%;">';
									
									foreach( $field['values'] as $key => $f) {
										
										$f['id'] = $field['id'] . '[' . $key . '][]';

										$f['default'] = ( isset($data[$key][$e]) ? $data[$key][$e] : '' ) ;
										
										$html .= '<div style="width:150px;display: inline-block;margin: 0 5px;">';
										
											$html .= '<label>';
											
												$html .= $f['name'];
											
												$html .= $this->display_field($f,[],false);
											
											$html .= '</label>';
											
										$html .= '</div>';
									}
					
									if( $e > 0 ){
										
										$html .= '<a class="remove-input-group" href="#">[ x ]</a> ';
									}

								$html .= '</li>';						
							}
						
						$html .= '</ul>';					
						
					$html .= '</div>';				
					
					
					/*
					if( !isset($data['key']) || !isset($data['value']) ){

						$data = ['key' => [ 0 => '' ], 'value' => [ 0 => '' ]];
					}

					if( !empty($field['inputs']) && is_string($field['inputs']) ){
						
						$inputs = [$field['inputs']];
					}
					elseif(empty($field['inputs'])||!is_array($field['inputs'])) {
						
						$inputs = ['string','text','number','password','url','parameter','xpath','attribute','folder','filename'];
					}				
					else{
					
						$inputs = $field['inputs'];
					}

					$html .= '<div id="'.$field['id'].'" class="sortable">';
						
						$html .= ' <a href="#" class="add-input-group" data-target="'.$field['id'].'-row" style="line-height:40px;">Add field</a>';
					
						$html .= '<ul class="input-group ui-sortable">';
							
							foreach( $data['key'] as $e => $key) {

								if($e > 0){
									
									$class='input-group-row ui-state-default ui-sortable-handle';
								}
								else{
									
									$class='input-group-row ui-state-default ui-state-disabled';
								}
							
								$value = str_replace('\\\'','\'',$data['value'][$e]);
										
								$html .= '<li class="'.$class.' '.$field['id'].'-row" style="display:inline-block;width:100%;">';
							
									$html .= '<select name="'.$option_name.'[input][]" style="float:left;">';

									foreach ( $inputs as $input ) {
										
										$selected = false;
										if ( isset($data['input'][$e]) && $data['input'][$e] == $input ) {
											
											$selected = true;
										}
										
										$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $input ) . '">' . $input . '</option>';
									}
									
									$html .= '</select> ';
							
									$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['key']) ? $field['placeholder']['key'] : 'key' ).'" name="'.$option_name.'[key][]" style="width:30%;float:left;" value="'.$data['key'][$e].'">';
									
									$html .= '<span style="float:left;"> => </span>';
									
									if(isset($data['input'][$e])){
										
										if($data['input'][$e] == 'number'){
											
											$html .= '<input type="number" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'number' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
										elseif($data['input'][$e] == 'password'){
											
											$html .= '<input type="password" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'password' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
										elseif($data['input'][$e] == 'text'){
											
											$html .= '<textarea placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'text' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;height:200px;">' . $value . '</textarea>';
										}										
										else{
											
											$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'value' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
									}
									else{
										
										$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'value' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
									}

									if( $e > 0 ){
										
										$html .= '<a class="remove-input-group" href="#">[ x ]</a> ';
									}

								$html .= '</li>';						
							}
						
						$html .= '</ul>';					
						
					$html .= '</div>';
					*/

				break;

				case 'form':

					if( !isset($data['name']) || !isset($data['value']) ){

						$data = array(
						
							'name' 		=> [ 0 => '' ],
							'required' 	=> [ 0 => '' ],
							'value' 	=> [ 0 => '' ],
						);
					}

					$inputs 	= ['title','label','checkbox','select','text','textarea','number','password','domain','submit'];
					$required 	= ['required','optional'];
					$id 		= ( !empty($field['id']) ? $field['id'] : 'form' );
					
					$html .= '<div id="'.$id.'" class="sortable">';
						
						if( !isset($field['action']) ){
						
							$html .= ' <a href="#" class="add-input-group" data-target="'.$field['id'].'-row" style="line-height:40px;">Add field</a>';
						
							$html .= '<ul class="input-group ui-sortable" style="width:100%;">';
								
								foreach( $data['name'] as $e => $name) {
									
									if($e > 0){
										
										$class='input-group-row ui-state-default ui-sortable-handle';
									}
									else{
										
										$class='input-group-row ui-state-default ui-state-disabled';
									}								
									
									$req_val 	= ( isset($data['required'][$e]) ? str_replace('\\\'','\'',$data['required'][$e]): 'optional');
									$value 		= ( isset($data['value'][$e]) 	 ? str_replace('\\\'','\'',$data['value'][$e]) 	 : '');
											
									$html .= '<li class="'.$class.' '.$field['id'].'-row" style="display:inline-block;width:100%;border-top:1px solid #eee;padding:15px 0 10px 0;margin:0;">';
								
										// inputs
								
										$html .= '<select class="form-control" name="'.$field['name'].'[input][]" style="width:20%;height:34px;float:left;">';

											foreach ( $inputs as $input ) {
												
												$selected = false;
												if ( isset($data['input'][$e]) && $data['input'][$e] == $input ) {
													
													$selected = true;
												}
												
												$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $input ) . '">' . $input . '</option>';
											}
										
										$html .= '</select> ';
										
										// required
								
										if ( isset($data['input'][$e]) && in_array($data['input'][$e],['title','label','submit']) ) {

											$disabled = ' disabled="disabled"';
										}
										else{
											
											$disabled = '';
										}
										
										$html .= '<select class="form-control" name="'.$field['name'].'[required][]" style="width:20%;height:34px;float:left;"'.$disabled.'>';

											foreach ( $required as $r ) {
												
												$selected = false;
												if ( empty($disabled) && isset($data['required'][$e]) && $data['required'][$e] == $r ) {
													
													$selected = true;
												}
												
												$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $r ) . '">' . $r . '</option>';
											}
										
										$html .= '</select> ';
								
										if( isset($data['input'][$e]) && $data['input'][$e] == 'domain'){
											
											$html .= '<input class="form-control" type="text" style="width:25%;float:left;" value="domain_name" disabled="true">';
											$html .= '<input type="hidden" name="'.$field['name'].'[name][]" value="domain_name">'; 
										}	
										else{
											
											$html .= '<input class="form-control" type="text" placeholder="name" name="'.$field['name'].'[name][]" style="width:25%;float:left;" value="'.$data['name'][$e].'">';
										}
										
										//$html .= '<span style="float:left;"> => </span>';
										
										if(isset($data['input'][$e])){
											
											if($data['input'][$e] == 'number'){
												
												$html .= '<input class="form-control" type="number" placeholder="number" name="'.$field['name'].'[value][]" style="width:30%;float:left;" value="'.$value.'">';
											}
											elseif($data['input'][$e] == 'password'){
												
												$html .= '<input class="form-control" type="password" placeholder="password" name="'.$field['name'].'[value][]" style="width:30%;float:left;" value="'.$value.'">';
											}
											elseif($data['input'][$e] == 'textarea'){
												
												$html .= '<textarea class="form-control" placeholder="text" name="'.$field['name'].'[value][]" style="width:30%;float:left;height:100px;">' . $value . '</textarea>';
											}									
											elseif($data['input'][$e] == 'text'){
												
												$html .= '<input class="form-control" type="text" placeholder="value" name="'.$field['name'].'[value][]" style="width:30%;float:left;" value="'.$value.'">';
											}
											else{
												
												$html .= '<textarea class="form-control" placeholder="values" name="'.$field['name'].'[value][]" style="width:30%;float:left;height:100px;">' . $value . '</textarea>';
											}
										}
										else{
											
											$html .= '<textarea class="form-control" placeholder="values" name="'.$field['name'].'[value][]" style="width:30%;float:left;height:100px;">' . $value . '</textarea>';
											
											//$html .= '<input class="form-control" type="text" placeholder="value" name="'.$field['name'].'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}

										if( $e > 0 ){
											
											$html .= '<a class="remove-input-group" style="padding-left:10px;" href="#">[ x ]</a> ';
										}

									$html .= '</li>';						
								}
								
							$html .= '</ul>';
						
						}
						else{
							
							$method = ( ( isset($field['method']) && $field['method'] == 'post' ) ? 'post' : 'get' );
							
							$html .= '<form action="'.$field['action'].'" method="'.$method.'">';

							foreach( $data['name'] as $e => $name) {
								
								if(isset($data['input'][$e])){

									$required = ( ( empty($data['required'][$e]) || $data['required'][$e] == 'required' ) ? true : false );
									
									if($data['input'][$e] == 'title'){
										
										$html .= '<h4 id="'.ucfirst($name).'">'.ucfirst(ucfirst($data['value'][$e])).'</h4>';
									}
									elseif($data['input'][$e] == 'label'){
										
										$html .= '<label class="label label-default" style="padding:6px;margin:7px 0;text-align:left;display:block;font-weight:bold;font-size:14px;" id="'.ucfirst($name).'">'.ucfirst(ucfirst($data['value'][$e])).'</label>';
									}
									elseif($data['input'][$e] == 'submit'){
										
										$html .= '<div class="form-group" style="margin: 7px 0 0 0;">';
										
											$html .= '<button style="width:100%;" type="'.$data['input'][$e].'" id="'.ucfirst($data['name'][$e]).'" class="control-input pull-right btn btn-sm btn-primary">'.ucfirst(ucfirst($data['value'][$e])).'</button>';
										
										$html .= '</div>';
									}
									elseif( $data['input'][$e] == 'domain' ){

										$html .= $this->display_field( array(
								
											'type'				=> $data['input'][$e],
											'id'				=> $id.'['.$name.']',
											'value' 			=> $data['value'][$e],
											'required' 			=> $required,
											'placeholder' 		=> '',
											'description'		=> '',
											'default'			=> ( isset($_REQUEST[$id][$name]) ? $_REQUEST[$id][$name] : ''),
											
										), false, false ); 									
									}
									elseif( $data['input'][$e] == 'checkbox' || $data['input'][$e] == 'select' ){

										if( $values = explode(PHP_EOL,$data['value'][$e]) ){
									
											$options = [];
											
											if( $data['input'][$e] == 'select' ){
												
												$options[] = '';
											}
									
											foreach( $values as $value ){
												
												$value = trim($value);
												
												if( !empty($value) ){
												
													$options[strtolower($value)] = ucfirst($value);
												}
											}
									
											if( $data['input'][$e] == 'checkbox' ){
									
												$html .= $this->display_field( array(
										
													'type'				=> 'checkbox_multi',
													'id'				=> $id.'['.$name.']',
													'options' 			=> $options,
													'required' 			=> false,
													'description'		=> '',
													'style'				=> 'margin:0px 10px;',
													'default'			=> ( isset($_REQUEST[$id][$name]) ? $_REQUEST[$id][$name] : ''),
													
												), false, false ); 
											}
											else{
												
												$html .= $this->display_field( array(
										
													'type'				=> 'select',
													'id'				=> $id.'['.$name.']',
													'options' 			=> $options,
													'required' 			=> $required,
													'description'		=> '',
													'style'				=> 'height:30px;padding:0px 5px;',
													'default'			=> ( isset($_REQUEST[$id][$name]) ? $_REQUEST[$id][$name] : ''),
													
												), false, false ); 											
											}
										}									
									}								
									else{
										
										$html .= $this->display_field( array(
								
											'type'				=> $data['input'][$e],
											'id'				=> $id.'['.$name.']',
											'value' 			=> $data['value'][$e],
											'required' 			=> $required,
											'placeholder' 		=> '',
											'description'		=> '',
											'default'			=> ( isset($_REQUEST[$id][$name]) ? $_REQUEST[$id][$name] : ''),
											
										), false, false ); 
									}
								}							
							}
							
							$html .= '</form>';
						}
						
					$html .= '</div>';

				break;

				case 'element':
					
					//$types = ['grid','section','form','media','mix'];
					$types = ['headers','features','blogs','teams','projects','products','pricing','testimonials','contact'];
					
					if( !isset($data['name']) ){

						$data = array(
						
							'name' 		=> [ 0 => '' ],
							'category' 	=> [ 0 => '' ],
							'image' 	=> [ 0 => '' ],
							'content' 	=> [ 0 => '' ],
						);
					}

					$id = ( !empty($field['id']) ? $field['id'] : 'elements' );

					$html .= '<div id="'.$id.'" class="sortable">';
						
						$html .= ' <a href="#" class="add-input-group" data-target="'.$field['id'].'-row" style="line-height:40px;">Add element</a>';
					
						$html .= '<ul class="input-group ui-sortable" style="width:100%;">';
							
							foreach( $data['name'] as $e => $name) {
								
								$image 		= $data['image'][$e];
								$content 	= stripslashes($data['content'][$e]);
								
								if($e > 0){
									
									$class='input-group-row ui-state-default ui-sortable-handle';
								}
								else{
									
									$class='input-group-row ui-state-default ui-state-disabled';
								}								
									
								$html .= '<li class="'.$class.' '.$field['id'].'-row" style="display:inline-block;width:100%;border-top:1px solid #eee;padding:15px 0 10px 0;margin:0;">';
									
									$html .= '<div class="col-sm-11">';
										
										// name
										
										$html .= '<div class="form-group">';
									
											$html .= '<label class="col-sm-2">Name</label>';
											
											$html .= '<div class="col-sm-10">';
											
												$html .= '<input class="form-control" style="width:100%;" type="text" placeholder="value" name="'.$field['name'].'[name][]" value="'.$name.'">';
										
											$html .= '</div>';
										
										$html .= '</div>';
										
										// type
										
										$html .= '<div class="form-group">';
										
											$html .= '<label class="col-sm-2">Type</label>';
									
											$html .= '<div class="col-sm-10">';
												
												$html .= '<select style="height:35px;" class="form-control" name="'.$field['name'].'[type][]">';

													foreach ( $types as $type ) {
														
														$selected = false;
														
														if ( isset($data['type'][$e]) && $data['type'][$e] == $type ) {
															
															$selected = true;
														}
														
														$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $type ) . '">' . ucfirst($type) . '</option>';
													}
												
												$html .= '</select> ';
											
											$html .= '</div>';
											
										$html .= '</div>';
										
										// image
										
										$html .= '<div class="form-group">';
									
											$html .= '<label class="col-sm-2">Image</label>';
											
											$html .= '<div class="col-sm-10">';
											
												$html .= '<input class="form-control" style="width:100%;" type="text" placeholder="http://" name="'.$field['name'].'[image][]" value="'.$image.'">';
										
											$html .= '</div>';
										
										$html .= '</div>';
										
										// content
										
										$html .= '<div class="form-group">';
									
											$html .= '<label class="col-sm-2">Content</label>';
											
											$html .= '<div class="col-sm-10">';
											
												$html .= '<textarea class="form-control" placeholder="HTML content" name="'.$field['name'].'[content][]">' . $content . '</textarea>';
										
											$html .= '</div>';
										
										$html .= '</div>';
										
									$html .= '</div>';
									
									if( $e > 0 ){
										
										$html .= '<div class="col-sm-1" style="padding:0;">';
										
											$html .= '<a class="remove-input-group" style="padding-left:10px;" href="#">[ x ]</a> ';
										
										$html .= '</div>';
									}

								$html .= '</li>';						
							}
							
						$html .= '</ul>';
						
					$html .= '</div>';

				break;

				case 'license':
					
					if( !empty($data) ){
					
						$is_valid = $this->parent->license->is_valid();
					}
					else{
						
						$is_valid = false;
					}

					echo '<input class="regular-text" type="text" id="' . esc_attr( $option_name ) . '" name="' . esc_attr( $option_name ) . '"  value="' . $data . '" ' . ( $is_valid ? 'disabled' : '') . '>';
					echo '<p class="submit">';
									
						if( !$is_valid ){
							
							echo '<input type="submit" name="activate_license" value="Activate" class="button-primary" />';
						}
						else{
							
							echo '<input type="hidden" name="' . esc_attr( $option_name ) . '" value="'.$data.'" />';
							echo '<input type="submit" name="deactivate_license" value="Deactivate" class="button" />';
						}
						
					echo '</p>';				
						 
				break;				
				
				case 'radio':
					
					$i = 0;
					
					foreach ( $field['options'] as $k => $v ) {
						
						$checked = false;
						
						if( $k == $data || ( empty($data) && $i == 0 ) ) {
							
							$checked = true;
						}
						
						$html .= '<div for="' . esc_attr( $field['id'] . '_' . $k ) . '">';
						
						$html .= '<input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ';
							
							$html .= $v; 
							
						$html .= '</div> ';
						
						if( isset($field['inline']) && $field['inline'] === false ){
							
							$html .= '<br>'; 
						}
						
						$i++;
					}
					
				break;
				
				case 'avatar':
					
					$checked = array();
					
					$data = remove_query_arg('_',$data);
					
					foreach ( $field['options'] as $k => $v ) {

						if( $k === 0){
						
							$checked[$k] = true;
						}
						else{
							
							$checked[$k] = false;
							
							if ( $v == $data ) {
								
								$checked[$k] 	= true;
								$checked[0] 	= false;
							}						
						}		
					}
					
					foreach ( $field['options'] as $k => $v ) {
						
						$image_url = add_query_arg('_',time(),$v);
						
						$html .= '<div for="' . esc_attr( $field['id'] . '_' . $k ) . '" style="width:50px;text-align:center;display:inline-block;">';
						
							$html .= '<img class="img-circle" src="' . $image_url . '" height="50" width="50" title="My picture '.( $k + 1 ).'" />'; 
						
							$html .= '<input type="radio" ' . checked( $checked[$k], true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $image_url ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" />';

						$html .= '</div>';
					}
					
					$html .= '<div class="input-group col-xs-10" style="margin:10px 0;">';
					
						$html .= '<input style="padding:2px;height:26px;" class="form-control input-sm" type="file" name="avatar" accept="image/*">';
						
						$html .= '<div class="input-group-btn">';
						
							$html .= '<input style="height: 26px;line-height: 0px;" class="btn btn-sm btn-default" value="Upload" type="submit">';
						
						$html .= '</div>';
						
					$html .= '</div>';
					
				break;
				
				case 'banner': 
					
					$html .= '<img src="'.$field['default'].'" />';
					
					$html .= '<div class="input-group col-xs-10" style="margin:10px 0;">';
					
						$html .= '<input style="padding:2px;height:26px;" class="form-control input-sm" type="file" name="banner" accept="image/*">';
						
						$html .= '<div class="input-group-btn">';
						
							$html .= '<input style="height: 26px;line-height: 0px;" class="btn btn-sm btn-default" value="Upload" type="submit">';
						
						$html .= '</div>';
						
					$html .= '</div>';
					
				break;
				
				case 'select':
					
					$html .= '<div class="form-group" style="margin:7px 0;">';
					
						if(isset($field['name'])){
							
							$html .= '<select'.$style.' class="form-control" name="' . $field['name'] . '" id="' . $id . '"'.$required.$disabled.'>';
						}
						else{
							
							$html .= '<select'.$style.' class="form-control" name="' . esc_attr( $option_name ) . '" id="' . $id . '"'.$required.$disabled.'>';
						}

						foreach ( $field['options'] as $k => $v ) {
							$selected = false;
							if ( $k == $data ) {
								
								$selected = true;
							}
							elseif(isset($field['selected']) && $field['selected'] == $k ){
								
								$selected = true;
							}
							$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
						}
						$html .= '</select> ';
						
					$html .= '</div>';
					
				break;

				case 'select_multi':
					$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . $id . '" multiple="multiple">';
					foreach ( $field['options'] as $k => $v ) {
						$selected = false;
						if ( in_array( $k, (array) $data ) ) {
							$selected = true;
						}
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
					}
					$html .= '</select> ';
				break;
				
				case 'dropdown_categories':

					$html .=wp_dropdown_categories(array(
					
						'show_option_none' => 'None',
						'taxonomy'     => $field['taxonomy'],
						'name'    	   => $field['name'],
						'show_count'   => false,
						'hierarchical' => true,
						'selected'     => $data,
						'echo'		   => false,
						'class'		   => 'form-control',
						'hide_empty'   => false
					));			
				
				break;			
				
				case 'dropdown_main_apps':
				
					//get admin IDs
					
					$users = get_users(array('role' => 'administrator'));
					
					$ids=[];
					
					foreach($users as $user){
						
						$ids[]=$user->ID;
					}

					//get app accounts
					
					$apps = get_posts(array(
					
						'author__in'  => $ids,
						'post_type'   => 'user-app',
						'post_status' => 'publish',
						'numberposts' => -1
					));
					
					$selected_id 	= get_option( $this->parent->_base . $field['id'] );
					$options 		= array( -1 => 'none');
					
					foreach($apps as $app){

						if(strpos($app->post_name, $field['app'] . '-')===0){
							
							$options[$app->ID] = str_replace($field['app'].' - ','',$app->post_title);
						}
					}
					
					if(isset($field['name'])){
						
						$html .= '<select class="form-control" name="' . $field['name'] . '" id="' . $id . '">';
					}
					else{
						
						$html .= '<select class="form-control" name="' . esc_attr( $option_name ) . '" id="' . $id . '">';
					}

					foreach ( $options as $k => $v ) {
						
						$selected = false;
						
						if ( $k == $data ) {
							
							$selected = true;
						}
						elseif($selected_id == $k ){
							
							$selected = true;
						}
						
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
					}
					$html .= '</select> ';

				break;

				case 'action_schedule':
					
					$html .= '<div id="'.$option_name.'">';

						$html .= ucfirst($field['action']).' ';				
						
						if( !empty($field['appId']) ){
							
							$html .= '<input type="hidden" name="'.$option_name.'[args][]" id="'.$option_name.'_app_id" value="'.$field['appId'].'"> ';
						}							
						
						if( $field['last'] === true ){
						
							$html .= 'last ';
						
							$html .= '<input type="number" step="1" min="0" max="100" placeholder="0" name="'.$option_name.'[args][]" id="'.$option_name.'_last" style="width: 50px;" value="'.( !empty($data['args'][1]) ? $data['args'][1] : 10 ).'"> ';
							
							$html .= ucfirst($field['unit']).' ';
						}
						
						$html .= 'every ';
						
						$html .= '<input type="number" step="5" min="15" max="60" placeholder="0" name="'.$option_name.'[every]" id="'.$option_name.'_every" style="width: 50px;" value="'.( isset($data['every']) ? $data['every'] : 15 ).'"> ';
						
						$html .= 'minutes ';

					$html .= '</div>';

				break;

				case 'image':
					$image_thumb = '';
					if ( $data ) {
						$image_thumb = wp_get_attachment_thumb_url( $data );
					}
					$html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
					$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'live-template-editor-client' ) . '" data-uploader_button_text="' . __( 'Use image' , 'live-template-editor-client' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'live-template-editor-client' ) . '" />' . "\n";
					$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'live-template-editor-client' ) . '" />' . "\n";
					$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
				break;
				
				case 'color':
					?><div class="color-picker" style="position:relative;">
						<input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color form-control" value="<?php esc_attr_e( $data ); ?>" />
						<div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
					</div>
					<?php
				break;

			}

			//output description
			
			switch( $field['type'] ) {

				case 'checkbox_multi':
				case 'radio':
				case 'select_multi':
				
					if( !empty($field['description']) ){
						
						$html .= '<br/><span class="description">' . $field['description'] . '</span>';
					}
					
				break;

				default:
					
					if(!empty($field['description'])){
					
						if ( ! $item ) {
							
							$html .= '<div for="' . $id . '">' . "\n";
						}

						$html .= '<div><i style="color:#aaa;">' . $field['description'] . '</i></div>' . "\n";

						if ( ! $item ) {
							
							$html .= '</div>' . "\n";
						}
					}
					
				break;
			}

			if ( ! $echo ) {
				return $html;
			}

			echo $html;

		}

		/**
		 * Validate form field
		 * @param  string $data Submitted value
		 * @param  string $type Type of field to validate
		 * @return string       Validated value
		 */
		public function validate_field ( $data = '', $type = 'text' ) {

			switch( $type ) {
				case 'text'	: $data = esc_attr( $data ); break;
				case 'url'	: $data = esc_url( $data ); break;
				case 'email': $data = is_email( $data ); break;
			}

			return $data;
		}

		/**
		 * Add meta box to the dashboard
		 * @param string $id            Unique ID for metabox
		 * @param string $title         Display title of metabox
		 * @param array  $post_types    Post types to which this metabox applies
		 * @param string $context       Context in which to display this metabox ('advanced' or 'side')
		 * @param string $priority      Priority of this metabox ('default', 'low' or 'high')
		 * @param array  $callback_args Any axtra arguments that will be passed to the display function for this metabox
		 * @return void
		 */
		public function add_meta_box ( $id = '', $title = '', $post_types = array(), $context = 'advanced', $priority = 'default', $callback_args = null ) {

			// Get post type(s)
			if ( ! is_array( $post_types ) ) {
				
				$post_types = array( $post_types );
			}

			// Generate each metabox
			foreach ( $post_types as $post_type ) {
				
				add_meta_box( $id, $title, array( $this, 'meta_box_content' ), $post_type, $context, $priority, $callback_args );
			}
		}

		public function add_meta_boxes($fields){
			
			if( !empty($fields) ){
				
				foreach( $fields as $field ){
					
					if( !empty($field['metabox']) ){
					
						if( !isset($field['metabox']['add_new']) || $field['metabox']['add_new'] || !empty($_REQUEST['post']) ){
						
							if( !empty($field['metabox']['name']) && !empty($field['metabox']['title']) && !empty($field['metabox']['screen']) && !empty($field['metabox']['context']) ){
								
								$this->add_meta_box(
									
									$field['metabox']['name'],
									$field['metabox']['title'],
									$field['metabox']['screen'],
									$field['metabox']['context']
								);						
							}
						}
					}
				}
			}
		}
		
		/**
		 * Display metabox content
		 * @param  object $post Post object
		 * @param  array  $args Arguments unique to this metabox
		 * @return void
		 */
		public function meta_box_content ( $post, $args ) {

			$fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type );

			if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

			echo '<div class="custom-field-panel">' . "\n";

			foreach ( $fields as $field ) {

				if ( ! isset( $field['metabox'] ) ) continue;

				if ( ! is_array( $field['metabox'] ) ) {
					
					$field['metabox'] = array( $field['metabox'] );
				}

				if ( in_array( $args['id'], $field['metabox'] ) ) {

					$this->display_meta_box_field( $field, $post );
				}
			}

			echo '</div>' . "\n";
		}

		/**
		 * Dispay field in metabox
		 * @param  array  $field Field data
		 * @param  object $post  Post object
		 * @return void
		 */
		public function display_meta_box_field ( $field = array(), $post ) {

			if ( ! is_array( $field ) || 0 == count( $field ) ) return;

			$meta_box  = '<p class="form-field form-group">' . PHP_EOL;
			
				if(!empty($field['label'])){
					
					$meta_box .= '<div for="' . $field['id'] . '">' . $field['label'] . '</div> ' . PHP_EOL;
				}
				
				$meta_box .= $this->display_field( $field, $post, false ) . PHP_EOL;
				
			$meta_box .= '</p>' . PHP_EOL;

			echo $meta_box;
		}

		/**
		 * Save metabox fields
		 * @param  integer $post_id Post ID
		 * @return void
		 */
		public function save_meta_boxes ( $post_id = 0 ) {

			if ( ! $post_id ) return;

			$post_type = get_post_type( $post_id );

			$fields = apply_filters( $post_type . '_custom_fields', array(), $post_type );

			if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

			foreach ( $fields as $field ) {
				
				if ( isset( $_REQUEST[ $field['id'] ] ) ) {
					
					update_post_meta( $post_id, $field['id'], $this->validate_field( $_REQUEST[ $field['id'] ], $field['type'] ) );
				} 
				else {
					
					update_post_meta( $post_id, $field['id'], '' );
				}
			}
		}
	}

