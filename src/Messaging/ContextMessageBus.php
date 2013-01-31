<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
class ContextMessageBus extends \Nette\Object implements IMessageBus {

	/** @var \Nette\DI\Container */
	private $container;

	/** @var string[][] */
	protected $keys = array();

	function __construct(\Nette\DI\Container $container, $tag = 'handler') {
		$this->container = $container;
		$this->registerHandlers($container, $tag);
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

	protected function registerHandlers(\Nette\DI\Container $container, $tag) {
		foreach ($container->findByTag($tag) as $commandHandlerName => $commands) {
			$commandClassNames = $commands === true ? $this->getCommandClassNamesByParam($container->getService($commandHandlerName)) : (array) $commands;
			foreach ($commandClassNames as $untrimedCommandClassName) {
				$commandClassName = \Nette\Utils\Strings::lower(trim($untrimedCommandClassName, '\\'));
				if(!class_exists($commandClassName)) {
					throw new \Nette\InvalidStateException("Class $untrimedCommandClassName not exists!");
				}
				$class = \Nette\Reflection\ClassType::from($commandClassName);
				if(!$class->isSubclassOf('Messaging\ICommand')) {
					throw new \Nette\InvalidStateException("Class $untrimedCommandClassName does not implements ICommand interface!");
				}
				if (!isset($this->keys[$commandClassName])) {
					$this->keys[$commandClassName] = array();
				}
				$this->keys[$commandClassName][] = $commandHandlerName;
			}
		}
	}

	protected function getCommandClassNamesByParam($handler) {
		$class = \Nette\Reflection\ClassType::from($handler);
		$method = $class->getMethod('handle');
		$paramAnnotation = $method->getAnnotation('param');
		if ($paramAnnotation === null) {
			throw new \Nette\InvalidStateException("@param annotation on method {$class->getName()}::handle(\$message) must be specified!");
		}
		$paramAnnotation = \Nette\Utils\Strings::match($paramAnnotation, '/^([^ \t]*)/');
		return explode('|', $paramAnnotation[0]);
	}

}