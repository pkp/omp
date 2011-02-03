{**
 * users.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User management.
 *
 *}

<script type="text/javascript">
	<!--
	{literal}
	$(function() {
		// AJAX search form options
		var searchOptions = {
			// JSON data
			dataType:  'json',
			// Update the grid with search results
			success: function(returnString) {
				if (returnString.status == true) {
					$('#userGridContainer').html(returnString.content);
				}
			}
		};

		// Bind AJAX to search form submit event
		$('#userSearchForm').submit(
			function() {
				$(this).ajaxSubmit(searchOptions);
				// Since we're using AJAX, prevent standard browser form submit
				return false;
			}
		);
	});
	{/literal}
	// -->
</script>

<div class="pkp_helpers_clear">&nbsp;</div>

<form id="userSearchForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.user.UserGridHandler" op="fetchGrid"}" method="post">
<div id="userSearchFormArea">
	<select name="userGroup" id="userGroup" size="1" class="selectMenu">
		{html_options options=$userGroupOptions selected=$userGroup}
	</select>
	<select name="searchField" id="searchField" size="1" class="selectMenu">
		{html_options_translate options=$fieldOptions selected=$searchField}
	</select>
	<select name="searchMatch" id="searchMatch" size="1" class="selectMenu">
		{html_options_translate options=$matchOptions selected=$searchMatch}
	</select>
	<input type="text" size="15" name="search" id="search" class="textField" value="{$search|escape}" />&nbsp;<input type="submit" name="searchButton" id="searchButton" value="{translate key="common.search"}" class="button" />
</div>
</form>

{url|assign:usersUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.user.UserGridHandler" op="fetchGrid"}
{assign var=gridContainerId value="userGridContainer"}
{load_url_in_div id=$gridContainerId url=$usersUrl}
