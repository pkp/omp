{**
 * reviewRoundTab.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Code snippet to initiate a new review round on the client side.
 *}

$("<li class='ui-state-default ui-corner-top ui-state-active'><a href='{$newRoundUrl}'>{translate key='submission.round' round=$round}</a></li>").insertBefore('#newRoundTabContainer');