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


class Kinento_Bankintegration_Block_Bind_Main_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId( 'bankintegrationGrid' );
		$this->_controller = 'bankintegration';
		$this->setDefaultSort( 'entity_id' );
	}

	protected function _prepareCollection() {
		$model = Mage::getModel( 'bankintegration/bankintegration' );
		$collection = $model->getCollection();
		$collection->addFieldToFilter( 'status', array( 'certain', 'guess', 'unbound' ) );
		$this->setCollection( $collection );
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {
		$minimal = Mage::getStoreConfig( 'bankintegration/generalsettings/minimalview' );
		$extended = Mage::getStoreConfig( 'bankintegration/generalsettings/extendedview' );
		$usem2epro = Mage::getStoreConfig( 'bankintegration/generalsettings/usem2epro' );

		if ( $extended == 'enabled' ) {
			$this->addColumn( 'date', array(
					'header'        => Mage::helper( 'bankintegration' )->__( 'Date' ),
					'align'         => 'right',
					'width'         => '30px',
					'filter_index'  => 'date',
					'index'         => 'date',
					'type'          => 'date',
					'filter'        => false,
				) );
		}

		$this->addColumn( 'name', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Bank name' ),
				'align'         => 'left',
				'width'         => '250px',
				'filter_index'  => 'name',
				'index'         => 'name',
				'type'          => 'text',
				'escape'        => true,
			) );

		$this->addColumn( 'amount', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Bank amount' ),
				'align'         => 'left',
				'width'         => '30px',
				'filter_index'  => 'amount',
				'index'         => 'amount',
				'type'          => 'currency',
				'currency_code' => (string) Mage::getStoreConfig( Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE ),
				'escape'        => true,
			) );

		if ( $extended == 'enabled' ) {

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
					'width'         => '30px',
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
		}

		$this->addColumn( 'identifier', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Bank identifier' ),
				'align'         => 'left',
				'width'         => '80px',
				'filter_index'  => 'identifier',
				'index'         => 'identifier',
				'type'          => 'text',
				'escape'        => true,
			) );

		if ( $usem2epro == 'enabled' ) {
			$this->addColumn( 'translated_identifier', array(
					'header'        => Mage::helper( 'bankintegration' )->__( 'Translated bank identifier' ),
					'align'         => 'left',
					'width'         => '50px',
					'filter_index'  => 'translated_identifier',
					'index'         => 'translated_identifier',
					'type'          => 'text',
					'renderer'      => new Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Translater(),
					'escape'        => true,
				) );
		}

		$this->addColumn( 'status', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Coupling certainty' ),
				'align'         => 'left',
				'width'         => '30px',
				'filter_index'  => 'status',
				'index'         => 'status',
				'type'          => 'options',
				'escape'        => true,
				'options' => array(
						'guess'     => Mage::helper( 'bankintegration' )->__( 'Guess' ),
						'certain'   => Mage::helper( 'bankintegration' )->__( 'Certain' ),
						'unbound'   => Mage::helper( 'bankintegration' )->__( 'Uncoupled' ),
					),
				'renderer'      => new Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Statusdisplay()
			) );

		$this->addColumn( 'neglect', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Change status' ),
				'width'         => '50px',
				'type'          => 'action',
				'getter'        => 'getEntryId',
				'renderer'      => new Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Status(),
				'filter'        => false,
				'sortable'      => false,
			) );

		if ( $minimal == 'disabled' ) {
			$this->addColumn( 'bindname', array(
					'header'        => Mage::helper( 'bankintegration' )->__( 'Order name' ),
					'width'         => '200px',
					'type'          => 'select',
					'index'         => 'type',
					'renderer'      => new Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Bind(),
					'filter'        => false,
					'sortable'      => false,
				) );
		}

		$this->addColumn( 'bindnamemanual', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Manually type an order/invoice ID' ),
				'width'         => '120px',
				'type'          => 'action',
				'index'         => 'type',
				'renderer'      => new Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Bindmanual(),
				'filter'        => false,
				'sortable'      => false,
			) );

		$this->addColumn( 'bindamount', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Order amount' ),
				'align'         => 'left',
				'width'         => '40px',
				'filter_index'  => 'bindamount',
				'index'         => 'bindamount',
				'type'          => 'currency',
				'currency_code' => (string) Mage::getStoreConfig( Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE ),
				'escape'        => true,
			) );

		$this->addColumn( 'bindorder', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Order identifier' ),
				'align'         => 'left',
				'width'         => '50px',
				'filter_index'  => 'bindorder',
				'index'         => 'bindorder',
				'type'          => 'text',
				'renderer'      => new Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Orderlink(),
				'escape'        => true,
			) );
/*
		$this->addColumn( 'manual', array(
				'header'        => Mage::helper( 'bankintegration' )->__( 'Manual input' ),
				'width'         => '200px',
				'type'          => 'input',
				'index'         => 'type',
				'filter'        => false,
				'sortable'      => false,
			) );
*/
		if ( $minimal == 'disabled' ) {
			$this->addColumn( 'paymentstatus', array(
					'header'        => Mage::helper( 'bankintegration' )->__( 'Paid fraction' ),
					'width'         => '50px',
					'type'          => 'action',
					'getter'        => 'getEntryId',
					'renderer'      => new Kinento_Bankintegration_Block_Bind_Main_Widget_Column_Renderer_Paidfraction(),
					'filter'        => false,
					'sortable'      => false,
				) );
		}

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction() {
		$this->setMassactionIdField( 'entity_id' );
		$this->getMassactionBlock()->setFormFieldName( 'bankintegration' );
		$this->getMassactionBlock()->addItem( 'decouplesel', array(
				'label'    => Mage::helper( 'bankintegration' )->__( 'Decouple' ),
				'url'      => $this->getUrl( '*/*/changesel/status/unbound' )
			) );
		$this->getMassactionBlock()->addItem( 'confirmsel', array(
				'label'    => Mage::helper( 'bankintegration' )->__( 'Confirm' ),
				'url'      => $this->getUrl( '*/*/changesel/status/certain' )
			) );
		$this->getMassactionBlock()->addItem( 'ignoresel', array(
				'label'    => Mage::helper( 'bankintegration' )->__( 'Ignore' ),
				'url'      => $this->getUrl( '*/*/changesel/status/neglected' )
			) );
		return $this;
	}

}
?>
