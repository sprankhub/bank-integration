<?php
/**
 * Kinento Bank Integration
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @category   Kinento
 * @package    Kinento_Bankintegration
 * @copyright  Copyright (c) 2009-2015 Kinento
 * @license    MIT license
 *
 */


class Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Translater extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render( Varien_Object $row ) {

		// Get the bank's identifier value
		$bankid = $row->getIdentifier();

		// Match this to M2E Pro's Ebay identifiers (stop searching if one is found)
		$orders = Mage::getModel('M2ePro/ebay_order')->getCollection();
		foreach( $orders as $order ) {
			// See if the bank's identifier matches an Ebay identifier
			if ( $bankid == $order->getData('ebay_order_id') ) {
				// Return the normal Magento order ID
				$order_id = $order->getData('order_id');
				$magento_order_id = Mage::getModel('M2ePro/order')->load( $order_id )->getMagentoOrderId();
				return Mage::getModel( 'sales/order' )->load( $magento_order_id )->getIncrementId();
			}
		}

		// Match this to M2E Pro's Amazon identifiers (stop searching if one is found)
		$orders = Mage::getModel('M2ePro/amazon_order')->getCollection();
		foreach( $orders as $order ) {
			// See if the bank's identifier matches an Amazon identifier
			if ( $bankid == $order->getData('amazon_order_id') ) {
				// Return the normal Magento order ID
				$order_id = $order->getData('order_id');
				$magento_order_id = Mage::getModel('M2ePro/order')->load( $order_id )->getMagentoOrderId();
				return Mage::getModel( 'sales/order' )->load( $magento_order_id )->getIncrementId();
			}
		}

		// Could not find a match, return nothing
		return "(not translated)";
	}
}
?>
