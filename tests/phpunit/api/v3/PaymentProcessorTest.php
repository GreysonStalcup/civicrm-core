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
 * Class contains api test cases for "civicrm_payment_processor"
 *
 * @group headless
 */
class api_v3_PaymentProcessorTest extends CiviUnitTestCase {
  protected $_paymentProcessorType;
  protected $_params;

  /**
   * Set up class.
   *
   * @throws \CRM_Core_Exception
   */
  public function setUp(): void {
    parent::setUp();
    $this->useTransaction(TRUE);
    // Create dummy processor
    $params = [
      'name' => 'API_Test_PP_Type',
      'title' => 'API Test Payment Processor Type',
      'class_name' => 'CRM_Core_Payment_APITest',
      'billing_mode' => 'form',
      'is_recur' => 0,
      'payment_instrument_id' => 2,
    ];
    $result = $this->callAPISuccess('payment_processor_type', 'create', $params);
    $this->_paymentProcessorType = $result['id'];
    $this->_params = [
      'name' => 'API Test PP',
      'title' => 'API Test PP',
      'payment_processor_type_id' => $this->_paymentProcessorType,
      'class_name' => 'CRM_Core_Payment_APITest',
      'is_recur' => 0,
      'domain_id' => 1,
    ];
  }

  /**
   * Check create with no name specified.
   * @dataProvider versionThreeAndFour
   */
  public function testPaymentProcessorCreateWithoutName($version) {
    $this->_apiversion = $version;
    $this->callAPIFailure('payment_processor', 'create', ['is_active' => 1]);
  }

  /**
   * Create payment processor.
   * @dataProvider versionThreeAndFour
   *
   * @throws \CRM_Core_Exception
   */
  public function testPaymentProcessorCreate($version) {
    $this->_apiversion = $version;
    $params = $this->_params;
    $result = $this->callAPIAndDocument('payment_processor', 'create', $params, __FUNCTION__, __FILE__);
    $this->callAPISuccessGetSingle('EntityFinancialAccount', ['entity_table' => 'civicrm_payment_processor', 'entity_id' => $result['id']]);

    // Test that the option values are flushed so ths can be used straight away.
    $this->callAPISuccess('ContributionRecur', 'create', [
      'contact_id' => $this->individualCreate(),
      'amount' => 5,
      'financial_type_id' => 'Donation',
      'payment_processor_id' => 'API Test PP',
      'frequency_interval' => 1,
    ]);
    $this->getAndCheck($params, $result['id'], 'PaymentProcessor');
    $this->assertEquals(2, $result['values'][$result['id']]['payment_instrument_id']);
  }

  /**
   * Update payment processor.
   * @dataProvider versionThreeAndFour
   *
   * @throws \CRM_Core_Exception
   */
  public function testPaymentProcessorUpdate($version) {
    $this->_apiversion = $version;
    $params = $this->_params;
    $params['payment_instrument_id'] = 1;
    $result = $this->callAPISuccess('payment_processor', 'create', $params);
    $this->assertNotNull($result['id']);

    $updateParams = [
      'id' => $result['id'],
      'name' => 'Update API Test',
    ];
    $this->assertDBState('CRM_Financial_DAO_PaymentProcessor', $result['id'], $params);
    $this->callAPISuccess('payment_processor', 'create', $updateParams);
    $result = $this->callAPISuccess('payment_processor', 'get', ['id' => $result['id']]);

    $expectedResult = [
      'id' => $result['id'],
      'domain_id' => $params['domain_id'],
      'name' => $updateParams['name'],
      'title' => $params['title'],
      'frontend_title' => $params['title'],
      'payment_processor_type_id' => $params['payment_processor_type_id'],
      'is_default' => 0,
      'is_test' => 0,
      'class_name' => $params['class_name'],
      'billing_mode' => 1,
      'is_recur' => $params['is_recur'],
      'payment_type' => 1,
      'payment_instrument_id' => 1,
      'is_active' => 1,
    ];
    if ($version === 4) {
      // In APIv3 If a field is default NULL it is not returned.
      foreach ($result['values'][$result['id']] as $field => $value) {
        if (is_null($value)) {
          unset($result['values'][$result['id']][$field]);
        }
      }
    }
    $this->checkArrayEquals($expectedResult, $result['values'][$result['id']]);
  }

  /**
   * Test  using example code.
   */
  public function testPaymentProcessorCreateExample() {
    require_once 'api/v3/examples/PaymentProcessor/Create.ex.php';
    $result = payment_processor_create_example();
    $expectedResult = payment_processor_create_expectedresult();
    $this->assertAPISuccess($result);
  }

  /**
   * Check payment processor delete.
   * @dataProvider versionThreeAndFour
   *
   * @throws \CRM_Core_Exception
   */
  public function testPaymentProcessorDelete($version) {
    $this->_apiversion = $version;
    $result = $this->callAPISuccess('payment_processor', 'create', $this->_params);
    $params = [
      'id' => $result['id'],
    ];

    $this->callAPIAndDocument('payment_processor', 'delete', $params, __FUNCTION__, __FILE__);
  }

  /**
   * Check with valid params array.
   * @dataProvider versionThreeAndFour
   *
   * @throws \CRM_Core_Exception
   */
  public function testPaymentProcessorsGet($version) {
    $this->_apiversion = $version;
    $params = $this->_params;
    $params['user_name'] = 'test@test.com';
    $this->callAPISuccess('payment_processor', 'create', $params);

    $params = [
      'user_name' => 'test@test.com',
    ];
    $results = $this->callAPISuccess('payment_processor', 'get', $params);

    $this->assertEquals(1, $results['count']);
    $this->assertEquals('test@test.com', $results['values'][$results['id']]['user_name']);
  }

}
