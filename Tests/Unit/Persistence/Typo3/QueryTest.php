<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Extbase Team
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class Tx_Extbase_Tests_Unit_Persistence_Typo3_QueryTest extends Tx_Extbase_Tests_Unit_BaseTestCase {

	/**
	 * @test
	 * @expectedException Tx_Extbase_Persistence_Exception_NoJoinRequired
	 */
	public function cantBuildJoinTargetForNormalProperty() {
		$query = $this->getQueryMock();
		$query->buildJoinTargets('property');
	}

	/**
	 * @test
	 */
	public function canBuildJoinTargets() {

		/* @var $columnMap Tx_Extbase_Persistence_Mapper_ColumnMap */
		$columnMap = $this->getMock('Tx_Extbase_Persistence_Mapper_ColumnMap');
		$columnMap->expects($this->any())->method('getChildTableName')
				->will($this->onConsecutiveCalls('tx_mytarget', 'tx_myothertarget'));

		$columnMap->expects($this->any())->method('getChildKeyFieldName')
				->will($this->onConsecutiveCalls('targetfield', 'othertargetfield'));

		/* @var $dataMap Tx_Extbase_Persistence_Mapper_DataMap */
		$dataMap = $this->getMock('Tx_Extbase_Persistence_Mapper_DataMap');
		$dataMap->expects($this->any())->method('getColumnMap')->will($this->returnValue($columnMap));

		$dataMapper = $this->getMock('Tx_Extbase_Persistence_Mapper_DataMapper');
		$dataMapper->expects($this->any())->method('getDataMap')->will($this->returnValue($dataMap));
		$dataMapper->expects($this->atLeastOnce())->method('getType')->will($this->returnValue('Tx_MyTarget'));
		$dataMapper->expects($this->any())->method('convertClassNameToTableName')
				->will($this->returnCallback(function($val) {
			return strtolower($val);
		}));

		$query = $this->getQueryMock();
		$query->injectDataMapper($dataMapper);

		$joinTargets = $query->buildJoinTargets('sourceproperty.subproperty.property');

		$this->assertEquals(2, sizeof($joinTargets));

		$this->assertJoinTarget($joinTargets[0], 'tx_mysource', 'sourceproperty', 'tx_mytarget', 'targetfield');
		$this->assertJoinTarget($joinTargets[1], 'tx_mytarget', 'subproperty', 'tx_myothertarget', 'othertargetfield');
	}

	/**
	 * Building a "plain" join between two tables
	 *
	 * @test
	 */
	public function canBuildSingleJoinedSource() {

		$joinTargets = array(
			$this->getJoinTargetMock('tx_mysource', 'sourceproperty', 'tx_mytarget', 'targetfield')
		);

		$query = $this->getQueryMock();
		$query->join($joinTargets);

		/* @var $source Tx_Extbase_Persistence_QOM_JoinInterface */
		$source = $query->getSource();
		$this->assertTrue(is_a($source, 'Tx_Extbase_Persistence_QOM_JoinInterface'));

		$this->assertSelector($source->getLeft(), 'tx_mysource');
		$this->assertSelector($source->getRight(), 'tx_mytarget');
		$this->assertJoinCondition($source->getJoinCondition(), 'sourceproperty', 'targetfield');
	}

	/**
	 * Building a nested condition which is e.g. required to get data from property.subproperty.value
	 *
	 * @test
	 */
	public function canBuildNestedJoinedSource() {

		$joinTargets = array(
			$this->getJoinTargetMock('tx_mysource', 'sourceproperty', 'tx_mytarget', 'targetfield'),
			$this->getJoinTargetMock('tx_mytarget', 'subproperty', 'tx_myothertarget', 'othertargetfield')
		);

		$query = $this->getQueryMock();
		$query->join($joinTargets);
			// make sure the internal mapping doesn't break even if join() is called multiple times
		$query->join($joinTargets);

		/* @var $source Tx_Extbase_Persistence_QOM_JoinInterface */
		$source = $query->getSource();
		$this->assertTrue(is_a($source, 'Tx_Extbase_Persistence_QOM_JoinInterface'));

		$this->assertSelector($source->getLeft(), 'tx_mysource');
		$this->assertJoinCondition($source->getJoinCondition(), 'sourceproperty', 'targetfield');

		$this->assertTrue(is_a($source->getRight(), 'Tx_Extbase_Persistence_QOM_JoinInterface'));

		$this->assertSelector($source->getRight()->getLeft(), 'tx_mytarget');
		$this->assertSelector($source->getRight()->getRight(), 'tx_myothertarget');
		$this->assertJoinCondition($source->getRight()->getJoinCondition(), 'subproperty', 'othertargetfield');
	}

	/**
	 * Building a nested condition which is e.g. required to get data from property.subproperty.value
	 *
	 * @test
	 */
	public function canBuildComplexJoinedSource() {

		$this->markTestIncomplete();

		$joinTargets = array(
			$this->getJoinTargetMock('tx_mysource', 'uid', 'tx_mysource', 't3ver_oid'),
			$this->getJoinTargetMock('tx_mysource', 'sourceproperty', 'tx_mytarget', 'targetproperty'),
			$this->getJoinTargetMock('tx_mytarget', 'uid', 'tx_mytarget', 't3ver_oid'),
		);

		$query = $this->getQueryMock();
		$query->join($joinTargets);

		/* @var $source Tx_Extbase_Persistence_QOM_JoinInterface */
		$source = $query->getSource();
		$this->assertTrue(is_a($source, 'Tx_Extbase_Persistence_QOM_JoinInterface'));

		// FROM ((mysource AS origin INNER mysource as version) ON .... )
		// FROM (mysource AS origin INNER mysource AS lang ON  .... ) INNER ((mysource AS origin2 INNER mysource AS version ON  ....) INNER lang2 ON origin.uid=origin2.uid

		$this->assertTrue(is_a($source->getLeft(), 'Tx_Extbase_Persistence_QOM_OverlayJoinInterface'));
		$this->assertSelector($source->getLeft()->getLeft(), 'tx_mysource');
		$this->assertSelector($source->getLeft()->getRight(), 'tx_mysource');
		$this->assertJoinCondition($source->getLeft()->getJoinCondition(), 'uid', 't3ver_oid');

		$this->assertTrue(is_a($source->getRight(), 'Tx_Extbase_Persistence_QOM_OverlayJoinInterface'));
		$this->assertSelector($source->getRight()->getLeft(), 'tx_mysource');
		$this->assertSelector($source->getRight()->getRight(), 'tx_mysource');
		$this->assertJoinCondition($source->getRight()->getJoinCondition(), 'uid', 't3ver_oid');

		$this->assertJoinCondition($source->getJoinCondition(), 'sourceproperty', 'targetproperty');
	}

	/**
	 * @return Tx_Extbase_Persistence_Typo3_Query
	 */
	protected function getQueryMock() {
		$query = $this->getMock('Tx_Extbase_Persistence_Typo3_Query', array('getType', 'getSelectorName'));
		$query->expects($this->any())->method('getType')->will($this->returnValue('Tx_MySource'));
		$query->expects($this->any())->method('getSelectorName')->will($this->returnValue('tx_mysource'));
		return $query;
	}

	protected function assertJoinTarget($joinTarget, $sourceName, $sourceField, $targetName, $targetField) {
		$this->assertTrue(is_a($joinTarget, 'Tx_Extbase_Persistence_QOM_JoinTargetInterface'));
		$this->assertEquals($targetName, $joinTarget->getTablename()); // taken from the property
		$this->assertEquals(Tx_Extbase_Persistence_QOM_JoinInterface::TYPE_INNER, $joinTarget->getType());

		/* @var $condition Tx_Extbase_Persistence_QOM_PlainJoinCondition */
		$condition = $joinTarget->getCondition();

		$this->assertTrue(is_a($condition, 'Tx_Extbase_Persistence_QOM_PlainJoinCondition'));
		$this->assertEquals($sourceName, $condition->getSelector1Name());
		$this->assertEquals($sourceField, $condition->getField1Name());
		$this->assertEquals($targetName, $condition->getSelector2Name());
		$this->assertEquals($targetField, $condition->getField2Name());
	}

	/**
	 * Check whether the object is a valid selector for the given (table)name
	 *
	 * @param Tx_Extbase_Persistence_QOM_SelectorInterface $selector
	 * @param string $name
	 */
	protected function assertSelector(Tx_Extbase_Persistence_QOM_SelectorInterface $selector, $name) {
		$this->assertEquals($name, $selector->getSelectorName());
	}

	/**
	 * Check whether the object is a valid join condition containing the two fieldnames
	 *
	 * @param Tx_Extbase_Persistence_QOM_PlainJoinCondition $condition
	 * @param string $field1
	 * @param string $field2
	 */
	protected function assertJoinCondition(Tx_Extbase_Persistence_QOM_PlainJoinCondition $condition, $field1, $field2) {
		$this->assertEquals($field1, $condition->getField1Name());
		$this->assertEquals($field2, $condition->getField2Name());
	}

	/**
	 * @param string $targetName
	 * @param string $targetField
	 * @return Tx_Extbase_Persistence_QOM_JoinTarget
	 */
	protected function getJoinTargetMock($sourceName, $sourceField, $targetName, $targetField) {

		$condition = $this->getMock('Tx_Extbase_Persistence_QOM_PlainJoinCondition');
		$condition->expects($this->any())->method('getSelector1Name')->will($this->returnValue($sourceName));
		$condition->expects($this->any())->method('getField1Name')->will($this->returnValue($sourceField));
		$condition->expects($this->any())->method('getSelector2Name')->will($this->returnValue($targetName));
		$condition->expects($this->any())->method('getField2Name')->will($this->returnValue($targetField));

		$joinTarget = $this->getMock('Tx_Extbase_Persistence_QOM_JoinTarget', array(), array(), '', FALSE);
		$joinTarget->expects($this->any())->method('getTablename')->will($this->returnValue($targetName));
		$joinTarget->expects($this->any())->method('getType')
				->will($this->returnValue(Tx_Extbase_Persistence_QOM_JoinInterface::TYPE_INNER));

		$joinTarget->expects($this->any())->method('getCondition')
				->will($this->returnValue($condition));

		return $joinTarget;
	}
}

?>