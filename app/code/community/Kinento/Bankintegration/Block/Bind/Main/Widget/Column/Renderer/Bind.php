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


class Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Bind extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function __construct() {
		$this->_storedoptions = 'empty';
		$this->_setting = Mage::getStoreConfig( 'bankintegration/generalsettings/baseoninvoices' );
	}

	public function render( Varien_Object $row ) {
		if ( $row->getStatus() == 'unbound' ) {
			$form = '<form action="'.$this->getUrl( '*/*/manualBind/id/'.$row->getId().'' ).'" method="post"><select name="bind" onchange="javascript:this.form.submit()">'.$this->getOptions().'</select><input name="form_key" type="hidden" value="'.Mage::getSingleton( 'core/session' )->getFormKey().'"/></input></form>';
		}
		else {
			$form = $row->getBindname();
		}
		return $form;
	}

	public function getOptions() {

		// Get the status settings
		$store = Mage::app()->getStore()->getStoreId();
		$oldstatuses1 = explode( ',', Mage::getStoreConfig( 'bankintegration/statussettings/statusold1', $store ) );
		$oldstatuses2 = explode( ',', Mage::getStoreConfig( 'bankintegration/statussettings/statusold2', $store ) );

		$baseonid = Mage::getStoreConfig( 'bankintegration/generalsettings/baseoninvoices', $store );
		if ( $this->_storedoptions == 'empty' || $baseonid != $this->_setting ) {
			$tempoptions = array();
			$options = '<option value="Unbound">';
			if ( $baseonid == 'invoiceid' ) { $options .= Mage::helper( 'bankintegration' )->__( 'Select an invoice' ); }
			else if ( $baseonid == 'orderid' ) { $options .= Mage::helper( 'bankintegration' )->__( 'Select an order' ); }
			else { $options .= Mage::helper( 'bankintegration' )->__( 'Manually select an order/invoice' ); }
			$options .= '</option>';
			foreach ( array_merge($oldstatuses1, $oldstatuses2) as $oldstatus ) {
				if ($oldstatus != 'disabled') {
					$date = Mage::getStoreConfig( 'bankintegration/displaysettings/fromdate', $store );
					$orders = Mage::getResourceModel( 'sales/order_grid_collection' )
						->addFieldToFilter( 'main_table.created_at', array( 'gt'=>$date ) )
						->addAttributeToFilter( 'main_table.status', $oldstatus )
						->getItems();
					if ( !empty( $orders ) ) {
						foreach ( $orders as $order ) {

							// Get customer data
							$customer = Mage::getModel( 'customer/customer' )->load( $order->getCustomerId() );
							$ordername1 = $customer->getFirstname().' '.$customer->getLastname();
							//$ordername2 = $order->getShippingName();

							// Based on invoices
							if ( $baseonid == 'invoiceid' || $baseonid == 'orderandinvoiceid' ) {
								if ( $order->hasInvoices() ) {
									foreach ($order->getInvoiceCollection() as $invoice ) {
										$orderamount = money_format( "%n", $invoice->getGrandTotal() );
										$orderincrementid = $invoice->getIncrementId();
										$tempoptions[] .= '<option value="'.$orderincrementid.'">'.$orderincrementid.' || '.$ordername1.' || '.$orderamount.'</option>';
									}
								}
							}

							// Based on orders
							if ( $baseonid == 'orderid' || $baseonid == 'orderandinvoiceid' ) {
								$orderamount = money_format( "%n", $order->getGrandTotal() );
								$orderincrementid = $order->getIncrementId();
								$tempoptions[] .= '<option value="'.$orderincrementid.'">'.$orderincrementid.' || '.$ordername1.' || '.$orderamount.'</option>';
							}
						}
					}
				}
			}
			sort( $tempoptions );
			$options .= implode( " ", $tempoptions );
			$this->_storedoptions = $options;
		}
		return $this->_storedoptions;
	}
}

?>
