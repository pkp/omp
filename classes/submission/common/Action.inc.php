<?php

class Action {

	/**
	 * View metadata of an article.
	 * @param $article object
	 */
	function viewMetadata($monograph, $roleId) {
		if (!HookRegistry::call('Action::viewMetadata', array(&$monograph, &$roleId))) {
			import('submission.form.MetadataForm');
			$metadataForm =& new MetadataForm($monograph, $roleId);
			if ($metadataForm->getCanEdit() && $metadataForm->isLocaleResubmit()) {
				$metadataForm->readInputData();
			} else {
				$metadataForm->initData();
			}
			$metadataForm->display();
		}
	}
}
?>