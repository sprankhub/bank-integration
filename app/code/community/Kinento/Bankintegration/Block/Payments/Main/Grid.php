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


class Kinento_Bankintegration_Block_Payments_Main_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();

		$this->setId( 'bankintegrationGrid' );
		$this->_controller = 'bankintegration';
		$this->setDefaultSort( 'entity_id' );
	}

	protected function _prepareCollection() {
		$model = Mage::getModel( 'bankintegration/bankintegration' );
		$order = $this->getOrder();
		$collection = $model->getCollection();
		$collection->addFieldToFilter( 'bindorder', $order->getRealOrderId() );
		$this->setCollection( $collection );

		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn( 'date', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Date' ),
				'align'         => 'right',
				'width'         => '50px',
				'filter_index'  => 'date',
				'index'         => 'date',
				'type'          => 'date',
			) );

		$this->addColumn( 'name', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Name' ),
				'align'         => 'left',
				'width'         => '80px',
				'filter_index'  => 'name',
				'index'         => 'name',
				'type'          => 'text',
				'escape'        => true,
			) );

		$this->addColumn( 'account', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Account' ),
				'align'         => 'left',
				'width'         => '50px',
				'filter_index'  => 'account',
				'index'         => 'account',
				'type'          => 'text',
				'escape'        => true,
			) );

		$this->addColumn( 'amount', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Amount' ),
				'align'         => 'left',
				'width'         => '50px',
				'filter_index'  => 'amount',
				'index'         => 'amount',
				'type'          => 'currency',
				'currency_code' => (string) Mage::getStoreConfig( Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE ),
				'escape'        => true,
			) );

		$this->addColumn( 'type', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Type (1)' ),
				'align'         => 'left',
				'width'         => '30px',
				'filter_index'  => 'type',
				'index'         => 'type',
				'type'          => 'text',
				'escape'        => true,
			) );

		$this->addColumn( 'mutation', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Type (2)' ),
				'align'         => 'left',
				'width'         => '50px',
				'filter_index'  => 'mutation',
				'index'         => 'mutation',
				'type'          => 'text',
				'escape'        => true,
			) );

		$this->addColumn( 'remarks', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Remarks' ),
				'align'         => 'left',
				'width'         => '150px',
				'filter_index'  => 'remarks',
				'index'         => 'remarks',
				'type'          => 'text',
				'escape'        => true,
			) );
		$this->addColumn( 'bindorder', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Coupled to order' ),
				'align'         => 'left',
				'width'         => '50px',
				'filter_index'  => 'bindorder',
				'index'         => 'bindorder',
				'type'          => 'text',
				'escape'        => true,
				'renderer'      => new Kinento_Bankintegration_Block_Review_Processed_Widget_Column_Renderer_Decouple()
			) );


		return parent::_prepareColumns();
	}

}
?>
