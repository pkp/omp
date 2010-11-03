{**
 * seriesForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to create/modify a series.
 *}
{strip}
{assign var="pageTitle" value="series.series"}
{assign var="pageCrumbTitle" value="series.series"}
{include file="common/header.tpl"}
{/strip}

<form name="series" method="post" action="{url op="updateSeries" path=$seriesId}" onsubmit="return checkEditorAssignments()">
<input type="hidden" name="editorAction" value="" />
<input type="hidden" name="userId" value="" />
<input type="hidden" name="seriesType" value="{$smarty.const.SERIES_SERIES}" />

{literal}
<script type="text/javascript">
<!--

function addEditor(editorId) {
	document.series.editorAction.value = "addEditor";
	document.series.userId.value = editorId;
	document.series.submit();
}
function removeEditor(editorId) {
	document.series.editorAction.value = "removeEditor";
	document.series.userId.value = editorId;
	document.series.submit();
}

function checkEditorAssignments() {
	var isOk = true;
	{/literal}
	{foreach from=$assignedEditors item=editorEntry}
	{assign var=editor value=$editorEntry.user}
	{literal}
		if (!document.series.canReview{/literal}{$editor->getId()}{literal}.checked && !document.series.canEdit{/literal}{$editor->getId()}{literal}.checked) {
			isOk = false;
		}
	{/literal}{/foreach}{literal}
	if (!isOk) {
		alert({/literal}'{translate|escape:"jsparam" key="manager.series.form.mustAllowPermission"}'{literal});
		return false;
	}
	return true;
}

// -->
</script>
{/literal}

{include file="common/formErrors.tpl"}

<table class="data" width="100%">
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{if $seriesId}{url|assign:"seriesFormUrl" op="editSeries" path=$seriesId}
			{else}{url|assign:"seriesFormUrl" op="createSeries" path=$seriesId}
			{/if}
			{form_language_chooser form="series" url=$seriesFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="title" required="true" key="series.title"}</td>
	<td width="80%" class="value"><input type="text" name="title[{$formLocale|escape}]" value="{$title[$formLocale]|escape}" id="title" size="40" maxlength="120" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="abbrev" required="true" key="series.abbreviation"}</td>
	<td class="value"><input type="text" name="abbrev[{$formLocale|escape}]" id="abbrev" value="{$abbrev[$formLocale]|escape}" size="20" maxlength="20" class="textField" />&nbsp;&nbsp;{translate key="series.abbreviation.example"}</td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="policy" key="manager.series.policy"}</td>
	<td class="value"><textarea name="policy[{$formLocale|escape}]" rows="4" cols="40" id="policy" class="textArea">{$policy[$formLocale]|escape}</textarea></td>
</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="printIssn" key="manager.setup.printIssn"}</td>
		<td width="80%" class="value"><input type="text" name="printIssn" id="printIssn" value="{$printIssn|escape}" size="8" maxlength="16" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="onlineIssn" key="manager.setup.onlineIssn"}</td>
		<td width="80%" class="value">
			<input type="text" name="onlineIssn" id="onlineIssn" value="{$onlineIssn|escape}" size="8" maxlength="16" class="textField" />
			<br />
			<span class="instruct">{translate key="manager.setup.issnDescription"}</span>
		</td>
	</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="reviewFormId" key="submission.reviewForm"}</td>
	<td class="value">
		<select name="reviewFormId" size="1" id="reviewFormId" class="selectMenu">
			<option value="">{translate key="manager.reviewForms.noneChosen"}</option>
			{html_options options=$reviewFormOptions selected=$reviewFormId}
		</select>
	</td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel suppressId="true" key="submission.indexing"}</td>
	<td class="value">
		<input type="checkbox" name="metaIndexed" id="metaIndexed" value="1" {if $metaIndexed}checked="checked"{/if} />
		{fieldLabel name="metaIndexed" key="manager.series.submissionIndexing"}
	</td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel suppressId="true" key="submission.restrictions"}</td>
	<td class="value">
		<input type="checkbox" name="editorRestriction" id="editorRestriction" value="1" {if $editorRestriction}checked="checked"{/if} />
		{fieldLabel name="editorRestriction" key="manager.series.editorRestriction"}
	</td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="hideAbout" key="navigation.about"}</td>
	<td class="value">
		<input type="checkbox" name="hideAbout" id="hideAbout" value="1" {if $hideAbout}checked="checked"{/if} />
		{fieldLabel name="hideAbout" key="manager.series.hideAbout"}
	</td>
</tr>
{if $commentsEnabled}
<tr valign="top">
	<td class="label">{fieldLabel name="disableComments" key="comments.readerComments"}</td>
	<td class="value">
		<input type="checkbox" name="disableComments" id="disableComments" value="1" {if $disableComments}checked="checked"{/if} />
		{fieldLabel name="disableComments" key="manager.series.disableComments"}
	</td>
</tr>
{/if}
</table>

<div class="separator"></div>

<h3>{translate key="user.role.seriesEditors"}</h3>
{url|assign:"seriesEditorsUrl" op="people" path="seriesEditors"|to_array}
<p><span class="instruct">{translate key="manager.series.seriesEditorInstructions" seriesEditorsUrl=$seriesEditorsUrl}</span></p>
<h4>{translate key="manager.series.unassigned"}</h4>

<table width="100%" class="listing" id="unassignedSeriesEditors">
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="20%">{translate key="user.username"}</td>
		<td width="60%">{translate key="user.name"}</td>
		<td width="20%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="3" class="headseparator">&nbsp;</td>
	</tr>
	{foreach from=$unassignedEditors item=editor}
		<tr valign="top">
			<td>{$editor->getUsername()|escape}</td>
			<td>{$editor->getFullName()|escape}</td>
			<td align="right">
				<a class="action" href="javascript:addEditor({$editor->getId()})">{translate key="common.add"}</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="3" class="nodata">{translate key="common.none"}</td>
		</tr>
	{/foreach}
	<tr>
		<td colspan="3" class="endseparator">&nbsp;</td>
	</tr>
</table>

<h4>{translate key="manager.series.assigned"}</h4>

<table width="100%" class="listing" id="assignedSeriesEditors">
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="20%">{translate key="user.username"}</td>
		<td width="40%">{translate key="user.name"}</td>
		<td width="10%" align="center">{translate key="submission.review"}</td>
		<td width="10%" align="center">{translate key="submission.editing"}</td>
		<td width="20%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="5" class="headseparator">&nbsp;</td>
	</tr>
	{foreach from=$assignedEditors item=editorEntry}
		{assign var=editor value=$editorEntry.user}
		<input type="hidden" name="assignedEditorIds[]" value="{$editor->getId()|escape}" />
		<tr valign="top">
			<td>{$editor->getUsername()|escape}</td>
			<td>{$editor->getFullName()|escape}</td>
			<td align="center"><input type="checkbox" {if $editorEntry.canReview}checked="checked"{/if} name="canReview{$editor->getId()}" /></td>
			<td align="center"><input type="checkbox" {if $editorEntry.canEdit}checked="checked"{/if} name="canEdit{$editor->getId()}" /></td>
			<td align="right">
				<a class="action" href="javascript:removeEditor({$editor->getId()})">{translate key="common.remove"}</a>
			</td>
		</tr>
	{foreachelse}
		<tr>
			<td colspan="5" class="nodata">{translate key="common.none"}</td>
		</tr>
	{/foreach}
	<tr>
		<td colspan="5" class="endseparator">&nbsp;</td>
	</tr>
</table>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="series" escape=false}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
{include file="common/footer.tpl"}

