<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
interface IValidator {

	/**
	 * @return IValidationResult 
	 */
	function validate($message);
}