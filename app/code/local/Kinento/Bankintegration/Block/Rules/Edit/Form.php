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


class Kinento_Bankintegration_Block_Rules_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm() {

		$form = new Varien_Data_Form( array(
				'id'        => 'edit_form',
				'action'    => $this->getUrl( '*/*/save', array( 'id' => $this->getRequest()->getParam( 'id' ) ) ),
				'method'    => 'post'
			) );

		$fieldset = $form->addFieldset( 'edit_bankrules', array( 'legend' => Mage::helper( 'bankintegration' )->__( 'Edit filter rules' ) ) );

		$fieldset->addField( 'name', 'text', array(
				'name'      => 'name',
				'title'     => Mage::helper( 'bankintegration' )->__( 'Name of the filter' ),
				'label'     => Mage::helper( 'bankintegration' )->__( 'Name of the filter' ),
				'maxlength' => '50',
				'required'  => true,
			) );

		$fieldset->addField( 'field', 'select', array(
				'name'      => 'field',
				'title'     => Mage::helper( 'bankintegration' )->__( 'Field to filter' ),
				'label'     => Mage::helper( 'bankintegration' )->__( 'Field to filter' ),
				'required'  => true,
				'options'   => array(
					'date'    => Mage::helper( 'bankintegration' )->__( 'Date' ),
					'name'    => Mage::helper( 'bankintegration' )->__( 'Name' ),
					'account' => Mage::helper( 'bankintegration' )->__( 'Account' ),
					'type'    => Mage::helper( 'bankintegration' )->__( 'Type' ),
					'amount'  => Mage::helper( 'bankintegration' )->__( 'Amount' ),
					'mutation'=> Mage::helper( 'bankintegration' )->__( 'Mutation' ),
					'remarks' => Mage::helper( 'bankintegration' )->__( 'Remarks' ),
				),
			) );

		$fieldset->addField( 'filter', 'text', array(
				'name'      => 'filter',
				'title'     => Mage::helper( 'bankintegration' )->__( 'Text to filter' ),
				'label'     => Mage::helper( 'bankintegration' )->__( 'Text to filter' ),
				'maxlength' => '50',
				'required'  => true,
			) );

		$fieldset->addField( 'type', 'select', array(
				'name'      => 'type',
				'title'     => Mage::helper( 'bankintegration' )->__( 'Complete or partial string' ),
				'label'     => Mage::helper( 'bankintegration' )->__( 'Complete or partial string' ),
				'required'  => true,
				'options'   => array(
					'1'       => Mage::helper( 'bankintegration' )->__( 'Exact' ),
					'2'       => Mage::helper( 'bankintegration' )->__( 'Partial' ),
				),
			) );


		$form->setUseContainer( true );
		$form->setValues( Mage::registry( 'frozen_bankintegration' )->getData() );
		$this->setForm( $form );
		return parent::_prepareForm();
	}
}
?>
