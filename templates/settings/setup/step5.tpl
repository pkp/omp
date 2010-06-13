{**
 * step5.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 5 of press setup.
 *
 * $Id$
 *}
{assign var="pageTitle" value="settings.setup.customizingTheLook"}
{include file="settings/setup/setupHeader.tpl"}

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
{fbvFormArea id="locales"}
{fbvFormSection title="form.formLanguage" for="languageSelector"}
	{fbvCustomElement}
		{url|assign:"setupFormUrl" op="setup" path="1"}
		{form_language_chooser form="setupForm" url=$setupFormUrl}
		<span class="instruct">{translate key="form.formLanguage.description"}</span>
	{/fbvCustomElement}
{/fbvFormSection}
{/fbvFormArea}
{/if} {* count($formLocales) > 1*}

<h3>5.1 {translate key="settings.setup.pressHomepageHeader"}</h3>

<p>{translate key="settings.setup.pressHomepageHeaderDescription"}</p>

<h4>{translate key="settings.setup.pressName"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><input type="radio" name="homeHeaderTitleType[{$formLocale|escape}]" id="homeHeaderTitleType-0" value="0"{if not $homeHeaderTitleType[$formLocale]} checked="checked"{/if} /> {fieldLabel name="homeHeaderTitleType-0" key="settings.setup.useTextTitle"}</td>
		<td width="80%" class="value"><input type="text" name="homeHeaderTitle[{$formLocale|escape}]" value="{$homeHeaderTitle[$formLocale]|escape}" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><input type="radio" name="homeHeaderTitleType[{$formLocale|escape}]" id="homeHeaderTitleType-1" value="1"{if $homeHeaderTitleType[$formLocale]} checked="checked"{/if} /> {fieldLabel name="homeHeaderTitleType-1" key="settings.setup.useImageTitle"}</td>
		<td width="80%" class="value">{fbvFileInput id="homeHeaderTitleImage" submit="uploadHomeHeaderTitleImage"}</td>
	</tr>
</table>

{if $homeHeaderTitleImage[$formLocale]}
{translate key="common.fileName"}: {$homeHeaderTitleImage[$formLocale].name|escape} {$homeHeaderTitleImage[$formLocale].dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteHomeHeaderTitleImage" value="{translate key="common.delete"}" class="button" />
<br />
<img src="{$publicFilesDir}/{$homeHeaderTitleImage[$formLocale].uploadName|escape:"url"}" width="{$homeHeaderTitleImage[$formLocale].width|escape}" height="{$homeHeaderTitleImage[$formLocale].height|escape}" style="border: 0;" alt="{translate key="common.homePageHeader.altText"}" />
<br />
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="homeHeaderTitleImageAltText" key="common.altText"}</td>
		<td width="80%" class="value"><input type="text" name="homeHeaderTitleImageAltText[{$formLocale|escape}]" value="{$homeHeaderTitleImage[$formLocale].altText|escape}" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value"><span class="instruct">{translate key="common.altTextInstructions"}</span></td>
	</tr>
</table>
{/if}

<h4>{translate key="settings.setup.pressLogo"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{translate key="settings.setup.useImageLogo"}</td>
		<td width="80%" class="value">{fbvFileInput id="homeHeaderLogoImage" submit="uploadHomeHeaderLogoImage"}</td>
	</tr>
</table>

{if $homeHeaderLogoImage[$formLocale]}
{translate key="common.fileName"}: {$homeHeaderLogoImage[$formLocale].name|escape} {$homeHeaderLogoImage[$formLocale].dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteHomeHeaderLogoImage" value="{translate key="common.delete"}" class="button" />
<br />
<img src="{$publicFilesDir}/{$homeHeaderLogoImage[$formLocale].uploadName|escape:"url"}" width="{$homeHeaderLogoImage[$formLocale].width|escape}" height="{$homeHeaderLogoImage[$formLocale].height|escape}" style="border: 0;" alt="{translate key="common.homePageHeaderLogo.altText"}" />
<br />
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="homeHeaderLogoImageAltText" key="common.altText"}</td>
		<td width="80%" class="value"><input type="text" name="homeHeaderLogoImageAltText[{$formLocale|escape}]" value="{$homeHeaderLogoImage[$formLocale].altText|escape}" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value"><span class="instruct">{translate key="common.altTextInstructions"}</span></td>
		</tr>
</table>
{/if}

<div class="separator"></div>


<h3>5.2 {translate key="settings.setup.pressHomepageContent"}</h3>

<p>{translate key="settings.setup.pressHomepageContentDescription"}</p>

{fbvFormArea id="pressDescription"}
	{fbvFormSection title="settings.setup.pressDescription"}
		<p>{translate key="settings.setup.pressDescriptionDescription"}</p>
		{fbvElement type="textarea" name="description[$formLocale]" id="description" value=$description[$formLocale] size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}
{/fbvFormArea}

<h4>{translate key="settings.setup.homepageImage"}</h4>

<p>{translate key="settings.setup.homepageImageDescription"}</p>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{translate key="settings.setup.homepageImage"}</td>
		<td width="80%" class="value">{fbvFileInput id="homepageImage" submit="uploadHomepageImage"}</td>
	</tr>
</table>

{if $homepageImage[$formLocale]}
{translate key="common.fileName"}: {$homepageImage[$formLocale].name|escape} {$homepageImage[$formLocale].dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteHomepageImage" value="{translate key="common.delete"}" class="button" />
<br />
<img src="{$publicFilesDir}/{$homepageImage[$formLocale].uploadName|escape:"url"}" width="{$homepageImage[$formLocale].width|escape}" height="{$homepageImage[$formLocale].height|escape}" style="border: 0;" alt="{translate key="common.pressHomepageImage.altText"}" />
<br />
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="homepageImageAltText" key="common.altText"}</td>
		<td width="80%" class="value"><input type="text" name="homepageImageAltText[{$formLocale|escape}]" value="{$homepageImage[$formLocale].altText|escape}" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value"><span class="instruct">{translate key="common.altTextInstructions"}</span></td>
		</tr>
</table>
{/if}

<h4>{translate key="settings.setup.recentTitles"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="40%" class="label">{translate key="settings.setup.numRecentTitlesOnHomepage"}</td>
		<td width="60%" class="value"><input type="text" size="3" name="numRecentTitlesOnHomepage" class="textField" value="{$numRecentTitlesOnHomepage|escape}" /></td>
	</tr>
</table>

{fbvFormArea id="additionalContent"}
	{fbvFormSection title="settings.setup.additionalContent"}
		<p>{translate key="settings.setup.additionalContentDescription"}</p>
		{fbvElement type="textarea" name="additionalHomeContent[$formLocale]" id="additionalHomeContent" value=$additionalHomeContent[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<h3>5.3 {translate key="settings.setup.addItemtoAboutPress"}</h3>

{fbvFormArea id="addItemtoAboutPress"}
{foreach name=customAboutItems from=$customAboutItems[$formLocale] key=aboutId item=aboutItem}
	{fbvFormSection title="common.title" for="customAboutItems-$aboutId-title"}
		{fbvElement type="text" name="customAboutItems[$formLocale][$aboutId][title]" id="customAboutItems-$aboutId-title" value=$aboutItem.title maxlength="255"}
	{/fbvFormSection}
	{fbvFormSection title="settings.setup.aboutItemContent" for="customAboutItems-$aboutId-content"}
		{fbvElement type="textarea" name="customAboutItems[$formLocale][$aboutId][content]" id="customAboutItems-$aboutId-content" value=$aboutItem.content size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}
{foreachelse}
	{fbvFormSection title="common.title" for="customAboutItems-0-title"}
		{fbvElement type="text" name="customAboutItems[$formLocale][0][title]" id="customAboutItems-0-title" value="" maxlength="255"}
	{/fbvFormSection}
	{fbvFormSection title="settings.setup.aboutItemContent" for="customAboutItems-0-content"}
		{fbvElement type="textarea" name="customAboutItems[$formLocale][0][content]" id="customAboutItems-0-content" value="" size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}
{/foreach}
{/fbvFormArea}

<p><input type="submit" name="addCustomAboutItem" value="{translate key="settings.setup.addAboutItem"}" class="button" /></p>

<div class="separator"></div>

<h3>5.4 {translate key="settings.setup.information"}</h3>

<p>{translate key="settings.setup.information.description"}</p>

{fbvFormArea id="information"}
	{fbvFormSection title="settings.setup.information.forReaders"}
		{fbvElement type="textarea" name="readerInformation[$formLocale]" id="readerInformation" value=$readerInformation[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}
	{fbvFormSection title="settings.setup.information.forAuthors"}
		{fbvElement type="textarea" name="authorInformation[$formLocale]" id="authorInformation" value=$authorInformation[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}
	{fbvFormSection title="settings.setup.information.forLibrarians"}
		{fbvElement type="textarea" name="librarianInformation[$formLocale]" id="librarianInformation" value=$librarianInformation[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<h3>5.5 {translate key="settings.setup.pressLayout"}</h3>

<p>{translate key="settings.setup.pressLayoutDescription"}</p>

<table width="100%" class="data">
<tr>
	<td width="20%" class="label"><label for="pressTheme">{translate key="settings.setup.pressTheme"}</label></td>
	<td width="80%" class="value">
		<select name="pressTheme" class="selectMenu" id="pressTheme"{if empty($pressThemes)} disabled="disabled"{/if}>
			<option value="">{translate key="common.none"}</option>
			{foreach from=$pressThemes key=path item=pressThemePlugin}
				<option value="{$path|escape}"{if $path == $pressTheme} selected="selected"{/if}>{$pressThemePlugin->getDisplayName()}</option>
			{/foreach}
		</select>
	</td>
</tr>
<tr>
	<td width="20%" class="label"><label for="pressStyleSheet">{translate key="settings.setup.usePressStyleSheet"}</label></td>
	<td width="80%" class="value">{fbvFileInput id="pressStyleSheet" submit="uploadPressStyleSheet"}</td>
</tr>
</table>

{if $pressStyleSheet}
{translate key="common.fileName"}: <a href="{$publicFilesDir}/{$pressStyleSheet.uploadName|escape:"url"}" class="file">{$pressStyleSheet.name|escape}</a> {$pressStyleSheet.dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deletePressStyleSheet" value="{translate key="common.delete"}" class="button" />
{/if}

<table border="0" align="center">
	<tr align="center">
		<td rowspan="2">
			{translate key="settings.setup.layout.leftSidebar"}<br/>
			<input class="button defaultButton" style="width: 130px;" type="button" value="&uarr;" onclick="moveUp(this.form.elements['blockSelectLeftWidget']);" /><br/>
			<select name="blockSelectLeftWidget" multiple="multiple" size="10" class="selectMenu" style="width: 130px; height:200px">
				{foreach from=$leftBlockPlugins item=block}
					<option value="{$block->getName()|escape}">{$block->getDisplayName()|escape}</option>
				{/foreach}
			</select><br/>
			<input class="button defaultButton" style="width: 130px;" type="button" value="&darr;" onclick="moveDown(this.form.elements['blockSelectLeftWidget']);" />
		</td>
		<td>
			<input class="button defaultButton" style="width: 30px;" type="button" value="&larr;" onclick="jumpList(this.form.elements['blockUnselectedWidget'],this.form.elements['blockSelectLeftWidget']);" /><br/>
			<input class="button defaultButton" style="width: 30px;" type="button" value="&rarr;" onclick="jumpList(this.form.elements['blockSelectLeftWidget'],this.form.elements['blockUnselectedWidget']);" />
		</td>
		<td valign="top">
			{translate key="settings.setup.layout.unselected"}<br/>
			<select name="blockUnselectedWidget" multiple="multiple" size="10" class="selectMenu" style="width: 120px; height:180px;" >
				{foreach from=$disabledBlockPlugins item=block}
					<option value="{$block->getName()|escape}">{$block->getDisplayName()|escape}</option>
				{/foreach}
			</select>
		</td>
		<td>
			<input class="button defaultButton" style="width: 30px;" type="button" value="&larr;" onclick="jumpList(this.form.elements['blockSelectRightWidget'],this.form.elements['blockUnselectedWidget']);" /><br/>
			<input class="button defaultButton" style="width: 30px;" type="button" value="&rarr;" onclick="jumpList(this.form.elements['blockUnselectedWidget'],this.form.elements['blockSelectRightWidget']);" />
		</td>
		<td rowspan="2">
			{translate key="settings.setup.layout.rightSidebar"}<br/>
			<input class="button defaultButton" style="width: 130px;" type="button" value="&uarr;" onclick="moveUp(this.form.elements['blockSelectRightWidget']);" /><br/>
			<select name="blockSelectRightWidget" multiple="multiple" size="10" class="selectMenu" style="width: 130px; height:200px" >
				{foreach from=$rightBlockPlugins item=block}
					<option value="{$block->getName()|escape}">{$block->getDisplayName()|escape}</option>
				{/foreach}
			</select><br/>
			<input class="button defaultButton" style="width: 130px;" type="button" value="&darr;" onclick="moveDown(this.form.elements['blockSelectRightWidget']);" />
		</td>
	</tr>
	<tr align="center">
		<td colspan="3" valign="top" height="60px">
			<input class="button defaultButton" style="width: 190px;" type="button" value="&larr;" onclick="jumpList(this.form.elements['blockSelectRightWidget'],this.form.elements['blockSelectLeftWidget']);" /><br/>
			<input class="button defaultButton" style="width: 190px;" type="button" value="&rarr;" onclick="jumpList(this.form.elements['blockSelectLeftWidget'],this.form.elements['blockSelectRightWidget']);" />
		</td>
	</tr>
</table>
<input type="hidden" name="blockSelectLeft" value="" />
<input type="hidden" name="blockSelectRight" value="" />
<input type="hidden" name="blockUnselected" value="" />

<div class="separator"></div>

<h3>5.6 {translate key="settings.setup.pressPageHeader"}</h3>

<p>{translate key="settings.setup.pressPageHeaderDescription"}</p>

<h4>{translate key="settings.setup.pressName"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label"><input type="radio" name="pageHeaderTitleType[{$formLocale|escape}]" id="pageHeaderTitleType-0" value="0"{if not $pageHeaderTitleType[$formLocale]} checked="checked"{/if} /> {fieldLabel name="pageHeaderTitleType-0" key="settings.setup.useTextTitle"}</td>
		<td width="80%" class="value"><input type="text" name="pageHeaderTitle[{$formLocale|escape}]" value="{$pageHeaderTitle[$formLocale]|escape}" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label"><input type="radio" name="pageHeaderTitleType[{$formLocale|escape}]" id="pageHeaderTitleType-1" value="1"{if $pageHeaderTitleType[$formLocale]} checked="checked"{/if} /> {fieldLabel name="pageHeaderTitleType-1" key="settings.setup.useImageTitle"}</td>
		<td width="80%" class="value">{fbvFileInput id="pageHeaderTitleImage" submit="uploadPageHeaderTitleImage"}</td>
	</tr>
</table>

{if $pageHeaderTitleImage[$formLocale]}
{translate key="common.fileName"}: {$pageHeaderTitleImage[$formLocale].name|escape} {$pageHeaderTitleImage[$formLocale].dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deletePageHeaderTitleImage" value="{translate key="common.delete"}" class="button" />
<br />
<img src="{$publicFilesDir}/{$pageHeaderTitleImage[$formLocale].uploadName|escape:"url"}" width="{$pageHeaderTitleImage[$formLocale].width|escape}" height="{$pageHeaderTitleImage[$formLocale].height|escape}" style="border: 0;" alt="{translate key="common.pageHeader.altText"}" />
<br />
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="pageHeaderTitleImageAltText" key="common.altText"}</td>
		<td width="80%" class="value"><input type="text" name="pageHeaderTitleImageAltText[{$formLocale|escape}]" value="{$pageHeaderTitleImage[$formLocale].altText|escape}" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value"><span class="instruct">{translate key="common.altTextInstructions"}</span></td>
		</tr>
</table>
{/if}

<h4>{translate key="settings.setup.pressLogo"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{translate key="settings.setup.useImageLogo"}</td>
		<td width="80%" class="value">{fbvFileInput id="pageHeaderLogoImage" submit="uploadPageHeaderLogoImage"}</td>
	</tr>
</table>

{if $pageHeaderLogoImage[$formLocale]}
{translate key="common.fileName"}: {$pageHeaderLogoImage[$formLocale].name|escape} {$pageHeaderLogoImage[$formLocale].dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deletePageHeaderLogoImage" value="{translate key="common.delete"}" class="button" />
<br />
<img src="{$publicFilesDir}/{$pageHeaderLogoImage[$formLocale].uploadName|escape:"url"}" width="{$pageHeaderLogoImage[$formLocale].width|escape}" height="{$pageHeaderLogoImage[$formLocale].height|escape}" style="border: 0;" alt="{translate key="common.pageHeaderLogo.altText"}" />
<br />
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="pageHeaderLogoImageAltText" key="common.altText"}</td>
		<td width="80%" class="value"><input type="text" name="pageHeaderLogoImageAltText[{$formLocale|escape}]" value="{$pageHeaderLogoImage[$formLocale].altText|escape}" size="40" maxlength="255" class="textField" /></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td class="value"><span class="instruct">{translate key="common.altTextInstructions"}</span></td>
		</tr>
</table>
{/if}

{fbvFormArea id="alternateHeader"}
{fbvFormSection title="settings.setup.alternateHeader"}
	<p>{translate key="settings.setup.alternateHeaderDescription"}</p>
	{fbvElement type="textarea" name="pressPageHeader[$formLocale]" id="pressPageHeader" value=$pressPageHeader[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>


<h3>5.7 {translate key="settings.setup.pressPageFooter"}</h3>

<p>{translate key="settings.setup.pressPageFooterDescription"}</p>

{fbvFormArea id="pressPageFooterContainer"}
{fbvFormSection}
	{fbvElement type="textarea" name="pressPageFooter[$formLocale]" id="pressPageFooter" value=$pressPageFooter[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<h3>5.8 {translate key="settings.setup.navigationBar"}</h3>

<p>{translate key="settings.setup.itemsDescription"}</p>


{fbvFormArea id="navigationBar"}
{foreach name=navItems from=$navItems[$formLocale] key=navItemId item=navItem}
	{fbvFormSection title="settings.setup.labelName" for="navItems-$navItemId-name" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" name="navItems[$formLocale][$navItemId][name]" id="navItems-$navItemId-name" value=$navItem.name size=$fbvStyles.size.SMALL maxlength="90"}
		<input type="submit" name="delNavItem[{$navItemId|escape}]" value="{translate key="common.delete"}" class="button" />
		{fbvElement type="checkbox" id="navItems-$navItemId-isLiteral" name="navItems[$formLocale][$navItemId][isLiteral]" value="1" checked=$navItem.isLiteral label="settings.setup.navItemIsLiteral"}
	{/fbvFormSection}
	{fbvFormSection title="common.url" for="navItems-$navItemId-url" float=$fbvStyles.float.RIGHT}
		{fbvElement type="text" name="navItems[$formLocale][$navItemId][url]" id="navItems-$navItemId-url" value=$navItem.url size=$fbvStyles.size.SMALL maxlength="255"}
		{fbvElement type="checkbox" id="navItems-$navItemId-isAbsolute" name="navItems[$formLocale][$navItemId][isAbsolute]" value="1" checked=$navItem.isAbsolute label="settings.setup.navItemIsAbsolute"}
	{/fbvFormSection}
{foreachelse}
	{fbvFormSection title="settings.setup.labelName" for="navItems-0-name" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" name="navItems[$formLocale][0][name]" id="navItems-0-name" value=$navItem.name size=$fbvStyles.size.SMALL maxlength="90"}
		{fbvElement type="checkbox" id="navItems-0-isLiteral" name="navItems[$formLocale][0][isLiteral]" value="1" checked=$navItem.isLiteral label="settings.setup.navItemIsLiteral"}
	{/fbvFormSection}
	{fbvFormSection title="common.url" for="navItems-0-url" float=$fbvStyles.float.RIGHT}
		{fbvElement type="text" name="navItems[$formLocale][0][url]" id="navItems-0-url" value=$navItem.url size=$fbvStyles.size.SMALL maxlength="255"}
		{fbvElement type="checkbox" id="navItems-0-isAbsolute" name="navItems[$formLocale][0][isAbsolute]" value="1" checked=$navItem.isAbsolute label="settings.setup.navItemIsAbsolute"}
	{/fbvFormSection}
{/foreach}
{/fbvFormArea}

<p><input type="submit" name="addNavItem" value="{translate key="settings.setup.addNavItem"}" class="button" /></p>


<div class="separator"></div>

<h3>5.9 {translate key="settings.setup.lists"}</h3>
<p>{translate key="settings.setup.listsDescription"}</p>

{fbvFormArea id="lists"}
{fbvFormSection float=$fbvStyles.float.LEFT title="settings.setup.itemsPerPage"}
	{fbvElement type="text" id="itemsPerPage" value=$itemsPerPage size=$fbvStyles.size.SMALL}
{/fbvFormSection}
{fbvFormSection float=$fbvStyles.float.RIGHT title="settings.setup.numPageLinks"}
	{fbvElement type="text" id="numPageLinks" value=$numPageLinks size=$fbvStyles.size.SMALL}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<p><input type="submit" onclick="prepBlockFields()" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="setup" escape=false}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>
</div>

{include file="common/footer.tpl"}
