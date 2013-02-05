<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
class ContextMessageBus extends \Nette\Object implements IMessageBus {

	/** @var \Nette\DI\Container */
	private $container;

	/** @var string */
	protected $handlerTag;

	/** @var string */
	protected $validatorTag;

	/** @var string[][] */
	protected $handlers;

	/** @var string[][] */
	protected $validators;

	function __construct(\Nette\DI\Container $container, $handlerTag = 'handler', $validatorTag = 'validator') {
		$this->container = $container;
		$this->handlerTag = $handlerTag;
		$this->validatorTag = $validatorTag;
	}

	public function publish($event) {
		$this->doValidation($event);
		foreach ($this->getHandlers($event) as $handler) {
			$handler->handle($event);
		}
	}

	public function send($command) {
		$this->doValidation($command);
		$handlers = $this->getHandlers($command);
		if (count($handlers) != 1) {
			throw new \Nette\InvalidStateException("One handler is allowed to process command, " . count($handlers) . " given!");
		}
		return $handlers[0]->handle($command);
	}

	/**
	 * @param ICommand $command
	 * @param bool $need
	 * @return \Messaging\ValidationResultAggregator
	 * @throws \Nette\InvalidStateException
	 */
	public function validate($command, $need = true) {
		$validators = $this->getValidators($command);
		if ($need && count($validators) < 1) {
			throw new \Nette\InvalidStateException("No validator found!");
		}
		$result = new ValidationResultAggregator();
		foreach($validators as $validator) {
			try {
				$validationResult = $validator->validate($command);
				if($validationResult === null) {
					$validationResult = new ValidationResult();
				}
			} catch(\Messaging\ValidationException $e) {
				$validationResult = new ValidationResult($e->getMessage());
			}
			$result->addValidationResult($validationResult);
		}
		return $result;
	}

	/**
	 * @param ICommand $command
	 * @throws ValidationException
	 */
	protected function doValidation($command) {
		$result = $this->validate($command, false);
		if(!$result->isValid()) {
			$messages = $result->getMessages();
			throw new ValidationException(reset($messages));
		}
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
			$handlers = array_merge($handlers, $this->getHandlersForClass($class->getName()));
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
			$validators = array_merge($validators, $this->getValidatorsForClass($class->getName()));
		} while (($class = $class->getParentClass()) !== null);
		foreach ($validators as $key => $validator) {
			$validators[$key] = $this->container->getService($validator);
			if (!($validators[$key] instanceof IValidator)) {
				throw new \Nette\InvalidStateException('Validator must be instance of IValidator!');
			}
		}
		return $validators;
	}
	
	protected function getHandlersForClass($className, $default = array()) {
		if($this->handlers === null) {
			$this->prepareRegistry();
		}
		$commandType = \Nette\Utils\Strings::lower($className);
		return isset($this->handlers[$commandType]) ? $this->handlers[$commandType] : $default;
	}
	
	protected function getValidatorsForClass($className, $default = array()) {
		if($this->validators === null) {
			$this->prepareRegistry();
		}
		$commandType = \Nette\Utils\Strings::lower($className);
		return isset($this->validators[$commandType]) ? $this->validators[$commandType] : $default;
	}

	protected function prepareRegistry() {
		$registry = new ContextMessageBusRegistry();
		$this->handlers = $registry->getServices($this->container, $this->handlerTag, 'handle');
		$this->validators = $registry->getServices($this->container, $this->validatorTag, 'validate');
	}

}