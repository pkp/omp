{**
 * common/pressSwitcher.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press switcher.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#pressSwitcher').pkpHandler('$.pkp.site.form.PressSwitcherFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="pressSwitcher" method="post" action="#">
	{fbvFormArea id="switcher"}
		{fbvFormSection}
			{fbvElement type="select" id="pressSwitcherSelect" from=$pressesNameAndUrl selected=$currentPressUrl translate=false}
		{/fbvFormSection}
	{/fbvFormArea}
</form>