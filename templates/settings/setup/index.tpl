<!-- templates/settings/setup/index.tpl -->

{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press setup index/intro.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="settings.setup.pressSetup"}
{include file="common/header.tpl"}
{/strip}

<h3>{translate key="settings.setup.stepsToPressSite"}</h3>

<ol>
	<li>
		<h4><a href="{url op="setup" path="1"}">{translate key="settings.setup.details"}</a></h4>
		{translate key="settings.setup.details.description"}<br/>
		&nbsp;
	</li>
	<li>
		<h4><a href="{url op="setup" path="2"}">{translate key="settings.setup.policies"}</a></h4>
		{translate key="settings.setup.policies.description"}<br/>
		&nbsp;
	</li>
	<li>
		<h4><a href="{url op="setup" path="3"}">{translate key="settings.setup.workflow"}</a></h4>
		{translate key="settings.setup.submissions.description"}<br/>
		&nbsp;
	</li>
	<li>
		<h4><a href="{url op="setup" path="4"}">{translate key="settings.setup.settings"}</a></h4>
		{translate key="settings.setup.management.description"}<br/>
		&nbsp;
	</li>
	<li>
		<h4><a href="{url op="setup" path="5"}">{translate key="settings.setup.look"}</a></h4>
		{translate key="settings.setup.look.description"}<br/>
		&nbsp;
	</li>
</ol>

{include file="common/footer.tpl"}

<!-- / templates/settings/setup/index.tpl -->

