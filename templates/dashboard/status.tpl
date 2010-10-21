{**
 * status.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dashboard status tab.
 *
 *}

<!-- Author submissions grid -->
{url|assign:mySubmissionsListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.mySubmissions.MySubmissionsListGridHandler" op="fetchGrid"}
{load_url_in_div id="mySubmissionsListGridContainer" url="$mySubmissionsListGridUrl"}

<!-- Unassigned submissions grid: If the user is a manager, display these submissions which have not been assigned to anyone -->
{url|assign:unassignedSubmissionsListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.unassignedSubmissions.UnassignedSubmissionsListGridHandler" op="fetchGrid"}
{load_url_in_div id="unassignedSubmissionsListGridContainer" url="$unassignedSubmissionsListGridUrl"}

<!-- Assigned submissions grid: Show all submissions the user is assigned to (besides their own) -->
{url|assign:assignedSubmissionsListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.assignedSubmissions.AssignedSubmissionsListGridHandler" op="fetchGrid"}
{load_url_in_div id="assignedSubmissionsListGridContainer" url="$assignedSubmissionsListGridUrl"}