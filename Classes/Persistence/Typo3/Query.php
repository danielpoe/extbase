<?php
/**
 * Created by JetBrains PhpStorm.
 * User: info
 * Date: 16.03.12
 * Time: 13:10
 * To change this template use File | Settings | File Templates.
 */
class Tx_Extbase_Persistence_Typo3_Query extends Tx_Extbase_Persistence_Query implements Tx_Extbase_Persistence_Typo3_QueryInterface {

	/**
	 * @param Tx_Extbase_Persistence_QOM_JoinTargetInterface $target
	 * @return void
	 */
	public function join(Tx_Extbase_Persistence_QOM_JoinTargetInterface $target) {

	}

	/**
	 * @param $name
	 * @return Tx_Extbase_Persistence_QOM_JoinTargetInterface
	 */
	public function buildJoinTargetForClassname($className) {
		return t3lib_div::makeInstance('Tx_Extbase_Persistence_QOM_JoinTarget',
			$this->dataMapper->convertClassNameToTableName($className),
			$className,


			//tablename
			//nodetypename
			//conditions
		);
	}

	/**
	 * @param $name
	 * @param array $conditions
	 */
	public function buildJoinTargetForTablename($name, array $conditions) {
		return t3lib_div::makeInstance('Tx_Extbase_Persistence_QOM_JoinTarget',
			//$name,
			//nodetypename
			$this->getConditionForArray($conditions)
		);
	}

	protected function getConditionForArray(array $conditions) {

		/* @var $andCondition Tx_Extbase_Persistence_QOM_LogicalAnd */
		$andCondition = t3lib_div::makeInstance('Tx_Extbase_Persistence_QOM_LogicalAnd');


	}

}
