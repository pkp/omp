{**
 * reviewForm.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to create/modify a review form.
 *
 *}

<form id="reviewFormForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.reviewForm.ReviewFormGridHandler" op="updateReviewForm"}">

<table class="data" width="100%">
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{if $reviewFormId}{url|assign:"reviewFormFormUrl" op="editReviewForm" path=$reviewFormId}
			{else}{url|assign:"reviewFormFormUrl" op="createReviewForm"}
			{/if}
			{form_language_chooser form="reviewFormForm" url=$reviewFormFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="title" required="true" key="manager.reviewForms.title"}</td>
	<td width="80%" class="value"><input type="text" name="title[{$formLocale|escape}]" value="{$title[$formLocale]|escape}" id="title" size="40" maxlength="120" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="description" key="manager.reviewForms.description"}</td>
	<td class="value"><textarea name="description[{$formLocale|escape}]" rows="4" cols="40" id="description" class="textArea richContent">{$description[$formLocale]|escape}</textarea></td>
</tr>
</table>

{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
{if $rowId}
	<input type="hidden" name="rowId" value="{$rowId|escape}" />
{/if}
{if $reviewFormId}
	<input type="hidden" name="reviewFormId" value="{$reviewFormId|escape}" />
{/if}

</form>

