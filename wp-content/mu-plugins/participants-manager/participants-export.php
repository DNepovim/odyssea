<?php




function ptcm_get_participants_record_data($year) {

	$prefix = 'ptcm_';

	// get Post object off selected vintage
	$currentVintage = get_page_by_title($year, 'OBJECT', $prefix . 'vintage');

	// get participants older then limit
	$args = array(
		'post_type'   => $prefix . $year,
		'numberposts' => -1,
		'order'       => 'ASC',
		'post_status' => 'private',
		'meta_key'	  => $prefix . 'birthdate',
		'meta_query' => array(
			 array(
				 'key'     => $prefix . 'birthdate',
				 'value'   => get_post_meta($currentVintage->ID, $prefix . 'age_date', true),
				 'compare' => '<'
			 )
		 )
	);
	$participants = get_posts($args);

	// get participants younger then limit
	$args = array(
		'post_type'   => $prefix . $year,
		'numberposts' => -1,
		'order'       => 'ASC',
		'post_status' => 'private',
		'meta_key'	  => $prefix . 'birthdate',
		'meta_query' => array(
			 array(
				 'key'     => $prefix . 'birthdate',
				 'value'   => get_post_meta($currentVintage->ID, $prefix . 'age_date', true),
				 'compare' => '>'
			 )
		 )
	);
	$participantsYounger = get_posts($args);

	// get list of custom questions
	$custom_fields = get_post_meta( $currentVintage->ID, $prefix . 'field', true );

	// create new document
	$dokument = new PHPExcel();
	// select sheet
	$dokument->setActiveSheetIndex(0);
	$list = $dokument->getActiveSheet();

	// add data to table
	ptcm_add_data_to_sheet($list, $custom_fields, $participants);

	// select second sheet
	$list = $dokument->createSheet(1);

	// add data to table
	ptcm_add_data_to_sheet($list, $custom_fields, $participantsYounger);

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="export.xlsx"');
	header('Cache-Control: max-age=0');
	$objWriter = PHPExcel_IOFactory::createWriter($dokument, ("Excel2007"));
	$objWriter->save('php://output'); //stahování souboru
	die();
}


function ptcm_add_data_to_sheet($list, $custom_fields, $participants) {

	$prefix = 'ptcm_';
	// set head of table
	$col = 'A';
	$list->setCellValue($col++ . '1', 'pohlaví');
	$list->setCellValue($col++ . '1', 'přesdívka');
	$list->setCellValue($col++ . '1', 'jméno');
	$list->setCellValue($col++ . '1', 'příjmení');
	$list->setCellValue($col++ . '1', 'datum narození');
	$list->setCellValue($col++ . '1', 'výzva');
	$list->setCellValue($col++ . '1', 'zpráva k výzvě');

	// add custom questions to head of table
	foreach($custom_fields as $field) {
		$list->setCellValue($col++ . '1', $field[$prefix . 'name']);
	}

	// add data to table
	$row = 2;
	foreach($participants as $participant) {
		$col = 'A';
		$data = get_post_meta($participant->ID);

		$list->setCellValue($col++ . $row , $data[$prefix . 'gender'][0]);
		$list->setCellValue($col++ . $row , $data[$prefix . 'nickname'][0]);
		$list->setCellValue($col++ . $row , $data[$prefix . 'firstname'][0]);
		$list->setCellValue($col++ . $row , $data[$prefix . 'surname'][0]);
		$list->setCellValue($col++ . $row , $data[$prefix . 'birthdate'][0]);
		$list->setCellValue($col++ . $row , '');
		$list->setCellValue($col++ . $row , '');

		$fieldIndex = 1;
		foreach($custom_fields as $field) {
			$list->setCellValue($col++ . $row , $data[$prefix . 'custom_meta_' . sprintf("%02d", $fieldIndex++)][0]);
		}
		$row++;
	}
}






