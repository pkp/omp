{**
 * templates/dashboard/overview.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dashboard overview tab.
 *
 *}

<!-- Announcements grid -->
{url|assign:announcementGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.announcements.AnnouncementGridHandler" op="fetchGrid"}
{load_url_in_div id="announcementGridContainer" url="$announcementGridUrl"}
