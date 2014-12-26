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


class Kinento_Bankintegration_UploadController extends Mage_Adminhtml_Controller_Action
{
	// Displays the upload page
	public function indexAction() {
		$this->loadLayout();
		$this->_setActiveMenu( 'bankintegration/upload' );
		$this->getLayout()
		->getBlock( 'content' )->append(
			$this->getLayout()->createBlock( 'bankintegration/upload_edit' )
		);
		$this->renderLayout();
	}

	// Import bankfile 1
	public function import1Action() {
		$this->performImport( 1 );
	}

	// Import bankfile 2
	public function import2Action() {
		$this->performImport( 2 );
	}

	// Import bankfile 3
	public function import3Action() {
		$this->performImport( 3 );
	}

	// Actual import function
	private function performImport( $bankid ) {
		
		// Get the bank details
		$store = Mage::app()->getStore()->getStoreId();
		$bank = Mage::getStoreConfig( 'bankintegration/banksettings/bank'.$bankid, $store );
		Mage::log( '[kinento-bankintegration] Importing for bank '.$bankid, null, 'kinento.log', true );

		// Configure the uploader
		$uploader = null;
		$error = false;
		try {
			$uploader = new Varien_File_Uploader( 'bankfile'.$bankid );
			$uploader->setAllowedExtensions( array( 'txt', 'csv', 'dat', 'DAT', 'TAB', 'exp', 'xls', 'mt940', '940', 'mt942', '942', 'v11', 'xml', 'XML' ) );
		}
		catch ( Exception $ex ) {
			$error = true;
		}

		// Uploading was not done correctly, throw an error
		if ( $error ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( Mage::helper( 'bankintegration' )->__( 'An error occurred while uploading the bankdata.' ) );
			$this->getResponse()->setRedirect( $this->getUrl( '*/*/' ) );
		}

		// Uploading was done correctly, save the file and parse the data
		else {
			$path = Mage::app()->getConfig()->getTempVarDir().'/import/';
			$uploader->save( $path );
			if ( $uploadFile = $uploader->getUploadedFileName() ) {
				Mage::log( '[kinento-bankintegration] File: '.$uploadFile, null, 'kinento.log', true );
				$path .= $uploadFile;
				ini_set('auto_detect_line_endings',true);

				// Process an XML file
				if ( ( $bank == 'ISO 20022' ) || ( $bank == 'camt.054' ) ) {
					$xml = new Varien_Simplexml_Config($path);
					$content = $xml->getNode();
				}

				// Process a regular file
				else {
					$content = file( $path );
				}

				// Parse the data
				$bankmodel = Mage::getModel( 'bankintegration/bankintegration' );
				$bankmodel->parse( $content, $bankid );
				Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Bankdata successfully uploaded.' ) );
			}
			else {
				Mage::getSingleton( 'adminhtml/session' )->addError( Mage::helper( 'bankintegration' )->__( 'An error occurred while parsing the bankdata.' ) );
			}
		}

		// End, go back to the view
		$this->getResponse()->setRedirect( $this->getUrl( '*/*/' ) );
	}

}
?>
