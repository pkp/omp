{**
 * pressSettings.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Basic press settings under site administration.
 *}
{strip}
{assign var="pageTitle" value="admin.presses.pressSettings"}
{include file="common/header.tpl"}
{/strip}

<br />

<script type="text/javascript">
{literal}
<!--
// Ensure that the form submit button cannot be double-clicked
function doSubmit() {
	if (document.getElementById('press').submitted.value != 1) {
		document.getElementById('press').submitted.value = 1;
		document.getElementById('press').submit();
	}
	return true;
}
// -->
{/literal}
</script>

<form class="pkp_form" id="press" method="post" action="{url op="updatePress"}">
{include file="common/formErrors.tpl"}

{if not $pressId}
	<p>{translate key="admin.presses.createInstructions"}</p>
{/if}

{fbvElement id="submitted" type="hidden" name="submitted" value="0"}
{if $pressId}
	{fbvElement id="pressId" type="hidden" name="pressId" value=$pressId}
{/if}


{fbvFormArea id="pressSettings"}
	{fbvFormSection title="manager.setup.pressName" required=true for="name"}
		{fbvElement type="text" id="name" value=$name multilingual=true}
	{/fbvFormSection}
	{fbvFormSection title="admin.presses.pressDescription" required=true for="description"}
		{fbvElement type="textarea" id="description" value=$description multilingual=true rich=true}
	{/fbvFormSection}
	{fbvFormSection title="press.path" required=true for="path"}
		{fbvElement type="text" id="path" value=$path size=$smarty.const.SMALL maxlength="32"}
		{url|assign:"sampleUrl" press="path"}
		{** FIXME: is this class instruct still the right one? **}
		<span class="instruct">{translate key="admin.presses.urlWillBe" sampleUrl=$sampleUrl}</span>
	{/fbvFormSection}
	{fbvFormSection title="admin.presses.enablePressInstructions" for="enabled" list=true}
		{if $enabled}{assign var="enabled" value="checked"}{/if}
		{fbvElement type="checkbox" id="enabled" checked=$enabled value="1"}
	{/fbvFormSection}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
	{fbvFormButtons id="submit" submitText="common.save"}
{/fbvFormArea}

{include file="common/footer.tpl"}

