{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{* this template is used for adding/editing/deleting membership status  *}
<div class="crm-block crm-form-block crm-membership-status-form-block" id=membership_status>
<fieldset><legend>{if $action eq 1}{ts}New Membership Status{/ts}{elseif $action eq 2}{ts}Edit Membership Status{/ts}{else}{ts}Delete Membership Status{/ts}{/if}</legend>
 <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
  {if $action eq 8}
    <div class="messages status no-popup">
      {icon icon="fa-info-circle"}{/icon}
      {$deleteMessage|escape}
    </div>
  {else}
    <table class="form-layout-compressed">
      {if $action eq 2}
      <tr class="crm-membership-status-form-block-name">
  <td class="label">{$form.name.label}</td>
        <td class="html-adjust">{$form.name.html}</td>
      </tr>
      {/if}

      <tr class="crm-membership-status-form-block-label">
  <td class="label">{$form.label.label}{if $action == 2}{include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_membership_status' field='label' id=$id}{/if}</td>
        <td class="html-adjust">{$form.label.html}<br />
           <span class="description">{ts}Display name for this Membership status (e.g. New, Current, Grace, Expired...).{/ts}</span>
        </td>
      </tr>

      <tr class="crm-membership-status-form-block-start_event">
        <td class="label">{$form.start_event.label}</td>
        <td class="html-adjust">{$form.start_event.html}<br />
           <span class="description">{ts}When does this status begin? EXAMPLE: <strong>New</strong> status begins at the Member Since.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-status-form-block-start_event_unit_interval">
        <td class="label">{$form.start_event_adjust_unit.label}</td>
        <td class="html-adjust">&nbsp;{$form.start_event_adjust_interval.html}&nbsp;&nbsp;{$form.start_event_adjust_unit.html}<br />
           <span class="description">{ts}Optional adjustment period added or subtracted from the Start Event. EXAMPLE: <strong>Current</strong> status might begin at Member Since PLUS 3 months (to distinguish Current from New members).{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-status-form-block-end_event">
        <td class="label">{$form.end_event.label}</td>
        <td class="html-adjust">{$form.end_event.html}<br />
           <span class="description">{ts}When does this status end? EXAMPLE: <strong>Current</strong> status ends at the Membership Expiration Date.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-status-form-block-end_event_unit_interval">
        <td class="label">{$form.end_event_adjust_unit.label}</td>
        <td class="html-adjust">&nbsp;{$form.end_event_adjust_interval.html}&nbsp;{$form.end_event_adjust_unit.html}<br />
           <span class="description">{ts}Optional adjustment period added or subtracted from the End Event. EXAMPLE: <strong>Grace</strong> status might end at the Membership Expiration Date PLUS 1 month.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-status-form-block-is_current_member">
        <td class="label">{$form.is_current_member.label}</td><td class="html-adjust">{$form.is_current_member.html}<br />
           <span class="description">{ts}Should this status be considered a current membership in good standing. EXAMPLE: New, Current and Grace could all be considered 'current'.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-status-form-block-is_admin">
        <td class="label">{$form.is_admin.label}</td>
        <td class="html-adjust">{$form.is_admin.html}<br />
           <span class="description">{ts}Check this box if this status is for use by administrative staff only. If checked, this status is never automatically assigned by CiviMember. It is assigned to a contact's Membership by checking the <strong>Status Override</strong> flag when adding or editing the Membership record. Start and End Event settings are ignored for Administrator statuses. EXAMPLE: This setting can be useful for special case statuses like 'Non-expiring', 'Barred' or 'Expelled', etc.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-status-form-block-weight">
        <td class="label">{$form.weight.label}</td>
        <td class="html-adjust">&nbsp;{$form.weight.html}<br />
           <span class="description">{ts}Weight sets the order of precedence for automatic assignment of status to a membership. It also sets the order for status displays. EXAMPLE: The default 'New' and 'Current' statuses have overlapping ranges. Memberships that meet both status range criteria are assigned the status with the lower weight.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-status-form-block-is_default">
        <td class="label">{$form.is_default.label}</td>
        <td class="html-adjust">{$form.is_default.html}<br />
           <span class="description">{ts}The default status is assigned when there are no matching status rules for a membership.{/ts}</span>
        </td>
      </tr>
      <tr class="crm-membership-status-form-block-is_active">
        <td class="label">{$form.is_active.label}</td>
        <td class="html-adjust">{$form.is_active.html}<br />
           <span class="description">{ts}Is this status enabled.{/ts}</span>
        </td>
      </tr>
    </table>
    {/if}
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
  <br clear="all" />
</fieldset>
</div>
