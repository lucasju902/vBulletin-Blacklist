<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.6.11 Patch Level 1 - Licence Number 3578c1c3
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

/**
* Class that provides payment verification and form generation functions
*
* @package	vBulletin
* @version	$Revision: 16101 $
* @date		$Date: 2007-01-15 06:44:01 -0600 (Mon, 15 Jan 2007) $
*/
class vB_PaidSubscriptionMethod_worldpay extends vB_PaidSubscriptionMethod
{
	/**
	* The currencies that are supported by this payment provider
	*
	* @var	array
	*/
	var $supported_currency = array('usd' => true, 'gbp' => true, 'eur' => true);

	/**
	* The variable indicating if this payment provider supports recurring transactions
	*
	* @var	bool
	*/
	var $supports_recurring = false;

	/**
	* Perform verification of the payment, this is called from the payment gateway
	*
	* @return	bool	Whether the payment is valid
	*/
	function verify_payment()
	{
		$this->registry->input->clean_array_gpc('r', array(
			'callbackPW'  => TYPE_STR,
			'desc'        => TYPE_STR,
			'transStatus' => TYPE_STR,
			'authMode'    => TYPE_STR,
			'cost'        => TYPE_NUM,
			'currency'    => TYPE_STR,
			'transId'     => TYPE_STR
		));

		$this->transaction_id = $this->registry->GPC['transId'];

		if ($this->registry->GPC['callbackPW'] == $this->settings['worldpay_password'])
		{
			$this->paymentinfo = $this->registry->db->query_first("
				SELECT paymentinfo.*, user.username
				FROM " . TABLE_PREFIX . "paymentinfo AS paymentinfo
				INNER JOIN " . TABLE_PREFIX . "user AS user USING (userid)
				WHERE hash = '" . $this->registry->db->escape_string($this->registry->GPC['desc']) . "'
			");
			// lets check the values
			if (!empty($this->paymentinfo))
			{
				$sub = $this->registry->db->query_first("SELECT * FROM " . TABLE_PREFIX . "subscription WHERE subscriptionid = " . $this->paymentinfo['subscriptionid']);
				$cost = unserialize($sub['cost']);
				$this->paymentinfo['currency'] = strtolower($this->registry->GPC['currency']);
				$this->paymentinfo['amount'] = floatval($this->registry->GPC['cost']);
				if ($this->registry->GPC['transStatus'] == 'Y' AND ($this->registry->GPC['authMode'] == 'A' OR $this->registry->GPC['authMode'] == 'O'))
				{
					if (doubleval($this->registry->GPC['cost']) == doubleval($cost["{$this->paymentinfo[subscriptionsubid]}"]['cost'][strtolower($this->registry->GPC['currency'])]))
					{
						$this->type = 1;
					}
				}
				return true;
			}
		}
		return false;
	}

	/**
	* Test that required settings are available, and if we can communicate with the server (if required)
	*
	* @return	bool	If the vBulletin has all the information required to accept payments
	*/
	function test()
	{
		return (!empty($this->settings['worldpay_instid']) AND !empty($this->settings['worldpay_password']));
	}

	/**
	* Generates HTML for the subscription form page
	*
	* @param	string		Hash used to indicate the transaction within vBulletin
	* @param	string		The cost of this payment
	* @param	string		The currency of this payment
	* @param	array		Information regarding the subscription that is being purchased
	* @param	array		Information about the user who is purchasing this subscription
	* @param	array		Array containing specific data about the cost and time for the specific subscription period
	*
	* @return	array		Compiled form information
	*/
	function generate_form_html($hash, $cost, $currency, $subinfo, $userinfo, $timeinfo)
	{
		global $vbphrase, $vbulletin, $stylevar, $show;

		$item = $hash;
		$currency = strtoupper($currency);

		$form['action'] = 'https://select.worldpay.com/wcc/purchase';
		$form['method'] = 'post';

		// load settings into array so the template system can access them
		$settings =& $this->settings;

		eval('$form[\'hiddenfields\'] .= "' . fetch_template('subscription_payment_worldpay') . '";');
		return $form;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 11:43, Mon Sep 8th 2008
|| # CVS: $RCSfile$ - $Revision: 16101 $
|| ####################################################################
\*======================================================================*/
?>