{**
 * controllers/grid/users/user/form/userDisableForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display form to enable/disable a user.
 *}
 <script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#userDisableForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>
<form id="userDisableForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.user.UserGridHandler" op="disableUser"}">

	<input type="hidden" name="userId" value="{$userId|escape}" />
	<input type="hidden" name="enable" value="{$enable|escape}" />

	{if $enable}
		{fbvFormSection title="grid.user.enableReason" for="disableReason"}
			{fbvElement type="textarea" id="disableReason" value=$disableReason size=$fbvStyles.size.LARGE measure=$fbvStyles.measure.3OF4}
		{/fbvFormSection}
	{else}
		{fbvFormSection title="grid.user.disableReason" for="disableReason"}
			{fbvElement type="textarea" id="disableReason" value=$disableReason size=$fbvStyles.size.LARGE measure=$fbvStyles.measure.3OF4}
		{/fbvFormSection}
	{/if}

	{include file="form/formButtons.tpl"}
</form>
