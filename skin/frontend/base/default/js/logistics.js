/**
 * Smartsend_Logistics
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@smartsend.dk so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to http://www.smartsend.dk
 *
 * @folder		/skin/frontend/base/default/js/logistics.js
 * @category	Smartsend
 * @package		Smartsend_Logistics
 * @author		Anders Bilfeldt
 * @url			www.smartsend.dk
 */


function showShipping(code, id) {
    hideShippingAll();

    if (jQuery('#' + id).is(':checked')) {
        if (jQuery('#' + 'shipping_form_' + code).length != 0) {
            jQuery('#' + 'shipping_form_' + code).show();
            jQuery('#' + id).find('.required-entry').attr('disabled', 'false');
        }
    }
}
function hideShippingAll() {
    jQuery('input[type="radio"][name="shipping_method"]').each(function() {
        var code = jQuery(this).val();

        jQuery('#' + 'shipping_form_' + code).hide();
        jQuery(this).find('.required-entry').attr('disabled', 'true');
    });
}

