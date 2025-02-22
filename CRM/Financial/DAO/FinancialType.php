<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from xml/schema/CRM/Financial/FinancialType.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:97ab5fc26c36616ca7805c5578ed5657)
 */

/**
 * Database access object for the FinancialType entity.
 */
class CRM_Financial_DAO_FinancialType extends CRM_Core_DAO {
  const EXT = 'civicrm';
  const TABLE_ADDED = '1.3';
  const COMPONENT = 'CiviContribute';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_financial_type';

  /**
   * Field to show when displaying a record.
   *
   * @var string
   */
  public static $_labelField = 'name';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Paths for accessing this entity in the UI.
   *
   * @var string[]
   */
  protected static $_paths = [
    'add' => 'civicrm/admin/financial/financialType/edit?action=add&reset=1',
    'update' => 'civicrm/admin/financial/financialType/edit?action=update&id=[id]&reset=1',
    'delete' => 'civicrm/admin/financial/financialType/edit?action=delete&id=[id]&reset=1',
  ];

  /**
   * ID of original financial_type so you can search this table by the financial_type.id and then select the relevant version based on the timestamp
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * Financial Type Name.
   *
   * @var string
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $name;

  /**
   * Financial Type Description.
   *
   * @var string|null
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $description;

  /**
   * Is this financial type tax-deductible? If true, contributions of this type may be fully OR partially deductible - non-deductible amount is stored in the Contribution record.
   *
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_deductible;

  /**
   * Is this a predefined system object?
   *
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_reserved;

  /**
   * Is this property active?
   *
   * @var bool|string
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $is_active;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_financial_type';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? ts('Financial Types') : ts('Financial Type');
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Financial Type ID'),
          'description' => ts('ID of original financial_type so you can search this table by the financial_type.id and then select the relevant version based on the timestamp'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_financial_type.id',
          'table_name' => 'civicrm_financial_type',
          'entity' => 'FinancialType',
          'bao' => 'CRM_Financial_BAO_FinancialType',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => '1.3',
        ],
        'financial_type' => [
          'name' => 'name',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Financial Type'),
          'description' => ts('Financial Type Name.'),
          'required' => TRUE,
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'usage' => [
            'import' => TRUE,
            'export' => TRUE,
            'duplicate_matching' => TRUE,
            'token' => FALSE,
          ],
          'import' => TRUE,
          'where' => 'civicrm_financial_type.name',
          'headerPattern' => '/(finan(cial)?)?type/i',
          'dataPattern' => '/donation|member|campaign/i',
          'export' => TRUE,
          'table_name' => 'civicrm_financial_type',
          'entity' => 'FinancialType',
          'bao' => 'CRM_Financial_BAO_FinancialType',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
            'label' => ts("Name"),
          ],
          'add' => '1.3',
        ],
        'description' => [
          'name' => 'description',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Description'),
          'description' => ts('Financial Type Description.'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_financial_type.description',
          'table_name' => 'civicrm_financial_type',
          'entity' => 'FinancialType',
          'bao' => 'CRM_Financial_BAO_FinancialType',
          'localizable' => 0,
          'html' => [
            'type' => 'TextArea',
            'label' => ts("Description"),
          ],
          'add' => '1.3',
        ],
        'is_deductible' => [
          'name' => 'is_deductible',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Is Tax Deductible?'),
          'description' => ts('Is this financial type tax-deductible? If true, contributions of this type may be fully OR partially deductible - non-deductible amount is stored in the Contribution record.'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_financial_type.is_deductible',
          'default' => '0',
          'table_name' => 'civicrm_financial_type',
          'entity' => 'FinancialType',
          'bao' => 'CRM_Financial_BAO_FinancialType',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
            'label' => ts("Tax-Deductible?"),
          ],
          'add' => '1.3',
        ],
        'is_reserved' => [
          'name' => 'is_reserved',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Financial Type is Reserved?'),
          'description' => ts('Is this a predefined system object?'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_financial_type.is_reserved',
          'default' => '0',
          'table_name' => 'civicrm_financial_type',
          'entity' => 'FinancialType',
          'bao' => 'CRM_Financial_BAO_FinancialType',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
            'label' => ts("Reserved?"),
          ],
          'add' => '1.3',
        ],
        'is_active' => [
          'name' => 'is_active',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Financial Type Is Active?'),
          'description' => ts('Is this property active?'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civicrm_financial_type.is_active',
          'default' => '1',
          'table_name' => 'civicrm_financial_type',
          'entity' => 'FinancialType',
          'bao' => 'CRM_Financial_BAO_FinancialType',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
            'label' => ts("Enabled"),
          ],
          'add' => '1.3',
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'financial_type', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'financial_type', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
