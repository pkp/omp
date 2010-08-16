<!-- templates/reviewer/index.tpl -->

{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Reviewer index.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="about.submissions"}
{include file="common/header.tpl"}
{/strip}

{init_tabs id="#submissions"}

<div id="submissions" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top"><a href="{url router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.reviewer.ReviewerSubmissionsListGridHandler" op="fetchGrid" status="active"}">{translate key="common.queue.short.active"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.reviewer.ReviewerSubmissionsListGridHandler" op="fetchGrid" status="completed"}">{translate key="common.queue.short.completed"}</a></li>
	</ul>
</div>

</div>

{include file="common/footer.tpl"}

<!-- / templates/reviewer/index.tpl -->

