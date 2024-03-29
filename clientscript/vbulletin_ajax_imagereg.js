/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.6.11 Patch Level 1
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

/**
* Adds onclick event to the save search prefs buttons
*
* @param	string	The ID of the button that fires the search prefs
*/
function vB_AJAX_ImageReg_Init()
{
	if (AJAX_Compatible && (typeof vb_disable_ajax == 'undefined' || vb_disable_ajax < 2) && fetch_object('refresh_imagereg'))
	{
		fetch_object('refresh_imagereg').onclick = vB_AJAX_ImageReg.prototype.image_click;
		fetch_object('refresh_imagereg').style.cursor = pointer_cursor;
		fetch_object('refresh_imagereg').style.display = '';

		if (fetch_object('imagereg'))
		{
			fetch_object('imagereg').style.cursor = pointer_cursor;
			fetch_object('imagereg').onclick = vB_AJAX_ImageReg.prototype.image_click;
		}
	}
};

/**
* Class to handle saveing search prefs
*
* @param	object	The form object containing the search options
*/
function vB_AJAX_ImageReg()
{
	// AJAX handler
	this.xml_sender = null;

	// Imagehach
	this.imagehash = '';

	// Closure
	var me = this;

	/**
	* OnReadyStateChange callback. Uses a closure to keep state.
	* Remember to use me instead of this inside this function!
	*/
	this.handle_ajax_response = function()
	{
		if (me.xml_sender.handler.readyState == 4 && me.xml_sender.handler.status == 200)
		{
			fetch_object('progress_imagereg').style.display = 'none';
			if (me.xml_sender.handler.responseXML)
			{
				// check for error
				var error = me.xml_sender.fetch_data(fetch_tags(me.xml_sender.handler.responseXML, 'error')[0]);
				if (error)
				{
					alert(error);
				}
				else
				{
					var imagehash = me.xml_sender.fetch_data(fetch_tags(me.xml_sender.handler.responseXML, 'imagehash')[0]);
					if (imagehash)
					{
						fetch_object('imagehash').value = imagehash;
						fetch_object('imagereg').src = 'image.php?' + SESSIONURL + 'type=regcheck&imagehash=' + imagehash;
					}
				}
			}

			if (is_ie)
			{
				me.xml_sender.handler.abort();
			}
		}
	}
};

/**
* Submits the form via Ajax
*/
vB_AJAX_ImageReg.prototype.fetch_image = function()
{
	fetch_object('progress_imagereg').style.display = '';
	this.xml_sender = new vB_AJAX_Handler(true);
	this.xml_sender.onreadystatechange(this.handle_ajax_response);
	this.xml_sender.send('ajax.php?do=imagereg&imagehash=' + this.imagehash, 'do=imagereg&imagehash=' + this.imagehash);
};

/**
* Handles the form 'submit' action
*/
vB_AJAX_ImageReg.prototype.image_click = function()
{
	var AJAX_ImageReg = new vB_AJAX_ImageReg();
	AJAX_ImageReg.imagehash = fetch_object('imagehash').value;
	AJAX_ImageReg.fetch_image();
	return false;
};

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 11:43, Mon Sep 8th 2008
|| # CVS: $RCSfile$ - $Revision: 16534 $
|| ####################################################################
\*======================================================================*/