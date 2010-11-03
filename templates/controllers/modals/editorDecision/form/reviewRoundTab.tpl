{**
 * initiateReviewForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to initiate the first review round.
 *
 *}

$("<li class='ui-state-default ui-corner-top ui-state-active'><a href='{$newRoundUrl}'>{translate key='submission.round' round=$round}</a></li>").insertBefore('#newRoundTabContainer');