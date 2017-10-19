<?php 
	
	$ltple = LTPLE_Client::instance();

	// get navbar
	
	if( !empty($_GET['pr']) ){
		
		echo'<div style="background: transparent;padding: 8px 15px;margin: 0;position: absolute;width: 100%;z-index: 1000;right: 0;left: 0;">';
	}
	else{
	
		echo'<div class="row" style="box-shadow:inset 0 -1px 10px -6px rgba(0,0,0,0.75);background: rgb(236, 236, 236);padding: 8px 0;margin: 0;border-bottom: 1px solid #ddd;position: relative;">';
	}
	
		echo'<div class="col-xs-6 col-sm-4" style="z-index:10;padding:0 8px;">';			
			
			if( is_admin() ){
				
				echo'<div class="pull-left">';

					echo'<a class="btn btn-sm btn-info" href="'. add_query_arg( 'action', 'edit', $ltple->urls->current ) .'" role="button">';
						
						echo'<span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> ';
						
						echo'Back';
					
					echo'</a>';
				
				echo'</div>';				
			}
			else{
				
				echo'<div class="pull-left">';

					echo'<a class="btn btn-sm btn-warning" href="'. $ltple->urls->editor .'" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Gallery of Designs" data-content="The gallery is where you can find beautifull designs to start a project. New things are added every week.">';
					
						echo'Gallery';
					
					echo'</a>';
				
				echo'</div>';
				
				echo'<div class="pull-left">';

					echo'<a style="margin-left: 6px;" class="btn btn-sm btn-primary" href="' . $ltple->urls->editor . '?media=user-images" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Media Library" data-content="The media library allows you to import and manage all your media, a good way to centralize everything.">';
						
						echo'Media';
					
					echo'</a>';
				
				echo'</div>';
				
				if( $ltple->settings->options->enable_ranking == 'on' ){
				
					echo'<div class="pull-left">';
			 
						echo'<a style="margin-left:5px;" class="popover-btn" href="' . $ltple->urls->editor . '?rank" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Popularity score" data-content="Your stars determine your rank in our World Ranking, give you visibility and drive traffic.">';
			  
							echo'<span class="badge"><span class="glyphicon glyphicon-star" aria-hidden="true"></span>  ' . $ltple->user->stars . '</span>';
						
						echo'</a>';
						
					echo'</div>';
				}
			}

		echo'</div>';
		
		echo'<div class="col-xs-6 col-sm-8 text-right" style="padding:0 5px;">';
			
			if( $ltple->layer->id > 0 ){
				

				// get elements
				
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
				
				if( !empty($elemLibraries) && ( isset($_GET['edit']) || !empty($ltple->user->layer->post_title) ) ){
						
					echo'<div style="margin:0 4px;" class="btn-group">';
					
						echo '<a class="btn btn-sm btn-info" href="#" data-toggle="dialog" data-target="#LiveTplEditorDndDialog">Elements</a>';
				
						echo '<div id="LiveTplEditorDndDialog" title="Elements library" style="display:none;">';
						echo '<div id="LiveTplEditorDndPanel">';
						
							echo '<div id="dragitemslist">';
								
								echo '<ul id="dragitemslistcontainer">';

									foreach( $elemLibraries as $elements ){
								
										if( !empty($elements['name']) ){
											
											foreach( $elements['name'] as $e => $name ){
												
												echo '<li draggable="true" data-insert-html="' . str_replace( array('\\"','"'), "'", $elements['content'][$e] ) . '">';
												
													echo '<span>'.$name.'</span>';
												
													echo '<img title="'.$name.'" height="60" src="' . $elements['image'][$e] . '" />';
												
												echo '</li>';
											}
										}
									}
									
								echo '</ul>';
								
							
							echo '</div>';
							
						echo '</div>';
						echo '</div>';				
				
					echo'</div>';
				}

				// get save button				
				
				if( is_admin() || ( $ltple->layer->type != 'cb-default-layer' && $ltple->user->plan["info"]["total_price_amount"] > 0 )){

					if( $ltple->user->has_layer || $ltple->user->is_admin ){
						
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
								
								echo'<button style="background-color: #3F51B5;border: 1px solid #5869ca;margin-right:5px;" class="btn btn-sm btn-primary" type="button" id="saveBtn">Save</button>';
								
							echo'</form>';
							
							if( $ltple->layer->type == 'user-default-layer' ){
							
								echo '<a class="btn btn-sm btn-danger" href="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '&postAction=delete">Delete</a>';
							}
						}
						
						echo '<a target="_blank" class="btn btn-sm btn-default" href="' . get_post_permalink( $ltple->layer->id ) . '?preview" style="margin-left: 4px;border:1px solid #9c6433;color: #fff;background-color: rgb(189, 120, 61);">View</a>';
					}
				}
			}
			
			if( $ltple->user->ID > 0  ){
			
				if( !empty($ltple->user->layers) && !is_admin() ){ 

					echo'<div style="margin:0 2px;" class="btn-group">';
					
						echo'<button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Load <span class="caret"></span></button>';
						
						echo'<ul class="dropdown-menu dropdown-menu-right" style="width:250px;">';
							
								foreach($ltple->user->layers as $i => $layer) {
									
									echo'<li style="position:relative;">';
										
										echo '<a href="' . $ltple->urls->editor . '?uri=' . $layer->ID . '">' . ( $i + 1 ) . ' - ' . ucfirst($layer->post_title) . '</a>';
										echo '<a class="btn-xs btn-danger" href="' . $ltple->urls->editor . '?uri=' . $layer->ID . '&postAction=delete" style="padding: 0px 5px;position: absolute;top: 11px;right: 11px;font-weight: bold;">x</a>';
									
									echo'</li>';						
								}
								
						echo'</ul>';
						
					echo'</div>';
				}
				elseif( $ltple->user->plan["info"]["total_price_amount"] ==0 ){ 
					
					echo '<button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-lock" aria-hidden="true" data-toggle="popover" data-placement="bottom" title="Pro users only" data-content="You need a paid plan ' . PHP_EOL . 'to unlock this action"></span> Load <span class="caret"></span></button>';
				}
			}

			if( ( $ltple->layer->type == 'cb-default-layer' && $ltple->user->is_admin ) || $ltple->layer->type == 'user-layer' ){
			
				echo'<div style="margin:0 2px;" class="btn-group">';
				
					echo'<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="margin-left:2px;font-size:15px;height:26px;background:transparent;border:none;color:rgb(177, 177, 177);"><span class="glyphicon glyphicon-cog icon-cog" aria-hidden="true"></span></button>';
										
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

						if( $ltple->user->is_admin ){
							
							/*
							
							// TODO repare breaking update layer
							
							echo'<li style="position:relative;">';
								
								echo '<a id="updateBtn" href="#update-layer">Update Template <span class="label label-warning pull-right">admin</span></a>';

							echo'</li>';
							*/						
						
							echo'<li style="position:relative;">';
								
								echo '<a target="_blank" href="' . get_edit_post_link( $ltple->layer->id ) . '"> Edit Backend <span class="label label-warning pull-right">admin</span></a>';

							echo'</li>';
							
							echo'<li style="position:relative;">';
								
								echo '<a target="_self" href="' . $ltple->urls->editor . '?uri=' . $ltple->layer->id . '&edit"> Edit Frontend <span class="label label-warning pull-right">admin</span></a>';

							echo'</li>';
							
							echo'<li style="position:relative;">';
								
								echo '<a target="_blank" href="' . get_post_permalink( $ltple->layer->id ) . '?preview"> Preview Template <span class="label label-warning pull-right">admin</span></a>';

							echo'</li>';
						}
						
					echo'</ul>';
					
				echo'</div>';
			}			

		echo'</div>';
		
	echo'</div>';

	if( $ltple->user->plan["info"]["total_price_amount"] == 0 ){

		echo'<div class="row" style="background-color: #65c5e8;font-size: 18px;color: #fff;padding: 20px;">';
			
			echo'<div class="col-xs-1 text-right">';
			
				echo'<span style="font-size:40px;" class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> ';
			
			echo'</div>';
			
			echo'<div class="col-xs-9">';

				echo'You are using a Demo version of ' . strtoupper(get_bloginfo( 'name' )) . '. Many features are missing such as: </br>';
				echo'Save & Load templates, Generate Meme images, Insert images from the Media Library, Copy CSS...';
			
			echo'</div>';
			
			echo'<div class="col-xs-2 text-right">';
			
				echo'<a class="btn btn-success btn-lg" href="' . $ltple->urls->plans . '"><span class="glyphicon glyphicon-hand-right" aria-hidden="true"></span> Upgrade now</a>';
			
			echo'</div>';
			
		echo'</div>';

	}