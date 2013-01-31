<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
interface IValidationResult {

	function isValid();

	function getMessages();
}
