{**
 * grid.tpl
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * JS addition to grid HTML markup to allow for row selection
 *
 *}
<script type="text/javascript">
	{literal}
        $(function(){
        // Select the revisions by default and hide all others; Also add a revision class so the filter button can work both ways
       	$('input:checkbox:checked').parent().parent().addClass('selected revision');
       	$('input:checkbox:not(:checked)').parent().parent().addClass('unrevised').hide();

		$('.reviewFilesSelect').live("click", (function() {
			if($(this).is(':checked')) {
				$(this).parent().parent().addClass('selected');
				$(this).attr('checked', true);
			} else {
				$(this).parent().parent().removeClass('selected');
				$(this).attr('checked', false);
			}
		}));

		// Handle the filter button
		$('#component-revisionsSelect-filter-button').live('click', (function () {
			if($('.unrevised').first().is(':visible')) {
				// If hiding the non-revised files, deselect them so they don't get accidentally added
				$('.unrevised').each(function() {
					$(this).find('.checkbox').attr('checked', false);
					$(this).removeClass('selected');
				});
			}
			$('.unrevised').toggle();
			return false;
		}));
	});
	{/literal}
</script>

{include file='core:controllers/grid/grid.tpl'}
