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


class Kinento_Bankintegration_Block_Upload_Edit_Renderer1 extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

	public function render( Varien_Data_Form_Element_Abstract $element ) {

		// Get the settings
		$store = Mage::app()->getStore()->getStoreId();
		$bank = Mage::getStoreConfig( 'bankintegration/banksettings/bank1', $store );
		$bank_array = Mage::getModel( 'bankintegration/banks' )->getBankArray();
		$bankname = $bank_array[$bank];

		// Create the form
		$html = "";
		$html .= "<form id='form_import_tracking' name='form_import_tracking' action='".$this->getUrl( '*/*/import1' )."' enctype='multipart/form-data' method='post'>";
		$html .= "<input type='file' id='bankfile1' name='bankfile1'></input>";
		$html .= "<input id='form_key' name='form_key' type='hidden' value='".Mage::getSingleton( 'core/session' )->getFormKey()."'></input>";
		$html .= "<br><br><input id='importbutton' type='submit'' value='".Mage::helper( 'bankintegration' )->__( 'Upload and process' )." (".$bankname.")'></input>";
		$html .= "</form>";

		return $html;
	}
}
