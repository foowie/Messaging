<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
class ValidationResult extends \Nette\Object implements IValidationResult {

	private $messages;

	public function __construct($messages = null) {
		$this->messages = is_array($messages) ? $messages : func_get_args();
	}

	public function isValid() {
		return count($this->messages) == 0;
	}

	public function getMessages() {
		return $this->messages;
	}

}
