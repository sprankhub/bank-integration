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


class Kinento_Bankintegration_Block_Upload_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareLayout() {
		return parent::_prepareLayout();
	}

	protected function _prepareForm() {

		// Get the settings
		$store = Mage::app()->getStore()->getStoreId();
		$bank1 = Mage::getStoreConfig( 'bankintegration/banksettings/bank1', $store );
		$bank2 = Mage::getStoreConfig( 'bankintegration/banksettings/bank2', $store );
		$bank3 = Mage::getStoreConfig( 'bankintegration/banksettings/bank3', $store );
		$banks = array( $bank1, $bank2, $bank3 );

		// Upload form
		$form = new Varien_Data_Form();

		// Iterate over the banks
		$count = 1;
		foreach ( $banks as $bank ) {
			if ( $bank != 'disabled' ) {

				// Create the fields
				$fieldset = $form->addFieldset( 'order_number_pattern'.$count, array( 'legend'=>Mage::helper( 'bankintegration' )->__( 'Select a new bankfile to upload' ) ) );
				$field = $fieldset->addField( 'form_key'.$count, 'hidden', array(
						'name'  => 'form_key'.$count,
						'value' => Mage::getSingleton( 'core/session' )->getFormKey(),
				) );
				$field->setRenderer( $this->getLayout()->createBlock( 'bankintegration/upload_edit_renderer'.$count ) );

			}

			// Increment the count
			$count += 1;
		}

		// Upload form
		$this->setForm( $form );
		return parent::_prepareForm();
	}

}
?>
