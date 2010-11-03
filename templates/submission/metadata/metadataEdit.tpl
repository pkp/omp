{**
 * metadataEdit.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for changing metadata of a monograph.
 *}
{strip}
{assign var="pageTitle" value="submission.editMetadata"}
{include file="common/header.tpl"}
{/strip}

<form name="metadata" method="post" action="{url op="saveMetadata"}" enctype="multipart/form-data">
<input type="hidden" name="monographId" value="{$monographId|escape}" />
{include file="common/formErrors.tpl"}

{if $canViewAuthors}
{if count($formLocales) > 1}
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{url|assign:"formUrl" path=$monographId escape=false}
			{* Maintain localized author info across requests *}
			{foreach from=$authors key=authorIndex item=author}
				{foreach from=$author.biography key="thisLocale" item="thisBiography"}
					{if $thisLocale != $formLocale}<input type="hidden" name="authors[{$authorIndex|escape}][biography][{$thisLocale|escape}]" value="{$thisBiography|escape}" />{/if}
				{/foreach}
			{/foreach}
			{form_language_chooser form="metadata" url=$formUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
</table>
{/if}
{/if}
{include file="inserts/monographComponents/MonographComponentsInsert.tpl"}

<h3>{translate key="submission.titleAndAbstract"}</h3>

<table width="100%" class="data">
	<tr>
		<td width="20%" class="label">{fieldLabel name="title" required="true" key="monograph.title"}</td>
		<td width="80%" class="value"><input type="text" name="title[{$formLocale|escape}]" id="title" value="{$title[$formLocale]|escape}" size="60" maxlength="255" class="textField" /></td>
	</tr>

	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="monograph.description"}</td>
		<td class="value"><textarea name="abstract[{$formLocale|escape}]" id="abstract" rows="15" cols="60" class="textArea">{$abstract[$formLocale]|escape}</textarea></td>
	</tr>
</table>

<div class="separator"></div>

<h3>{translate key="submission.supportingAgencies"}</h3>

<p>{translate key="submission.submit.submissionSupportingAgenciesDescription"}</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="sponsor" key="submission.agencies"}</td>
		<td width="80%" class="value">
			<input type="text" name="sponsor[{$formLocale|escape}]" id="sponsor" value="{$sponsor[$formLocale]|escape}" size="60" maxlength="255" class="textField" />
		</td>
	</tr>
</table>


<div class="separator"></div>


<p><input type="submit" value="{translate key="submission.saveMetadata"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="history.go(-1)" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer.tpl"}

