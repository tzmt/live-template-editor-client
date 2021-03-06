<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Layer extends LTPLE_Client_Object { 
	
	public $parent;
	
	public $defaultFields 	= array();
	public $userFields 		= array();
	
	public $id				= -1;
	public $defaultId		= -1;
	public $uri				= '';
	public $key				= ''; // gives the server proxy access to the layer
	public $slug			= '';
	public $title			= '';
	public $type			= '';
	public $form			= '';
	public $embedded		= '';
	public $outputs			= '';

	public $is_local		= false;	
	
	public $sections		= null;
	public $types			= null; 
	public $ranges			= null;
	
	public $accountOptions 	= array();
	public $columns			= '';
	public $column			= '';
	public $options			= array(); 
	
	/**
	 * Constructor function
	 */ 
	
	public function __construct( $parent ) {
		
		$this->parent = $parent;
		
		$this->parent->register_post_type( 'cb-default-layer', __( 'Default Templates', 'live-template-editor-client' ), __( 'Default Template', 'live-template-editor-client' ), '', array(

			'public' 				=> true,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'cb-default-layer',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export'			=> true,
			'rewrite' 				=> array('slug'=>'default-layer'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'excerpt', 'thumbnail', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		)); 

		$this->parent->register_post_type( 'user-layer', __( 'User Templates', 'live-template-editor-client' ), __( 'User Template', 'live-template-editor-client' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'user-layer',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> array('slug'=>'user-layer'),
			'capability_type' 		=> 'post',
			'has_archive' 			=> true,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array( 'title', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));

		$this->parent->register_taxonomy( 'layer-type', __( 'Template Types', 'live-template-editor-client' ), __( 'Template Type', 'live-template-editor-client' ),  array('user-plan','cb-default-layer'), array(
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'layer-range', __( 'Template Ranges', 'live-template-editor-client' ), __( 'Template Range', 'live-template-editor-client' ), array('user-plan','cb-default-layer'), array(
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'account-option', __( 'Template Options', 'live-template-editor-client' ), __( 'Template Option', 'live-template-editor-client' ),  array('user-plan'), array(
			'hierarchical' 			=> false,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'css-library', __( 'CSS Libraries', 'live-template-editor-client' ), __( 'CSS Library', 'live-template-editor-client' ),  array('cb-default-layer'), array(
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'js-library', __( 'JS Libraries', 'live-template-editor-client' ), __( 'JS Library', 'live-template-editor-client' ),  array('cb-default-layer'), array(
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		$this->parent->register_taxonomy( 'font-library', __( 'Font Libraries', 'live-template-editor-client' ), __( 'Font Library', 'live-template-editor-client' ),  array('cb-default-layer'), array(
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => true,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));
		
		add_action( 'add_meta_boxes', function(){

			global $post;
			
			$fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type );
			
			if( $post->post_type == 'cb-default-layer' ){
				
				if( empty($_REQUEST['post']) ){
					
					// remove all metaboxes except submit button
					
					global $wp_meta_boxes;
					
					$submitbox = $wp_meta_boxes[$post->post_type]['side']['core']['submitdiv'];

					$wp_meta_boxes[$post->post_type]['side']['core'] 	= array( 'submitdiv' => $submitbox );
					$wp_meta_boxes[$post->post_type]['side']['low'] 	= array();
					$wp_meta_boxes[$post->post_type]['normal'] 			= array();
					$wp_meta_boxes[$post->post_type]['advanced'] 		= array();
				}
				
				$layer_type = $this->get_layer_type($post);	
				
				if( $layer_type->output != 'hosted-page' && $layer_type->output != 'downloadable' ){		

					remove_meta_box( 'css-librarydiv', 'cb-default-layer', 'side' );
					remove_meta_box( 'js-librarydiv', 'cb-default-layer', 'side' );
					remove_meta_box( 'font-librarydiv', 'cb-default-layer', 'side' );

					if( $layer_type->output == 'image' ){
						
						remove_meta_box( 'element-librarydiv', 'cb-default-layer', 'side' );
					}
				}
				
				$this->parent->admin->add_meta_boxes($fields);
			}
			elseif( $post->post_type == 'user-layer' || $this->is_local ){
				
				$this->parent->admin->add_meta_box (
				
					'default_layer_id',
					__( 'Default Layer', 'live-template-editor-client' ), 
					array($post->post_type),
					'advanced'
				);
				
				if( !empty($_REQUEST['post']) ){
					
					if( $this->is_local ){

						if( $this->defaultId < 1 ){
							
							return;
						}
					}
					else{
						
						$this->parent->admin->add_meta_box (
							
							'layer-settings',
							__( 'Template Settings', 'live-template-editor-client' ), 
							array($post->post_type),
							'advanced'
						);				
					}
					
					$this->parent->admin->add_meta_box (
						
						'layer-content',
						__( 'Template HTML', 'live-template-editor-client' ), 
						array($post->post_type),
						'advanced'
					); 				
					
					$this->parent->admin->add_meta_box (
						
						'layer-css',
						__( 'Template CSS', 'live-template-editor-client' ), 
						array($post->post_type),
						'advanced'
					);
					
					$this->parent->admin->add_meta_box (
						
						'layer-js',
						__( 'Template Javascript', 'live-template-editor-client' ), 
						array($post->post_type),
						'advanced'
					);
				}
			}
		});
		
		// default layer
		
		add_filter('manage_cb-default-layer_posts_columns', array( $this, 'set_default_layer_columns'));
		add_action('manage_cb-default-layer_posts_custom_column', array( $this, 'add_default_layer_column_content'), 10, 2);
		
		// account option fields
		
		add_action('account-option_add_form_fields', array( $this, 'add_layer_fields' ) );
		add_action('account-option_edit_form_fields', array( $this, 'add_edit_layer_fields' ) );	
	
		add_filter('manage_edit-account-option_columns', array( $this, 'set_account_option_columns' ) );
		add_filter('manage_account-option_custom_column', array( $this, 'add_layer_column_content' ),10,3);			
	
		add_action('create_account-option', array( $this, 'save_layer_taxonomy_fields' ) );
		add_action('edit_account-option', array( $this, 'save_layer_taxonomy_fields' ) );	
		
		// layer type fields
		
		add_action('layer-type_add_form_fields', array( $this, 'add_layer_fields' ) );
		add_action('layer-type_edit_form_fields', array( $this, 'add_edit_layer_fields' ) );
	
		add_filter('manage_edit-layer-type_columns', array( $this, 'set_layer_type_columns' ) );
		add_filter('manage_layer-type_custom_column', array( $this, 'add_layer_column_content' ),10,3);		
		
		add_action('create_layer-type', array( $this, 'save_layer_taxonomy_fields' ) );
		add_action('edit_layer-type', array( $this, 'save_layer_taxonomy_fields' ) );	
		
		// layer range fields
		
		add_action('layer-range_add_form_fields', array( $this, 'add_layer_fields' ) );
		add_action('layer-range_edit_form_fields', array( $this, 'add_edit_layer_fields' ) );
	
		add_filter('manage_edit-layer-range_columns', array( $this, 'set_layer_range_columns' ) );
		add_filter('manage_layer-range_custom_column', array( $this, 'add_layer_column_content' ),10,3);
	
		add_action('create_layer-range', array( $this, 'save_layer_taxonomy_fields' ) );
		add_action('edit_layer-range', array( $this, 'save_layer_taxonomy_fields' ) );			
		
		// css library fields
		
		add_action('css-library_edit_form_fields', array( $this, 'get_css_library_fields' ) );	
		add_action('create_css-library', array( $this, 'save_library_fields' ) );
		add_action('edit_css-library', array( $this, 'save_library_fields' ) );	
	
		// js library fields
		
		add_action('js-library_edit_form_fields', array( $this, 'get_js_library_fields' ) );	
		add_action('create_js-library', array( $this, 'save_library_fields' ) );
		add_action('edit_js-library', array( $this, 'save_library_fields' ) );	
		
		// font library fields
		
		add_action('font-library_edit_form_fields', array( $this, 'get_font_library_fields' ) );		
		add_action('create_font-library', array( $this, 'save_library_fields' ) );
		add_action('edit_font-library', array( $this, 'save_library_fields' ) );			
		
		// init
		
		add_filter('init', array( $this, 'init_layer' ));
		
		add_filter('admin_init', array( $this, 'init_layer_backend' ));
		
		add_action('wp_loaded', array($this,'get_layer_types'));
		add_action('wp_loaded', array($this,'get_layer_ranges'));
		add_action('wp_loaded', array($this,'get_account_options'));
		add_action('wp_loaded', array($this,'get_js_libraries'));
		add_action('wp_loaded', array($this,'get_css_libraries'));
		add_action('wp_loaded', array($this,'get_font_libraries'));
		//add_action('wp_loaded', array($this,'get_default_layers'));
		
		add_action( 'save_post', array($this,'upload_static_contents'), 10, 3 );
		
		add_action( 'before_delete_post', array($this,'delete_static_contents'), 10, 3 );
	
		add_action( 'ltple_layer_loaded', array($this,'output_static_layer') );
		
		add_action( 'wp_head', array( $this, 'get_hosted_page_header') );
		
		add_filter( 'the_content', array($this,'get_hosted_page_content'),99999 );
	}
	
	public function is_local_page($post){
		
		if( is_numeric($post) ){
			
			$post = get_post($post);
		}
		
		if( !empty($post) && $post->post_type != 'cb-default-layer' && $post->post_type != 'user-layer' && !empty($this->parent->settings->options->postTypes) && in_array( $post->post_type, $this->parent->settings->options->postTypes ) ){
			
			return true;
		}
		
		return false;
	}
	
	public function get_default_layer_fields($post=null){
		
		if( empty($this->defaultFields) ){
		
			//get post
			
			if( empty($post) ){
				
				$post_id = get_the_ID();
				
				$post = get_post($post_id);
			}
			
			//get layer types
			
			$layer_types=[];
			
			foreach($this->types as $term){
				
				$layer_types[$term->slug]=$term->name;
			}

			//get current layer type
			
			$layer_type = $this->get_layer_type($post);			
			
			$this->defaultFields[]=array(
			
				"metabox" =>
				
					array(
				
						'name' 		=> 'tagsdiv-layer-type',
						'title' 	=> __( 'Template Type', 'live-template-editor-client' ), 
						'screen'	=> array('cb-default-layer'),
						'context' 	=> ( !empty($layer_type->output) ? 'side' : 'advanced' ),
						'taxonomy'	=> 'layer-type',
						'frontend'	=> false,
					),
					
					'id'			=> "new-tag-layer-type",
					'name'			=> 'tax_input[layer-type]',
					'label'			=> "",
					'type'			=> 'select',
					'options'		=> $layer_types,
					'callback' 		=> array($this,'get_layer_type_slug'),
					'description'	=> ''
			);
			
			//get current layer range
			
			$layer_range = $this->get_layer_range($post);	
			
			$this->defaultFields[]=array(
			
				"metabox" =>
				
					array( 
				
						'name' 		=> 'layer-rangediv',
						'title' 	=> __( 'Template Range', 'live-template-editor-client' ), 
						'screen'	=> array('cb-default-layer'),
						'context' 	=> 'side',
						'taxonomy'	=> 'layer-range',
						'frontend'	=> false,
					),
					
					'type'		=> 'dropdown_categories',
					'id'		=> 'layer-range',
					'name'		=> 'tax_input[layer-range][]',
					'label'		=> '',
					'taxonomy'	=> 'layer-range',
					'callback' 	=> array($this,'get_layer_range_id'),
					'description'=>''
			);
			
			if( !empty($layer_type->output) ){
				
				// json object
				/*
				$this->defaultFields[]=array(
				
					"metabox" =>
					
						array(
					
							'name' 		=> 'metabox_1',
							'title' 	=> __( 'Template JSON', 'live-template-editor-client' ), 
							'screen'	=> array('cb-default-layer'),
							'context' 	=> 'advanced',
							'add_new'	=> false
						),					

						'id'=>"pageDef",
						'label'=>"",
						'type'=>'textarea',
						'placeholder'=>"JSON object",
						'description'=>'
						
							<table class="widefat fixed striped" cellspacing="0">
								<thead>
								
									<tr>
									
										<th>option</th>
										<th>description</th>
										<th>default</th>
										<th>possible values</th>
										
									</tr>
									
								</thead>
								<tbody>
								
									<tr>
									
										<td><strong>name</strong></td>
										<td>ID of the element</td>
										<td>null</td>
										<td>String</td>
										
									</tr>
									<tr>
									
										<td><strong>iconClass</strong></td>
										<td>Class of the icon before the element name</td>
										<td>glyphicon glyphicon-plus</td>
										<td>String</td>
										
									</tr>							
									<tr>
									
										<td><strong>props</strong></td>
										<td>List of editable CSS propertises</td>
										<td>null</td>
										<td>Array</td>
									</tr>
									
									<tr>
										<td><strong>labels</strong></td>
										<td>Labels of the editable CSS propertises</td>
										<td>null</td>
										<td>Array</td>
									</tr>
									
									<tr>
									
										<td><strong>editorsConfig</strong></td>
										<td>Configuration of some editable CSS propertise surch as background-image or image source</td>
										<td>null</td>
										<td>Object{"prop":{"urls":Object}}</td>
										
									</tr>
									
									<tr>
									
										<td><strong>draggable</strong></td>
										<td>Is the element draggable inside the preview</td>
										<td>false</td>
										<td>String</td>
										
									</tr>
									
									<tr>
									
										<td><strong>contenteditable</strong></td>
										<td>Is the element content editable</td>
										<td>true</td>
										<td>String</td>
										
									</tr>
									
								</tbody>
								
							</table>'
				);
				*/
				
				if( $layer_type->output != 'image' ){
				
					// get layer content
					
					$this->defaultFields[]=array(
					
						"metabox" =>
						
							array(
						
								'name' 		=> 'layer-content',
								'title' 	=> __( 'Template HTML', 'live-template-editor-client' ), 
								'screen'	=> array('cb-default-layer'),
								'context' 	=> 'advanced',
								'add_new'	=> false,
							),
							
							'id'			=> "layerContent",
							'label'			=> "",
							'type'			=> 'textarea',
							'placeholder'	=> "HTML content",
							//'description'	=> '<i>without '.htmlentities('<style></style>').'</i>'
					);

					$this->defaultFields[]=array(
					
						"metabox" =>
						
							array(
						
								'name' 		=> 'layer-margin',
								'title' 	=> __( 'Editor Margin', 'live-template-editor-client' ), 
								'screen'	=> array('cb-default-layer'),
								'context' 	=> 'side',
								'add_new'	=> false,
							),
							
							'id'			=> "layerMargin",
							'label'			=> "",
							'type'			=> 'text',
							'placeholder'	=> '0px auto',
							'default'		=> '',
							'description'	=> ''
					);				

					if( $layer_type->output != 'inline-css' ){
						
						// get layer css
						
						$this->defaultFields[]=array(
						
							"metabox" =>
							
								array(
							
									'name' 		=> 'layer-css',
									'title' 	=> __( 'Template CSS', 'live-template-editor-client' ), 
									'screen'	=> array('cb-default-layer'),
									'context' 	=> 'advanced',
									'add_new'	=> false,
								),
								
								'id'			=> "layerCss",
								'label'			=> "",
								'type'			=> 'textarea',
								'stripcslashes'	=> false,
								'placeholder'	=> "Internal CSS style sheet",
								'description'	=> '<i>without '.htmlentities('<style></style>').'</i>'
						);
					}
					
					if( $layer_type->output == 'hosted-page' || $layer_type->output == 'downloadable' ){		
						
						$this->defaultFields[]=array(
						
							"metabox" =>
							
								array(
							
									'name' 		=> 'layer-json',
									'title' 	=> __( 'Template JSON', 'live-template-editor-client' ), 
									'screen'	=> array('cb-default-layer'),
									'context' 	=> 'advanced'
								),
								
								'id'			=> "layerJson",
								'label'			=> "",
								'type'			=> 'textarea',
								'placeholder'	=> "JSON Data",
								'description'	=> '<i>without '.htmlentities('<script></script>').'</i>'
						);						
						
						$this->defaultFields[]=array(
						
							"metabox" =>
							
								array(
							
									'name' 		=> 'layer-js',
									'title' 	=> __( 'Template Javascript', 'live-template-editor-client' ), 
									'screen'	=> array('cb-default-layer'),
									'context' 	=> 'advanced'
								),
								
								'id'			=> "layerJs",
								'label'			=> "",
								'type'			=> 'textarea',
								'placeholder'	=> "Additional Javascript",
								'description'	=> '<i>without '.htmlentities('<script></script>').'</i>'
						);
						
						
						$this->defaultFields[]=array(
						
							"metabox" =>
							
								array(
							
									'name' 		=> 'layer-meta',
									'title' 	=> __( 'Template Meta Data', 'live-template-editor-client' ), 
									'screen'	=> array('cb-default-layer'),
									'context' 	=> 'advanced'
								),
								
								'id'			=> "layerMeta",
								'label'			=> "",
								'type'			=> 'textarea',
								'placeholder'	=> "JSON",
								'description'	=> '<i>Additional Meta Data</i>'
						);	
						
						
						if( $layer_type->output == 'downloadable' ){
							
							$this->defaultFields[]=array(
							
								"metabox" =>
								
									array(
								
										'name' 		=> 'layer-static-url',
										'title' 	=> __( 'Template Static Content', 'live-template-editor-client' ), 
										'screen'	=> array('cb-default-layer'),
										'context' 	=> 'advanced'
									),
									
									'id'			=> "layerStaticTpl",
									'type'			=> 'file',
									'label'			=> '<b>Upload Archive</b>',
									'accept'		=> '.zip,.tar',
									'script'		=> 'jQuery(document).ready(function($){$(\'form#post\').attr(\'enctype\',\'multipart/form-data\');});',
									'placeholder'	=> "archive.zip",
									'style'			=> "padding:5px;margin: 15px 0 5px 0;",
									'description'	=> "Upload a static template (zip,tar)",
							);					
						}
					}
					
					$this->defaultFields[]=array(
					
						"metabox" =>
						
							array(
						
								'name' 		=> 'layer-elements',
								'title' 	=> __( 'Template Elements', 'live-template-editor-client' ), 
								'screen'	=> array('cb-default-layer'),
								'context' 	=> 'advanced'
							),
							
							'id'			=> "layerElements",
							'name'			=> "layerElements",
							'type'			=> 'element',
							'description'	=> '',
							
					);
				}
				else{
					
					$this->defaultFields[]=array(
					
						"metabox" =>
						
							array(
						
								'name' 		=> 'layer-image-url',
								'title' 	=> __( 'Image Template', 'live-template-editor-client' ), 
								'screen'	=> array('cb-default-layer'),
								'context' 	=> 'advanced'
							),
							
							'id'			=> "layerImageTpl",
							'type'			=> 'file',
							'label'			=> '<b>Upload File</b>',
							'accept'		=> '.psd,.xfc,.sketch',
							'script'		=> 'jQuery(document).ready(function($){$(\'form#post\').attr(\'enctype\',\'multipart/form-data\');});',
							'placeholder'	=> "image.psd",
							'style'			=> "padding:5px;margin: 15px 0 5px 0;",
							'description'	=> "Upload an image template ( Photoshop, GIMP, Sketch )",
					);						
				}
				
				if( $layer_type->output == 'inline-css' || $layer_type->output == 'external-css' ){
							
					$this->defaultFields[]=array( 
					
						"metabox" =>
						
							array(
						
								'name' 		=> 'layer-form',
								'title' 	=> __( 'Template Form', 'live-template-editor-client' ), 
								'screen'	=> array('cb-default-layer'),
								'context' 	=> 'side',
								'frontend' 	=> false,
							),
							
							'id'			=> "layerForm",
							'label'			=> "",
							'type'			=> 'radio',
							'options'		=> array(
							
								'none'		=> 'None',
								'importer'	=> 'Importer',
								//'scraper'	=> 'Scraper',
							),
							'inline'		=> false,
							'description'	=> ''
					);				
				}
			}

			$this->defaultFields[]=array( 
			
				"metabox" =>
				
					array(
				
						'name' 		=> 'layer-visibility',
						'title' 	=> __( 'Template Visibility', 'live-template-editor-client' ), 
						'screen'	=> array('cb-default-layer'),
						'context' 	=> 'side',
						'frontend' 	=> false,
					),
						
					'id'			=> "layerVisibility",
					'label'			=> "",
					'type'			=> 'radio',
					'options'		=> array(
					
						'subscriber'	=> 'Subscriber',
						'assigned'		=> 'Assigned (tailored)',
						'registered'	=> 'Registered',
						'anyone'		=> 'Anyone',
					),
					'inline'		=> false,
					'description'	=> ''
			);
			
			/*
			$this->defaultFields[]=array(
			
				"metabox" =>
				
					array(
				
						'name' 		=> 'layer-options',
						'title' 	=> __( 'Template Options', 'live-template-editor-client' ), 
						'screen'	=> array('cb-default-layer'),
						'context' 	=> 'side',
						'add_new'	=> false,
					),
					
					'id'		=>"layerOptions",
					'label'		=>"",
					'type'		=>'checkbox_multi',
					'options'	=>array(
					
						'line-break'	=> 'Line break (Enter)',
						'wrap-text'		=> 'Auto wrap text',
					
					),
					'checked'	=>array('margin-top'),
					'description'=>''
			);
			*/
			
			do_action('ltple_default_layer_fields');
			
		}
		
		return $this->defaultFields;
	}
	
	public function get_user_layer_fields(){
		
		if( empty($this->userFields) ){
		
			$this->userFields[]=array(
			
				"metabox" =>
				
					array('name'=>"layer-content"),
					'id'			=> "layerContent",
					'label'			=> "",
					'type'			=> 'textarea',
					'placeholder'	=> "HTML content",
					//'description'	=> '<i>without '.htmlentities('<style></style>').'</i>'
			);
		
			$this->userFields[]=array(
			
				"metabox" =>
				
					array('name'=>"layer-css"),
					'id'			=> "layerCss",
					'label'			=> "",
					'type'			=> 'textarea',
					'stripcslashes'	=> false,
					'placeholder'	=> "Internal CSS style sheet",
					'description'	=> '<i>without '.htmlentities('<style></style>').'</i>'
			);
			
			$this->userFields[]=array(
			
				"metabox" =>
				
					array('name'=>"layer-js"),
					'id'			=> "layerJs",
					'label'			=> "",
					'type'			=> 'textarea',
					'placeholder'	=> "Additional Javascript",
					'description'	=> '<i>without '.htmlentities('<script></script>').'</i>'
			);

			$this->userFields[]=array(
				"metabox" =>
				
					array('name'=>"default_layer_id"),
					'id'			=> "defaultLayerId",
					'label'			=> "Default Template ID",
					'type'			=> 'edit_layer',
					'placeholder'	=> "",
					'description'	=> '',
					'disabled'		=> true,
			);
			
			$this->userFields[]=array(
			
				"metabox" =>
				
					array('name'=>"layer-settings"),
					'id'			=> "layerSettings",
					'label'			=> "",
					'type'			=> 'textarea',
					'placeholder'	=> "JSON content",
					//'description'	=> '<i>without '.htmlentities('<style></style>').'</i>'
			);
			
			/*
			$this->userFields[]=array(
			
				"metabox" =>
				
					array('name'=>"layer-embedded"),
					'id'			=> "layerEmbedded",
					'label'			=> "",
					'type'			=> 'text',
					'placeholder'	=> "http://",
					'disabled'		=> true,
			);	
			*/			
		
			do_action('ltple_user_layer_fields');
		
		}
		
		return $this->userFields;
	}
	
	public function get_layer_outputs(){
		
		if( empty($this->outputs) ){
		
			$this->outputs = array(
					
				'inline-css'	=>'HTML',
				'external-css'	=>'HTML + CSS',
				'hosted-page'	=>'Hosted',
				'downloadable'	=>'Downloadable',
				'canvas'		=>'Collage',
				'image'			=>'Image',
			);
			
			do_action('ltple_layer_outputs');
		}
		
		return $this->outputs;
	}
	
	public function get_gallery_sections(){
		
		if( is_null($this->sections) ){
			
			$this->sections = array();
			
			if( $sections = $this->get_terms( 'gallery-section', array())){
				
				foreach( $sections as $section ){
					
					$this->sections[$section->term_id] = $section;
				}
			}
		}

		return $this->sections;
	}	
	
	public function get_layer_types(){
		
		if( is_null($this->types) ){
		
			$this->types = $this->get_terms( 'layer-type', array(
					
				'emails'  			=> 'Emails',
				'memes'  			=> 'Memes',
				'pricing-tables'	=> 'Pricing Tables',
				'sandbox' => array(
				
					'name' 		=> 'Sandbox',
					'options' 	=> array(
					
						'visibility'	=> 'admin',
					),
				),
			));	
		}

		return $this->types;
	}
	
	public function get_layer_range_id($post){
		
		$layer_range_id = 0;
		
		$layer_range = $this->get_layer_range($post);
		
		if( !empty($layer_range->term_id) ){
			
			$layer_range_id = $layer_range->term_id;
		}
		
		return $layer_range_id;
	}
	
	public function get_layer_range($post){
		
		$term = null;
		
		if( is_numeric($post) ){
			
			$post = get_post($post);
		}

		if( !empty($post->post_type) ){
			
			if( $post->post_type == 'user-layer' ){
					
				// get default layer id
				
				$default_id = intval(get_post_meta( $post->ID, 'defaultLayerId', true ));
				
				$post = get_post($default_id);
			}
			
			if( !is_null($post) && $post->post_type == 'cb-default-layer' ){
				
				$terms = wp_get_post_terms($post->ID,'layer-range');
			
				if( !empty($terms[0]) ){
					
					$term = $terms[0];
				}				
			}
		}
		
		return $term;
	}	
	
	public function get_layer_ranges(){
		
		if( is_null($this->ranges) ){
		
			$this->ranges = $this->get_terms( 'layer-range',array(
					
				'demo' => array(
				
					'name' 		=> 'Demo',
					'options' 	=> array(
					
						'price_amount'	=> 0,
						'price_period' 	=> 'month',
						'storage_amount'=> 0,
						'storage_unit' 	=> 'templates',
					),
				),
				'addons' => array(
				
					'name' 		=> 'Addons',
					'options' 	=> array(
					
						'price_amount'	=> 0,
						'price_period' 	=> 'month',
						'storage_amount'=> 0,
						'storage_unit' 	=> 'templates',
					),
				),
			));
		}
		
		return $this->ranges;
	}
	
	public function get_account_options(){

		$this->accountOptions = $this->get_terms( 'account-option', array(

			'1-template-storage' => array(
			
				'name' 		=> '+1 template storage',
				'options' 	=> array(
				
					'price_amount'	=> 0,
					'price_period' 	=> 'month',
					'storage_amount'=> 1,
					'storage_unit' 	=> 'templates',
				),
			),
			'5-template-storage' => array(
			
				'name' 		=> '+5 template storage',
				'options' 	=> array(
				
					'price_amount'	=> 0,
					'price_period' 	=> 'month',
					'storage_amount'=> 5,
					'storage_unit' 	=> 'templates',
				),
			),
			'10-template-storage' => array(
			
				'name' 		=> '+10 template storage',
				'options' 	=> array(
				
					'price_amount'	=> 0,
					'price_period' 	=> 'month',
					'storage_amount'=> 10,
					'storage_unit' 	=> 'templates',
				),
			),	
			'tailored-page-fee' => array(
			
				'name' 		=> 'Tailored Page Fee',
				'options' 	=> array(
				
					'price_amount'	=> 200,
					'price_period' 	=> 'once',
					'storage_amount'=> 0,
					'storage_unit' 	=> 'templates',
				),
			),
			'tailored-multi-page-fee' => array(
			
				'name' 		=> 'Tailored Multi-Page Fee',
				'options' 	=> array(
				
					'price_amount'	=> 400,
					'price_period' 	=> 'once',
					'storage_amount'=> 0,
					'storage_unit' 	=> 'templates',
				),
			),			
			'seo-basic' => array(
			
				'name' 		=> 'SEO Basic',
				'options' 	=> array(
				
					'price_amount'	=> 100,
					'price_period' 	=> 'month',
					'storage_amount'=> 0,
					'storage_unit' 	=> 'templates',
				),
			),
		));
	}
	
	public function get_css_libraries(){
		
		$this->cssLibraries = $this->get_terms( 'css-library', array(
			
			'jquery-ui-1-12-1' => array(
			
				'name' 		=> 'Jquery UI 1.12.1',
				'options' 	=> array(
				
					'css_url'	 => 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css',
					'css_content' => '',
				),
			),			
			'bootstrap-3-3-7' => array(
			
				'name' 		=> 'Bootstrap 3.3.7',
				'options' 	=> array(
				
					'css_url'	 => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
					'css_content' => '',
				),
				'children'	=> array(
				
					'material-kit-1-1-0' => array(
					
						'name' 		=> 'Material Kit 1.1.0',
						'options' 	=> array(
						
							'css_url'	 => $this->parent->assets_url . 'css/material-kit.css',
							'css_content' => '.card .card-image{height:auto;}',
						),
					),				
				),
			),
			'font-awesome-4-7-0' => array(
			
				'name' 		=> 'Font Awesome 4.7.0',
				'options' 	=> array(
				
					'css_url'	 => 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
					'css_content' => '',
				),
			),
			'elementor-2-2-7' => array(
			
				'name' 		=> 'Elementor 2.2.7',
				'options' 	=> array(
				
					'css_url'	 	=> 'https://ltple.recuweb.com/c/p/live-template-editor-resources/assets/elementor/2.2.7/frontend.min.css',
					'css_content' 	=> '',
				),
			),			
			'animate-3-5-2' => array(
			
				'name' 		=> 'Animate 3.5.2',
				'options' 	=> array(
				
					'css_url'	  => 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css',
					'css_content' => '',
				),
			),
		));
	}
	
	public function get_js_libraries(){

		$this->jsLibraries = $this->get_terms( 'js-library', array(
			'jquery-3-1-1' => array(
			
				'name' 		=> 'Jquery 3.1.1',
				'options' 	=> array(
				
					'js_url'	 => 'https://code.jquery.com/jquery-3.1.1.min.js',
					'js_content' => '',
				),
				'children'	=> array(
				
					'jquery-ui-1-12-1' => array(
					
						'name' 		=> 'Jquery UI 1.12.1',
						'options' 	=> array(

							'js_url'		=> 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
							'js_content'	=> '
								<script>
								;(function($){
									$(document).ready(function(){						
										if($("#scrapeLayer").length){
											$("#scrapeLayer").dialog({autoOpen: true});
										}								
									});
								})(jQuery);
								</script>
							',
						),
					),				
					'bootstrap-3-3-7' => array(
					
						'name' 		=> 'Bootstrap 3.3.7',
						'options' 	=> array(

							'js_url'		=> 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
							'js_content'	=> '
								<script>
								;(function($){
									$(document).ready(function(){							
										$(\'.modal\').appendTo("body");
										$(\'[data-slide-to]\').on(\'click\',function(e){
											e.preventDefault();
											if( typeof $(this).attr(\'data-target\') !== typeof undefined ){
												var carouselId 	= $(this).attr(\'data-target\');
											}
											else{
												var carouselId 	= $(this).attr("href");
											}
											var slideTo 	= parseInt( $(this).attr(\'data-slide-to\') );
											$(carouselId).carousel(slideTo);
											return false;
										});
										
										$(\'[data-slide]\').on(\'click\',function(e){
											e.preventDefault();
											if( typeof $(this).attr(\'data-target\') !== typeof undefined ){
												var carouselId 	= $(this).attr(\'data-target\');
											}
											else{
												var carouselId 	= $(this).attr("href");
											}
											var slideTo 	= $(this).attr(\'data-slide\');
											$(carouselId).carousel(slideTo);
											return false;
										});
									});
								})(jQuery);
								</script>
							',
						),
					),				
				)
			),
		));
	}

	public function get_font_libraries(){
		
		$this->cssLibraries = $this->get_terms( 'font-library', array(
			
			'material-icons' => array(
			
				'name' 		=> 'Material Icons',
				'options' 	=> array(
				
					'font_url'	 => 'https://fonts.googleapis.com/css?family=Material+Icons',
				),
			),
			'roboto' => array(
			
				'name' 		=> 'Roboto',
				'options' 	=> array(
				
					'font_url'	 => 'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700',
				),
			),
		));
	}
	
	public function get_layer_type_slug($post){
		
		$layer_type_slug = '';
		
		$layer_type = $this->get_layer_type($post);
		
		if( !empty($layer_type->slug) ){
			
			$layer_type_slug = $layer_type->slug;
		}
		
		return $layer_type_slug;
	}
	
	public function get_layer_type($post){
		
		$term = null;
		
		if( is_numeric($post) ){
			
			$post = get_post($post);
		}

		if( !empty($post->post_type) ){
			
			if( $post->post_type == 'user-layer' ){
					
				// get default layer id
				
				$default_id = intval(get_post_meta( $post->ID, 'defaultLayerId', true ));
				
				$post = get_post($default_id);
			}
			
			if( !is_null($post) && $post->post_type == 'cb-default-layer' ){
				
				$terms = wp_get_post_terms($post->ID,'layer-type');
			
				if( !empty($terms[0]) ){
					
					$term = $terms[0];

					if( !$term->output = get_term_meta( $term->term_id, 'output', true ) ){
						
						$term->output = 'inline-css';
					}
				}				
			}
		}
		
		if( !isset($term->output) ){
			
			$term = new stdClass();
			
			$term->output = '';
		}
		
		return $term;
	}
	
	public function get_thumbnail_url($post){
		
		$post_id = 0;
		
		if( is_numeric($post) ){
		
			$post_id = intval($post);
		}
		elseif( is_object($post) && !empty($post->ID) ){
			
			$post_id = $post->ID;
		}
		
		if( $post_id > 0 ){
			
			if( $image_id = get_post_thumbnail_id( $post_id ) ){
				
				if ($src = wp_get_attachment_image_src( $image_id, 'medium_large' )){
					
					return $src[0];
				}
			}
		}

		return $this->parent->assets_url . 'images/default_item.png';
	}
	
	public function init_layer_backend(){
		
		add_filter('show_user_profile', array( $this, 'get_user_layers' ),2,10 );
		add_action('edit_user_profile', array( $this, 'get_user_layers' ) );
		
		add_filter('cb-default-layer_custom_fields', array( $this, 'get_default_layer_fields' ));
		
		add_filter('user-layer_custom_fields', array( $this, 'get_user_layer_fields' ));

		if( !empty($this->parent->settings->options->postTypes) ){
		
			foreach( $this->parent->settings->options->postTypes as $post_type ){
				
				add_filter( $post_type . '_custom_fields', array( $this, 'get_user_layer_fields' ));
			}
		}
	}
		
	public function get_embedded_url(){
		
		$embedded_url = '';
		
		if(isset($_GET['le'])){
			
			$embedded_url = sanitize_text_field($_GET['le']);
		}
		elseif(isset($_POST['postEmbedded'])){
			
			$embedded_url = sanitize_text_field($_POST['postEmbedded']);
		}

		return 	$embedded_url;
	}
	
	public function get_embedded_layer($embedded_url){
		
		$embedded = parse_url($embedded_url);

		parse_str($embedded['query'],$query);

		$embedded = array_merge($embedded,$query);

		foreach($embedded as $i => $e){
			
			if(is_numeric($e)){
				
				$embedded[$i]=intval($e);
			}
		}			
		
		// get url
		
		$embedded['url'] = $embedded_url;
		
		// get title
		
		$embedded_title = '';
		
		if(!empty($_GET['title'])){
			
			$embedded_title = sanitize_text_field($_GET['title']);
		}
		
		$embedded['title'] = $embedded_title;	

		return $embedded;
	}
	
	public function set_uri(){
		
		if( is_admin() ){
			
			if( !empty($_REQUEST['post']) && intval($_REQUEST['post']) > 0 ){
				
				$this->uri = intval($_REQUEST['post']);
			}				
		}
		else{
			
			if( isset($_GET['uri']) ){
				
				$this->uri = intval($_GET['uri']);
			}
			elseif( isset($_REQUEST['t']) ){
				
				$this->uri = intval($_REQUEST['t']);
			}			
			elseif( strpos($this->parent->urls->current, $this->parent->urls->editor) === false ){
				
				$this->uri = url_to_postid($this->parent->urls->current);
			}
		}
	}
	
	public function init_layer(){

		$this->dirUrl = ( defined('LTPLE_LAYER_URL') ? LTPLE_LAYER_URL : $this->parent->urls->home . '/t/');

		$this->dirPath = ( defined('LTPLE_LAYER_DIR') ? LTPLE_LAYER_DIR : ABSPATH . 't/');	

		// get layer key
	
		if(isset($_GET['lk'])){
			
			$this->key = sanitize_text_field($_GET['lk']);
		}

		// get embedded layer
		
		$embedded_url = $this->get_embedded_url();
		
		if( !empty($embedded_url) ){
			
			$this->embedded = $this->get_embedded_layer( $embedded_url );
		}				
		
		// set layer
		
		$this->set_uri();

		if( $this->uri > 0 ){

			//set layer data
			
			$this->set_layer($this->uri);
			
			// remove local page support
			
			if( is_admin() && $this->is_local && $this->defaultId > 0 ){
				
				remove_post_type_support($this->type,'editor');
				remove_post_type_support($this->type,'revisions');
			}			
		}
	}
	
	public function set_layer($uri){
	
		if( $q = get_post($uri) ){
			
			if( $q->post_status == 'publish' || $q->post_status == 'draft' ){
				
				$this->is_local = $this->is_local_page($q);
				
				if( $q->post_type == 'cb-default-layer' || $q->post_type == 'user-layer' || $this->is_local ){
				
					$this->id 		= $q->ID;
					$this->type 	= $q->post_type;
					$this->slug 	= $q->post_name;
					$this->title 	= $q->post_title;
		
					if( $this->type == 'user-layer' ){
					
						$this->defaultId = intval(get_post_meta( $this->id, 'defaultLayerId', true ));
					}
					elseif( in_array( $q->post_type, $this->parent->settings->options->postTypes ) ){
						
						$this->defaultId = intval(get_post_meta( $q->ID, 'defaultLayerId', true));
						$this->form 	 = get_post_meta( $this->defaultId, 'layerForm', true );							
					}
					else{
						
						$this->defaultId = $this->id;
						$this->form 	 = get_post_meta( $this->defaultId, 'layerForm', true );
					}
					
					// get default layer type
					
					$this->defaultLayerType = $this->get_layer_type($this->defaultId);
					
					//get layer output

					$this->layerOutput = $this->defaultLayerType->output;
											
					if( $this->layerOutput == 'image' ){
						
						// get layer image template
						
						$this->layerImageTpl = array();
						
						$attachments = $this->get_layer_attachments($this->id);
						
						if( empty($attachments) && $this->id != $this->defaultId ){
							
							$attachments = $this->get_layer_attachments($this->defaultId);
						}
						
						if( !empty($attachments) ){

							$this->layerImageTpl = reset($attachments);
						}
					}
					else{
						
						//get layer image proxy
						
						$this->layerImgProxy = $this->parent->request->proto . $_SERVER['HTTP_HOST'].'/image-proxy.php?'.time().'&url=';

						// get layer Content
						
						$this->layerContent = get_post_meta( $this->id, 'layerContent', true );

						if( $this->layerContent == '' && $this->id != $this->defaultId ){
							
							$this->layerContent = get_post_meta( $this->defaultId, 'layerContent', true );
						}
						
						// get default css

						$this->defaultCss = get_post_meta( $this->defaultId, 'layerCss', true );
						
						// get layer css

						$this->layerCss = get_post_meta( $this->id, 'layerCss', true );
						
						if( $this->layerCss == '' && $this->id != $this->defaultId ){
							
							if( $this->layerOutput != 'hosted-page' ){
							
								$this->layerCss = $this->defaultCss;
							}
						}

						// get default js

						$this->defaultJs = get_post_meta( $this->defaultId, 'layerJs', true );
							
						// get default json

						$this->defaultJson = get_post_meta( $this->defaultId, 'layerJson', true );
							
						// get layer js
						
						$this->layerJs = get_post_meta( $this->id, 'layerJs', true );
						
						if( $this->layerJs == '' && $this->id != $this->defaultId ){
							
							$this->layerJs = $this->defaultJs;
						}
						
						// get default elements

						$this->defaultElements = get_post_meta( $this->defaultId, 'layerElements', true );
												
						// get layer meta
						
						$this->layerMeta = get_post_meta( $this->id, 'layerMeta', true );
						
						if( $this->layerMeta == '' && $this->id != $this->defaultId ){
							
							$this->layerMeta = get_post_meta( $this->defaultId, 'layerMeta', true );
						}

						if(!empty($this->layerMeta)){
							
							$this->layerMeta = json_decode($this->layerMeta,true);
						}								
											
						// get layer Margin
						
						$this->layerMargin 	 = get_post_meta( $this->defaultId, 'layerMargin', true );
						
						if( empty($this->layerMargin) ){

							$this->layerMargin = '-120px 0px -20px 0px';
						}
						
						// get layer Min Width

						$this->layerMinWidth = get_post_meta( $this->defaultId, 'layerMinWidth', true );
						
						if( empty($this->layerMinWidth) ){
							
							$this->layerMinWidth = '1000px';
						}									
						
						//get page def
						
						$this->pageDef = get_post_meta( $this->defaultId, 'pageDef', true );
						
						//get default static path
						
						$this->defaultStaticPath = $this->get_static_path($this->defaultId,$this->defaultId);
							
						//get default static css path
						
						$this->defaultStaticCssPath = $this->get_static_asset_path($this->id,'css','default_style');
							
						//get default static js path
						
						$this->defaultStaticJsPath = $this->get_static_asset_path($this->id,'js','default_script');

						//get default static dir url
						
						$this->defaultStaticDirUrl = $this->get_static_dir_url($this->defaultId,$this->layerOutput);					
						
						//get default static url
						
						$this->defaultStaticUrl = $this->get_static_url($this->defaultId,$this->defaultId,$this->layerOutput);
						
						//get default static css url
						
						$this->defaultStaticCssUrl = ( file_exists($this->defaultStaticCssPath) ? $this->get_static_asset_url($this->id,'css','default_style') : '' );

						//get default static js url
						
						$this->defaultStaticJsUrl = ( file_exists($this->defaultStaticJsPath) ? $this->get_static_asset_url($this->id,'js','default_script') : '' );					
						
						//get default static dir
						
						$this->defaultStaticDir = $this->get_static_dir($this->defaultId);

						//get layer static path
						
						$this->layerStaticPath = $this->get_static_path($this->id,$this->defaultId);
							
						//get layer static css path
						
						$this->layerStaticCssPath = $this->get_static_asset_path($this->id,'css','custom_style');
							
						//get layer static js path
						
						$this->layerStaticJsPath = $this->get_static_asset_path($this->id,'js','custom_script');
						
						//get layer static url
						
						$this->layerStaticUrl = $this->get_static_url($this->id,$this->defaultId);
						
						//get layer static css url
						
						$this->layerStaticCssUrl = ( file_exists($this->layerStaticCssPath) ? $this->get_static_asset_url($this->id,'css','custom_style') : '' );

						//get layer static js url
						
						$this->layerStaticJsUrl = ( file_exists($this->layerStaticJsPath) ? $this->get_static_asset_url($this->id,'js','custom_script') : '' );						

						//get layer static dir
						
						$this->layerStaticDir = $this->get_static_dir($this->id);
									
						//get layer options
						
						$this->layerOptions = get_post_meta( $this->defaultId, 'layerOptions', true );
						
						//get layer settings
						
						$this->layerSettings = get_post_meta( $this->id, 'layerSettings', true );

						if( is_string($this->layerSettings) ){
							
							$this->layerSettings = json_decode($this->layerSettings,true);
						}
						
						//get layer embedded
						
						$this->layerEmbedded = get_post_meta( $this->id, 'layerEmbedded', true );	
						
						//get layer form
						
						$this->layerForm = get_post_meta( $this->defaultId, 'layerForm', true );
						
						//get css libraries

						$this->layerCssLibraries = wp_get_post_terms( $this->defaultId, 'css-library', array( 'orderby' => 'term_id' ) );

						//get js libraries
						
						$this->layerJsLibraries = wp_get_post_terms( $this->defaultId, 'js-library', array( 'orderby' => 'term_id' ) );								
						
						//get font libraries
						
						$this->layerFontLibraries = wp_get_post_terms( $this->defaultId, 'font-library', array( 'orderby' => 'term_id' ) );																			
						
						//get element libraries
						
						$this->layerHtmlLibraries = wp_get_post_terms( $this->defaultId, 'element-library', array( 'orderby' => 'term_id' ) );								
						
						//get layer image proxy

						$this->layerImgProxy = $this->parent->request->proto . $_SERVER['HTTP_HOST'].'/image-proxy.php?'.time().'&url=';
					
						if( $this->layerOutput == 'hosted-page' ){
							
							$this->parse_hosted_content();
						}
					}
				}
			}
		}
	}
	
	public function extract_css_urls( $str ){
		
		$urls = array( );
	 
		$url_pattern     = '(([^\\\\\'", \(\)]*(\\\\.)?)+)';
		
		$urlfunc_pattern = 'url\(\s*[\'"]?' . $url_pattern . '[\'"]?\s*\)';
		
		$pattern         = '/(' .
			 '(@import\s*[\'"]' . $url_pattern     . '[\'"])' .
			'|(@import\s*'      . $urlfunc_pattern . ')'      .
			'|('                . $urlfunc_pattern . ')'      .  ')/iu';
		
		if ( !preg_match_all( $pattern, $str, $matches ) )
			return $urls;
	 
		foreach ( $matches[11] as $match )
			if ( !empty($match) )
				$urls[] = 
					preg_replace( '/\\\\(.)/u', '\\1', $match );
	 
		return $urls;
	}

	public function parse_css_content($content,$prepend,$source='',$charset='utf-8'){
		
		// protect unicode numbers
		
		$content = str_replace('\\','\\\\',$content);
		
		// parse content
		
		include_once($this->parent->vendor . '/autoload.php');
		
		$cssSettings = Sabberworm\CSS\Settings::create();
		
		$cssSettings->withDefaultCharset($charset);
		
		$cssSettings->withMultibyteSupport(false); // use mb_* functions
		
		$cssParser = new Sabberworm\CSS\Parser($content,$cssSettings);
		
		$css = $cssParser->parse();
		
		// remove rules
		
		/*
		foreach( $css->getAllRuleSets() as $rule ) {
			
			$rule->removeRule('cursor');
		}
		*/
		
		foreach( $css->getAllValues() as $value ) {
			
			if( !empty($source) ){
				
				// replace relative path to absolute
			
				if( method_exists($value,'__toString') ) {
					
					$str = $value->__toString();
					
					if( strpos($str,'url(') !== false ){

						$urls = $this->extract_css_urls($str);
						
						if( !empty($urls) ){
						
							foreach( $urls as $url){
								
								$abs_url = $this->parent->get_absolute_url($url, $source);
								
								$newUrl = new \Sabberworm\CSS\Value\CSSString($abs_url);
								
								$value->setURL($newUrl);							
							}
						}
					}
				}
			}
		}
				
		// prepend selectors
		
		foreach( $css->getAllDeclarationBlocks() as $block ) {
			
			//dump($block);
			
			foreach( $block->getSelectors() as $selector ) {
				
				$name = $selector->getSelector();
				
				$valid = true;
				
				/*
				
				// filter selectors
				
				$filters = '.glyphicon-';
				
				$filters = explode(' ',$filters);
				
				if( !empty($filters) ){
				
					foreach( $filters as $filter ){
					
						if( strpos($name,$filter) !== false ){
							
							$valid = false;
							break;
						}
					}
				}
				*/
				
				if( $valid ){

					$selector->setSelector( $prepend . ' ' . $selector->getSelector() );
				}
				else{
					
					// remove block
					
					$css->removeDeclarationBlockBySelector($block, true);
				}
			}
		}
		
		//$content = $css->render(Sabberworm\CSS\OutputFormat::createPretty());
		
		//$content = $css->render(Sabberworm\CSS\OutputFormat::createCompact());
		
		$content = $css->render();

		// restore unicode numbers
		
		$content = str_replace('\\\\','\\',$content);
		
		return $content;
	}
	
	public function parse_hosted_content(){
		
		// get layer content
		
		$layerHead 			= '';
		$layerContent 		= '';
		
		$headStyles = array();
		$headLinks = array();

		if( !empty($this->defaultStaticPath) && file_exists($this->defaultStaticPath) ){
			
			$output = file_get_contents($this->defaultStaticPath);

			// strip html comments
			
			$output = preg_replace('/<!--(.*)-->/Uis', '', $output);
			
			// parse dom elements
			
			libxml_use_internal_errors( true );
			
			$dom= new DOMDocument();
			$dom->loadHTML('<?xml encoding="UTF-8">' . $output); 

			$xpath = new DOMXPath($dom);
			
			// remove nodes
			
			$nodes = $xpath->query('//meta|//title|//base');
			
			foreach ($nodes as $node) {
				
				$node->parentNode->removeChild($node);
			}

			// remove duplicate styles
			
			$nodes = $xpath->query('//style');
			
			foreach ($nodes as $node) {
				
				$nodeValue 	= $node->nodeValue;
				
				if( !empty($nodeValue) ){
				
					if( !in_array($nodeValue,$headStyles) ){
					
						$headStyles[] = $nodeValue;
					}
					else{
					
						$node->parentNode->removeChild($node);
					}
				}
			}		
			
			// remove duplicate links
			
			$nodes = $xpath->query('//link');
			
			foreach ($nodes as $node) {
				
				$nodeValue 	= $node->getAttribute('href');
				
				if( !empty($nodeValue) ){
					
					$link = $this->sanitize_url($nodeValue,$this->defaultStaticDirUrl);
				
					if( !in_array($link,$headLinks) ){
						
						if( $link != $nodeValue ){
							
							//normalize link
							
							$node->setAttribute('href',$link);
						}
					
						$headLinks[] = $link;
					}
					else{
					
						$node->parentNode->removeChild($node);
					}
				}
			}
			
			// parse relative image urls
			
			$nodes = $xpath->query('//img');
			
			foreach ($nodes as $node) {
				
				$nodeValue 	= $node->getAttribute('src');
				
				if( !empty($nodeValue) ){
					
					//normalize link
					
					$link =$this->sanitize_url($nodeValue,$this->defaultStaticDirUrl);

					$node->setAttribute('src',$link);
				}
			}

			// get head
			
			$layerHead = $dom->saveHtml( $xpath->query('/html/head')->item(0) );
			$layerHead = preg_replace('~<(?:!DOCTYPE|/?(?:head))[^>]*>\s*~i', '', $layerHead);
			
			// get body
			
			if( !empty($this->parent->layer->layerContent) ){
			
				$layerContent =$this->layerContent;
			}
			else{
				
				$layerContent = $dom->saveHtml( $xpath->query('/html/body')->item(0) );
				$layerContent = preg_replace('~<(?:!DOCTYPE|/?(?:body))[^>]*>\s*~i', '', $layerContent);
			}
		}
		else{
			
			$layerContent = $this->layerContent;
			
			$layerContent = $this->sanitize_content($layerContent);
		}

		// parse content elements
		
		libxml_use_internal_errors( true );
		
		$dom= new DOMDocument();
		$dom->loadHTML('<?xml encoding="UTF-8">' . $layerContent); 

		$xpath = new DOMXPath($dom);

		// remove pagespeed_url_hash
		
		$links = [];
		
		$nodes = $xpath->query('//img');
		
		foreach ($nodes as $node) {
			
			$node->removeAttribute('pagespeed_url_hash');
		}			
		
		$layerContent = $dom->saveHtml( $xpath->query('/html/body')->item(0) );
		$layerContent = preg_replace('~<(?:!DOCTYPE|/?(?:body))[^>]*>\s*~i', '', $layerContent);

		//get style-sheet
		
		$defaultCss 	= '';
		$layerCss 		= '';
		$defaultJs 		= '';
		$defaultJson 	= '';
		$layerJs 		= '';
		$layerMeta 		= '';
		
		$this->layerStyleClasses = array(
			
			'layer-' . $this->defaultId,
			'layer-' . $this->id,
		);
		
		if( isset($_POST['importCss']) ){

			$layerCss = stripcslashes($_POST['importCss']);
		}
		elseif( empty($_POST) ){
			
			$defaultCss = $this->parse_css_content($this->defaultCss, '.layer-' . $this->defaultId);
			
			$layerCss = $this->layerCss;
			
			$defaultJs = $this->defaultJs;
			
			$defaultJson = $this->defaultJson;
			
			$layerJs = $this->layerJs;

			$layerMeta = $this->layerMeta;
		}
		
		$defaultCss = sanitize_text_field($defaultCss);
		$layerCss 	= sanitize_text_field($layerCss);
		
		$layerContent = str_replace('<?xml encoding="UTF-8">','',$layerContent);
		
		// get google fonts
		
		$googleFonts = [];
		$fontsLibraries = [];
		
		if( !empty($layerCss) ){
			
			$regex = '`\/\/fonts\.googleapis\.com\/css\?family=([0-9A-Za-z\|\,\+\:]+)`';
			$fonts = preg_match($regex, $layerCss,$match);
			
			if(isset($match[1])){
				
				$googleFonts = array_merge( $googleFonts, explode('|',$match[1]));
			}
		}
		
		// get font libraries
		
		if( !empty($this->layerFontLibraries) ){
			
			foreach($this->layerFontLibraries as $term){
				
				$font_url = get_option( 'font_url_' . $term->slug);
				
				if( !empty($font_url) ){
					
					$regex = '`\/\/fonts\.googleapis\.com\/css\?family=([0-9A-Za-z\|\,\+\:]+)`';
					$fonts = preg_match($regex, $font_url,$match);

					if(isset($match[1])){
						
						$googleFonts = array_merge( $googleFonts, explode('|',$match[1]));
					}
					else{
						
						$fontsLibraries[] = $font_url;
					}	
				}
			}
		}

		// get head content

		$head = '';
		
		// font library
		
		if( !empty($googleFonts) ){
		
			$head .= '<link href="//fonts.googleapis.com/css?family='.implode('|',$googleFonts).'" rel="stylesheet" />';
		}
		
		if( !empty($fontsLibraries) ){
		
			foreach( $fontsLibraries as $font ){
		
				$font = $this->sanitize_url( $font );
				
				if( !empty($font) && !in_array($font,$headLinks) ){
		
					$head .= '<link href="' . $font . '" rel="stylesheet" />';
				
					$headLinks[] = $font;
				}
			}
		}	
		
		if( !empty($this->layerCssLibraries) ){
			
			foreach($this->layerCssLibraries as $term){
				
				$this->layerStyleClasses[] = 'style-' . $term->term_id;
				
				$css_url = $this->get_css_parsed_url($term);
				
				if( !empty($css_url) ){
					
					$css_url = $this->sanitize_url($css_url);
					
					if( !empty($css_url) && !in_array($css_url,$headLinks) ){

						$head .= '<link href="' . $css_url . '" rel="stylesheet" type="text/css" />';
							
						$headLinks[] = $css_url;
					}					
				}
				else{
				
					$css_url = $this->sanitize_url(get_option( 'css_url_' . $term->slug));
					
					if( !empty($css_url) && !in_array($css_url,$headLinks) ){

						$head .= '<link href="' . $css_url . '" rel="stylesheet" type="text/css" />';
							
						$headLinks[] = $css_url;
					}
					
					$css_content = get_option( 'css_content_' . $term->slug);
					
					if( !empty($css_content) ){
					
						$head .= '<style>' . stripcslashes($css_content) . '</style>';
					}
				}
			}
		}
		
		$head .= PHP_EOL;
	
		if( !empty($layerHead) ){
			
			$head .= $layerHead;
		}
		
		if(!empty($layerMeta['link'])){
			
			foreach($layerMeta['link'] as $url){
				
				$url =$this->sanitize_url( $url );
				
				if( !empty($url) && !in_array($url,$headLinks) ){
				
					$head .= '<link href="' . $url . '" rel="stylesheet" type="text/css" />';
			
					$headLinks[] = $url;
				}
			}
		}			
		

		// output css files
		
		if( !empty($this->defaultStaticCssUrl) ){
			
			$this->defaultStaticCssUrl =$this->sanitize_url( $this->defaultStaticCssUrl );
			
			if( !empty($this->defaultStaticCssUrl) && !in_array($this->defaultStaticCssUrl,$headLinks) ){
			
				$head .= '<link href="' . $this->defaultStaticCssUrl . '" rel="stylesheet" />';
			
				$headLinks[] = $this->defaultStaticCssUrl;
			}
		}
		
		if($this->type == 'user-layer' && $layerCss != $defaultCss ){
			
			$this->layerStaticCssUrl =$this->sanitize_url( $this->layerStaticCssUrl );
			
			if( !empty($this->layerStaticCssUrl) && !in_array($this->layerStaticCssUrl,$headLinks) ){
			
				$head .= '<link href="' . $this->layerStaticCssUrl . '" rel="stylesheet" />';
			
				$headLinks[] = $this->layerStaticCssUrl;
			}
		}
		
		// add meta
		
		if(!$this->is_local_page($this->id)){ 
			
			// output custom meta tags
			
			if( !empty($this->layerSettings) ){

				foreach( $this->layerSettings as $key => $content ){
					
					if( !empty($content) ){
					
						if( $key == 'meta_title' ){
							
							$title = ucfirst($content);
							
							$head .= '<title>'.$title.'</title>'.PHP_EOL;
							$head .= '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
							$head .= '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
							$head .= '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;		
						}
						elseif( $key == 'meta_keywords' ){

							$content = implode(',',explode(PHP_EOL,$content));
						
							$head .= '<meta name="keywords" content="'.$content.'" />'.PHP_EOL;
							
						}
						elseif( $key == 'meta_description' ){
							
							$head .= '<meta name="description" content="'.$content.'" />'.PHP_EOL;
							$head .= '<meta name="abstract" content="'.$content.'" />' . PHP_EOL;
							$head .= '<meta name="summary" content="'.$content.'" />' . PHP_EOL;
							$head .= '<meta property="og:description" content="'.$content.'" />' . PHP_EOL;
							$head .= '<meta name="twitter:description" content="'.$content.'" />'.PHP_EOL;
						}
						elseif( $key == 'link_author' ){
							
							$head .= '<link rel="author" href="' .$this->sanitize_url( $content ) . '" />'.PHP_EOL;
							$head .= '<link rel="publisher" href="' .$this->sanitize_url( $content ) . '" />'.PHP_EOL;
						}
						elseif( $key == 'meta_image' ){
							
							$head .= '<meta property="og:image" content="'.$content.'" />'.PHP_EOL;
							$head .= '<meta name="twitter:image" content="'.$content.'" />'.PHP_EOL;
							
						}
						elseif( $key == 'meta_favicon' ){
							
							$head .= '<link rel="icon" href="'.$content.'" sizes="32x32"/>'.PHP_EOL;
							$head .= '<link rel="icon" href="'.$content.'" sizes="192x192"/>'.PHP_EOL;
							$head .= '<link rel="apple-touch-icon-precomposed" href="'.$content.'"/>'.PHP_EOL;
							$head .= '<meta name="msapplication-TileImage" content="'.$content.'"/>'.PHP_EOL;				
						}
						elseif( $key == 'meta_facebook-id' ){
							
							$head .= '<meta property="fb:admins" content="'.$content.'"/>'.PHP_EOL;
							
						}				
						else{
							
							list($markup,$name) = explode('_',$key);
							
							if( $markup == 'meta' ){
								
								$head .= '<meta name="'.$name.'" content="'.$content.'" />'.PHP_EOL;
							}
							elseif( $markup == 'link' ){
								
								$head .= '<link rel="'.$name.'" href="' .$this->sanitize_url( $content ) . '" />'.PHP_EOL;
							}
						}
					}
				}
			}
			
			if( empty($this->layerSettings['meta_title']) ){
				
				// output default title
				
				$title = ucfirst($this->parent->layer->title);
				
				$head .= '<title>'.$title.'</title>'.PHP_EOL;
				$head .= '<meta name="subject" content="'.$title.'" />'.PHP_EOL;
				$head .= '<meta property="og:title" content="'.$title.'" />'.PHP_EOL;
				$head .= '<meta name="twitter:title" content="'.$title.'" />'.PHP_EOL;					
			}			
			
			// output default meta tags
			
			$ggl_webmaster_id = get_option( $this->parent->_base . 'embedded_ggl_webmaster_id' );
			
			if( !empty($ggl_webmaster_id) ){
			
				$head .= '<meta name="google-site-verification" content="'.$ggl_webmaster_id.'" />'.PHP_EOL;
			}
			
			/*
			
			//TODO $post doesnt exist
			
			$author_name = get_the_author_meta('display_name', $post->post_author );
			$author_mail = get_the_author_meta('user_email', $post->post_author );
			
			if( empty($this->layerSettings['meta_author']) ){
				
				$head .= '<meta name="author" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
				$head .= '<meta name="creator" content="'.$author_name.', '.$author_mail.'" />' . PHP_EOL;
				$head .= '<meta name="owner" content="' . $author_name . '" />' . PHP_EOL;
				$head .= '<meta name="reply-to" content="'.$author_mail.'" />' . PHP_EOL;					
			}
			*/
			
			$locale = get_locale();
			
			if( empty($this->layerSettings['meta_language']) ){
				
				$head .= '<meta name="language" content="' . $locale . '" />'.PHP_EOL;
			}
			
			$robots = 'index,follow';
			
			if( empty($this->layerSettings['meta_robots']) ){
				
				$head .= '<meta name="robots" content="'.$robots.'" />' . PHP_EOL;
			}
			/*
			$revised = $post->post_date;
			
			if( empty($this->layerSettings['meta_revised']) ){
			
				$head .= '<meta name="revised" content="' . $revised . '" />' . PHP_EOL;
			}
			*/
			
			$content = ucfirst($this->parent->layer->title);
			
			if( empty($this->layerSettings['meta_description']) ){
				
				$head .= '<meta name="description" content="'.$content.'" />'.PHP_EOL;
				$head .= '<meta name="abstract" content="'.$content.'" />' . PHP_EOL;
				$head .= '<meta name="summary" content="'.$content.'" />' . PHP_EOL;
				$head .= '<meta property="og:description" content="'.$content.'" />' . PHP_EOL;
				$head .= '<meta name="twitter:description" content="'.$content.'" />'.PHP_EOL;
			}
			
			$head .= '<meta name="classification" content="Business" />' . PHP_EOL;
			//$head .= '<meta name="classification" content="products, product classifications, company classification, company type, industry" />' . PHP_EOL;
			
			$service_name = get_bloginfo( 'name' );
			
			$head .= '<meta name="copyright" content="'.$service_name.'" />'.PHP_EOL;
			$head .= '<meta name="designer" content="'.$service_name.' team" />' . PHP_EOL;
			
			if( !empty($this->layerEmbedded) ){
			
				$url =$this->sanitize_url( $this->layerEmbedded );
				
				$head .= '<meta name="url" content="'.$url.'" />' . PHP_EOL;
				//$head .= '<meta name="canonical" content="'.$url.'" />' . PHP_EOL;
				$head .= '<meta name="original-source" content="'.$url.'" />' . PHP_EOL;
				$head .= '<link rel="original-source" href="'.$url.'" />' . PHP_EOL;
				$head .= '<meta property="og:url" content="'.$url.'" />' . PHP_EOL;
				$head .= '<meta name="twitter:url" content="'.$url.'" />' . PHP_EOL;
			}
			
			$head .= '<meta name=viewport content="width=device-width, initial-scale=1">' . PHP_EOL;
			
			$head .= '<meta name="rating" content="General" />' . PHP_EOL;
			$head .= '<meta name="directory" content="submission" />' . PHP_EOL;
			$head .= '<meta name="coverage" content="Worldwide" />' . PHP_EOL;
			$head .= '<meta name="distribution" content="Global" />' . PHP_EOL;
			$head .= '<meta name="target" content="all" />' . PHP_EOL;
			$head .= '<meta name="medium" content="blog" />' . PHP_EOL;
			$head .= '<meta property="og:type" content="article" />' . PHP_EOL;
			$head .= '<meta name="twitter:card" content="summary" />' . PHP_EOL;
			
			/*
			$head .= '<meta name="geo.position" content="latitude; longitude" />' . PHP_EOL;
			$head .= '<meta name="geo.placename" content="Place Name" />' . PHP_EOL;
			$head .= '<meta name="geo.region" content="Country Subdivision Code" />' . PHP_EOL;
			*/

			/*
			$ggl_analytics_id = get_option( $this->parent->_base . 'embedded_ggl_analytics_id' );
							
			if( !empty($ggl_analytics_id) ){
			
				?>
				<script> 
				
					<!-- Google Analytics Code -->
				
					(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
					(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
					m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
					})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

					ga('create', '<?php echo $ggl_analytics_id; ?>', 'auto');
					ga('send', 'pageview');
					
					<!-- End Google Analytics Code -->
					
				</script>

				<?php					
			}
			*/	
		}			

		$this->layerHeadContent = $head;
		
		// get body content
		
		$body = '';
		
		//include style-sheets
		
		if( $defaultCss!='' ){
			
			$body .= '<style id="LiveTplEditorDefaultStyleSheet">'.PHP_EOL;
				
				$body .= $defaultCss .PHP_EOL;
			
			$body .= '</style>'.PHP_EOL;
		}
		
		$body .= '<style id="LiveTplEditorStyleSheet">'.PHP_EOL;
		
			if( $layerCss!='' ){

				$body .= $layerCss .PHP_EOL;
			}
			
		$body .= '</style>'.PHP_EOL;		
		
		//include layer
		
		$layer_template = get_page_template_slug( $this->id );
		
		if( empty($_REQUEST['t']) && $this->is_local_page($this->id) && $layer_template != 'templates/full-page.php' ){
		
			$body .='<div class="' . implode(' ',$this->layerStyleClasses) . '">' .PHP_EOL;
		}
		
			if( empty($_POST) && $this->layerForm == 'importer' && empty($this->parent->layer->layerContent) ){
				
				$body .='<script>' .PHP_EOL;

					$body .= ' var layerFormActive = true;' .PHP_EOL;
					
				$body .='</script>' .PHP_EOL;
				
				$body .= '<div class="container">';
				
					$body .= '<div class="panel panel-default" style="margin:50px;">';
					
					$body .= '<div class="panel-heading">';
					
						if( !empty($this->layerForm) ){
							
							$body .='<h4>'.ucfirst($this->parent->layer->title).'</h4>';
						}
						
					$body .= '</div>';
					
					$body .= '<div class="panel-body">';
					
						$body .= '<form target="_self" action="" method="post" style="width:100%;background:#FFFFFF;">';
						
							if( $this->layerForm == 'importer' ){
						
								$body .= '<div class="col-xs-3">';
								
									$body .='<label>HTML</label>';
									
								$body .= '</div>';
								
								$body .= '<div class="col-xs-9">';
								
									$body .= '<div class="form-group">';
									
										$body .= '<textarea class="form-control" name="importHtml" style="min-height:100px;"></textarea>';
										
									$body .= '</div>';
									
								$body .= '</div>';

								$body .= '<div class="col-xs-12 text-right">';
									
									$body .= '<input class="btn btn-primary btn-md" type="submit" value="Import" />';
									
								$body .= '</div>';
							}							
						
						$body .= '</form>';
						
					$body .= '</div>';
					$body .= '</div>';
				
				$body .= '</div>';
			} 
			else{

				$body .= '<layer class="editable" style="width:100%;' . ( !empty($this->layerMargin) ? 'margin:'.$this->layerMargin.';' : '' ) . '">';
								
					$body .= $layerContent;
				
				$body .= '</layer>' .PHP_EOL;
			}
		
		if( empty($_REQUEST['t']) && $this->is_local_page($this->id) && $layer_template != 'templates/full-page.php' ){
		
			$body .='</div>' .PHP_EOL;
		}
		
		if( !empty($defaultJson) ){
			
			$body .= '<script>'.$defaultJson.'</script>' .PHP_EOL;
		}

		if( !empty($this->layerJsLibraries) ){
			
			foreach($this->layerJsLibraries as $term){
				
				$js_skip = 'off';
				
				if( $this->is_local ){
				
					$js_skip = get_option( 'js_skip_local_' . $term->slug);
				}
				
				if( $js_skip != 'on' ){
					
					$js_url = get_option( 'js_url_' . $term->slug);
					
					if( !empty($js_url) ){
						
						$body .= '<script src="'.$js_url.'"></script>' .PHP_EOL;
					}
					
					$js_content = get_option( 'js_content_' . $term->slug);
					
					if( !empty($js_content) ){
					
						$body .= stripcslashes($js_content) .PHP_EOL;	
					}
				}
			}
		}
		
		if( !empty($layerMeta['script']) ){
			
			foreach($layerMeta['script'] as $url){
				
				$body .= '<script src="'.$url.'"></script>' .PHP_EOL;
			}
		}
		
		//include layer script
		
		$body .='<script id="LiveTplEditorScript">' .PHP_EOL;
		
			if( $layerJs != '' ){

				$body .= $layerJs .PHP_EOL;				
			}				
			
		$body .='</script>' .PHP_EOL;
		
		if($this->type == 'user-layer' && !empty($layerJs) ){

			$body .= '<script src="'.$this->layerStaticJsUrl.'"></script>' .PHP_EOL;
		}
		elseif( !empty($defaultJs) ){
			
			$body .= '<script src="'.$this->defaultStaticJsUrl.'"></script>' .PHP_EOL;
		}
		
		$this->layerBodyContent = $body;
	}
	
	public function get_layer_attachments($post_id){
	
		$attachments = get_posts( array(
						
			'post_parent' 		=> $post_id,
			'post_type' 		=> 'attachment',
			'post_mime_type' 	=> array('application/zip','image/vnd.adobe.photoshop'),
			'orderby' 			=> 'date',
			'order' 			=> 'DESC'
		));
		
		return $attachments;
	}

	public function get_user_layers( $user, $context='admin-dashboard' ) {

		echo '<div class="postbox">';
			
			echo '<h3 style="margin:10px;">' . __( 'Saved Projects', 'live-template-editor-client' ) . '</h3>';
		
			echo '<table class="widefat fixed striped" style="border:none;">';
				
				if( $layers = get_posts(array(
				
					'author'      => $user->ID,
					'post_type'   => 'user-layer',
					'post_status' => 'publish',
					'numberposts' => -1
					
				))){

					foreach( $layers as $layer ){

						echo '<tr>';
						
							echo '<td style="width:300px;">';
								
								echo $layer->post_title;
							
							echo '</td>';
							
							echo '<td>';
								
								echo'<a class="btn btn-sm btn-default" href="' . get_edit_post_link( $layer->ID ) . '" target="_blank">Edit backend</a>';
								echo ' | ';
								echo'<a class="btn btn-sm btn-default" href="' . $this->parent->urls->editor . '?uri=' . $layer->ID . '" target="_blank">Edit frontend</a>';
								echo ' | ';
								echo'<a class="btn btn-sm btn-default" href="' . get_post_permalink( $layer->ID ) . '" target="_blank">Preview</a>';
								
							echo '</td>';
						
						echo '</tr>';
					}
				}
				else{
					
					echo '<tr>';
					
						echo '<td style="width:300px;">';
							
							echo 'None';
						
						echo '</td>';
						
						echo '<td>';
							
							echo'';
							
						echo '</td>';
					
					echo '</tr>';					
				}
				
			echo '</table>';
			
		echo '</div>';
	}
		
	public function get_options($taxonomy,$term,$price_currency='$'){
		
		if(is_array($term)){
			
			$term_slug = $term['slug'];
		}
		else{
		
			$term_slug = $term->slug;
		}
	
		if(!$price_amount = get_option('price_amount_' . $term_slug)){
			
			$price_amount = 0;
		} 
		
		if(!$price_period = get_option('price_period_' . $term_slug)){
			
			$price_period = 'month';
		}
		
		if(!$storage_amount = get_option('storage_amount_' . $term_slug)){
			
			$storage_amount = 0;
		}
		
		if(!$storage_unit = get_option('storage_unit_' . $term_slug)){
			
			$storage_unit = 'templates';
		}
		
		if(!$form = get_option('meta_' . $term_slug)){
			
			$form = [];
		}

		$options=[];
		$options['price_currency']	= $price_currency;
		$options['price_amount']	= $price_amount;
		$options['price_period']	= $price_period;
		$options['storage_amount']	= $storage_amount;
		$options['storage_unit']	= $storage_unit;
		$options['form']			= $form;
		
		// add addon options
		
		$this->options  = array();
		
		do_action('ltple_layer_options',$term_slug);
		
		$options = array_merge($options,$this->options);
		
		return $options;
	}
	
	public function show_layer(){
		
		$data = [];
		
		if( !empty($_GET['url']) ){
			
			$url = parse_url(urldecode(urldecode($_GET['url'])));
			
			if(!empty($url['host'])){
			
				$domain = get_page_by_title($url['host'], OBJECT, 'user-domain');
			
				if(!empty($domain)){
					
					$urls = get_post_meta($domain->ID,'domainUrls',true);
					
					foreach($urls as $layerId => $domainPath ){
						
						if( $url['path'] == '/'.$domainPath ){
							
							$post = get_post($layerId);
							
							if( !empty($post) ){
								
								if(!isset($this->defaultId)){
									
									$this->defaultId = intval(get_post_meta( $this->id, 'defaultLayerId', true ));
								}

								include($this->parent->views . '/layer.php');
								
								exit;
							}							
						}
					}
				}
			}
		}
		elseif( !empty($_GET['uid']) ){
			
			$layerId = intval($_GET['uid']);
			 
			if( $layerId > 0 ){
				
				$post = get_post($layerId);
				
				if( !empty($post) && $post->post_type == 'user-layer' ){
					
					if( $post->post_status == 'publish' ){
						
						if(!isset($this->defaultId)){
							
							$this->defaultId = intval(get_post_meta( $this->id, 'defaultLayerId', true ));
						}						

						include($this->parent->views . '/layer.php');
					}
					elseif( $post->post_status == 'draft' ){
						
						echo 'This page is in draft mode...';
					}
					else{
						
						echo 'This page has been removed...';
					}
					
					exit;
				}
			}
		}
	}
	
	public static function is_absolute_path($file){
		
		return strspn($file, '/\\', 0, 1)
			|| (strlen($file) > 3 && ctype_alpha($file[0])
				&& substr($file, 1, 1) === ':'
				&& strspn($file, '/\\', 2, 1)
			)
			|| null !== parse_url($file, PHP_URL_SCHEME)
		;
	}	
	
	public static function sanitize_url( $url, $dirUrl = '' ){
		
		if( !empty($url) ){
		
			if( !empty($dirUrl) && !self::is_absolute_path($url) ){
				
				$url = $dirUrl . $url;
			}
			
			if( is_ssl() ){
				
				$url = str_replace( 'http://', 'https://', $url);
			}
			else{
				
				$url = str_replace( 'https://', 'http://', $url);
			}
		}
		
		return $url;
	}
	
	public static function sanitize_content($str,$is_hosted=false){
		
		$str = stripslashes($str);
		
		//$str = str_replace(array('&quot;'),array(htmlentities('&quot;')),$str);
		
		$str = str_replace(array('cursor: pointer;','data-element_type="video.default"'),'',$str);
		
		$str = str_replace(array('<body','</body>','src=" ','href=" '),array('<div id="main"','</div>','src="','href="'),$str);
		
		//$str = html_entity_decode(stripslashes($str));
		
		//$str = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $str);
		
		$str = preg_replace( array(
		
				//'/<iframe(.*?)<\/iframe>/is',
				'/<title(.*?)<\/title>/is',
				'/<!doctype(.*?)>/is',
				'/<link(.*?)>/is',
				//'/<body(.*?)>/is',
				//'/<\/body>/is',
				//'/<head(.*?)>/is',
				//'/<\/head>/is',				
				'/<html(.*?)>/is',
				'/<\/html>/is'
			), 
			'', $str
		);		
		
		if( !$is_hosted ){
		
			$str = preg_replace( array(
			
					'/<pre(.*?)<\/pre>/is',
					'/<frame(.*?)<\/frame>/is',
					'/<frameset(.*?)<\/frameset>/is',
					'/<object(.*?)<\/object>/is',
					'/<script(.*?)<\/script>/is',
					'/<style(.*?)<\/style>/is',
					'/<embed(.*?)<\/embed>/is',
					'/<applet(.*?)<\/applet>/is',
					'/<meta(.*?)>/is',
					'/onload="(.*?)"/is',
					'/onunload="(.*?)"/is',
				), 
				'', $str
			);
		}
		
		return $str;
	}
	
	public function add_layer_fields( $taxonomy, $term_slug = '' ){
		
		//collect our saved term field information
		
		$price = $storage = [];

		if( !empty($term_slug) ){
		
			$price['price_amount'] = get_option('price_amount_' . $term_slug); 
			$price['price_period'] = get_option('price_period_' . $term_slug); 

			$storage['storage_amount'] 	= get_option('storage_amount_' . $term_slug);
			$storage['storage_unit'] 	= get_option('storage_unit_' . $term_slug);
		}	
		
		echo'<div class="form-field" style="margin-bottom:15px;">';
			
			echo'<label for="'.$taxonomy.'-price-amount">Price</label>';

			echo $this->parent->plan->get_layer_price_fields($taxonomy,$price);
			
		echo'</div>';
		
		echo'<div class="form-field" style="margin-bottom:15px;">';
			
			echo'<label for="'.$taxonomy.'-storage-amount">Storage</label>';

			echo $this->parent->plan->get_layer_storage_fields($taxonomy,$storage);
			
		echo'</div>';
		
		do_action('ltple_layer_plan_fields', $taxonomy, $term_slug);
	}
	
	public function add_edit_layer_fields($term){
		
		//output our additional fields

		if( $term->taxonomy == 'layer-type' ){
		
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Type </label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'output',
						'id'			=> 'output',
						'label'			=> "",
						'type'			=> 'select',
						'options'		=> $this->get_layer_outputs(),
						'inline'		=> false,
						'default'		=> 'inline-css',
						'description'	=> '',
						
					), $term );
					
				echo'</td>';	
				
			echo'</tr>';		
		
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Visibility </label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'visibility_'.$term->slug,
						'id'			=> 'visibility_'.$term->slug,
						'label'			=> "",
						'type'			=> 'radio',
						'options'		=> array(
							
							'admin'			=> 'Admin',
							'anyone'		=> 'Anyone',
							'none'			=> 'None',
						),
						'inline'		=> false,
						'default'		=> 'anyone',
						'description'	=> ''
						
					), false );
					
				echo'</td>';	
				
			echo'</tr>';
			
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Gallery section </label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$options = array( '-1' => 'none' );
					
					$sections = get_terms( array(
							
						'taxonomy' 		=> 'gallery-section',
						'orderby' 		=> 'name',
						'order' 		=> 'ASC',
						'hide_empty'	=> false, 
					));
					
					if( !empty($sections) ){
							
						foreach( $sections as $section ){
							
							$options[$section->term_id] = $section->name;
						}
					} 
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'gallery_section',
						'id'			=> 'gallery_section',
						'label'			=> "",
						'type'			=> 'select',
						'options'		=> $options,
						'inline'		=> false,
						'description'	=> '',
						
					), $term );				
					
				echo'</td>';	
				
			echo'</tr>';
			
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Addon range </label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$options = array( '-1' => 'none' );
					
					$ranges = get_terms( array(
							
						'taxonomy' 		=> 'layer-range',
						'orderby' 		=> 'name',
						'order' 		=> 'ASC',
						'hide_empty'	=> false, 
					));
					
					if( !empty($ranges) ){
							
						foreach( $ranges as $range ){
							
							$options[$range->term_id] = $range->name;
						}
					} 
					
					$this->parent->admin->display_field( array(			
						
						'name'			=> 'addon_range',
						'id'			=> 'addon_range',
						'label'			=> "",
						'type'			=> 'select',
						'options'		=> $options,
						'inline'		=> false,
						'description'	=> '',
						
					), $term );				
					
				echo'</td>';	
				
			echo'</tr>';
		}
		
		// layer plan attributes
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Plan </label>';
			
			echo'</th>';		
		
			echo'<td>';
				
				$this->add_layer_fields($term->taxonomy,$term->slug);
				
			echo'</td>';
			
		echo'</tr>';
		
		/*
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Meta </label>';
			
			echo'</th>';
			
				echo'<td>';
					
					$this->parent->admin->display_field(array(
					
						'type'				=> 'form',
						'id'				=> 'meta_'.$term->slug,
						'name'				=> $term->taxonomy . '-meta',
						'array' 			=> [],
						'description'		=> ''
						
					), $term );
					
				echo'</td>';	
			
		echo'</tr>';
		
		*/
	}
	
	public function get_css_library_fields($term){

		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Remote Url </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'text',
					'id'				=> 'css_url_'.$term->slug,
					'name'				=> 'css_url_'.$term->slug,
					'placeholder'		=> 'http://',
					'description'		=> '',
					
				), false );					
				
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">CSS Content </label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'				=> 'textarea',
					'id'				=> 'css_content_'.$term->slug,
					'name'				=> 'css_content_'.$term->slug,
					'placeholder'		=> '.style{display:block;}',
					'description'		=> '<i>without ' . htmlentities('<style></style>') . '</i>'
					
				), false );				
					
			echo'</td>';
			
		echo'</tr>';
		
		$parse = get_option('css_parse_'.$term->slug,'off');
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Parse Content</label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'			=> 'switch',
					'id'			=> 'css_parse_'.$term->slug,
					'name'			=> 'css_parse_'.$term->slug,
					'data'			=> $parse,
					'description'	=> 'Prepend unique class name to CSS selectors',
					
				), false );				
					
			echo'</td>';
			
		echo'</tr>';
		
		if( $parse == 'on' ){
			
			/*
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Md5 </label>';
				
				echo'</th>';
				
				echo'<td>';
						
					$this->parent->admin->display_field(array(
					
						'type'		=> 'text',
						'id'		=> 'css_md5_'.$term->slug,
						'name'		=> 'css_md5_'.$term->slug,
						'disabled'	=> true,
						
					), false );				
						
				echo'</td>';
				
			echo'</tr>';
			*/
			
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Source </label>';
				
				echo'</th>';
				
				echo'<td>';
						
					$this->parent->admin->display_field(array(
					
						'type'		=> 'text',
						'id'		=> 'css_source_'.$term->slug,
						'name'		=> 'css_source_'.$term->slug,
						'data'		=> $this->get_css_parsed_url($term),
						'disabled'	=> true,
						
					), false );				
						
				echo'</td>';
				
			echo'</tr>';
		}
	}
	
	public function get_css_parsed_url($term){
		
		$css_parse = get_option('css_parse_'.$term->slug);
	
		if( $css_parse == 'on' ){
			
			$attach_id = intval(get_option('css_attachment_'.$term->slug));		

			$css_url = get_option('css_url_'.$term->slug);
			
			$css_content = get_option('css_content_'.$term->slug);

			$css_md5 = get_option('css_md5_'.$term->slug);
			
			$css_version = '1.0.6';
			
			$styleName = 'style-' . $term->term_id;
			
			$md5 = md5($css_url.$css_content.$styleName.$css_version);
			
			if( $css_md5 != $md5 ){
				
				$content = '';
				
				if( !empty($css_url) ){
					
					$response = wp_remote_get($css_url);
					
					if ( is_array( $response ) ) {
						
						$body = $response['body'];

						if( !empty($body) ){
							
							$content .= $this->parse_css_content($body, '.' . $styleName, $css_url);
						}
					}
				}
				
				if( !empty($css_content) ){
					
					$content .= $this->parse_css_content($css_content, '.' . $styleName, $css_content);
				}
				
				//dump($content);

				if( !empty($content) ){
					
					// remove current attachement
					
					$css_attachement = get_post($attach_id);
					
					if(!empty($css_attachement)){
						
						wp_delete_attachment( $css_attachement->ID, true );
					}				
				
					// add style to media
					
					if ( !function_exists('media_handle_upload') ) {
						
						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
						require_once(ABSPATH . "wp-admin" . '/includes/file.php');
						require_once(ABSPATH . "wp-admin" . '/includes/media.php');
					}

					// create archive
					
					$tmp = wp_tempnam($styleName) . '.css';
					
					file_put_contents($tmp,$content);
					
					$file_array = array(
					
						'name' 		=> $styleName . '.css',
						'tmp_name' 	=> $tmp,
					);
					
					$post_data = array(
					
						'post_title' 		=> $styleName,
						'post_mime_type' 	=> 'text/css',
					);

					if(!defined('ALLOW_UNFILTERED_UPLOADS')) define('ALLOW_UNFILTERED_UPLOADS', true);
					
					$attach_id = media_handle_sideload( $file_array, null, null, $post_data );
					
					@unlink($tmp);
					
					if( is_numeric($attach_id) ){
					
						update_option('css_attachment_'.$term->slug,$attach_id);
					}
					else{
						
						dump($attach_id);
					}
				}
				
				//update md5
				
				update_option('css_md5_'.$term->slug,$md5);
			}
			
			if( is_numeric($attach_id) ){
				
				$url = wp_get_attachment_url($attach_id);
				
				if(!empty($url)){
				
					return $url . '?' . $md5;
				}
			} 			
		}
		
		return false;
	}
	
	public function get_js_library_fields($term){

		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Remote Url </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'text',
					'id'				=> 'js_url_'.$term->slug,
					'name'				=> 'js_url_'.$term->slug,
					'placeholder'		=> 'http://',
					'description'		=> ''
					
				), false );					
				
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">JS Content </label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'				=> 'textarea',
					'id'				=> 'js_content_'.$term->slug,
					'name'				=> 'js_content_'.$term->slug,
					'placeholder'		=> htmlentities('<script></script>'),
					'description'		=> '<i>with '.htmlentities('<script></script>').'</i>'
					
				), false );				
					
			echo'</td>';
			
		echo'</tr>';
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Skip local pages</label>';
			
			echo'</th>';
			
			echo'<td>';
					
				$this->parent->admin->display_field(array(
				
					'type'			=> 'switch',
					'id'			=> 'js_skip_local_'.$term->slug,
					'name'			=> 'js_skip_local_'.$term->slug,
					'description'	=> 'Skip the library in local pages to avoid conflict with the current theme',
					
				), false );				
					
			echo'</td>';
			
		echo'</tr>';
	}
	
	public function get_font_library_fields($term){

		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Url </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$this->parent->admin->display_field(array(
				
					'type'				=> 'text',
					'id'				=> 'font_url_'.$term->slug,
					'name'				=> 'font_url_'.$term->slug,
					'placeholder'		=> 'http://',
					'description'		=> ''
					
				), false );					
				
			echo'</td>';
			
		echo'</tr>';
	}
	
	public function set_default_layer_columns($columns){
		
		$columns = 	array_slice($columns, 0, 3, true) +
					array("elements" => "Elements") +
					array_slice($columns, 3, count($columns)-3, true);
		
		return $columns;
	}
	
	public function add_default_layer_column_content($column_name, $post_id){
		
		if($column_name == 'elements') {
			
			$count = 0;
			
			$elements = get_post_meta( $post_id, 'layerElements', true );
			
			if( isset($elements['content']) && !empty($elements['content']) ){
				
				foreach( $elements['content'] as $content ){
					
					if( !empty($content) ){
						
						++$count;
					}
				}
			}
			
			echo '<span>'.$count.'</span>';
		}
	}
		
	public function set_layer_type_columns($columns) {

		// Remove description, posts, wpseo columns
		$this->columns = [];
		
		// Add artist-website, posts columns

		$this->columns['cb'] 			= '<input type="checkbox" />';
		$this->columns['name'] 			= 'Name';
		$this->columns['output'] 		= 'Type';
		$this->columns['section'] 		= 'Section';
		$this->columns['visibility'] 	= 'Visibility';
		//$this->columns['slug'] 		= 'Slug';
		//$this->columns['description'] = 'Description';
		$this->columns['price'] 		= 'Price';
		$this->columns['storage'] 		= 'Storage';
		//$this->columns['posts'] 		= 'Layers';
		//$this->columns['users'] 		= 'Users';
		
		do_action('ltple_layer_type_columns');

		return $this->columns;
	}	
	
	public function set_layer_range_columns($columns) {

		// Remove description, posts, wpseo columns
		$this->columns = [];
		
		// Add artist-website, posts columns

		$this->columns['cb'] 			= '<input type="checkbox" />';
		$this->columns['name'] 			= 'Name';
		//$this->columns['slug'] 		= 'Slug';
		//$this->columns['description'] = 'Description';
		$this->columns['price'] 		= 'Price';
		$this->columns['storage'] 		= 'Storage';
		//$this->columns['posts'] 		= 'Layers';
		//$this->columns['users'] 		= 'Users';
	
		do_action('ltple_layer_range_columns');
	
		return $this->columns;
	}
	
	public function set_account_option_columns($columns) {

		// Remove description, posts, wpseo columns
		$this->columns = [];
		
		// Add artist-website, posts columns

		$this->columns['cb'] 			= '<input type="checkbox" />';
		$this->columns['name'] 			= 'Name';
		//$this->columns['slug'] 		= 'Slug';
		//$this->columns['description'] = 'Description';
		$this->columns['price'] 		= 'Price';
		$this->columns['storage'] 		= 'Storage';
		//$this->columns['posts'] 		= 'Layers';
		//$this->columns['users'] 		= 'Users';
		
		do_action('ltple_layer_option_columns');

		return $this->columns;
	}
		
	public function add_layer_column_content($content, $column_name, $term_id){
		
		$this->column = $content;
		
		if( $term = get_term($term_id) ){
			
			if($column_name === 'output') {
				
				$outputs = $this->get_layer_outputs();
				
				if(!$output = get_term_meta($term->term_id,'output',true)){
					
					$output = 'inline-css';
				}

				$this->column .='<span class="label label-primary">'.$outputs[$output].'</span>';
			}
			elseif($column_name === 'section') {

				if( $section_id = get_term_meta($term->term_id,'gallery_section',true)){
					
					$sections = $this->get_gallery_sections();
					
					if( !empty($sections[$section_id]) ){
						
						$this->column .='<span class="label label-info">' . $sections[$section_id]->name . '</span>';
					}
				}
			}
			elseif($column_name === 'visibility') {
				
				if(!$visibility = get_option('visibility_' . $term->slug)){
					
					$visibility = 'anyone';
				}
				
				if( $visibility == 'admin' ){
					
					$this->column .='<span class="label label-warning">'.$visibility.'</span>';
				}
				elseif( $visibility == 'none' ){
					
					$this->column .='<span class="label label-danger">'.$visibility.'</span>';
				}
				else{
					
					$this->column .='<span class="label label-success">'.$visibility.'</span>';
				}
				
			}
			elseif($column_name === 'price') {
				
				if(!$price_amount = get_option('price_amount_' . $term->slug)){
					
					$price_amount = 0;
				} 
				
				if(!$price_period = get_option('price_period_' . $term->slug)){
					
					$price_period = 'month';
				} 	
				
				$this->column .=$price_amount.'$'.' / '.$price_period;
			}
			elseif($column_name === 'storage') {
				
				if(!$storage_amount = get_option('storage_amount_' . $term->slug)){
					
					$storage_amount = 0;
				}
				
				if(!$storage_unit = get_option('storage_unit_' . $term->slug)){
					
					$storage_unit = 'templates';
				} 
				
				if( $storage_unit == 'templates' ){
					
					if( $storage_amount == 1 ){
						
						$this->column .='+'.$storage_amount.' project';
					}
					elseif($storage_amount > 0){
						
						$this->column .='+'.$storage_amount.' projects';
					}
					else{
						
						$this->column .= $storage_amount.' projects';
					}
				}
				elseif($storage_amount > 0){
					
					$this->column .='+'.$storage_amount.' '.$storage_unit;
				}
				else{
					
					$this->column .= $storage_amount.' '.$storage_unit;
				}
				
			}
			elseif($column_name === 'users') {
				
				$users=0;
				
				$this->column .=$users;
			}
			
			do_action('ltple_layer_column_content',$column_name,$term);
		}

		return $this->column;
	}
	
	public function save_layer_taxonomy_fields($term_id){

		if( $this->parent->user->is_admin ){
			
			//collect all term related data for this new taxonomy
			
			if( $term = get_term($term_id) ){
							
				//save our custom fields as wp-options
				
				if( isset($_POST[$term->taxonomy .'-price-amount']) && is_numeric($_POST[$term->taxonomy .'-price-amount']) ){

					update_option('price_amount_' . $term->slug, round(intval(sanitize_text_field($_POST[$term->taxonomy . '-price-amount'])),1));			
				}
				
				if( isset($_POST[$term->taxonomy .'-price-period']) ){

					$periods = $this->parent->plan->get_price_periods();
					$period = sanitize_text_field($_POST[$term->taxonomy . '-price-period']);
					
					if(isset($periods[$period])){
						
						update_option('price_period_' . $term->slug, $period);	
					}
				}
				
				if(isset($_POST[$term->taxonomy .'-storage-amount'])&&is_numeric($_POST[$term->taxonomy .'-storage-amount'])){

					update_option('storage_amount_' . $term->slug, round(intval(sanitize_text_field($_POST[$term->taxonomy . '-storage-amount'])),0));			
				}
				
				if(isset($_POST[$term->taxonomy .'-storage-unit'])){

					$storage_units = $this->parent->plan->get_storage_units();
					$storage_unit = sanitize_text_field($_POST[$term->taxonomy . '-storage-unit']);
					
					if(isset($periods[$period])){			
					
						update_option('storage_unit_' . $term->slug, $storage_unit);			
					}
				}

				if(isset($_POST['output'])){

					update_term_meta( $term->term_id, 'output', $_POST['output']);			
				}			
				
				if(isset($_POST['visibility_'.$term->slug])){

					update_option('visibility_'.$term->slug, $_POST['visibility_'.$term->slug]);			
				}
				
				if(isset($_POST['gallery_section'])){

					update_term_meta( $term->term_id, 'gallery_section', $_POST['gallery_section']);			
				}				
				
				if(isset($_POST['addon_range'])){

					update_term_meta( $term->term_id, 'addon_range', $_POST['addon_range']);			
				}
				
				/*
				if(isset($_POST[$term->taxonomy . '-meta'])){

					update_term_meta( $term->term_id, 'layer_type_meta', $_POST[$term->taxonomy . '-meta']);			
				}
				*/
				
				do_action('ltple_save_layer_taxonomy_fields',$term);
			}
		}
	}
	
	public function save_library_fields($term_id){

		if( $this->parent->user->is_editor ){
			
			//collect all term related data for this new taxonomy
			$term = get_term($term_id);

			//save our custom fields as wp-options

			if(isset($_POST['css_url_'.$term->slug])){

				update_option('css_url_'.$term->slug, $_POST['css_url_'.$term->slug]);			
			}
			
			if(isset($_POST['css_content_'.$term->slug])){

				update_option('css_content_'.$term->slug, $_POST['css_content_'.$term->slug]);			
			}
			
			if(isset($_POST['css_parse_'.$term->slug])){

				update_option('css_parse_'.$term->slug, $_POST['css_parse_'.$term->slug]);			
			}
			
			if(isset($_POST['js_url_'.$term->slug])){

				update_option('js_url_'.$term->slug, $_POST['js_url_'.$term->slug]);			
			}
			
			if(isset($_POST['js_content_'.$term->slug])){

				update_option('js_content_'.$term->slug, $_POST['js_content_'.$term->slug]);			
			}
			
			if(isset($_POST['js_skip_local_'.$term->slug])){

				update_option('js_skip_local_'.$term->slug, $_POST['js_skip_local_'.$term->slug]);			
			}
			
			if(isset($_POST['font_url_'.$term->slug])){

				update_option('font_url_'.$term->slug, $_POST['font_url_'.$term->slug]);			
			}
		}
	}
	
	public function get_static_dir_url($postId,$output){
		
		$static_url = '';
		
		if( $output == 'hosted-page' ){
			
			$static_url = $this->sanitize_url( $this->dirUrl . $postId . '/' );	
		}
	
		return $static_url;
	}	
	
	public function get_static_url($postId,$defaultId,$output=''){
		
		$layerStaticUrl = get_post_meta( $defaultId, 'layerStaticUrl', true );
		
		if( empty($layerStaticUrl) ){
			
			$layerStaticUrl = 'index.html';
		}
		
		$static_url = $this->sanitize_url( $this->get_static_dir_url($postId,$output) . $layerStaticUrl );				
	
		return $static_url;
	}
	
	public function get_static_asset_url($postId, $type = 'css', $filename = 'style'){
		
		$static_url = $this->sanitize_url( $this->dirUrl . $postId . '/assets/'.$type.'/' . $filename . '.' . $type );
		
		return $static_url;
	}
	
	public function get_static_dir($postId,$empty=false){
		
		$static_dir = $this->dirPath . $postId;
		
		if( !is_dir($static_dir) ){
			
			mkdir($static_dir,0755,true);
		}
		elseif( $empty === true ){
			
			$this->delete_static_contents( $postId );
			
			mkdir($static_dir,0755,true);
		}
	
		return $static_dir;
	}
	
	public function get_static_asset_dir($postId, $type = 'css'){
		
		$static_dir = $this->dirPath . $postId . '/assets/' . $type;
		
		if( !is_dir($static_dir) ){
			
			mkdir($static_dir,0755,true);
		}		
		
		return $static_dir;
	}	
	
	public function get_static_path( $postId, $defaultId ){
		
		$static_path = '';
		
		$layerStaticUrl = get_post_meta( $defaultId, 'layerStaticUrl', true );
		
		if( empty($layerStaticUrl) ){
			
			$layerStaticUrl = 'index.html';
		}
	
		return $this->get_static_dir( $postId ) . '/' . $layerStaticUrl;
	}
	
	public function get_static_asset_path( $postId, $type = 'css', $filename = 'style' ){
		
		$static_path = $this->get_static_asset_dir( $postId, $type ) . '/' . $filename . '.' . $type;
	
		return $static_path;
	}
	
	public function upload_image_template($source = 'php://input', $ext = 'zip'){
		
		if( $this->parent->user->loggedin && !empty($this->id) ){
			
			if ( !function_exists('media_handle_upload') ) {
				
				require_once(ABSPATH . "wp-admin" . '/includes/image.php');
				require_once(ABSPATH . "wp-admin" . '/includes/file.php');
				require_once(ABSPATH . "wp-admin" . '/includes/media.php');
			}
					
			// get file name
					
			$filename = 'image_template_' . $this->id ;
			
			// get psd content
			
			$tmp = wp_tempnam($source);
			
			$fi = fopen($source, 'rb');
			
			$p = JSON_decode(fread($fi, 2000)); // skip the first 2000 characters

			$fo = fopen($tmp,'wb');  

			while( $row = fread($fi,50000) ){
				
				fwrite($fo,$row);
			}
			
			fclose($fi);  
			fclose($fo);
			
			if( $ext == 'zip' ){
			
				// create archive
				
				$tmpa = wp_tempnam($tmp);
				
				$zip = new ZipArchive;
				
				if( $zip->open($tmpa, ZipArchive::CREATE ) === TRUE){
					
					// Add random.txt file to zip and rename it to newfile.txt
					
					$zip->addFile($tmp, $filename . '.psd');

					// All files are added, so close the zip file.
					
					$zip->close();
					
					@unlink($tmp);
					
					$tmp = $tmpa;
				}
			}			

			$file_array = array(
			
				'name' 		=> $filename . '.' . $ext,
				'tmp_name' 	=> $tmp,
			);
			
			$post_data = array(
			
				'post_title' => $filename,
			);
			
			$attach_id = media_handle_sideload( $file_array, $this->id, null, $post_data );
			
			@unlink($tmp);
			
			if( is_numeric($attach_id) ) {
				
				$this->delete_layer_attachments($this->id,2);
				
				return true;
			}
		}
		
		return false;
	}
	
	public function upload_static_contents($post_id){
		
		if( is_admin() ){
			
			if( !current_user_can('edit_page', $post_id) ){
				
				return $post_id;
			}
			
			if( !empty($_POST['layerStaticTpl_nonce']) ){
				
				//security verification
				
				if( !wp_verify_nonce($_POST['layerStaticTpl_nonce'], $this->parent->file) ) {
				  
					return $post_id;
				}

				// upload archive
				
				if( !empty( $_FILES['layerStaticTpl']['name'] ) ){
					
					// Setup the array of supported file types. In this case, it's just PDF.
					
					$supported_types = array('application/zip','application/tar');			
				
					// Get the file type of the upload
					
					$arr_file_type = wp_check_filetype(basename($_FILES['layerStaticTpl']['name']));
					
					$uploaded_type = $arr_file_type['type'];		
				
					// Check if the type is supported. If not, throw an error.
					
					if( in_array($uploaded_type,$supported_types) ){

						$zip = new ZipArchive;
						
						if( $res = $zip->open($_FILES['layerStaticTpl']['tmp_name']) ) {
							
							$zip->extractTo( $this->get_static_dir($post_id,true) . '/' );
							
							$zip->close();
							
							return $post_id;
						}
						else {
							
							wp_die("Error extracting the archive...");
						}
					}
					else{
						
						wp_die("The file type that you've uploaded is not an archive (zip, tar)");
					}
				}	
			}
			elseif( !empty($_POST['layerImageTpl_nonce']) ){
				
				//security verification
				
				if( !wp_verify_nonce($_POST['layerImageTpl_nonce'], $this->parent->file) ) {
				  
					//return $post_id;
				}
				
				// upload image template
				
				if( !empty( $_FILES['layerImageTpl']['name'] ) ){
					
					// Setup the array of supported file types. In this case, it's just PDF.
					
					$supported_types = array('image/vnd.adobe.photoshop','image/x-xcf');			
				
					// Get the file type of the upload
					
					$arr_file = wp_check_filetype(basename($_FILES['layerImageTpl']['name']));
					
					// Check if the type is supported. If not, throw an error.
					
					if( in_array($arr_file['type'],$supported_types) ){
						
						$file = 'layerImageTpl';
									
						if($_FILES[$file]['error'] !== UPLOAD_ERR_OK ) {
							
							if( intval($_FILES[$file]['error']) != 4 ){
								
								echo "upload error : " . $_FILES[$file]['error'];
								exit;
							}
						}
						else{
						
							//require the needed files
							
							require_once(ABSPATH . "wp-admin" . '/includes/image.php');
							require_once(ABSPATH . "wp-admin" . '/includes/file.php');
							require_once(ABSPATH . "wp-admin" . '/includes/media.php');
													
							// get file name
					
							$filename = 'image_template_' . $post_id;
								
							// create archive
							
							$ext = 'zip';
							
							$tmp = $_FILES[$file]['tmp_name'];
							
							$tmpa = wp_tempnam($tmp);
							
							$zip = new ZipArchive;
							
							if( $zip->open($tmpa, ZipArchive::CREATE ) === TRUE){
								
								// Add random.txt file to zip and rename it to newfile.txt
								
								$zip->addFile($tmp, $filename . '.psd');

								// All files are added, so close the zip file.
								
								$zip->close();
								
								//@unlink($tmp);
								
								$tmp = $tmpa;
								
								$file_array = array(
								
									'name' 		=> $filename . '.' . $ext,
									'tmp_name' 	=> $tmp,
								);
								
								$post_data = array(
								
									'post_title' => $filename,
								);
								
								$attach_id = media_handle_sideload( $file_array, $post_id, null, $post_data );
								
								if( is_numeric($attach_id) ){
									
									return $this->delete_layer_attachments($post_id,2);
								}
								elseif( !empty($attach_id['error']) ){
								
									wp_die($attach_id['error']);
								}
								else{
										
									wp_die('Error uploading template...');									
								}	
							}
						}						
					}
					else{
						
						wp_die("The file type that you've uploaded is not an image template (psd, xfc, sketch)");
					}
				}
			}
		}
	}
	
	public function delete_layer_attachments($post_id,$keep_last = 1){
		
		$attachments = $this->get_layer_attachments($post_id);
		
		if( count($attachments) > $keep_last ){
			
			$i = 1;
			
			foreach( $attachments as $attachment ){
				
				if( $i > $keep_last ){
					
					wp_delete_attachment( $attachment->ID, true );
				}
				
				++$i;
			}
		}
		
		return $post_id;
	}
	
	public function download_static_contents($post_id){
		
		// get the path
		
		$rootPath = $this->get_static_dir($post_id);
		
		// remove previous archive
		
		if( file_exists($rootPath . '/template.zip') ){
		
			unlink($rootPath . '/template.zip');
		}
		
		// get the archive
		
		$zip = new ZipArchive();
		$zip->open( $rootPath . '/template.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

		// Create recursive directory iterator
		
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach( $files as $name => $file ){
			
			// Skip directories (they would be added automatically)
			
			if ( !$file->isDir() ){
				
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}

		// Zip archive will be created only after closing object
		$zip->close();
		
		// output the archive	
		
		header('Content-type: application/zip');
		header('Content-Disposition: attachment; filename="template.zip"');
		header('Content-Length: ' . filesize($archive));
		
		echo file_get_contents( $rootPath . '/template.zip' );	
		
		// remove current archive
		
		unlink( $rootPath . '/template.zip' );		
		
		exit;
	}
	
	public function delete_static_contents($post_id){
		
		if( $post = get_post($post_id) ){
			
			if( $post->post_type == 'cb-default-layer' || $post->post_type == 'user-layer' ){
				
				$layer_type = $this->get_layer_type($post);
				
				if( $layer_type->output == 'hosted-page' || $layer_type->output == 'downloadable' ){
				
					$dir = $this->dir . $post_id . '/';
					
					if ( is_dir( $dir ) ){
						
						$it = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
						
						$files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );
						
						foreach ( $files as $file ) {
							
							if ( $file->isDir() ) {
								
								rmdir( $file->getRealPath() );
							}
							else {
								
								unlink( $file->getRealPath() );
							}
						}
						rmdir( $dir );
					}
				}
				elseif( $layer_type->output == 'image' ){
					
					$this->delete_layer_attachments($post->ID,0);
				}
			}
		}
		
		return true;
	}
	
	public function copy_dir($src,$dst) { 
	
		if( !is_dir($dst) ){
	
			$mkdir = mkdir($dst,0755,true);
		}
		
		$dir = opendir($src);
		
		while(false !== ( $file = readdir($dir)) ) { 
		
			if (( $file != '.' ) && ( $file != '..' )) { 
			
				if ( is_dir($src . '/' . $file) ) { 
				
					$this->copy_dir($src . '/' . $file,$dst . '/' . $file); 
				} 
				else{
					
					copy($src . '/' . $file,$dst . '/' . $file); 
				} 
			} 
		} 
		
		closedir($dir); 
		
		return true;
	} 
	
	public function copy_static_contents($defaultLayerId,$post_id){
		
		$src = $this->get_static_dir($defaultLayerId);
		$dst = $this->get_static_dir($post_id,true);

		return $this->copy_dir($src,$dst);
	}
	
	public function output_layer(){
		
		if( !empty($this->layerOutput) ){
		
			http_response_code(200);
		
			if( file_exists( $this->parent->views . '/layers/' . $this->layerOutput  . '.php' ) ){
				
				include_once( $this->parent->views . '/layers/' . $this->layerOutput  . '.php' );
			}
			else{
				
				do_action( 'ltple_' . $this->layerOutput . '_layer' );
			}
			
			do_action( 'ltple_layer_loaded', $layer );
		}
	}
	
	public function get_hosted_page_header(){
		
		global $post;
		
		if( !isset($_REQUEST['uri']) && $this->is_local_page($post) && !empty($this->layerOutput) && $this->layerOutput == 'hosted-page' ){
			
			echo $this->layerHeadContent;
		}
	}
	
	public function get_hosted_page_content($content){
		
		global $post;
		
		if( !isset($_REQUEST['uri']) && $this->is_local_page($post) && !empty($this->layerOutput) && $this->layerOutput == 'hosted-page' ){
			
			$content = $this->layerBodyContent;
		}
		
		return $content;
	}
	
	public function output_static_layer( $output ){

		if( !empty($output) ){
			
			if( isset($_GET['filetree']) && ( $this->layerOutput == 'hosted-page' || $this->layerOutput == 'downloadable' ) ){
				
				echo'<!DOCTYPE html>';

				echo'<head>';

					echo'<meta charset="utf-8">';
					echo'<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
					echo'<meta name="viewport" content="width=device-width, initial-scale=1">';
					
					echo'<title></title>';
					
					echo'<link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">';
					echo'<link href="https://fonts.googleapis.com/css?family=Quicksand" rel="stylesheet" type="text/css">';
					echo'<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">';
					echo'<link href="' . $this->parent->assets_url . 'css/filetree.css" rel="stylesheet" type="text/css">';
					
					echo'<style>';
					echo'body { background-color:#182f42; color:#fff; font-family:\'Quicksand\';}';
					echo'.container { margin:150px auto; max-width:640px;}';
					echo'</style>';
					
				echo'</head>';

				echo'<body>';
					
					echo'<div class="filetree">';
					
						echo $this->get_filetree( $this->layerStaticDir );

					echo'</div>';
					
					echo'<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>';
					echo'<script src="' . $this->parent->assets_url . 'js/filetree.js"></script>';

				echo'</body>';
				
				exit;
			}
			elseif(( $this->type == 'user-layer' || $this->type == 'cb-default-layer' ) && $this->layerOutput == 'downloadable' ){
			 
				// sanitize content
				
				$output = str_replace(array('<?php'),'',$output);
				
				// remove absolute image path
				
				$output = str_replace($this->parent->image->url,'assets/images/',$output);
				
				// store static output
				
				//if( $this->type == 'user-layer' || !file_exists($this->layerStaticPath) ){ 
				
					file_put_contents($this->layerStaticPath,$output);
				//}
				
				// store static css
				
				if( !empty( $this->defaultCss ) ){
				
					file_put_contents($this->defaultStaticCssPath,$this->defaultCss);
				}
				
				if( $this->type == 'user-layer' && $this->layerCss != $this->defaultCss ){
					
					file_put_contents($this->layerStaticCssPath,$this->layerCss);
				}					
				
				// store static js
				
				if( !empty( $this->defaultJs) ){
				
					file_put_contents($this->defaultStaticJsPath,$this->defaultJs);
				}
				
				if( $this->type == 'user-layer' && $this->layerJs != $this->defaultJs ){
					
					file_put_contents($this->layerStaticJsPath,$this->layerJs);
				}
				
				// output content
				
				if( isset($_GET['preview']) ){
					
					echo '<!DOCTYPE html>';
					
					echo '<head>';
					
						echo '<title>';
						
							echo 'Preview - ' . $this->title;
							
						echo '</title>';
					
					echo '</head>';
					
					echo '<body>';
					
						echo '<iframe src="' . $this->layerStaticUrl . '" style="position:fixed;top:0px;left:0px;bottom:0px;right:0px;width:100%;height:100%;border:none;margin:0;padding:0;overflow:hidden;z-index:999999;" />';
						
					echo '</body>';
				}
				else{
					
					wp_redirect($this->layerStaticUrl);exit;
					
					// add base
					
					$content  = '<head>' . PHP_EOL;
					$content .= '<base href="' . dirname($this->layerStaticUrl) . '/">';
					
					$output = str_replace('<head>',$content,$output);
					
					echo $output;
				}
			}
			else{
				
				echo $output;
			}
		}
	}

	public function get_filetree( $dir, $main = true ){   
		
		$filetree = '';
		
		if($main){

			$filetree .= '<ul class="main-tree">';
			
				//$dirname = basename($dir);
				
				$dirname = 'Template';
			
				$filetree .= '<li class="tree-title">' . $dirname . '</li>';
			
				$filetree .= $this->get_filetree($dir,false);
			
			$filetree .= '</ul>';
		}		
		else{
			
			$files = array_map('basename', glob( $dir . '/*' ));
			
			if( !empty($files) ){

				foreach( $files as $file ) {
					
					if( is_dir( $dir . '/' . $file ) ) {
						
						$filetree .= '<ul class="tree">';
					
							$filetree .= '<li class="tree-title">' . $file . '</li>';
					
							$filetree .= $this->get_filetree( $dir . '/' . $file, false );
					
						$filetree .= '</ul>';
					} 
					else{
						
						$filetree .= '<li class="tree-item">' . $file . '</li>';
					}
				}
			}
		}
		
		return $filetree;
	}
}
