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


class Kinento_Bankintegration_Block_Rules_Main_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct() {
		parent::__construct();
		$this->setId( 'bankintegrationGrid' );
		$this->_controller = 'bankintegration';
	}

	protected function _prepareCollection() {
		$model = Mage::getModel( 'bankrules/bankrules' );
		$collection = $model->getCollection();
		$this->setCollection( $collection );
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {
		$this->addColumn( 'entry_id', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'ID' ),
				'align'         => 'right',
				'width'         => '20px',
				'filter_index'  => 'entry_id',
				'index'         => 'entry_id',
			) );

		$this->addColumn( 'name', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Name' ),
				'align'         => 'center',
				'width'         => '150px',
				'filter_index'  => 'name',
				'index'         => 'name',
			) );

		$this->addColumn( 'field', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Filter field' ),
				'align'         => 'center',
				'width'         => '150px',
				'filter_index'  => 'field',
				'index'         => 'field',
			) );

		$this->addColumn( 'filter', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Filter string' ),
				'align'         => 'center',
				'width'         => '150px',
				'filter_index'  => 'filter',
				'index'         => 'filter',
			) );

		$this->addColumn( 'type', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Filter type' ),
				'align'         => 'center',
				'width'         => '100px',
				'filter_index'  => 'type',
				'index'         => 'type',
			) );

		return parent::_prepareColumns();
	}

	public function getRowUrl( $row ) {
		return $this->getUrl( '*/*/edit', array(
				'id' => $row->getEntryId(),
			) );
	}


}
?>
