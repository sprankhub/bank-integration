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


class Kinento_Bankintegration_Model_Statuses {
	protected $_options;

	public function toOptionArray() {
		if ( !$this->_options ) {
			$this->getAllOptions();
		}
		return $this->_options;
	}

	public function getAllOptions() {
		if ( !$this->_options ) {
			$this->_options = array();
			$this->_options[] = array( 'value' => 'disabled' , 'label' => '<< disable this option >>' );
			$this->_options[] = array( 'value' => 'canceled' , 'label' => 'Canceled' );
			$this->_options[] = array( 'value' => 'closed' , 'label' => 'Closed' );
			$this->_options[] = array( 'value' => 'complete' , 'label' => 'Complete' );
			$this->_options[] = array( 'value' => 'fraud' , 'label' => 'Suspected Fraud' );
			$this->_options[] = array( 'value' => 'holded' , 'label' => 'On Hold' );
			$this->_options[] = array( 'value' => 'payment_review' , 'label' => 'Payment Review' );
			$this->_options[] = array( 'value' => 'pending' , 'label' => 'Pending' );
			$this->_options[] = array( 'value' => 'pending_payment' , 'label' => 'Pending Payment' );
			$this->_options[] = array( 'value' => 'pending_paypal' , 'label' => 'Pending PayPal' );
			$this->_options[] = array( 'value' => 'processing' , 'label' => 'Processing' );
		}
		return $this->_options;
	}
	
}

?>
