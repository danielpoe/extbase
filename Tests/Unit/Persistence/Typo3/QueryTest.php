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

		$dataMapper = $this->getMock('Tx_Extbase_Persistence_Mapper_DataMapper');

		$query = $this->getQueryMock($dataMapper);
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

		$query = $this->getQueryMock($dataMapper);

		$joinTargets = $query->buildJoinTargets('sourceproperty.subproperty.property');

		$this->assertEquals(2, sizeof($joinTargets));

		$this->assertJoinTarget($joinTargets[0], 'tx_mysource', 'sourceproperty', 'tx_mytarget', 'targetfield');
		$this->assertJoinTarget($joinTargets[1], 'tx_mytarget', 'subproperty', 'tx_myothertarget', 'othertargetfield');
	}

	/**
	 * @param $dataMapper
	 * @return Tx_Extbase_Persistence_Typo3_Query
	 */
	protected function getQueryMock($dataMapper) {
		$query = $this->getMock('Tx_Extbase_Persistence_Typo3_Query', array('getType'));
		$query->expects($this->any())->method('getType')->will($this->returnValue('Tx_MySource'));
		$query->injectDataMapper($dataMapper);
		return $query;
	}

	protected function assertJoinTarget($joinTarget, $sourceName, $sourceField, $targetName, $targetField) {
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
}

?>