{**
 * grid.tpl
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * JS addition to grid HTML markup to allow for row selection
 *}
<script type="text/javascript">
	{literal}
        $(function(){
	       	$('input:checkbox:checked').parent().parent().addClass('selected');

	       	// Check all by default
	       	$("input.reviewFilesSelect").attr('checked', true);

            // Highlight the row when checkbox is clicked
			$('.reviewFilesSelect').live("click", function() {
				$this = $(this);
				if($this.is(':checked')) {
					$this.parent().parent().addClass('selected');
					$this.attr('checked', true);
				} else {
					$this.parent().parent().removeClass('selected');
					$this.attr('checked', false);
				}
			});
		});
	{/literal}
</script>

{include file='core:controllers/grid/grid.tpl'}
