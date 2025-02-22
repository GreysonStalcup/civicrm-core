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

namespace Civi\Core;

/**
 * Class SettingsBag
 * @package Civi\Core
 *
 * Read and write settings for a given domain (or contact).
 *
 * If the target entity does not already have a value for the setting, then
 * the defaults will be used. If mandatory values are provided, they will
 * override any defaults or custom settings.
 *
 * It's expected that the SettingsBag will have O(50-250) settings -- and that
 * we'll load the full bag on many page requests. Consequently, we don't
 * want the full metadata (help text and version history and HTML widgets)
 * for all 250 settings, but we do need the default values.
 *
 * This class is not usually instantiated directly. Instead, use SettingsManager
 * or Civi::settings().
 *
 * @see \Civi::settings()
 * @see SettingsManagerTest
 */
class SettingsBag {

  protected $domainId;

  protected $contactId;

  /**
   * @var array
   *   Array(string $settingName => mixed $value).
   */
  protected $defaults;

  /**
   * @var array
   *   Array(string $settingName => mixed $value).
   */
  protected $mandatory;

  /**
   * The result of combining default values, mandatory
   * values, and user values.
   *
   * @var array|null
   *   Array(string $settingName => mixed $value).
   */
  protected $combined;

  /**
   * @var array
   */
  protected $values;

  /**
   * @param int $domainId
   *   The domain for which we want settings.
   * @param int|null $contactId
   *   The contact for which we want settings. Use NULL for domain settings.
   */
  public function __construct($domainId, $contactId) {
    $this->domainId = $domainId;
    $this->contactId = $contactId;
    $this->values = [];
    $this->combined = NULL;
  }

  /**
   * Set/replace the default values.
   *
   * @param array $defaults
   *   Array(string $settingName => mixed $value).
   * @return SettingsBag
   */
  public function loadDefaults($defaults) {
    $this->defaults = $defaults;
    $this->combined = NULL;
    return $this;
  }

  /**
   * Set/replace the mandatory values.
   *
   * @param array $mandatory
   *   Array(string $settingName => mixed $value).
   * @return SettingsBag
   */
  public function loadMandatory($mandatory) {
    $this->mandatory = $mandatory;
    $this->combined = NULL;
    return $this;
  }

  /**
   * Load all explicit settings that apply to this domain or contact.
   *
   * @return SettingsBag
   */
  public function loadValues() {
    // Note: Don't use DAO child classes. They require fields() which require
    // translations -- which are keyed off settings!

    $this->values = [];
    $this->combined = NULL;

    $isUpgradeMode = \CRM_Core_Config::isUpgradeMode();

    // Only query table if it exists.
    if (!$isUpgradeMode || \CRM_Core_DAO::checkTableExists('civicrm_setting')) {
      $dao = \CRM_Core_DAO::executeQuery($this->createQuery()->toSQL());
      while ($dao->fetch()) {
        $this->values[$dao->name] = ($dao->value !== NULL) ? \CRM_Utils_String::unserialize($dao->value) : NULL;
      }
    }

    return $this;
  }

  /**
   * Add a batch of settings. Save them.
   *
   * @param array $settings
   *   Array(string $settingName => mixed $settingValue).
   * @return SettingsBag
   */
  public function add(array $settings) {
    foreach ($settings as $key => $value) {
      $this->set($key, $value);
    }
    return $this;
  }

  /**
   * Get a list of all effective settings.
   *
   * @return array
   *   Array(string $settingName => mixed $settingValue).
   */
  public function all() {
    if ($this->combined === NULL) {
      $this->combined = $this->combine(
        [$this->defaults, $this->values, $this->mandatory]
      );
      // computeVirtual() depends on completion of preceding pass.
      $this->combined = $this->combine(
        [$this->combined, $this->computeVirtual()]
      );
    }
    return $this->combined;
  }

  /**
   * Determine the effective value.
   *
   * @param string $key
   * @return mixed
   */
  public function get($key) {
    $all = $this->all();
    return $all[$key] ?? NULL;
  }

  /**
   * Determine the default value of a setting.
   *
   * @param string $key
   *   The simple name of the setting.
   * @return mixed|NULL
   */
  public function getDefault($key) {
    return $this->defaults[$key] ?? NULL;
  }

  /**
   * Determine the explicitly designated value, regardless of
   * any default or mandatory values.
   *
   * @param string $key
   *   The simple name of the setting.
   * @return mixed|NULL
   */
  public function getExplicit($key) {
    return ($this->values[$key] ?? NULL);
  }

  /**
   * Determine the mandatory value of a setting.
   *
   * @param string $key
   *   The simple name of the setting.
   * @return mixed|NULL
   */
  public function getMandatory($key) {
    return $this->mandatory[$key] ?? NULL;
  }

  /**
   * Determine if the entity has explicitly designated a value.
   *
   * Note that get() may still return other values based on
   * mandatory values or defaults.
   *
   * @param string $key
   *   The simple name of the setting.
   * @return bool
   */
  public function hasExplict($key) {
    // NULL means no designated value.
    return isset($this->values[$key]);
  }

  /**
   * Removes any explicit settings. This restores the default.
   *
   * @param string $key
   *   The simple name of the setting.
   * @return SettingsBag
   */
  public function revert($key) {
    // It might be better to DELETE (to avoid long-term leaks),
    // but setting NULL is simpler for now.
    return $this->set($key, NULL);
  }

  /**
   * Add a single setting. Save it.
   *
   * @param string $key
   *   The simple name of the setting.
   * @param mixed $value
   *   The new, explicit value of the setting.
   * @return SettingsBag
   */
  public function set($key, $value) {
    if ($this->updateVirtual($key, $value)) {
      return $this;
    }
    $this->setDb($key, $value);
    $this->values[$key] = $value;
    $this->combined = NULL;
    return $this;
  }

  /**
   * Update a virtualized/deprecated setting.
   *
   * Temporary handling for phasing out contribution_invoice_settings.
   *
   * Until we have transitioned we need to handle setting & retrieving
   * contribution_invoice_settings.
   *
   * Once removed from core we will add deprecation notices & then remove this.
   *
   * https://lab.civicrm.org/dev/core/issues/1558
   *
   * @param string $key
   * @param array $value
   * @return bool
   *   TRUE if $key is a virtualized setting. FALSE if it is a normal setting.
   */
  public function updateVirtual($key, $value) {
    if ($key === 'contribution_invoice_settings') {
      foreach (SettingsBag::getContributionInvoiceSettingKeys() as $possibleKeyName => $settingName) {
        $keyValue = $value[$possibleKeyName] ?? '';
        if ($possibleKeyName === 'invoicing' && is_array($keyValue)) {
          $keyValue = $keyValue['invoicing'];
        }
        $this->set($settingName, $keyValue);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Determine the values of any virtual/computed settings.
   *
   * @return array
   */
  public function computeVirtual() {
    $contributionSettings = [];
    foreach (SettingsBag::getContributionInvoiceSettingKeys() as $keyName => $settingName) {
      switch ($keyName) {
        case 'invoicing':
          $contributionSettings[$keyName] = $this->get($settingName) ? [$keyName => 1] : 0;
          break;

        default:
          $contributionSettings[$keyName] = $this->get($settingName);
          break;
      }
    }
    return ['contribution_invoice_settings' => $contributionSettings];
  }

  /**
   * @return \CRM_Utils_SQL_Select
   */
  protected function createQuery() {
    $select = \CRM_Utils_SQL_Select::from('civicrm_setting')
      ->select('id, name, value, domain_id, contact_id, is_domain, component_id, created_date, created_id')
      ->where('domain_id = #id', [
        'id' => $this->domainId,
      ]);
    if ($this->contactId === NULL) {
      $select->where('is_domain = 1');
    }
    else {
      $select->where('contact_id = #id', [
        'id' => $this->contactId,
      ]);
      $select->where('is_domain = 0');
    }
    return $select;
  }

  /**
   * Combine a series of arrays, excluding any
   * null values. Later values override earlier
   * values.
   *
   * @param array $arrays
   *   List of arrays to combine.
   * @return array
   */
  protected function combine($arrays) {
    $combined = [];
    foreach ($arrays as $array) {
      foreach ($array as $k => $v) {
        if ($v !== NULL) {
          $combined[$k] = $v;
        }
      }
    }
    return $combined;
  }

  /**
   * Update the DB record for this setting.
   *
   * @param string $name
   *   The simple name of the setting.
   * @param mixed $value
   *   The new value of the setting.
   */
  protected function setDb($name, $value) {
    $fields = [];
    $fieldsToSet = \CRM_Core_BAO_Setting::validateSettingsInput([$name => $value], $fields);
    //We haven't traditionally validated inputs to setItem, so this breaks things.
    //foreach ($fieldsToSet as $settingField => &$settingValue) {
    //  self::validateSetting($settingValue, $fields['values'][$settingField]);
    //}

    $metadata = $fields['values'][$name];

    $dao = new \CRM_Core_DAO_Setting();
    $dao->name = $name;
    $dao->domain_id = $this->domainId;
    if ($this->contactId) {
      $dao->contact_id = $this->contactId;
      $dao->is_domain = 0;
    }
    else {
      $dao->is_domain = 1;
    }
    $dao->find(TRUE);

    // Call 'on_change' listeners. It would be nice to only fire when there's
    // a genuine change in the data. However, PHP developers have mixed
    // expectations about whether 0, '0', '', NULL, and FALSE represent the same
    // value, so there's no universal way to determine if a change is genuine.
    if (isset($metadata['on_change'])) {
      foreach ($metadata['on_change'] as $callback) {
        call_user_func(
          \Civi\Core\Resolver::singleton()->get($callback),
          \CRM_Utils_String::unserialize($dao->value),
          $value,
          $metadata,
          $this->domainId
        );
      }
    }

    if (!is_array($value) && \CRM_Utils_System::isNull($value)) {
      $dao->value = 'null';
    }
    else {
      $dao->value = serialize($value);
    }

    if (!isset(\Civi::$statics[__CLASS__]['upgradeMode'])) {
      \Civi::$statics[__CLASS__]['upgradeMode'] = \CRM_Core_Config::isUpgradeMode();
    }
    if (\Civi::$statics[__CLASS__]['upgradeMode'] && \CRM_Core_BAO_SchemaHandler::checkIfFieldExists('civicrm_setting', 'group_name')) {
      $dao->group_name = 'placeholder';
    }

    $dao->created_date = \CRM_Utils_Time::getTime('YmdHis');

    $session = \CRM_Core_Session::singleton();
    if (\CRM_Contact_BAO_Contact_Utils::isContactId($session->get('userID'))) {
      $dao->created_id = $session->get('userID');
    }

    if ($dao->id) {
      $dao->save();
    }
    else {
      // Cannot use $dao->save(); in upgrade mode (eg WP + Civi 4.4=>4.7), the DAO will refuse
      // to save the field `group_name`, which is required in older schema.
      \CRM_Core_DAO::executeQuery(\CRM_Utils_SQL_Insert::dao($dao)->toSQL());
    }
  }

  /**
   * @return array
   */
  public static function getContributionInvoiceSettingKeys(): array {
    $convertedKeys = [
      'credit_notes_prefix' => 'credit_notes_prefix',
      'invoice_prefix' => 'invoice_prefix',
      'due_date' => 'invoice_due_date',
      'due_date_period' => 'invoice_due_date_period',
      'notes' => 'invoice_notes',
      'is_email_pdf'  => 'invoice_is_email_pdf',
      'tax_term' => 'tax_term',
      'tax_display_settings' => 'tax_display_settings',
      'invoicing' => 'invoicing',
    ];
    return $convertedKeys;
  }

}
