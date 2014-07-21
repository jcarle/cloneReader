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
			parentSteps: 0,
			scaleToFit: true,
			centerVertical: true,
			show: true,
			createFrame: false,
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
			
			// Declare the current Image as a variable.
			var $img = $(this);
			
			if (opts.createFrame == true) {
				if ($img.parent().hasClass('imgCenterFrame') == true) {
					var $div = $img.parent();
					if ($div.find('i').length == 0) {
						$div.append('<i class="fa fa-spinner fa-spin fa-lg" />');
					}
				}
				else {
					var $div = $('<div />').addClass('imgCenterFrame');
					$div.insertBefore($img).append($img).show().css('visibility', 'visible');
					$div.append('<i class="fa fa-spinner fa-spin fa-lg" />')
				}
			}

			$img.hide();
			
			// Move up Parents until the spcified limit has been met.
			var $theParent = $img;
			for (var i=0; i <= opts.parentSteps; i++){
				$theParent = $theParent.parent();
			}
			var parWidth 	= parseInt($theParent.width());
			var parHeight 	= parseInt($theParent.height());
			var parAspect 	= parWidth / parHeight;
			
			$img.load($.proxy(
				function(event) {
					imgMath($(event.target));
				}
			, this));

			if($img[0].complete){
				imgMath($img);
			}

			function imgMath($img) {
				// reset properties
				$img.css({'margin': 0, 'width': 'auto', 'height': 'auto' });
				
				// Get image properties.		
				var imgWidth 	= parseInt($img.get(0).width);
				var imgHeight 	= parseInt($img.get(0).height);
				var imgAspect 	= imgWidth / imgHeight;
				
				if (parAspect == Infinity) {
					parWidth  = imgWidth;
					parHeight = imgHeight;
				}

				// Center the image.
				if(parWidth != imgWidth || parHeight != imgHeight){
					$theParent.css('overflow', 'hidden');
					
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

				opts.complete.call(this, $img);
				if(current == len){
					opts.end.call(this);
				}
				
				if (opts.show == true) {
					$img.show();
					$img.parent().find('i').remove();
				}
				else {
					$img.fadeIn('slow', function() {
						var $icon = $(this).parent().find('i');
						$icon.remove();
					});
				}
			}
		});
	}
})(jQuery);
