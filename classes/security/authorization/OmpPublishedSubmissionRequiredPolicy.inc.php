<?php
/**
 * @file classes/security/authorization/OmpPublishedSubmissionRequiredPolicy.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OmpPublishedSubmissionRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Policy that ensures that the request contains a valid published submission.
 */

namespace APP\security\authorization;

use APP\facades\Repo;
use PKP\security\authorization\DataObjectRequiredPolicy;
use PKP\submission\PKPSubmission;

class OmpPublishedSubmissionRequiredPolicy extends DataObjectRequiredPolicy
{
    /** @var Context */
    public $context;

    /**
     * Constructor
     *
     * @param PKPRequest $request
     * @param array $args request parameters
     * @param string $submissionParameterName the request parameter we expect
     *  the submission id in.
     * @param array $operations
     */
    public function __construct($request, &$args, $submissionParameterName = 'submissionId', $operations = null)
    {
        parent::__construct($request, $args, $submissionParameterName, 'user.authorization.invalidPublishedSubmission', $operations);
        $this->context = $request->getContext();
    }

    //
    // Implement template methods from AuthorizationPolicy
    //
    /**
     * @see DataObjectRequiredPolicy::dataObjectEffect()
     */
    public function dataObjectEffect()
    {
        $submissionId = $this->getDataObjectId();
        if (!$submissionId) {
            return AUTHORIZATION_DENY;
        }

        // Make sure the published submissions belongs to the press.
        $submission = Repo::submission()->getByUrlPath($submissionId, $this->context->getId());
        if (!$submission && ctype_digit((string) $submissionId)) {
            $submission = Repo::submission()->get($submissionId);
        }
        if (!$submission || $submission->getData('status') !== PKPSubmission::STATUS_PUBLISHED) {
            return AUTHORIZATION_DENY;
        }

        // Save the published submission to the authorization context.
        $this->addAuthorizedContextObject(ASSOC_TYPE_SUBMISSION, $submission);
        return AUTHORIZATION_PERMIT;
    }

    /**
     * @copydoc DataObjectRequiredPolicy::getDataObjectId()
     * Considers a not numeric public URL identifier
     */
    public function getDataObjectId($lookOnlyByParameterName = false)
    {
        // Identify the data object id.
        $router = $this->_request->getRouter();
        switch (true) {
            case $router instanceof \PKP\core\PKPPageRouter:
                if (ctype_digit((string) $this->_request->getUserVar($this->_parameterName))) {
                    // We may expect a object id in the user vars
                    return (int) $this->_request->getUserVar($this->_parameterName);
                } elseif (isset($this->_args[0])) {
                    // Or the object id can be expected as the first path in the argument list
                    return $this->_args[0];
                }
                break;

            default:
                return parent::getDataObjectId($lookOnlyByParameterName);
        }

        return false;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\security\authorization\OmpPublishedSubmissionRequiredPolicy', '\OmpPublishedSubmissionRequiredPolicy');
}
