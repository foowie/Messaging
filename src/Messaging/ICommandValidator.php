<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
interface ICommandValidator {

	/**
	 * @param object
	 * @return ICommandValidationResult 
	 */
	function validate($message);
}