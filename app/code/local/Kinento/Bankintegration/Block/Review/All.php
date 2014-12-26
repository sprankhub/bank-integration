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


class Kinento_Bankintegration_Block_Review_All extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct() {
		parent::__construct();
		$this->_blockGroup = 'bankintegration';
		$this->_controller = 'review_all';
		$this->_headerText = Mage::helper( 'bankintegration' )->__( 'Review bankdata (all items)' );
		$this->_removeButton( 'add' );
		$this->_addButton(
			'manualpayment',
			array(
				'label'     => Mage::helper( 'bankintegration' )->__( 'Add manual payment' ),
				'onclick'   => 'setLocation(\'' . $this->getUrl( '*/review/new' ) . '\')',
			)
		);
		$this->_addButton(
			'exportall',
			array(
				'label'     => Mage::helper( 'bankintegration' )->__( 'Export all entries' ),
				'onclick'   => 'setLocation(\'' . $this->getUrl( '*/*/exportall' ) . '\')',
			)
		);
		$this->_addButton(
			'exporttime',
			array(
				'label'     => Mage::helper( 'bankintegration' )->__( 'Export entries (time interval)' ),
				'onclick'   => 'setLocation(\'' . $this->getUrl( '*/*/exporttime' ) . '\')',
			)
		);
		$this->_addButton(
			'delete',
			array(
				'label'     => Mage::helper( 'bankintegration' )->__( 'Remove _ALL_ stored bankdata' ),
				'onclick'   => 'deleteConfirm(\''.Mage::helper( 'bankintegration' )->__( 'This will remove _ALL_ bankdata. This includes processed, unproccessed and ignored data (orders will remain unaffected). Are you sure you want to continue?' ).'\', \'' . $this->getUrl( '*/review/delete' ) . '\')',
			)
		);
	}
}
?>
