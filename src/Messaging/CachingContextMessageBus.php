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

	protected function registerHandlers(\Nette\DI\Container $container, $tag) {
		if (!isset($this->cache['CachingContextMessageBus'])) {
			parent::registerHandlers($container, $tag);
			$this->cache['CachingContextMessageBus'] = $this->keys;
		}
		$this->keys = $this->cache['CachingContextMessageBus'];
	}

}

