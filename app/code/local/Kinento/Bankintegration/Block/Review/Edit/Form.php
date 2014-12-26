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


class Kinento_Bankintegration_Block_Review_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareLayout() {


		return parent::_prepareLayout();
	}

	protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$fieldset = $form->addFieldset( 'manual', array( 'legend'=>Mage::helper( 'bankintegration' )->__( 'Manual payment details:' ) ) );

		// Date field
		$field = $fieldset->addField( 'date', 'date', array(
				'name'  => 'date',
				'label' => Mage::helper( 'bankintegration' )->__( 'Date (as YYYY-MM-DD)' ),
				'title' => Mage::helper( 'bankintegration' )->__( 'Date' ),
				'image' => $this->getSkinUrl('images/grid-cal.gif'),
				'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
				'format' => '%Y-%m-%d',
				'required' => false,
			)
		);

		// Name field
		$field = $fieldset->addField( 'name', 'text', array(
				'name'  => 'name',
				'label' => Mage::helper( 'bankintegration' )->__( 'Name' ),
				'title' => Mage::helper( 'bankintegration' )->__( 'Name' ),
				'required' => false,
			)
		);

		// Account field
		$field = $fieldset->addField( 'account', 'text', array(
				'name'  => 'account',
				'label' => Mage::helper( 'bankintegration' )->__( 'Account' ),
				'title' => Mage::helper( 'bankintegration' )->__( 'Account' ),
				'required' => false,
			)
		);

		// Amount field
		$field = $fieldset->addField( 'amount', 'text', array(
				'name'  => 'amount',
				'label' => Mage::helper( 'bankintegration' )->__( 'Amount (cents behind comma)' ),
				'title' => Mage::helper( 'bankintegration' )->__( 'Amount' ),
				'required' => false,
			)
		);

		// Type field
		$field = $fieldset->addField( 'type', 'text', array(
				'name'  => 'type',
				'label' => Mage::helper( 'bankintegration' )->__( 'Type' ),
				'title' => Mage::helper( 'bankintegration' )->__( 'Type' ),
				'required' => false,
			)
		);

		// Mutation field
		$field = $fieldset->addField( 'mutation', 'text', array(
				'name'  => 'mutation',
				'label' => Mage::helper( 'bankintegration' )->__( 'Mutation' ),
				'title' => Mage::helper( 'bankintegration' )->__( 'Mutation' ),
				'required' => false,
			)
		);

		// Remarks field
		$field = $fieldset->addField( 'remarks', 'text', array(
				'name'  => 'remarks',
				'label' => Mage::helper( 'bankintegration' )->__( 'Remarks' ),
				'title' => Mage::helper( 'bankintegration' )->__( 'Remarks' ),
				'required' => false,
			)
		);

		// Get the status settings
		$store = Mage::app()->getStore()->getStoreId();
		$oldstatuses1 = explode( ',', Mage::getStoreConfig( 'bankintegration/statussettings/statusold1', $store ) );
		$oldstatuses2 = explode( ',', Mage::getStoreConfig( 'bankintegration/statussettings/statusold2', $store ) );

		// Get an option list of order/invoice IDs
		$tempoptions = array();
		$baseonid = Mage::getStoreConfig( 'bankintegration/generalsettings/baseoninvoices', $store );
		foreach ( array_merge($oldstatuses1, $oldstatuses2) as $oldstatus ) {
			if ($oldstatus != 'disabled') {
				$date = Mage::getStoreConfig( 'bankintegration/displaysettings/fromdate' );
				$orders = Mage::getResourceModel( 'sales/order_grid_collection' )
					->addFieldToFilter( 'main_table.created_at', array( 'gt'=>$date ) )
					->addAttributeToFilter( 'status', $oldstatus )
					->getItems();
				if ( !empty( $orders ) ) {
					foreach ( $orders as $order ) {

						// Based on invoices
						if ( $baseonid == 'invoiceid' || $baseonid == 'orderandinvoiceid' ) {
							if ( $order->hasInvoices() ) {
								foreach ($order->getInvoiceCollection() as $invoice ) {
									$id = $invoice->getIncrementId();
									array_push( $tempoptions, array( 'value' => $id, 'label' => $id ) );
								}
							}
						}
						
						// Based on orders
						if ( $baseonid == 'orderid' || $baseonid == 'orderandinvoiceid' ) {
							$id = $order->getIncrementId();
							array_push( $tempoptions, array( 'value' => $id, 'label' => $id ) );
						}
					}
				}
			}
		}

		// Add identifiers by selection or by manual input 
		$manualidentifierselection = Mage::getStoreConfig( 'bankintegration/generalsettings/manualidentifierselection' );
		if ( $manualidentifierselection == 'enabled' ) {

			// Identifier field
			$field = $fieldset->addField( 'identifier', 'select', array(
					'name'      => 'identifier',
					'label'     => Mage::helper( 'bankintegration' )->__( 'Identifier (Order/Invoice ID)' ),
					'title'     => Mage::helper( 'bankintegration' )->__( 'Identifier' ),
					'required'  => false,
					'values'    => $tempoptions,
				)
			);
		}

		// Manual input
		else {

			// Identifier field (manual)
			$field = $fieldset->addField( 'identifier', 'text', array(
					'name'      => 'identifier',
					'label'     => Mage::helper( 'bankintegration' )->__( 'Identifier (Order/Invoice ID)' ),
					'title'     => Mage::helper( 'bankintegration' )->__( 'Identifier' ),
					'required'  => false,
				)
			);
		}

		// Complete the form
		$form->setAction( $this->getUrl( '*/review/manualpayment' ) );
		$form->setMethod( 'post' );
		$form->setUseContainer( true );
		$form->setId( 'edit_form' );
		$this->setForm( $form );
		return parent::_prepareForm();
	}


}
?>
