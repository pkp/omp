{**
 * index.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display review signoffs.
 *
 * $Id$
 *
 *}
{strip}
{assign var="pageTitle" value="manager.reviewSignoff.process"} 
{include file="common/header.tpl"}
{/strip}
<ul class="menu">
	<li {if $reviewType eq "internal"}class="current"{/if}><a href="{url path="internal"}">{translate key="manager.reviewSignoff.internal"}</a></li>
	<li {if $reviewType eq "external"}class="current"{/if}<a href="{url path="external"}">{translate key="manager.reviewSignoff.external"}</a></li>
</ul>

<h3>{translate key="manager.reviewSignoff.`$reviewType`"}</h3>
{include file="manager/signoffGroups/users.tpl"}

{include file="manager/signoffGroups/groups.tpl"}

{include file="common/footer.tpl"}