{**
 * copyediting.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Copyediting workflow stage
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{include file="workflow/header.tpl"}

<div class="ui-widget ui-widget-content ui-corner-all">

{url|assign:finalDraftGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.finalDraftFiles.FinalDraftFilesGridHandler" op="fetchGrid" monographId=$monograph->getId()}
{load_url_in_div id="finalDraftGrid" url=$finalDraftGridUrl}

<br />

{url|assign:copyeditingGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.copyeditingFiles.CopyeditingFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() canUpload=true canAddAuthor=true escape=false}
{load_url_in_div id="copyeditingGrid" url=$copyeditingGridUrl}

<br />

{url|assign:fairCopyGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.fairCopyFiles.FairCopyFilesGridHandler" op="fetchGrid" monographId=$monograph->getId() escape=false}
{load_url_in_div id="fairCopyGrid" url=$fairCopyGridUrl}

<br />

{include file="linkAction/legacyLinkAction.tpl" action=$editorActions[0] id="promoteAction"}

</div>
{include file="common/footer.tpl"}

