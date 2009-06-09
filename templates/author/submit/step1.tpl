{**
 * step1.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 1 of author monograph submission.
 *
 * $Id$
 *}
{assign var="pageTitle" value="author.submit.step1"}
{include file="author/submit/submitStepHeader.tpl"}

<!-- ********Support******** -->
{if $pressSettings.supportPhone}
	{assign var="howToKeyName" value="author.submit.howToSubmit"}
{else}
	{assign var="howToKeyName" value="author.submit.howToSubmitNoPhone"}
{/if}
{if isset($pressSettings.supportName)}
<p>{translate key=$howToKeyName supportName=$pressSettings.supportName supportEmail=$pressSettings.supportEmail supportPhone=$pressSettings.supportPhone}</p>
{/if}

<div class="separator"></div>

<h3>{translate key="author.submit.category"}</h3>

<p>{translate key="author.submit.category.description"}</p>

<form name="submit" method="post" action="{url op="saveSubmit" path=$submitStepAlias}" onsubmit="return checkSubmissionChecklist()">
{if $monographId}
<input type="hidden" name="monographId" value="{$monographId|escape}" />
{/if}
<input type="hidden" name="submissionChecklist" value="1" />
{include file="common/formErrors.tpl"}

<table class="data" width="100%">
<tr valign="top">	
	<td width="20%" class="label">{fieldLabel name="arrangementId" key="submissionCategory.submissionCategory"}</td>
	<td width="80%" class="value"><select name="arrangementId" id="arrangementId" size="1" class="selectMenu">{html_options options=$arrangementOptions selected=$arrangementId}</select></td>
</tr>
	
</table>
<!-- ********Type of Work******** -->

<div class="separator"></div>
<h3>{translate key="author.submission.workType"}</h3>
<p>{translate key="author.submission.workType.description"}</p>
<table class="data" width="100%">
<tr valign="top">	
	<td width="20%" class="label">{translate key="author.submission.workType.editedVolume"}</td>
	<td width="80%" class="value"><input type="radio" name="isEditedVolume" value="1" {if $isEditedVolume}checked="checked" {/if}/></td>
</tr>
<tr valign="top">	
	<td width="20%" class="label">{translate key="author.submission.workType.authoredWork"}</td>
	<td width="80%" class="value"><input type="radio" name="isEditedVolume" value="0" {if !$isEditedVolume}checked="checked" {/if}/></td>
</tr>

</table>

<div class="separator"></div>
<!-- **************** -->

<script type="text/javascript">
{literal}
<!--
function checkSubmissionChecklist() {
	var elements = document.submit.elements;
	for (var i=0; i < elements.length; i++) {
		if (elements[i].type == 'checkbox' && !elements[i].checked) {
			if (elements[i].name.match('^checklist')) {
				alert({/literal}'{translate|escape:"jsparam" key="author.submit.verifyChecklist"}'{literal});
				return false;
			} else if (elements[i].name == 'copyrightNoticeAgree') {
				alert({/literal}'{translate|escape:"jsparam" key="author.submit.copyrightNoticeAgreeRequired"}'{literal});
				return false;
			}
		}
	}
	return true;
}
// -->
{/literal}
</script>

<!-- *******Checklist********* -->

{if $currentPress->getLocalizedSetting('submissionChecklist')}

{foreach name=checklist from=$currentPress->getLocalizedSetting('submissionChecklist') key=checklistId item=checklistItem}
	{if $checklistItem.content}
		{if !$notFirstChecklistItem}
			{assign var=notFirstChecklistItem value=1}
			<h3>{translate key="author.submit.submissionChecklist"}</h3>
			<p>{translate key="author.submit.submissionChecklistDescription"}</p>
			<table width="100%" class="data">
		{/if}
		<tr valign="top">
			<td width="5%"><input type="checkbox" id="checklist-{$smarty.foreach.checklist.iteration}" name="checklist[]" value="{$checklistId|escape}"{if $monographId || $submissionChecklist} checked="checked"{/if} /></td>
			<td width="95%"><label for="checklist-{$smarty.foreach.checklist.iteration}">{$checklistItem.content|nl2br}</label></td>
		</tr>
	{/if}
{/foreach}

{if $notFirstChecklistItem}
	</table>
	<div class="separator"></div>
{/if}

{/if}
<!-- ********Copyright Notice******** -->
{if $currentPress->getLocalizedSetting('copyrightNotice') != '' || 1}
<h3>{translate key="about.copyrightNotice"}</h3>

<p>{$currentPress->getLocalizedSetting('copyrightNotice')|nl2br}</p>

{if $pressSettings.copyrightNoticeAgree || 1}
<table width="100%" class="data">
	<tr valign="top">
		<td width="5%"><input type="checkbox" name="copyrightNoticeAgree" id="copyrightNoticeAgree" value="1"{if $monographId || $copyrightNoticeAgree} checked="checked"{/if} /></td>
		<td width="95%"><label for="copyrightNoticeAgree">{translate key="author.submit.copyrightNoticeAgree"}</label></td>
	</tr>
</table>
{/if}

<div class="separator"></div>
{/if}

<!-- ********Privacy Statement******** -->

<h3>{translate key="author.submit.privacyStatement"}</h3>
<br />
{$currentPress->getLocalizedSetting('privacyStatement')|nl2br}

<div class="separator"></div>

<!-- ********Comments******** -->
<h3>{translate key="author.submit.commentsForEditor"}</h3>
<table width="100%" class="data">

<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="commentsToEditor" key="author.submit.comments"}</td>
	<td width="80%" class="value"><textarea name="commentsToEditor" id="commentsToEditor" rows="3" cols="40" class="textArea">{$commentsToEditor|escape}</textarea></td>
</tr>

</table>

<div class="separator"></div>

<!-- *******THE END********* -->

<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="{if $monographId}confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}'){else}document.location.href='{url page="author" escape=false}'{/if}" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer.tpl"}
