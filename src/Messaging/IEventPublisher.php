<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
interface IEventPublisher {

	function publish($event);
}
