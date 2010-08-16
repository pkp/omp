<!-- templates/editor/index.tpl -->

{**
 * index.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Monograph editor index.
 *
 *}
{strip}
{assign var="pageTitle" value="about.submissions"}
{include file="common/header.tpl"}
{/strip}

{url|assign:editorSubmissionListGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="fetchGrid"}
{load_url_in_div id="#editorSubmissionListGrid" url="$editorSubmissionListGridUrl"}

{include file="common/footer.tpl"}

<!-- / templates/editor/index.tpl -->

