{**
 * controllers/tab/settings/appearance/form/appearanceForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
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

	<h3>{translate key="manager.setup.pressHomepageHeader"}</h3>
	<p>{translate key="manager.setup.pressHomepageHeaderDescription"}</p>
	<h4>{translate key="manager.setup.pressName"}</h4>
	{fbvFormArea id="homepageHeader"}
		{fbvFormSection list=true}
			{fbvElement type="radio" name="homeHeaderTitleType[$locale]" id="homeHeaderTitleType-0" value=0 checked=!$homeHeaderTitleType[$locale] label="manager.setup.useTextTitle"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" name="homeHeaderTitle" id="homeHeaderTitle" value=$homeHeaderTitle multilingual=true}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{fbvElement type="radio" name="homeHeaderTitleType[$locale]" id="homeHeaderTitleType-1" value=1 checked=$homeHeaderTitleType[$locale] label="manager.setup.useImageTitle" inline=true}
			<div id="{$uploadImageLinkActions.homeHeaderTitleImage->getId()}" class="pkp_linkActions inline">
				{include file="linkAction/linkAction.tpl" action=$uploadImageLinkActions.homeHeaderTitleImage contextId="appearanceForm"}
			</div>
			<div id="homeHeaderTitleImage">
				{$imagesViews.homeHeaderTitleImage}
			</div>
		{/fbvFormSection}
	{/fbvFormArea}

	<div {if $wizardMode}class="pkp_form_hidden"{/if}>
		<h4>{translate key="manager.setup.useImageLogo"}</h4>
		<div id="{$uploadImageLinkActions.homeHeaderLogoImage->getId()}" class="pkp_linkActions">
			{include file="linkAction/linkAction.tpl" action=$uploadImageLinkActions.homeHeaderLogoImage contextId="appearanceForm"}
		</div>
		<div id="homeHeaderLogoImage">
			{$imagesViews.homeHeaderLogoImage}
		</div>
	</div>

	<h3>{translate key="manager.setup.pressHomepageContent"}</h3>
	<p>{translate key="manager.setup.pressHomepageContentDescription"}</p>
	{fbvFormArea id="pressHomePageContent"}
		{fbvFormSection title="manager.setup.pressDescription"}
			<p>{translate key="manager.setup.pressDescriptionDescription"}</p>
			{fbvElement type="textarea" multilingual=true name="description" id="description" value=$description rich=true}
		{/fbvFormSection}
		<div {if $wizardMode}class="pkp_form_hidden"{/if}>
			{fbvFormSection title="manager.setup.homepageImage"}
				<p>{translate key="manager.setup.homepageImageDescription"}</p>
				<div id="{$uploadImageLinkActions.homepageImage->getId()}" class="pkp_linkActions">
					{include file="linkAction/linkAction.tpl" action=$uploadImageLinkActions.homepageImage contextId="appearanceForm"}
				</div>
				<div id="homepageImage">
					{$imagesViews.homepageImage}
				</div>
			{/fbvFormSection}
			{fbvFormSection title="manager.setup.additionalContent"}
				<p>{translate key="manager.setup.additionalContentDescription"}</p>
				{fbvElement type="textarea" multilingual=true name="additionalHomeContent" id="additionalHomeContent" value=$additionalHomeContent size=$fbvStyles.size.MEDIUM  rich=true}
			{/fbvFormSection}
		</div>
	{/fbvFormArea}

	<h3>{translate key="manager.setup.pressPageHeader"}</h3>
	<p>{translate key="manager.setup.pressPageHeaderDescription"}</p>
	<h4>{translate key="manager.setup.pressName"}</h4>
	{fbvFormArea id="pageHeader"}
		{fbvFormSection list=true}
			{fbvElement type="radio" name="pageHeaderTitleType[$locale]" id="pageHeaderTitleType-0" value=0 checked=!$pageHeaderTitleType[$locale] label="manager.setup.useTextTitle"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" name="pageHeaderTitle" id="pageHeaderTitle" value=$pageHeaderTitle multilingual=true}
		{/fbvFormSection}
		{fbvFormSection list=true}
			{fbvElement type="radio" name="pageHeaderTitleType[$locale]" id="pageHeaderTitleType-1" value=1 checked=$pageHeaderTitleType[$locale] label="manager.setup.useImageTitle" inline=true}
			<div id="{$uploadImageLinkActions.pageHeaderTitleImage->getId()}" class="pkp_linkActions inline">
				{include file="linkAction/linkAction.tpl" action=$uploadImageLinkActions.pageHeaderTitleImage contextId="appearanceForm"}
			</div>
			<div id="pageHeaderTitleImage">
				{$imagesViews.pageHeaderTitleImage}
			</div>
		{/fbvFormSection}
	{/fbvFormArea}

	<div {if $wizardMode}class="pkp_form_hidden"{/if}>
		<h4>{translate key="manager.setup.pressLogo"}</h4>
		<div id="{$uploadImageLinkActions.pageHeaderLogoImage->getId()}" class="pkp_linkActions">
			{include file="linkAction/linkAction.tpl" action=$uploadImageLinkActions.pageHeaderLogoImage contextId="appearanceForm"}
		</div>
		<div id="pageHeaderLogoImage">
			{$imagesViews.pageHeaderLogoImage}
		</div>

		{fbvFormArea id="alternateHeader"}
			{fbvFormSection title="manager.setup.alternateHeader"}
				<p>{translate key="manager.setup.alternateHeaderDescription"}</p>
				{fbvElement type="textarea" multilingual=true name="pressPageHeader" id="pressPageHeader" value=$pressPageHeader size=$fbvStyles.size.MEDIUM  rich=true}
			{/fbvFormSection}
		{/fbvFormArea}

		<h3>{translate key="manager.setup.pressPageFooter"}</h3>
		<p>{translate key="manager.setup.pressPageFooterDescription"}</p>

		{fbvFormArea id="pressPageFooterContainer"}
			{fbvFormSection}
				{fbvElement type="textarea" multilingual=true name="pressPageFooter" id="pressPageFooter" value=$pressPageFooter size=$fbvStyles.size.MEDIUM  rich=true}
			{/fbvFormSection}
		{/fbvFormArea}

		<h3>{translate key="manager.setup.pressLayout"}</h3>
		<p>{translate key="manager.setup.pressLayoutDescription"}</p>
		{fbvFormArea id="pressLayout"}
			{fbvFormSection title="manager.setup.pressTheme"}
				{if !$pressTheme}
					{assign var="themeEnabled" value=false}
				{/if}
				{fbvElement type="select" id="pressThemes" from=$pressThemes selected=$themeEnabled enanbled=false translate=false}
			{/fbvFormSection}
			{fbvFormSection title="manager.setup.usePressStyleSheet"}
				<div id="{$uploadCssLinkAction->getId()} class="pkp_linkActions">
					{include file="linkAction/linkAction.tpl" action=$uploadCssLinkAction contextId="appearanceForm"}
				</div>
				<div id="pressStyleSheet">
					{$pressStyleSheetView}
				</div>
			{/fbvFormSection}
		{/fbvFormArea}

		<h3>{translate key="manager.setup.navigationBar"}</h3>
		<p>{translate key="manager.setup.itemsDescription"}</p>

		<h3>{translate key="manager.setup.lists"}</h3>
		<p>{translate key="manager.setup.listsDescription"}</p>

		{fbvFormArea id="advancedAppearanceSettings"}
			{fbvFormSection}
				{fbvElement type="text" id="itemsPerPage" value=$itemsPerPage size=$fbvStyles.size.SMALL}
			{/fbvFormSection}
			{fbvFormSection}
				{fbvElement type="text" id="numPageLinks" value=$numPageLinks size=$fbvStyles.size.SMALL}
			{/fbvFormSection}
			{fbvFormSection title="manager.setup.recentTitles"}
				{fbvElement type="text" label="manager.setup.numRecentTitlesOnHomepage" id="numRecentTitlesOnHomepage" name="numRecentTitlesOnHomepage" value="$numRecentTitlesOnHomepage" size="3"}
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	{if !$wizardMode}
		{fbvFormButtons id="appearanceFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>