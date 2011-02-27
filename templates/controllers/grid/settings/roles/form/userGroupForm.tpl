{**
 * templates/controllers/grid/settings/roles/form/userGroupForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to edit or create a user group
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#userGroupForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

{include file="common/formErrors.tpl"}

<form id="userGroupForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.roles.UserGroupGridHandler" op="updateUserGroup"}">
	{if $userGroupId}
		<input type="hidden" id="userGroupId" name="userGroupId" value="{$userGroupId|escape}" />
	{/if}
	{fbvFormArea id="userGroupDetails"}
		<h3>{translate key="settings.roles.roleDetails"}</h3>
		{fbvFormSection title="settings.roles.from" for="roleId" required="true"}
			{fbvSelect name="roleId" from=$roleOptions|escape id="roleId" selected=$roleId disabled=$disableRoleSelect}
		{/fbvFormSection}
		{fbvFormSection title="settings.roles.roleName" for="name[$formLocale]" required="true"}
			{fbvTextInput name="name[$formLocale]" value=$name[$formLocale]|escape id="name-$formLocale"}
		{/fbvFormSection}
		{fbvFormSection title="settings.roles.roleAbbrev" for="abbrev[$formLocale]" required="true"}
			{fbvTextInput name="abbrev[$formLocale]" value=$abbrev[$formLocale]|escape id="abbrev-$formLocale"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormArea id="userGroupAssignedStages"}
		<h3>{translate key="settings.roles.assignedStages"}</h3>
		{fbvFormSection title="settings.roles.stages"}
			{fbvSelect from=$stageOptions|escape name="assignedStages[]" id="assignedStages" selected=$assignedStages multiple=true}
		{/fbvFormSection}
	{/fbvFormArea}
	{include file="form/formButtons.tpl" submitText="common.save"}
</form>