<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
interface ICommandValidationResult {

	function isValid();

	function getMessages();
}
