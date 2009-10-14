{**
 * step3.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 3 of author monograph submission.
 *
 * $Id: step3.tpl,v 1.11 2009/10/14 19:26:00 tylerl Exp $
 *}

{include file="author/submit/submitStepHeader.tpl"}

<div class="separator"></div>

<form method="post" action="{url op="saveSubmit" path=$submitStep}" enctype="multipart/form-data">
<input type="hidden" name="monographId" value="{$monographId|escape}" />
{include file="common/formErrors.tpl"}

{translate key="author.submit.uploadInstructions"}

{if $pressSettings.supportPhone}
	{assign var="howToKeyName" value="author.submit.howToSubmit"}
{else}
	{assign var="howToKeyName" value="author.submit.howToSubmitNoPhone"}
{/if}

<p>{translate key=$howToKeyName supportName=$pressSettings.supportName supportEmail=$pressSettings.supportEmail supportPhone=$pressSettings.supportPhone}</p>

<div class="separator"></div>

<h3>{translate key="common.bookFiles"}</h3>

<table class="listing" width="100%">
<tr valign="top">
	<td width="5%">&nbsp;</td>
	<td width="30%">{translate key="common.fileName"}</td>
	<td width="8%">{translate key="common.note"}</td>
	<td width="22%">{translate key="common.type"}</td>
	<td width="10%">{translate key="common.fileSize"}</td>
	<td width="30%">{translate key="common.originalFileName"}</td>
</tr>
<tr>
	<td class="separator" colspan="6">&nbsp;</td>
</tr>
{foreach from=$submissionFiles item=submissionFile}
<tr valign="top">
	<td><input type="checkbox" name="selectedFiles[]" value="{$submissionFile->getFileId()}" /></td>
	<td><a href="{url op="download" path=$monographId|to_array:$submissionFile->getFileId()}">{$submissionFile->getFileName()|escape}</a></td>
	<td>{icon name="comment" disabled="disabled"}</td>
	<td>{if $submissionFile->getSetting('bookFileTypeName')}{$submissionFile->getSetting('bookFileTypeName')}{/if}</td>
	<td>{$submissionFile->getNiceFileSize()}</td>
	<td>{$submissionFile->getOriginalFilename()}</td>
</tr>
{foreachelse}
<tr valign="top">
	<td colspan="6" class="nodata"><em>{translate key="common.none"}</em></td>
</tr>
{/foreach}
<tr>
	<td class="separator" colspan="6">&nbsp;</td>
</tr>
</table>
{if count($submissionFiles) > 0}<input type="submit" class="button" name="deleteSelectedFiles" value="{translate key="common.delete"}" />{/if}
<br />
<br />

<div class="newItemContainer">
<h3>{translate key="common.fileUpload"}</h3>

<table class="data" width="100%">
<tr>
	<td width="30%" class="label">
		{fieldLabel name="submissionFile" key="author.submit.uploadSubmissionFile"}
	</td>
	<td width="70%" class="value">
		<input type="file" class="uploadField" name="bookFile" id="bookFile" />
		{if $currentPress->getSetting('showEnsuringLink')}<a class="action" href="javascript:openHelp('{get_help_id key="editorial.acquisitionsEditorsRole.review.blindPeerReview" url="true"}')">{translate key="reviewer.monograph.ensuringBlindReview"}</a>{/if}
	</td>
</tr>
<tr>
	<td class="label">{translate key="common.type"}</td>
	<td class="value">
		<select name="bookFileType" id="bookFileType" class="selectMenu">
		{foreach from=$bookFileTypes key=bookFileTypeKey item=bookFileType}
			<option value="{$bookFileType->getId()|escape}">{$bookFileType->getName($formLocale)|escape}</option>
		{/foreach}
		</select>
	</td>
<tr>
	<td class="label">&nbsp;</td>
	<td class="value"><input name="uploadBookFile" type="submit" class="button" value="{translate key="common.upload"}" /></td>
</tr>
</table>
</div>

<div class="separator"></div>

<p><input type="submit"{if !$submissionFile} onclick="return confirm('{translate|escape:"jsparam" key="author.submit.noSubmissionConfirm"}')"{/if} value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="confirmAction('{url page="author"}', '{translate|escape:"jsparam" key="author.submit.cancelSubmission"}')" /></p>





</form>

{include file="common/footer.tpl"}
