<?php

class Tx_Extbase_Persistence_QOM_PlainJoinCondition implements Tx_Extbase_Persistence_QOM_JoinConditionInterface {

	/**
	 * @var string
	 */
	protected $selector1Name;

	/**
	 * @var string
	 */
	protected $field1Name;

	/**
	 * @var string
	 */
	protected $selector2Name;

	/**
	 * @var string
	 */
	protected $field2Name;

	/**
	 * @param string $selector1Name the name of the first selector; non-null
	 * @param string $field1Name the field name in the first selector; non-null
	 * @param string $selector2Name the name of the second selector; non-null
	 * @param string $field2Name the field name in the second selector; non-null
	 */
	public function __construct($selector1Name, $field1Name, $selector2Name, $field2Name) {
		$this->selector1Name = $selector1Name;
		$this->field1Name = $field1Name;
		$this->selector2Name = $selector2Name;
		$this->field2Name = $field2Name;
	}

	/**
	 * Gets the name of the first selector.
	 *
	 * @return string the selector name; non-null
	 */
	public function getSelector1Name() {
		return $this->selector1Name;
	}

	/**
	 * Gets the name of the first field.
	 *
	 * @return string the field name; non-null
	 */
	public function getField1Name() {
		return $this->field1Name;
	}

	/**
	 * Gets the name of the second selector.
	 *
	 * @return string the selector name; non-null
	 */
	public function getSelector2Name() {
		return $this->selector2Name;
	}

	/**
	 * Gets the name of the second field.
	 *
	 * @return string the field name; non-null
	 */
	public function getField2Name() {
		return $this->field2Name;
	}

}

?>