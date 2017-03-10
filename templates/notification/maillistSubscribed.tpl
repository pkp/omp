{**
 * index.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays the notification settings page and unchecks
 *
 *}
{strip}
{assign var="pageTitle" value="notification.mailList"}
{include file="frontend/components/header.tpl"}
{/strip}

<ul>
	<li class="pkp_form">
		<span{if $error} class="pkp_form_error"{/if}>
			{translate key="notification.$status"}
		</span>
	</li>
<ul>

{include file="frontend/components/footer.tpl"}

