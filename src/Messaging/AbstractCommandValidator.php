<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
abstract class AbstractCommandValidator extends \Nette\Object implements ICommandValidator {

	private $messages = array();

	protected function addMessage($message) {
		$this->messages[] = $message;
	}

	public function validate($message) {
		$this->doValidate($message);
		return new SimpleCommandValidationResult($this->messages);
	}

	protected abstract function doValidate($message);
}
