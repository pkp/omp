{**
 * templates/dashboard/status.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dashboard status tab.
 *}

<!-- New Submission entry point -->
{if $pressCount > 1}
	<script type="text/javascript">
		$(function() {ldelim}
			$("#pressSelect").change(function() {ldelim}
				if($("#pressSelect option:selected").val() == 0) return false; // User has select the default text; do nothing
				window.location.href = $("#pressSelect option:selected").val();
			{rdelim});
		{rdelim});
	</script>
	<h3 class="pkp_helpers_align_left">{translate key="submission.submit.newSubmissionMultiple"}</h3>
	<select id="pressSelect" class="pkp_helpers_align_left deprecated_selectHeader">
		<option value="0">{translate key="submission.submit.selectAPress"}</option>
		{iterate from=presses item=press}
			<option value="{url press=$press->getPath() page="submission" op="wizard"}">{$press->getLocalizedName()|escape}</option>
		{/iterate}
	</select>
	<div class="pkp_helpers_clear"></div>
{else}
	<h3><a href="{url press=$press->getPath() page="submission" op="wizard"}" class="add_item">{translate key="submission.submit.newSubmissionSingle" pressName=$press->getLocalizedName()}</a></h3>
{/if}

<!-- Author and editor submissions grid -->
{if array_intersect(array(ROLE_ID_AUTHOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR), $userRoles)}
	{url|assign:mySubmissionsListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.mySubmissions.MySubmissionsListGridHandler" op="fetchGrid"}
	{load_url_in_div id="mySubmissionsListGridContainer" url="$mySubmissionsListGridUrl"}
{/if}

<!-- Unassigned submissions grid: If the user is a press manager or a series editor, then display these submissions which have not been assigned to anyone -->
{if array_intersect(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR), $userRoles)}
	{url|assign:unassignedSubmissionsListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.unassignedSubmissions.UnassignedSubmissionsListGridHandler" op="fetchGrid"}
	{load_url_in_div id="unassignedSubmissionsListGridContainer" url="$unassignedSubmissionsListGridUrl"}
{/if}

<!-- Assigned submissions grid: Show all submissions the user is assigned to (besides their own) -->
{url|assign:assignedSubmissionsListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.assignedSubmissions.AssignedSubmissionsListGridHandler" op="fetchGrid"}
{load_url_in_div id="assignedSubmissionsListGridContainer" url="$assignedSubmissionsListGridUrl"}
