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

	protected function prepareRegistry(\Nette\DI\Container $container, $handlerTag, $validatorTag) {
		if (!isset($this->cache['CachingContextMessageBus'])) {
			parent::prepareRegistry($container, $handlerTag, $validatorTag);
			$this->cache['CachingContextMessageBus'] = array($this->keys, $this->validators);
		}
		list($this->keys, $this->validators) = $this->cache['CachingContextMessageBus'];
		
	}

}

