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

class Kinento_Bankintegration_Model_Bankintegration extends Mage_Core_Model_Abstract {

	// Initialize the class
	protected function _construct() {
		$this->_init( 'bankintegration/bankintegration' );
	}

	// Function to transform the ISO20022 format to the default bank format
	public function ISO20022_to_default( $setting, $data ) {
		$newdata = array();

		// Process each statement separately
		$statements = $data->BkToCstmrStmt->Stmt->Ntry;
		foreach ( $statements as $statement ) {
				$transactions = $statement->NtryDtls->TxDtls;
				foreach ( $transactions as $transaction ) {

				// Collect the data for this transaction
				$entry = $this->process_statement( $statement, $transaction );

				// Add the data to the array
				$data = '"'.implode('","',$entry).'"';
				array_push($newdata, $data);
			}
		}
		return $newdata;
	}

	// Function to transform the ISO20022 (camt.054) format to the default bank format
	public function camt054_to_default( $setting, $data ) {
		$newdata = array();

		// Process each transaction separately
		$groups = $data->BkToCstmrDbtCdtNtfctn->Ntfctn;
		foreach ( $groups as $group ) {
			$statements = $group->Ntry;
			foreach ( $statements as $statement ) {
				$transactions = $statement->NtryDtls->TxDtls;
				foreach ( $transactions as $transaction ) {

					// Collect the data for this transaction
					$entry = $this->process_statement( $statement, $transaction );

					// Add the data to the array
					$data = '"'.implode('","',$entry).'"';
					array_push($newdata, $data);
				}
			}
		}
		return $newdata;
	}

	// Process an ISO20022 statement
	public function process_statement( $statement, $transaction ) {
		#echo "<pre>"; print_r($statement); echo "</pre>";

		// Get the date
		$date        = $statement->BookgDt->Dt;
		if (!$date) {
			$date      = reset( explode( 'T' , $statement->BookgDt->DtTm ) );
		}

		// Get the amount
		$amount      = $transaction->AmtDtls->TxAmt->Amt;
		if (!$amount) {
			$amount    = $statement->Amt;
		}

		// Get the type
		$type1       = $statement->CdtDbtInd;
		$type2       = $statement->Sts;

		// Get the transfer's details
		$name        = $transaction->RltdPties->Dbtr->Nm;
		$account     = $transaction->RltdPties->DbtrAcct->Id->IBAN;
		$remarks     = $transaction->RmtInf->Strd->CdtrRefInf->Ref;
		if (!$remarks) {
			$remarks = $transaction->Refs->EndToEndId;
		}

		// Collect everything and return
		$entry = array(
			$date, # date (0)
			$name, # name (1)
			$account, # account (2)
			$type1, # type (3)
			$amount, # amount (4)
			$type2, # mutation (5)
			$remarks, # remarks (6)
		);
		#echo "<pre>"; print_r($entry); echo "</pre>";
		#die(" END OF ENTRY ");
		return $entry;
	}

	// Function to transform the MT94x format to the default bank format
	public function MT94x_to_default( $setting, $data ) {

		// Pre-process the data line by line
		$newdata = array();
		$newrow = "";
		foreach ( $data as $row ) {
			$row = iconv( "ISO-8859-1", "UTF-8", $row );

			// Skip lines starting with a '{1' or a '-}' or a '-X'
			if ( ( $row[0] == '{' && $row[1] == '1' ) || ( $row[0] == '-' && $row[1] == '}' ) || ( $row[0] == '-' && $row[1] == 'X' ) ) {
				if ( $newrow != "" ) {
					array_push($newdata, $newrow);
				}
				$newrow = $row;
			}
			else {

				// Found an identifier
				if ( $row[0] == ":" ) {
					array_push($newdata, $newrow);
					$newrow = $row;
				}
				else {
					$newrow .= "\n".$row;
				}
			}
		}

		// Add the last entry
		if ( $newrow != "" ) {
			array_push($newdata, $newrow);
		}

		// Output and temp variables
		$output = array();
		$message = array(
			array(), # date 0
			array(), # name 1
			"", # account 2
			"", # type 3
			array(), # amount 4
			array(), # mutation 5
			array() # remarks 6
		);

		// Process the data line by line
		$row_id = 0;
		foreach ( $newdata as $row ) {
			$row_id = $row_id + 1;

			// Finalize this message, continue to the next one
			$c1 = ( $row[0] == "-" );
			$c2 = ( $row[0] == ":" && intval( substr( $row, 1, 2 ) ) == 20 && $row_id > 2);
			$c3 = ( $row_id == count( $newdata ) );
			if ( $c1 || $c2 || $c3 ) {

				// Iterate over all messages
				for ( $i = 0; $i < count($message[6]); $i++ ) {
					$result = '"';
					$result .= $message[0][$i].'","';
					$result .= $message[1][$i].'","';
					$result .= $message[2]    .'","';
					$result .= $message[3]    .'","';
					$result .= $message[4][$i].'","';
					$result .= $message[5][$i].'","';
					$result .= $message[6][$i].'","';
					$result .= '"';
					array_push( $output, $result );
				}
				$message = array(
					array(), # date 0
					array(), # name 1
					"", # account 2
					"", # type 3
					array(), # amount 4
					array(), # mutation 5
					array() # remarks 6
				);
			}

			// Find the current identifier
			if ( $row[0] == ":" ) {
				$identifier = intval( substr( $row, 1, 2 ) );
				$start = 4;

				// Process the 'mutation' field
				if ( $identifier == 60 ) {
					//$message[5] .= substr( $row, $start+1 );
				}
				// Process the 'type' field
				if ( $identifier == 28 ) {
					$message[3] .= substr( $row, $start );
				}
				// Process the 'account' field
				if ( $identifier == 25 ) {
					$message[2] .= substr( $row, $start );
				}
				// Process the 'date' and 'amount' fields
				if ( $identifier == 61 ) {
					if ($row[3] == 'R') {
						array_push( $message[0], substr( $row, $start+1, 6 ) );
						array_push( $message[4], 0 );
						array_push( $message[5], "TXT" );
					}
					else {
						if ($row[14] == 'C') {
							$expl1 = explode( 'C', substr( $row, $start ) );
							array_push( $message[5], "C" );
						}
						else if ($row[14] == 'D') {
							$expl1 = explode( 'D', substr( $row, $start ) );
							array_push( $message[5], "D" );
						}
						else {
							$expl1 = explode( 'D', substr( $row, $start ) );
							array_push( $message[5], "???" );
						}
						$expl2 = explode( 'N', $expl1[1] );
						$amount = $expl2[0];
						array_push( $message[0], substr( $expl1[0], 0, 6 ) );
						array_push( $message[4], $amount );
					}
				}
				// Process the 'name' and 'remarks' fields
				if ( $identifier == 86 ) {
					if ($row[3] == 'E') {
						array_push( $message[1], substr( $row, $start+1 ) );
						array_push( $message[6], substr( $row, $start+1 ) );
					}
					else {
						array_push( $message[1], substr( $row, $start ) );
						array_push( $message[6], substr( $row, $start ) );
					}
				}
			}
		}
		return $output;
	}

	// Function to transform the Rietumu XML format to a normal bank format
	public function Rietumu_to_normal( $setting, $data ) {

		// Pre-process the data line by line
		$newdata = array();
		$start = false;
		foreach ( $data as $row ) {

			// Found the start of an entry
			if ( strpos( $row, '<TrxSet>' ) !== false ) {
				$start = true;
				$newrow = array();
			}

			// Found the end of an entry
			if ( strpos( $row, '</TrxSet>' ) !== false ) {
				$start = false;
				ksort($newrow, SORT_NUMERIC);
				array_push( $newdata, implode( ';', $newrow ) );
			}

			// Process an entry
			// DATE - NAME - ACCOUNT - TYPE - AMOUNT - MUTATION - REMARKS
			if ($start == true) {
				$temp = explode( '>', $row );
				$temp2 = explode( '<', $temp[1] );
				$processedrow = $temp2[0];

				// Found the date
				if ( strpos( $row, '<BookDate>' ) !== false ) {
					$newrow[0] = $processedrow;
				}
				// Found the name
				if ( strpos( $row, '<Name>' ) !== false ) {
					$newrow[1] = $processedrow;
				}
				// Found the account
				if ( strpos( $row, '<AccNo>' ) !== false ) {
					$newrow[2] = $processedrow;
				}
				// Found the type
				if ( strpos( $row, '<BankName>' ) !== false ) {
					$newrow[3] = $processedrow;
				}
				// Found the amount
				if ( strpos( $row, '<Amt>' ) !== false ) {
					$newrow[4] = $processedrow;
				}
				// Found the mutation
				if ( strpos( $row, '<BankRef>' ) !== false ) {
					$newrow[5] = $processedrow;
				}
				// Found the remark
				if ( strpos( $row, '<PmtInfo>' ) !== false ) {
					$newrow[6] = $processedrow;
				}
			}
		}
		return $newdata;
	}

	// Function to process the data uploaded to the module
	public function parse( $data, $bankid ) {
		$store = Mage::app()->getStore()->getStoreId();

		// Get the bank details
		$bank = Mage::getStoreConfig( 'bankintegration/banksettings/bank'.$bankid, $store );
		$bank_array = Mage::getModel( 'bankintegration/banks' )->getBankArray();
		$bankname = $bank_array[$bank];
		Mage::log( '[kinento-bankintegration] Parsing data for '.$bankname, null, 'kinento.log', true );

		// Get some more settings from the database
		$filternegative = Mage::getStoreConfig( 'bankintegration/generalsettings/filternegative', $store );
		$regexp = Mage::getStoreConfig( 'bankintegration/banksettings/pattern', $store );
		$convertutf = Mage::getStoreConfig( 'bankintegration/generalsettings/convertutf', $store );

		// Transform the MT94x bank format into a normal 'default' format
		if ( $bank == 'MT94x' ) {
			$data = $this->MT94x_to_default( $bank, $data );
			$bank = 'default';
		}

		// Transform the ISO20022 bank format into a normal 'default' format
		if ( $bank == 'ISO 20022' ) {
			$data = $this->ISO20022_to_default( $bank, $data );
			$bank = 'default';
		}

		// Transform the ISO20022 (camt.054) bank format into a normal 'default' format
		if ( $bank == 'camt.054' ) {
			$data = $this->camt054_to_default( $bank, $data );
			$bank = 'default';
		}

		// Transform the Rietumu XML bank format into a normal format
		if ( $bank == 'Rietumu' ) {
			$data = $this->Rietumu_to_normal( $bank, $data );
		}

		// Get all bank templates
		$templates = Mage::getModel( 'bankintegration/banks' )->getBankTemplates();

		// Get the template information for the chosen bank
		$banktemplate = null;
		foreach ($templates as $template) {
			if ($template[0] == $bank) {
				$banktemplate = $template;
				break;
			}
		}

		// Return an error if we haven't found a bank template
		if (!$banktemplate) {
			Mage::getSingleton( 'adminhtml/session' )->addError( Mage::helper( 'bankintegration' )->__( 'Invalid bank: '.$bank ) );
			return;
		}

		// Gather information on the selected bank template
		$bank_name          = $banktemplate[0];
		$bank_string        = $banktemplate[1];
		$bank_separator     = $banktemplate[2];
		$bank_firstlineskip = $banktemplate[3];
		$bank_remove        = $banktemplate[4];
		Mage::log( '[kinento-bankintegration] Using template "'.$bank_name.'", "'.$bank_string.'", "'.$bank_separator.'", "'.$bank_remove.'"', null, 'kinento.log', true );

		// Initialise
		$firstline = $bank_firstlineskip;
		$template = explode( " ", $bank_string );

		// Additional pre-processing for the 'VR-Bank' file format
		if ( $bank_name == "VR-Bank" ) {
			$line_number = 0;
			$newdata = array();
			foreach ( $data as $row ) {
				if ( $line_number > 12 && $line_number < ( count( $data )-2 ) ) {
					$newdata[] = $row;
				}
				$line_number += 1;
			}
			$newdata = implode( ' ', $newdata );
			$newdata = explode( ' "', $newdata );
			$data = array();
			foreach ( $newdata as $row ) {
				$newrow = '"'.$row;
				$data[] = str_replace( '""', '"', $newrow );
			}
		}

		// Additional pre-processing for the 'Swiss Postfinance' file format
		if ( $bank_name == "Swiss Postfinance" ) {
			$newdata = array();
			$newrow = "";
			foreach ( $data as $row ) {
				if ( $row[0] == '"' ) {
					$newdata[] = $newrow;
					$newrow = $row;
				}
				else {
					$newrow = $newrow.' '.$row;
				}
			}
			$newdata[] = $newrow;
			$data = $newdata;
		}

		// Additional pre-processing for the 'UniCredit (2)' file format
		if ( $bank_name == "UniCredit (2)" ) {
			$newdata = array();
			$newrow = "";
			foreach ( $data as $row ) {
				if ( $row[0] == '"' ) {
					$newdata[] = $newrow;
					$newrow = $row;
				}
				else {
					$newrow = $newrow.' '.$row;
				}
			}
			$newdata[] = $newrow;
			$data = $newdata;
			array_shift( $data );
		}

		// Additional pre-processing for the 'Raiffaisen' file format
		if ( $bank_name == "Raiffaisen" ) {
			$section = 1;
			$skip = false;
			$newdata = array();
			$newrow = "";
			foreach ( $data as $row ) {

				// Two different parts in a single file
				if ($skip == true) {
					$skip = false;
					continue;
				}
				if ( substr($row, 19, 15) == "telek;;;;;;;;;;" ) {
					$section = 1;
					$skip = true;
					continue;
				}
				if ( substr($row, 11, 15) == "telek;;;;;;;;;;" ) {
					$section = 2;
					$skip = true;
					continue;
				}

				// Empty rows
				if ( substr($row, 0, 10) == ";;;;;;;;;;" ) {
					continue;
				}

				// Transform the data of the second section into the format of the first
				if ( $section == 2 ) {
				}

				// Set the data
				$newdata[] = $newrow;
				$newrow = $row;
			}

			// Last piece of data
			$newdata[] = $newrow;

			// Create the final array
			$data = $newdata;
		}

		// Additional pre-processing for the 'HSBC Bank (MY)' file format
		if ( $bank_name == "HSBC Bank" ) {
			$newdata = array();
			$newrow = '';
			$inquotes = false;
			foreach ( $data as $row ) {

				// Check for quotes in the row
				$chars = str_split($row);
				foreach ( $chars as $char ) {
					if ( $char == '"' ) {
						$inquotes = !$inquotes;
					}
				}

				// Only terminated rows (by quotes) are 'real' rows
				$newrow = trim($newrow.' '.$row);
				if ( !$inquotes ) {
					$newdata[] = $newrow;
					$newrow = '';
				}
			}
			$data = $newdata;
		}

		// Remove the first 5 and the last 1 line for the 'Deutsche Bank'
		if ( $bank_name == "Deutsche Bank" ) {
			array_shift( $data );
			array_shift( $data );
			array_shift( $data );
			array_shift( $data );
			array_shift( $data );
			array_pop( $data );
			#array_pop( $data );
		}

		// Remove the first 8 lines for the 'Postbank (2)'
		if ( $bank_name == "Postbank (2)" ) {
			array_shift( $data );
			array_shift( $data );
			array_shift( $data );
			array_shift( $data );
			array_shift( $data );
			array_shift( $data );
			array_shift( $data );
			array_shift( $data );
		}

		// Remove the first 8 lines for 'Bank of America'
		if ( $bank_name == "Bank of America" ) {
			array_shift( $data ); array_shift( $data ); array_shift( $data ); array_shift( $data );
			array_shift( $data ); array_shift( $data ); array_shift( $data ); array_shift( $data );
		}

		// Remove the last 2 lines for 'Banco Monex'
		if ( $bank_name == "Banco Monex" ) {
			array_pop( $data );
			array_pop( $data );
		}

		// Remove the last line for 'Erste Bank'
		if ( $bank_name == "Erste Bank" ) {
			array_pop( $data );
		}

		// Start the processing of the data per row
		foreach ( $data as $row ) {

			// Additional per-entry processing for the 'Credit Suisse' file format
			if ( $bank_name == "Credit Suisse V11" ) {
				$offset = -1;
				$lengths = array(3,9,27,10,10,6,6,6,10,9,4);
				foreach ( $lengths as $length ) {
					$offset = $offset + $length + 1;
					$row = substr_replace($row,";",$offset,0);
				}
			}

			// Additional per-entry processing for the 'BBVA Bancomer' file format
			if ( $bank_name == "BBVA Bancomer" ) {
				$row = str_replace( "\t\t", "\t", $row );
			}

			// Remove the separator from entries with quotes for two Malaysian banks
			if ( $bank_name == "CIMB Bank" || $bank_name == "HSBC Bank" ) {
				$newrow = "";
				$chars = str_split($row);
				$inquotes = false;
				foreach ( $chars as $char ) {
					if ( $char == '"' ) {
						$inquotes = !$inquotes;
					}
					else {
						if ( $inquotes && $char == ',' ) {
							$newrow .= '';
						}
						else {
							$newrow .= $char;
						}
					}
				}
				$row = $newrow;
			}

			// Split the row
			$array = preg_split( $bank_separator, $row );

			// Additional per-entry processing for the 'DAB Munchen' file format
			if ( $bank_name == "DAB Munchen" ) {
				for ( $k = 31; $k <= 43; $k++ ) {
					$array[30] .= $array[$k];
				}
			}

			// Additional per-entry processing for the 'Hibiscus' file format
			if ( $bank_name == "Hibiscus" ) {
				$array[9] = $array[9].$array[10].$array[16];
				$array[9] = str_replace( '""', ' ', $array[9] );
			}

			// Additional per-entry processing for the 'ABN AMRO' file format
			if ( $bank_name == "ABN Amro" ) {
				$array_temp = preg_split( "/ +/", $array[7] );
				$array[6] = str_replace( '-', '-', $array[6] );
				$array[7] = $array_temp[0];
				
				// Iterate over the possible bank formats

				// GIRO payment
				if ( $array[7] == "GIRO" ) {
					$array[8] = $array_temp[1];
					$array[9] = "";
					for ( $i = 2; $array_temp[$i] != "BETALINGSKENM." && $i+5<count( $array_temp ) && $array_temp[$i]." ".$array_temp[$i+1] != "iDEAL betaling:"; $i++ ) {
						$array[9] = $array[9]." ".$array_temp[$i];
					}
					$array[11] = "";
					$array[10] = "";
					for ( $j = $i; $j <= count( $array_temp )-1; $j++ ) {
						$array[10] = $array[10]." ".$array_temp[$j];
					}
				}

				// BEA payment
				elseif ( $array[7] == "BEA" ) {
					$array[8] = "";
					$array[9] = $array_temp[2];
					$array[10] = "";
					for ( $j = 3; $j <= count( $array_temp )-1; $j++ ) {
						$array[10] = $array[10]." ".$array_temp[$j];
					}
					$array[11] = "";
				}

				// IBAN payment
				elseif ( $array[7] == "/TRTP/SEPA" ) {
					$array[7] = "SEPA/IBAN";
					$array[8] = str_replace( 'OVERBOEKING', '', $array_temp[1] );
					$array[9] = "";
					$array[10] = "";
					for ( $j = 2; $j <= count( $array_temp )-1; $j++ ) {
						$array[10] = $array[10]." ".$array_temp[$j];
					}
					$array[11] = "";
				}

				// BUITENLAND payment
				elseif ( preg_match("/BUITENLAND OVERBOEKING/", implode( ' ', $array_temp ) ) == 1 ) {
					$array[7] = "BUITENLAND";
					$array[8] = "";
					$array[9] = "";
					$array[10] = str_replace("BUITENLAND OVERBOEKING", "", implode( ' ', $array_temp ) );
					$array[11] = "";
				}

				// Default payment
				else {
					$array[8] = $array_temp[1];
					$array[9] = "";
					for ( $i = 2; $array_temp[$i] != "BETALINGSKENM." && $i+3<count( $array_temp ) && $array_temp[$i]." ".$array_temp[$i+1] != "iDEAL betaling:"; $i++ ) {
						$array[9] = $array[9]." ".$array_temp[$i];
					}
					$array[10] = "";
					for ( $j = $i; $j <= count( $array_temp )-1; $j++ ) {
						$array[10] = $array[10]." ".$array_temp[$j];
					}
					$array[11] = "";
				}
				//Mage::log( '[kinento-bankintegration] '.implode( ' :: ',$array ), null, 'kinento.log', true );
			}

			// Continue with normal processing
			$row = trim( $row );
			if ( $firstline == false && !empty( $row ) ) {
				$date     = str_replace( $bank_remove, '', $array[$template[0]] );
				$name     = str_replace( $bank_remove, '', $array[$template[1]] );
				$account  = str_replace( $bank_remove, '', $array[$template[2]] );
				$type     = str_replace( $bank_remove, '', $array[$template[3]] );
				if ( $bank_name != "BBVA Bancomer" && $bank_name != "Banco Monex" ) {
					$amount = str_replace( ','         , '.',$array[$template[4]] );
				}
				else {
					$amount = str_replace( ','         , '', $array[$template[4]] );
				}
				$amount = str_replace( $bank_remove, '', $amount);
				$mutation = str_replace( $bank_remove, '', $array[$template[5]] );
				$remarks  = "";
				for ( $i = $template[6]; $i <= $template[6]; $i++ ) {
					$remarks .= str_replace( $bank_remove, '', $array[$i] )." ";
				}
				preg_match( '/'.$regexp.'/', $remarks, $identifier );
				//$identifier = array_reverse( $identifier );

				// Remove double spaces
				$name = preg_replace( '/\s+/', ' ', $name );
				$remarks = preg_replace( '/\s+/', ' ', $remarks );


				// Check for a proper date input
				$date = trim( $date );
				if ( empty( $date ) ) {
					$date = "01.01.1990";
				}
				else {
					if ( preg_match("/[a-zA-Z:+?;,<>'*$^&#!?\[\]=\)\(@]/", $date ) == 1 ) {
						$date = "01.01.1990";
					}
				}

				if ( $bank_name == "Deutsche Bank" && $amount == '' ) {
					$amount = str_replace( ',', '.', $array[3] );
				}

				// Additional processing for the 'Bank X' file format
				if ( $bank_name == "Bank X") {
					if ( $mutation != "" ) {
						$amount = str_replace( ',', '.', $mutation);
						$mutation = "";
					}
					else {
						$amount = -$amount;
					}
					$temp = explode( "/", $name );
					if ( count($temp) == 3 ) {
						$name = $temp[0];
						$account = $temp[1];
						$type = $temp[2];
						$mutation = "complete";
					}
					else {
						$mutation = "pending";
					}
				}

				// Additional processing for the 'Bank of America' file format
				if ( $bank_name == "Bank of America" ) {
					preg_match( "/TYPE:([^:]*)(\s\w+:|$)/", $array[1], $matches ); $type = $matches[1];
					preg_match( "/TRN:([^:]*)(\s\w+:|$)/", $array[1], $matches );  $account = $matches[1];
					preg_match( "/SEQ:([^:]*)(\s\w+:|$)/", $array[1], $matches );  $mutation = $matches[1];
					preg_match( "/DES:([^:]*)(\s\w+:|$)/", $array[1], $matches );  $type = $matches[1];
					preg_match( "/ID:([^:]*)(\s\w+:|$)/", $array[1], $matches );   $remarks = $matches[1];
					preg_match( "/INDN:([^:]*)(\s\w+:|$)/", $array[1], $matches ); $name = $matches[1];
				}

				// Additional post-processing for the 'Credit Suisse' format
				if ( $bank_name == "Credit Suisse V11" ) {
					$name = '';
					$amount = substr_replace( $amount, ',', -2,0 );
					$identifier = str_replace( "9932690000000000", "", $identifier );
				}

				// Remove spaces from the identifier
				$identifier = str_replace( " ", "", array_pop( $identifier ) );

				// Log the result
				//Mage::log( '[kinento-bankintegration] Using the regular expression /'.$regexp.'/ on '.$remarks.': '.$identifier, null, 'kinento.log', true );


				// Additional post-processing for the 'HSBC Bank (MY)' format (extra amount)
				if ( $bank_name == "HSBC Bank" ) {
					if ( $amount == "" ) {
						$amount = $array[15];
					}
				}

				// Additional post-processing for the 'BBVA Bancomer' format
				if ( $bank_name == "BBVA Bancomer" ) {
					$amount = str_replace( ",", "", $amount );
					$name = '';
					$type = '';
					$mutation = '';
					if ( preg_match( '/C(\w)(\d{5,20})(\d)\/\d{7}/', $remarks, $regex_result ) ) { // Pattern: CDXXXXXXXXXXXXXXXXXXV/YYYYYYY
						$type = $regex_result[1];
						$remarks = $regex_result[0];
						$mutation = $regex_result[3];
						$identifier = $regex_result[2];
					}
					if ( preg_match( '/CIE(\d{5,20})(\d)\w{11}/', $remarks, $regex_result ) ) { // Pattern: CIEXXXXXXXXXXXXXXXXXXXVYYYYYYYYYYY
						$type = 'CD';
						$remarks = $regex_result[0];
						$mutation = $regex_result[2];
						$identifier = $regex_result[1];
						$amount = -$amount;
					}
				}

				// Additional post-processing for the 'Banco Monex' format
				if ( $bank_name == "Banco Monex" ) {

					// Get the data from the Kinento Banco Monex module
					try {
						$bancomonex = Mage::getModel( 'bancomonex/bancomonex' )->getCollection()
							->addFieldToFilter( 'account', $remarks )
							->getItems();

						// Account does not exist
						if ( empty( $bancomonex ) ) {
							$identifier = $remarks;
						}

						// Account is present, get the customer ID
						else {
							$identifier = reset( $bancomonex )->getCustomerId();
						}
					}

					// Module is not installed, produced an error message
					catch ( Exception $ex ) {
						$identifier = 'Error: Banco Monex payment module not installed';
					}
				}

				// Additional post-processing for the 'Raiffaisen' file format
				if ( $bank_name == "Raiffaisen" ) {
					$date = str_replace( $bank_remove, '', $array[$template[0]] );
					$date = substr($date, 0, 10);
					$amount = filter_var($amount,FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
				}

				// Convert from UTF8 (or not)
				if ( $convertutf == 'enabled' ) {
					$name = iconv( "ISO-8859-1", "UTF-8", $name );
					$account = iconv( "ISO-8859-1", "UTF-8", $account );
					$type = iconv( "ISO-8859-1", "UTF-8", $type );
					$mutation = iconv( "ISO-8859-1", "UTF-8", $mutation );
					$remarks = iconv( "ISO-8859-1", "UTF-8", $remarks );
				}

				// Set the obtained data
				$entry = array(
					"date"      => $date,
					"name"      => $name,
					"account"   => $account,
					"type"      => $type,
					"amount"    => $amount,
					"mutation"  => $mutation,
					"remarks"   => $remarks,
					"identifier"=> $identifier,
				);

				// Additional post-processing for 'Osuuspankki'
				if ( $bank_name == "Osuuspankki" ) {
					$entry["name"] = strtoupper( $entry["name"] );
					$entry["account"] = preg_replace( '/\s\s+/', ' ', $entry["account"] );
					$entry["account"] = preg_replace( '/^.*Viesti:/', ' ', $entry["account"] );
					/*
					$temp1 = $entry["identifier"];
					$temp2 = '';
					for ( $i = 0; $i <= 9; $i++) {
						if ($i < count( $temp1 ) ) {
							$temp2 = $temp1[$i];
						}
					}
					$entry["identifier"] = $temp2;
					*/
				}

				// Filter out entries according to the filter settings
				$condition = true;
				$filter = Mage::getResourceModel( 'bankrules/bankrules_collection' )->getItems();
				foreach ( $filter as $filterEntry ) {
					if ( $filterEntry->getType() == 'Exact' ) {
						$condition = $condition && ( $entry[$filterEntry->getField()] != $filterEntry->getFilter() );
					}
					else {
						preg_match( '/'.$filterEntry->getFilter().'/', $entry[$filterEntry->getField()], $regResult );
						$condition = $condition && !implode( $regResult );
					}
				}

				// Filter out negative entries
				// if ( $filternegative == 'enabled' ) {
				// 	$condition = $condition && ( $entry["amount"] > 0.0 );
				// }
				
				// Filter out negative entries
				if ( $filternegative == 'enabled' ) {
					$entry_amount = $entry["amount"];
					if ( $entry_amount[0] == '-' ) {
						$condition = false;
					}
				}

				// Filter out entries for the 'Credit Suisse' bank format
				if ( $bank_name == 'Credit Suisse V11' ) {
					if ( $type == '999' ) {
						$condition = false;
					}
				}

				$collection = Mage::getModel( 'bankintegration/bankintegration' )->getCollection();
				$collection->addFieldToFilter( 'date', $date );
				$collection->addFieldToFilter( 'name', $name );
				$collection->addFieldToFilter( 'amount', $amount );
				//$collection->addFieldToFilter( 'identifier', $entry["identifier"] );
				$collection->addFieldToFilter( 'remarks', $entry["remarks"] );
				$items = $collection->getItems();
				if ( empty( $items ) ) {
					$bankmodel = Mage::getModel( 'bankintegration/bankintegration' );
					$bankmodel->setData( $entry );
					if ( $condition == false ) {
						$bankmodel->setStatus( 'neglected' );
					}
					$bankmodel->save();
				}
			}
			$firstline = false;
		}
	}

	// Function to find which which bank item has to be bound to which order
	public function bind() {
		$store = Mage::app()->getStore()->getStoreId();

		// Get the settings
		$name_of_main_bank = Mage::getStoreConfig( 'bankintegration/banksettings/bank1', $store );
		$collection = Mage::getModel( 'bankintegration/bankintegration' )->getCollection();
		$baseonid = Mage::getStoreConfig( 'bankintegration/generalsettings/baseoninvoices' );
		$usem2epro = Mage::getStoreConfig( 'bankintegration/generalsettings/usem2epro', $store );

		// Iterate over all bank items
		$bankmodel = $collection->addFieldToFilter( 'status', 'unbound' );
		$bankitems = $bankmodel->getItems();
		foreach ( $bankitems as $bankitem ) {
			Mage::log( '[kinento-bankintegration] Processing bank-item with ID:'.$bankitem->getIdentifier(), null, 'kinento.log', true );
			$found = 0;
			$amount = $bankitem->getAmount();

			// Prepare the collections based on invoices or orders or both
			$idtypes = array();
			if ( $baseonid == 'invoiceid' || $baseonid == 'orderandinvoiceid' ) {
				array_push( $idtypes, 'invoice' );
			}
			if ( $baseonid == 'orderid' || $baseonid == 'orderandinvoiceid' ) {
				array_push( $idtypes, 'order' );
			}
			foreach ( $idtypes as $idtype ) {
				if ( $idtype == 'order' ) {
					$collectionname = 'sales/order_grid_collection';
				}
				else {
					$collectionname = 'sales/order_invoice_grid_collection';
				}

				// Check whether there is a bank identifier or not
				if ( $bankitem->getIdentifier() != ' ' && $bankitem->getIdentifier() != '' ) {
					$identifiers = array();

					// Banco Monex: Get a list of order IDs from the customer ID
					if ( "Banco Monex" == $name_of_main_bank ) {
						$customerid = $bankitem->getIdentifier();
						$orders = Mage::getResourceModel('sales/order_collection')
							->addFieldToSelect('*')
							->addFieldToFilter('customer_id', $customerid )
							->getItems();
						foreach ( $orders as $order ) {
							$identifiers[] = $order->getIncrementId();
						}
					}

					// Normal processing
					else {

						// Translate in case of using the M2E Pro module
						if ( $usem2epro == 'enabled' ) {
							Mage::log( '[kinento-bankintegration] Using M2EPro integration', null, 'kinento.log', true );
							$identifiers[] = $this->translate( $bankitem->getIdentifier() );
						}
						// Don't translate (normal)
						else {
							$identifiers[] = $bankitem->getIdentifier();
						}
					}

					// Iterate over all the identifiers (normal: just one, Banco Monex: multiple)
					foreach ( $identifiers as $identifier ) {
						if ( $found != 1 ) {

							// Check for IDENTIFIER and AMOUNT
							$collection = Mage::getResourceModel( $collectionname );
							$datacollection = $collection->addFieldToFilter( 'increment_id', $identifier )->addFieldToFilter( 'grand_total', $amount );
							$orders = $datacollection->getItems();
							if ( !empty( $orders ) ) {
								$found = 1;
								if ( $idtype == 'invoice' ) { $order = Mage::getModel( 'sales/order' )->loadByIncrementId( reset( $orders )->getOrderIncrementId() ); }
								else { $order = reset( $orders ); }
								$this->bindorder( $order, $bankitem, 'certain' );
							}

							else {
								// Check for IDENTIFIER but not AMOUNT
								$collection = Mage::getResourceModel( $collectionname );
								$datacollection = $collection->addFieldToFilter( 'increment_id', $identifier );
								$orders = $datacollection->getItems();
								if ( !empty( $orders ) ) {
									$found = 1;
									if ( $idtype == 'invoice' ) { $order = Mage::getModel( 'sales/order' )->loadByIncrementId( reset( $orders )->getOrderIncrementId() ); }
									else { $order = reset( $orders ); }
									$this->bindorder( $order, $bankitem, 'guess' );
								}
								else {

									// Check for AMOUNT but not for IDENTIFIER
									$collection = Mage::getResourceModel( $collectionname );
									$datacollection = $collection->addFieldToFilter( 'grand_total', $amount );
									$orders = $datacollection->getItems();
									if ( !empty( $orders ) ) {
										$found = 1;
										if ( $idtype == 'invoice' ) { $order = Mage::getModel( 'sales/order' )->loadByIncrementId( reset( $orders )->getOrderIncrementId() ); }
										else { $order = reset( $orders ); }
										$this->bindorder( $order, $bankitem, 'guess' );
									}
								}
							}
						}
					}
				}

				// Identifier is not present
				else {

					// Check for AMOUNT but not for IDENTIFIER
					$collection = Mage::getResourceModel( $collectionname );
					$datacollection = $collection->addFieldToFilter( 'grand_total', $amount );
					$orders = $datacollection->getItems();
					if ( !empty( $orders ) ) {
						$found = 1;
						if ( $idtype == 'invoice' ) { $order = Mage::getModel( 'sales/order' )->loadByIncrementId( reset( $orders )->getOrderIncrementId() ); }
						else { $order = reset( $orders ); }
						$this->bindorder( $order, $bankitem, 'guess' );
					}
				}
			}
			if ( $found == 0 ) {

				// Still not found anything, check for CUSTOMER-NAME (heavy on processing - not working for invoice-based or Banco Monex)
				/*
				if ( $idtype == 'order' && "Banco Monex" != $name_of_main_bank ) { 
					$datacollection = Mage::getResourceModel( 'sales/order_grid_collection' );
					$orders = $datacollection->getItems();
					if ( !empty( $orders ) ) {
						$bankname = strtoupper( $bankitem->getName() );
						$initialmatch = round( strlen( $bankname )/2 );
						$topmatch = $initialmatch;
						$toporder = reset( $orders );
						foreach ( $orders as $order ) {
							$customer = Mage::getModel( 'customer/customer' )->load( $order->getCustomerId() );
							$ordername = strtoupper( $customer->getFirstname().' '.$customer->getLastname() );
							$match = c( $bankname, $ordername );
							if ( $match > $topmatch ) {
								$topmatch = $match;
								$toporder = $order;
							}
						}
						if ( $topmatch > $initialmatch ) {
							$found = 1;
							$this->bindorder( $toporder, $bankitem, 'guess' );
						}
					}
				}
				*/
			}
		}
	}

	// Function to perform the actual binding
	public function bindorder( $order, $bankitem, $status ) {
		$notfound = true;
		$oldstatuses1 = explode( ',', Mage::getStoreConfig( 'bankintegration/statussettings/statusold1', $order->getStoreId() ) );
		foreach ( $oldstatuses1 as $oldstatus ) {
			if ( $oldstatus != 'disabled' ) {
				if ( $order->getStatus() == $oldstatus ) {
					$this->binddata( $order, $bankitem, $status );
					$notfound = false;
				}
			}
		}
		if ($notfound) {
			$oldstatuses2 = explode( ',', Mage::getStoreConfig( 'bankintegration/statussettings/statusold2', $order->getStoreId() ) );
			foreach ( $oldstatuses2 as $oldstatus ) {
				if ( $oldstatus != 'disabled' ) {
					if ( $order->getStatus() == $oldstatus ) {
						$this->binddata( $order, $bankitem, $status );
					}
				}
			}
		}
	}

	// Function to set the data for a binding
	public function binddata( $order, $bankitem, $status ) {
		$customer = Mage::getModel( 'customer/customer' )->load( $order->getCustomerId() );
		$bankitem->setBindorder( $order->getIncrementId() );
		$bankitem->setBindname( $customer->getFirstname().' '.$customer->getLastname() );
		$bankitem->setBindamount( $order->getGrandTotal() );
		$bankitem->setStatus( $status );
		$bankitem->save();
	}

	// Function to translate a bank ID from an Ebay or Amazon ID into a Magento ID
	public function translate( $bankid ) {

		// Match this to M2E Pro's Ebay identifiers (stop searching if one is found)
		$orders = Mage::getModel('M2ePro/ebay_order')->getCollection();
		foreach( $orders as $order ) {
			// See if the bank's identifier matches an Ebay identifier
			if ( $bankid == $order->getData('ebay_order_id') ) {
				// Return the normal Magento order ID
				$order_id = $order->getData('order_id');
				$magento_order_id = Mage::getModel('M2ePro/order')->load( $order_id )->getMagentoOrderId();
				return Mage::getModel( 'sales/order' )->load( $magento_order_id )->getIncrementId();
			}
		}

		// Match this to M2E Pro's Amazon identifiers (stop searching if one is found)
		$orders = Mage::getModel('M2ePro/amazon_order')->getCollection();
		foreach( $orders as $order ) {
			// See if the bank's identifier matches an Amazon identifier
			if ( $bankid == $order->getData('amazon_order_id') ) {
				// Return the normal Magento order ID
				$order_id = $order->getData('order_id');
				$magento_order_id = Mage::getModel('M2ePro/order')->load( $order_id )->getMagentoOrderId();
				return Mage::getModel( 'sales/order' )->load( $magento_order_id )->getIncrementId();
			}
		}

		// Could not find a match, return the bank identifier
		return $bankid;
	}
}

?>
