{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Layout editor index.

 *}
{strip}
{assign var="pageTitle" value="user.role.designer"}
{include file="common/header.tpl"}
{/strip}

<h3>{translate key="manuscript.submissions"}</h3>

<ul class="plain">
	<li>&#187; <a href="{url op="submissions" path="active"}">{translate key="common.queue.short.active"}</a></li>
	<li>&#187; <a href="{url op="submissions" path="completed"}">{translate key="common.queue.short.completed"}</a></li>
</ul>

{include file="common/footer.tpl"}

