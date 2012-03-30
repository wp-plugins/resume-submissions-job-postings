<?php 
@include( resume_get_plugin_dir( 'go' ) . '/includes/fpdf/fpdf.php');
@include( resume_get_plugin_dir( 'go' ) . '/includes/fpdi/fpdi.php');

$pdf =& new FPDI(); 
$pdf->AddPage(); 
$pdf->setSourceFile( get_option( 'resume_pdf_base_file' ) ); 
$tplIdx = $pdf->importPage( 1 ); 
$pdf->useTemplate( $tplIdx, 0, 0 ); 
$pdf->SetDisplayMode( 'real' );
$pdf->SetTitle( 'Roadway Worker Training - Purchase Invoice' );
$pdf->SetAuthor( 'Roadway Worker Training' );
$pdf->SetSubject( 'Purchase Invoice' );







?>