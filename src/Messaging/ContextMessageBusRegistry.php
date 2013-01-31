<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
class ContextMessageBusRegistry extends \Nette\Object {

	public function getServices(\Nette\DI\Container $container, $tag, $method) {
		$keys = array();
		foreach ($container->findByTag($tag) as $commandHandlerName => $commands) {
			$commandClassNames = $commands === true ? $this->getCommandClassNamesByParam($container->getService($commandHandlerName), $method) : (array) $commands;
			foreach ($commandClassNames as $untrimedCommandClassName) {
				$commandClassName = \Nette\Utils\Strings::lower(trim($untrimedCommandClassName, '\\'));
				if (!class_exists($commandClassName)) {
					throw new \Nette\InvalidStateException("Class $untrimedCommandClassName not exists!");
				}
				$class = \Nette\Reflection\ClassType::from($commandClassName);
				if (!$class->isSubclassOf('Messaging\ICommand')) {
					throw new \Nette\InvalidStateException("Class $untrimedCommandClassName does not implements ICommand interface!");
				}
				if (!isset($keys[$commandClassName])) {
					$keys[$commandClassName] = array();
				}
				$keys[$commandClassName][] = $commandHandlerName;
			}
		}
		return $keys;
	}

	protected function getCommandClassNamesByParam($service, $method) {
		$class = \Nette\Reflection\ClassType::from($service);
		$method = $class->getMethod($method);
		$paramAnnotation = $method->getAnnotation('param');
		if ($paramAnnotation === null) {
			throw new \Nette\InvalidStateException("@param annotation on method {$class->getName()}::{$method->getName()}(\$message) must be specified!");
		}
		$paramAnnotation = \Nette\Utils\Strings::match($paramAnnotation, '/^([^ \t]*)/');
		return explode('|', $paramAnnotation[0]);
	}

}
