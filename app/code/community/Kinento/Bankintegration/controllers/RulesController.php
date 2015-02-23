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


class Kinento_Bankintegration_RulesController extends Mage_Adminhtml_Controller_Action
{
	// Displays the filter rules main grid
	public function indexAction() {
		$this->loadLayout();
		$this->_setActiveMenu( 'bankintegration/rules' );
		$this->getLayout()
		->getBlock( 'content' )->append(
			$this->getLayout()->createBlock( 'bankintegration/rules_main' )
		);
		$this->renderLayout();
	}

	// Deletes a selected filter rule
	public function deleteAction() {
		$entryId = $this->getRequest()->getParam( 'id', false );

		try {
			Mage::getModel( 'bankrules/bankrules' )->setId( $entryId )->delete();
			Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Filter entry deleted' ) );
			$this->getResponse()->setRedirect( $this->getUrl( '*/*/' ) );
			return;
		} catch ( Exception $e ) {
			Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
		}

		$this->_redirectReferer();
	}

	// Adds a new filter rule
	public function newAction() {
		$this->loadLayout()
		->_addContent( $this->getLayout()->createBlock( 'bankintegration/rules_new' ) );
		$this->_setActiveMenu( 'bankintegration/rules' );
		$this->renderLayout();
	}

	// Allows for the editing of a filter rule
	public function editAction() {
		$this->loadLayout()
		->_addContent( $this->getLayout()->createBlock( 'bankintegration/rules_edit' ) );
		$this->_setActiveMenu( 'bankintegration/rules' );
		$this->renderLayout();
	}

	// Saves an edited filter rule
	public function saveAction() {
		$entryId = $this->getRequest()->getParam( 'id', false );
		if ( $data = $this->getRequest()->getPost() ) {
			$bankrule = Mage::getModel( 'bankrules/bankrules' )->load( $entryId )->addData( $data );
			try {
				$bankrule->setId( $entryId )->save();

				Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Filter entry saved' ) );
				$this->getResponse()->setRedirect( $this->getUrl( '*/*/' ) );
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
			}
		}
		$this->_redirectReferer();
	}

	// Saves a newly created filter rule
	public function postAction() {
		if ( $data = $this->getRequest()->getPost() ) {
			$bankrule = Mage::getModel( 'bankrules/bankrules' )->setData( $data );
			try {
				$bankrule->save();

				Mage::getSingleton( 'adminhtml/session' )->addSuccess( Mage::helper( 'bankintegration' )->__( 'Filter entry saved' ) );
				$this->getResponse()->setRedirect( $this->getUrl( '*/*/' ) );
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton( 'adminhtml/session' )->addError( $e->getMessage() );
			}
		}
		$this->getResponse()->setRedirect( $this->getUrl( '*/*/' ) );
	}

	// Deletes all filter rules
	public function removeAction() {
		$bankrules = Mage::getModel( 'bankrules/bankrules' )->getCollection()->getItems();
		foreach ( $bankrules as $bankrule ) {
			$bankrule->delete();
		}
		$this->_redirectReferer();
	}



}
?>
