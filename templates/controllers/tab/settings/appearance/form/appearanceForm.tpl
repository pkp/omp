{**
 * controllers/tab/settings/appearance/form/appearanceForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Website appearance management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#appearanceForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="appearanceForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="saveFormData" tab="appearance"}" enctype="multipart/form-data">
	{include file="common/formErrors.tpl"}

	<h3>{translate key="manager.setup.pressHomepageHeader"}</h3>
	<p>{translate key="manager.setup.pressHomepageHeaderDescription"}</p>
	<h4>{translate key="manager.setup.pressName"}</h4>
	{fbvFormArea id="homepageHeader"}
		{fbvFormSection layout=$fbvStyles.layout.TWO_COLUMNS measure=$fbvStyles.measure.1OF2}
				{fbvElement type="radio" name="homeHeaderTitleType[$locale]" id="homeHeaderTitleType-0" value=0 checked=!$homeHeaderTitleType[$locale] label="manager.setup.useTextTitle"}
				{fbvElement type="text" name="homeHeaderTitle" id="homeHeaderTitle" value=$homeHeaderTitle multilingual=true}
		{/fbvFormSection}
		{fbvFormSection layout=$fbvStyles.layout.TWO_COLUMNS measure=$fbvStyles.measure.1OF2}
				{fbvElement type="radio" name="homeHeaderTitleType[$locale]" id="homeHeaderTitleType-1" value=1 checked=$homeHeaderTitleType[$locale] label="manager.setup.useImageTitle"}
				{fbvFileInput id="homeHeaderTitleImage" submit="uploadHomeHeaderTitleImage"}
				{if $homeHeaderTitleImage[$locale]}
					{translate key="common.fileName"}: {$homeHeaderTitleImage[$locale].name|escape} {$homeHeaderTitleImage[$locale].dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteHomeHeaderTitleImage" value="{translate key="common.delete"}" class="button" />
					<br />
					<img src="{$publicFilesDir}/{$homeHeaderTitleImage[$locale].uploadName|escape:"url"}" width="{$homeHeaderTitleImage[$locale].width|escape}" height="{$homeHeaderTitleImage[$locale].height|escape}" style="border: 0;" alt="{translate key="common.homePageHeader.altText"}" />
					<br />
					<table width="100%" class="data">
						<tr valign="top">
							<td width="20%" class="label">{fieldLabel name="homeHeaderTitleImageAltText" key="common.altText"}</td>
							<td width="80%" class="value"><input type="text" name="homeHeaderTitleImageAltText[{$locale|escape}]" value="{$homeHeaderTitleImage[$locale].altText|escape}" size="40" maxlength="255" class="textField" /></td>
						</tr>
						<tr valign="top">
							<td>&nbsp;</td>
							<td class="value"><span class="instruct">{translate key="common.altTextInstructions"}</span></td>
						</tr>
					</table>
				{/if}
		{/fbvFormSection}
	{/fbvFormArea}


	{include file="form/formButtons.tpl" submitText="common.save"}
</form>