<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
interface ICommandSender {

	function send($command);
}
