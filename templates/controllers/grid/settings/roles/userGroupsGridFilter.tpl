<script type="text/javascript">
	// Attach the form handler to the form.
	$('#userGroupSearchForm').pkpHandler('$.pkp.controllers.form.ClientFormHandler');
</script>
<form class="pkp_form" id="userGroupSearchForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.roles.UserGroupGridHandler" op="fetchGrid"}" method="post">
	<div id="userGroupSearchFormArea">
		<label for="selectedRoleId">{translate key="settings.roles.listRoles"}: </label>
		<select name="selectedRoleId" id="selectedRoleId" size="1" class="selectMenu">
			{html_options_translate options=$filterData.roleOptions selected=$filterSelectionData.selectedRoleId}
		</select>
		<input type="submit" name="searchButton" id="searchButton" value="{translate key="common.search"}" class="button" />
	</div>
</form>