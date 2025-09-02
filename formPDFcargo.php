<?php
    // header('Content-Type: text/html; charset=ISO-8859-1');


if (!empty($_POST['submit'])) {
	//
	$txt_nomb=$_POST['txt_name'];
	$txt_ape_pat=$_POST['txt_ape_pat'];
	$txt_ape_mat=$_POST['txt_ape_mat'];
	$txt_DNI=$_POST['txt_DNI'];
	$arrayCHK=$_POST['checkboxes'];
	$txt_IP=$_POST['txt_IP'];
	$cmb_ofi=$_POST['txt_ofic'];
	$cmb_carg=$_POST['txt_cond_lab'];
	
	$num=count($arrayCHK);

	// SISTEMAS REGISTRALES

	$txt_user_ad=$_POST['txt_user_ad'];  // USUARIO WINDOWS
	$txt_user_consulta=$_POST['txt_user_consulta'];  // CONSULTA REGISTRAL
	// $txt_user_SIV=$_POST['txt_user_SIV'];
	$txt_user_libro=$_POST['txt_user_libro'];  //LIBRO DIARIO
	$txt_user_mepa=$_POST['txt_user_mepa'];  //MESA DE PARTES
	$txt_user_rpu=$_POST['txt_user_rpu'];  //RPU GRAFICOS
	$txt_user_sarp=$_POST['txt_user_sarp'];  //SARP
	$txt_user_scunac=$_POST['txt_user_scunac'];  //SCUNAC
	$txt_user_sepr=$_POST['txt_user_sepr'];  //SEPR 
	$txt_user_sigesar=$_POST['txt_user_sigesar'];  //SIGESAR
	$txt_user_sir=$_POST['txt_user_sir'];  //SIR
	$txt_user_minero=$_POST['txt_user_minero'];  //SIR MINERO
	$txt_user_rpv=$_POST['txt_user_rpv'];  //SIR RPV
	$txt_user_sgtd=$_POST['txt_user_sgtd'];  // SGTD
	$txt_user_sou=$_POST['txt_user_sou'];  //SOU
	$txt_user_spij=$_POST['txt_user_spij'];  //SPIJ
	$txt_user_spr=$_POST['txt_user_spr'];  //SPR
	$txt_user_sprn=$_POST['txt_user_sprn'];  //SPRN
	$txt_user_sprnMP=$_POST['txt_user_sprnMP'];  //SPRN MESA DE PARTES
	$txt_user_toolgis=$_POST['txt_user_toolgis'];  //TOOLGIS
	$txt_user_QSARP=$_POST['txt_user_QSARP']; // QUEMADOR SARP
	$txt_user_FDS=$_POST['txt_user_FDS']; // FDS

	// SISTEMAS WEB

	$txt_user_citrix=$_POST['txt_user_citrix'];  // Citrix
	$txt_user_correo=$_POST['txt_user_correo'];  // Correo Institucional
	$txt_user_psi=$_POST['txt_user_psi'];  // PSI
	$txt_user_reniec=$_POST['txt_user_reniec'];  // RENIEC
	$txt_user_sgd=$_POST['txt_user_sgd'];  // SGD
	$txt_user_sgdMP=$_POST['txt_user_sgdMP'];  // SGD MESA DE PARTES
	$txt_user_sgit=$_POST['txt_user_sgit'];  // SGIT
	$txt_user_sprl=$_POST['txt_user_sprl'];  // SPRL - EXTRANET
	
	// SISTEMAS ADMINISTRATIVOS

	$txt_user_axion=$_POST['txt_user_axion']; // AXION
	$txt_user_Clarissa=$_POST['txt_user_Clarissa']; // CLARISSA
	$txt_user_CheckSmart=$_POST['txt_user_CheckSmart']; // CHECKSMART
	$txt_user_devo=$_POST['txt_user_devo']; // DEVOLUCIONES 
	$txt_user_Firma=$_POST['txt_user_Firma']; // FIRMA ONPE
	$txt_user_madaf=$_POST['txt_user_madaf']; // MADAF SIAF
	$txt_user_meli=$_POST['txt_user_meli']; // MELISSA
	$txt_user_logis=$_POST['txt_user_logis']; // MODULO LOGISTICA
	$txt_user_lega=$_POST['txt_user_lega']; // LEGAJO
	$txt_user_refirma=$_POST['txt_user_refirma']; // REFIRMA PDF
	$txt_user_siaf=$_POST['txt_user_siaf']; // SIAF
	$txt_user_sica=$_POST['txt_user_sica']; // SICA
	$txt_user_siga=$_POST['txt_user_siga']; // SIGA
	$txt_user_sisaba=$_POST['txt_user_sisaba']; // SISABA
	$txt_user_sistram=$_POST['txt_user_sistram']; // SISTRAM
	$txt_user_sute=$_POST['txt_user_sute']; // SUTESOR
	$txt_user_teso=$_POST['txt_user_teso']; // TESORERIA
	$txt_user_visitas=$_POST['txt_user_visitas']; // REGISTRO DE VISITAS

	// SISTEMAS INFORMATICA Y OTROS

	$txt_user_any=$_POST['txt_user_any'];
	$txt_user_bd=$_POST['txt_user_bd'];
	$txt_user_keyfile=$_POST['txt_user_keyfile'];
	$txt_user_vpn=$_POST['txt_user_vpn'];
	$txt_user_ftp=$_POST['txt_user_ftp'];
	$txt_user_certficado=$_POST['txt_user_certficado'];
	$txt_user_discovery=$_POST['txt_user_discovery'];
	$txt_user_Acceso=$_POST['txt_user_Acceso'];



	$txt_user_mcafee=$_POST['txt_user_mcafee'];


// require 'C:\xampp\htdocs\zrx\PUBLIC\FSAv2\FPDF\fpdf.php';
require 'C:\ejxampp\htdocs\Consultas\tfpdf\tfpdf.php';
// $pdf=new FPDF('P','mm','A4');
$pdf=new tFPDF();
$pdf->SetFont("Arial","B","12");
$pdf->AddPage();
$width=210; // Width of Current Page
$height=297; // Height of Current Page
$edge=10; // Gap between line and border , change this value
$fecha=date('d/m/y');
$dia=date('d');
$mes=date('m');
$anio=date('y');
$hora=date('h:i:s');

$pdf->AddFont('DejaVu','','Helvetica-Font/Helvetica.ttf',true);//
//$pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'Helvetica-Font/Helvetica-Bold.ttf', true);

//-------------PRIMER CUADRO--------------
//----------------------------------------
$pdf->Line(10, 33,$width-$edge,33); // Horizontal line at top
$pdf->Line(10, 105+($num*4),$width-$edge,105+($num*4)); // Horizontal line at bottom
$pdf->Line(10, 33,10,105+($num*4)); // Vetical line at left
$pdf->Line($width-$edge, 33,$width-$edge,105+($num*4)); // Vertical line at Right


//--------------SEGUNDO CUADRO--------------
//------------------------------------------
$pdf->Line($edge, 110+($num*4),$width-$edge,110+($num*4)); // Horizontal line at top
$pdf->Line($edge, 200+($num*4),$width-$edge,200+($num*4)); // Horizontal line at bottom
$pdf->Line($edge, 110+($num*4),$edge,200+($num*4)); // Vetical line at left 
$pdf->Line($width-$edge,110+($num*4),$width-$edge,200+($num*4)); // Vetical line at right
/**
//--------------TERCER CUADRO--------------
//------------------------------------------
$pdf->Line($edge, 167,$width-$edge,167); // Horizontal line at top
$pdf->Line($edge, 220,$width-$edge,220); // Horizontal line at bottom
$pdf->Line($edge, 167,$edge,220); // Vetical line at left
$pdf->Line($width-$edge, 167,$width-$edge,220); // Vetical line at right
$pdf->Line(105, 167,105,220); // Middle line at left

//--------------CUARTO CUADRO--------------
//------------------------------------------
$pdf->Line($edge, 225,$width-$edge,225); // Horizontal line at top
$pdf->Line($edge, 280,$width-$edge,280); // Horizontal line at bottom
$pdf->Line($edge, 225,$edge,280); // Vetical line at left
$pdf->Line($width-$edge, 225,$width-$edge,280); // Vetical line at right
$pdf->Line(105, 225,105,280); // Middle line at left
**/
$pdf->SetFont("DejaVu","","10");
$pdf->Cell(55,20,$pdf->Image('C:\ejxampp\htdocs\Consultas\IMG\logo.png',16,8,35),0,0,'C');
//$pdf->Ln(15);
//$pdf->Cell(0,10,"UNIDAD DE TECNOLOGIAS DE LA INFORMACION",0,0,'C');
//$pdf->Ln(7);
$pdf->SetFont("DejaVu","B");
$pdf->Cell(87,6,"ZONA REGISTRAL N X - SEDE CUSCO",0,1,"C");
$pdf->Cell(142,8,"UNIDAD DE TECNOLOGIAS DE LA INFORMACION",0,0,"R");
$pdf->SetFont("DejaVu","B","16");
$pdf->Cell(40,10,"N ____",0,1,"R");
$pdf->SetFont("DejaVu","BU",13);
$pdf->Cell(139,4,"CARGO DE ENTREGA DE ACCESOS",0,0,"R");
$pdf->Ln(8);
$pdf->GetX();
$pdf->SetFont("DejaVu",'BU',11);
//$pdf->SetXY(20,35);
$pdf->Cell(140,6,"1. ACCESOS",0);
$pdf->SetFont("DejaVu","B","11"); //fuente para fecha
$pdf->Cell(15,6,"Fecha:",0,0,'L');
$pdf->SetFont("DejaVu","","11");
$pdf->Cell(25,6,"{$fecha} ",0,1,'L');

$pdf->SetFont("DejaVu","","11");
$pdf->MultiCell(188,6,"Por la presente, la Unidad de Tecnologias de la Informacion hace entrega al personal {$txt_nomb} {$txt_ape_pat} {$txt_ape_mat} identificado con DNI {$txt_DNI} que labora en la Oficina {$cmb_ofi} la(s) clave(s) de acceso siguientes:",0,"J");
$pdf->SetFont("Arial","","11");

$pdf->Ln(2);

$pdf->Ln(5);
$pdf->SetFont("Arial","","11"); //fuente para tabla



$pdf->Cell(10,6,"",0,0);
$pdf->SetFont("DejaVu","B","11");
$pdf->Cell(7,6,"N",1,0,"C");
$pdf->Cell(45,6,"SISTEMA",1,0,"C");
$pdf->Cell(75,6,"USUARIO",1,0,"C");
$pdf->Cell(45,6,"CONTRASEÑA",1,1,"C");


$pdf->SetFont("Arial","","10");


for($n=0;$n<$num;$n++)
	{
		$ntemp=$n+1;
		$temp=("$arrayCHK[$n]");
		$pdf->Cell(10,6,"",0,0);
		$pdf->Cell(7,6,"{$ntemp}",1,0,'C');
		$pdf->Cell(45,6,"{$temp} ",1,0,'L');

		// ------------------------------------------------------ SISTEMAS REGISTRALES ----------------------------------------------------------

		// USUARIO WINDOWS
		if (strpos($arrayCHK[$n],'Windows')) {
			$pdf->Cell(75,6,"{$txt_user_ad}",1,0,"C");	
			$pdf->Cell(45,6,"Cusco01cusco",1,1,"C");	
		}

		// CONSULTA REGISTRAL
		else if(strpos($arrayCHK[$n],'Registral')) {
			$pdf->Cell(75,6,"{$txt_user_consulta}",1,0,"C");	
			$pdf->Cell(45,6,"{$txt_user_consulta}",1,1,"C");		
		}

		// else if(strpos($arrayCHK[$n],'Verificadores')) {
		// 	$pdf->Cell(70,6,"{$txt_user_SIV}",1,0,"C");	
		// 	$pdf->Cell(45,6,"sdsdss",1,1,"C");	
		// }

		// LIBRO DIARIO
		else if(strpos($arrayCHK[$n],'Diario')) {
			$pdf->Cell(75,6,"{$txt_user_libro}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_libro}",1,1,"C");		
		}

		// MESA DE PARTES
		else if(strpos($arrayCHK[$n],'Partes')) {
			$pdf->Cell(75,6,"{$txt_user_mepa}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_mepa}",1,1,"C");		
		}

		// RPU GRAFICOS
		else if(strpos($arrayCHK[$n],'Grafico')) {
			$pdf->Cell(75,6,"{$txt_user_rpu}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_rpu}",1,1,"C");		
		}
		
		// SARP
		else if($arrayCHK[$n]=='SARP') {
			$pdf->Cell(75,6,"{$txt_user_sarp}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_sarp}",1,1,"C");		
		}

		// SCUNAC
		else if($arrayCHK[$n]=='SCUNAC') {
			$pdf->Cell(75,6,"{$txt_user_scunac}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_scunac}",1,1,"C");		
		}

		// SEPR
		else if($arrayCHK[$n]=='SEPR') {
			$pdf->Cell(75,6,"{$txt_user_sepr}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_sepr}",1,1,"C");		
		}

		// SIGESAR 
		else if($arrayCHK[$n]=='SIGESAR') {
			$pdf->Cell(75,6,"{$txt_user_sigesar}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_sigesar}",1,1,"C");		
		}

		// SIR
		else if($arrayCHK[$n]=='SIR') {
			$pdf->Cell(75,6,"{$txt_user_sir}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_sir}",1,1,"C");
		}

		// SIR MINERO
		else if(strpos($arrayCHK[$n],'Minero')) {
			$pdf->Cell(75,6,"{$txt_user_minero}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_minero}",1,1,"C");		
		}

		// SIR RPV
		else if(strpos($arrayCHK[$n],'RPV')) {
			$pdf->Cell(75,6,"{$txt_user_rpv}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_rpv}",1,1,"C");		
		}

		// SGTD
		else if($arrayCHK[$n]=='SGTD') {
			$pdf->Cell(75,6,"{$txt_user_sgtd}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_sgtd}",1,1,"C");
		}

		// SOU
		else if($arrayCHK[$n]=='SOU') {
			$pdf->Cell(75,6,"{$txt_user_sou}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_sou}",1,1,"C");		
		}

		// SPIJ
		else if($arrayCHK[$n]=='SPIJ') {
			$pdf->Cell(75,6,"{$txt_user_spij}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_spij}",1,1,"C");		
		}

		// SPR
		else if($arrayCHK[$n]=='SPR') {
			$pdf->Cell(75,6,"{$txt_user_spr}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_spr}",1,1,"C");		
		}

		// SPRN
		else if($arrayCHK[$n]=='SPRN') {
			$pdf->Cell(75,6,"{$txt_user_sprn}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_sprn}",1,1,"C");		
		}

		// SPRN MESA DE PARTES
		else if($arrayCHK[$n]=='SPRN(MESA DE PARTES)') {
			$pdf->Cell(75,6,"{$txt_user_sprnMP}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_sprnMP}",1,1,"C");		
		}
		
		// TOOLGIS
		else if($arrayCHK[$n]=='ToolGIS') {
			$pdf->Cell(75,6,"{$txt_user_toolgis}",1,0,"C");
			$pdf->Cell(45,6,"sunarp1,",1,1,"C");		
		}

		// QUEMADOR DE SARP
		else if($arrayCHK[$n]=='Quemador SARP') {
			$pdf->Cell(75,6,"{$txt_user_QSARP}",1,0,"C");
			$pdf->Cell(45,6,"Cusco01cusco",1,1,"C");		
		}

		// FDS
		else if($arrayCHK[$n]=='FDS') {
			$pdf->Cell(75,6,"{$txt_user_FDS}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_FDS}",1,1,"C");		
		}

		// --------------------------------------------- SISTEMAS WEB -----------------------------------------------------

		// CITRIX
		else if($arrayCHK[$n]=='Citrix') {
			$pdf->Cell(75,6,"{$txt_user_citrix}",1,0,"C");
			$pdf->Cell(45,6,"Cusco01cusco",1,1,"C");		
		}

		// CORREO INSTITUCIONAL
		else if(strpos($arrayCHK[$n],'Institucional')) {
			$pdf->Cell(75,6,"{$txt_user_correo}",1,0,"C");
			$pdf->Cell(45,6,"Sunarp1,",1,1,"C");		
		}

		// PSI
		else if($arrayCHK[$n]=='PSI') {
			$pdf->Cell(75,6,"{$txt_user_psi}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_psi}",1,1,"C");		
		}

		// RENIEC
		else if($arrayCHK[$n]=='RENIEC') {
			$pdf->Cell(75,6,"{$txt_user_reniec}",1,0,"C");
			$pdf->Cell(45,6,"S/C",1,1,"C");
		}

		// SGD
		else if($arrayCHK[$n]=='SGD') {
			$pdf->Cell(75,6,"{$txt_user_sgd}",1,0,"C");
			$pdf->Cell(45,6,"Cusco01cusco",1,1,"C");
		}
		
		// SGD MESA DE PARTES
		else if($arrayCHK[$n]=='SGD(MESA DE PARTES)') {
			$pdf->Cell(75,6,"{$txt_user_sgdMP}",1,0,"C");
			$pdf->Cell(45,6,"Cusco01cusco",1,1,"C");
		}
		
		// SGIT
		else if($arrayCHK[$n]=='SGIT') {
			$pdf->Cell(75,6,"{$txt_user_sgit}",1,0,"C");
			$pdf->Cell(45,6,"Sunarp1,",1,1,"C");				
		}

		// SPRL - EXTRANET
		else if($arrayCHK[$n]=='SPRL') {
			$pdf->Cell(75,6,"{$txt_user_sprl}",1,0,"C");
			$pdf->Cell(45,6,"cusco01",1,1,"C");		
		}
		

		// ------------------------------------------------ SISTEMAS ADMINISTRATIVOS ----------------------------------------------------

		// AXION
		else if($arrayCHK[$n]=='AXION') {
			$pdf->Cell(75,6,"{$txt_user_axion}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_axion}",1,1,"C");
		}

		// CLARISSA
		else if($arrayCHK[$n]=='Clarissa') {
			$pdf->Cell(75,6,"{$txt_user_Clarissa}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_Clarissa}",1,1,"C");		
		}

		// CHECKSMART
		else if($arrayCHK[$n] == 'CheckSmart') {
			$pdf->Cell(75,6,"{$txt_user_CheckSmart}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_CheckSmart}",1,1,"C");		
		}

		// DEVOLUCIONES
		else if($arrayCHK[$n]=='Devoluciones') {
			$pdf->Cell(75,6,"{$txt_user_devo}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_devo}",1,1,"C");		
		}

		// FIRMA ONPE
		else if($arrayCHK[$n]=='Firma') {
			$pdf->Cell(75,6,"{$txt_user_Firma}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_Firma}",1,1,"C");		
		}

		// MADAF SIAF
		else if($arrayCHK[$n]=='MADAF') {
			$pdf->Cell(75,6,"{$txt_user_madaf}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_madaf}",1,1,"C");		
		}
		
		// MELISSA
		else if($arrayCHK[$n]=='Melissa') {
			$pdf->Cell(75,6,"{$txt_user_meli}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_meli}",1,1,"C");		
		}

		// MODULO LOGISTICA
		else if(strpos($arrayCHK[$n],'Logistica')) {
			$pdf->Cell(75,6,"{$txt_user_logis}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_logis}",1,1,"C");		
		}
		
		//LEGAJO
		else if($arrayCHK[$n]=='LEGAJO') {
			$pdf->Cell(75,6,"{$txt_user_lega}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_lega}",1,1,"C");		
		}
		
		// REFIRMA PDF
		else if($arrayCHK[$n]=='ReFirma') {
			$pdf->Cell(75,6,"{$txt_user_refirma}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_refirma}",1,1,"C");		
		}
		
		// SIAF
		else if($arrayCHK[$n]=='SIAF') {
			$pdf->Cell(75,6,"{$txt_user_siaf}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_siaf}",1,1,"C");		
		}

		// SICA
		else if($arrayCHK[$n]=='SICA') {
			$pdf->Cell(75,6,"{$txt_user_sica}",1,0,"C");
			$pdf->Cell(45,6,"123456",1,1,"C");		
		}

		// SIGA
		else if($arrayCHK[$n]=='SIGA') {
			$pdf->Cell(75,6,"{$txt_user_siga}",1,0,"C");
			$pdf->Cell(45,6,"123456",1,1,"C");		
		}

		// SISABA
		else if($arrayCHK[$n]=='SISABA') {
			$pdf->Cell(75,6,"{$txt_user_sisaba}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_sisaba}",1,1,"C");		
		}

		// SISTRAM
		else if($arrayCHK[$n]=='SISTRAM') {
			$pdf->Cell(75,6,"{$txt_user_sistram}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_sistram}",1,1,"C");		
		}

		// SUTETOR
		else if($arrayCHK[$n]=='SUTESOR') {
			$pdf->Cell(75,6,"{$txt_user_sute}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_sute}",1,1,"C");		
		}
		
		// TESORERIA
		else if($arrayCHK[$n]=='TESORERIA') {
			$pdf->Cell(75,6,"{$txt_user_teso}",1,0,"C");		
			$pdf->Cell(45,6,"{$txt_user_teso}",1,1,"C");		
		}

		// REGISTRO DE VISITAS
		else if(strpos($arrayCHK[$n],'Visitas')) {
			$pdf->Cell(75,6,"{$txt_user_visitas}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_visitas}",1,1,"C");		
		}

		// ----------------------------------------- SISTEMAS INFORMATICA / OTROS -------------------

		// ANYDESK
		else if($arrayCHK[$n]=='AnyDesk') {
			$pdf->Cell(75,6,"{$txt_user_any}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_any}",1,1,"C");		
		}

		// BASE DE DATOS
		else if($arrayCHK[$n]=='Base de Datos') {
			$pdf->Cell(75,6,"{$txt_user_bd}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_bd}",1,1,"C");		
		}

		// KEYFILE
		else if($arrayCHK[$n]=='KeyFile') {
			$pdf->Cell(75,6,"{$txt_user_keyfile}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_keyfile}",1,1,"C");		
		}

		// VPN
		else if($arrayCHK[$n]=='VPN') {
			$pdf->Cell(75,6,"{$txt_user_vpn}",1,0,"C");
			$pdf->Cell(45,6,"Sunarp10,",1,1,"C");		
		}

		// FTP
		else if($arrayCHK[$n]=='FTP') {
			$pdf->Cell(75,6,"{$txt_user_ftp}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_ftp}",1,1,"C");		
		}

		// CERTIFICADO DIGITAL
		else if($arrayCHK[$n]=='Certificado Digital') {
			$pdf->Cell(75,6,"{$txt_user_certficado}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_certficado}",1,1,"C");		
		}

		// DISCOVERY
		else if($arrayCHK[$n]=='Discovery') {
			$pdf->Cell(75,6,"{$txt_user_discovery}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_discovery}",1,1,"C");		
		}
		
		// ACCESO A INTERNET
		else if($arrayCHK[$n]=='Acceso a Internet') {
			$pdf->Cell(75,6,"{$txt_user_Acceso}",1,0,"C");
			$pdf->Cell(45,6,"{$txt_user_Acceso}",1,1,"C");		
		}

		// ----------------------------------------------- SISTEMAS DE ANTIVIRUS WITH SECURE ------------------------------------------
		
		// WITHSECURE
		else if($arrayCHK[$n]=='WithSecure') {
			$pdf->Cell(75,6,"{$txt_user_mcafee}",1,0,"C");
			$pdf->Cell(45,6,"Enviado al correo",1,1,"C");
		}
		else{
			$pdf->Cell(45,6," ",1,1,"C");
		}	
	}



//$pdf->Ln(10);
$pdf->SetXY(10, 111+($num*4));
$pdf->SetFont("Arial","BU","11");
$pdf->Cell(140,6,"2. RECEPCION",0,0,'L');
$pdf->SetFont("Arial","","11");

$pdf->SetFont("Arial","B","11"); //fuente para fecha
$pdf->Cell(15,6,"Fecha:",0,0,'L');
$pdf->SetFont("Arial","","11");
$pdf->Cell(25,6,"{$fecha} ",0,1,'L');
$pdf->Ln(20);

$pdf->Cell(188,3,"_______________________",0,1,"C");
$pdf->SetFont("Arial","","10");
$pdf->Cell(188,5,"Firma",0,1,"C");
$pdf->Cell(188,5,"DNI:{$txt_DNI}",0,1,"C");
$pdf->Ln(5);

$pdf->SetFont("Arial","U","11"); 
$pdf->Cell(188,6,"IMPORTANTE",0,1,'C');

$pdf->SetFont("Helvetica","","10"); 
$pdf->MultiCell(188,6,"- La clave entregada es de caracter temporal ya que el usuario se compromete bajo responsabilidad a cambiarla de forma inmediata, quedando a su responsabilidad las futuras modificaciones a las mismas.",0,"J");
$pdf->MultiCell(188,6,"- El usuario se compromete a hacer un uso diligente de las claves de acceso, y a no ponerlas a disposicion de terceros.",0,"J");
$pdf->MultiCell(188,6,"- El uso de la(s) clave(s) proporcionadas son de plena responsabilidad del titular, asi como el uso indebido que terceras personas pudieran hacer del mismo, quedando la SUNARP liberada de toda responsabilidad.",0,"J");

//-------------------------- NUEVA PAGINA - CONVENIO --------------------------------------
$pdf->AddPage();
$width=210; // Width of Current Page
$height=297; // Height of Current Page
$edge=10; // Gap between line and border , change this value

$pdf->Cell(55,20,$pdf->Image('C:\ejxampp\htdocs\Consultas\IMG\logo.png',16,8,35),0,0,'C');

//-------------PRIMER CUADRO--------------
//----------------------------------------
$pdf->Line(10, 6,$width-$edge,6); // Horizontal line at top
$pdf->Line(10, 30,$width-$edge,30); // Horizontal line at bottom
$pdf->Line(10, 6,10,30); // Vetical line at left
$pdf->Line(55,6,55,30); //Linea separadora
//TEXTO DEL MEDIO
$pdf->SetFont("Arial","B","13");
$pdf->Ln(1);
$pdf->Cell(190,5,"Acuerdo de Confidencialidad para el Acceso",0,0,"C");
$pdf->Ln(8);
$pdf->Cell(190,5,"a la plataforma de TICs SUNARP",0,0,"C");
//-----------------------------------------------------------------------------
$pdf->Line($width-$edge, 6,$width-$edge,30); // Vertical line at Right
$pdf->Line($width-$edge-45, 6,$width-$edge-45,30); // separador
$pdf->SetFont("Arial","","12");
$pdf->ln(0);
$pdf->Cell(186.5,-14,"Codigo: F-002-OTI",0,0,'R');
$pdf->Ln(1);
$pdf->Cell(182,7,"Version: v1.0",0,0,'R');
$pdf->Line($width-$edge-45, 18,$width-$edge,18); // separador
$pdf->ln(1);
$pdf->Cell(150,10," ",0,1,'L');
//

if($mes==1) { $mesAux = "Enero"; }
elseif($mes==2) { $mesAux = "Febrero"; }
elseif($mes==3) { $mesAux = "Marzo"; }
elseif($mes==4) { $mesAux = "Abril"; }
elseif($mes==5) { $mesAux = "Mayo"; }
elseif($mes==6) { $mesAux = "Junio"; }
elseif($mes==7) { $mesAux = "Julio"; }
elseif($mes==8) { $mesAux = "Agosto"; }
elseif($mes==9) { $mesAux = "Setiembre"; }
elseif($mes==10) { $mesAux = "Octubre"; }
elseif($mes==11) { $mesAux = "Noviembre"; }
elseif($mes==12) { $mesAux = "Diciembre"; }

// CONTENIDO DE LA SEGUNDA PAGINA 
$pdf->ln(2);
$pdf->SetXY(10, 35);
// $pdf->SetFont("Helvetica","","10");
$pdf->SetFont("DejaVu","","11");
$pdf->Cell(180,15,"Cusco, {$dia} de {$mesAux} del 20{$anio}",0,1,'R');
$pdf->SetXY(20,50);
$pdf->MultiCell(170,6,"            Yo, {$txt_nomb} {$txt_ape_pat} {$txt_ape_mat}, con DNI {$txt_DNI}, personal {$cmb_carg} de la Zona Registral N°X-Sede Cusco-SUNARP, suscribo el presente acuse de recibo de credenciales y acuerdo de confidencialidad.
Declaro ser consciente de la importancia de las credenciales, códigos fuentes, equipos de TI e información que me fueron asignadas y acepto que las mismas solo serán utilizadas para los propósitos de mis funciones y/o desarrollo de actividades.",0 , 'J');
$pdf->SetXY(20,90);
$pdf->MultiCell(170,6,"            Adicionalmente, entiendo que la publicación, traspaso no autorizado o mal uso de las mismas, están sujetos a sanciones definidas por la SUNARP y, en algunos casos puede ser un crimen penado por Ley. Debido a ello, durante la vigencia del vínculo laboral o contrato de servicio, me comprometo a:",0 , 'J');
$pdf->SetXY(20,116);
$pdf->MultiCell(170,6,"     a. No compartir mis credenciales de acceso a los equipos y/o servicios institucionales, bajo ningún motivo, puesto que esta credencial permite el acceso a datos e información confidencial y privilegiada.",0,'J');
$pdf->SetXY(20,135);
$pdf->MultiCell(170,6,"     b. No compartir códigos fuentes, equipos de TI e informacion que me fuera asignada, salvo me encuentre laborando y tenga la aprobacion expresa de mi Jefe inmediato y Jefe de la Oficina/Unidad de Tecnologías de la Información (OTI/UTI).", 0 , 'J');
$pdf->SetXY(20,153);
$pdf->MultiCell(170,6,"      c. Comunicar por escrito o correo electrónico a mi jefe inmediato y al Jefe de la Unidad de Tecnologías de la Información, en caso detectar el uso no autorizado de los mismos, a fin de que se tomen las medidas correctivas necesarias.", 0 , 'J');
$pdf->SetXY(20,175);
$pdf->MultiCell(170,6,"            Los compromisos a y b indicados en los párrafos presentes incluirá un periodo de cinco (5) años posteriores a la finalización del vinculo laboral con la SUNARP o termino del contrato de servicios.
Dejo constancia por escrito a través de este documento, de mi aceptacion a los términos y condiciones, aqui expresados.", 0 , 'J');


$pdf->MultiCell(170,6,"





                                                    ____________________________________
                                                           {$txt_ape_pat} {$txt_ape_mat} {$txt_nomb}", 0 , 'J');

$pdf->Output();
}






?>


    


<!-- if (!empty($_POST['submit'])) {
	//
	$txt_nomb=$_POST['txt_nomb'];
	$txt_ape_pat=$_POST['txt_ape_pat'];
	$txt_ape_mat=$_POST['txt_ape_mat'];
	$txt_DNI=$_POST['txt_DNI'];
	$arrayCHK=$_POST['checkboxes'];
	$txt_IP=$_POST['txt_IP'];
	
	$num=count($arrayCHK);




//-------------PRIMER CUADRO--------------
//----------------------------------------
$pdf->Line(10, 33,$width-$edge,33); // Horizontal line at top
$pdf->Line(10, 105+($num*4),$width-$edge,105+($num*4)); // Horizontal line at bottom
$pdf->Line(10, 33,10,105+($num*4)); // Vetical line at left
$pdf->Line($width-$edge, 33,$width-$edge,105+($num*4)); // Vertical line at Right


//--------------SEGUNDO CUADRO--------------
//------------------------------------------
$pdf->Line($edge, 110+($num*4),$width-$edge,110+($num*4)); // Horizontal line at top
$pdf->Line($edge, 200+($num*4),$width-$edge,200+($num*4)); // Horizontal line at bottom
$pdf->Line($edge, 110+($num*4),$edge,200+($num*4)); // Vetical line at left 
$pdf->Line($width-$edge,110+($num*4),$width-$edge,200+($num*4)); // Vetical line at right


**/
$pdf->SetFont("Arial","","10");
$pdf->Cell(55,20,$pdf->Image('C:\xampp\htdocs\zrx\PUBLIC\FSAv2\IMG\logo.png',16,8,35),0,0,'C');
//$pdf->Ln(15);
//$pdf->Cell(0,10,"UNIDAD DE TECNOLOGIAS DE LA INFORMACION",0,0,'C');
//$pdf->Ln(7);
$pdf->SetFont("Arial","B");
$pdf->Cell(87,6,"ZONA REGISTRAL N X - SEDE CUSCO",0,1,"C");
$pdf->Cell(142,8,"UNIDAD DE TECNOLOGIAS DE LA INFORMACION",0,0,"R");
$pdf->SetFont("Arial","B","16");
$pdf->Cell(40,10,"N ____",0,1,"R");
$pdf->SetFont("Arial","BU",13);
$pdf->Cell(139,4,"CARGO DE ENTREGA DE ACCESOS",0,0,"R");
$pdf->Ln(8);
$pdf->GetX();
$pdf->SetFont("Arial",'BU',11);
//$pdf->SetXY(20,35);
$pdf->Cell(140,6,"1. ACCESOS",0);
$pdf->SetFont("Arial","B","11"); //fuente para fecha
$pdf->Cell(15,6,"Fecha:",0,0,'L');
$pdf->SetFont("Arial","","11");
$pdf->Cell(25,6,"{$fecha} ",0,1,'L');

$pdf->SetFont("Arial","","11");
$pdf->MultiCell(188,6,"Por la presente, la Unidad de Tecnologias de la Informacion hace entrega al personal {$txt_nomb} {$txt_ape_pat} {$txt_ape_mat} identificado con DNI {$txt_DNI} la(s) clave(s) de acceso siguientes:",0,"J");
$pdf->SetFont("Arial","","11");

$pdf->Ln(2);

$pdf->Ln(5);
$pdf->SetFont("Arial","","11"); //fuente para tabla



$pdf->Cell(10,6,"",0,0);
$pdf->SetFont("Arial","B","11");
$pdf->Cell(7,6,"N",1,0,"C");
$pdf->Cell(45,6,"SISTEMA",1,0,"C");
$pdf->Cell(75,6,"USUARIO",1,0,"C");
$pdf->Cell(45,6,"CONTRASENA",1,1,"C");


$pdf->SetFont("Arial","","10");

//$pdf->Ln(10);


$pdf->Output(); 
}-->

