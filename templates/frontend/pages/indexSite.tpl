{**
 * templates/frontend/pages/indexSite.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Site index.
 *
 *}
{include file="frontend/components/header.tpl"}

<div class="page_index_site">

	{if $highlights->count()}
		{include file="frontend/components/highlights.tpl" highlights=$highlights}
	{/if}

	{if $about}
		<div class="about_site">
			{$about}
		</div>
	{/if}

	{include file="frontend/objects/announcements_list.tpl" numAnnouncements=$numAnnouncementsHomepage}

	<div class="presses">
		<h2>
			{translate key="context.contexts"}
		</h2>
		{if !$presses|@count}
			{translate key="site.noPresses"}
		{else}
			<ul>
				{foreach from=$presses item=press}
					{capture assign="url"}{url press=$press->getPath()}{/capture}
					{assign var="thumb" value=$press->getLocalizedData('pressThumbnail')}
					{assign var="description" value=$press->getLocalizedDescription()}
					<li{if $thumb} class="has_thumb"{/if}>
						{if $thumb}
							<div class="thumb">
								<a href="{$url}">
									<img src="{$pressesFilesPath}{$press->getId()}/{$thumb.uploadName|escape:"url"}"{if $thumb.altText} alt="{$thumb.altText|escape|default:''}"{/if}>
								</a>
							</div>
						{/if}

						<div class="body">
							<h3>
								<a href="{$url}" rel="bookmark">
									{$press->getLocalizedName()}
								</a>
							</h3>
							{if $description}
								<div class="description">
									{$description}
								</div>
							{/if}
							<ul class="links">
								<li class="view">
									<a href="{$url}">
										{translate key="site.pressView"}
									</a>
								</li>
							</ul>
						</div>
					</li>
				{/foreach}
			</ul>
		{/if}
	</div>

</div><!-- .page -->

{include file="frontend/components/footer.tpl"}
