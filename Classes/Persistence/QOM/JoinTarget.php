<?php

class Tx_Extbase_Persistence_QOM_JoinTarget implements Tx_Extbase_Persistence_QOM_JoinTargetInterface {

	/**
	 * @var string
	 */
	protected $tablename;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var Tx_Extbase_Persistence_QOM_JoinConditionInterface
	 */
	protected $condition;

	/**
	 * Constructs the JoinTarget instance
	 *
	 * @param string $tablename
	 * @param string $nodetypename
	 * @param Tx_Extbase_Persistence_QOM_JoinConditionInterface $condition
	 */
	public function __construct($tablename, $type, Tx_Extbase_Persistence_QOM_JoinConditionInterface $condition) {
		$this->tablename = $tablename;
		$this->type = $type;
		$this->condition = $condition;
	}

	/**
	 * @return string
	 */
	public function getTablename() {
		return $this->tablename;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return Tx_Extbase_Persistence_QOM_JoinConditionInterface
	 */
	public function getCondition() {
		return $this->condition;
	}
}

?>