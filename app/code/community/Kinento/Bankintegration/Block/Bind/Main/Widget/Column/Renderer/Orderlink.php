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


class Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Orderlink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render( Varien_Object $row ) {
		if ( $row->getStatus() != 'unbound' ) {
			$orderdata = Mage::getResourceModel( 'sales/order_collection' );
			$orders = $orderdata->addFieldToFilter( 'increment_id', $row->getBindorder() )->getItems();
			foreach ( $orders as $order ) {
				$id = $order->getId();
			}
			if ( !( $ordernumber = $row->getBindorder() ) )
				$ordernumber = '000000000';
			$form = '<a href="'.$this->getUrl( 'adminhtml/sales_order/view/order_id/'.$id.'/' ).'">'.$ordernumber.'</a>';
		}
		else {
			$form = '';
		}
		return $form;
	}
}

?>
