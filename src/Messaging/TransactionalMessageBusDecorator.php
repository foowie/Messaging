<?php

namespace Messaging;

/**
 * @author Daniel Robenek <daniel.robenek@me.com>
 */
class TransactionalMessageBusDecorator extends \Nette\Object implements IMessageBus {

	const TYPE_NONE = 0;
	const TYPE_COMMIT = 1;
	const TYPE_ROLLBACK = 2;

	/** @var int Current depth of nested transactions */
	protected $depth = 0;

	/** @var int Last commit/rollback operation type */
	protected $type = self::TYPE_NONE;

	/** @var IMessageBus */
	private $messageBus;

	/** @var \Nette\Database\Connection */
	private $connection;

	function __construct(IMessageBus $messageBus, \Nette\Database\Connection $connection) {
		$this->messageBus = $messageBus;
		$this->connection = $connection;
	}

	public function publish($command) {
		try {
			$this->beginTransaction();
			$result = $this->messageBus->publish($command);
			$this->commit();
			return $result;
		} catch (\Exception $e) {
			$this->rollBack();
			throw $e;
		}
	}

	public function send($command) {
		try {
			$this->beginTransaction();
			$result = $this->messageBus->send($command);
			$this->commit();
			return $result;
		} catch (\Exception $e) {
			$this->rollBack();
			throw $e;
		}
	}

	protected function beginTransaction() {
		if ($this->depth === 0) {
			$this->type = self::TYPE_NONE;
			$this->connection->beginTransaction();
		}
		$this->depth++;
	}

	protected function commit() {
		if ($this->type === self::TYPE_ROLLBACK) {
			throw new \Nette\NotSupportedException('Nested transactions are not supported!');
		}
		if ($this->depth === 1) {
			$this->connection->commit();
		}
		$this->type = self::TYPE_COMMIT;
		$this->depth--;
	}

	protected function rollBack() {
		if ($this->depth === 1) {
			$this->connection->rollBack();
		}
		$this->type = self::TYPE_ROLLBACK;
		$this->depth--;
	}

}