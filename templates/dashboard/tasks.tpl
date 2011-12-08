{**
 * templates/dashboard/tasks.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dashboard tasks tab.
 *
 *}

{url|assign:notificationsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.notifications.NotificationsGridHandler" op="fetchGrid"}
{load_url_in_div id="notificationsGrid" url=$notificationsGridUrl}
