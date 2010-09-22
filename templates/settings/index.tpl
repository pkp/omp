{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Settings index.
 *
 * $Id$
 *}
 
{strip}
{assign var="pageTitle" value="settings.settings"}
{include file="common/header.tpl"}
{/strip}

<div class="unit size1of2">
	<h3>Press</h3>
	<a href="{url page="settings" op="setup"}">Setup</a>
</div>
<div class="unit size2of2 lastUnit">
	<h3>Data</h3>
	<a href="{url page="settings" op="data"}">Data</a>
</div>
<div class="unit size1of2">
	<h3>System</h3>
	<a href="{url page="settings" op="system"}">System</a>
</div>
<div class="unit size2of2 lastUnit">
	<h3>Users &amp; Roles</h3>
	<a href="{url page="settings" op="users"}">Users &amp; Roles</a>
</div>

{include file="common/footer.tpl"}

