{**
 * controllers/tab/settings/appearance/form/appearanceForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Website appearance management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#appearanceForm').pkpHandler('$.pkp.controllers.tab.settings.form.FileViewFormHandler',
			{ldelim}
				fetchFileUrl: '{url|escape:javascript op='fetchFile' tab='appearance' escape=false}',
			{rdelim}
		);
	{rdelim});
</script>

<form id="appearanceForm" class="pkp_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="saveFormData" tab="appearance"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="appearanceFormNotification"}
	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	<p class="pkp_grid_description">{translate key="manager.setup.pressAppearanceDescription"}</p>

	{* Homepage Header *}
	{fbvFormArea id="homepageHeader" title="manager.setup.pressHomepageHeader" border="true"}
		{fbvFormSection list=true description="manager.setup.pressHomepageHeaderDescription" label="manager.setup.pressName"}
			{fbvElement type="radio" name="homeHeaderTitleType[$locale]" id="homeHeaderTitleType-0" value=0 checked=!$homeHeaderTitleType[$locale] label="manager.setup.useTextTitle"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" name="homeHeaderTitle" id="homeHeaderTitle" value=$homeHeaderTitle multilingual=true}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{fbvElement type="radio" name="homeHeaderTitleType[$locale]" id="homeHeaderTitleType-1" value=1 checked=$homeHeaderTitleType[$locale] label="manager.setup.useImageTitle" inline=true}
			<div id="{$uploadImageLinkActions.homeHeaderTitleImage->getId()}" class="pkp_linkActions inline">
				{include file="linkAction/buttonGenericLinkAction.tpl" buttonSelector="#homeHeaderTitleImageButton" action=$uploadImageLinkActions.homeHeaderTitleImage}
				{fbvElement type="button" class="submitFormButton" id="homeHeaderTitleImageButton" label="common.upload"}
			</div>
			<div id="homeHeaderTitleImage">
				{$imagesViews.homeHeaderTitleImage}
			</div>
		{/fbvFormSection}
		<div {if $wizardMode}class="pkp_form_hidden"{/if}>
			{fbvFormSection label="manager.setup.useImageLogo" description="manager.setup.useImageLogoDescription"}
			<div id="{$uploadImageLinkActions.homeHeaderLogoImage->getId()}" class="pkp_linkActions">
				{include file="linkAction/buttonGenericLinkAction.tpl" buttonSelector="#homeHeaderLogoImageButton" action=$uploadImageLinkActions.homeHeaderLogoImage}
				{fbvElement type="button" class="submitFormButton" id="homeHeaderLogoImageButton" label="common.upload"}
			</div>
			<div id="homeHeaderLogoImage">
				{$imagesViews.homeHeaderLogoImage}
			</div>
			{/fbvFormSection}
		</div>
	{/fbvFormArea}
	{* end Homepage Header *}

	{* Homepage Content *}
	{fbvFormArea id="pressHomePageContent" title="manager.setup.pressHomepageContent" border="true"}
		{fbvFormSection description="manager.setup.pressHomepageContentDescription"}
		{/fbvFormSection}
		{fbvFormSection label="manager.setup.pressDescription" description="manager.setup.pressDescriptionDescription"}
			{fbvElement type="textarea" multilingual=true name="description" id="description" value=$description rich=true}
		{/fbvFormSection}
		<div {if $wizardMode}class="pkp_form_hidden"{/if}>
			{fbvFormSection label="manager.setup.homepageImage" description="manager.setup.homepageImageDescription"}
				<div id="{$uploadImageLinkActions.homepageImage->getId()}" class="pkp_linkActions">
					{include file="linkAction/buttonGenericLinkAction.tpl" buttonSelector="#homepageImageButton" action=$uploadImageLinkActions.homepageImage}
					{fbvElement type="button" class="submitFormButton" id="homepageImageButton" label="common.upload"}
				</div>
				<div id="homepageImage">
					{$imagesViews.homepageImage}
				</div>
			{/fbvFormSection}
			{fbvFormSection label="manager.setup.additionalContent" description="manager.setup.additionalContentDescription"}
				{fbvElement type="textarea" multilingual=true name="additionalHomeContent" id="additionalHomeContent" value=$additionalHomeContent rich=true}
			{/fbvFormSection}
		</div>
	{/fbvFormArea}
	{* end Homepage Content *}

	{* Press Page Header *}
	{fbvFormArea id="pageHeader" title="manager.setup.pressPageHeader" border="true"}
		{fbvFormSection list=true description="manager.setup.pressPageHeaderDescription" title="manager.setup.pressName"}
			{fbvElement type="radio" name="pageHeaderTitleType[$locale]" id="pageHeaderTitleType-0" value=0 checked=!$pageHeaderTitleType[$locale] label="manager.setup.useTextTitle"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" name="pageHeaderTitle" id="pageHeaderTitle" value=$pageHeaderTitle multilingual=true}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{fbvElement type="radio" name="pageHeaderTitleType[$locale]" id="pageHeaderTitleType-1" value=1 checked=$pageHeaderTitleType[$locale] label="manager.setup.useImageTitle" inline=true}
			<div id="{$uploadImageLinkActions.pageHeaderTitleImage->getId()}" class="pkp_linkActions inline">
				{include file="linkAction/buttonGenericLinkAction.tpl" buttonSelector="#pageHeaderTitleImageButton" action=$uploadImageLinkActions.pageHeaderTitleImage}
				{fbvElement type="button" class="submitFormButton" id="pageHeaderTitleImageButton" label="common.upload"}
			</div>
			<div id="pageHeaderTitleImage">
				{$imagesViews.pageHeaderTitleImage}
			</div>
		{/fbvFormSection}
		<div {if $wizardMode}class="pkp_form_hidden"{/if}>
			{fbvFormSection label="manager.setup.pressLogo" description="manager.setup.useImageLogoDescription"}
			<div id="{$uploadImageLinkActions.pageHeaderLogoImage->getId()}" class="pkp_linkActions">
				{include file="linkAction/buttonGenericLinkAction.tpl" buttonSelector="#pageHeaderLogoImageButton" action=$uploadImageLinkActions.pageHeaderLogoImage}
				{fbvElement type="button" class="submitFormButton" id="pageHeaderLogoImageButton" label="common.upload"}
			</div>
			<div id="pageHeaderLogoImage">
				{$imagesViews.pageHeaderLogoImage}
			</div>
		{/fbvFormSection}
			{fbvFormSection label="manager.setup.alternateHeader" description="manager.setup.alternateHeaderDescription"}
				{fbvElement type="textarea" multilingual=true name="pressPageHeader" id="pressPageHeader" value=$pressPageHeader rich=true}
			{/fbvFormSection}
		</div>
	{/fbvFormArea}
	{* end Press Page Header *}

	{* Press Page Footer *}
	{fbvFormArea id="pressPageFooterContainer" title="manager.setup.pressPageFooter" border="true"}
		{fbvFormSection description="manager.setup.pressPageFooterDescription"}
			{fbvElement type="textarea" multilingual=true name="pressPageFooter" id="pressPageFooter" value=$pressPageFooter rich=true}
		{/fbvFormSection}
	{/fbvFormArea}
	{* end Press Page Footer *}

	{* Press Layout *}
	{fbvFormArea id="pressLayout" title="manager.setup.pressLayout" border="true"}
		{fbvFormSection title="manager.setup.pressTheme" description="manager.setup.pressLayoutDescription"}
			{if !$pressTheme}
				{assign var="themeEnabled" value=false}
			{/if}
			{fbvElement type="select" id="pressThemes" from=$pressThemes selected=$themeEnabled enabled=false translate=false}
		{/fbvFormSection}

		{fbvFormSection title="manager.setup.usePressStyleSheet" description="manager.setup.pressStyleSheetDescription"}
			<div id="{$uploadCssLinkAction->getId()}" class="pkp_linkActions">
				{include file="linkAction/buttonGenericLinkAction.tpl" buttonSelector="#uploadCssLinkActionButton" action=$uploadCssLinkAction}
				{fbvElement type="button" class="submitFormButton" id="uploadCssLinkActionButton" label="common.upload"}
			</div>
			<div id="pressStyleSheet">
				{$pressStyleSheetView}
			</div>
		{/fbvFormSection}
	{/fbvFormArea}
	{* end Press Layout *}

	<!-- ** FIXME: there's a header and description for navigation bar stuff, but no actual code **
	<h3>{translate key="manager.setup.navigationBar"}</h3>
	<p>{translate key="manager.setup.itemsDescription"}</p>
	-->

	{* Lists *}
	{fbvFormArea id="advancedAppearanceSettings" title="manager.setup.lists" border="true"}
		{fbvFormSection description="manager.setup.listsDescription"}
			{fbvElement type="text" id="itemsPerPage" value=$itemsPerPage size=$fbvStyles.size.SMALL label="manager.setup.itemsPerPage"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="numPageLinks" value=$numPageLinks size=$fbvStyles.size.SMALL label="manager.setup.numPageLinks"}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.recentTitles"}
			{fbvElement type="text" label="manager.setup.numRecentTitlesOnHomepage" id="numRecentTitlesOnHomepage" name="numRecentTitlesOnHomepage" value="$numRecentTitlesOnHomepage" size="3"}
		{/fbvFormSection}
	{/fbvFormArea}
	{* end Lists *}

	{if !$wizardMode}
		{fbvFormButtons id="appearanceFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>