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
			var org_image = $(this);
			
			if (opts.createFrame == true) {
				if (org_image.parent().hasClass('imgCenterFrame') == true) {
					var $div = org_image.parent();
					if ($div.find('i').length == 0) {
						$div.append('<i class="icon-spinner icon-spin icon-large" />');
					}
				}
				else {
					var $div = $('<div />').addClass('imgCenterFrame').append('<i class="icon-spinner icon-spin icon-large" />');
					$div.insertBefore(org_image).append(org_image).show().css('visibility', 'visible');
				}
			}
			
			org_image.hide();
			
			// Move up Parents until the spcified limit has been met.
			var theParent = org_image;
			for (var i=0; i <= opts.parentSteps; i++){
				theParent = theParent.parent();
			}	
			var parWidth 	= parseInt(theParent.width());
			var parHeight 	= parseInt(theParent.height());
			var parAspect 	= parWidth / parHeight;

			$(org_image).load($.proxy(
				function(event) {
					imgMath($(event.target));
				}
			, this));

			if(org_image[0].complete){
				imgMath(org_image);
			} 

			function imgMath(org_image) {
				// reset properties
				org_image.css({'margin': 0, 'width': 'auto', 'height': 'auto' });


					//var tmp = new Image()
//					var $imgTmp = $('<img />');
//					$imgTmp.attr('src', $(org_image).attr('src'));

				// Get image properties.		
				var imgWidth 	= parseInt(org_image.get(0).width);
				var imgHeight 	= parseInt(org_image.get(0).height);
				var imgAspect 	= imgWidth / imgHeight;
	
				// Center the image.
				if(parWidth != imgWidth || parHeight != imgHeight){
					theParent.css('overflow', 'hidden');
					
					if(opts.scaleToFit){
						if(parAspect >= 1){
							org_image.css({'width': parWidth +'px'});
							imgWidth = parWidth;
							imgHeight = Math.round(imgWidth / imgAspect);
							
							if((parWidth / imgAspect) < parHeight){
								org_image.css({'height': parHeight +'px', 'width': 'auto'});
								imgHeight = parHeight;
								imgWidth = Math.round(imgHeight * imgAspect);
							}				
						} else {
							org_image.css({'height': parHeight +'px'});
							imgHeight = parHeight;
							imgWidth = Math.round(imgHeight * imgAspect);
							if((parHeight * imgAspect) < parWidth){
								org_image.css({'width': parWidth +'px', 'height': 'auto'});
								imgWidth = parWidth;
								imgHeight = Math.round(imgWidth / imgAspect);
							}
						}
						if(imgWidth > parWidth){
							org_image.css({'margin-left': '-'+ Math.round((imgWidth - parWidth) / 2) + 'px'});
						}
						if(imgHeight > parHeight && opts.centerVertical){
							org_image.css({'margin-top': '-' + Math.round((imgHeight - parHeight) / 2) + 'px'});
						}		
					} else {
						if(imgWidth > parWidth){
							org_image.css({'margin-left': '-' + Math.round((imgWidth - parWidth) / 2) + 'px'});
						} else if(imgWidth < parWidth){
							org_image.css({'margin-left': Math.round((parWidth -imgWidth) / 2) + 'px'});
						}
						if(imgHeight > parHeight && opts.centerVertical){
							org_image.css({'margin-top': '-' + Math.round((imgHeight - parHeight) / 2) + 'px'});
						} else if(imgHeight < parHeight && opts.centerVertical){
							org_image.css({'margin-top': Math.round((parHeight - imgHeight) / 2) + 'px'});
						}
					}
				}
				

				$(org_image).fadeIn('slow', function() {
					$(org_image).parent().find('i').remove();
				});

				opts.complete.call(this, org_image);
				if(current == len){
					opts.end.call(this);
				}
				if (opts.show == true) {
					org_image.show();
				}
			}
			
		});		
	}
})(jQuery);
