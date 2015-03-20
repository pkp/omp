{**
 * controllers/tab/settings/appearance/form/appearanceForm.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Website appearance management form.
 *
 *}
{capture assign="newContentFormContent"}
	{fbvFormSection list="true" label="manager.setup.newReleases"}
		{fbvElement type="checkbox" label="manager.setup.displayNewReleases" id="displayNewReleases" checked=$displayNewReleases}
	{/fbvFormSection}
{/capture}
{capture assign="featuredContentFormContent"}
	{fbvFormSection list="true" label="manager.setup.featuredBooks"}
		{fbvElement type="checkbox" label="manager.setup.displayFeaturedBooks" id="displayFeaturedBooks" checked=$displayFeaturedBooks}
	{/fbvFormSection}
	{fbvFormSection list="true" label="manager.setup.inSpotlight"}
		{fbvElement type="checkbox" label="manager.setup.displayInSpotlight" id="displayInSpotlight" checked=$displayInSpotlight}
	{/fbvFormSection}
{/capture}
{capture assign="thumbnailsSizeSettings"}
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
{/capture}
{include file="core:controllers/tab/settings/appearance/form/appearanceForm.tpl" newContentFormContent=$newContentFormContent featuredContentFormContent=$featuredContentFormContent}
