<?php
session_start();
// Verificar si hay una sesión activa
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
// Acción para buscar usuarios dinámicamente mientras se escribe
if (isset($_GET['action']) && $_GET['action'] == 'buscar_usuarios' && isset($_GET['term'])) {
    $username = $_SESSION['username'];
    $password = $_SESSION['password'];
    $searchTerm = $_GET['term'];

    // Conectar a Oracle
    $conn = oci_connect($username, $password, "//172.20.100.37:1531/OR0601", "AL32UTF8");

    if (!$conn) {
        echo json_encode(['error' => 'Error de conexión a la base de datos']);
        exit;
    }

    // Consulta para buscar usuarios que coincidan con el término
    $sql = "SELECT 
                NRO_DOCU_EMPL AS DNI, 
                NOMB_EMPL AS NOMBRES, 
                APE_PATE_EMPL AS APELLIDO_PATERNO, 
                APE_MATE_EMPL AS APELLIDO_MATERNO
            FROM uti.t_empl
            WHERE LOWER(NOMB_EMPL) LIKE LOWER(:searchTerm)
               OR LOWER(APE_PATE_EMPL) LIKE LOWER(:searchTerm)
               OR LOWER(APE_MATE_EMPL) LIKE LOWER(:searchTerm)
               OR NRO_DOCU_EMPL LIKE :searchTerm";

    $stmt = oci_parse($conn, $sql);
    $searchWildcard = "%" . $searchTerm . "%";
    oci_bind_by_name($stmt, ':searchTerm', $searchWildcard);
    
    oci_execute($stmt);

    // Obtener resultados
    $results = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $results[] = [
            'id' => $row['DNI'],
            'text' => "{$row['NOMBRES']} {$row['APELLIDO_PATERNO']} {$row['APELLIDO_MATERNO']} ({$row['DNI']})",
            'dni' => $row['DNI']
        ];
    }

    // Cerrar la conexión
    oci_free_statement($stmt);
    oci_close($conn);

    // Devolver el resultado como JSON
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}
// Variable para el estado de conexión
$connection_status = "";


// Función de búsqueda - procesamiento AJAX
if (isset($_GET['action']) && $_GET['action'] == 'buscar' && isset($_GET['dni_buscar'])) {
    // Recuperar las credenciales de la sesión
    $username = $_SESSION['username'];
    $password = $_SESSION['password'];
    $busqueda = $_GET['dni_buscar'];

    // Conectar a Oracle
    $conn = oci_connect($username, $password, "//172.20.100.37:1531/OR0601", "AL32UTF8");

    if (!$conn) {
        echo json_encode(['error' => 'Error de conexión a la base de datos']);
        exit;
    }

    // Preparar la consulta SQL - Corregida para usar la tabla uti.t_empl
    $sql = "SELECT 
                te.NRO_DOCU_EMPL AS DNI, 
                te.NOMB_EMPL AS NOMBRES, 
                te.APE_PATE_EMPL AS APELLIDO_PATERNO, 
                te.APE_MATE_EMPL AS APELLIDO_MATERNO,
                ta.nomb_area_unid AS AREA,
                tvl.VALO_COLU AS CONDICION_LABORAL,  
                tof.desc_ofic AS OFICINA,
                tpc.NOMB_PSTO AS CARGO
            FROM uti.t_empl te 
            LEFT JOIN uti.t_area_unid ta ON te.id_area_unid = ta.id_area_unid
            LEFT JOIN uti.t_tabl tvl ON te.TIPO_VINC_LABO = tvl.CO_COLU  
            LEFT JOIN uti.t_ofic tof ON te.ID_OFIC = tof.ID_OFIC 
            LEFT JOIN uti.t_psto_chk tpc ON te.ID_PSTO = tpc.ID_PSTO 
            WHERE NRO_DOCU_EMPL = :busqueda
            OR NOMB_EMPL LIKE :busqueda_like 
            OR APE_PATE_EMPL LIKE :busqueda_like 
            OR APE_MATE_EMPL LIKE :busqueda_like
            OR nomb_area_unid LIKE :busqueda_like
            OR VALO_COLU LIKE :busqueda_like
            OR desc_ofic LIKE :busqueda_like
            OR NOMB_PSTO LIKE :busqueda_like";

    $stmt = oci_parse($conn, $sql);
    $busqueda_like = '%' . $busqueda . '%';
    oci_bind_by_name($stmt, ':busqueda', $busqueda);
    oci_bind_by_name($stmt, ':busqueda_like', $busqueda_like);
    oci_execute($stmt);

    // Obtener resultados
    $result = [];
    if ($row = oci_fetch_assoc($stmt)) {
        $result = [
            'dni' => $row['DNI'],
            'nombres' => $row['NOMBRES'],
            'apellido_paterno' => $row['APELLIDO_PATERNO'],
            'condicion_laboral' => $row['CONDICION_LABORAL'],
            'oficina_usuario' => $row['OFICINA'],
            'cargo' => $row['CARGO'],
            'area' => $row['AREA'],
            'apellido_materno' => $row['APELLIDO_MATERNO']
        ];
    } else {
        $result = ['error' => 'No se encontraron resultados'];
    }

    // Cerrar la conexión
    oci_free_statement($stmt);
    oci_close($conn);

    // Devolver el resultado como JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Verificar conexión a la base de datos
function checkDatabaseConnection()
{
    if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
        $username = $_SESSION['username'];
        $password = $_SESSION['password'];
        $conn = oci_connect($username, $password, "//172.20.100.37:1531/OR0601", "AL32UTF8");

        if ($conn) {
            oci_close($conn);
            return true;
        }
    }
    return false;
}

// Comprobar estado de conexión
$is_connected = checkDatabaseConnection();
$connection_status = $is_connected ?
    '<span class="badge bg-success">Conectado</span>' :
    '<span class="badge bg-danger">Desconectado</span>';

// Cierre de sesión
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Destruir todas las variables de sesión
    $_SESSION = array();

    // Si se desea destruir la sesión completamente, borrar también la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Finalmente, destruir la sesión
    session_destroy();

    // Redireccionar a la página de login
    header("Location: login.php");
    exit;
}
header('Content-Type: 1| +/html; charset=ISO-8859-1'); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUNARP - Cargo de Accesos</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

    <!-- Custom CSS -->
    <style>
    :root {
        --sunarp-blue: #0056b3;
        --sunarp-light-blue: #e9f0f8;
        --sunarp-dark: #343a40;
    }

    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .navbar-brand {
        font-weight: 700;
        color: var(--sunarp-blue);
        color: white;
    }

    .navbar {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: none;
        margin-bottom: 20px;
    }

    .card-header {
        background-color: var(--sunarp-blue);
        color: white;
        border-radius: 10px 10px 0 0 !important;
        padding: 15px 20px;
    }

    .form-section {
        background-color: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .required-field::after {
        content: " *";
        color: #dc3545;
    }

    .system-group {
        margin-bottom: 15px;
    }

    .system-checkbox {
        margin-bottom: 8px;
    }

    .system-checkbox label {
        font-weight: 500;
    }

    .btn-sunarp {
        background-color: var(--sunarp-blue);
        color: white;
        padding: 10px 25px;
        font-weight: 500;
    }

    .btn-sunarp:hover {
        background-color: #004494;
        color: white;
    }

    .search-container {
        background-color: var(--sunarp-light-blue);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
    }

    .status-badge {
        font-size: 0.85rem;
        padding: 5px 10px;
        border-radius: 50px;
    }

    .nav-link.active {
        font-weight: 600;
        color: var(--sunarp-blue) !important;
        border-bottom: 2px solid var(--sunarp-blue);
    }

    .system-category {
        margin-bottom: 25px;
    }

    .system-category h5 {
        color: var(--sunarp-blue);
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
        margin-bottom: 15px;
    }

    .input-group-text {
        background-color: #f8f9fa;
    }

    .toast-container {
        z-index: 1100;
    }

    .form-check-input {
        border-color:rgb(189, 189, 253);
    }

    @media (max-width: 768px) {
        .form-section {
            padding: 15px;
        }
    }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="">
                <img src="IMG\logo.png" alt="SUNARP" height="40" class="me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link " aria-current="page" href="index_MDA.php">
                            <i class="bi bi-person-vcard-fill"></i> MDA
                        </a>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link active" onclick="window.location.href='index_cargo.php'">
                            <i class="bi bi-briefcase"></i> Cargo
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " aria-current="page" href="Directorio.php">
                            <i class="bi bi-person-vcard-fill"></i> Directorio
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-inline">Estado: <?php echo $connection_status; ?></span>

                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo $_SESSION['username'] ?? 'Usuario'; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href=""><i class="bi bi-person me-2"></i>Mi perfil</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="?action=logout"><i
                                    class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
                    </ul>
                </div>
            </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0 text-primary"><i class="bi bi-key me-2"></i> Cargo de Accesos</h1>
                    <nav aria-label="breadcrumb">
                    </nav>
                </div>
                <hr class="mt-2">
            </div>
        </div>

        <!-- Main Form -->
        <form class="form-horizontal" action="formPDFcargo.php" method="post">
            <div class="row">
                <!-- Left Column - User Data -->
                <div class="col-lg-5">
                    <!-- Search Section -->
                    <div class="search-container mb-4">
                        <h4 class="mb-3"><i class="bi bi-search me-2"></i> Buscar usuario</h4>
                        <div class="input-group mb-3">
                            <select class="form-select select2-search" id="txt_DNI_Buscar"
                                name="txt_DNI_Buscar"></select>
                            <button type="button" class="btn btn-primary" id="btnBuscar">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                        </div>
                        <div class="alert alert-info py-2">
                            <i class="bi bi-info-circle me-2"></i>
                            Busque por DNI, nombres o apellidos. El sistema completará automáticamente los campos.
                        </div>
                    </div>

                    <!-- User Data Section -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i> Datos del usuario</h5>
                        </div>
                        <div class="card-body">
                            <!-- DNI Field -->
                            <div class="mb-3">
                                <label for="txt_DNI" class="form-label required-field">DNI</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                    <input type="text" class="form-control" id="txt_DNI" name="txt_DNI"
                                        placeholder="Ingrese el número de DNI" required pattern="[0-9]{8}"
                                        title="El DNI debe tener 8 dígitos">
                                </div>
                                <div class="invalid-feedback">El DNI es obligatorio y debe tener 8 dígitos.</div>
                            </div>

                            <!-- Name Fields -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="txt_name" class="form-label required-field">Nombres</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="txt_nomb" name="txt_nomb"
                                            placeholder="Nombres" required>
                                    </div>
                                    <div class="invalid-feedback">Los nombres son obligatorios.</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="txt_ape_pat" class="form-label required-field">Apellido Paterno</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="txt_ape_pat" name="txt_ape_pat"
                                            placeholder="Apellido paterno" required>
                                    </div>
                                    <div class="invalid-feedback">El apellido paterno es obligatorio.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="txt_ape_mat" class="form-label">Apellido Materno</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="txt_ape_mat" name="txt_ape_mat"
                                        placeholder="Apellido materno">
                                </div>
                            </div>

                            <!-- Additional Fields -->
                            <div class="mb-3">
                                <label for="txt_IP" class="form-label">Dirección IP</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-pc-display"></i></span>
                                    <input id="txt_IP" name="txt_IP" type="text" placeholder="Ej: 172.20.100.50"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="txt_ofic" class="form-label required-field">Oficina</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <input type="text" class="form-control" id="txt_ofic" name="txt_ofic"
                                        placeholder="Oficina" required>
                                </div>
                                <div class="invalid-feedback">La oficina es obligatoria.</div>
                            </div>

                            <div class="mb-3">
                                <label for="txt_cond_lab" class="form-label required-field">Condición Laboral</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                    <input type="text" class="form-control" id="txt_cond_lab" name="txt_cond_lab"
                                        placeholder="Condición laboral" required>
                                </div>
                                <div class="invalid-feedback">La condición laboral es obligatoria.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Systems -->
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-lock me-2"></i> Sistemas a asignar</h5>
                        </div>
                        <div class="card-body">
                            <!-- Registral Systems -->
                            <div class="system-category">
                                <h5><i class="bi bi-archive me-2"></i> Sistemas Registrales</h5>
                                <div class="row">
                                    <div class="col-md-6 system-group">
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-21" value="Usuario Windows">
                                            <label class="form-check-label" for="checkboxes-21">Usuario Windows</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_ad" name="txt_user_ad">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-0" value="Consulta Registral">
                                            <label class="form-check-label" for="checkboxes-0">Consulta
                                                Registral</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_consulta" name="txt_user_consulta">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-1" value="Libro Diario">
                                            <label class="form-check-label" for="checkboxes-1">Libro Diario</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_libro" name="txt_user_libro">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-2" value="Mesa de Partes">
                                            <label class="form-check-label" for="checkboxes-2">Mesa de Partes</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_mepa" name="txt_user_mepa">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-3" value="RPU Grafico">
                                            <label class="form-check-label" for="checkboxes-3">RPU Gráfico</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_rpu" name="txt_user_rpu">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="SGTD">
                                            <label class="form-check-label" for="checkboxes-8">SGTD</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sgtd" name="txt_user_sgtd">
                                        </div>


                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="SOU">
                                            <label class="form-check-label" for="checkboxes-8">SOU</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sou" name="txt_user_sou">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="SPIJ">
                                            <label class="form-check-label" for="checkboxes-8">SPIJ</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_spij" name="txt_user_spij">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="SPRN">
                                            <label class="form-check-label" for="checkboxes-8">SPRN</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sprn" name="txt_user_sprn">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="SPRN(MESA DE PARTES)">
                                            <label class="form-check-label" for="checkboxes-8">"SPRN(MESA DE
                                                PARTES)"</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sprnMP" name="txt_user_sprnMP">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="ToolGIS">
                                            <label class="form-check-label" for="checkboxes-8">ToolGIS</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_toolgis" name="txt_user_toolgis">
                                        </div>
                                    </div>

                                    <div class="col-md-6 system-group">
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-4" value="SARP">
                                            <label class="form-check-label" for="checkboxes-4">SARP</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sarp" name="txt_user_sarp">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-5" value="SCUNAC">
                                            <label class="form-check-label" for="checkboxes-5">SCUNAC</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_scunac" name="txt_user_scunac">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-6" value="SEPR">
                                            <label class="form-check-label" for="checkboxes-6">SEPR</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sepr" name="txt_user_sepr">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-7" value="SIGESAR">
                                            <label class="form-check-label" for="checkboxes-7">SIGESAR</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sigesar" name="txt_user_sigesar">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="SIR RPV">
                                            <label class="form-check-label" for="checkboxes-8">SIR RPV</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_rpv" name="txt_user_rpv">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="SIR Minero">
                                            <label class="form-check-label" for="checkboxes-8">SIR Minero</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_minero" name="txt_user_minero">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="SIR">
                                            <label class="form-check-label" for="checkboxes-8">SIR</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sir" name="txt_user_sir">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="SPR">
                                            <label class="form-check-label" for="checkboxes-8">SPR</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_spr" name="txt_user_spr">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="Quemador SARP">
                                            <label class="form-check-label" for="checkboxes-8">Quemador SARP</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_QSARP" name="txt_user_QSARP">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-8" value="FDS">
                                            <label class="form-check-label" for="checkboxes-8">FDS</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_FDS" name="txt_user_FDS">
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Web Systems -->
                            <div class="system-category">
                                <h5><i class="bi bi-globe me-2"></i> Sistemas Web</h5>
                                <div class="row">
                                    <div class="col-md-6 system-group">
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-14" value="Citrix">
                                            <label class="form-check-label" for="checkboxes-14">Citrix</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_citrix" name="txt_user_citrix">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-15" value="Correo Institucional">
                                            <label class="form-check-label" for="checkboxes-15">Correo
                                                Institucional</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_correo" name="txt_user_correo">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-16" value="PSI">
                                            <label class="form-check-label" for="checkboxes-16">PSI</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_psi" name="txt_user_psi">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-18" value="SPRL">
                                            <label class="form-check-label" for="checkboxes-18">SPRL - EXTRANET</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sprl" name="txt_user_sprl">
                                        </div>
                                    </div>

                                    <div class="col-md-6 system-group">
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-17" value="RENIEC">
                                            <label class="form-check-label" for="checkboxes-17">RENIEC</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_reniec" name="txt_user_reniec">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-17" value="SGD">
                                            <label class="form-check-label" for="checkboxes-17">SGD</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sgd" name="txt_user_sgd">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-18" value="SGIT">
                                            <label class="form-check-label" for="checkboxes-18">SGIT</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sgit" name="txt_user_sgit">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-18" value="SGD(MESA DE PARTES)">
                                            <label class="form-check-label" for="checkboxes-18">SGD(MESA DE
                                                PARTES)</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sgdMP" name="txt_user_sgdMP">
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Administrative Systems -->
                            <div class="system-category">
                                <h5><i class="bi bi-clipboard-data me-2"></i> Sistemas Administrativos</h5>
                                <div class="row">
                                    <div class="col-md-6 system-group">
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-20" value="AXION">
                                            <label class="form-check-label" for="checkboxes-20">AXION</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_axion" name="txt_user_axion">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-20" value="Clarissa">
                                            <label class="form-check-label" for="checkboxes-20">Clarissa</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_Clarissa" name="txt_user_Clarissa">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-20" value="SIAF">
                                            <label class="form-check-label" for="checkboxes-20">SIAF</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_siaf" name="txt_user_siaf">
                                        </div>

                                        
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="LEGAJO">
                                            <label class="form-check-label" for="checkboxes-26">LEGAJO</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_lega" name="txt_user_lega">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="ReFirma">
                                            <label class="form-check-label" for="checkboxes-26">REFIRMA PDF</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_refirma" name="txt_user_refirma">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="SISABA">
                                            <label class="form-check-label" for="checkboxes-26">SISABA</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sisaba" name="txt_user_sisaba">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="SUTESOR">
                                            <label class="form-check-label" for="checkboxes-26">SUTESOR</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sute" name="txt_user_sute">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="TESORERIA">
                                            <label class="form-check-label" for="checkboxes-26">TESORERIA</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_teso" name="txt_user_teso">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="Registro de Visitas">
                                            <label class="form-check-label" for="checkboxes-26">REGISTRO DE
                                                VISITAS</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_visitas" name="txt_user_visitas">
                                        </div>
                                    </div>

                                    <div class="col-md-6 system-group">
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-24" value="SICA">
                                            <label class="form-check-label" for="checkboxes-24">SICA</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sica" name="txt_user_sica">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-24" value="SIGA">
                                            <label class="form-check-label" for="checkboxes-24">SIGA</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_siga" name="txt_user_siga">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="SISTRAM">
                                            <label class="form-check-label" for="checkboxes-26">SISTRAM</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_sistram" name="txt_user_sistram">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="CheckSmart">
                                            <label class="form-check-label" for="checkboxes-26">CHECKSMART</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_CheckSmart" name="txt_user_CheckSmart">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="Devoluciones">
                                            <label class="form-check-label" for="checkboxes-26">DEVOLUCIONES</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_devo" name="txt_user_devo">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="Firma">
                                            <label class="form-check-label" for="checkboxes-26">FIRMA ONPE</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_Firma" name="txt_user_Firma">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="MADAF">
                                            <label class="form-check-label" for="checkboxes-26">MADAF SIAF</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_madaf" name="txt_user_madaf">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="Melissa">
                                            <label class="form-check-label" for="checkboxes-26">MELISSA</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_meli" name="txt_user_meli">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-26" value="Modulo Logistica">
                                            <label class="form-check-label" for="checkboxes-26">MODULO LOGISTICA</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_logis" name="txt_user_logis">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- IT Systems -->
                            <div class="system-category">
                                <h5><i class="bi bi-pc-display me-2"></i> Informática/Otros</h5>
                                <div class="row">
                                    <div class="col-md-6 system-group">
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-20" value="AnyDesk">
                                            <label class="form-check-label" for="checkboxes-20">AnyDesk</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_any" name="txt_user_any">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-20" value="Base de Datos">
                                            <label class="form-check-label" for="checkboxes-20">Base de Datos</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_bd" name="txt_user_bd">
                                        </div>
                                        
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-22" value="Certificado Digital">
                                            <label class="form-check-label" for="checkboxes-22">Certificado
                                                Digital</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_certficado" name="txt_user_certficado">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-22" value="Discovery">
                                            <label class="form-check-label" for="checkboxes-22">DISCOVERY</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_discovery" name="txt_user_discovery">
                                        </div>
                                    </div>

                                    <div class="col-md-6 system-group">
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-21" value="VPN">
                                            <label class="form-check-label" for="checkboxes-21">VPN</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_vpn" name="txt_user_vpn">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-22" value="Acceso a Internet">
                                            <label class="form-check-label" for="checkboxes-22">Acceso a
                                                Internet</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_Acceso" name="txt_user_Acceso">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-22" value="KeyFile">
                                            <label class="form-check-label" for="checkboxes-22">KEYFILE</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_keyfile" name="txt_user_keyfile">
                                        </div>

                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-22" value="FTP">
                                            <label class="form-check-label" for="checkboxes-22">FTP</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_ftp" name="txt_user_ftp">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Systems -->
                            <div class="system-category">
                                <h5><i class="bi bi-shield-lock me-2"></i> Seguridad</h5>
                                <div class="row">
                                    <div class="col-md-6 system-group">
                                        <div class="system-checkbox form-check">
                                            <input class="form-check-input" type="checkbox" name="checkboxes[]"
                                                id="checkboxes-21" value="WithSecure">
                                            <label class="form-check-label" for="checkboxes-21">WithSecure</label>
                                            <input type="text" class="form-control form-control-sm mt-1"
                                                id="txt_user_mcafee" name="txt_user_mcafee">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-sunarp btn-lg" value="GENERAR" name="submit">
                        <i class="bi bi-send-check me-2"></i> Generar Formato
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <!-- Toasts will be inserted here dynamically -->
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2-search').select2({
            theme: 'bootstrap-5',
            placeholder: "Buscar por DNI, nombres o apellidos...",
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: window.location.href + '?action=buscar_usuarios',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        // Search button functionality
        $('#btnBuscar').on('click', function() {
            const dniBuscar = $('#txt_DNI_Buscar').val();

            if (!dniBuscar) {
                showToast('Advertencia', 'Por favor seleccione un usuario', 'warning');
                return;
            }

            const btn = $(this);
            btn.html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Buscando...'
            );
            btn.prop('disabled', true);

            fetch(window.location.href + '?action=buscar&dni_buscar=' + encodeURIComponent(dniBuscar))
                .then(response => response.json())
                .then(data => {
                    btn.html('<i class="bi bi-search me-1"></i> Buscar');
                    btn.prop('disabled', false);

                    if (data.error) {
                        showToast('Error', data.error, 'danger');
                        return;
                    }

                    // Fill form fields
                    $('#txt_DNI').val(data.dni || '');
                    $('#txt_name').val(data.nombres || '');
                    $('#txt_ape_pat').val(data.apellido_paterno || '');
                    $('#txt_ape_mat').val(data.apellido_materno || '');
                    $('#txt_ofic').val(data.oficina_usuario || '');

                    let condicion_laboral = data.condicion_laboral;
                    if (condicion_laboral === "PRACTICAS PRO") {
                        $('#txt_cond_lab').val("PRÁCTICAS PROFESIONALES");
                    } else if (condicion_laboral === "PRACTICAS PRE") {
                        $('#txt_cond_lab').val("PRÁCTICAS PREPROFESIONALES");
                    } else {
                        $('#txt_cond_lab').val(condicion_laboral || '');
                    }

                    showToast('Éxito', 'Datos del usuario cargados correctamente', 'success');
                })
                .catch(error => {
                    btn.html('<i class="bi bi-search me-1"></i> Buscar');
                    btn.prop('disabled', false);
                    showToast('Error', 'Ocurrió un error al buscar el usuario', 'danger');
                    console.error('Error:', error);
                });
        });

        // Form validation
        $('form').on('submit', function(e) {
            let isValid = true;

            // Validate required fields
            $('[required]').each(function() {
                if (!$(this).val().trim()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Validate DNI format
            const dniField = $('#txt_DNI');
            if (dniField.val() && !/^\d{8}$/.test(dniField.val())) {
                dniField.addClass('is-invalid');
                showToast('Error', 'El DNI debe tener 8 dígitos', 'danger');
                isValid = false;
            }

            // Validate IP format if provided
            const ipField = $('#txt_IP');
            if (ipField.val() && !/^(\d{1,3}\.){3}\d{1,3}$/.test(ipField.val())) {
                ipField.addClass('is-invalid');
                showToast('Error', 'Formato de IP inválido', 'danger');
                isValid = false;
            }

            // Check at least one system is selected
            if ($('input[name="checkboxes[]"]:checked').length === 0) {
                showToast('Advertencia', 'Seleccione al menos un sistema', 'warning');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                showToast('Error', 'Por favor complete todos los campos requeridos correctamente',
                    'danger');
                $('html, body').animate({
                    scrollTop: $('.is-invalid').first().offset().top - 100
                }, 500);
            }
        });

        // Show toast notification
        function showToast(title, message, type) {
            const toastId = 'toast-' + Date.now();
            const toastHTML = `
                <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>${title}</strong><br>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            $('.toast-container').append(toastHTML);
            const toast = new bootstrap.Toast(document.getElementById(toastId));
            toast.show();

            // Remove toast after 5 seconds
            setTimeout(() => {
                $(`#${toastId}`).remove();
            }, 5000);
        }

        // Add username to system fields when checkbox is checked
        $('input[name="checkboxes[]"]').on('change', function() {
            const inputId = $(this).attr('id').replace('checkboxes', 'txt_user');
            const inputField = $(`#${inputId}`);

            if ($(this).is(':checked')) {
                const dni = $('#txt_DNI').val();
                if (dni) {
                    const username = dni.toLowerCase();
                    inputField.val(username);
                }
            } else {
                inputField.val('');
            }
        });
    });
    </script>
</body>

</html>