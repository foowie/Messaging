<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
class ValidationResultAggregator extends \Nette\Object implements IValidationResult {

	protected $messages = array();
	protected $isValid = true;

	public function addValidationResult(IValidationResult $result) {
		foreach ($result->getMessages() as $message) {
			$this->messages[] = $message;
		}
		if (!$result->isValid()) {
			$this->isValid = false;
		}
	}

	public function getMessages() {
		return $this->messages;
	}

	public function isValid() {
		return $this->isValid;
	}

}
