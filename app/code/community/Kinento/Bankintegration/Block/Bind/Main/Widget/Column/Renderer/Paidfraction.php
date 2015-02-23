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


class Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Paidfraction extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render( Varien_Object $row ) {
		$value = '';

		// Add additional information regarding the state of the payment
		if ( $row->getStatus() == 'certain' ) {

			// Get the orderdata
			$orderdata = Mage::getResourceModel( 'sales/order_collection' );
			$orders = $orderdata->addFieldToFilter( 'increment_id', $row->getBindorder() )->getItems();
			$order = reset( $orders );

			// Compute the total payment so far
			$totalamount = 0;
			$sameorderpayments = Mage::getModel( 'bankintegration/bankintegration' )->getCollection()->addFieldToFilter( 'bindorder', $order->getIncrementId() )->getItems();
			foreach ( $sameorderpayments as $bankpayment ) {
				$totalamount = $totalamount + $bankpayment->getAmount();
			}

			// Compute the paid fraction
			$percentage = sprintf( '%.0f' , 100*$totalamount/($order->getGrandTotal()*1.0 ) );
			if ( $percentage < 100 ) {
				$color = 'red';
			}
			elseif ( $percentage == 100 ) {
				$color = 'green';
			}
			else {
				$color = 'orange';
			}
			$value .= '<p style="color:'.$color.'">'.$percentage.'%</color>';
		}

		return $value;
	}
}
?>
