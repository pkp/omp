{**
 * reviewFormElementForm.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to create/modify a review form element.
 *
 *}

{if $reviewFormElementId}
	{assign var="possibleResponsesWrapperId" value="possibleResponsesWrapper-"|concat:$reviewFormElementId}
{else}
	{assign var="possibleResponsesWrapperId" value="possibleResponsesWrapper-0"}
{/if}

<script type="text/javascript">
{literal}
<!--
function togglePossibleResponses(newValue) {
	multipleResponsesElementTypesString = {/literal}"{$multipleResponsesElementTypesString}";{literal}
	if (multipleResponsesElementTypesString.indexOf(';'+newValue+';') != -1) {
		document.getElementById({/literal}"{$possibleResponsesWrapperId}"{literal}).style.display="block";
	} else {
		document.getElementById({/literal}"{$possibleResponsesWrapperId}"{literal}).style.display="none";
	}
}
// -->
{/literal}
</script>

<form class="pkp_form" id="reviewFormElementForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.reviewForm.ReviewFormElementGridHandler" op="updateReviewFormElement"}">

<table class="data" width="100%">
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{if $reviewFormElementId}{url|assign:"reviewFormElementFormUrl" op="editReviewFormElement" path=$reviewFormId|to_array:$reviewFormElementId escape=false}
			{else}{url|assign:"reviewFormElementFormUrl" op="createReviewFormElement" path=$reviewFormId path=$reviewFormId escape=false}
			{/if}
			{form_language_chooser form="reviewFormElementForm" url=$reviewFormElementFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
<tr valign="top">
	<td class="label">{fieldLabel name="question" required="true" key="manager.reviewFormElements.question"}</td>
	<td class="value"><textarea name="question[{$formLocale|escape}]" rows="4" cols="40" id="question" class="textArea">{$question[$formLocale]|escape}</textarea></td>
</tr>
<tr valign="top">
	<td>&nbsp;</td>
	<td class="value">
		<input type="checkbox" name="required" id="required" value="1" {if $required}checked="checked"{/if} />
		{fieldLabel name="required" key="manager.reviewFormElements.required"}
	</td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="elementType" required="true" key="manager.reviewFormElements.elementType"}</td>
	<td class="value">
		<select name="elementType" id="elementType" class="selectMenu" size="1" onchange="togglePossibleResponses(this.options[this.selectedIndex].value)">{html_options_translate options=$reviewFormElementTypeOptions selected=$elementType}</select>
	</td>
</tr>
</table>

<div id="{$possibleResponsesWrapperId}"{if !in_array($elementType, $multipleResponsesElementTypes)} style="display:none"{/if}>
	{** TODO: add delayed-write listbuilder or something similar **}
	<input type="submit" name="addResponse" value="{translate key="manager.reviewFormElements.addResponseItem"}" class="button" disabled="disabled"/>
</div>

{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
{if $rowId}
	<input type="hidden" name="rowId" value="{$rowId|escape}" />
{/if}
{if $reviewFormId}
	<input type="hidden" name="reviewFormId" value="{$reviewFormId|escape}" />
{/if}
{if $reviewFormElementId}
	<input type="hidden" name="reviewFormElementId" value="{$reviewFormElementId|escape}"/>
{/if}

</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
