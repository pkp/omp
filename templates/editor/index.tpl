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

{init_tabs id="#submissions"}

<div id="submissions" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top"><a href="{url router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="fetchGrid" status="submissionsUnassigned"}">{translate key="common.queue.short.submissionsUnassigned"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="fetchGrid" status="submissionsInReview"}">{translate key="common.queue.short.submissionsInReview"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="fetchGrid" status="submissionsInEditing"}">{translate key="common.queue.short.submissionsInEditing"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url router=$smarty.const.ROUTE_COMPONENT component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="fetchGrid" status="submissionsArchives"}">{translate key="common.queue.short.submissionsArchives"}</a></li>
	</ul>
</div>


{url|assign:"informationCenterUrl" page='informationCenter' op='viewInformationCenter' fileId='4'}
{modal url="$informationCenterUrl" actOnType="nothing" actOnId="nothing" dialogText='informationCenter.informationCenter' button="#informationCenterButton"}
<a id="informationCenterButton" href="informationCenterUrl">{translate key="informationCenter.informationCenter"}</a>

{include file="common/footer.tpl"}
