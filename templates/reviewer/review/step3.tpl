{**
 * step3.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the step 3 review page 
 *
 * $Id$
 *}
{strip}
{assign var="pageCrumbTitle" value="submission.review"}
{include file="reviewer/review/reviewStepHeader.tpl"}
{/strip}

<form name="review" method="post" action="{url op="saveStep" path="3" reviewId=$submission->getReviewId()}">
{include file="common/formErrors.tpl"}

<div id="filesGrid">
	<h3>{translate key=""}</h3>
	<p>{translate key="files grid goes here"}</p>
</div>

<div id="review">
	<h3>{translate key="submission.review"}</h3>
	{confirm dialogText=$press->getLocalizedSetting('reviewGuidelines') translate=false button="#reviewGuidelines"}
	<p style="float:right;"><a id="reviewGuidelines" href="#">{translate key="reviewer.monograph.guidelines"}</a></p>

	{if $reviewAssignment->getReviewFormId()}
	<!-- Display a review form if one is assigned -->
	<div id="reviewForm">
		<div id="reviewFormResponse">
		<h4>{$reviewForm->getLocalizedTitle()}</h4>
		<p>{$reviewForm->getLocalizedDescription()}</p>
		
		<form name="saveReviewFormResponse" method="post" action="{url op="saveReviewFormResponse" path=$reviewId|to_array:$reviewForm->getId()}">
			{foreach from=$reviewFormElements name=reviewFormElements key=elementId item=reviewFormElement}
				<p>{$reviewFormElement->getLocalizedQuestion()} {if $reviewFormElement->getRequired() == 1}*{/if}</p>
				<p>
					{if $reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_SMALL_TEXT_FIELD}
						<input {if $disabled}onkeypress="return (event.keyCode >= 37 && event.keyCode <= 40);" {/if}type="text" name="reviewFormResponses[{$elementId}]" id="reviewFormResponses-{$elementId}" value="{$reviewFormResponses[$elementId]|escape}" size="10" maxlength="40" class="textField" />
					{elseif $reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_TEXT_FIELD}
						<input {if $disabled}onkeypress="return (event.keyCode >= 37 && event.keyCode <= 40);" {/if}type="text" name="reviewFormResponses[{$elementId}]" id="reviewFormResponses-{$elementId}" value="{$reviewFormResponses[$elementId]|escape}" size="40" maxlength="120" class="textField" />
					{elseif $reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_TEXTAREA}
						<textarea {if $disabled}onkeypress="return (event.keyCode >= 37 && event.keyCode <= 40);" {/if}name="reviewFormResponses[{$elementId}]" id="reviewFormResponses-{$elementId}" rows="4" cols="40" class="textArea">{$reviewFormResponses[$elementId]|escape}</textarea>
					{elseif $reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES}
						{assign var=possibleResponses value=$reviewFormElement->getLocalizedPossibleResponses()}
						{foreach name=responses from=$possibleResponses key=responseId item=responseItem}
							<input {if $disabled}disabled="disabled" {/if}type="checkbox" name="reviewFormResponses[{$elementId}][]" id="reviewFormResponses-{$elementId}-{$smarty.foreach.responses.iteration}" value="{$smarty.foreach.responses.iteration}"{if !empty($reviewFormResponses[$elementId]) && in_array($smarty.foreach.responses.iteration, $reviewFormResponses[$elementId])} checked="checked"{/if} /><label for="reviewFormResponses-{$elementId}-{$smarty.foreach.responses.iteration}">{$responseItem.content}</label><br/>
						{/foreach}
					{elseif $reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_RADIO_BUTTONS}
						{assign var=possibleResponses value=$reviewFormElement->getLocalizedPossibleResponses()}
						{foreach name=responses from=$possibleResponses key=responseId item=responseItem}
							<input {if $disabled}disabled="disabled" {/if}type="radio"  name="reviewFormResponses[{$elementId}]" id="reviewFormResponses-{$elementId}-{$smarty.foreach.responses.iteration}" value="{$smarty.foreach.responses.iteration}"{if $smarty.foreach.responses.iteration == $reviewFormResponses[$elementId]} checked="checked"{/if}/><label for="reviewFormResponses-{$elementId}-{$smarty.foreach.responses.iteration}">{$responseItem.content}</label><br/>
						{/foreach}
					{elseif $reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_DROP_DOWN_BOX}
						<select {if $disabled}disabled="disabled" {/if}name="reviewFormResponses[{$elementId}]" id="reviewFormResponses-{$elementId}" size="1" class="selectMenu">
							<option label="" value=""></option>
							{assign var=possibleResponses value=$reviewFormElement->getLocalizedPossibleResponses()}
							{foreach name=responses from=$possibleResponses key=responseId item=responseItem}
								<option label="{$responseItem.content}" value="{$smarty.foreach.responses.iteration}"{if $smarty.foreach.responses.iteration == $reviewFormResponses[$elementId]} selected="selected"{/if}>{$responseItem.content}</option>
							{/foreach}
						</select>
					{/if}
				</p>
			{/foreach}
		
			<br />		
		</form>
		</div>
	</div>
	{else}
	<!-- Display a free text entry field if no review form is assigned -->
	<!-- FIXME: Needs to be a rich text editor -->
	<div id="freeFormReview">
		<textarea id="comments" name="comments" class="textArea" style="width: 95%; height: 200px; margin-left: 10px;">{$reviewAssignment->getComments()|escape}</textarea></td>
	</div>
	{/if}
	<br />

	<div id="attachments">
		<h4>{translate key="email.attachments"}</h4><br />
		<table class="data" width="100%" style="margin-left:10px;">
			{foreach from=$submission->getReviewerFileRevisions() item=reviewerFile key=key}
				{assign var=uploadedFileExists value="1"}
				<tr valign="top">
				<td class="label" width="30%">
					{if $key eq "0"}
						{translate key="reviewer.monograph.uploadedFile"}
					{/if}
				</td>
				<td class="value" width="70%">
					<a href="{url op="downloadFile" path=$reviewId|to_array:$monographId:$reviewerFile->getFileId():$reviewerFile->getRevision()}" class="file">{$reviewerFile->getFileName()|escape}</a>
					{$reviewerFile->getDateModified()|date_format:$dateFormatShort}
					{if ($submission->getRecommendation() === null || $submission->getRecommendation() === '') && (!$submission->getCancelled())}
						<a class="action" href="{url op="deleteReviewerVersion" path=$reviewId|to_array:$reviewerFile->getFileId():$reviewerFile->getRevision()}">{translate key="common.delete"}</a>
					{/if}
				</td>
				</tr>
			{foreachelse}
				<tr valign="top">
				<td class="label" width="30%">
					{translate key="reviewer.monograph.uploadedFile"}
				</td>
				<td class="nodata">
					{translate key="common.none"}
				</td>
				</tr>
			{/foreach}
		</table>
		<form method="post" action="{url op="uploadReviewerVersion"}" style="margin-left:10px;" enctype="multipart/form-data">
			<input type="hidden" name="reviewId" value="{$reviewId|escape}" />
			<input type="file" name="upload" class="uploadField" />
			<input type="submit" name="submit" value="{translate key="common.upload"}" class="button" />
		</form>
	</div>
</div>

<br />

<div id="nextSteps">
	<p>
		<a href="{url op="submission" path=$submission->getReviewId() step=1}">{translate key="navigation.goBack"}</a>
		<input style="float:right;" type="submit" id="submit" value="{translate key='reviewer.monograph.continueToStepFour'}" class="button" />
	</p>
</div>

</form>
</div>
{include file="common/footer.tpl"}

