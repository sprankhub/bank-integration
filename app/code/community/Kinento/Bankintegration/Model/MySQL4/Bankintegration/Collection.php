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


class Kinento_Bankintegration_Model_Mysql4_Bankintegration_Collection extends Varien_Data_Collection_Db
{
	protected $_table;

	public function __construct() {
		$resources = Mage::getSingleton( 'core/resource' );
		parent::__construct( $resources->getConnection( 'bankintegration_read' ) );
		$this->_table = $resources->getTableName( 'bankintegration/bankintegration' );

		$this->_select->from(
			array( 'bankintegration'=>$this->_table ),
			array( '*' )
		);
		$this->setItemObjectClass( Mage::getConfig()->getModelClassName( 'bankintegration/bankintegration' ) );
	}

}
?>
