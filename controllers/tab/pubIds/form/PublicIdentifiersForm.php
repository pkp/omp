<?php

/**
 * @file controllers/tab/pubIds/form/PublicIdentifiersForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicIdentifiersForm
 * @ingroup controllers_tab_pubIds_form
 *
 * @brief Displays a pub ids form.
 */

namespace APP\controllers\tab\pubIds\form;

use APP\core\Application;
use APP\template\TemplateManager;
use PKP\controllers\tab\pubIds\form\PKPPublicIdentifiersForm;
use PKP\db\DAORegistry;

class PublicIdentifiersForm extends PKPPublicIdentifiersForm
{
    /**
     * @copydoc Form::fetch()
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $enablePublisherId = (array) $request->getContext()->getData('enablePublisherId');
        $templateMgr->assign([
            'enablePublisherId' => (is_a($this->getPubObject(), 'Chapter') && in_array('chapter', $enablePublisherId)) ||
                    (is_a($this->getPubObject(), 'Representation') && in_array('representation', $enablePublisherId)) ||
                    (is_a($this->getPubObject(), 'SubmissionFile') && in_array('file', $enablePublisherId)),
        ]);

        return parent::fetch($request, $template, $display);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        parent::execute(...$functionArgs);
        $pubObject = $this->getPubObject();
        if (is_a($pubObject, 'Chapter')) {
            $chapterDao = DAORegistry::getDAO('ChapterDAO'); /** @var ChapterDAO $chapterDao */
            $chapterDao->updateObject($pubObject);
        }
    }

    /**
     * @copydoc PKPPublicIdentifiersForm::getAssocType()
     */
    public function getAssocType($pubObject)
    {
        if (is_a($pubObject, 'Chapter')) {
            return Application::ASSOC_TYPE_CHAPTER;
        }
        return parent::getAssocType($pubObject);
    }
}
