{**
 * step2.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 2 of author monograph submission.
 *
 * $Id$
 *}
{assign var="pageTitle" value="author.submit.step2"}
{include file="author/submit/submitStepHeader.tpl"}

{url|assign:"competingInterestGuidelinesUrl" page="information" op="competingInterestGuidelines"}

<div class="separator"></div>

<form name="submit" method="post" action="{url op="saveSubmit" path=$submitStep}">
<input type="hidden" name="monographId" value="{$monographId|escape}" />
{include file="common/formErrors.tpl"}



{if count($formLocales) > 1}
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{url|assign:"submitFormUrl" op="submit" path="2" monographId=$monographId}
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
			{form_language_chooser form="submit" url=$submitFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
</table>
{/if}

{include file="inserts/monographComponents/MonographComponentsInsert.tpl"}

<div class="separator"></div>

<h3>Title and Description</h3>
<br />
Please provide the title and a description of work, including the scope, aim, and value of this contribution.
<br /><br />
<table width="100%" class="data">

<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="title" required="true" key="monograph.title"}</td>
	<td width="80%" class="value"><input type="text" class="textField" name="title[{$formLocale|escape}]" id="title" value="{$title[$formLocale]|escape}" size="60" maxlength="255" /></td>
</tr>

<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="abstract" key="monograph.abstract"}</td>
	<td width="80%" class="value"><textarea name="abstract[{$formLocale|escape}]" id="abstract" class="textArea" rows="15" cols="60">{$abstract[$formLocale]|escape}</textarea></td>
</tr>
</table>

<div class="separator"></div>

	<h3>{translate key="submission.indexing"}</h3>
	{if $pressSettings.metaDiscipline || $pressSettings.metaSubjectClass || $pressSettings.metaSubject || $pressSettings.metaCoverage || $pressSettings.metaType}<p>{translate key="author.submit.submissionIndexingDescription"}</p>{/if}
	<table width="100%" class="data">
	{if $pressSettings.metaDiscipline}
	<tr valign="top">
		<td{if $currentPress->getLocalizedSetting('metaDisciplineExamples') != ''} rowspan="2"{/if} width="20%" class="label">{fieldLabel name="discipline" key="monograph.discipline"}</td>
		<td width="80%" class="value"><input type="text" class="textField" name="discipline[{$formLocale|escape}]" id="discipline" value="{$discipline[$formLocale]|escape}" size="40" maxlength="255" /></td>
	</tr>
	{if $currentPress->getLocalizedSetting('metaDisciplineExamples')}
	<tr valign="top">
		<td><span class="instruct">{$currentPress->getLocalizedSetting('metaDisciplineExamples')|escape}</span></td>
	</tr>
	{/if}
	<tr valign="top">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	{/if}
	
	{if $pressSettings.metaSubjectClass}
	<tr valign="top">
		<td rowspan="2" width="20%" class="label">{fieldLabel name="subjectClass" key="monograph.subjectClassification"}</td>
		<td width="80%" class="value"><input type="text" class="textField" name="subjectClass[{$formLocale|escape}]" id="subjectClass" value="{$subjectClass[$formLocale]|escape}" size="40" maxlength="255" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><a href="{$currentPress->getLocalizedSetting('metaSubjectClassUrl')|escape}" target="_blank">{$currentPress->getLocalizedSetting('metaSubjectClassTitle')|escape}</a></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	{/if}
	
	{if $pressSettings.metaSubject}
	<tr valign="top">
		<td{if $currentPress->getLocalizedSetting('metaSubjectExamples') != ''} rowspan="2"{/if} width="20%" class="label">{fieldLabel name="subject" key="monograph.subject"}</td>
		<td width="80%" class="value"><input type="text" class="textField" name="subject[{$formLocale|escape}]" id="subject" value="{$subject[$formLocale]|escape}" size="40" maxlength="255" /></td>
	</tr>
	{if $currentPress->getLocalizedSetting('metaSubjectExamples') != ''}
	<tr valign="top">
		<td><span class="instruct">{$currentPress->getLocalizedSetting('metaSubjectExamples')|escape}</span></td>
	</tr>
	{/if}
	<tr valign="top">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	{/if}
	
	{if $pressSettings.metaCoverage}
	<tr valign="top">
		<td{if $currentPress->getLocalizedSetting('metaCoverageGeoExamples') != ''} rowspan="2"{/if} width="20%" class="label">{fieldLabel name="coverageGeo" key="monograph.coverageGeo"}</td>
		<td width="80%" class="value"><input type="text" class="textField" name="coverageGeo[{$formLocale|escape}]" id="coverageGeo" value="{$coverageGeo[$formLocale]|escape}" size="40" maxlength="255" /></td>
	</tr>
	{if $currentPress->getLocalizedSetting('metaCoverageGeoExamples')}
	<tr valign="top">
		<td><span class="instruct">{$currentPress->getLocalizedSetting('metaCoverageGeoExamples')|escape}</span></td>
	</tr>
	{/if}
	<tr valign="top">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr valign="top">
		<td{if $currentPress->getLocalizedSetting('metaCoverageChronExamples') != ''} rowspan="2"{/if} width="20%" class="label">{fieldLabel name="coverageChron" key="monograph.coverageChron"}</td>
		<td width="80%" class="value"><input type="text" class="textField" name="coverageChron[{$formLocale|escape}]" id="coverageChron" value="{$coverageChron[$formLocale]|escape}" size="40" maxlength="255" /></td>
	</tr>
	{if $currentPress->getLocalizedSetting('metaCoverageChronExamples') != ''}
	<tr valign="top">
		<td><span class="instruct">{$currentPress->getLocalizedSetting('metaCoverageChronExamples')|escape}</span></td>
	</tr>
	{/if}
	<tr valign="top">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr valign="top">
		<td{if $currentPress->getLocalizedSetting('metaCoverageResearchSampleExamples') != ''} rowspan="2"{/if} width="20%" class="label">{fieldLabel name="coverageSample" key="monograph.coverageSample"}</td>
		<td width="80%" class="value"><input type="text" class="textField" name="coverageSample[{$formLocale|escape}]" id="coverageSample" value="{$coverageSample[$formLocale]|escape}" size="40" maxlength="255" /></td>
	</tr>
	{if $currentPress->getLocalizedSetting('metaCoverageResearchSampleExamples') != ''}
	<tr valign="top">
		<td><span class="instruct">{$currentPress->getLocalizedSetting('metaCoverageResearchSampleExamples')|escape}</span></td>
	</tr>
	{/if}
	<tr valign="top">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	{/if}
	
	{if $pressSettings.metaType}
	<tr valign="top">
		<td width="20%" {if $currentPress->getLocalizedSetting('metaTypeExamples') != ''}rowspan="2" {/if}class="label">{fieldLabel name="type" key="monograph.type"}</td>
		<td width="80%" class="value"><input type="text" class="textField" name="type[{$formLocale|escape}]" id="type" value="{$type[$formLocale]|escape}" size="40" maxlength="255" /></td>
	</tr>

	{if $currentPress->getLocalizedSetting('metaTypeExamples') != ''}
	<tr valign="top">
		<td><span class="instruct">{$currentPress->getLocalizedSetting('metaTypeExamples')|escape}</span></td>
	</tr>
	{/if}
	<tr valign="top">
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	{/if}
	
	<tr valign="top">
		<td rowspan="2" width="20%" class="label">{fieldLabel name="language" key="monograph.language"}</td>
		<td width="80%" class="value"><input type="text" class="textField" name="language" id="language" value="{$language|escape}" size="5" maxlength="10" /></td>
	</tr>
	<tr valign="top">
		<td><span class="instruct">{translate key="author.submit.languageInstructions"}</span></td>
	</tr>
	</table>

<div class="separator"></div>

<!-- ******* -->

<h3>{translate key="author.submit.submissionSupportingAgencies"}</h3>
<p>{translate key="author.submit.submissionSupportingAgenciesDescription"}</p>

<table width="100%" class="data">
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="sponsor" key="author.submit.agencies"}</td>
	<td width="80%" class="value"><input type="text" class="textField" name="sponsor[{$formLocale|escape}]" id="sponsor" value="{$sponsor[$formLocale]|escape}" size="60" maxlength="255" /></td>
</tr>
</table>

<div class="separator"></div>

<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}')" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer.tpl"}
