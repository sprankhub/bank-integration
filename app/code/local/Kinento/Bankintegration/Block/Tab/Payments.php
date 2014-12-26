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


class Kinento_Bankintegration_Block_Tab_Payments extends Kinento_Bankintegration_Block_Payments_Main_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface {

	protected $_chat = null;

	public function getTabLabel() {
		return $this->__( 'Bank Payments' );
	}

	public function getTabTitle() {
		return $this->__( 'Bank Payments' );
	}

	public function canShowTab() {
		return true;
	}

	public function isHidden() {
		return false;
	}

	public function getOrder() {
		return Mage::registry( 'current_order' );
	}
}
?>
