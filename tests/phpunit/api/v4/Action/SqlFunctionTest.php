<?php

/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */


namespace api\v4\Action;

use api\v4\Api4TestBase;
use Civi\Api4\Activity;
use Civi\Api4\Contact;
use Civi\Api4\Contribution;
use Civi\Api4\Utils\CoreUtil;
use Civi\Test\TransactionalInterface;

/**
 * @group headless
 */
class SqlFunctionTest extends Api4TestBase implements TransactionalInterface {

  public function testGetFunctions() {
    $functions = array_column(CoreUtil::getSqlFunctions(), NULL, 'name');
    $this->assertArrayHasKey('SUM', $functions);
    $this->assertArrayNotHasKey('', $functions);
    $this->assertArrayNotHasKey('SqlFunction', $functions);
    $this->assertEquals(1, $functions['MAX']['params'][0]['min_expr']);
    $this->assertEquals(1, $functions['MAX']['params'][0]['max_expr']);
    $this->assertFalse($functions['YEAR']['options']);
    $this->assertEquals(1, $functions['MONTH']['options'][0]['id']);
    $this->assertEquals(12, $functions['MONTH']['options'][11]['id']);
  }

  public function testGroupAggregates() {
    $cid = Contact::create(FALSE)->addValue('first_name', 'bill')->execute()->first()['id'];
    Contribution::save(FALSE)
      ->setDefaults(['contact_id' => $cid, 'financial_type_id:name' => 'Donation'])
      ->setRecords([
        ['total_amount' => 100, 'receive_date' => '2020-01-01'],
        ['total_amount' => 200, 'receive_date' => '2020-01-01'],
        ['total_amount' => 300, 'receive_date' => '2020-01-01', 'financial_type_id:name' => 'Member Dues'],
        ['total_amount' => 400, 'receive_date' => '2020-01-01', 'financial_type_id:name' => 'Event Fee'],
      ])
      ->execute();

    // Test AVG, SUM, MAX, MIN, COUNT
    $agg = Contribution::get(FALSE)
      ->addGroupBy('contact_id')
      ->addWhere('contact_id', '=', $cid)
      ->addSelect('AVG(total_amount) AS average')
      ->addSelect('SUM(total_amount)')
      ->addSelect('MAX(total_amount)')
      ->addSelect('MIN(total_amount)')
      ->addSelect('COUNT(*) AS count')
      ->addOrderBy('average')
      ->execute()
      ->first();
    $this->assertTrue(250.0 === $agg['average']);
    $this->assertTrue(1000.0 === $agg['SUM:total_amount']);
    $this->assertTrue(400.0 === $agg['MAX:total_amount']);
    $this->assertTrue(100.0 === $agg['MIN:total_amount']);
    $this->assertTrue(4 === $agg['count']);

    // Test GROUP_CONCAT
    $agg = Contribution::get(FALSE)
      ->addGroupBy('contact_id')
      ->addWhere('contact_id', '=', $cid)
      ->addSelect('GROUP_CONCAT(financial_type_id:name)')
      ->addSelect('GROUP_CONCAT(financial_type_id)')
      ->addSelect('COUNT(*) AS count')
      ->execute()
      ->first();

    $this->assertTrue(4 === $agg['count']);
    $this->assertContains('Donation', $agg['GROUP_CONCAT:financial_type_id:name']);
    foreach ($agg['GROUP_CONCAT:financial_type_id'] as $type) {
      $this->assertTrue(is_int($type));
    }

    // Test GROUP_CONCAT with a CONCAT as well
    $agg = Contribution::get(FALSE)
      ->addGroupBy('contact_id')
      ->addWhere('contact_id', '=', $cid)
      ->addSelect("GROUP_CONCAT(CONCAT(financial_type_id, ', ', contact_id, ', ', total_amount))")
      ->addSelect('COUNT(*) AS count')
      ->execute()
      ->first();

    $this->assertTrue(4 === $agg['count']);
    $this->assertContains('1, ' . $cid . ', 100.00', $agg['GROUP_CONCAT:financial_type_id_contact_id_total_amount']);
  }

  public function testGroupHaving() {
    $cid = Contact::create(FALSE)->addValue('first_name', 'donor')->execute()->first()['id'];
    Contribution::save(FALSE)
      ->setDefaults(['contact_id' => $cid, 'financial_type_id' => 1])
      ->setRecords([
        ['total_amount' => 100, 'receive_date' => '2020-02-02'],
        ['total_amount' => 200, 'receive_date' => '2020-02-02'],
        ['total_amount' => 300, 'receive_date' => '2020-03-03'],
        ['total_amount' => 400, 'receive_date' => '2020-04-04'],
      ])
      ->execute();
    $result = Contribution::get(FALSE)
      ->addGroupBy('contact_id')
      ->addGroupBy('receive_date')
      ->addSelect('contact_id')
      ->addSelect('receive_date')
      ->addSelect('AVG(total_amount) AS average')
      ->addSelect('SUM(total_amount)')
      ->addSelect('MAX(total_amount)')
      ->addSelect('MIN(total_amount)')
      ->addSelect('COUNT(*) AS count')
      ->addOrderBy('receive_date')
      ->addHaving('contact_id', '=', $cid)
      ->addHaving('receive_date', '<', '2020-04-01')
      ->execute();
    $this->assertCount(2, $result);
    $this->assertEquals(150, $result[0]['average']);
    $this->assertEquals(300, $result[1]['average']);
    $this->assertEquals(300, $result[0]['SUM:total_amount']);
    $this->assertEquals(300, $result[1]['SUM:total_amount']);
    $this->assertEquals(200, $result[0]['MAX:total_amount']);
    $this->assertEquals(100, $result[0]['MIN:total_amount']);
    $this->assertEquals(2, $result[0]['count']);
    $this->assertEquals(1, $result[1]['count']);

    $result = Contribution::get(FALSE)
      ->addGroupBy('contact_id')
      ->addGroupBy('receive_date')
      ->addSelect('contact_id')
      ->addSelect('receive_date')
      ->addSelect('SUM(total_amount)')
      ->addOrderBy('receive_date')
      ->addWhere('contact_id', '=', $cid)
      ->addHaving('SUM(total_amount)', '>', 300)
      ->execute();
    $this->assertCount(1, $result);
    $this->assertStringStartsWith('2020-04-04', $result[0]['receive_date']);
    $this->assertEquals(400, $result[0]['SUM:total_amount']);
  }

  public function testComparisonFunctions() {
    $cid = Contact::create(FALSE)
      ->addValue('first_name', 'hello')
      ->execute()->first()['id'];
    $sampleData = [
      ['subject' => 'abc', 'activity_type_id:name' => 'Meeting', 'source_contact_id' => $cid, 'duration' => 123, 'location' => 'abc'],
      ['subject' => 'xyz', 'activity_type_id:name' => 'Meeting', 'source_contact_id' => $cid, 'location' => 'abc', 'is_deleted' => 1],
      ['subject' => 'def', 'activity_type_id:name' => 'Meeting', 'source_contact_id' => $cid, 'duration' => 456, 'location' => 'abc'],
    ];
    $aids = Activity::save(FALSE)
      ->setRecords($sampleData)
      ->execute()->column('id');

    $result = Activity::get(FALSE)
      ->addWhere('id', 'IN', $aids)
      ->addSelect('IF(is_deleted, "Trash", "No Trash") AS trashed')
      ->addSelect('NULLIF(subject, location) AS subject_is_location')
      ->addSelect('NULLIF(duration, 456) AS duration_not_456')
      ->addSelect('COALESCE(duration, location) AS duration_or_location')
      ->addSelect('GREATEST(duration, 0200) AS duration_or_200')
      ->addSelect('LEAST(duration, 300) AS 300_or_duration')
      ->addSelect('ISNULL(duration) AS duration_isnull')
      ->addOrderBy('id')
      ->execute()->indexBy('id');

    $this->assertCount(3, $result);
    $this->assertEquals('No Trash', $result[$aids[0]]['trashed']);
    $this->assertEquals('Trash', $result[$aids[1]]['trashed']);
    $this->assertEquals('No Trash', $result[$aids[2]]['trashed']);
    $this->assertEquals(NULL, $result[$aids[0]]['subject_is_location']);
    $this->assertEquals('xyz', $result[$aids[1]]['subject_is_location']);
    $this->assertEquals('def', $result[$aids[2]]['subject_is_location']);
    $this->assertEquals(123, $result[$aids[0]]['duration_not_456']);
    $this->assertEquals(NULL, $result[$aids[1]]['duration_not_456']);
    $this->assertEquals(NULL, $result[$aids[2]]['duration_not_456']);
    $this->assertEquals('123', $result[$aids[0]]['duration_or_location']);
    $this->assertEquals('abc', $result[$aids[1]]['duration_or_location']);
    $this->assertEquals(123, $result[$aids[0]]['300_or_duration']);
    $this->assertEquals(300, $result[$aids[2]]['300_or_duration']);
    $this->assertEquals(FALSE, $result[$aids[0]]['duration_isnull']);
    $this->assertEquals(TRUE, $result[$aids[1]]['duration_isnull']);
    $this->assertEquals(FALSE, $result[$aids[2]]['duration_isnull']);
  }

  public function testStringFunctions() {
    $sampleData = [
      ['first_name' => 'abc', 'middle_name' => 'Q', 'last_name' => 'tester1', 'source' => '123'],
    ];
    $cid = Contact::save(FALSE)
      ->setRecords($sampleData)
      ->execute()->first()['id'];

    $result = Contact::get(FALSE)
      ->addWhere('id', '=', $cid)
      ->addSelect('CONCAT_WS("|", first_name, middle_name, last_name) AS concat_ws')
      ->addSelect('REPLACE(first_name, "c", "cdef") AS new_first')
      ->addSelect('UPPER(first_name)')
      ->addSelect('LOWER(middle_name)')
      ->execute()->first();

    $this->assertEquals('abc|Q|tester1', $result['concat_ws']);
    $this->assertEquals('abcdef', $result['new_first']);
    $this->assertEquals('ABC', $result['UPPER:first_name']);
    $this->assertEquals('q', $result['LOWER:middle_name']);
  }

  public function testDateFunctions() {
    $lastName = uniqid(__FUNCTION__);
    $sampleData = [
      ['first_name' => 'abc', 'last_name' => $lastName, 'birth_date' => '2009-11-11'],
      ['first_name' => 'def', 'last_name' => $lastName, 'birth_date' => '2010-01-01'],
    ];
    $contacts = $this->saveTestRecords('Contact', [
      'records' => $sampleData,
    ]);

    $result = Contact::get(FALSE)
      ->addSelect('DATEDIFF("2010-01-01", birth_date) AS diff')
      ->addSelect('YEAR(birth_date) AS year')
      ->addSelect('QUARTER(birth_date) AS quarter')
      ->addSelect('MONTH(birth_date) AS month')
      ->addSelect('EXTRACT(YEAR_MONTH FROM birth_date) AS year_month')
      ->addWhere('last_name', '=', $lastName)
      ->addOrderBy('id')
      ->execute();

    $this->assertEquals(51, $result[0]['diff']);
    $this->assertEquals(2009, $result[0]['year']);
    $this->assertEquals(4, $result[0]['quarter']);
    $this->assertEquals(11, $result[0]['month']);
    $this->assertEquals('200911', $result[0]['year_month']);

    $this->assertEquals(0, $result[1]['diff']);
    $this->assertEquals(2010, $result[1]['year']);
    $this->assertEquals(1, $result[1]['quarter']);
    $this->assertEquals(1, $result[1]['month']);
    $this->assertEquals('201001', $result[1]['year_month']);
  }

  public function testIncorrectNumberOfArguments() {
    try {
      Activity::get(FALSE)
        ->addSelect('IF(is_deleted) AS whoops')
        ->execute();
      $this->fail('Api should have thrown exception');
    }
    catch (\CRM_Core_Exception $e) {
      $this->assertEquals('Missing param 2 for SQL function IF', $e->getMessage());
    }

    try {
      Activity::get(FALSE)
        ->addSelect('NULLIF(is_deleted, 1, 2) AS whoops')
        ->execute();
      $this->fail('Api should have thrown exception');
    }
    catch (\CRM_Core_Exception $e) {
      $this->assertEquals('Too many arguments given for SQL function NULLIF', $e->getMessage());
    }

    try {
      Activity::get(FALSE)
        ->addSelect('CONCAT_WS(",", ) AS whoops')
        ->execute();
      $this->fail('Api should have thrown exception');
    }
    catch (\CRM_Core_Exception $e) {
      $this->assertEquals('Too few arguments to param 2 for SQL function CONCAT_WS', $e->getMessage());
    }
  }

  public function testCurrentDate() {
    $lastName = uniqid(__FUNCTION__);
    $sampleData = [
      ['first_name' => 'abc', 'last_name' => $lastName, 'birth_date' => 'now'],
      ['first_name' => 'def', 'last_name' => $lastName, 'birth_date' => 'now - 1 year'],
      ['first_name' => 'def', 'last_name' => $lastName, 'birth_date' => 'now - 10 year'],
    ];
    Contact::save(FALSE)
      ->setRecords($sampleData)
      ->execute();

    $result = Contact::get(FALSE)
      ->addWhere('last_name', '=', $lastName)
      ->addWhere('birth_date', '=', 'CURDATE()', TRUE)
      ->selectRowCount()
      ->execute();
    $this->assertCount(1, $result);

    $result = Contact::get(FALSE)
      ->addWhere('last_name', '=', $lastName)
      ->addWhere('birth_date', '<', 'DATE(NOW())', TRUE)
      ->selectRowCount()
      ->execute();
    $this->assertCount(2, $result);
  }

  public function testRandFunction() {
    Contact::save(FALSE)
      ->setRecords(array_fill(0, 6, []))
      ->execute();

    $result = Contact::get(FALSE)
      ->addSelect('RAND() AS rand')
      ->addOrderBy('RAND()')
      ->setDebug(TRUE)
      ->setLimit(6)
      ->execute();

    // Random numbers should have been ordered from least to greatest
    $this->assertGreaterThanOrEqual($result[0]['rand'], $result[1]['rand']);
    $this->assertGreaterThanOrEqual($result[1]['rand'], $result[2]['rand']);
    $this->assertGreaterThanOrEqual($result[2]['rand'], $result[3]['rand']);
    $this->assertGreaterThanOrEqual($result[3]['rand'], $result[4]['rand']);
    $this->assertGreaterThanOrEqual($result[4]['rand'], $result[5]['rand']);
  }

  public function testDateInWhereClause() {
    $lastName = uniqid(__FUNCTION__);
    $sampleData = [
      ['first_name' => 'abc', 'last_name' => $lastName, 'birth_date' => '2009-11-11'],
      ['first_name' => 'def', 'last_name' => $lastName, 'birth_date' => '2009-01-01'],
      ['first_name' => 'def', 'last_name' => $lastName, 'birth_date' => '2010-01-01'],
    ];
    Contact::save(FALSE)
      ->setRecords($sampleData)
      ->execute();

    // Should work with isExpression=FALSE
    $result = Contact::get(FALSE)
      ->addWhere('last_name', '=', $lastName)
      ->addWhere('YEAR(birth_date)', '=', 2009)
      ->selectRowCount()
      ->execute();
    $this->assertCount(2, $result);

    // Should work with isExpression=TRUE
    $result = Contact::get(FALSE)
      ->addWhere('last_name', '=', $lastName)
      ->addWhere('YEAR(birth_date)', '=', 2009, TRUE)
      ->selectRowCount()
      ->execute();
    $this->assertCount(2, $result);

    // Try an expression in the value
    $result = Contact::get(FALSE)
      ->addWhere('last_name', '=', $lastName)
      ->addWhere('MONTH(birth_date)', '=', 'MONTH("2030-11-12")', TRUE)
      ->addSelect('birth_date')
      ->execute()->single();
    $this->assertEquals('2009-11-11', $result['birth_date']);

    // Try in GROUP_BY
    $result = Contact::get(FALSE)
      ->addSelect('COUNT(id) AS counted')
      ->addWhere('last_name', '=', $lastName)
      ->addGroupBy('EXTRACT(YEAR FROM birth_date)')
      ->execute();
    $this->assertCount(2, $result);
  }

}
