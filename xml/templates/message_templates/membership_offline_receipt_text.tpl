{assign var="greeting" value="{contact.email_greeting_display}"}{if $greeting}{$greeting},{/if}

{if $receipt_text}
{$receipt_text}
{else}{ts}Thank you for this contribution.{/ts}{/if}

{if empty($lineItem)}
===========================================================
{ts}Membership Information{/ts}

===========================================================
{ts}Membership Type{/ts}: {$membership_name}
{/if}
{if empty($cancelled)}
{if empty($lineItem)}
{ts}Membership Start Date{/ts}: {$mem_start_date}
{ts}Membership Expiration Date{/ts}: {$mem_end_date}
{/if}

{if $formValues.total_amount OR $formValues.total_amount eq 0 }
===========================================================
{ts}Membership Fee{/ts}

===========================================================
{if !empty($formValues.contributionType_name)}
{ts}Financial Type{/ts}: {$formValues.contributionType_name}
{/if}
{if !empty($lineItem)}
{foreach from=$lineItem item=value key=priceset}
{capture assign=ts_item}{ts}Item{/ts}{/capture}
{capture assign=ts_total}{ts}Fee{/ts}{/capture}
{if !empty($dataArray)}
{capture assign=ts_subtotal}{ts}Subtotal{/ts}{/capture}
{capture assign=ts_taxRate}{ts}Tax Rate{/ts}{/capture}
{capture assign=ts_taxAmount}{ts}Tax Amount{/ts}{/capture}
{capture assign=ts_total}{ts}Total{/ts}{/capture}
{/if}
{capture assign=ts_start_date}{ts}Membership Start Date{/ts}{/capture}
{capture assign=ts_end_date}{ts}Membership Expiration Date{/ts}{/capture}
{$ts_item|string_format:"%-30s"} {$ts_total|string_format:"%10s"} {if !empty($dataArray)} {$ts_subtotal|string_format:"%10s"} {$ts_taxRate|string_format:"%10s"} {$ts_taxAmount|string_format:"%10s"} {$ts_total|string_format:"%10s"} {/if} {$ts_start_date|string_format:"%20s"} {$ts_end_date|string_format:"%20s"}
--------------------------------------------------------------------------------------------------

{foreach from=$value item=line}
{capture assign=ts_item}{if $line.html_type eq 'Text'}{$line.label}{else}{$line.field_title} - {$line.label}{/if} {if $line.description} {$line.description}{/if}{/capture}{$ts_item|truncate:30:"..."|string_format:"%-30s"} {$line.line_total|crmMoney|string_format:"%10s"}  {if !empty($dataArray)} {$line.unit_price*$line.qty|crmMoney:$currency|string_format:"%10s"} {if $line.tax_rate || $line.tax_amount != ""}  {$line.tax_rate|string_format:"%.2f"} %  {$line.tax_amount|crmMoney:$currency|string_format:"%10s"}  {else}                  {/if}   {$line.line_total+$line.tax_amount|crmMoney|string_format:"%10s"} {/if} {$line.start_date|string_format:"%20s"} {$line.end_date|string_format:"%20s"}
{/foreach}
{/foreach}

{if !empty($dataArray)}
{ts}Amount before Tax{/ts}: {$formValues.total_amount-$totalTaxAmount|crmMoney:$currency}

{foreach from=$dataArray item=value key=priceset}
{if $priceset}
{$taxTerm} {$priceset|string_format:"%.2f"} %: {$value|crmMoney:$currency}
{elseif  $priceset == 0}
{ts}No{/ts} {$taxTerm}: {$value|crmMoney:$currency}
{/if}
{/foreach}
{/if}
--------------------------------------------------------------------------------------------------
{/if}

{if $totalTaxAmount}
{ts}Total Tax Amount{/ts}: {$totalTaxAmount|crmMoney:$currency}
{/if}

{ts}Amount{/ts}: {$formValues.total_amount|crmMoney}
{if !empty($receive_date)}
{ts}Date Received{/ts}: {$receive_date|truncate:10:''|crmDate}
{/if}
{if !empty($formValues.paidBy)}
{ts}Paid By{/ts}: {$formValues.paidBy}
{if !empty($formValues.check_number)}
{ts}Check Number{/ts}: {$formValues.check_number}
{/if}
{/if}
{/if}
{/if}

{if !empty($isPrimary) }
{if !empty($billingName)}

===========================================================
{ts}Billing Name and Address{/ts}

===========================================================
{$billingName}
{$address}
{/if}

{if !empty($credit_card_type)}
===========================================================
{ts}Credit Card Information{/ts}

===========================================================
{$credit_card_type}
{$credit_card_number}
{ts}Expires{/ts}: {$credit_card_exp_date|truncate:7:''|crmDate}
{/if}
{/if}

{if !empty($customValues)}
===========================================================
{ts}Membership Options{/ts}

===========================================================
{foreach from=$customValues item=value key=customName}
 {$customName} : {$value}
{/foreach}
{/if}
