{**
 * templates/common/footer.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site footer.
 *}

</div><!-- pkp_structure_main -->
</div><!-- pkp_structure_content -->
</div><!-- pkp_structure_body -->

<div class="pkp_structure_foot">
</div><!-- pkp_structure_foot -->

{if $footerCategories|@count > 0}{* include a subfoot section if there are footer link categories defined *}
	<div class="pkp_structure_subfoot">
		<div class="pkp_structure_content">
			{foreach from=$footerCategories item=category name=loop}
				{assign var=links value=$category->getLinks()}
				<div class="unit size1of{$footerCategories|@count} {if $smarty.foreach.loop.last}lastUnit{/if}">
					<h4>{$category->getLocalizedTitle()}</h4>
					<ul>
						{foreach from=$links item=link}
							<li><a href="{$link->getUrl()}">{$link->getLocalizedTitle()}</a></li>
						{/foreach}
						{if $links|@count < $maxLinks}
							{section name=padding start=$links|@count loop=$maxLinks step=1}
								<li>&nbsp;</li>
							{/section}
						{/if}
					</ul>
				</div>
			{/foreach}
		</div><!-- pkp_structure_content -->
	</div><!-- pkp_structure_subfoot -->
{/if}
<div class="pkp_structure_subfoot">
	<div class="pkp_structure_content">
		<div class="unit size1of3">
			<h4>{translate key="common.openMonographPress"}</h4>
		</div>
		<div class="unit size1of3">
			{*
			<ul class="sf-menu">
				<li><a href="{url page="about" op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>
				<li><a href="#">!Acknowledgements</a></li>
			</ul>
			<br />
			{if $displayCreativeCommons}
			{translate key="common.ccLicense"}
			{/if}
			{if $pageFooter}
			<br />
			{$pageFooter}
			{/if}
			{call_hook name="Templates::Common::Footer::PageFooter"}
			*}
		</div>
		<div class="unit size1of3 lastUnit">
			<h4 class="pkp_helpers_align_right">{translate key="common.publicKnowledgeProject"}</h4>
		</div>
	</div><!-- pkp_structure_content -->
</div><!-- pkp_structure_subfoot -->

{get_debug_info}
{if $enableDebugStats}{include file=$pqpTemplate}{/if}

</div><!-- pkp_structure_page -->

{$additionalFooterData}
</body>
</html>

