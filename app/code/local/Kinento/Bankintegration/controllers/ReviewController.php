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


class Kinento_Bankintegration_ReviewController extends Mage_Adminhtml_Controller_Action
{
	// Shows the grid containing all bankitems
	public function allAction() {
		$this->loadLayout();
		$this->_setActiveMenu( 'bankintegration/review' );
		$this->getLayout()
		->getBlock( 'content' )->append(
			$this->getLayout()->createBlock( 'bankintegration/review_all' )
		);
		$this->renderLayout();
	}

	// Shows the grid containing the processed bankitems
	public function processedAction() {
		$this->loadLayout();
		$this->_setActiveMenu( 'bankintegration/review' );
		$this->getLayout()
		->getBlock( 'content' )->append(
			$this->getLayout()->createBlock( 'bankintegration/review_processed' )
		);
		$this->renderLayout();
	}

	// Shows the grid containing the ignored bankitems
	public function ignoredAction() {
		$this->loadLayout();
		$this->_setActiveMenu( 'bankintegration/review' );
		$this->getLayout()
		->getBlock( 'content' )->append(
			$this->getLayout()->createBlock( 'bankintegration/review_ignored' )
		);
		$this->renderLayout();
	}

	// Add a bankitem manually
	public function newAction() {
		$this->loadLayout();
		$this->_setActiveMenu( 'bankintegration/review' );
		$this->getLayout()
		->getBlock( 'content' )->append(
			$this->getLayout()->createBlock( 'bankintegration/review_edit' )
		);
		$this->renderLayout();
	}

	// Store the newly added manual payment into the database
	public function manualpaymentAction() {
		$entry = array(
			"date"        => $this->getRequest()->getParam( 'date', false ),
			"name"        => $this->getRequest()->getParam( 'name', false ),
			"account"     => $this->getRequest()->getParam( 'account', false ),
			"amount"      => $this->getRequest()->getParam( 'amount', false ),
			"type"        => $this->getRequest()->getParam( 'type', false ),
			"mutation"    => $this->getRequest()->getParam( 'mutation', false ),
			"remarks"     => $this->getRequest()->getParam( 'remarks', false ),
			"identifier"  => $this->getRequest()->getParam( 'identifier', false )
		);

		// Create a new bankitem
		$bankmodel = Mage::getModel( 'bankintegration/bankintegration' );
		$bankmodel->setData( $entry );
		$bankmodel->save();

		// Return to the overview page
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Manual payment added.' ) );
		$this->getResponse()->setRedirect( $this->getUrl( '*/review/all/' ) );
	}

	// Function to accept ignored items
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
			Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Status changed' ) );
			$this->getResponse()->setRedirect( $this->getUrl( '*/*/'.$url ) );
			return;
		} catch ( Exception $e ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
		}

		$this->_redirectReferer();
	}

	// Funtion to delete ALL stored bankitems, including the processed, ignored and uncoupled items
	public function deleteAction() {
		$bankitems = Mage::getModel( 'bankintegration/bankintegration' )->getCollection()->getItems();
		foreach ( $bankitems as $bankitem ) {
			$bankitem->delete();
		}
		$this->_redirectReferer();
	}

	// Function to decouple bankitems from orders, including the status change of an order
	public function decoupleAction() {
		$orderdata = Mage::getResourceModel( 'sales/order_collection' );
		$ordermodel = Mage::getModel( 'sales/order' );

		// Get the status settings
		$store = Mage::app()->getStore()->getStoreId();
		$oldstatuses1 = explode( ',', Mage::getStoreConfig( 'bankintegration/statussettings/statusold1', $store ) );
		$oldstatuses2 = explode( ',', Mage::getStoreConfig( 'bankintegration/statussettings/statusold2', $store ) );
		$newstatus1 = Mage::getStoreConfig( 'bankintegration/statussettings/statusnew1', $store );
		$newstatus2 = Mage::getStoreConfig( 'bankintegration/statussettings/statusnew2', $store );

		$id = $this->getRequest()->getParam( 'id', false );
		$bankitems = Mage::getModel( 'bankintegration/bankintegration' )->getCollection()->addFieldToFilter( 'entry_id', $id )->getItems();
		foreach ( $bankitems as $bankitem ) {
			$orders = $orderdata->addFieldToFilter( 'increment_id', $bankitem->getBindorder() )->getItems();
			foreach ( $orders as $order ) {
				$order = $ordermodel->loadByIncrementId( $order->getIncrementId() );

				// Set order status back to the old value
				foreach ( $oldstatuses1 as $oldstatus ) {
					if ( $oldstatus != 'disabled' ) {
						if ( $order->getStatus() == $newstatus1 ) {
							$order->setStatus( $oldstatus );
						}
					}
				}
				foreach ( $oldstatuses2 as $oldstatus ) {
					if ( $oldstatus != 'disabled' ) {
						if ( $order->getStatus() == $newstatus2 ) {
							$order->setStatus( $oldstatus );
						}
					}
				}
				$order->save();

				// Transaction integration
				/*
				if ( $transactionintegration ) {
					$transactions = Mage::getModel( 'sales/order_payment' )->getCollection()->addFieldToFilter( 'parent_id', $order->getId() )->addFieldToFilter( 'base_amount_paid', $bankitem->getAmount() )->getItems();
					foreach ( $transactions as $transaction ) {
						$transaction->delete();
						Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Transaction for order '.$order->getIncrementId().' and amount '.money_format( "%n", $bankitem->getAmount() ).' removed.' ) );
					}
				}
				*/
			}

			$bankitem->setStatus( 'unbound' );
			$bankitem->setBindname( '' );
			$bankitem->setBindorder( '' );
			$bankitem->setBindamount( NULL );
			try {
				$bankitem->save();
				Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Order uncoupled.' ) );
			} catch ( Exception $e ) {
				Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
			}
		}
		$this->_redirectReferer();
	}

	// Funtion to export the selection of bankitems to a CSV file
	public function exportselAction() {
		$name = "bank_export_" . date( "d_m_y_Hi" );

		$outputfile = new Varien_Io_File();
		$path = Mage::getBaseDir( 'var' ) . DS . 'export' . DS;
		$outputfile->setAllowCreateFolders( true );
		$outputfile->open( array( 'path' => $path ) );
		$filepath = $path . DS . $name . '.csv';
		$outputfile->streamOpen( $filepath, 'w+' );
		$outputfile->streamLock( true );

		$ids = $this->getRequest()->getParam( 'bankintegration' );

		if ( !is_array( $ids ) ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( Mage::helper( 'bankintegration' )->__( 'Please select one or more items' ) );
		} else {
			try {
				foreach ( $ids as $id ) {
					$bankitems = Mage::getModel( 'bankintegration/bankintegration' )->getCollection()->addFieldToFilter( 'entry_id', $id )->getItems();
					foreach ( $bankitems as $bankitem ) {
						$outputfile->streamWrite( "\"" );
						$outputfile->streamWrite( trim( $bankitem->getDate() ) );
						$outputfile->streamWrite( "\";\"" );
						$outputfile->streamWrite( trim( $bankitem->getName() ) );
						$outputfile->streamWrite( "\";\"" );
						$outputfile->streamWrite( trim( $bankitem->getAccount() ) );
						$outputfile->streamWrite( "\";\"" );
						$outputfile->streamWrite( trim( $bankitem->getType() ) );
						$outputfile->streamWrite( "\";\"" );
						$outputfile->streamWrite( trim( $bankitem->getAmount() ) );
						$outputfile->streamWrite( "\";\"" );
						$outputfile->streamWrite( trim( $bankitem->getRemarks() ) );
						$outputfile->streamWrite( "\";\"" );
						$outputfile->streamWrite( trim( $bankitem->getStatus() ) );
						$outputfile->streamWrite( "\";\"" );
						$outputfile->streamWrite( trim( $bankitem->getBindorder() ) );
						$outputfile->streamWrite( "\";\"" );
						$outputfile->streamWrite( trim( $bankitem->getBindname() ) );
						$outputfile->streamWrite( "\";\"" );
						$outputfile->streamWrite( trim( $bankitem->getBindamount() ) );
						$outputfile->streamWrite( "\"\n" );

					}
				}
				Mage::getSingleton( 'adminhtml/session' )->addSuccess(
					Mage::helper( 'bankintegration' )->__( 'Total of %d item(s) were successfully exported', count( $ids ) )
				);
			} catch ( Exception $e ) {
				Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
			}
		}

		$outputfile->streamUnlock();
		$outputfile->streamClose();

		$this->_redirectReferer();
	}

	// Funtion to export all bankitems to a CSV file
	public function exportallAction() {
		$name = "bank_export_" . date( "d_m_y_Hi" );

		$outputfile = new Varien_Io_File();
		$path = Mage::getBaseDir( 'var' ) . DS . 'export' . DS;
		$outputfile->setAllowCreateFolders( true );
		$outputfile->open( array( 'path' => $path ) );
		$filepath = $path . DS . $name . '.csv';
		$outputfile->streamOpen( $filepath, 'w+' );
		$outputfile->streamLock( true );

		$count = 0;
		$bankitems = Mage::getModel( 'bankintegration/bankintegration' )->getCollection()->getItems();
		foreach ( $bankitems as $bankitem ) {
			$outputfile->streamWrite( "\"" );
			$outputfile->streamWrite( trim( $bankitem->getDate() ) );
			$outputfile->streamWrite( "\";\"" );
			$outputfile->streamWrite( trim( $bankitem->getName() ) );
			$outputfile->streamWrite( "\";\"" );
			$outputfile->streamWrite( trim( $bankitem->getAccount() ) );
			$outputfile->streamWrite( "\";\"" );
			$outputfile->streamWrite( trim( $bankitem->getType() ) );
			$outputfile->streamWrite( "\";\"" );
			$outputfile->streamWrite( trim( $bankitem->getAmount() ) );
			$outputfile->streamWrite( "\";\"" );
			$outputfile->streamWrite( trim( $bankitem->getRemarks() ) );
			$outputfile->streamWrite( "\";\"" );
			$outputfile->streamWrite( trim( $bankitem->getStatus() ) );
			$outputfile->streamWrite( "\";\"" );
			$outputfile->streamWrite( trim( $bankitem->getBindorder() ) );
			$outputfile->streamWrite( "\";\"" );
			$outputfile->streamWrite( trim( $bankitem->getBindname() ) );
			$outputfile->streamWrite( "\";\"" );
			$outputfile->streamWrite( trim( $bankitem->getBindamount() ) );
			$outputfile->streamWrite( "\"\n" );

			$count = $count + 1;
		}

		$outputfile->streamUnlock();
		$outputfile->streamClose();

		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Total of %d item(s) were successfully exported', $count ) );

		$this->_redirectReferer();
	}

	// Funtion to export all bankitems for a certain set time to a CSV file
	public function exporttimeAction() {
		$name = "bank_export_" . date( "d_m_y_Hi" );

		$outputfile = new Varien_Io_File();
		$path = Mage::getBaseDir( 'var' ) . DS . 'export' . DS;
		$outputfile->setAllowCreateFolders( true );
		$outputfile->open( array( 'path' => $path ) );
		$filepath = $path . DS . $name . '.csv';
		$outputfile->streamOpen( $filepath, 'w+' );
		$outputfile->streamLock( true );

		$count = 0;
		$bankitems = Mage::getModel( 'bankintegration/bankintegration' )->getCollection()->getItems();
		foreach ( $bankitems as $bankitem ) {
			$fromdate = Mage::getStoreConfig( 'bankintegration/exportsettings/fromdate' );
			$todate = Mage::getStoreConfig( 'bankintegration/exportsettings/todate' );
			$date = $bankitem->getDate();

			if ( $date >= $fromdate && $date <= $todate ) {
				$outputfile->streamWrite( "\"" );
				$outputfile->streamWrite( trim( $date ) );
				$outputfile->streamWrite( "\";\"" );
				$outputfile->streamWrite( trim( $bankitem->getName() ) );
				$outputfile->streamWrite( "\";\"" );
				$outputfile->streamWrite( trim( $bankitem->getAccount() ) );
				$outputfile->streamWrite( "\";\"" );
				$outputfile->streamWrite( trim( $bankitem->getType() ) );
				$outputfile->streamWrite( "\";\"" );
				$outputfile->streamWrite( trim( $bankitem->getAmount() ) );
				$outputfile->streamWrite( "\";\"" );
				$outputfile->streamWrite( trim( $bankitem->getRemarks() ) );
				$outputfile->streamWrite( "\";\"" );
				$outputfile->streamWrite( trim( $bankitem->getStatus() ) );
				$outputfile->streamWrite( "\";\"" );
				$outputfile->streamWrite( trim( $bankitem->getBindorder() ) );
				$outputfile->streamWrite( "\";\"" );
				$outputfile->streamWrite( trim( $bankitem->getBindname() ) );
				$outputfile->streamWrite( "\";\"" );
				$outputfile->streamWrite( trim( $bankitem->getBindamount() ) );
				$outputfile->streamWrite( "\"\n" );

				$count = $count + 1;
			}
		}

		$outputfile->streamUnlock();
		$outputfile->streamClose();
		Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Total of %d item(s) were successfully exported.', $count ) );

		$this->_redirectReferer();
	}

	// Funtion to delete a selection of bankitems permanently
	public function deleteselAction() {
		$ids = $this->getRequest()->getParam( 'bankintegration' );
		if ( !is_array( $ids ) ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( Mage::helper( 'bankintegration' )->__( 'Please select one or more items' ) );
		} else {
			try {
				foreach ( $ids as $id ) {
					$bankitems = Mage::getModel( 'bankintegration/bankintegration' )->getCollection()->addFieldToFilter( 'entry_id', $id )->getItems();
					foreach ( $bankitems as $bankitem ) {
						$bankitem->delete();
					}
				}
				Mage::getSingleton( 'adminhtml/session' )->addSuccess(
					Mage::helper( 'bankintegration' )->__( 'Total of %d item(s) deleted', count( $ids ) )
				);
			} catch ( Exception $e ) {
				Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
			}
		}
		$this->_redirectReferer();
	}
}
?>
