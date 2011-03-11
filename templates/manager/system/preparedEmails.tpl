{**
 * preparedEmails.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Page for managing prepared emails.
 *}
{assign var="pageTitle" value="manager.system.preparedEmails"}
{include file="manager/system/systemHeader.tpl"}

{url|assign:preparedEmailsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.preparedEmails.preparedEmailsGridHandler" op="fetchGrid"}
{load_url_in_div id="preparedEmailsGridDiv" url=$preparedEmailsGridUrl}

{include file="common/footer.tpl"}
