<?php

/** PHPExcel */
require_once 'excel_pdf/PHPExcel.php';
/** PHPExcel_IOFactory */
require_once 'excel_pdf/PHPExcel/IOFactory.php';
 
class Excel_pdf_manager
{      
    function import($filename)
    {
        
    }
 
    function export($AllDeals,$AllDealsDate,$PartnerDeals){
        $objPHPExcel = new PHPExcel();                      //creando un objeto excel
		$objPHPExcel->getProperties()->setCreator("Geek Bucket"); //propiedades
		$objPHPExcel->setActiveSheetIndex(0);               //poniendo active hoja 1
		$objPHPExcel->getActiveSheet()->setTitle("Reporte de deals");  //título de la hoja 1                                        //poniendo algo en una celda
		/*for($i = 0;$i<10;$i++){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $i, $textooo);
		}*/
		
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		
		$styleArray = array('font' => array('bold' => true));
		$objPHPExcel->getActiveSheet()->getStyle('A1:Z1')-> applyFromArray($styleArray);//poniendo en negritas una fila
		
		$poscCell = 1;
		$poscX = 0;
		
		if($AllDeals != 0){
			
			$objPHPExcel->getActiveSheet()->getStyle('A' . $poscCell .':B' . $poscCell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('A' . $poscCell .':B' . $poscCell)->getFill()->getStartColor()->setARGB('006600');
			
			$poscX = 3;
		
			$titleAllDeals = array("Descargados","De la semana","Redimidos","De la semana");
		
			$objPHPExcel->getActiveSheet()->mergeCells('A' . $poscCell .':B' . $poscCell);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $poscCell, "Todos los deals");
			$poscCell++;
			for($i = 0;$i<count($AllDeals);$i++){
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $poscCell, $titleAllDeals[$i]);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $poscCell, $AllDeals[$i]);
				$poscCell++;
			}
			
			$poscCell++;	
		}
		
		if($AllDealsDate != 0){
		
			$titleAllDealsDate = array("Descargados","Redimidos");
		
			$objPHPExcel->getActiveSheet()->mergeCells('A' . $poscCell .':B' . $poscCell);
			$objPHPExcel->getActiveSheet()->getStyle('A' . $poscCell .':B' . $poscCell)-> applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $poscCell, "Todos los deals por fecha");
			$poscCell++;
			$poscCell++;
			for($i = 0;$i<count($AllDealsDate);$i++){
				
				$DealsDate = explode("-", $AllDealsDate[$i]);
				
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $poscCell, $DealsDate[0]);
				$objPHPExcel->getActiveSheet()->mergeCells('A' . $poscCell .':B' . $poscCell);
				$poscCell++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $poscCell, "Descargados");
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $poscCell, $DealsDate[1]);
				$poscCell++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $poscCell, "Redimidos");
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $poscCell, $DealsDate[2]);
				$poscCell = $poscCell + 2;
			}
			
			$poscCell++;	
		}
		
		if($PartnerDeals != 0){
			$poscCell = 1;
			$letter1 = $this->getNameFromNumber($poscX);
			$letter2 = $this->getNameFromNumber($poscX + 1);
			$letter3 = $letter1;
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX, $poscCell, "Deals por comercio");
			$poscCell = 3;
			foreach($PartnerDeals as $items){
				
				$letter1 = $this->getNameFromNumber($poscX);
				$letter2 = $this->getNameFromNumber($poscX + 1);
				
				$PartnerDeas2 = explode("*", $items);
				
				$PartnerAllDeals = explode("-", $PartnerDeas2[0]);
				
				////////pinta el todal de deals del comercio
				
				$objPHPExcel->getActiveSheet()->getStyle($letter1 . $poscCell . ":" . $letter2 . $poscCell)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle($letter1 . $poscCell . ":" . $letter2 . $poscCell)->getFill()->getStartColor()->setARGB('00FF00');
				$objPHPExcel->getActiveSheet()->mergeCells($letter1 . $poscCell . ":" . $letter2 . $poscCell);
				$objPHPExcel->getActiveSheet()->getStyle($letter1 . $poscCell . ":" . $letter2 . $poscCell)-> applyFromArray($styleArray);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX, $poscCell, $PartnerAllDeals[0]);
				$poscCell++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX, $poscCell, "Descargados");
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX + 1, $poscCell, $PartnerAllDeals[1]);
				$poscCell++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX, $poscCell, "Redimidos");
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX + 1, $poscCell, $PartnerAllDeals[2]);
				
				
				$poscCell = $poscCell+2;
				
				if(count($PartnerDeas2)>1){
					
					for($i = 1; $i < count($PartnerDeas2);$i++){
						$PartnerAllDeals = explode("_", $PartnerDeas2[$i]);
						
						//pinta los deals por comercion y fecha
						
						$objPHPExcel->getActiveSheet()->mergeCells($letter1 . $poscCell . ":" . $letter2 . $poscCell);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX, $poscCell, $PartnerAllDeals[0]);
						$poscCell++;
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX, $poscCell, "Descargados");
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX + 1, $poscCell, $PartnerAllDeals[1]);
						$poscCell++;
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX, $poscCell, "Redimidos");
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($poscX + 1, $poscCell, $PartnerAllDeals[2]);
						$poscCell = $poscCell + 2;
						
						$objPHPExcel->getActiveSheet()->getColumnDimension($letter1)->setWidth(30);
						$objPHPExcel->getActiveSheet()->getColumnDimension($letter2)->setWidth(15);
						
					}
				}else{
					$objPHPExcel->getActiveSheet()->getColumnDimension($letter1)->setAutoSize(true);
					$objPHPExcel->getActiveSheet()->getColumnDimension($letter2)->setAutoSize(true);	
				}
				
				
				
				$poscX = $poscX + 3;
				$poscCell = 3;
				
				
			}
			$objPHPExcel->getActiveSheet()->mergeCells($letter3 . "1:" . $letter2 . "1");
			$objPHPExcel->getActiveSheet()->getStyle($letter3 . "1:" . $letter2 . "1")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle($letter3 . "1:" . $letter2 . "1")->getFill()->getStartColor()->setARGB('006600');
		}
		
		/*$styleThinBlackBorderOutline = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FF000000'),
				),
			),
		);
		$objPHPExcel->getActiveSheet()->getStyle('A4:E10')->applyFromArray($styleThinBlackBorderOutline);*/
		
		
 
		//creando un objeto writer para exportar el excel, y direccionando salida hacia el cliente web para invocar diálogo de salvar:
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="reporteDeals.xlsx');
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
		
    }  
	
	//obtiene el la letras del excel por numero
	public function getNameFromNumber($num) {
    	$numeric = $num % 26;
    	$letter = chr(65 + $numeric);
    	$num2 = intval($num / 26);
    	if ($num2 > 0) {
        	return $this->getNameFromNumber($num2 - 1) . $letter;
    	} else {
        	return $letter;
    	}
	} 
}



?>