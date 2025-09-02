<?php
//Importamos la libreria de pdf
error_reporting(E_ALL);
ini_set('display_errors', 1);
require('C:\EJXAMPP\htdocs\Consultas\tfpdf\tfpdf.php');

// Se verificac la conexion post
if (!empty($_POST['submit'])) {


    //IMPORTAMOS LAS VARIABLES

    //DATOS DEL USUARIO
    $txt_DNI=$_POST['txt_DNI'];
    $txt_name=$_POST['txt_name'];
    $txt_ape_pat=$_POST['txt_ape_pat'];
    $txt_ape_mat=$_POST['txt_ape_mat'];
    $txt_cond_lab=$_POST['txt_cond_lab'];

    //RECUPERAR DATOS DE OFICINA
    $txt_ofic = $_POST['txt_ofic'];
    $txt_unid = $_POST['txt_unid'];
    $txt_area = $_POST['txt_area'];
    $txt_carg = $_POST['txt_carg'];
    $txt_IP = $_POST['txt_IP'];
    $txt_Externo = $_POST['txt_Externo'];

    //RECUPERAR DATOS DE SISTEMAS
    $array_Sistem=$_POST['checkboxes'];
    $num_Sistem=count($array_Sistem);
    //RECUPERAR LOS ACCECOS DE INTERNET
    $txt_opcional = $_POST['txt_opcional'];
    $txt_acceso_net = $_POST['txt_acceso_net'];

    //RECUPERAR DATOS DE EQUIPAMIENTO
    if (isset($_POST['checkboxes1'])) {
        $array_Equip = $_POST['checkboxes1'];
        $numEquip=count($array_Equip);
    } else {
        $array_Equip = '';// O maneja el caso cuando el campo está vacío
        $numEquip=-1;
    }

    if (isset($_POST['checkboxes_Autoriz'])){
        //RECUPERAR NUMERO DEL DOCUMENTO
        $txt_document = $_POST['txt_document'];
        $txt_dni_jef = $_POST['txt_dni_jef'];
        $txt_name_jef = $_POST['txt_name_jef'];
        $txt_ape_pat_jef = $_POST['txt_ape_pat_jef'];
        $txt_ape_mat_jef = $_POST['txt_ape_mat_jef'];
        $txt_carg_jef = $_POST['txt_carg_jef'];
    } else {
        //RECUPERAR DATOS DEL JEFE
        $txt_dni_jef = $_POST['txt_dni_jef'];
        $txt_name_jef = $_POST['txt_name_jef'];
        $txt_ape_pat_jef = $_POST['txt_ape_pat_jef'];
        $txt_ape_mat_jef = $_POST['txt_ape_mat_jef'];
        $txt_carg_jef = $_POST['txt_carg_jef'];
        $txt_document = '';
    }


    //------------------------------------------ EMPEZAR EL PDF --------------------------------------------------------

    $pdf=new tFPDF();
    $pdf->AddPage();
    $width=210; // Ancho of Current Page
    $height=297; // Altura of Current Page
    $edge=10; // Gap between line and border , change this value
    $fecha=date('d/m/20y');
    $hora=date('h:i:s');
    // FUENTE DE LETRA
    $pdf->AddFont('DejaVu','','Helvetica-Font/Helvetica.ttf',true);
    $pdf->AddFont('DejaVu', 'B', 'Helvetica-Font/Helvetica-Bold.ttf', true);
    //-------------------------------------- HEADER ------------------------------------------------
    $pdf->SetFont('DejaVu','B',12);
    $pdf->Cell(55,20,$pdf->Image('C:\ejxampp\htdocs\Consultas\IMG\logo.png',16,8,35),0,0,'C');
    //-------------PRIMER CUADRO--------------
    //----------------------------------------
    $pdf->Line(10, 6,$width-$edge,6); // Horizontal line at top
    $pdf->Line(10, 30,$width-$edge,30); // Horizontal line at bottom
    $pdf->Line(10, 6,10,30); // Vetical line at left
    $pdf->Line(53,6,53,30); //Linea separadora
    //TEXTO DEL MEDIO
    $pdf->SetFont("DejaVu","B","13");
    $pdf->Ln(1);
    $pdf->Cell(190,5,"Formato de Solicitud/Modificación de Acceso",0,0,"C");
    $pdf->Ln(8);
    $pdf->Cell(190,5,"a la plataforma de TICs SUNARP",0,0,"C");
    //-----------------------------------------------------------------------------
    $pdf->Line($width-$edge, 6,$width-$edge,30); // Vertical line at Right
    $pdf->Line($width-$edge-43, 6,$width-$edge-43,30); // separador
    $pdf->SetFont("Dejavu","","12");
    $pdf->ln(0);
    $pdf->Cell(168,-14,"Codigo:",0,0,'R');
    $pdf->SetFont("Dejavu","B","12");
    $pdf->Cell(20,-14,"F-002-OTI",0,0,'R');
    $pdf->Ln(0);
    $pdf->SetFont("Dejavu","","12");
    $pdf->Cell(170,-2,"Version:",0,0,'R');
    $pdf->SetFont("Dejavu","B","12");
    $pdf->Cell(10,-2,"v4.0",0,1,'R');
    $pdf->SetFont("Dejavu","","10");
    $pdf->Cell(170,14,"Clasificación:",0,0,'R');
    $pdf->SetFont("Dejavu","B","10");
    $pdf->Cell(20,14,"Uso Interno",0,0,'R');
    $pdf->ln(1);
    $pdf->Cell(150,10," ",0,1,'L');
    //

    //-------------------------------------- CUERPO DEL FORMATO ---------------------------
    $pdf->ln(3);
    $pdf->SetFont("Dejavu","B","12");
    // Configurar color de fondo alternado para las filas
    $pdf->SetFillColor(25, 159, 166);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(0, 6, 'Datos del Jefe Inmediato del Solicitante', 1, 1, 'C',true);
    $pdf->ln(1);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont("Dejavu","B","11");
    $pdf->Cell(45, 6, 'Apellidos y Nombres:', 1, 0, 'R');
    $pdf->SetFont("Dejavu","","11");
    $pdf->Cell(145, 6, "{$txt_name_jef} {$txt_ape_pat_jef} {$txt_ape_mat_jef}", 1, 1, 'L');
    $pdf->SetFont("Dejavu","B","11");
    $pdf->Cell(45, 6, 'Cargo: (jefe)', 1, 0, 'R');
    $pdf->SetFont("Dejavu","","11");
    $pdf->Cell(145, 6, "{$txt_carg_jef}", 1, 1, 'L');
    $pdf->SetFont("Dejavu","","10");
    $texto_Aprobacion="APRUEBO el acceso  solicitado  a  la  plataforma  TICs  de  el/la  trabajador/a indicado/a  en concordancia a su contrato laboral o de servicios y la normativa vigente en la SUNARP.";
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont("Dejavu","B","10");
    $texto_FinalVPN=" EN CASO DE ACCESO VPN:";
    $pdf->SetFont("Dejavu","","10");
    $texto_VPN="                                                                                 APRUEBO el acceso remoto a la plataforma TICs de el/la trabajador/a indicado/a, a quien se encuentra debidamente autorizado/a para realizar trabajo remoto o teletrabajo cumpliendo las características y condiciones establecidas en la normativa vigente.";
    $texto_FinalVPN=$texto_FinalVPN.$texto_VPN;
    $texto_completo = $texto_Aprobacion.$texto_FinalVPN;

    //CONDICION PARA EL SISTEMA VPN TUNEL O VPN WEB
    for($n=0;$n<$num_Sistem;$n++){
        $ntemp=$n+1;
        $temp=("$array_Sistem[$n]");
        if("{$temp}" == "VPN Tunel" || "{$temp}" == "VPN Web"){
            $temp_Message=("$array_Sistem[$n]");
            $Vpn_habilitado = "Si";
            break;
        }
        else{
            $temp_Message = " ";
            $Vpn_habilitado = "No";
        }
	}
    if("{$temp_Message}" == "VPN Tunel" || "{$temp_Message}" == "VPN Web"){
        $pdf->SetFont("Dejavu","B","11");
        $pdf->Cell(45, 36, "Aprobación:", 1, 0, 'R');
        $pdf->SetFont("Dejavu","","10");
        $pdf->MultiCell(145,6,$texto_completo, 1, 1, 'L');
    }
    else{
        $pdf->SetFont("Dejavu","B","11");
        $pdf->Cell(45, 12,"Aprobación:", 1, 0, 'R');
        $pdf->SetFont("Dejavu","","10");
        $pdf->MultiCell(145,6,$texto_Aprobacion, 1, 1, 'L');
    }
    $pdf->SetFont("Dejavu","B","11");
    $pdf->Cell(45, 6, 'Tipo de Solicitud:', 1, 0, 'R');
    $pdf->SetFont("Dejavu","","11");
    $pdf->Cell(35, 6, 'Alta de Acceso', 1, 0, 'C');
    $pdf->Cell(23, 6, 'F. Inicio:', 1, 0, 'C');
    $pdf->Cell(32, 6, '          ', 1, 0, 'C');
    $pdf->Cell(23, 6, 'F. Final:', 1, 0, 'C');
    $pdf->Cell(32, 6, '          ', 1, 0, 'C');

    // ---------- DATOS DEL USUARIO ------------------------------
    $pdf->ln(7);
    $pdf->SetFont("Dejavu","B","12");
    // Configurar color de fondo alternado para las filas
    $pdf->SetFillColor(25, 159, 166);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(0, 6, 'Datos de el/la Usuario/a', 1, 1, 'C',true);
    $pdf->ln(1);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont("Dejavu","B","11");
    $pdf->Cell(38, 6, 'Apellidos Paterno', 1, 0, 'C');
    $pdf->Cell(38, 6, 'Apellidos Materno', 1, 0, 'C');
    $pdf->Cell(38, 6, 'Nombres', 1, 0, 'C');
    $pdf->Cell(38, 6, 'DNI', 1, 0, 'C');
    $pdf->Cell(38, 6, 'Telefono Celular', 1, 1, 'C');
    $pdf->SetFont("Dejavu","","11");
    $pdf->Cell(38, 6, "{$txt_ape_pat}", 1, 0, 'C');
    $pdf->Cell(38, 6, "{$txt_ape_mat}", 1, 0, 'C');
    $pdf->Cell(38, 6, "{$txt_name}", 1, 0, 'C');
    $pdf->Cell(38, 6, "{$txt_DNI}", 1, 0, 'C');
    $pdf->Cell(38, 6, " ", 1, 1, 'C');
    $contadorCaracteres = strlen("{$txt_carg}");
    if ($contadorCaracteres<20) {
        $pdf->SetFont("Dejavu","B","11");
        $pdf->Cell(47.5, 6, 'Oficina', 1, 0, 'C');
        $pdf->Cell(47.5, 6, 'Unidad/Área', 1, 0, 'C');
        $pdf->Cell(47.5, 6, 'Cargo', 1, 0, 'C');
        $pdf->Cell(47.5, 6, 'Condicion Laboral', 1, 1, 'C');
        $pdf->SetFont("Dejavu","","11");
        $pdf->Cell(47.5, 6, "{$txt_ofic}", 1, 0, 'C');
        $pdf->Cell(47.5, 6, "{$txt_area}", 1, 0, 'C');
        $pdf->Cell(47.5, 6, "{$txt_carg}", 1, 0, 'C');
        $pdf->Cell(47.5, 6, "{$txt_cond_lab}", 1, 1, 'C');
    } else {
        $pdf->SetFont("Dejavu","B","11");
        $pdf->Cell(63.33, 6, 'Oficina', 1, 0, 'C');
        $pdf->Cell(63.33, 6, 'Unidad/Área', 1, 0, 'C');
        $pdf->Cell(63.34, 6, 'Condicion Laboral', 1, 1, 'C');
        $pdf->SetFont("Dejavu","","11");
        $pdf->Cell(63.33, 6, "{$txt_ofic}", 1, 0, 'C');
        $pdf->Cell(63.33, 6, "{$txt_area}", 1, 0, 'C');
        $pdf->Cell(63.34, 6, "{$txt_cond_lab}", 1, 1, 'C');
        $pdf->SetFont("Dejavu","B","11");
        $pdf->Cell(0, 6, 'Cargo', 1, 1, 'C');
        $pdf->SetFont("Dejavu","","11");
        $pdf->Cell(0, 6, "{$txt_carg}", 1, 1, 'C');
    }
    $pdf->SetFillColor(206, 210, 201);
    // $pdf->Cell(0,1, "",'LR', 1, 'L');
    // $pdf->Cell(0, 6, "Información o comentario que considere relevante (es obligatorio en caso condición laboral es Otro)", 'LR', 1, 'L',true);
    // $pdf->Cell(0,7, "{$txt_Externo}",'LRB', 1, 'L');

    //--------------------------- ACCESOS SOLICITADOS -------------------------------------
    $pdf->ln(2);
    $pdf->SetFont("Dejavu","B","12");
    // Configurar color de fondo alternado para las filas
    $pdf->SetFillColor(25, 159, 166);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(0, 6, 'Accesos Solicitado', 1, 1, 'C',true);
    $pdf->ln(1);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont("Dejavu","","11");
    if ($num_Sistem<=6){
        for($n=0;$n<$num_Sistem;$n++){
            $temp=("$array_Sistem[$n]");
            $pdf->Cell(0,6,"$temp",1,1,"L");
        }
    } else {
        if ($num_Sistem<12) {
            $RestaProv=5-(10-$num_Sistem);
            for($n=0;$n<5;$n++){
                $temp=("$array_Sistem[$n]");
                if($RestaProv==0){
                    $Aux=$n+5;
                    $temp2=("$array_Sistem[$Aux]");
                } else {
                    if($n<$RestaProv){
                        $Aux=$n+5;
                        $temp2=("$array_Sistem[$Aux]");
                    } else {
                        $temp2=" ";
                    }
                }
                $pdf->Cell(95,6,"$temp",1,0,"L");
                $pdf->Cell(95,6,"$temp2",1,1,"L");
            }
        } else {
            $RestaProv=6-(18-$num_Sistem);
            for($n=0;$n<6;$n++){
                $nTemp=$n+1;
                $temp=("$array_Sistem[$n]");
                $Aux=$n+5;
                $temp2=("$array_Sistem[$Aux]");
                if($RestaProv==0){
                    $Aux1=$n+12;
                    $temp3=("$array_Sistem[$Aux1]");
                }else{
                    if($n<$RestaProv){
                        $Aux1=$n+12;
                        $temp3=("$array_Sistem[$Aux1]");
                    }else{
                        $temp3=" ";
                    }
                }
                $pdf->Cell(63.33,6,"$temp",1,0,"L");
                $pdf->Cell(63.33,6,"$temp2",1,0,"L");
                $pdf->Cell(63.34,6,"$temp3",1,1,"L");
            }
        }
    }

    //---------- CONDICION DE ACCESO A INTERNET ---------------
    for($n=0;$n<$num_Sistem;$n++){
        $ntemp=$n+1;
        $temp=("$array_Sistem[$n]");
        if("{$temp}" == "Acceso a Internet"){
            $temp_AWeb=("$array_Sistem[$n]");
            $pdf->ln(2);
            $pdf->SetFont("Dejavu","B","11");
            $pdf->Cell(0, 6, "Precisar los ACCESOS A INTERNET que se le otorgara:", 'LRT', 1, 'L');
            $pdf->SetFont("Dejavu","","11");
            $pdf->Cell(0,7, "{$txt_acceso_net}",'LRB', 1, 'L');
        }
	}
    $contadorCarac = strlen("{$txt_opcional}");
    if($contadorCarac>0){
        $pdf->ln(2);
        $pdf->SetFont("Dejavu","B","12");
        // Configurar color de fondo alternado para las filas
        $pdf->SetFillColor(25, 159, 166);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(0, 6, 'Especifique Solicitud', 1, 1, 'C',true);
        $pdf->ln(1);
        $pdf->SetFont("Dejavu","","11");
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->MultiCell(0,6, "{$txt_opcional}",1, 1, 'J');

    }
    //--------------------------- EQUIPAMIENTO SOLICITADOS -------------------------------------
    if ($numEquip>-1){
        $pdf->ln(2);
        $pdf->SetFont("Dejavu","B","12");
        // Configurar color de fondo alternado para las filas
        $pdf->SetFillColor(25, 159, 166);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(0, 6, 'Equipamiento ', 1, 1, 'C',true);
        $pdf->ln(1);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont("Dejavu","","12");
        if($numEquip<=3){
            for($n=0;$n<$numEquip;$n++)
            {
                $ntemp=$n+1;
                $temp=("$array_Equip[$n]");
                $pdf->Cell(0,6,"$temp",1,1,"L");
            }
        }else{
            $RestaProv=3-(6-$numEquip);
            for($n=0;$n<3;$n++)
            {
                $temp=("$array_Equip[$n]");
                if($RestaProv==0){
                    $Aux1=$n+3;
                    $temp4=("$array_Equip[$Aux1]");
                }else{
                    if($n<$RestaProv){
                        $Aux1=$n+3;
                        $temp4=("$array_Equip[$Aux1]");
                    }else{
                        $temp4=" ";
                    }
                }
                $pdf->Cell(95,6,"$temp",1,0,"L");
                $pdf->Cell(95,6,"$temp4",1,1,"L");
            }
        }
    }

    $contadorTextDoc = strlen("{$txt_document}");
    if($contadorTextDoc>0){
        $pdf->ln(2);
        $pdf->SetFont("Dejavu","B","12");
        // Configurar color de fondo alternado para las filas
        $pdf->SetFillColor(25, 159, 166);
        $pdf->SetTextColor(255,255,255);
        $pdf->Cell(0, 6, 'Autorizado con documento', 1, 1, 'C',true);
        $pdf->ln(1);
        $pdf->SetFont("Dejavu","B","11");
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(0,6, "{$txt_document}",1, 1, 'J');
    }
    // ESPACIO DE LA FIRMA
    $pdf->ln(2);
    $pdf->SetFont("Dejavu","B","12");
    // Configurar color de fondo alternado para las filas
    $pdf->SetFillColor(25, 159, 166);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(0, 6, 'Firma', 1, 1, 'C',true);
    $pdf->ln(1);
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(0, 6, 'JEFE INMEDIATO', 1, 1, 'C');
    $pdf->SetFont("Dejavu","","12");
    $pdf->Cell(0, 16, '','LR', 1, 'C');
    $pdf->Cell(0, 6, "Nombre: {$txt_name_jef} {$txt_ape_pat_jef} {$txt_ape_mat_jef}",'LR', 1, 'C');
    $pdf->Cell(0, 6, "Fecha(dd/mm/aaaa): {$fecha}",'LRB', 1, 'C');

    // DATOS LLENADOS POR PERSONAL UTI
    $pdf->ln(2);
    $pdf->SetFont("Dejavu","B","12");
    // Configurar color de fondo alternado para las filas
    $pdf->SetFillColor(25, 159, 166);
    $pdf->SetTextColor(255,255,255);
    // $pdf->SetLineWidth(1);
    $pdf->Cell(0, 6, 'Datos a ser llenados por el personal de la OTI/UTI o Mesa de Ayuda', 1, 1, 'C',true);
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(42, 6, 'Solicitud Conforme', 1, 0, 'C');
    $pdf->Cell(38, 6, 'Factibilidad Tec.', 1, 0, 'C');
    $pdf->Cell(36, 6, 'VPN Habilitada', 1, 0, 'C');
    $pdf->Cell(42, 6, 'Acceso configurado', 1, 0, 'C');
    $pdf->Cell(32,6, 'Estado Final', 1, 1, 'C');
    $pdf->SetFont("Dejavu","","12");
    $pdf->Cell(42, 6, 'Si', 1, 0, 'C');
    $pdf->Cell(38, 6, 'Si', 1, 0, 'C');
    $pdf->Cell(36, 6, $Vpn_habilitado, 1, 0, 'C');
    $pdf->Cell(42, 6, 'Si', 1, 0, 'C');
    $pdf->SetFillColor(63, 187, 24);
    $pdf->Cell(32,6, ' ', 'LRT', 1, 'C',true);
    $pdf->SetFont("Dejavu","B","12");
    $pdf->Cell(42, 6, 'HOST PC', 1, 0, 'C');
    $pdf->Cell(38, 6, 'IP Asignada', 1, 0, 'C');
    $pdf->Cell(36, 6, 'Usuario Red', 1, 0, 'C');
    $pdf->Cell(42, 6, 'Usuario Correo', 1, 0, 'C');
    $pdf->SetFillColor(63, 187, 24);
    $pdf->Cell(32,6, 'ATENDIDO', 'LR', 1, 'C',true);
    $pdf->SetFont("Dejavu","","12");
    $pdf->Cell(42, 6, ' ', 1, 0, 'C');
    $pdf->Cell(38, 6, "{$txt_IP}", 1, 0, 'C');
    $pdf->Cell(36, 6, ' ', 1, 0, 'C');
    $pdf->Cell(42, 6, ' ', 1, 0, 'C');
    $pdf->SetFillColor(63, 187, 24);
    $pdf->Cell(32,6, ' ', 'LRB', 1, 'C',true);
    $pdf->Cell(0,1, ' ', 'LR', 1, 'C');
    $pdf->SetFillColor(206, 210, 201);
    $pdf->Cell(0,6, 'Información u observación que considere relevante: (es opcional)', 'LR', 1, 'L',true);
    $pdf->Cell(0,7, ' ', 'LRB', 1, 'C');

    //PSI
    if (isset($_POST['checkboxes']) && in_array('PSI', $_POST['checkboxes'])) {
        //Agregando PSI 2025 :v 

        //Variables Notarios
        $moduloNotarios = isset($_POST['modulo_psi_Notarios']) ? 'X' : ' ';
        $moduloNotarios1 = isset($_POST['modulo_notarios_1']) ? 'X' : ' ';
        $moduloNotarios2 = isset($_POST['modulo_notarios_2']) ? 'X' : ' ';
        $moduloNotarios3 = isset($_POST['modulo_notarios_3']) ? 'X' : ' ';
        // Variables Empresas
        $moduloEmpresas = isset($_POST['modulo_psi_Empresas']) ? 'X' : ' ';
        $moduloEmpresas1 = isset($_POST['modulo_empresas_1']) ? 'X' : ' ';
        $moduloEmpresas2 = isset($_POST['modulo_empresas_2']) ? 'X' : ' ';
        $moduloEmpresas3 = isset($_POST['modulo_empresas_3']) ? 'X' : ' ';

        // Variables Seguridad
        $moduloSeguridad = isset($_POST['modulo_psi_Seguridad']) ? 'X' : ' ';
        $moduloSeguridad1 = isset($_POST['modulo_seguridad_1']) ? 'X' : ' ';
        $moduloSeguridad2 = isset($_POST['modulo_seguridad_2']) ? 'X' : ' ';

        // Variables Verificadores
        $moduloVerificadores = isset($_POST['modulo_psi_Verificadores']) ? 'X' : ' ';
        $moduloVerificadores1 = isset($_POST['modulo_Verificadores_1']) ? 'X' : ' ';
        $moduloVerificadores2 = isset($_POST['modulo_Verificadores_2']) ? 'X' : ' ';
        $moduloVerificadores3 = isset($_POST['modulo_Verificadores_3']) ? 'X' : ' ';

        // Variables Entidades
        $moduloEntidades = isset($_POST['modulo_psi_Entidades']) ? 'X' : ' ';
        $moduloEntidades1 = isset($_POST['modulo_Entidades_1']) ? 'X' : ' ';
        $moduloEntidades2 = isset($_POST['modulo_Entidades_2']) ? 'X' : ' ';

        // Variables Municipalidades
        $moduloMunicipalidades = isset($_POST['modulo_psi_Municipalidades']) ? 'X' : ' ';
        $moduloMunicipalidades1 = isset($_POST['modulo_muni_1']) ? 'X' : ' ';
        $moduloMunicipalidades2 = isset($_POST['modulo_muni_2']) ? 'X' : ' ';
        $moduloMunicipalidades3 = isset($_POST['modulo_muni_3']) ? 'X' : ' ';

        // Variables Servicios PIDE
        $moduloPIDE = isset($_POST['modulo_psi_PIDE']) ? 'X' : ' ';
        $moduloPIDE1 = isset($_POST['modulo_pide_1']) ? 'X' : ' ';
        $moduloPIDE2 = isset($_POST['modulo_pide_2']) ? 'X' : ' ';
        $moduloPIDE3 = isset($_POST['modulo_pide_3']) ? 'X' : ' ';
        $moduloPIDE4 = isset($_POST['modulo_pide_4']) ? 'X' : ' ';
        $moduloPIDE5 = isset($_POST['modulo_pide_5']) ? 'X' : ' ';
        $moduloPIDE6 = isset($_POST['modulo_pide_6']) ? 'X' : ' ';
        $moduloPIDE7 = isset($_POST['modulo_pide_7']) ? 'X' : ' ';
        $moduloPIDE8 = isset($_POST['modulo_pide_8']) ? 'X' : ' ';
        $moduloPIDE9 = isset($_POST['modulo_pide_9']) ? 'X' : ' ';

        //Correo electronico
        $correoPSI = isset($_POST['PSI_correo']) ? $_POST['PSI_correo'] : '';
        // Recuperar motivo/sustento
        $motivoPSI = isset($_POST['PSI_MotivoSustento']) ? $_POST['PSI_MotivoSustento'] : '';

        //Unidad Organiacional
        $unidadPSI = isset($_POST['txt_unid']) ? $_POST['txt_unid'] : '';






        //Definir colores
        $colorCeleste = [198, 217, 241];
        $ColorCeClaro=[218, 238, 243];
        $ColorPlomo=[238, 236, 225];

        // NUEVA PÁGINA: FORMATO PSI
        $pdf->AddPage();

        

        // Título principal centrado
        $pdf->SetTextColor(84, 141, 213);
        $pdf->SetFont("DejaVu", "B", 12);
        $pdf->Cell(55,20,$pdf->Image('C:\ejxampp\htdocs\Consultas\IMG\logo.png',16,8,35),0,0,'L');
        $pdf->Cell(0, 6, 'FORMATO DE SOLICITUD DE ACCESO A LA', 0, 1, 'L');
        $pdf->Cell(0, 6, '         PLATAFORMA DE SERVICIOS INSTITUCIONALES (PSI)', 0, 1, 'C');
        $pdf->Ln(8);
        $pdf->SetTextColor(0, 0, 0);

        // Sección: DATOS DEL USUARIO
        $pdf->SetFillColor(...$colorCeleste);
        $pdf->SetFont("DejaVu", "B", 10);
        $pdf->Cell(0, 6, 'DATOS DEL USUARIO', 1, 1, 'L', true);
        $pdf->SetFont("DejaVu", "", 9);

        // Definir ancho de columnas
        $col1 = 65;
        $col2 = 125;

        // Filas con etiquetas y datos
        $pdf->SetFillColor(...$ColorCeClaro);
        $pdf->Cell($col1, 5, 'Apellido Paterno', 1, 0, 'L',true);
        $pdf->Cell($col2, 5, $txt_ape_pat, 1, 1, 'L');

        $pdf->Cell($col1, 5, 'Apellido Materno', 1, 0, 'L',true);
        $pdf->Cell($col2, 5, $txt_ape_mat, 1, 1, 'L');

        $pdf->Cell($col1, 5, 'Nombres', 1, 0, 'L',true);
        $pdf->Cell($col2, 5, $txt_name, 1, 1, 'L');

        $pdf->Cell($col1, 5, 'DNI', 1, 0, 'L',true);
        $pdf->Cell($col2, 5, $txt_DNI, 1, 1, 'L');

        $pdf->Cell($col1, 5, 'Correo Electrónico', 1, 0, 'L',true);
        $pdf->Cell($col2, 5, $correoPSI, 1, 1, 'L'); 

        $pdf->Cell($col1, 5, 'Cargo', 1, 0, 'L',true);
        $pdf->Cell($col2, 5, $txt_carg, 1, 1, 'L');

        $pdf->Cell($col1, 5, 'Usuario PSI', 1, 0, 'L',true);
        $pdf->Cell($col2, 5, '---', 1, 1, 'L'); // Puedes agregar variable si aplica con información de usuarios PSI


        // DATOS DE LA UNIDAD ORGANIZACIONAL 
        $pdf->SetFillColor(...$colorCeleste);
        $pdf->Ln(4); // espacio
        $pdf->SetFont("DejaVu", "B", 10);
        $pdf->Cell(0, 8, 'DATOS DE LA UNIDAD ORGANIZACIONAL', 1, 1, 'L', true);
        $pdf->SetFont("DejaVu", "", 9);
        $pdf->SetFillColor(...$ColorCeClaro);

        $pdf->Cell($col1, 5, 'Unidad Organizacional', 1, 0, 'L', true);
        $pdf->Cell($col2, 5, $unidadPSI, 1, 1, 'L');

        $pdf->Cell($col1, 5, 'Zona Registral', 1, 0, 'L', true);
        $pdf->Cell($col2, 5, 'Zona Registral N° X', 1, 1, 'L');

        $pdf->Cell($col1, 5, 'Oficina Registral', 1, 0, 'L', true);
        $pdf->Cell($col2, 5, $txt_ofic, 1, 1, 'L');

        $pdf->Cell($col1, 5, 'Zonal / Receptora', 1, 0, 'L', true);
        $pdf->Cell($col2, 5, 'ZRX', 1, 1, 'L');

        $pdf->Cell($col1, 5, 'Anexo', 1, 0, 'L', true);
        $pdf->Cell($col2, 5, '8444', 1, 1, 'L');

        // DATOS DEL ACCESO
        $pdf->SetFillColor(...$colorCeleste);
        $pdf->Ln(4);
        $pdf->SetFont("DejaVu", "B", 10);
        $pdf->Cell(0, 6, 'DATOS DEL ACCESO', 1, 1, 'L', true);
        $pdf->SetFont("DejaVu", "", 9);

        $col01 = 35;
        $col02 = 80;
        $col03= 75;

        // Lista de módulos
        // Guardar posición inicial
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $pdf->SetFillColor(...$ColorCeClaro);
        // Columna 1
        $pdf->MultiCell($col01, 6, "Indicar los módulos a la cual se le brindará acceso\n \n", 1, 'L',true);

        // Volver a la posición inicial + ancho de col01
        $pdf->SetXY($x + $col01, $y);

        // Columna 2
        $modulos1 = "($moduloNotarios) Módulo de Notarios\n($moduloEmpresas) Módulo de Empresas\n($moduloSeguridad) Módulo de Seguridad\n($moduloVerificadores) Módulo de Verificadores";
        $pdf->MultiCell($col02, 6, $modulos1, 'B', 'L');

        // Volver a la posición inicial + ancho de col01 + col02
        $pdf->SetXY($x + $col01 + $col02, $y);

        // Columna 3
        $modulos2 = "($moduloEntidades) Módulo de Entidades\n($moduloPIDE) Servicios PIDE\n($moduloMunicipalidades) Módulo de Municipalidades\n \n";
        $pdf->MultiCell($col03, 6, $modulos2, 'RB', 'L',);

    // Definir anchos de columnas
        $col001 = 35;
        $col002 = 55;
        $col003= 55;
        $col004=45;

    

        // Primera fila de encabezados (módulos)
        $pdf->Cell($col001, 5, 'Indicar Rol', 'L', 0, 'L',true);
        $pdf->SetFont("DejaVu", "B", 9);

        $pdf->SetFillColor(...$ColorPlomo);
        $pdf->Cell($col002, 5, 'Módulo de Notarios', 1, 0, 'C',true);
        $pdf->Cell($col003, 5, 'Módulo de Empresas', 1, 0, 'C',true);
        $pdf->Cell($col004, 5, 'Módulo de Seguridad', 1, 1, 'C',true);

        // Filas de roles
        $pdf->SetFont("DejaVu", "", 10);
        $pdf->SetFillColor(...$ColorCeClaro);
        $pdf->Cell($col001, 5, 'del acceso', 'L', 0, 'L',true);
        $pdf->Cell($col002, 5, '(' . $moduloNotarios1 . ') Consulta', 'L', 0, 'L');
        $pdf->Cell($col003, 5, '(' . $moduloEmpresas1 . ') Consulta', 'LR', 0, 'L');
        $pdf->Cell($col004, 5, '( ' . $moduloSeguridad1 . ') Administración Zonal', 'R', 1, 'L');

        $pdf->Cell($col001, 5, '', 'L', 'L', 'L', true);
        $pdf->Cell($col002, 5, '(' . $moduloNotarios2 . ') Registro', 'L', 0, 'L');
        $pdf->Cell($col003, 5, '(' . $moduloEmpresas2 . ' ) Registro', 'LR', 0, 'L');
        $pdf->Cell($col004, 5, '(' . $moduloSeguridad2 . ' ) Administración', 'R', 1, 'L');

        $pdf->Cell($col001, 5, '', 'L', 'L', 'L', true);
        $pdf->Cell($col002, 5, '(' . $moduloNotarios3 . ') Administración', 'L', 0, 'L');
        $pdf->Cell($col003, 5, '( ' . $moduloEmpresas3 . ') Administración', 'LR', 0, 'L');
        $pdf->Cell($col004, 5, '     (solo para SC)', 'R', 1, 'L');

        // Segunda fila de encabezados (módulos)
        $pdf->SetFillColor(...$ColorCeClaro);
        $pdf->SetFont("DejaVu", "B", 9);
        $pdf->Cell($col001, 5, '', 'L', 0, 'L', true);
        $pdf->SetFillColor(...$ColorPlomo);
        $pdf->Cell($col002, 5, 'Módulo de Verifica', 1, 0, 'C',true);
        $pdf->Cell($col003, 5, 'Módulo de Entidad', 1, 0, 'C',true);
        $pdf->Cell($col004, 5, 'Módulo de Munis', 1, 1, 'C',true);

        // Filas de roles
        $pdf->SetFillColor(...$ColorCeClaro);
        $pdf->SetFont("DejaVu", "", 9);
        $pdf->Cell($col001, 5, '', 'L', 0, 'L', true);
        $pdf->Cell($col002, 5, '(' . $moduloVerificadores1 . ') Consulta', 'L', 0, 'L');
        $pdf->Cell($col003, 5, '(' . $moduloEntidades1 . ') Consulta', 'LR', 0, 'L');
        $pdf->Cell($col004, 5, '( ' . $moduloMunicipalidades1 . ') Consulta', 'R', 1, 'L');

        $pdf->Cell($col001, 5, '', 'L', 0, 'L', true);
        $pdf->Cell($col002, 5, '(' . $moduloVerificadores2 . ') Registrador', 'L', 0, 'L');
        $pdf->Cell($col003, 5, '(' . $moduloEntidades2 . ') Administración', 'RL', 0, 'L');
        $pdf->Cell($col004, 5, '( ' . $moduloMunicipalidades2 . ') Municipalidad', 'R', 1, 'L');

        $pdf->Cell($col001, 5, '', 'L', 0, 'L', true);
        $pdf->Cell($col002, 5, '( ' . $moduloVerificadores3 . ') Administración', 'L', 0, 'L');
        $pdf->Cell($col003, 5, '', 'LR', 0, 'L');
        $pdf->Cell($col004, 5, '(' . $moduloMunicipalidades3 . ' ) Administración', 'R', 1, 'L');

        // Encabezado de Servicios PIDE (una sola celda)
        $pdf->SetFillColor(...$ColorCeClaro);
        $pdf->SetFont("DejaVu", "B", 9);
        $pdf->Cell($col001, 8, '', 'L', 0, 'L', true);
        $pdf->SetFillColor(...$ColorPlomo);
        $pdf->Cell(0, 8, 'SERVICIOS PIDE', 1, 1, 'C', true);

        // Casillas PIDE: organizadas en 3 columnas × 3 filas
        $pideCols = 3;
        $pideWidth = 190 / $pideCols;
        $pdf->SetFont("DejaVu", "", 10);

        // Primera fila
        $pdf->SetFillColor(...$ColorCeClaro);
        $pdf->Cell($col001, 5, '', 'L', 0, 'L', true);
        $pdf->Cell($col002, 5, '(' . $moduloPIDE1 . ') RENIEC', 'L', 0, 'L');
        $pdf->Cell($col003, 5, '(' . $moduloPIDE4 . ') Poder Judicial', 0, 0, 'L');
        $pdf->Cell($col004, 5, '(' . $moduloPIDE7 . ') Servir', 'R', 1, 'L');

        // Segunda fila
        $pdf->Cell($col001, 5, '', 'L', 0, 'L', true);
        $pdf->Cell($col002, 5, '(' . $moduloPIDE2 . ') Inpe', 'L', 0, 'L');
        $pdf->Cell($col003, 5, '(' . $moduloPIDE5 . ') Sunedu', 0, 0, 'L');
        $pdf->Cell($col004, 5, '(' . $moduloPIDE8 . ') DS 21-2019-VIVIENDA', 'R', 1, 'L');

        // Tercera fila
        $pdf->Cell($col001, 5, '', 'LB', 0, 'L', true);
        $pdf->Cell($col002, 5, '(' . $moduloPIDE3 . ') PNP', 'LB', 0, 'L');
        $pdf->Cell($col003, 5, '(' . $moduloPIDE6 . ') Migraciones', 'B', 0, 'L');
        $pdf->Cell($col004, 5, '(' . $moduloPIDE9 . ') MINJUS', 'RB', 1, 'L');


        // Motivo o Sustento
        $pdf->SetFillColor(...$colorCeleste);
        $pdf->Ln(4);
        $pdf->SetFont("DejaVu", "B", 10);
        $pdf->Cell(0, 8, 'INDICAR MOTIVO/SUSTENTO:', 1, 1, 'L', true);
        $pdf->SetFont("DejaVu", "", 10);
        $pdf->Cell(0, 20, $motivoPSI, 1, 1);

        // Autorización
        $pdf->Ln(2);
        $pdf->SetFont("DejaVu", "B", 10);
        $pdf->Cell(0, 8, 'Autorizado por:', 1, 1, 'L', true);
        $pdf->Cell(0, 20, '', 1, 1);
        $pdf->Cell(0, 8, 'Firma y Sello', 0, 1, 'C');
        $pdf->Output();
    }
$nombreArchivo = "FSA- " . $txt_ape_pat ." " . $txt_ape_mat .  " " . $txt_name . ".pdf";
$pdf->Output("I", $nombreArchivo);
}
else{
}
?>