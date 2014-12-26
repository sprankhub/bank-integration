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


class Kinento_Bankintegration_BindController extends Mage_Adminhtml_Controller_Action {

	// Loads the main grid
	public function indexAction() {
		$this->loadLayout();
		$this->_setActiveMenu( 'bankintegration/bind' );
		$this->getLayout()->getBlock( 'content' )->append( $this->getLayout()->createBlock( 'bankintegration/bind_main' ) );
		$this->renderLayout();
	}

	// Performs the pre-binding when manually coupling bankdata to orders inside the main grid
	public function manualBindAction() {
		$entryId = $this->getRequest()->getParam( 'id', false );

		// Get bankdata
		$bankmodel = Mage::getModel( 'bankintegration/bankintegration' );
		$bankitems = $bankmodel->getCollection()->addFieldToFilter( 'entry_id', $entryId )->getItems();

		// Get orders
		$orderdata = Mage::getResourceModel( 'sales/order_collection' );
		$orders = $orderdata->addFieldToFilter( 'increment_id', $_POST['bind'] )
			->joinAttribute( 'billing_firstname', 'order_address/firstname', 'billing_address_id' )
			->joinAttribute( 'billing_lastname', 'order_address/lastname', 'billing_address_id' )
			->getItems();
		if (empty($orders)) {
			Mage::getSingleton( 'adminhtml/session' )->addError( Mage::helper( 'bankintegration' )->__( 'Unknown Magento order ID' ) );
		}

		// Process each order
		foreach ( $orders as $order ) {
			foreach ( $bankitems as $bankitem ) {
				$bankmodel->binddata( $order, $bankitem, 'certain' );
				Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Coupled manually.' ) );
			}
		}
		$this->getResponse()->setRedirect( $this->getUrl( '*/*/' ) );
	}

	// Function to decouple or confirm automatic and manually bound bankdata
	public function changeAction() {
		$entryId = $this->getRequest()->getParam( 'id', false );
		$status = $this->getRequest()->getParam( 'status', false );
		$url = $this->getRequest()->getParam( 'url', false );

		try {
			$bankitem = Mage::getModel( 'bankintegration/bankintegration' )->setId( $entryId );
			if ( $status == 'unbound' || $status == 'neglected' ) {
				$bankitem->setBindname( '' );
				$bankitem->setBindamount( NULL );
				$bankitem->setBindorder( ' ' );
			}
			$bankitem->setStatus( $status );
			$bankitem->save();
			Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Status changed.' ) );
			$this->getResponse()->setRedirect( $this->getUrl( '*/*/'.$url ) );
			return;
		} catch ( Exception $e ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
		}
		$this->_redirectReferer();
	}

	// Function to change the status of multiple items
	public function changeselAction() {
		$changes = 0;
		$ids = $this->getRequest()->getParam( 'bankintegration' );
		$status = $this->getRequest()->getParam( 'status', false );
		$bankmodel = Mage::getModel( 'bankintegration/bankintegration' );
		if ( !is_array( $ids ) ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( Mage::helper( 'bankintegration' )->__( 'Please select one or more items' ) );
		} else {
			try {
				foreach ( $ids as $id ) {
					$bankitem = Mage::getModel( 'bankintegration/bankintegration' )->setId( $id );
					$bankitems = $bankmodel->getCollection()->addFieldToFilter( 'entry_id', $id )->getItems();
					$old_status = reset($bankitems)->getStatus();
					if ( $status == 'unbound' && ( $old_status == 'certain' || $old_status == 'guess' ) ) {
						$bankitem->setBindname( '' );
						$bankitem->setBindamount( NULL );
						$bankitem->setBindorder( ' ' );
						$bankitem->setStatus( $status );
						$bankitem->save();
						$changes = $changes + 1;
					}
					elseif ( $status == 'neglected' && $old_status == 'unbound' ) {
						$bankitem->setBindname( '' );
						$bankitem->setBindamount( NULL );
						$bankitem->setBindorder( ' ' );
						$bankitem->setStatus( $status );
						$bankitem->save();
						$changes = $changes + 1;
					}
					elseif ( $status == 'certain' && $old_status == 'guess' ) {
						$bankitem->setStatus( $status );
						$bankitem->save();
						$changes = $changes + 1;
					}
				}
				Mage::getSingleton( 'adminhtml/session' )->addSuccess(
					Mage::helper( 'bankintegration' )->__( 'Status changed' ).' ('.$changes.'x)'
				);
			} catch ( Exception $e ) {
				Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
			}
		}
		$this->_redirectReferer();
	}

	// Performs the actual binding, after pressing the submit button
	public function submitAction() {

		// Get the bankname
		$store = Mage::app()->getStore()->getStoreId();
		$name_of_main_bank = Mage::getStoreConfig( 'bankintegration/banksettings/bank1', $store );

		// Get the setting regarding the status changes for part-payments
		$donotchangestatus = Mage::getStoreConfig( 'bankintegration/statussettings/partpaymentnostatuschange', $store );

		// Also change the payment type
		$changepaymenttype = Mage::getStoreConfig( 'bankintegration/generalsettings/changepaymenttype', $store );
		$newpaymenttype = Mage::getStoreConfig( 'bankintegration/generalsettings/newpaymenttype', $store );

		// Get the email settings from the settingspanel
		$emailsettings = Mage::getStoreConfig( 'bankintegration/emailsettings/emailenable', $store );
		$copy = Mage::getStoreConfig( 'bankintegration/emailsettings/emailcopy', $store );

		// Get the status settings
		$oldstatuses1 = explode( ',', Mage::getStoreConfig( 'bankintegration/statussettings/statusold1', $store ) );
		$oldstatuses2 = explode( ',', Mage::getStoreConfig( 'bankintegration/statussettings/statusold2', $store ) );
		$newstatus1 = Mage::getStoreConfig( 'bankintegration/statussettings/statusnew1', $store );
		$newstatus2 = Mage::getStoreConfig( 'bankintegration/statussettings/statusnew2', $store );
		$statusinvoice = Mage::getStoreConfig( 'bankintegration/statussettings/statusinvoicenew', $store );

		// Get the coupled bankitems (those that are 'certain')
		$bankitems = Mage::getModel( 'bankintegration/bankintegration' )->getCollection()->addFieldToFilter( 'status', 'certain' )->getItems();

		// Iterate over all the orders/bankdata combined
		foreach ( $bankitems as $bankitem ) {
			$orders = Mage::getResourceModel( 'sales/order_collection' )->addFieldToFilter( 'increment_id', $bankitem->getBindorder() )->getItems();
			foreach ( $orders as $order ) {
				$orderstatus = $order->getStatus();
				Mage::log( '[kinento-bankintegration] Updating status of order "'.$order->getIncrementId().'"', null, 'kinento.log', true );


				// Check if the order was already fully paid, but is not anymore (BBVA Bancomer bouncing cheques)
				if ( $name_of_main_bank == 'BBVA Bancomer' && $bankitem->getAmount() < 0 ) {

					// Use the settings reversed
					for ($i = 1; $i <= 2; $i++) {
						if ($i == 1) { $oldstatuses = $oldstatuses1; $newstatus = $newstatus1; }
						if ($i == 2) { $oldstatuses = $oldstatuses2; $newstatus = $newstatus2; }
						foreach ( $oldstatuses as $oldstatus ) {
							if ( $oldstatus != 'disabled' ) {
								if ( $orderstatus == $newstatus ) {

									// Set the old order state
									Mage::log( '[kinento-bankintegration] Changing status to "'.$oldstatus.'" (BBVA Bancomer bouncing cheques)', null, 'kinento.log', true );
									$order->setStatus( $oldstatus );
									$order->save();
									break;
								}
							}
						}
					}
				}

				// Normal trajectory
				else {

					// Process the order according to the settings
					for ($i = 1; $i <= 2; $i++) {
						if ($i == 1) { $oldstatuses = $oldstatuses1; $newstatus = $newstatus1; }
						if ($i == 2) { $oldstatuses = $oldstatuses2; $newstatus = $newstatus2; }
						foreach ( $oldstatuses as $oldstatus ) {
							if ( $oldstatus != 'disabled' ) {
								if ( $orderstatus == $oldstatus ) {
									$ordercomplete = true;

									// Check if the order is now fully paid
									if ( $donotchangestatus == 'enabled' ) {

										// This payment does not complete the payment at once (not required to check now, but saves processing)
										if ( $bankitem->getAmount() < $order->getGrandTotal() ) {

											// But maybe it completes the total payment?
											$totalamount = 0;
											$sameorderpayments = Mage::getModel( 'bankintegration/bankintegration' )->getCollection()->addFieldToFilter( 'bindorder', $order->getIncrementId() )->getItems();
											foreach ( $sameorderpayments as $bankpayment ) {
												$totalamount = $totalamount + $bankpayment->getAmount();
											}
											if ($totalamount < $order->getGrandTotal() ) {
												$ordercomplete = false;
												Mage::log( '[kinento-bankintegration] Not changing status for "'.$order->getIncrementId().'" (not complete)', null, 'kinento.log', true );
											}
										}
									}

									// Only set the new order status, create an invoice, and send emails if the order is fully paid (the option overrides this)
									if ( $ordercomplete == true ) {

										// Change the payment type
										if ( $changepaymenttype == 'enabled' ) {
											$order->setPaymentMethod( $newpaymenttype );
											//$order->getPaymentsCollection()->clear();
											//$order->setPayment( $newpaymenttype );
											$order->getPaymentsCollection()->save();
											//$order->getQuote()->getPayment()->addData(array('method' => $paymentMethod));
											$order->save();
											Mage::log( '[kinento-bankintegration] Changing payment method to "'.$newpaymenttype.'"', null, 'kinento.log', true );
										}

										// Set the new order state
										Mage::log( '[kinento-bankintegration] Changing status to "'.$newstatus.'" (regular)', null, 'kinento.log', true );
										$order->setStatus( $newstatus );
										$order->save();

										// Now also create an invoice
										if ( $order->canInvoice() ) {
											$invoiceId = Mage::getModel( 'sales/order_invoice_api' )->create( $order->getIncrementId(), array(), null, true, false );
											$invoice = Mage::getModel( 'sales/order_invoice' )->loadByIncrementId( $invoiceId );
										}

										// Optionally also change the status of an invoice
										if ( $statusinvoice != 'disabled' ) {
											if ( $order->hasInvoices() ) {
												foreach ($order->getInvoiceCollection() as $invoice ) {
													$invoiceid = $invoice->getIncrementId();
													Mage::log( '[kinento-bankintegration] Changing invoice '.$invoiceid.' status to "'.$statusinvoice.'"', null, 'kinento.log', true );
													$invoice->setState( $statusinvoice );
													$invoice->save();
												}
											}
										}

										// Send out an email
										if ( $emailsettings == 'enabled' ) {

											// Gather all necessary data
											$order_bis = Mage::getModel( 'sales/order' )->loadByIncrementId( $order->getIncrementId() );
											$customer = Mage::getModel( 'customer/customer' )->load( $order->getCustomerId() );
											$data = array(
												"order"            => $order,
												"shippingname"     => $order->getShippingName(),
												"customername"     => $customer->getFirstname().' '.$customer->getLastname(),
												"customeremail"    => $order_bis->getCustomerEmail(),
												"orderid"          => $order->getIncrementId(),
												"orderincrementid" => $order->getIncrementId(),
												"orderdate"        => $order->getCreatedAt(),
												"orderamount"      => money_format( "%n", $order->getGrandTotal() ),
												"invoices"         => $order->getInvoiceCollection()->getItems(),
												"storeid"          => $order->getStoreId(),
												"paymentmethod"    => $order->getPayment()->getMethod(),
											);

											// Send out the original email
											$this->sendEmail( $order, $data, $data["customeremail"] );

											// Send out a copy
											if ( $copy != '' ) {
												$this->sendEmail( $order, $data, $copy );
											}
										}
									}

									// Break out of the settings-loop
									break;
									$i = 999;
								}
							}
						}
					}
				}
			}

			// Bank details are now processed
			$bankitem->setStatus( 'processed' );
			$bankitem->save();
		}

		// Success, return to the view
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Bankdata succesfully submitted.' ) );
		$this->getResponse()->setRedirect( $this->getUrl( '*/*/' ) );
	}

	public function coupleAction() {
		$bankmodel = Mage::getModel( 'bankintegration/bankintegration' );
		$bankmodel->bind();
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Bankdata successfully coupled.' ) );
		$this->getResponse()->setRedirect( $this->getUrl( '*/*/' ) );
	}

	// Mail function
	const XML_PATH_EMAIL_SENDER = 'contacts/email/sender_email_identity';

	public function sendEmail( $order, $data, $emailaddress ) {

		// Set-up the email environment
		$translate = Mage::getSingleton( 'core/translate' );
		$translate->setTranslateInline( false );
		$mail = Mage::getModel( 'core/email_template' );

		// Get the reminder email templates
		$template = Mage::getStoreConfig( 'bankintegration/emailsettings/template', $order->getStoreId() );

		// Send out the email
		$mail->getMail();
		$mail->setDesignConfig( array( 'area' => 'frontend', 'store' => $order->getStoreId() ) );
		$mail->sendTransactional( $template,
			Mage::getStoreConfig( self::XML_PATH_EMAIL_SENDER, $order->getStoreId() ),
			$emailaddress,
			null,
			$data );

		// Finalize
		$translate->setTranslateInline( true );
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Email successfully send to "'.$emailaddress.'".' ) );

	}
}
?>
