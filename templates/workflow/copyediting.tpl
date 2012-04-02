{**
 * templates/workflow/copyediting.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Copyediting workflow stage
 *}
{strip}
{include file="workflow/header.tpl"}
{/strip}

<div id="copyediting">
	<p>{translate key="editor.monograph.editorial.introduction"}</p>

	<h3 class="pkp_grid_title">{translate key="submission.finalDraft"}</h3>
	
	<p class="pkp_grid_description">{translate key="editor.monograph.editorial.finalDraftDescription"}</p>
	
	{url|assign:finalDraftGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.final.FinalDraftFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId escape=false}
	{load_url_in_div id="finalDraftGrid" url=$finalDraftGridUrl}

	<h3 class="pkp_grid_title">{translate key="submission.copyediting"}</h3>
	
	<p class="pkp_grid_description">{translate key="editor.monograph.editorial.copyeditingDescription"}</p>

	{url|assign:copyeditingGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.copyedit.CopyeditingFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() escape=false}
	{load_url_in_div id="copyeditingGrid" class="update_target_signoff" url=$copyeditingGridUrl}

	<h3 class="pkp_grid_title">{translate key="editor.monograph.editorial.fairCopy"}</h3>
	
	<p class="pkp_grid_description">{translate key="editor.monograph.editorial.fairCopyDescription"}</p>

	{url|assign:fairCopyGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.fairCopy.FairCopyFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() escape=false}
	{load_url_in_div id="fairCopyGrid" url=$fairCopyGridUrl}

	<div class="grid_actions">
		{foreach from=$editorActions item=action}
			{include file="linkAction/linkAction.tpl" action=$action contextId="copyediting"}
		{/foreach}
	</div>
</div>
{include file="common/footer.tpl"}

