<!-- templates/dashboard/index.tpl -->

{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dashboard index.
 *
 * $Id$
 *}
 
{strip}
{assign var="pageTitle" value="dashboard.dashboard"}
{include file="common/header.tpl"}
{/strip}

<div class="unit size1of2">
	<h3>Temporary</h3>
<ul>
	<li><a href="{url page="user"}">User Home</a></li>
</ul>
</div>
<div class="unit size2of2 lastUnit">
	<h3></h3>
<ul>
	<li></li>
</ul>
</div>

{include file="common/footer.tpl"}

<!-- / templates/dashboard/index.tpl -->

