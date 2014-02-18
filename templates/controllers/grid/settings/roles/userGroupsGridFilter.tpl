{**
 * templates/controllers/grid/settings/roles/userGroupsGridFilter.tpl
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}

<script type="text/javascript">
	// Attach the form handler to the form.
	$('#userGroupSearchForm').pkpHandler('$.pkp.controllers.form.ClientFormHandler',
		{ldelim}
			trackFormChanges: false
		{rdelim}
	);
</script>
<form class="pkp_form" id="userGroupSearchForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.roles.UserGroupGridHandler" op="fetchGrid"}" method="post">
	{formArea id="userGroupSearchFormArea"}
		{fbvElement type="select" id="selectedRoleId" from=$filterData.roleOptions selected=$filterSelectionData.selectedRoleId label="settings.roles.listRoles"}
		{fbvFormButtons id="searchButton" hideCancel="true" submitText="common.search"}
	{/formArea}
</form>
