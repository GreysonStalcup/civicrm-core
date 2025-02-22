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
 * Class CRM_Event_BAO_EventPermissionsTest
 * @group headless
 */
class CRM_Event_BAO_EventPermissionsTest extends CiviUnitTestCase {

  use Civi\Test\ACLPermissionTrait;

  /**
   * @var int
   */
  private $contactID;

  /**
   * @var int
   */
  private $otherContactID;

  /**
   * @var int
   */
  private $ownEventID;

  /**
   * @var int
   */
  private $otherEventID;

  public function setUp(): void {
    parent::setUp();
    $this->contactID = $this->createLoggedInUser();
    $this->createOwnEvent();
    $this->createOtherEvent();
  }

  public function createOwnEvent() {
    $event = $this->eventCreate([
      'created_id' => $this->contactID,
    ]);
    $this->ownEventID = $event['id'];
  }

  public function createOtherEvent() {
    $this->otherContactID = $this->contactID + 1;
    $event = $this->eventCreate([
      'created_id' => $this->otherContactID,
    ]);
    $this->otherEventID = $event['id'];
  }

  private function setViewOwnEventPermissions() {
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM', 'access CiviEvent', 'view event info'];
  }

  private function setViewAllEventPermissions() {
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM', 'access CiviEvent', 'view event info', 'view event participants'];
  }

  private function setEditAllEventPermissions() {
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM', 'access CiviEvent', 'view event info', 'edit all events'];
  }

  private function setDeleteAllEventPermissions() {
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM', 'access CiviEvent', 'view event info', 'delete in CiviEvent'];
  }

  public function testViewOwnEvent() {
    $this->setViewOwnEventPermissions();
    unset(\Civi::$statics['CRM_Event_BAO_Event']['permissions']);
    $permissions = CRM_Event_BAO_Event::checkPermission($this->ownEventID, CRM_Core_Permission::VIEW);
    $this->assertTrue($permissions);
    // Now check that caching is actually working
    \Civi::$statics['CRM_Event_BAO_Event']['permission']['view'][$this->ownEventID] = FALSE;
    $permissions = CRM_Event_BAO_Event::checkPermission($this->ownEventID, CRM_Core_Permission::VIEW);
    $this->assertFalse($permissions);
  }

  public function testEditOwnEvent() {
    $this->setViewOwnEventPermissions();
    unset(\Civi::$statics['CRM_Event_BAO_Event']['permissions']);
    $permissions = CRM_Event_BAO_Event::checkPermission($this->ownEventID, CRM_Core_Permission::EDIT);
    $this->assertTrue($permissions);
  }

  /**
   * This requires the same permissions as testDeleteOtherEvent()
   */
  public function testDeleteOwnEvent() {
    // Check that you can't delete your own event without "Delete in CiviEvent" permission
    $this->setViewOwnEventPermissions();
    unset(\Civi::$statics['CRM_Event_BAO_Event']['permissions']);
    $permissions = CRM_Event_BAO_Event::checkPermission($this->ownEventID, CRM_Core_Permission::DELETE);
    $this->assertFalse($permissions);
  }

  public function testViewOtherEventDenied() {
    self::setViewOwnEventPermissions();
    unset(\Civi::$statics['CRM_Event_BAO_Event']['permissions']);
    $permissions = CRM_Event_BAO_Event::checkPermission($this->otherEventID, CRM_Core_Permission::VIEW);
    $this->assertFalse($permissions);
  }

  public function testViewOtherEventAllowed() {
    self::setViewAllEventPermissions();
    unset(\Civi::$statics['CRM_Event_BAO_Event']['permissions']);
    $permissions = CRM_Event_BAO_Event::checkPermission($this->otherEventID, CRM_Core_Permission::VIEW);
    $this->assertTrue($permissions);
  }

  /**
   * Test that the contact can view an event with an ACL permitting everyone to view it.
   */
  public function testViewAclEventAllowed() {
    $this->setupScenarioCoreACLEveryonePermittedToEvent();
    $permittedEventID = CRM_Core_Permission::event(CRM_Core_Permission::VIEW, $this->scenarioIDs['Event']['permitted_event']);
    $this->assertEquals($this->scenarioIDs['Event']['permitted_event'], $permittedEventID);
  }

  public function testEditOtherEventDenied() {
    $this->setViewAllEventPermissions();
    unset(\Civi::$statics['CRM_Event_BAO_Event']['permissions']);
    $permissions = CRM_Event_BAO_Event::checkPermission($this->otherEventID, CRM_Core_Permission::EDIT);
    $this->assertFalse($permissions);
  }

  public function testEditOtherEventAllowed() {
    self::setEditAllEventPermissions();
    unset(\Civi::$statics['CRM_Event_BAO_Event']['permissions']);
    $permissions = CRM_Event_BAO_Event::checkPermission($this->otherEventID, CRM_Core_Permission::EDIT);
    $this->assertTrue($permissions);
  }

  public function testDeleteOtherEventAllowed() {
    self::setDeleteAllEventPermissions();
    unset(\Civi::$statics['CRM_Event_BAO_Event']['permissions']);
    $permissions = CRM_Event_BAO_Event::checkPermission($this->otherEventID, CRM_Core_Permission::DELETE);
    $this->assertTrue($permissions);
  }

  public function testDeleteOtherEventDenied() {
    // FIXME: This test could be improved, but for now it checks that we can't delete if we don't have "Delete in CiviEvent"
    $this->setEditAllEventPermissions();
    unset(\Civi::$statics['CRM_Event_BAO_Event']['permissions']);
    $permissions = CRM_Event_BAO_Event::checkPermission($this->otherEventID, CRM_Core_Permission::DELETE);
    $this->assertFalse($permissions);
  }

  /**
   * Test get complete info function returns all info for contacts with view all info.
   */
  public function testGetCompleteInfo() {
    $this->setupScenarioCoreACLEveryonePermittedToEvent();
    $info = CRM_Event_BAO_Event::getCompleteInfo('20000101');
    $this->assertEquals('Annual CiviCRM meet', $info[0]['title']);
    $this->assertEquals('Annual CiviCRM meet', $info[1]['title']);
  }

}
