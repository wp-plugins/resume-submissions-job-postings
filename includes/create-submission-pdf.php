<?php 
require( 'fpdf/fpdf.php');
require( 'fpdi/fpdi.php');

$saveFile = md5( $id ) . '.pdf';
$info     = $wpdb->get_row( 'SELECT * FROM ' . SUBTABLE . ' WHERE id = "' . $id . '"' );

// function hex2dec
// returns an associative array (keys: R,G,B) from
// a hex html code (e.g. #3FE5AA)
function hex2dec( $couleur = "#000000" ){
    $R = substr( $couleur, 1, 2 );
    $rouge = hexdec( $R );
    $V = substr( $couleur, 3, 2 );
    $vert = hexdec( $V );
    $B = substr( $couleur, 5, 2 );
    $bleu = hexdec( $B );
    $tbl_couleur = array();
    $tbl_couleur['R']=$rouge;
    $tbl_couleur['V']=$vert;
    $tbl_couleur['B']=$bleu;
    return $tbl_couleur;
}

// conversion pixel -> millimeter at 72 dpi
function px2mm( $px ){
    return $px*25.4/72;
}

function txtentities( $html ){
    $trans = get_html_translation_table( HTML_ENTITIES );
    $trans = array_flip( $trans );
    return strtr( $html, $trans );
}
////////////////////////////////////

// Re-declare the class footer and construct the class to display HTML
class PDF extends FPDI {
	// Variables of html parser
	var $B;
	var $I;
	var $U;
	var $HREF;
	var $fontList;
	var $issetfont;
	var $issetcolor;
	
	function PDF_HTML( $orientation='P', $unit='mm', $format='A4' ){
		//Call parent constructor
		$this->FPDF( $orientation, $unit, $format );
		//Initialization
		$this->B    = 0;
		$this->I    = 0;
		$this->U    = 0;
		$this->HREF = '';
		$this->fontlist   = array( 'arial', 'times', 'courier', 'helvetica', 'symbol' );
		$this->issetfont  = false;
		$this->issetcolor = false;
	}
	
	function WriteHTML( $html ){
		// HTML parser
		$html = strip_tags( $html, "<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>" ); 
		$html = str_replace( "\n", ' ', $html ); 
		$a    = preg_split( '/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE ); 
		foreach( $a as $i=>$e ){
			if( $i%2 == 0 ){
				// Text
				if( $this->HREF )
					$this->PutLink( $this->HREF, $e );
				else
					$this->Write( 5, stripslashes( txtentities( $e  ) ) );
			} else {
				// Tag
				if( $e[0] == '/' )
					$this->CloseTag( strtoupper( substr( $e, 1) ) );
				else {
					// Extract attributes
					$a2   = explode( ' ', $e );
					$tag  = strtoupper( array_shift( $a2 ) );
					$attr = array();
					foreach( $a2 as $v ){
						if( preg_match( '/([^=]*)=["\']?([^"\']*)/', $v, $a3) )
							$attr[strtoupper( $a3[1] )] = $a3[2];
					}
					$this->OpenTag( $tag,$attr );
				}
			}
		}
	}
	
	function OpenTag( $tag, $attr )
	{
		// Opening tag
		switch( $tag ){
			case 'STRONG':
				$this->SetStyle( 'B', true );
				break;
			case 'EM':
				$this->SetStyle( 'I', true );
				break;
			case 'B':
			case 'I':
			case 'U':
				$this->SetStyle( $tag, true );
				break;
			case 'A':
				$this->HREF = $attr['HREF'];
				break;
			case 'IMG':
				if( isset( $attr['SRC'] ) && ( isset( $attr['WIDTH'] ) || isset( $attr['HEIGHT'] ) ) ) {
					if( !isset( $attr['WIDTH'] ) )
						$attr['WIDTH'] = 0;
					if( !isset( $attr['HEIGHT'] ) )
						$attr['HEIGHT'] = 0;
					$this->Image( $attr['SRC'], $this->GetX(), $this->GetY(), px2mm( $attr['WIDTH'] ), px2mm( $attr['HEIGHT'] ) );
				}
				break;
			case 'TR':
			case 'BLOCKQUOTE':
			case 'BR':
				$this->Ln( 5 );
				break;
			case 'P':
				$this->Ln( 10 );
				break;
			case 'FONT':
				if ( isset( $attr['COLOR'] ) && $attr['COLOR'] != '' ) {
					$coul = hex2dec( $attr['COLOR'] );
					$this->SetTextColor( $coul['R'], $coul['V'], $coul['B'] );
					$this->issetcolor=true;
				}
				if ( isset( $attr['FACE'] ) && in_array( strtolower( $attr['FACE'] ), $this->fontlist ) ) {
					$this->SetFont( strtolower( $attr['FACE'] ) );
					$this->issetfont = true;
				}
				break;
		}
	}
	
	function CloseTag( $tag ){
		// Closing tag
		if( $tag == 'STRONG' )
			$tag = 'B';
		if( $tag == 'EM' )
			$tag = 'I';
		if( $tag == 'B' || $tag == 'I' || $tag == 'U' )
			$this->SetStyle( $tag, false );
		if( $tag == 'A' )
			$this->HREF = '';
		if( $tag == 'FONT' ){
			if ( $this->issetcolor == true ) {
				$this->SetTextColor( 0 );
			}
			if ( $this->issetfont ) {
				$this->SetFont( 'arial' );
				$this->issetfont = false;
			}
		}
	}
	
	function SetStyle( $tag, $enable ){
		// Modify style and select corresponding font
		$this->$tag += ( $enable ? 1 : -1 );
		$style = '';
		foreach( array( 'B', 'I', 'U' ) as $s ){
			if( $this->$s > 0 )
				$style .= $s;
		}
		$this->SetFont( '', $style );
	}
	
	function PutLink( $URL, $txt ){
		// Put a hyperlink
		$this->SetTextColor( 0, 0, 255 );
		$this->SetStyle( 'U', true );
		$this->Write( 5, $txt, $URL );
		$this->SetStyle( 'U', false );
		$this->SetTextColor( 0 );
	}
	
		// Page footer
	function Footer(){
		$this->SetY( -15 );
		$this->SetFont( 'Arial', 'I', 8 );
		$this->SetTextColor( 0, 0, 0 ); 
		$this->Cell( 0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C' );
		$this->Cell( 0, 10, date( 'Y-m-d h:i:s' ), 0, 0, 'R' );
	}
}


// Setup the new pdf
$pdf = new PDF(); 
$pdf->AliasNbPages();
$pdf->AddPage(); 
$pdf->setSourceFile( get_option( 'resume_pdf_base_file' ) ); 
$tplIdx = $pdf->importPage( 1 ); 
$pdf->useTemplate( $tplIdx, 0, 0 ); 
$pdf->SetDisplayMode( 'real' );
$pdf->SetAutoPageBreak( 'on', 20 );
$pdf->SetTitle( 'Resume Submission - ' . $info->fname . ' ' . $info->lname );
$pdf->SetAuthor( get_option( 'blogname' ) );
$pdf->SetSubject( 'Resume Submission' );

$startY  = 35;
$startY2 = 35;

// Start the insert of the information
// Name
$pdf->SetFont( 'Arial' ); 
$pdf->SetFontSize( 24 ); 
$pdf->SetTextColor( 0, 0, 0 ); 
$pdf->SetXY( 0, 10 ); 
$pdf->Cell( 210, 5, $info->fname . ' ' . $info->lname, 0, 1, 'C' );

// For Job
$pdf->SetFont( 'Arial' ); 
$pdf->SetFontSize( 16 ); 
$pdf->SetTextColor( 0, 0, 0 ); 
$pdf->SetXY( 0, 20 ); 
$pdf->Cell( 210, 5, $info->job, 0, 1, 'C' );

// Submission Date
$pdf->SetFont( 'Arial' ); 
$pdf->SetFontSize( 12 ); 
$pdf->SetTextColor( 0, 0, 0 ); 
$pdf->SetXY( 0, 27 ); 
$pdf->Cell( 210, 5, date( 'F d, Y', strtotime( $info->pubdate ) ), 0, 1, 'C' );

// Address
if ( grabContents( get_option( 'resume_input_fields' ), 'address', 0 ) && $info->address ) {	
	$startY = $startY + 5; 
	$pdf->SetFont( 'Arial' ); 
	$pdf->SetFontSize( 10 ); 
	$pdf->SetTextColor( 0, 0, 0 ); 
	$pdf->SetXY( 20, $startY ); 
	$pdf->Cell( 100, 5, $info->address, 0, 1, 'L' );
}

// Suite/Apt #
if ( grabContents( get_option( 'resume_input_fields' ), 'address2', 0 ) && $info->address2 ) {	
	$startY = $startY + 5; 
	$pdf->SetFont( 'Arial' ); 
	$pdf->SetFontSize( 10 ); 
	$pdf->SetTextColor( 0, 0, 0 ); 
	$pdf->SetXY( 20, $startY ); 
	$pdf->Cell( 100, 5, $info->address2, 0, 1, 'L' );
}

// City, State, Zip
if ( grabContents( get_option( 'resume_input_fields' ), 'city', 0 ) && $info->city ) {	
	$cityStateZip .= $info->city . ' ';
}
if ( grabContents( get_option( 'resume_input_fields' ), 'state', 0 ) && $info->state ) {	
	$cityStateZip .= $info->state . ', ';
}
if ( grabContents( get_option( 'resume_input_fields' ), 'zip', 0 ) && $info->zip ) {	
	$cityStateZip .= $info->zip;
}
if ( $cityStateZip ){
	$startY = $startY + 5;
	$pdf->SetFont( 'Arial' ); 
	$pdf->SetFontSize( 10 ); 
	$pdf->SetTextColor( 0, 0, 0 ); 
	$pdf->SetXY( 20, $startY ); 
	$pdf->Cell( 100, 5, $cityStateZip, 0, 1, 'L' );
}

// Email
if ( grabContents( get_option( 'resume_input_fields' ), 'email', 0 ) && $info->email ) {	
	$startY2 = $startY2 + 5; 
	$pdf->SetFont( 'Arial' ); 
	$pdf->SetFontSize( 10 ); 
	$pdf->SetTextColor( 0, 0, 0 ); 
	$pdf->SetXY( 120, $startY2 ); 
	$pdf->Cell( 88, 5, $info->email, 0, 1, 'L' );
}

// Primary Number
if ( grabContents( get_option( 'resume_input_fields' ), 'pnumber', 0 ) && $info->pnumber ) {	
	$startY2 = $startY2 + 5; 
	$pdf->SetFont( 'Arial' ); 
	$pdf->SetFontSize( 10 ); 
	$pdf->SetTextColor( 0, 0, 0 ); 
	$pdf->SetXY( 120, $startY2 ); 
	$pdf->Cell( 88, 5, $info->pnumber . ' (' . $info->pnumbertype . ')', 0, 1, 'L' );
}

// Secondary Number
if ( grabContents( get_option( 'resume_input_fields' ), 'snumber', 0 ) && $info->snumber ) {	
	$startY2 = $startY2 + 5; 
	$pdf->SetFont( 'Arial' ); 
	$pdf->SetFontSize( 10 ); 
	$pdf->SetTextColor( 0, 0, 0 ); 
	$pdf->SetXY( 120, $startY2 ); 
	$pdf->Cell( 88, 5, $info->snumber . ' (' . $info->snumbertype . ')', 0, 1, 'L' );
}

// Attachments
if ( grabContents( get_option( 'resume_input_fields' ), 'attachment', 0 ) && $info->attachment ) {	
    // Title
	$startY2 = $startY2 + 10; 
	$pdf->SetFont( 'Arial' ); 
	$pdf->SetFontSize( 14 ); 
	$pdf->SetTextColor( 0, 0, 0 ); 
	$pdf->SetXY( 120, $startY2 ); 
	$pdf->Cell( 88, 5, 'Attachments', 0, 1, 'L' );
	
	// Display attachments
	$attachments = explode( ',', $info->attachment );
    $attachCount = 1;
    foreach ( $attachments as $attach){
		$startY2 = $startY2 + 5; 
		$pdf->SetFont( 'Arial' ); 
		$pdf->SetFontSize( 10 ); 
		$pdf->SetTextColor( 0, 0, 0 ); 
		$pdf->SetXY( 120, $startY2 ); 
		$pdf->Write( 5, $attachCount . '. ' );
		$pdf->SetTextColor( 0, 0, 255 ); 
		$pdf->Write( 5, $attach, WP_CONTENT_URL . '/uploads/rsjp/attachments/' . $attach );
		$attachCount++;
	}
}

// Cover Letter
if ( grabContents( get_option( 'resume_input_fields' ), 'cover', 0 ) && $info->cover ) {
	$pdf->AddPage(); 
	$pdf->SetFont( 'Times' ); 
	$pdf->SetFontSize( 10 ); 
	$pdf->SetTextColor( 0, 0, 0 ); 
	$pdf->SetXY( 20, 20 ); 
	$pdf->WriteHTML( $info->cover );	
}

// Resume
if ( grabContents( get_option( 'resume_input_fields' ), 'resume', 0 ) && $info->resume ) {
	$pdf->AddPage(); 
	$pdf->SetFont( 'Times' ); 
	$pdf->SetFontSize( 10 ); 
	$pdf->SetTextColor( 0, 0, 0 ); 
	$pdf->SetXY( 20, 20 ); 
	$pdf->WriteHTML( $info->resume );	
}

//$pdf->Output();
$pdf->Output( WP_CONTENT_DIR . '/uploads/rsjp/pdfs/' . $saveFile, 'F' );  
$generatedPDF = WP_CONTENT_DIR . '/uploads/rsjp/pdfs/' . $saveFile;

?>