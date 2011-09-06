{**
 * controllers/notification/copyeditingSignoffNotificationContent.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Content for NOTIFICATION_TYPE_COPYEDIT_SIGNOFF.
 *
 *}
 <span id="{$signoffFileLinkAction->getId()}" class="pkp_linkActions">
	{include file="linkAction/linkAction.tpl" action=$signoffFileLinkAction contextId="copyeditSignoffNotification"}
</span>