<?php 
	
	$ltple = LTPLE_Client::instance();

	if( empty($_REQUEST['output']) || $_REQUEST['output'] != 'widget' ){

		// get navbar
		
		if( $ltple->profile->id > 0 ){
			
			echo'<div style="background: transparent;padding: 8px 15px;margin: 0;position: absolute;width: 100%;z-index: 1000;right: 0;left: 0;">';
		}
		else{
		
			echo'<div class="row" style="box-shadow:inset 0 -1px 10px -6px rgba(0,0,0,0.75);background: rgb(236, 236, 236);padding: 8px 0;margin: 0;border-bottom: 1px solid #ddd;position: relative;">';
		}
		
			echo'<div class="col-xs-6 col-sm-4" style="z-index:10;padding:0 8px;">';			
				
				if( $ltple->profile->id == 0 ){
				
					echo'<div class="pull-left">';
					
						echo'<button type="button" id="sidebarCollapse">';
								
							echo'<i class="glyphicon glyphicon-align-left"></i>';
							
						echo'</button>';

					echo'</div>';
				}
				
				echo'<div class="pull-left">';

					echo'<a style="background:' . $ltple->settings->mainColor . ';border:1px solid ' . $ltple->settings->borderColor . ';" class="btn btn-sm" href="'. $ltple->urls->editor .'" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Gallery of Templates" data-content="The gallery is where you can find templates to start a project. New things are added every weeks.">';
					
						echo'Gallery';
					
					echo'</a>';
				
				echo'</div>';				
				
				if( $ltple->user->loggedin === true ){
					
					echo'<div class="pull-left">';

						echo'<a style="margin-left:6px;background: ' . $ltple->settings->mainColor . '99;border: 1px solid ' . $ltple->settings->borderColor . ';" class="btn btn-sm" href="' . $ltple->urls->media . 'user-images/" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Media Library" data-content="The media library allows you to import and manage all your media, a good way to centralize everything.">';
							
							echo'Media';
						
						echo'</a>';
					
					echo'</div>';
					
					do_action('ltple_left_navbar');
					
					if( $ltple->layer->id > 0 ){
						
						// elements button
					
						$elemLibraries = array();
						
						if( !empty($ltple->layer->defaultElements['name'][0]) ){
							
							$elemLibraries[] = $ltple->layer->defaultElements;
						}			
						
						if( !empty($ltple->layer->layerHtmlLibraries) ){
						
							foreach( $ltple->layer->layerHtmlLibraries as $term ){
								
								$elements = get_option( 'elements_' . $term->slug );

								if( !empty($elements['name'][0]) ){
									
									$elemLibraries[] = $elements;
								}
							} 
						}
					}
				}

			echo'</div>';
			
			echo'<div class="col-xs-6 col-sm-8 text-right" style="padding:0 5px;">';
				
				if( $ltple->user->loggedin === true ){
					
					if(  $ltple->layer->id > 0 && ( isset($_GET['uri']) || is_admin() ) ){
						
						// insert button
						
						if( $this->layer->layerOutput == 'image' ){

							echo '<button style="margin-left:2px;margin-right:2px;border:1px solid #761b86;background:#9C27B0;" id="elementsBtn" class="btn btn-sm pull-left" href="#" data-toggle="dialog" data-target="#LiveImgEditorElements" data-height="450" data-width="75%" data-resizable="false">Insert</button>';
					
							echo '<div id="LiveImgEditorElements" title="Elements library" style="display:none;">'; 
							echo '<div id="LiveImgEditorElementsPanel">';
								
								echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';
								
								echo'<iframe data-src="' . $this->urls->media . '?output=widget" style="border:0;width:100%;height:100%;position:absolute;top:0;bottom:0;right:0;left:0;"></iframe>';
								
							echo '</div>';
							echo '</div>';										
						}					
						elseif( !empty($elemLibraries) && ( isset($_GET['edit']) || isset($_GET['quick']) || $ltple->layer->type == 'user-layer' || is_admin() ) ){
							
							echo'<style>'.PHP_EOL;

								echo'#dragitemslistcontainer {
									
									margin: 0;
									padding: 0;
									/*
									height: 69px;
									overflow: hidden;
									border-bottom: 3px solid #eee;
									background: rgb(201, 217, 231);
									*/
									width: 100%;
									display:inline-block;
								}

								#dragitemslistcontainer li {
									
									float: left;
									position: relative;
									text-align: center;
									list-style: none;
									cursor: move; /* fallback if grab cursor is unsupported */
									cursor: grab;
									cursor: -moz-grab;
									cursor: -webkit-grab;
								}

								#dragitemslistcontainer li:active {
									cursor: grabbing;
									cursor: -moz-grabbing;
									cursor: -webkit-grabbing;
								}

								#dragitemslistcontainer span {
									
									float: left;
									position: absolute;
									left: 0;
									right: 0;
									background: rgba(52, 87, 116, 0.49);
									color: #fff;
									font-weight: bold;
									padding: 15px 5px;
									font-size: 16px;
									line-height: 25px;
									margin: 48px 4px 0 4px;
								}

								#dragitemslistcontainer li img {
									margin:3px 2px;
								}';		

							echo'</style>'.PHP_EOL;							
							
							echo '<button style="margin-left:2px;margin-right:2px;border:1px solid #761b86;background:#9C27B0;" id="elementsBtn" class="btn btn-sm pull-left" href="#" data-toggle="dialog" data-target="#LiveTplEditorDndDialog" data-height="300" data-width="500" data-resizable="false">Insert</button>';
					
							echo '<div id="LiveTplEditorDndDialog" title="Elements library" style="display:none;">';
							echo '<div id="LiveTplEditorDndPanel">';
							
								echo '<div id="dragitemslist">';
									
									$list = [];
									
									foreach( $elemLibraries as $elements ){
								
										if( !empty($elements['name']) ){
											
											foreach( $elements['name'] as $e => $name ){
												
												if( !empty($elements['type'][$e]) ){
												
													$type = $elements['type'][$e];
													
													$item = '<li draggable="true" data-insert-html="' . str_replace( array('\\"','"',"\\'"), "'", $elements['content'][$e] ) . '">';
													
														$item .= '<span>'.$name.'</span>';
													
														if( !empty($elements['image'][$e]) ){
													
															$item .= '<img title="'.$name.'" height="150" src="' . $elements['image'][$e] . '" />';
														}
														else{
															
															$item .= '<img title="'.$name.'" height="150" src="' . $this->server->url . '/c/p/live-template-editor-resources/assets/images/flow-charts/corporate/testimonials-slider.jpg" />';
															
															//$item .= '<div style="height: 115px;width: 150px;background: #afcfff;border: 4px solid #fff;"></div>';
														}
													$item .= '</li>';
													
													$list[$type][] = $item;
												}
											}
										}
									}
										
									//echo'<div class="library-content">';
											
										echo'<ul class="nav nav-pills" role="tablist">';

										$active=' class="active"';
										
										foreach($list as $type => $items){
											
											echo'<li role="presentation"'.$active.'><a href="#' . $type . '" aria-controls="' . $type . '" role="tab" data-toggle="tab">'.ucfirst(str_replace(array('-','_'),' ',$type)).' <span class="badge">'.count($list[$type]).'</span></a></li>';
											
											$active='';
										}							

										echo'</ul>';
										
									//echo'</div>';

									echo'<div id="dragitemslistcontainer" class="tab-content row">';
										
										$active=' active';
									
										foreach($list as $type => $items){
											
											echo'<ul role="tabpanel" class="tab-pane'.$active.'" id="' . $type . '">';
											
											foreach($items as $item){

												echo $item;
											}
											
											echo'</ul>';
											
											$active='';
										}
										
									echo'</div>';
								
								echo '</div>';
								
							echo '</div>';
							echo '</div>';				
						}

						if( is_admin() || ( $ltple->layer->type != 'cb-default-layer' && $ltple->user->plan["info"]["total_price_amount"] > 0 )){

							if( $ltple->user->has_layer ){
								
								// save button
								
								if( !empty($ltple->user->layer->post_title) ){

									$post_title = $ltple->user->layer->post_title;
									
									echo'<form style="display:inline-block;" target="_parent" action="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '" id="savePostForm" method="post">';
										
										echo'<input type="hidden" name="postTitle" id="postTitle" value="' . $post_title . '" class="form-control required" placeholder="Template Title">';
										echo'<input type="hidden" name="postContent" id="postContent" value="">';
										echo'<input type="hidden" name="postCss" id="postCss" value="">';
										echo'<input type="hidden" name="postJs" id="postJs" value="">';
										echo'<input type="hidden" name="postAction" id="postAction" value="save">';
										echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
										 
										wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
										
										echo'<input type="hidden" name="submitted" id="submitted" value="true">';
										
										echo'<div id="navLoader" style="float:left;margin-right:10px;display:none;"><img src="' . $this->assets_url . 'loader.gif" style="height: 20px;"></div>';				
										
										echo'<button style="background-color:#5869ca;border: 1px solid #3F51B5;" class="btn btn-sm" type="button" id="saveBtn">Save</button>';
										
									echo'</form>';
								}
								
								// view button 
								
								if( $ltple->layer->layerOutput != 'image' ){
									
									$preview = add_query_arg(array(
									
										'preview' => '',
									
									), get_post_permalink( $ltple->layer->id ));
									
									echo '<a target="_blank" class="btn btn-sm" href="' . $preview . '" style="margin-left:2px;margin-right:2px;border:1px solid #9c6433;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
								}
								
								// delete button
								
								if( $ltple->layer->type == 'user-layer' ){

									echo '<a style="border: 1px solid #c70000;background: #f44336;" class="btn btn-sm" href="#removeCurrentTpl" data-toggle="dialog" data-target="#removeCurrentTpl">Delete</a>';
								
									echo'<div style="display:none;" id="removeCurrentTpl" title="Remove current template">';
										
										echo '<h4>Are you sure you want to delete this template?</h4>';						

										echo '<a style="margin:10px;" class="btn btn-xs btn-success" href="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '&postAction=delete&confirmed">Yes</a>';
										
										//echo '<button style="margin:10px;" type="button" class="btn btn-xs btn-danger ui-button ui-widget" role="button" title="Close"><span class="ui-button-text">No</span></button>';

									echo'</div>';						
								}
							}
						}
				
						if( $ltple->layer->type == 'cb-default-layer' && $ltple->user->is_editor ){
							
							// load button
							
							$post_title = $ltple->layer->title;
							
							echo'<form style="display:inline-block;" target="_parent" action="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '" id="savePostForm" method="post">';
								
								echo'<input type="hidden" name="postTitle" id="postTitle" value="' . $post_title . '" class="form-control required" placeholder="Template Title">';
								echo'<input type="hidden" name="postContent" id="postContent" value="">';
								echo'<input type="hidden" name="postCss" id="postCss" value="">';
								echo'<input type="hidden" name="postJs" id="postJs" value="">';
								echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
								 
								wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
								
								echo'<input type="hidden" name="submitted" id="submitted" value="true">';
								
								echo'<div id="navLoader" style="margin-right:10px;display:none;"><img src="' . $this->assets_url . 'loader.gif" style="height: 20px;"></div>';				

								if( isset($_GET['edit']) ){
									
									echo'<input type="hidden" name="postAction" id="postAction" value="update">';
									
									echo'<button style="background-color:#5869ca;border:1px solid #3F51B5;" class="btn btn-sm" type="button" id="saveBtn">Update</button>';
								}
								else{
									
									echo'<input type="hidden" name="postAction" id="postAction" value="save">';
								}
								
							echo'</form>';
							
							if( !isset($_GET['quick']) ){
							
								// view button
							
								echo '<a target="_blank" class="btn btn-sm" href="' . get_post_permalink( $ltple->layer->id ) . '?preview" style="margin-left:2px;margin-right:2px;border:1px solid #9c6433;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
							}
						}

						if( $ltple->layer->layerOutput == 'canvas' && ( $ltple->layer->type == 'user-layer' || isset($_REQUEST['edit']) || isset($_REQUEST['quick']) ) ){
							
							/*
							echo '<div style="margin:0 2px;" class="btn-group">';
								
								echo '<button id="uploadImgBtn" type="button" class="btn btn-sm dropdown-toggle" style="border: 1px solid #773680;background: #a44caf;">';
								
									echo 'Upload';
								
								echo '</button>';
								
							echo '</div>';
							*/
							
							echo '<div style="margin:0 2px;" class="btn-group">';
								
								echo '<button id="downloadImgBtn" type="button" class="btn btn-sm dropdown-toggle" style="border: 1px solid #386e82;background: #4c94af;">';
								
									echo 'Download';
								
								echo '</button>';
								
							echo '</div>';
						}
						elseif( $ltple->layer->layerOutput == 'image' ){

							echo '<div style="margin:0 2px;" class="btn-group">';
								
								echo '<button id="downloadImgBtn" type="button" class="btn btn-sm dropdown-toggle" style="border: 1px solid #386e82;background: #4c94af;">';
								
									echo 'Download';
								
								echo '</button>';
								
							echo '</div>';							
						}	
					}
					
					if( $ltple->user->ID > 0  ){
						
						do_action('ltple_right_navbar');
						 
						if( !empty($ltple->user->layers) && !is_admin() ){ 

							echo'<div style="margin:0 2px;" class="btn-group">';
							
								echo'<button style="border: 1px solid #3d8840;" type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Load <span class="caret"></span></button>';
								
								echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
									
										foreach($ltple->user->layers as $i => $layer) {
											
											echo'<li style="position:relative;">';
												
												echo '<a href="' . $ltple->urls->editor . '?uri=' . $layer->ID . '">' . ( $i + 1 ) . ' - ' . ucfirst($layer->post_title) . '</a>';
												echo '<a href="#quickRemoveTpl' . ( $i + 1 ) . '" data-toggle="dialog" data-target="#quickRemoveTpl' . ( $i + 1 ) . '" class="btn-xs btn-danger" style="padding: 0px 5px;position: absolute;top: 11px;right: 11px;font-weight: bold;">x</a>';

												echo'<div style="display:none;" id="quickRemoveTpl' . ( $i + 1 ) . '" title="Remove Template ' . ( $i + 1 ) . '">';
													
													echo '<h4>Are you sure you want to delete this template?</h4>';						

													echo '<a style="margin:10px;" class="btn btn-xs btn-success" href="' . $ltple->urls->editor . '?uri=' . $layer->ID . '&postAction=delete&confirmed" target="'.( $ltple->layer->id == $layer->ID ? '_self' : '_self' ).'">Yes</a>';
													
													//echo '<button style="margin:10px;" type="button" class="btn btn-xs btn-danger ui-button ui-widget" role="button" title="Close"><span class="ui-button-text">No</span></button>';

												echo'</div>';
											
											echo'</li>';						
										}
										
								echo'</ul>';
								
							echo'</div>';
						}
						elseif( $ltple->user->plan["info"]["total_price_amount"] ==0 ){ 
							
							echo '<button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-lock" aria-hidden="true" data-toggle="popover" data-placement="bottom" title="Pro users only" data-content="You need a paid plan ' . PHP_EOL . 'to unlock this action"></span> Load <span class="caret"></span></button>';
						}
					}

					if( ( $ltple->layer->type == 'cb-default-layer' && $ltple->user->is_editor ) || $ltple->layer->type == 'user-layer' ){
					
						echo'<div style="margin:0 2px;" class="btn-group">';
						
							echo'<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size:14px;height:28px;background:#345774;border:1px solid #1b2e3e;color: #fff;"><span class="glyphicon glyphicon-cog icon-cog" aria-hidden="true"></span></button>';
												
							echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
								
								echo'<li style="position:relative;">';
								
									echo '<a href="#duplicateLayer" data-toggle="dialog" data-target="#duplicateLayer">Duplicate Template ' . ( $ltple->layer->type == 'cb-default-layer' ? '<span class="label label-warning pull-right">admin</span>' : '' ) . '</a>';

									echo'<div id="duplicateLayer" title="Duplicate Template">';
										
										echo'<form class="" style="width:250px;display:inline-block;" target="_parent" action="' . $ltple->urls->current . '" id="duplicatePostForm" method="post">';
											
											echo'<input type="text" name="postTitle" value="" class="form-control input-sm required" placeholder="Template Title" style="margin:7px 0;">';
											echo'<input type="hidden" name="postAction" id="postAction" value="duplicate">';
											echo'<input type="hidden" name="postContent" value="">';
											echo'<input type="hidden" name="postCss" value="">'; 
											echo'<input type="hidden" name="postJs" value="">'; 									
											echo'<input type="hidden" name="postSettings" id="postSettings" value="">';
											
											wp_nonce_field( 'user_layer_nonce', 'user_layer_nonce_field' );
											
											echo'<input type="hidden" name="submitted" id="submitted" value="true">';
											
											echo'<div class="ui-helper-clearfix ui-dialog-buttonset">';

												echo'<button class="btn btn-xs btn-primary pull-right" type="submit" id="duplicateBtn" style="border-radius:3px;">Duplicate</button>';
										 
											echo'</div>';
											
										echo'</form>';								
										
									echo'</div>';						
									
								echo'</li>';

								if( $ltple->user->is_editor ){
									
									echo'<li style="position:relative;">';
										
										echo '<a target="_blank" href="' . get_edit_post_link( $ltple->layer->id ) . '"> Edit Backend <span class="label label-warning pull-right">admin</span></a>';

									echo'</li>';
									
									if( $ltple->layer->type == 'cb-default-layer' && empty($ltple->user->layer->post_title) ){
									
										echo'<li style="position:relative;">';
											
											echo '<a target="_self" href="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '&edit"> Edit Frontend <span class="label label-warning pull-right">admin</span></a>';

										echo'</li>';
									}
									
									echo'<li style="position:relative;">';
										
										echo '<a target="_blank" href="' . get_post_permalink( $ltple->layer->id ) . '?preview"> Preview Template <span class="label label-warning pull-right">admin</span></a>';

									echo'</li>';
								}
								
							echo'</ul>';
							
						echo'</div>';
					}
				}
				else{
					

					echo'<a style="margin:0 2px;" class="btn btn-sm btn-success" href="'. wp_login_url( $ltple->request->proto . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) .'">Login</a>';
					
					echo'<a style="margin:0 2px;" class="btn btn-sm btn-info" href="'. wp_login_url( $ltple->urls->editor ) .'&action=register">Register</a>';
										
				}

			echo'</div>';
			
		echo'</div>';
	}
	