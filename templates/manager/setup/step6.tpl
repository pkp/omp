{**
 * step5.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 5 of press setup.
 *
 * $Id$
 *}
{assign var="pageTitle" value="manager.setup.customizingTheLook"}
{include file="manager/setup/setupHeader.tpl"}

<script type="text/javascript">
{literal}
<!--

// Swap the given entries in the select field.
function swapEntries(field, i, j) {
	var tmpLabel = field.options[j].label;
	var tmpVal = field.options[j].value;
	var tmpText = field.options[j].text;
	var tmpSel = field.options[j].selected;
	field.options[j].label = field.options[i].label;
	field.options[j].value = field.options[i].value;
	field.options[j].text = field.options[i].text;
	field.options[j].selected = field.options[i].selected;
	field.options[i].label = tmpLabel;
	field.options[i].value = tmpVal;
	field.options[i].text = tmpText;
	field.options[i].selected = tmpSel;
}

// Move selected items up in the given select list.
function moveUp(field) {
	var i;
	for (i=0; i<field.length; i++) {
		if (field.options[i].selected == true && i>0) {
			swapEntries(field, i-1, i);
		}
	}
}

// Move selected items down in the given select list.
function moveDown(field) {
	var i;
	var max = field.length - 1;
	for (i = max; i >= 0; i=i-1) {
		if(field.options[i].selected == true && i < max) {
			swapEntries(field, i+1, i);
		}
	}
}

// Move selected items from select list a to select list b.
function jumpList(a, b) {
	var i;
	for (i=0; i<a.options.length; i++) {
		if (a.options[i].selected == true) {
			bMax = b.options.length;
			b.options[bMax] = new Option(a.options[i].text);
			b.options[bMax].value = a.options[i].value;
			a.options[i] = null;
			i=i-1;
		}
	}
}

function prepBlockFields() {
	var i;
	var theForm = document.setupForm;

	theForm.elements["blockSelectLeft"].value = "";
	for (i=0; i<theForm.blockSelectLeftWidget.options.length; i++) {
		theForm.blockSelectLeft.value += theForm.blockSelectLeftWidget.options[i].value + " ";
	}

	theForm.blockSelectRight.value = "";
	for (i=0; i<theForm.blockSelectRightWidget.options.length; i++) {
		theForm.blockSelectRight.value += theForm.blockSelectRightWidget.options[i].value + " ";
	}

	theForm.blockUnselected.value = "";
	for (i=0; i<theForm.blockUnselectedWidget.options.length; i++) {
		theForm.blockUnselected.value += theForm.blockUnselectedWidget.options[i].value + " ";
	}
	return true;
}

// -->
{/literal}
</script>

<form name="setupForm" method="post" action="{url op="saveSetup" path="5"}" enctype="multipart/form-data">
{include file="common/formErrors.tpl"}

{if count($formLocales) > 1}
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{url|assign:"setupFormUrl" op="setup" path="5"}
			{form_language_chooser form="setupForm" url=$setupFormUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
</table>
{/if}

<h3>6.1 Internal Review Process</h3>

By default, the editors and acquisitions editors that are assigned to a particular manuscript will be the ones that will sign off on a review process.
<br /><br />
<a href="{url op="reviewSignoffs" path=$smarty.const.WORKFLOW_PROCESS_ASSESSMENT_INTERNAL}">Click here</a> to specify the other parties, if any, that will have to acknowledge their satisfaction of the review process in an action of 'signing off'.




<div class="separator"></div>

<h3>6.2 External Review Process</h3>

By default, the editors and acquisitions editors that are assigned to a particular manuscript will be the ones that will sign off on a review process.
<br /><br />
<a href="{url op="reviewSignoffs" path=$smarty.const.WORKFLOW_PROCESS_ASSESSMENT_EXTERNAL}">Click here</a> to specify the other parties, if any, that will have to acknowledge their satisfaction of the review process in an action of 'signing off'.


<div class="separator"></div>

<div class="separator"></div>

<h3>6.3 Editing Process</h3>


<div class="separator"></div>

<p><input type="submit" onclick="prepBlockFields()" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="setup" escape=false}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>

{include file="common/footer.tpl"}
