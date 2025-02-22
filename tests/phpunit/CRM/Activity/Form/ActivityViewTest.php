<?php

/**
 * @group headless
 */
class CRM_Activity_Form_ActivityViewTest extends CiviUnitTestCase {

  /**
   * Cleanup after class.
   *
   * @throws \CRM_Core_Exception
   */
  public function tearDown(): void {
    $tablesToTruncate = [
      'civicrm_activity',
      'civicrm_activity_contact',
    ];
    $this->quickCleanup($tablesToTruncate);
    parent::tearDown();
  }

  /**
   * Test that the smarty template for ActivityView contains what we expect
   * after preProcess().
   *
   * @throws \CRM_Core_Exception
   */
  public function testActivityViewPreProcess(): void {
    // create activity
    $activity = $this->activityCreate();

    // $activity doesn't contain everything we need, so do another get call
    $activityMoreInfo = $this->callAPISuccess('activity', 'getsingle', ['id' => $activity['id']]);

    // do preProcess
    $activityViewForm = new CRM_Activity_Form_ActivityView();
    $activityViewForm->controller = new CRM_Core_Controller_Simple('CRM_Activity_Form_ActivityView', 'Activity');
    $activityViewForm->set('id', $activity['id']);
    $activityViewForm->set('context', 'activity');
    $activityViewForm->set('cid', $activity['target_contact_id']);
    $activityViewForm->preProcess();

    // check one of the smarty template vars
    // not checking EVERYTHING
    $templateVar = CRM_Activity_Form_ActivityView::getTemplate()->get_template_vars('values');
    $expected = [
      'assignee_contact' => [0 => $activity['target_contact_id']],
      // it's always Julia
      'assignee_contact_value' => 'Anderson, Julia',
      'target_contact' => [0 => $activity['target_contact_id']],
      'target_contact_value' => 'Anderson, Julia',
      'source_contact' => $activityMoreInfo['source_contact_sort_name'],
      'case_subject' => NULL,
      'id' => $activity['id'],
      'subject' => $activity['values'][$activity['id']]['subject'],
      'activity_subject' => $activity['values'][$activity['id']]['subject'],
      'activity_date_time' => $activityMoreInfo['activity_date_time'],
      'location' => $activity['values'][$activity['id']]['location'],
      'activity_location' => $activity['values'][$activity['id']]['location'],
      'duration' => '90',
      'activity_duration' => '90',
      'details' => $activity['values'][$activity['id']]['details'],
      'activity_details' => $activity['values'][$activity['id']]['details'],
      'is_test' => '0',
      'activity_is_test' => '0',
      'is_auto' => '0',
      'is_current_revision' => '1',
      'is_deleted' => '0',
      'activity_is_deleted' => '0',
      'is_star' => '0',
      'created_date' => $activityMoreInfo['created_date'],
      'activity_created_date' => $activityMoreInfo['created_date'],
      'modified_date' => $activityMoreInfo['modified_date'],
      'activity_modified_date' => $activityMoreInfo['modified_date'],
      'attachment' => NULL,
      'mailingId' => NULL,
      'campaign' => NULL,
      'engagement_level' => NULL,
    ];

    $this->assertEquals($expected, $templateVar);
  }

}
