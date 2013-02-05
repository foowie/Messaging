<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
interface IValidator {

	/**
	 * @return IValidationResult|null
	 * @throws ValidationException
	 */
	function validate($message);
}