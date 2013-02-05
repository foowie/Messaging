<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
class CachingContextMessageBus extends ContextMessageBus {

	/** @var \Nette\Caching\Cache */
	protected $cache;

	function __construct(\Nette\Caching\IStorage $storage, \Nette\DI\Container $container, $tag = 'handler') {
		$this->cache = new \Nette\Caching\Cache($storage, 'CachingContextMessageBus');
		parent::__construct($container, $tag);
	}

	protected function prepareRegistry() {
		if (!isset($this->cache['CachingContextMessageBus'])) {
			parent::prepareRegistry();
			$this->cache['CachingContextMessageBus'] = array($this->handlers, $this->validators);
		}
		list($this->handlers, $this->validators) = $this->cache['CachingContextMessageBus'];
		
	}

}

