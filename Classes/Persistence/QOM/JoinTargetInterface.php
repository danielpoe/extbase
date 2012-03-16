<?php
/**
 * Created by JetBrains PhpStorm.
 * User: info
 * Date: 16.03.12
 * Time: 13:31
 * To change this template use File | Settings | File Templates.
 */
interface Tx_Extbase_Persistence_QOM_JoinTargetInterface {

	/**
	 * @abstract
	 * @return string
	 */
	public function getTablename();

	/**
	 * @abstract
	 * @return string
	 */
	public function getType();

	/**
	 * @return Tx_Extbase_Persistence_QOM_JoinConditionInterface
	 */
	public function getCondition();

}
