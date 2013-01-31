<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
class ContextMessageBus extends \Nette\Object implements IMessageBus {

	/** @var \Nette\DI\Container */
	private $container;

	/** @var string[][] */
	protected $keys;

	/** @var string[][] */
	protected $validators;

	function __construct(\Nette\DI\Container $container, $handlerTag = 'handler', $validatorTag = 'validator') {
		$this->container = $container;
		$this->prepareRegistry($container, $handlerTag, $validatorTag);
	}

	public function publish($event) {
		foreach ($this->getHandlers($event) as $handler) {
			$handler->handle($event);
		}
	}

	public function send($command) {
		$handlers = $this->getHandlers($command);
		if (count($handlers) != 1) {
			throw new \Nette\InvalidStateException("One handler is allowed to send command, " . count($handlers) . " given!");
		}
		$handlers[0]->handle($command);
	}

	public function validate($command) {
		$validators = $this->getValidators($command);
		if (count($validators) < 1) {
			throw new \Nette\InvalidStateException("No validator found!");
		}
		$result = new ValidationResultAggregator();
		foreach($validators as $validator) {
			$result->addValidationResult($validator->validate($command));
		}
		return $result;
	}

	/**
	 * @param ICommand $command
	 * @return ICommandHandler[]
	 * @throws \Nette\InvalidStateException
	 */
	protected function getHandlers($message) {
		$class = \Nette\Reflection\ClassType::from($message);
		$handlers = array();
		do {
			$commandType = \Nette\Utils\Strings::lower($class->getName());
			$handlers = array_merge($handlers, isset($this->keys[$commandType]) ? $this->keys[$commandType] : array());
		} while (($class = $class->getParentClass()) !== null);

		foreach ($handlers as $key => $handler) {
			$handlers[$key] = $this->container->getService($handler);
			if (!($handlers[$key] instanceof IHandler)) {
				throw new \Nette\InvalidStateException('Handler must be instance of IHandler!');
			}
		}
		return $handlers;
	}

	/**
	 * @param ICommand $command
	 * @return IValidator[]
	 * @throws \Nette\InvalidStateException
	 */
	protected function getValidators($message) {
		$class = \Nette\Reflection\ClassType::from($message);
		$validators = array();
		do {
			$commandType = \Nette\Utils\Strings::lower($class->getName());
			$validators = array_merge($validators, isset($this->validators[$commandType]) ? $this->validators[$commandType] : array());
		} while (($class = $class->getParentClass()) !== null);
		foreach ($validators as $key => $validator) {
			$validators[$key] = $this->container->getService($validator);
			if (!($validators[$key] instanceof IValidator)) {
				throw new \Nette\InvalidStateException('Validator must be instance of IValidator!');
			}
		}
		return $validators;
	}

	protected function prepareRegistry(\Nette\DI\Container $container, $handlerTag, $validatorTag) {
		$registry = new ContextMessageBusRegistry();
		$this->keys = $registry->getServices($container, $handlerTag, 'handle');
		$this->validators = $registry->getServices($container, $validatorTag, 'validate');
	}

}