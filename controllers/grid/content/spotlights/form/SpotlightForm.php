<?php
/**
 * @file controllers/grid/content/spotlights/form/SpotlightForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SpotlightForm
 *
 * @ingroup controllers_grid_content_spotlights_form
 *
 * @brief Form for reading/creating/editing spotlight items.
 */

namespace APP\controllers\grid\content\spotlights\form;

use APP\facades\Repo;
use APP\spotlight\Spotlight;
use APP\spotlight\SpotlightDAO;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\form\Form;

class SpotlightForm extends Form
{
    /**
     * @var int spotlightId
     */
    public $_spotlightId;

    /**
     * @var int pressId
     */
    public $_pressId;

    /**
     * Constructor
     *
     * @param int $pressId
     * @param int $spotlightId leave as default for new spotlight
     */
    public function __construct($pressId, $spotlightId = null)
    {
        parent::__construct('controllers/grid/content/spotlights/form/spotlightForm.tpl');

        $this->_spotlightId = $spotlightId;
        $this->_pressId = $pressId;

        $form = $this;
        $this->addCheck(new \PKP\form\validation\FormValidatorCustom($this, 'assocId', 'required', 'grid.content.spotlights.itemRequired', function ($assocId) use ($form) {
            [$id, $type] = preg_split('/:/', $assocId);
            return is_numeric($id) && $id > 0 && $form->_isValidSpotlightType($type);
        }));
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'title', 'required', 'grid.content.spotlights.titleRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }


    //
    // Extended methods from Form
    //
    /**
     * @copydoc Form::fetch()
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);

        $spotlightDao = DAORegistry::getDAO('SpotlightDAO'); /** @var SpotlightDAO $spotlightDao */
        $spotlight = $spotlightDao->getById($this->getSpotlightId());
        $templateMgr->assign([
            'spotlight' => $spotlight,
            'pressId' => $this->getPressId()
        ]);

        if (isset($spotlight)) {
            $templateMgr->assign([
                'title' => $spotlight->getTitle(null),
                'description' => $spotlight->getDescription(null),
                'assocTitle' => $this->getAssocTitle($spotlight->getAssocId(), $spotlight->getAssocType()),
                'assocId' => $spotlight->getAssocId() . ':' . $spotlight->getAssocType(),
            ]);
        }

        return parent::fetch($request, $template, $display);
    }

    //
    // Extended methods from Form
    //
    /**
     * @see Form::readInputData()
     */
    public function readInputData()
    {
        $this->readUserVars(['title', 'description', 'assocId']);
    }

    /**
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        $spotlightDao = DAORegistry::getDAO('SpotlightDAO'); /** @var SpotlightDAO $spotlightDao */

        $spotlight = $spotlightDao->getById($this->getSpotlightId(), $this->getPressId());

        if (!$spotlight) {
            // this is a new spotlight
            $spotlight = $spotlightDao->newDataObject();
            $spotlight->setPressId($this->getPressId());
            $existingSpotlight = false;
        } else {
            $existingSpotlight = true;
        }

        [$assocId, $assocType] = preg_split('/:/', $this->getData('assocId'));
        $spotlight->setAssocType($assocType);
        $spotlight->setTitle($this->getData('title'), null); // localized
        $spotlight->setDescription($this->getData('description'), null); // localized
        $spotlight->setAssocId($assocId);

        if ($existingSpotlight) {
            $spotlightDao->updateObject($spotlight);
            $spotlightId = $spotlight->getId();
        } else {
            $spotlightId = $spotlightDao->insertObject($spotlight);
        }

        parent::execute(...$functionArgs);
        return $spotlightId;
    }


    //
    // helper methdods.
    //

    /**
     * Fetch the spotlight Id for this form.
     *
     * @return int $spotlightId
     */
    public function getSpotlightId()
    {
        return $this->_spotlightId;
    }

    /**
     * Fetch the press Id for this form.
     *
     * @return int $pressId
     */
    public function getPressId()
    {
        return $this->_pressId;
    }

    /**
     * Fetch the title of the Spotlight item, based on the assocType and pressId
     *
     * @param int $assocId
     * @param int $assocType
     */
    public function getAssocTitle($assocId, $assocType)
    {
        $returner = null;
        switch ($assocType) {
            case Spotlight::SPOTLIGHT_TYPE_BOOK:
                $submission = Repo::submission()->get($assocId);
                $returner = isset($submission) ? $submission->getLocalizedTitle() : '';
                break;
            case Spotlight::SPOTLIGHT_TYPE_SERIES:
                $series = Repo::section()->get($assocId, $this->getPressId());
                $returner = isset($series) ? $series->getLocalizedTitle() : '';
                break;
            default:
                fatalError('invalid type specified');
        }
        return $returner;
    }

    /**
     * Internal function for spotlight type verification.
     *
     * @param int $type
     *
     * @return bool
     */
    public function _isValidSpotlightType($type)
    {
        $validTypes = [Spotlight::SPOTLIGHT_TYPE_BOOK, Spotlight::SPOTLIGHT_TYPE_SERIES];
        return in_array((int) $type, $validTypes);
    }
}
