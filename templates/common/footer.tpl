{**
 * footer.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site footer.
 *
 *}

</div><!-- content -->
</div><!-- main -->
</div><!-- body -->

<div class="pkp_structure_footerPanel">
	<div class="unit size1of3">
		{translate key="common.publicKnowledgeProject"}
	</div>
	<div class="unit size1of3 pkp_helpers_text_center">
		<ul class="flat_list navigation">
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
	</div>
	<div class="unit size1of3 lastUnit pkp_helpers_align_right">
		{translate key="common.openMonographPress"}
	</div>
</div> <!-- /footPanel -->

{get_debug_info}
{if $enableDebugStats}{include file=$pqpTemplate}{/if}

</div><!-- /pagePanel -->

{if !empty($systemNotifications)}
	{translate|assign:"defaultTitleText" key="notification.notification"}
	<script type="text/javascript">
	<!--
	{foreach from=$systemNotifications item=notification}
		{literal}
			$.pnotify({
				pnotify_title: '{/literal}{if $notification->getIsLocalized()}{translate|escape:"js"|default:$defaultTitleText key=$notification->getTitle()}{else}{$notification->getTitle()|escape:"js"|default:$defaultTitleText}{/if}{literal}',
				pnotify_text: '{/literal}{if $notification->getIsLocalized()}{translate|escape:"js" key=$notification->getContents() param=$notification->getParam()}{else}{$notification->getContents()|escape:"js"}{/if}{literal}',
				pnotify_addclass: '{/literal}{$notification->getStyleClass()|escape:"js"}{literal}',
				pnotify_notice_icon: 'notifyIcon {/literal}{$notification->getIconClass()|escape:"js"}{literal}'
			});
		{/literal}
	{/foreach}
	// -->
	</script>
{/if}{* systemNotifications *}

{$additionalFooterData}
</body>
</html>

