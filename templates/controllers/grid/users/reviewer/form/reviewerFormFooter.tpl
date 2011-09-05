{**
 * templates/controllers/grid/user/reviewer/form/reviewerFormFooter.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * The non-searching part of the add reviewer form
 *
 *}
 <script type="text/javascript">
$("#responseDueDate").datepicker({ldelim} dateFormat: 'yy-mm-dd' {rdelim});
$("#reviewDueDate").datepicker({ldelim} dateFormat: 'yy-mm-dd' {rdelim});

$("#interests").tagit({ldelim}
	// This is the list of interests in the system used to populate the autocomplete
	availableTags: [{foreach name=existingInterests from=$existingInterests item=interest}"{$interest|escape|escape:'javascript'}"{if !$smarty.foreach.existingInterests.last}, {/if}{/foreach}],
	currentTags: []
{rdelim});
</script>

<!--  Message to reviewer textarea -->
{fbvFormSection title="editor.review.personalMessageToReviewer" for="personalMessage"}
	{fbvElement type="textarea" name="personalMessage" id="personalMessage" value=$personalMessage|escape}
{/fbvFormSection}

<!--  Reviewer due dates (see http://jqueryui.com/demos/datepicker/) -->
{fbvFormSection title="editor.review.importantDates"}
	{fbvElement type="text" id="responseDueDate" name="responseDueDate" label="editor.responseDueDate" value=$responseDueDate inline=true size=$fbvStyles.size.MEDIUM}
	{fbvElement type="text" id="reviewDueDate" name="reviewDueDate" label="editor.review.reviewDueDate" value=$reviewDueDate inline=true size=$fbvStyles.size.MEDIUM}
{/fbvFormSection}

{fbvFormSection list=true title="editor.submissionReview.reviewType"}
	{foreach from=$reviewMethods key=methodId item=methodTranslationKey}
		{assign var=elementId value="reviewMethod"|concat:"-"|concat:$methodId}
		{if $reviewMethod == $methodId}
			{assign var=elementChecked value=true}
		{else}
			{assign var=elementChecked value=false}
		{/if}
		{fbvElement type="radio" name="reviewMethod" id=$elementId value=$methodId checked=$elementChecked label=$methodTranslationKey}
	{/foreach}
{/fbvFormSection}

<!-- All of the hidden inputs -->
<input type="hidden" name="selectionType" value={$selectionType|escape} />
<input type="hidden" name="monographId" value={$monographId|escape} />
<input type="hidden" name="stageId" value="{$stageId|escape}" />
<input type="hidden" name="round" value="{$round|escape}" />