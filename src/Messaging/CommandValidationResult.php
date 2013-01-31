<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
class CommandValidationResult extends \Nette\Object implements ICommandValidationResult {

	private $messages;

	public function __construct(array $messages) {
		$this->messages = $messages;
	}

	public function isValid() {
		return count($this->messages) == 0;
	}

	public function getMessages() {
		return $this->messages;
	}

}
