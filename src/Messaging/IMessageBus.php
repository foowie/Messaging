<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
interface IMessageBus extends ICommandSender, IEventPublisher, IValidator {
	
}