{**
 * userGridFilter.tpl
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Filter template for user grid.
 *}
<script type="text/javascript">
	// Attach the form handler to the form.
	$('#userSearchForm').pkpHandler('$.pkp.controllers.form.ClientFormHandler');
</script>
<form id="userSearchForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.user.UserGridHandler" op="fetchGrid"}" method="post">
	<div id="userSearchFormArea">
		<select name="userGroup" id="userGroup" size="1" class="selectMenu">
			{html_options options=$userGroupOptions selected=$userGroup|escape}
		</select>
		<select name="searchField" id="searchField" size="1" class="selectMenu">
			{html_options_translate options=$fieldOptions selected=$searchField|escape}
		</select>
		<select name="searchMatch" id="searchMatch" size="1" class="selectMenu">
			{html_options_translate options=$matchOptions selected=$searchMatch|escape}
		</select>
		<input type="text" size="15" name="search" id="search" class="textField" value="{$search|escape}" />&nbsp;<input type="submit" name="searchButton" id="searchButton" value="{translate key="common.search"}" class="button" />
	</div>
</form>
<div class="pkp_helpers_clear">&nbsp;</div>
