<?php

class Tx_Extbase_Persistence_Typo3_Query extends Tx_Extbase_Persistence_Query implements Tx_Extbase_Persistence_Typo3_QueryInterface {


	/**
	 * @var array
	 */
	protected $joinMap = array();

	/**
	 * @var array
	 */
	protected $sourceMap = array();

	/**
	 * @param array<Tx_Extbase_Persistence_QOM_JoinTargetInterface> $targets
	 * @return void
	 */
	public function join(array $targets) {

		$leftSource = $this->getMappedSelector($this->getSelectorName(), $this->getType());

		/* @var $rightTarget Tx_Extbase_Persistence_QOM_JoinTargetInterface */
		$rightTarget = $targets[0];
		$rightSource = $this->getMappedSelector($rightTarget->getTablename(), $rightTarget->getType());

		$leftSource = $this->getMappedSource($leftSource, $rightTarget);

		$targets = array_slice($targets, 1);

		/* @var $target Tx_Extbase_Persistence_QOM_JoinTarget */
		foreach($targets as $target) {
			$selector = $this->getMappedSelector($target->getTablename(), $target->getType());
			$rightSource = $this->getMappedJoin($rightSource, $selector, $target);
		}


		/* @var $join Tx_Extbase_Persistence_QOM_Join */
		$joinedSource = t3lib_div::makeInstance('Tx_Extbase_Persistence_QOM_Join',
			$leftSource,
			$rightSource,
			$rightTarget->getType(),
			$rightTarget->getCondition()
		);

		$this->setSource($joinedSource);
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @return Tx_Extbase_Persistence_QOM_SourceInterface
	 */
	protected function getMappedSelector($name, $type) {
		if (!isset($this->joinMap[$name])) {
			/* @var $source Tx_Extbase_Persistence_QOM_Selector */
			$source = t3lib_div::makeInstance('Tx_Extbase_Persistence_QOM_Selector',
				$name,
				$type
			);
			$this->joinMap[$name] = $source;
		}
		return $this->joinMap[$name];
	}

	/**
	 * @param Tx_Extbase_Persistence_QOM_SourceInterface $left
	 * @param Tx_Extbase_Persistence_QOM_SourceInterface $right
	 * @param Tx_Extbase_Persistence_QOM_JoinTargetInterface $target
	 * @return Tx_Extbase_Persistence_QOM_SourceInterface
	 */
	protected function getMappedJoin(Tx_Extbase_Persistence_QOM_SourceInterface $left,Tx_Extbase_Persistence_QOM_SourceInterface $right,Tx_Extbase_Persistence_QOM_JoinTargetInterface $target) {
		if (!isset($this->joinMap[$target->getTablename()]) || !is_a($this->joinMap[$target->getTablename()], 'Tx_Extbase_Persistence_QOM_JoinInterface') ) {
			$source = t3lib_div::makeInstance('Tx_Extbase_Persistence_QOM_Join',
				$left,
				$right,
				$target->getType(),
				$target->getCondition()
			);
			$this->joinMap[$target->getTablename()] = $source;
		}
		return $this->joinMap[$target->getTablename()];
	}

	/**
	 * @param Tx_Extbase_Persistence_QOM_Selector $source
	 * @param Tx_Extbase_Persistence_QOM_JoinTargetInterface $target
	 * @return Tx_Extbase_Persistence_QOM_SourceInterface
	 */
	protected function getMappedSource(Tx_Extbase_Persistence_QOM_Selector $source,Tx_Extbase_Persistence_QOM_JoinTargetInterface $target) {
		$type = $source->getNodeTypeName();
		$field = $target->getCondition()->getField1Name();
		if (isset($this->sourceMap[$type][$field])) {
			return $this->sourceMap[$type][$field];
		} else if (isset($this->sourceMap[$type])) {
			$this->sourceMap[$type][$field] = $this->getMappedJoin(
				$source,
				current($this->sourceMap[$type]),
				$target
			);
		} else {
			$this->sourceMap[$type][$field] = $source;
		}
		return $this->sourceMap[$type][$field];
	}

	/**
	 * @param string $name
	 * @param string $name
	 * @return array<Tx_Extbase_Persistence_QOM_JoinTarget>
	 */
	public function buildJoinTargets($propertyPath, $sourceType = NULL) {

		if (!stristr($propertyPath, '.')) {
			throw new Tx_Extbase_Persistence_Exception_NoJoinRequired('We can\'t perform joins for simple properties.', 1331941411);
		}
		$explodedPropertyPath = explode('.', $propertyPath, 2);
		$propertyName = $explodedPropertyPath[0];

		$type = $sourceType ?: $this->getType();

		$columnMap = $this->dataMapper->getDataMap($type)->getColumnMap($propertyName);
		$childTable = $columnMap->getChildTableName();

		$condition = t3lib_div::makeInstance('Tx_Extbase_Persistence_QOM_PlainJoinCondition',
			$this->dataMapper->convertClassNameToTableName($type),
			$propertyName,
			$childTable,
			$columnMap->getChildKeyFieldName()
		);

		$joinTarget = t3lib_div::makeInstance('Tx_Extbase_Persistence_QOM_JoinTarget',
			$childTable,
			Tx_Extbase_Persistence_QOM_JoinInterface::TYPE_INNER,
			$condition
		);

		$targets = array($joinTarget);

		if (isset($explodedPropertyPath[1]) && stristr($explodedPropertyPath[1], '.') !== FALSE) {
			$propertyType = $this->dataMapper->getType($type, $propertyName);
			$targets = array_merge(
				$targets,
				$this->buildJoinTargets($explodedPropertyPath[1], $propertyType)
			);
		}
		return $targets;
	}

	/**
	 * @param Tx_Extbase_Persistence_QOM_SelectorInterface $selector
	 * @param Tx_Extbase_Persistence_QOM_JoinConditionInterface $condition
	 * @param string $tableName
	 * @return
	 */
	public function buildOverlayJoinTargets($selector, Tx_Extbase_Persistence_QOM_JoinConditionInterface $condition, $tableName = '') {

		$this->getMappedSelector($this->getSelectorName(), $this->getType());



	}
}
