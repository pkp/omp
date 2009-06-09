{**
 * metadataEdit.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for changing metadata of a monograph.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="submission.editMetadata"}
{include file="common/header.tpl"}
{/strip}

{url|assign:"competingInterestGuidelinesUrl" page="information" op="competingInterestGuidelines"}

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
				{if $currentPress->getSetting('requireAuthorCompetingInterests')}
					{foreach from=$author.competingInterests key="thisLocale" item="thisCompetingInterests"}
						{if $thisLocale != $formLocale}<input type="hidden" name="authors[{$authorIndex|escape}][competingInterests][{$thisLocale|escape}]" value="{$thisCompetingInterests|escape}" />{/if}
					{/foreach}
				{/if}
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
		<td class="label">{translate key="monograph.abstract"}</td>
		<td class="value"><textarea name="abstract[{$formLocale|escape}]" id="abstract" rows="15" cols="60" class="textArea">{$abstract[$formLocale]|escape}</textarea></td>
	</tr>
</table>

<div class="separator"></div>

<h3>{translate key="submission.indexing"}</h3>

{if $pressSettings.metaDiscipline || $pressSettings.metaSubjectClass || $pressSettings.metaSubject || $pressSettings.metaCoverage || $pressSettings.metaType}<p>{translate key="author.submit.submissionIndexingDescription"}</p>{/if}

<table width="100%" class="data">
	{if $pressSettings.metaDiscipline}
	<tr valign="top">
		<td class="label">{fieldLabel name="discipline" key="monograph.discipline"}</td>
		<td class="value">
			<input type="text" name="discipline[{$formLocale|escape}]" id="discipline" value="{$discipline[$formLocale]|escape}" size="40" maxlength="255" class="textField" />
			{if $currentPress->getLocalizedSetting('metaDisciplineExamples') != ''}
			<br />
			<span class="instruct">{$currentPress->getLocalizedSetting('metaDisciplineExamples')|escape}</span>
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $pressSettings.metaSubjectClass}
	<tr valign="top">
		<td colspan="2" class="label"><a href="{$currentPress->getLocalizedSetting('metaSubjectClassUrl')|escape}" target="_blank">{$currentPress->getLocalizedSetting('metaSubjectClassTitle')|escape}</a></td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="subjectClass" key="monograph.subjectClassification"}</td>
		<td class="value">
			<input type="text" name="subjectClass[{$formLocale|escape}]" id="subjectClass" value="{$subjectClass[$formLocale]|escape}" size="40" maxlength="255" class="textField" />
			<br />
			<span class="instruct">{translate key="author.submit.subjectClassInstructions"}</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $pressSettings.metaSubject}
	<tr valign="top">
		<td class="label">{fieldLabel name="subject" key="monograph.subject"}</td>
		<td class="value">
			<input type="text" name="subject[{$formLocale|escape}]" id="subject" value="{$subject[$formLocale]|escape}" size="40" maxlength="255" class="textField" />
			{if $currentPress->getLocalizedSetting('metaSubjectExamples') != ''}
			<br />
			<span class="instruct">{$currentPress->getLocalizedSetting('metaSubjectExamples')|escape}</span>
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $pressSettings.metaCoverage}
	<tr valign="top">
		<td class="label">{fieldLabel name="coverageGeo" key="monograph.coverageGeo"}</td>
		<td class="value">
			<input type="text" name="coverageGeo[{$formLocale|escape}]" id="coverageGeo" value="{$coverageGeo[$formLocale]|escape}" size="40" maxlength="255" class="textField" />
			{if $currentPress->getLocalizedSetting('metaCoverageGeoExamples') != ''}
			<br />
			<span class="instruct">{$currentPress->getLocalizedSetting('metaCoverageGeoExamples')|escape}</span>
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="coverageChron" key="monograph.coverageChron"}</td>
		<td class="value">
			<input type="text" name="coverageChron[{$formLocale|escape}]" id="coverageChron" value="{$coverageChron[$formLocale]|escape}" size="40" maxlength="255" class="textField" />
			{if $currentPress->getLocalizedSetting('metaCoverageChronExamples') != ''}
			<br />
			<span class="instruct">{$currentPress->getLocalizedSetting('metaCoverageChronExamples')|escape}</span>
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{fieldLabel name="coverageSample" key="monograph.coverageSample"}</td>
		<td class="value">
			<input type="text" name="coverageSample[{$formLocale|escape}]" id="coverageSample" value="{$coverageSample[$formLocale]|escape}" size="40" maxlength="255" class="textField" />
			{if $currentPress->getLocalizedSetting('metaCoverageResearchSampleExamples') != ''}
			<br />
			<span class="instruct">{$currentPress->getLocalizedSetting('metaCoverageResearchSampleExamples')|escape}</span>
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $pressSettings.metaType}
	<tr valign="top">
		<td class="label">{fieldLabel name="type" key="monograph.type"}</td>
		<td class="value">
			<input type="text" name="type[{$formLocale|escape}]" id="type" value="{$type[$formLocale]|escape}" size="40" maxlength="255" class="textField" />
		</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="language" key="monograph.language"}</td>
		<td width="80%" class="value">
			<input type="text" name="language" id="language" value="{$language|escape}" size="5" maxlength="10" class="textField" />
			<br />
			<span class="instruct">{translate key="author.submit.languageInstructions"}</span>
		</td>
	</tr>
</table>


<div class="separator"></div>


<h3>{translate key="submission.supportingAgencies"}</h3>

<p>{translate key="author.submit.submissionSupportingAgenciesDescription"}</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="sponsor" key="author.submit.agencies"}</td>
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
