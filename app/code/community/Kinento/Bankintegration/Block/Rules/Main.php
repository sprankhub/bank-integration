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


class Kinento_Bankintegration_Block_Rules_Main extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct() {
		parent::__construct();
		$this->_blockGroup = 'bankintegration';
		$this->_controller = 'rules_main';
		$this->_headerText = Mage::helper( 'bankintegration' )->__( 'Bankdata filter rules' );
		$this->_addButton(
			'delete',
			array(
				'label'     => Mage::helper( 'bankintegration' )->__( 'Clear all filterdata' ),
				'onclick'   => 'setLocation(\'' . $this->getUrl( '*/rules/remove' ) . '\')',
			)
		);

	}
}
?>
