<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
interface IMessageBus extends ICommandSender, IEventPublisher {

	/**
	 * @param ICommand $command
	 * @param bool $need
	 * @return \Messaging\ValidationResultAggregator
	 * @throws \Nette\InvalidStateException
	 */
	function validate($command, $need = true);
}