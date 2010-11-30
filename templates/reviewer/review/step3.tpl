{**
 * step3.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the step 3 review page
 *}
{strip}
{assign var="pageCrumbTitle" value="submission.review"}
{include file="reviewer/review/reviewStepHeader.tpl"}
{/strip}

<form name="review" method="post" action="{url op="saveStep" path=$submission->getId() step="3"}">
{include file="common/formErrors.tpl"}

{** FIXME: need to set escape=false due to bug 5265 *}
{url|assign:reviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewerReviewFilesGridHandler" op="fetchGrid" monographId=$submission->getId() reviewType=$submission->getCurrentReviewType() round=$submission->getCurrentRound() escape=false}
{load_url_in_div id="reviewFiles" url=$reviewFilesGridUrl}

<div id="review">
	<h3>{translate key="submission.review"}</h3>
	{if $press->getLocalizedSetting('reviewGuidelines')}
		{confirm dialogText=$press->getLocalizedSetting('reviewGuidelines') translate=false button="#reviewGuidelines"}
		<p style="float:right;"><a id="reviewGuidelines" href="#">{translate key="reviewer.monograph.guidelines"}</a></p>
		<div style="clear:both;" />
	{/if}
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
	<div id="freeFormReview" style="margin-left: 10px;">
		<textarea id="comments" name="comments" class="textArea" style="width: 95%; height: 200px; margin-left: 10px;">{$reviewAssignment->getComments()|escape}</textarea></td>
	</div>
	{/if}
	<br />

	<div id="attachments">
		{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewAttachments.ReviewerReviewAttachmentsGridHandler" op="fetchGrid" reviewId=$submission->getReviewId() monographId=$submission->getId() escape=false}
		{load_url_in_div id="reviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
	</div>
</div>

<br />

<div id="nextSteps">
	<p>
		<a href="{url op="submission" path=$submission->getReviewId() step=2}">{translate key="navigation.goBack"}</a>
		{confirm_submit button='submit' dialogText='reviewer.confirmSubmit'}
		<input style="float:right;" type="submit" id="submit" value="{translate key='reviewer.monograph.continueToStepFour'}" class="button" />
	</p>
</div>
<br />
</form>
</div>
{include file="common/footer.tpl"}


