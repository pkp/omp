{**
 * controllers/tab/settings/appearance/form/appearanceForm.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Website appearance management form.
 *
 *}

{* Help Link *}
{help file="settings.md" section="website" class="pkp_help_tab"}

{include file="core:controllers/tab/settings/appearance/form/setup.tpl"}
<form id="appearanceForm" class="pkp_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.WebsiteSettingsTabHandler" op="saveFormData" tab="appearance"}">
	{csrf}

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="appearanceFormNotification"}
	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	{* Header *}
	{include file="core:controllers/tab/settings/appearance/form/header.tpl"}

	{* Footer *}
	{include file="core:controllers/tab/settings/appearance/form/footer.tpl"}

	{* Theme and stylesheet *}
	{include file="core:controllers/tab/settings/appearance/form/theme.tpl"}
	{include file="core:controllers/tab/settings/appearance/form/stylesheet.tpl"}

	{* Sidebar *}
	{include file="core:controllers/tab/settings/appearance/form/sidebar.tpl"}

	{* Homepage Image *}
	{include file="core:controllers/tab/settings/appearance/form/homepageImage.tpl"}

	{* New/Featured/Spotlight Display Toggles *}
	{fbvFormSection list="true" label="manager.setup.displayOnHomepage"}
		{fbvElement type="checkbox" label="manager.setup.displayInSpotlight" id="displayInSpotlight" checked=$displayInSpotlight}
		{fbvElement type="checkbox" label="manager.setup.displayFeaturedBooks" id="displayFeaturedBooks" checked=$displayFeaturedBooks}
		{fbvElement type="checkbox" label="manager.setup.displayNewReleases" id="displayNewReleases" checked=$displayNewReleases}
	{/fbvFormSection}

	{* Additional Homepage Content *}
	{include file="core:controllers/tab/settings/appearance/form/additionalHomepageContent.tpl"}

	{* Catalog sorting option *}
	{fbvFormSection label="catalog.sortBy" description="catalog.sortBy.catalogDescription" for="catalogSortOption"}
		{fbvElement type="select" id="catalogSortOption" from=$sortOptions selected=$catalogSortOption translate=false}
	{/fbvFormSection}

	{* List Display Options *}
	{include file="core:controllers/tab/settings/appearance/form/lists.tpl"}

	{* Cover Thumbnails Size *}
	{fbvFormArea id="thumbnailsSizeSettings" title="manager.setup.coverThumbnails" class="border"}
		{fbvFormSection description="manager.setup.coverThumbnailsDescription"}
			{fbvElement type="text" id="coverThumbnailsMaxWidth" value=$coverThumbnailsMaxWidth size=$fbvStyles.size.SMALL label="manager.setup.coverThumbnailsMaxWidth" required="true"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="coverThumbnailsMaxHeight" value=$coverThumbnailsMaxHeight size=$fbvStyles.size.SMALL label="manager.setup.coverThumbnailsMaxHeight" required="true"}
		{/fbvFormSection}
		{fbvFormSection list="true"}
			{fbvElement type="checkbox" label="manager.setup.coverThumbnailsResize" id="coverThumbnailsResize" checked=$coverThumbnailsResize}
		{/fbvFormSection}
	{/fbvFormArea}

	{* Save button *}
	{if !$wizardMode}
		{fbvFormButtons id="appearanceFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>
