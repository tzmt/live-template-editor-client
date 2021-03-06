<?php 

	// get layer
	
	$layer  = '<!DOCTYPE html>';
	$layer .= '<html class="' . implode(' ',$this->layerStyleClasses) . '">';
	
	$layer .= '<head>';
	
		$layer .= '<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->';
		$layer .= '<!--[if lt IE 9]>';
		$layer .= '<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>';
		$layer .= '<![endif]-->';	

		$layer .= '<meta charset="UTF-8">';
		$layer .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
		
		$layer .= '<link rel="profile" href="//gmpg.org/xfn/11">';
		
		$layer .= '<link rel="dns-prefetch" href="//fonts.googleapis.com">';
		$layer .= '<link rel="dns-prefetch" href="//s.w.org">';
	
		$layer .= $this->layerHeadContent;
		
	$layer .= '<head>';

	$layer .= '<body style="background-color:#fff;padding:0;margin:0;display:flex !important;width:100%;overflow-x:hidden;">';
		
		$layer .= $this->layerBodyContent;
		
	$layer .='</body></html>' .PHP_EOL;