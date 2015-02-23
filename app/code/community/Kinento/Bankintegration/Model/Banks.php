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

class Kinento_Bankintegration_Model_Banks {
	protected $_options;

	// Banks as they are selectable by the user and displayed
	// NOTE: To add a new bank, start by adding a new entry below
	public function getBankArray() {
		return array(
			'disabled'          => Mage::helper( 'bankintegration' )->__( 'Disabled' ),
			'ING Bank'          => Mage::helper( 'bankintegration' )->__( 'ING Bank (NL)' ),
			'ING Bank (2)'      => Mage::helper( 'bankintegration' )->__( 'ING Bank alternative (NL)' ),
			'Rabobank'          => Mage::helper( 'bankintegration' )->__( 'Rabobank (NL)' ),
			'ABN Amro'          => Mage::helper( 'bankintegration' )->__( 'ABN Amro (NL)' ),
			'VR-Bank'           => Mage::helper( 'bankintegration' )->__( 'VR-Bank (DE)' ),
			'Postbank'          => Mage::helper( 'bankintegration' )->__( 'Postbank (DE)' ),
			'Postbank (2)'      => Mage::helper( 'bankintegration' )->__( 'Postbank alternative (DE)' ),
			'UniCredit'         => Mage::helper( 'bankintegration' )->__( 'UniCredit (DE)' ),
			'Deutsche Bank'     => Mage::helper( 'bankintegration' )->__( 'Deutsche Bank (DE)' ),
			'Kreissparkasse'    => Mage::helper( 'bankintegration' )->__( 'Kreissparkasse (DE)' ),
			'Outbank'           => Mage::helper( 'bankintegration' )->__( 'Outbank (DE)' ),
			'DAB Munchen'       => Mage::helper( 'bankintegration' )->__( 'DAB Munchen (DE)' ),
			'Bank X'            => Mage::helper( 'bankintegration' )->__( 'Bank X (DE)' ),
			'Hibiscus'          => Mage::helper( 'bankintegration' )->__( 'Hibiscus (DE)' ),
			'Commerzbank'       => Mage::helper( 'bankintegration' )->__( 'Commerzbank (DE)' ),
			'Credit Suisse V11' => Mage::helper( 'bankintegration' )->__( 'Credit Suisse V11 (CH)' ),
			'Swiss Postfinance' => Mage::helper( 'bankintegration' )->__( 'Swiss Postfinance (CH)' ),
			'Erste Bank'        => Mage::helper( 'bankintegration' )->__( 'Erste Bank (AT)' ),
			'Osuuspankki'       => Mage::helper( 'bankintegration' )->__( 'Osuuspankki (FI)' ),
			'Rietumu'           => Mage::helper( 'bankintegration' )->__( 'Rietumu (LV)' ),
			'Multibank'         => Mage::helper( 'bankintegration' )->__( 'Multibank (PL)' ),
			'UniCredit (2)'     => Mage::helper( 'bankintegration' )->__( 'UniCredit (HU)' ),
			'Raiffaisen'        => Mage::helper( 'bankintegration' )->__( 'Raiffaisen (HU)' ),
			'Bank of America'   => Mage::helper( 'bankintegration' )->__( 'Bank of America (US)' ),
			'BBVA Bancomer'     => Mage::helper( 'bankintegration' )->__( 'BBVA Bancomer (MX)' ),
			'Banco Monex'       => Mage::helper( 'bankintegration' )->__( 'Banco Monex (MX)' ),
			'CIMB Bank'         => Mage::helper( 'bankintegration' )->__( 'CIMB Bank (MY)' ),
			'HSBC Bank'         => Mage::helper( 'bankintegration' )->__( 'HSBC Bank (MY)' ),
			'Maybank'           => Mage::helper( 'bankintegration' )->__( 'Maybank (MY)' ),
			'MT94x'             => Mage::helper( 'bankintegration' )->__( 'MT942/MT940' ),
			'ISO 20022'         => Mage::helper( 'bankintegration' )->__( 'ISO 20022' ),
			'camt.054'          => Mage::helper( 'bankintegration' )->__( 'ISO 20022 (camt.054)' ),
		);
	}

	// Bank templates as they are used to parse imported bank files
	// # date
	// # name
	// # account
	// # type
	// # amount
	// # mutation
	// # remarks (including order ID)
	//
	// NOTE: To finish adding a new bank, add a new entry below, specifying the order of data as it
	// appears in the CSV file of the bank (e.g. '8 5 3 4 6 0 9'), the separator used in the CSV-file
	// in between slashes (e.g. '/;/' for a semicolon), whether or not the first line should be
	// skipped (true) or not (false), and which characters to delete (e.g. '"' to delete double-
	// quotes).
	// Optionally also edit Bankintegration.php in case your bank-file is special (e.g. multiple
	// lines that need to be skipped or lines that need to be concatenated).
	//
	public function getBankTemplates() {
		return array(
			array( 'ING Bank',          '0 1 3 5 6 7 8',       '/","/'  , true,  '"' ),
			array( 'ING Bank (2)',      '4 25 24 13 12 14 27', '/;/'    , false, '"' ),
			array( 'Rabobank',          '0 1 7 5 6 3 8',       '/","/'  , true,  '"' ),
			array( 'ABN Amro',          '2 9 8 7 6 11 10',     '/\t/'   , false, ''  ),
			array( 'VR-Bank',           '0 3 4 9 8 7 6',       '/";"/'  , false, '"' ),
			array( 'Postbank',          '0 3 9 9 2 9 1',       '/;/'    , false, ''  ),
			array( 'Postbank (2)',      '0 4 5 2 6 7 3',       '/;/'    , true,  '"' ),
			array( 'UniCredit',         '1 3 0 4 6 7 5',       '/;/'    , true,  ''  ),
			array( 'Deutsche Bank',     '0 3 5 15 14 7 4',     '/;/'    , false, '"' ),
			array( 'Kreissparkasse',    '2 5 6 3 8 7 4',       '/;/'    , true,  '"' ),
			array( 'Outbank',           '3 5 6 7 2 0 8',       '/";"/'  , true,  '"' ),
			array( 'DAB Munchen',       '5 8 0 3 2 4 30',      '/;/'    , true,  '"' ),
			array( 'Bank X',            '0 3 9 9 1 2 4',       '/\t/'   , false, ''  ),
			array( 'Hibiscus',          '8 5 3 4 6 0 9',       '/;/'    , true,  '"' ),
			array( 'Commerzbank',       '0 3 8 2 4 5 3',       '/;/'    , true,  '"' ),
			array( 'Credit Suisse V11', '5 4 1 0 3 10 2',      '/;/'    , false, ''  ),
			array( 'Swiss Postfinance', '0 7 2 4 5 6 1',       '/;/'    , true,  '"' ),
			array( 'Erste Bank',        '1 3 6 0 2 99 4',      '/;/'    , true,  '"' ),
			array( 'Osuuspankki',       '0 5 8 3 2 4 9',       '/;/'    , true,  '"' ),
			array( 'Rietumu',           '0 1 2 3 4 5 6',       '/;/'    , false, ''  ),
			array( 'Multibank',         '0 4 5 2 7 6 3',       '/;/'    , true,  ''  ),
			array( 'UniCredit (2)',     '2 4 0 1 6 5 7',       '/;/'    , true,  '"' ),
			array( 'Raiffaisen',        '1 5 3 0 4 7 6',       '/;/'    , true,  ''  ),
			array( 'Bank of America',   '0 1 1 1 2 1 1',       '/,/'    , false, ''  ),
			array( 'BBVA Bancomer',     '0 1 1 1 2 3 1',       '/\t/'   , true,  ''  ),
			array( 'Banco Monex',       '0 1 6 2 5 3 4',       '/\t/'   , true,  '"' ),
			array( 'CIMB Bank',         '2 12 6 0 7 4 13',     '/,/'    , true,  '"' ),
			array( 'HSBC Bank',         '18 10 9 1 14 12 11',  '/,/'    , true,  '"' ),
			array( 'Maybank',           '4 11 99 0 12 5 9',    '/\^/'   , true,  ''  ),
			array( 'default',           '0 1 2 3 4 5 6',       '/","/'  , false, '"' ),
		);
	}

	// Create an option array
	public function toOptionArray() {
		if ( !$this->_options ) {
			$this->getAllOptions();
		}
		return $this->_options;
	}

	// Get the options (the bank array)
	public function getAllOptions() {
		if ( !$this->_options ) {
			$this->_options = $this->getBankArray();
		}
		return $this->_options;
	}
}

?>
