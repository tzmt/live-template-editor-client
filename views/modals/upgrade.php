<?php
	
	$ltple = LTPLE_Client::instance();
	
	if( !empty($ltple->plan->options) ){
		
		$options = implode('|',$ltple->plan->options);
	}
	else{
		
		$options = $layer_type . '|' . $layer_range;
	}

	$checkout_url = add_query_arg( array(
		
		'output' 	=> 'widget',
		'options' 	=> $options,
	
	), $ltple->urls->checkout );
	
	echo '<div class="modal fade" id="upgrade_plan" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
		
		echo '<div class="modal-dialog modal-lg" role="document">'.PHP_EOL;
			
			echo '<div class="modal-content">'.PHP_EOL;
				
				echo '<div class="modal-header">'.PHP_EOL;
					
					echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'.PHP_EOL;
					
					echo '<h4 class="modal-title text-left" id="myModalLabel">Upgrade</h4>'.PHP_EOL;
				
				echo '</div>'.PHP_EOL;
			  
				echo '<div class="modal-body text-center">'.PHP_EOL;
					
					echo '<div class="loadingIframe" style="position:absolute;height:50px;width:100%;background-position:50% center;background-repeat: no-repeat;background-image:url(\'' . $ltple->server->url . '/c/p/live-template-editor-server/assets/loader.gif\');"></div>';

					echo '<iframe data-src="' . $checkout_url . '" style="width: 100%;position:relative;bottom: 0;border:0;height: 450px;overflow: hidden;"></iframe>';						
					
				echo '</div>'.PHP_EOL;

			echo '</div>'.PHP_EOL;
			
		echo '</div>'.PHP_EOL;
		
	echo '</div>'.PHP_EOL;