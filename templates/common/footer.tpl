{**
 * footer.tpl
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site footer.
 *
 *}

</div><!-- content -->
</div><!-- main -->
</div><!-- body -->

<div class="foot">
	<div class="unit size1of3">
		{translate key="common.publicKnowledgeProject"}
	</div>
	<div class="unit size1of3 text_center">
		<ul class="flat_list footer_navigation">
			<li><a href="{url page="about" op="submissions" anchor="privacyStatement"}">{translate key="about.privacyStatement"}</a></li>
			<li><a href="acknowledgements.php">!Acknowledgements</a></li>
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
	</div>
	<div class="unit size1of3 lastUnit align_right">
		{translate key="common.openMonographPress"}
	</div>
</div> <!-- /foot -->

{get_debug_info}
{if $enableDebugStats}{include file=$pqpTemplate}{/if}

</div><!-- page -->
{$additionalFooterData}
</body>
</html>
