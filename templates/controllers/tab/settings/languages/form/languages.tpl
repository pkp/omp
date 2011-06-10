{**
 * controllers/tab/settings/languages/form/languages.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to edit press language settings.
 *}

 <script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#languagesForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form pkp_controllers_form" id="languagesForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="saveFormData" tab="languages"}">
{include file="common/formErrors.tpl"}

	<p>{translate key="manager.languages.languageInstructions"}</p>

	{if count($availableLocales) > 1}
		{fbvFormArea id="primaryPressLocale"}
			{fbvFormSection title="locale.primary" for="primaryLocale" required=true}
				{fbvElement type="select" id="primaryLocale" name="primaryLocale" required=true from=$availableLocales selected=$primaryLocale translate=false}
				<span class="instruct">{translate key="manager.languages.primaryLocaleInstructions"}</span>
			{/fbvFormSection}
		{/fbvFormArea}
		{fbvFormArea id="supportedPressLocales"}
			{fbvFormSection title="locale.supported" for="supportedPressLocalesOptions"}
				<table class="data" id="supportedPressLocalesOptions" width="100%">
					<tr valign="top">
						<td width="20%">&nbsp;</td>
						<td align="center" width="10%">{translate key="manager.language.ui"}</td>
						<td align="center" width="20%">{translate key="manager.language.submissions"}</td>
						<td align="center" width="10%">{translate key="manager.language.forms"}</td>
						<td>&nbsp;</td>
					</tr>
				{foreach from=$availableLocales key=localeKey item=localeName}
					<tr>
						<td>{$localeName|escape}</td>
						<td align="center"><input type="checkbox" name="supportedLocales[]" value="{$localeKey|escape}"{if in_array($localeKey, $supportedLocales)} checked="checked"{/if}/></td>
						<td align="center"><input type="checkbox" name="supportedSubmissionLocales[]" value="{$localeKey|escape}"{if in_array($localeKey, $supportedSubmissionLocales)} checked="checked"{/if}/></td>
						<td align="center"><input type="checkbox" name="supportedFormLocales[]" value="{$localeKey|escape}"{if in_array($localeKey, $supportedFormLocales)} checked="checked"{/if}/></td>
						<td>
							<div id="{$reloadDefaultsLinkActions[$localeKey]->getId()}" class="pkp_linkActions">
								{include file="linkAction/linkAction.tpl" action=$reloadDefaultsLinkActions[$localeKey] contextId="languagesForm"}
							</div>
						</td>
					</tr>
				{/foreach}
				</table>
				<span class="instruct">{translate key="manager.languages.supportedLocalesInstructions"}</span>
			{/fbvFormSection}
		{/fbvFormArea}
		<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
		{include file="form/formButtons.tpl" submitText="common.save"}
	{else}
		<div class="separator"></div>
		<p>{translate key="manager.languages.noneAvailable"}</p>
	{/if}
</form>

