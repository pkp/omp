{**
 * javascriptInit.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Contains all javascript that needs to be initialized on page load.
 *}
	<script type="text/javascript">{literal}
		<!--
		// initialise plugins
		$(function(){
			$('ul.sf-menu').superfish(); // Initialize the navigation menu
			jqueryValidatorI18n("{/literal}{$baseUrl}{literal}", "{/literal}{$currentLocale}{literal}"); // include the appropriate validation localization
			fontSize("#sizer", ".page", 9, 12, 20); // Initialize the font sizer
			$('.button').button();
			$('a.settings').live("click", (function() { // Initialize grid settings button handler
				$(this).parent().siblings('.row_controls').toggle(300);
			}));
			{/literal}{if $validateId}{literal}
			$("form#{/literal}{$validateId}{literal}").validate({
				errorClass: "error",
				highlight: function(element, errorClass) {
					$(element).parent().parent().addClass(errorClass);
				},
				unhighlight: function(element, errorClass) {
					$(element).parent().parent().removeClass(errorClass);
				}
			});
			{/literal}{/if}{literal}
			$("a.openHelp").each(function(){
				$(this).click(function() {openHelp($(this).attr('href')); return false;})
			});
			$('div.grid').live('mouseover', function(){
				// Since grids are loaded after the DOM is, we have to attach the image preview to a live event
				$('div.grid').die('mouseover');
				$('a.imageFile').imgPreview({
					preloadImages: false,
					imgCSS: { width: 300 }
				});
			});
			$(".tagit").live('click', function() {
				$(this).find('input').focus();
			});
		});
		// -->
	{/literal}</script>

