<!-- templates/submission/index.tpl -->

{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Monograph index.
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{* FIXME: This page does not have a spec, just temporary layout, see #5849 *}
<div>
	{if $roleId == $smarty.const.ROLE_ID_AUTHOR}
		{init_tabs id="#submissions"}
		<div id="submissions">
			<ul>
				<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.author.AuthorSubmissionsListGridHandler" op="fetchGrid" status="active"}">{translate key="common.queue.short.active"}</a></li>
				<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.author.AuthorSubmissionsListGridHandler" op="fetchGrid" status="completed"}">{translate key="common.queue.short.completed"}</a></li>
			</ul>
		</div>
	{elseif $roleId == $smarty.const.ROLE_ID_PRESS_MANAGER || $roleId == $smarty.const.ROLE_ID_SERIES_EDITOR}
		{url|assign:editorSubmissionListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="fetchGrid"}
		{load_url_in_div id="editorSubmissionListGrid" url="$editorSubmissionListGridUrl"}
	{/if}
</div>

{include file="common/footer.tpl"}

<!-- / templates/submission/index.tpl -->

