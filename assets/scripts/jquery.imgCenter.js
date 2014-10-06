 /*
 * Image Centering
 * Copyright 2009 Drew Wilson
 * www.drewwilson.com
 *
 * Version 1.0   -   Updated: Oct. 10, 2009
 *
 * This Plug-In will center images inside of it's parent element.
 * By default it even scales each image up or down to fit inside it's parent element.
 * It will also wait to make sure each image is loaded before doing any re-sizing.
 *
 * This Image Centering jQuery plug-in is dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

(function($){
	$.fn.imgCenter = function(options) {

		var defaults = {
			scaleToFit: true,
			centerVertical: true,
			centerType: 'outside', // [ 'outside', 'inside']
			autoRedraw: false, // 
			complete: function(){},
			start: function(){},
			end: function(){}
		};
	 	var opts = $.extend(defaults, options);
	 	
		opts.start.call(this);
		
		// Get total number of items.
		var len = this.length - 1;
		
		return this.each(function(i){
			var current = i;
			var $img    = $(this);
			var $parent = $img.parent();

			if (opts.autoRedraw == true && $img.data('registerEvent') != true) {
				$(window).resize($.proxy(
					function(opts) {
						var $img = $(this);
						$img.data('imgCenterComplete', false);
						$img.imgCenter(opts);
					}
				, this, opts));
				$img.data('registerEvent', true);
			}
			
			if ($img.data('imgCenterComplete') == true) {
				return;
			}
			
			$parent.addClass('imgCenter');
			$parent.removeClass('imgCenterComplete');
			
			if (opts.centerType == 'outside') {
				$parent.addClass('imgCenterOutside');
			}
			else {
				$parent.addClass('imgCenterInside');
			}
			
			if ($parent.find('i').length == 0) {
				$img.before('<i class="fa fa-file-image-o fa-3x" />');
			}
			
			// reset properties
			$img.removeAttr('style');
			
			var parWidth   = parseInt($parent.actual('innerWidth'));
			var parHeight  = parseInt($parent.actual('innerHeight'));
			var parAspect  = parWidth / parHeight;
			
			$img.load($.proxy(
				function(event) {
					imgMath($(event.target));
				}
			, this));

			if($img[0].complete){
				imgMath($img);
			}

			function imgMath($img) {
				if (opts.centerType == 'outside') {
					// Get image properties.		
					var imgWidth 	= parseInt($img.get(0).naturalWidth);
					var imgHeight 	= parseInt($img.get(0).naturalHeight);
					var imgAspect 	= imgWidth / imgHeight;

					if (parAspect == Infinity) {
						parWidth  = imgWidth;
						parHeight = imgHeight;
					}
	
					// Center the image.
					if(parWidth != imgWidth || parHeight != imgHeight){
						if(opts.scaleToFit){
							if(parAspect >= 1){
								$img.css({'width': parWidth +'px'});
								imgWidth = parWidth;
								imgHeight = Math.round(imgWidth / imgAspect);
								
								if((parWidth / imgAspect) < parHeight){
									$img.css({'height': parHeight +'px', 'width': 'auto'});
									imgHeight = parHeight;
									imgWidth = Math.round(imgHeight * imgAspect);
								}				
							} else {
								$img.css({'height': parHeight +'px'});
								imgHeight = parHeight;
								imgWidth = Math.round(imgHeight * imgAspect);
								if((parHeight * imgAspect) < parWidth){
									$img.css({'width': parWidth +'px', 'height': 'auto'});
									imgWidth = parWidth;
									imgHeight = Math.round(imgWidth / imgAspect);
								}
							}
							if(imgWidth > parWidth){
								$img.css({'margin-left': '-'+ Math.round((imgWidth - parWidth) / 2) + 'px'});
							}
							if(imgHeight > parHeight && opts.centerVertical){
								$img.css({'margin-top': '-' + Math.round((imgHeight - parHeight) / 2) + 'px'});
							}		
						} else {
							if(imgWidth > parWidth){
								$img.css({'margin-left': '-' + Math.round((imgWidth - parWidth) / 2) + 'px'});
							} else if(imgWidth < parWidth){
								$img.css({'margin-left': Math.round((parWidth -imgWidth) / 2) + 'px'});
							}
							if(imgHeight > parHeight && opts.centerVertical){
								$img.css({'margin-top': '-' + Math.round((imgHeight - parHeight) / 2) + 'px'});
							} else if(imgHeight < parHeight && opts.centerVertical){
								$img.css({'margin-top': Math.round((parHeight - imgHeight) / 2) + 'px'});
							}
						}
					}
				}

				opts.complete.call(this, $img);
				if(current == len){
					opts.end.call(this);
				}

				$img.data('imgCenterComplete', true);
				$img.parent().addClass('imgCenterComplete');
			}
		});
	}
})(jQuery);
