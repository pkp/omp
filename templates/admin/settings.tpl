{**
 * settings.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site settings form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#siteSetupForm').pkpHandler('$.pkp.controllers.tab.settings.form.RefreshableFileFormHandler',
			{ldelim}
				fetchFileUrl: '{url|escape:javascript op='fetchFile' tab='siteSetup' escape=false}',
			{rdelim}
		);
	{rdelim});
</script>

<form id="siteSetupForm" class="pkp_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.AdminSettingsTabHandler" op="saveSettings" tab="siteSetup"}" enctype="multipart/form-data">
{include file="common/formErrors.tpl"}

	{fbvElement name="label" key="admin.settings.siteTitle" required="true"}
	{fbvFormArea id="siteTitle"}
		{fbvFormSection list=true}
			{fbvElement type="radio" name="pageHeaderTitleType[$locale]" id="pageHeaderTitleType-0" value=0 checked=!$pageHeaderTitleType[$locale] label="manager.setup.useTextTitle"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" name="title" id="title" value=$title multilingual=true required=true}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{fbvElement type="radio" name="pageHeaderTitleType[$locale]" id="pageHeaderTitleType-1" value=1 checked=$pageHeaderTitleType[$locale] label="manager.setup.useImageTitle"}
		{/fbvFormSection}
		{fbvFormSection}
			<div id="{$uploadImageLinkActions.pageHeaderTitleImage->getId()}" class="pkp_linkActions">
				{include file="linkAction/linkAction.tpl" action=$uploadImageLinkActions.pageHeaderTitleImage contextId="siteSetupForm"}
			</div>
			<div id="pageHeaderTitleImage" {if !$pageHeaderTitleImage[$locale]}class="pkp_form_hidden"{/if}>
				<div class="refreshable_file_wrapper">
					{$imagesViews.pageHeaderTitleImage}
				</div>
				{fbvElement type="text" label="common.altText" name="pageHeaderTitleImageAltText[$locale]" id="pageHeaderTitleImageAltText[$locale]" value=$pageHeaderTitleImage[$locale].altText}
				<span class="instruct">{translate key="common.altTextInstructions"}</span>
			</div>
		{/fbvFormSection}
	{/fbvFormArea}

<table class="data">
	<tr valign="top">
		<td {if $pageHeaderTitleType[$formLocale] && $pageHeaderTitleImage[$formLocale]}rowspan="4"{else}rowspan="3"{/if} width="20%" class="label">{fieldLabel name="title" key="admin.settings.siteTitle" required="true"}</td>
		<td width="15%" class="value">
			<input type="radio" name="pageHeaderTitleType[{$formLocale|escape}]" id="pageHeaderTitleType-0" value="0"{if not $pageHeaderTitleType[$formLocale]} checked="checked"{/if} /> {fieldLabel name="pageHeaderTitleType-0" key="manager.setup.useTextTitle"}
		</td>
		<td width="65%" class="value">
			<input type="text" id="title" name="title[{$formLocale|escape}]" value="{$title[$formLocale]|escape}" size="40" maxlength="120" class="textField" />
		</td>
	</tr>
	<tr valign="top">
		<td class="label" width="20%"><input type="radio" name="pageHeaderTitleType[{$formLocale|escape}]" id="pageHeaderTitleType-1" value="1"{if $pageHeaderTitleType[$formLocale]} checked="checked"{/if} /> {fieldLabel name="pageHeaderTitleType-1" key="manager.setup.useImageTitle"}</td>
		<td colspan="2" width="80%" class="value">{fbvElement type="file" id="pageHeaderTitleImage" submit="uploadPageHeaderTitleImage"} </td>
	</tr>
	<tr valign="top">
		<td colspan="2">
			{if $pageHeaderTitleType[$formLocale] && $pageHeaderTitleImage[$formLocale]}
				{translate key="common.fileName"}: {$pageHeaderTitleImage[$formLocale].originalFilename|escape} {$pageHeaderTitleImage[$formLocale].dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deletePageHeaderTitleImage" value="{translate key="common.delete"}" class="button" />
				<br />
				<img src="{$publicFilesDir}/{$pageHeaderTitleImage[$formLocale].uploadName|escape:"url"}" width="{$pageHeaderTitleImage[$formLocale].width|escape}" height="{$pageHeaderTitleImage[$formLocale].height|escape}" style="border: 0;" alt="{translate key="admin.settings.homeHeaderImage.altText"}" />
			{/if}
		</td>
	</tr>
	{if $pageHeaderTitleType[$formLocale] && $pageHeaderTitleImage[$formLocale]}
		<tr valign="top">
			<td class="label">{fieldLabel name="pageHeaderTitleImageAltText" key="common.altText"}</td>
			<td colspan="2" width="80%" class="value">
				<input type="text" id="pageHeaderTitleImageAltText" name="pageHeaderTitleImageAltText[{$formLocale|escape}]" value="{$pageHeaderTitleImage[$formLocale].altText|escape}" size="40" maxlength="255" class="textField" />
			</td>
		</tr>
		<tr valign="top">
			<td>&nbsp;</td>
			<td colspan="2" class="value"><span class="instruct">{translate key="common.altTextInstructions"}</span></td>
		</tr>
	{/if}
	<tr valign="top">
		<td class="label">{fieldLabel name="intro" key="admin.settings.introduction"}</td>
		<td colspan="2" class="value"><textarea name="intro[{$formLocale|escape}]" id="intro" cols="40" rows="10" class="textArea richContent">{$intro[$formLocale]|escape}</textarea></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="redirect" key="admin.settings.pressRedirect"}</td>
		<td colspan="2" class="value">
			<select name="redirect" id="redirect" size="1" class="selectMenu">
				<option value="">{translate key="admin.settings.noPressRedirect"}</option>
				{html_options options=$redirectOptions selected=$redirect}
			</select>
		</td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td colspan="2" class="value"><span class="instruct">{translate key="admin.settings.pressRedirectInstructions"}</span></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="aboutField" key="admin.settings.aboutDescription"}</td>
		<td colspan="2" class="value"><textarea name="about[{$formLocale|escape}]" id="aboutField" cols="40" rows="10" class="textArea richContent">{$about[$formLocale]|escape}</textarea></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="contactName" key="admin.settings.contactName" required="true"}</td>
		<td colspan="2" class="value"><input type="text" id="contactName" name="contactName[{$formLocale|escape}]" value="{$contactName[$formLocale]|escape}" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="contactEmail" key="admin.settings.contactEmail" required="true"}</td>
		<td colspan="2" class="value"><input type="text" id="contactEmail" name="contactEmail[{$formLocale|escape}]" value="{$contactEmail[$formLocale]|escape}" size="40" maxlength="90" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="minPasswordLength" key="admin.settings.minPasswordLength" required="true"}</td>
		<td colspan="2" class="value"><input type="text" id="minPasswordLength" name="minPasswordLength" value="{$minPasswordLength|escape}" size="4" maxlength="2" class="textField" /> {translate key="admin.settings.passwordCharacters"}</td>
	</tr>
	<tr>
		<td width="20%" valign="top" class="label">{translate key="admin.settings.siteStyleSheet"}</td>
		<td colspan="2" width="80%" valign="top" class="value">
			{fbvElement type="file" id="siteStyleSheet" submit="uploadSiteStyleSheet"}
			{if $siteStyleFileExists}
				<br />
				{translate key="common.fileName"}: <a href="{$publicFilesDir}/{$styleFilename}" class="file">{$originalStyleFilename|escape}</a> {$dateStyleFileUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteSiteStyleSheet" value="{translate key="common.delete"}" class="button" />
			{/if}
		</td>
	</tr>
</table>

<br />

<h4>{translate key="admin.settings.oaiRegistration"}</h4>

{url|assign:"oaiUrl" page="oai"}
{url|assign:"siteUrl" page="index"}
<p>{translate key="admin.settings.oaiRegistrationDescription" siteUrl=$siteUrl oaiUrl=$oaiUrl}</p>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url page="admin" escape=false}'" /></p>

</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}

