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

//
header('Content-Type: 1| +/html; charset=ISO-8859-1');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="http://172.20.106.150:8085/Consultas/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <title>Modificación de Accesos</title>
    <style>
        :root {
            --sunarp-blue: #0056b3;
            --sunarp-light-blue: #e9f0f8;
            --sunarp-dark: #343a40;
        }

        .error-message {
            color: red;
            display: none;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info .badge {
            margin-left: 10px;
        }

        /* Efecto hover para mejorar la interactividad */
        .btn:hover {
            transform: translateY(-2px);
            transition: all 0.3s;
        }

        /* Estilos para los campos requeridos */
        .required-field::after {
            content: " *";
            color: red;
        }

        /* Estilos para el contenedor principal */
        .main-container {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        /* Estilos para las tarjetas de datos */
        .data-card {
            transition: all 0.3s;
            height: 100%;
        }

        .data-card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        /* Estilos para animar la transición del spinner */
        .search-spinner {
            display: none;
            animation: spin 1s linear infinite;
        }

        .nav-link.active {
            font-weight: 600;
            color: var(--sunarp-blue) !important;
            border-bottom: 2px solid var(--sunarp-blue);
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        body {
            background-color: #e9f0f8;
            /* font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; */
        }

        .card-header {
            background-color: var(--sunarp-blue);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }

        .card-jefe {
            background-color: #e9f0f8;
        }

        /* Mejoras visuales para los inputs con errores */
        .is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .is-invalid~.invalid-feedback {
            display: block;
        }
        /* Syle para PSI */
        #checkboxes_PSI:checked,
        #PSI_1:checked,
        #ModNot1:checked,
        #ModNot2:checked,
        #ModNot3:checked,

        #PSI_2:checked,
        #ModEmp1:checked,
        #ModEmp2:checked,
        #ModEmp3:checked,

        #PSI_3:checked,
        #ModSeg1:checked,
        #ModSeg2:checked,

        #PSI_4:checked,
        #ModVer1:checked,
        #ModVer2:checked,
        #ModVer3:checked,

        #PSI_5:checked,
        #ModEnt1:checked,
        #ModEnt2:checked,

        #PSI_6:checked,
        #ModMuni1:checked,
        #ModMuni2:checked,
        #ModMuni3:checked,

        #PSI_7:checked,
        #ModPIDE1:checked,
        #ModPIDE2:checked,
        #ModPIDE3:checked,
        #ModPIDE4:checked,
        #ModPIDE5:checked,
        #ModPIDE6:checked,
        #ModPIDE7:checked,
        #ModPIDE8:checked,
        #ModPIDE9:checked {
            background-color: #28a745; /* Verde Bootstrap */
            border-color: #28a745;
        }


        #checkboxes_PSI:checked::before {
            background-color: white;
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
                        <a class="nav-link active" aria-current="page" href="">
                            <i class="bi bi-person-vcard-fill"></i> MDA
                        </a>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn" onclick="window.location.href='index_cargo.php'">
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
    <!-- ************************************************** CONTENIDO *************************************************************************** -->
    <form class="form-horizontal" action="PDF_MDA.php" method="POST" id="formMDA">
        <div class="container my-5">
            <!-- Título principal -->
            <div class="text-center mb-5">
                <h1 class="display-3 text-primary fw-bold">
                    <i class="bi bi-shield-lock"></i> Modificación de Accesos
                </h1>
                <div class="progress mt-3" style="height: 4px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" id="formProgress"></div>
                </div>
            </div>

            <!-- Buscador mejorado -->
            <div class="main-container bg-light mb-4">
                <h2 class="text-start mb-4">
                    <i class="bi bi-search"></i> Buscar usuario
                </h2>
                <div class="input-group mb-3">
                    <select class="form-select" id="txt_DNI_Buscar" name="txt_DNI_Buscar"></select>

                    <button type="button" class="btn btn-primary" id="btnBuscar">
                        <i class="bi bi-search me-1"></i> Buscar
                        <span class="spinner-border spinner-border-sm search-spinner" role="status"
                            aria-hidden="true"></span>
                    </button>
                </div>
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    Puedes buscar por DNI o por nombres y apellidos. El sistema completará automáticamente los campos.
                </div>
            </div>

            <!-- Formulario de datos mejorado usuario -->
            <div class="main-container" id="requiredFields">
                <h2 class="text-start mb-4">
                    <i class="bi bi-pencil-square"></i> Datos de la solicitud
                </h2>

                <div class="row g-4">
                    <!-- Primera columna (datos del usuario) -->
                    <div class="col-md-6">
                        <div class="card data-card h-100">
                            <div class="card-header text-white">
                                <h3 class="card-title mb-0">
                                    <i class="bi bi-person-fill"></i> Datos del Usuario
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="txt_DNI" class="form-label required-field">DNI:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                        <input type="text" class="form-control" id="txt_DNI" name="txt_DNI"
                                            placeholder="Ingresa el número de DNI" required pattern="[0-9]{8}"
                                            title="El DNI debe tener 8 dígitos">
                                    </div>
                                    <div class="invalid-feedback">
                                        El DNI es obligatorio y debe tener 8 dígitos.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="txt_name" class="form-label required-field">Nombres:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="txt_name" name="txt_name"
                                            placeholder="Ingresa los nombres" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        Los nombres son obligatorios.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="txt_ape_pat" class="form-label required-field">Apellido Paterno:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="txt_ape_pat" name="txt_ape_pat"
                                            placeholder="Ingresa el apellido paterno" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        El apellido paterno es obligatorio.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="txt_ape_mat" class="form-label required-field">Apellido Materno:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="txt_ape_mat" name="txt_ape_mat"
                                            placeholder="Ingresa el apellido materno" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        El apellido materno es obligatorio.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="txt_cond_lab" class="form-label required-field">Condición
                                        Laboral:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                        <input type="text" class="form-control" id="txt_cond_lab" name="txt_cond_lab"
                                            placeholder="Ingrese condicion laboral" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        La condición laboral es obligatoria.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Segunda columna (datos de la oficina) -->
                    <div class="col-md-6">
                        <div class="card data-card h-100">
                            <div class="card-header text-white">
                                <h3 class="card-title mb-0">
                                    <i class="bi bi-building-fill"></i> Datos de la Oficina
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="txt_ofic" class="form-label required-field">Oficina:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                                        <input type="text" class="form-control" id="txt_ofic" name="txt_ofic"
                                            placeholder="Oficina" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        La oficina es obligatoria.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="txt_unid" class="form-label required-field">Unidad:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                                        <select class="form-select" id="txt_unid" name="txt_unid" required>
                                            <option value="">Seleccione una unidad</option>
                                            <option value="-">-</option>
                                            <option value="Administración">Administración</option>
                                            <option value="Asesoria Juridica">Asesoría Jurídica</option>
                                            <option value="Oficina de Control Interno">Oficina de Control Interno
                                            </option>
                                            <option value="Planeamiento, Presupuesto y Modernizacion">Planeamiento,
                                                Presupuesto y Modernización</option>
                                            <option value="Registral">Registral</option>
                                            <option value="Tecnologia de la Informacion">Tecnología de la Información
                                            </option>
                                        </select>
                                    </div>
                                    <div class="invalid-feedback">
                                        La unidad es obligatoria.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="txt_area" class="form-label required-field">Área:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-diagram-2"></i></span>
                                        <input type="text" class="form-control" id="txt_area" name="txt_area"
                                            placeholder="Área" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        El área es obligatoria.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="txt_carg" class="form-label required-field">Cargo:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                        <input type="text" class="form-control" id="txt_carg" name="txt_carg"
                                            placeholder="Cargo" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        El cargo es obligatorio.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="txt_IP" class="form-label required-field">Dirección IP:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-pc-display"></i></span>
                                        <input type="text" class="form-control" id="txt_IP" name="txt_IP"
                                            placeholder="Ej: 172.1.1.1" required pattern="^(\d{1,3}\.){3}\d{1,3}$"
                                            title="Formato de IP válido: xxx.xxx.xxx.xxx">
                                    </div>
                                    <div class="invalid-feedback">
                                        La dirección IP es obligatoria y debe tener un formato válido.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Div para condición laboral Externo -->
                <div class="card mt-4" id="Div_Externo" style="display:none;">
                    <div class="card-header bg-warning">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Información Adicional para Personal Externo
                        </h4>
                    </div>
                    <div class="card-body">
                        <label for="txt_Externo" class="form-label">Especificar la condición laboral:</label>
                        <textarea class="form-control" id="txt_Externo" name="txt_Externo" rows="4"
                            placeholder="Especifique la condición laboral con que se encuentra..."></textarea>
                        <div class="invalid-feedback">
                            Este campo es obligatorio para personal externo.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de accesos solicitados -->
            <div class="main-container mt-4">
                <h2 class="text-start mb-4">
                    <i class="bi bi-key"></i> Accesos solicitados
                </h2>

                <div class="card">
                    <div class="card-header  text-white">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-list-check"></i> Seleccione los sistemas a los que requiere acceso
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Primera columna (formulario de Sistemas Registrales) -->
                        <div class="row g-3">
                            <!-- Aquí se agregarían los checkboxes de sistemas -->
                            <div class="col-md-4 bg-light p-4 rounded-3 mb-3 ">
                                <h3 class="text-center mb-4">Sistemas Registrales</h3>
                                <hr>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-0"
                                        name="checkboxes[]" value="Consulta Registral">
                                    <label class="form-check-label" for="checkboxes-0">Consulta Registral</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-1"
                                        name="checkboxes[]" value="Devoluciones">
                                    <label class="form-check-label" for="checkboxes-1">Devoluciones</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-2"
                                        name="checkboxes[]" value="Libro Diario">
                                    <label class="form-check-label" for="checkboxes-2">Libro Diario</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-3"
                                        name="checkboxes[]" value="Mesa de Partes">
                                    <label class="form-check-label" for="checkboxes-3">Mesa de Partes</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-4"
                                        name="checkboxes[]" value="RPU Grafico">
                                    <label class="form-check-label" for="checkboxes-4">RPU Grafico</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-5"
                                        name="checkboxes[]" value="SARP">
                                    <label class="form-check-label" for="checkboxes-5">SARP</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-6"
                                        name="checkboxes[]" value="SCUNAC">
                                    <label class="form-check-label" for="checkboxes-6">SCUNAC</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-7"
                                        name="checkboxes[]" value="SERP">
                                    <label class="form-check-label" for="checkboxes-7">SERP</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-8"
                                        name="checkboxes[]" value="SGTD">
                                    <label class="form-check-label" for="checkboxes-8">SGTD</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-9"
                                        name="checkboxes[]" value="SIGESAR">
                                    <label class="form-check-label" for="checkboxes-9">SIGESAR</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-10"
                                        name="checkboxes[]" value="SIR">
                                    <label class="form-check-label" for="checkboxes-10">SIR</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-11"
                                        name="checkboxes[]" value="SIR Minero">
                                    <label class="form-check-label" for="checkboxes-11">SIR Minero</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-12"
                                        name="checkboxes[]" value="SIR RPV">
                                    <label class="form-check-label" for="checkboxes-12">SIR RPV</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-13"
                                        name="checkboxes[]" value="SOU">
                                    <label class="form-check-label" for="checkboxes-13">SOU</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-14"
                                        name="checkboxes[]" value="SPIJ">
                                    <label class="form-check-label" for="checkboxes-14">SPIJ</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-15"
                                        name="checkboxes[]" value="SPR">
                                    <label class="form-check-label" for="checkboxes-15">SPR</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-16"
                                        name="checkboxes[]" value="SPRN">
                                    <label class="form-check-label" for="checkboxes-16">SPRN</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-17"
                                        name="checkboxes[]" value="SPRN - Mesa de Partes">
                                    <label class="form-check-label" for="checkboxes-17">SPRN - Mesa de Partes</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-18"
                                        name="checkboxes[]" value="Toolgis">
                                    <label class="form-check-label" for="checkboxes-18">Toolgis</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-64"
                                        name="checkboxes[]" value="FDS">
                                    <label class="form-check-label" for="checkboxes-64">FDS</label>
                                </div>
                            </div>
                            <!-- Segunda columna (SISTEMAS ADMINISTRATIVOS) -->
                            <div class="col-md-4 bg-light text-black p-4 rounded-3 mb-3">

                                <h3 class="text-center mb-4" for="checkboxes">Sistemas Administrativos</h3>
                                <hr>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-19"
                                        name="checkboxes[]" value="Alfresco">
                                    <label class="form-check-label" for="checkboxes-19">Alfresco</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-20"
                                        name="checkboxes[]" value="AXIOM">
                                    <label class="form-check-label" for="checkboxes-20">AXIOM</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-21"
                                        name="checkboxes[]" value="CheckSmart">
                                    <label class="form-check-label" for="checkboxes-21">CheckSmart</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-22"
                                        name="checkboxes[]" value="Clarissa">
                                    <label class="form-check-label" for="checkboxes-22">Clarissa</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-23"
                                        name="checkboxes[]" value="Legajo">
                                    <label class="form-check-label" for="checkboxes-23">Legajo</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-24"
                                        name="checkboxes[]" value="MADAF SIAF">
                                    <label class="form-check-label" for="checkboxes-24">MADAF SIAF</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-25"
                                        name="checkboxes[]" value="Melissa">
                                    <label class="form-check-label" for="checkboxes-25">Melissa</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-26"
                                        name="checkboxes[]" value="Modulo Logistica">
                                    <label class="form-check-label" for="checkboxes-26">Modulo Logistica</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-27"
                                        name="checkboxes[]" value="Registro de Visitas">
                                    <label class="form-check-label" for="checkboxes-27">Registro de Visitas</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-28"
                                        name="checkboxes[]" value="SIAF">
                                    <label class="form-check-label" for="checkboxes-28">SIAF</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-29"
                                        name="checkboxes[]" value="SICA">
                                    <label class="form-check-label" for="checkboxes-29">SICA</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-30"
                                        name="checkboxes[]" value="SIGA">
                                    <label class="form-check-label" for="checkboxes-30">SIGA</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-31"
                                        name="checkboxes[]" value="SISABA">
                                    <label class="form-check-label" for="checkboxes-31">SISABA</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-32"
                                        name="checkboxes[]" value="SISTRAM">
                                    <label class="form-check-label" for="checkboxes-32">SISTRAM</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-33"
                                        name="checkboxes[]" value="SUTESOR">
                                    <label class="form-check-label" for="checkboxes-33">SUTESOR</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-34"
                                        name="checkboxes[]" value="Tesoreria">
                                    <label class="form-check-label" for="checkboxes-34">Tesoreria</label>
                                </div>

                            </div>
                            <!-- Tercera columna (SISTEMAS WEB) -->
                            <div class="col-md-4 bg-light p-4 rounded-3 mb-3">

                                <h3 class="text-center mb-4" for="checkboxes">Sistemas Web</h3>
                                <hr>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-35"
                                        name="checkboxes[]" value="Acceso a Internet"
                                        onclick="toggleDiv('myDiv','checkboxes-35')">
                                    <label class="form-check-label" for="checkboxes-35">Acceso a Internet</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-36"
                                        name="checkboxes[]" value="Citrix">
                                    <label class="form-check-label" for="checkboxes-36">Citrix</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-37"
                                        name="checkboxes[]" value="Correo Institucional">
                                    <label class="form-check-label" for="checkboxes-37">Correo Institucional</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-38"
                                        name="checkboxes[]" value="Firma ONPE">
                                    <label class="form-check-label" for="checkboxes-38">Firma ONPE</label>
                                </div>
                                
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-40"
                                        name="checkboxes[]" value="Refirma PDF">
                                    <label class="form-check-label" for="checkboxes-40">Refirma PDF</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-41"
                                        name="checkboxes[]" value="RENIEC">
                                    <label class="form-check-label" for="checkboxes-41">RENIEC</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-42"
                                        name="checkboxes[]" value="SGD">
                                    <label class="form-check-label" for="checkboxes-42">SGD</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-43"
                                        name="checkboxes[]" value="SGD - Mesa de Partes">
                                    <label class="form-check-label" for="checkboxes-43">SGD - Mesa de Partes</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-44"
                                        name="checkboxes[]" value="SGIT">
                                    <label class="form-check-label" for="checkboxes-44">SGIT</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-45"
                                        name="checkboxes[]" value="SPRL - Extranet">
                                    <label class="form-check-label" for="checkboxes-45">SPRL - Extranet</label>
                                </div>
                                <!-- Checkbox PSI -->
                                <div class="form-check form-switch">
                                    <input class="form-check-input" id="checkboxes_PSI" type="checkbox" role="switch"
                                        name="checkboxes[]" value="PSI"
                                        onclick="togglePSISection()">
                                    <label class="form-check-label" for="checkboxes_PSI">PSI</label>
                                </div>
                                <!-- Caja de opciones oculta inicialmente -->
                                <div id="Div_PSI" style="display: none; margin-left: 20px; margin-top: 10px;">
                                    <!-- cuadro Correo electronico -->
                                     <div style="margin-bottom: 10px;">
                                        <label for="inputCorreos" class="form-label">Ingrese correo Electrónico <span style="color: red;">*</span></label>
                                        <input type="text" id="inputCorreoPSI" name="PSI_correo" class="form-control">
                                    </div>
                                    <!-- Modulo de notaria -->
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="modulo_psi_Notarios" value="Módulo de Notarios" id="PSI_1"
                                        onclick="toggleDiv('Modulo_Notario','PSI_1')">
                                        <label class="form-check-label" for="mod1">Módulo de Notarios</label>
                                    </div>
                                     <div id="Modulo_Notario" style="display: none; margin-left: 20px; margin-top: 10px;">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_notarios_1" value="X" id="ModNot1">
                                            <label class="form-check-label" for="mod1">Consulta</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_notarios_2" value="X" id="ModNot2">
                                            <label class="form-check-label" for="mod1">Registro</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_notarios_3" value="X" id="ModNot3">
                                            <label class="form-check-label" for="mod1">Administración</label>
                                        </div>
                                     </div>
                                    <!-- Modulo de Empresas -->
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="modulo_psi_Empresas" value="Módulo de Empresas" id="PSI_2"
                                        onclick="toggleDiv('Modulo_Empresas','PSI_2')">
                                        <label class="form-check-label" for="mod1">Módulo de Empresas</label>
                                    </div>
                                    
                                     <div id="Modulo_Empresas" style="display: none; margin-left: 20px; margin-top: 10px;">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_empresas_1" value="X" id="ModEmp1">
                                            <label class="form-check-label" for="mod1">Consulta</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_empresas_2" value="X" id="ModEmp2">
                                            <label class="form-check-label" for="mod1">Registro</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_empresas_3" value="X" id="ModEmp3">
                                            <label class="form-check-label" for="mod1">Administración</label>
                                        </div>
                                     </div>
                                     <!-- Modulo de Seguridad -->
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="modulo_psi_Seguridad" value="Módulo de Seguridad" id="PSI_3"
                                        onclick="toggleDiv('Modulo_Seguridad','PSI_3')">
                                        <label class="form-check-label" for="mod1">Módulo de Seguridad</label>
                                    </div>
                                    
                                     <div id="Modulo_Seguridad" style="display: none; margin-left: 20px; margin-top: 10px;">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_seguridad_1" value="X" id="ModSeg1">
                                            <label class="form-check-label" for="mod1">Administración Zonal</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_seguridad_2" value="X" id="ModSeg2">
                                            <label class="form-check-label" for="mod1">Administracion (Solo para SC)</label>
                                        </div>
                                     </div>
                                     <!-- Modulo de Verificadores -->
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="modulo_psi_Verificadores" value="Módulo de Verificadores" id="PSI_4"
                                        onclick="toggleDiv('Modulo_Verificadores','PSI_4')">
                                        <label class="form-check-label" for="mod1">Módulo de Verificadores</label>
                                    </div>
                                    
                                     <div id="Modulo_Verificadores" style="display: none; margin-left: 20px; margin-top: 10px;">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_Verificadores_1" value="X" id="ModVer1">
                                            <label class="form-check-label" for="mod1">Consulta</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_Verificadores_2" value="X" id="ModVer2">
                                            <label class="form-check-label" for="mod1">Registro</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_Verificadores_3" value="X" id="ModVer3">
                                            <label class="form-check-label" for="mod1">Administración</label>
                                        </div>
                                     </div>
                                     <!-- Modulo de Entidades -->
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="modulo_psi_Entidades" value="Módulo de Entidades" id="PSI_5"
                                        onclick="toggleDiv('Modulo_Entidades','PSI_5')">
                                        <label class="form-check-label" for="mod1">Módulo de Entidades</label>
                                    </div>
                                    
                                     <div id="Modulo_Entidades" style="display: none; margin-left: 20px; margin-top: 10px;">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_Entidades_1" value="X" id="ModEnt1">
                                            <label class="form-check-label" for="mod1">Consulta</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_Entidades_2" value="X" id="ModEnt2">
                                            <label class="form-check-label" for="mod1">Administración</label>
                                        </div>
                                     </div>
                                    <!-- Modulo de Municipalidades -->
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="modulo_psi_Municipalidades" value="Módulo de Notarios" id="PSI_6"
                                        onclick="toggleDiv('Modulo_Municipalidades','PSI_6')">
                                        <label class="form-check-label" for="mod1">Módulo de Municipalidades</label>
                                    </div>
                                    
                                     <div id="Modulo_Municipalidades" style="display: none; margin-left: 20px; margin-top: 10px;">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_muni_1" value="X" id="ModMuni1">
                                            <label class="form-check-label" for="mod1">Consulta</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_muni_2" value="X" id="ModMuni2">
                                            <label class="form-check-label" for="mod1">Registro</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_muni_3" value="X" id="ModMuni3">
                                            <label class="form-check-label" for="mod1">Administración</label>
                                        </div>
                                     </div>


                                     <!-- Servicios PIDE -->
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="modulo_psi_PIDE" value="Módulo de Notarios" id="PSI_7"
                                        onclick="toggleDiv('Modulo_PIDE','PSI_7')">
                                        <label class="form-check-label" for="mod1">Servicios PIDE</label>
                                    </div>
                                    
                                     <div id="Modulo_PIDE" style="display: none; margin-left: 20px; margin-top: 10px;">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_pide_1" value="X" id="ModPIDE1">
                                            <label class="form-check-label" for="mod1">Reniec</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_pide_2" value="X" id="ModPIDE2">
                                            <label class="form-check-label" for="mod1">Inpe</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_pide_3" value="X" id="ModPIDE3">
                                            <label class="form-check-label" for="mod1">PNP</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_pide_4" value="X" id="ModPIDE4">
                                            <label class="form-check-label" for="mod1">Poder Judicial</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_pide_5" value="X" id="ModPIDE5">
                                            <label class="form-check-label" for="mod1">Sunedu</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_pide_6" value="X" id="ModPIDE6">
                                            <label class="form-check-label" for="mod1">Migraciones</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_pide_7" value="X" id="ModPIDE7">
                                            <label class="form-check-label" for="mod1">Servir</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_pide_8" value="X" id="ModPIDE8">
                                            <label class="form-check-label" for="mod1">DS 21-2019-VIVIENDA</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="modulo_pide_9" value="X" id="ModPIDE9">
                                            <label class="form-check-label" for="mod1">MINJUS</label>
                                        </div>
                                     </div>

                                     <!-- Motivo/Sustento -->
                                    <div style="margin-bottom: 10px;">
                                        <label for="InputMotivoSustento" class="form-label"> Indicar Motivo/Sustento  <span style="color: red;">*</span></label>
                                        <input type="text" id="InputMotivoSustento" name="PSI_MotivoSustento" class="form-control">
                                    </div>
                                    
                                </div>


                                <div id="myDiv">
                                    <hr>
                                    <label for="txt_acceso_net" class="control-label fw-bold">Especificar los
                                        accesos de internet:</label>
                                    <textarea class="form-control" id="txt_acceso_net" name="txt_acceso_net" rows="4"
                                        placeholder="Ingrese los link..."></textarea>
                                </div>

                            </div>
                        </div>


                        <!--El formulario del lo que solicitara -->
                        <div class="container-fluid ">
                            <div class="row h-100">
                                <!-- Primera columna (formulario del Informatica) -->
                                <div class="col-md-4 bg-light p-4 rounded-3 mb-3">

                                    <h3 class="text-center mb-4" for="checkboxes">Informatica</h3>
                                    <hr>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-46"
                                            name="checkboxes[]" value="Base de Datos">
                                        <label class="form-check-label" for="checkboxes-46">Base de Datos</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-47"
                                            name="checkboxes[]" value="Discovery Consola">
                                        <label class="form-check-label" for="checkboxes-47">Discovery Consola</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-48"
                                            name="checkboxes[]" value="FTP">
                                        <label class="form-check-label" for="checkboxes-48">FTP</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-49"
                                            name="checkboxes[]" value="KeyFile">
                                        <label class="form-check-label" for="checkboxes-49">KeyFile</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-50"
                                            name="checkboxes[]" value="WithSecure Consola">
                                        <label class="form-check-label" for="checkboxes-50">WithSecure Consola</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-63"
                                            name="checkboxes[]" value="Aranda">
                                        <label class="form-check-label" for="checkboxes-63">Aranda</label>
                                    </div>

                                </div>
                                <!-- Segunda columna (formulario del WithSecure) -->

                                <div class="col-md-4 bg-light text-black p-4 rounded-3 mb-3">

                                    <h3 class="text-center mb-4" for="checkboxes">WithSecure</h3>
                                    <hr>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-51"
                                            name="checkboxes[]" value="Bloqueo USB/CD">
                                        <label class="form-check-label" for="checkboxes-51">Bloqueo USB/CD</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-52"
                                            name="checkboxes[]" value="CD (Lectura)">
                                        <label class="form-check-label" for="checkboxes-52">CD (Lectura)</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-53"
                                            name="checkboxes[]" value="CD (R / W)">
                                        <label class="form-check-label" for="checkboxes-53">CD (R / W)</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-54"
                                            name="checkboxes[]" value="USB (Lectura)">
                                        <label class="form-check-label" for="checkboxes-54">USB (Lectura)</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-55"
                                            name="checkboxes[]" value="USB (R / W)">
                                        <label class="form-check-label" for="checkboxes-55">USB (R / W)</label>
                                    </div>

                                </div>
                                <!-- Tercera columna (formulario de Otros Accesos) -->
                                <div class="col-md-4 bg-light p-4 rounded-3 mb-3">

                                    <h3 class="text-center mb-4" for="checkboxes">Otros</h3>
                                    <hr>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-56"
                                            name="checkboxes[]" value="AnyDesk">
                                        <label class="form-check-label" for="checkboxes-56">AnyDesk</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-57"
                                            name="checkboxes[]" value="Certificado Digital">
                                        <label class="form-check-label" for="checkboxes-57">Certificado Digital</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-58"
                                            name="checkboxes[]" value="Equipamiento">
                                        <label class="form-check-label" for="checkboxes-58">Equipamiento</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-59"
                                            name="checkboxes[]" value="Softphone">
                                        <label class="form-check-label" for="checkboxes-59">Softphone</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-60"
                                            name="checkboxes[]" value="Usuario Windows">
                                        <label class="form-check-label" for="checkboxes-60">Usuario Windows</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-61"
                                            name="checkboxes[]" value="VPN Tunel">
                                        <label class="form-check-label" for="checkboxes-61">VPN Tunel</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes-62"
                                            name="checkboxes[]" value="VPN Web">
                                        <label class="form-check-label" for="checkboxes-62">VPN Web</label>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <br> <!-- Especificar la solicitud-->
                        <div class="mb-3">
                            <label for="txt_opcional" class="control-label fw-bold">Especifique la solicitud:</label>
                            <textarea class="form-control" id="txt_opcional" name="txt_opcional" rows="4"
                                placeholder="Ingrese texto para especificar la solicitud..."></textarea>
                            <hr>
                        </div>
                        <br>
                        <div class="container-fluid ">
                            <div class="row h-100">
                                <!-- Primera columna (formulario del Informatica) -->
                                <div class="col-md-6 bg-light p-4 rounded-3 mb-3">

                                    <h2 class="text-start" for="checkboxes1">Equipamento:</h2>
                                    <hr>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes1-1"
                                            name="checkboxes1[]" value="Audifonos">
                                        <label class="form-check-label" for="checkboxes1-1">Audifonos</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes1-2"
                                            name="checkboxes1[]" value="Camara">
                                        <label class="form-check-label" for="checkboxes1-2">Camara</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes1-3"
                                            name="checkboxes1[]" value="Celular">
                                        <label class="form-check-label" for="checkboxes1-3">Celular</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes1-4"
                                            name="checkboxes1[]" value="Disco Duro Externo">
                                        <label class="form-check-label" for="checkboxes1-4">Disco Duro Externo</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes1-5"
                                            name="checkboxes1[]" value="Escaner">
                                        <label class="form-check-label" for="checkboxes1-5">Escaner</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes1-6"
                                            name="checkboxes1[]" value="Estacion Grafica">
                                        <label class="form-check-label" for="checkboxes1-6">Estacion Grafica</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes1-7"
                                            name="checkboxes1[]" value="Impresora">
                                        <label class="form-check-label" for="checkboxes1-7">Impresora</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes1-8"
                                            name="checkboxes1[]" value="Lector Biometrico">
                                        <label class="form-check-label" for="checkboxes1-8">Lector Biometrico</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="checkboxes1-9"
                                            name="checkboxes1[]" value="Lector DNI">
                                        <label class="form-check-label" for="checkboxes1-9">Lector DNI</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-10" name="checkboxes1[]" value="PC Escritorio">
                                        <label class="form-check-label" for="checkboxes1-10">PC Escritorio</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-11" name="checkboxes1[]" value="PC Laptop">
                                        <label class="form-check-label" for="checkboxes1-11">PC Laptop</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-12" name="checkboxes1[]" value="Plotter">
                                        <label class="form-check-label" for="checkboxes1-12">Plotter</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-13" name="checkboxes1[]" value="Proyector">
                                        <label class="form-check-label" for="checkboxes1-13">Proyector</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-14" name="checkboxes1[]" value="Refrendadora">
                                        <label class="form-check-label" for="checkboxes1-14">Refrendadora</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-15" name="checkboxes1[]" value="Tablet">
                                        <label class="form-check-label" for="checkboxes1-15">Tablet</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-16" name="checkboxes1[]" value="Teclado">
                                        <label class="form-check-label" for="checkboxes1-16">Teclado</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-17" name="checkboxes1[]" value="Telefono IP">
                                        <label class="form-check-label" for="checkboxes1-17">Telefono IP</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-18" name="checkboxes1[]" value="Televisor">
                                        <label class="form-check-label" for="checkboxes1-18">Televisor</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-19" name="checkboxes1[]" value="Token">
                                        <label class="form-check-label" for="checkboxes1-19">Token</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="checkboxes1-20" name="checkboxes1[]" value="UPS">
                                        <label class="form-check-label" for="checkboxes1-20">UPS</label>
                                    </div>

                                </div>
                                <!-- Segunda columna (formulario del WithSecure) -->
                                <div class="card-jefe col-md-6 text-black p-4 rounded-3 mb-3">

                                    <h2 class="text-start">Datos de la Autorización:</h2>
                                    <hr>
                                    <div>
                                        <div class="d-flex align-items justify-content-center">
                                            <!-- Etiqueta izquierda -->
                                            <label class="form-check-label me-2">Jefe Inmediato</label>
                                            <!-- Checkbox en el medio -->
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    id="checkboxes_Autoriz" name="checkboxes_Autoriz"
                                                    value="Datos Autorizacion"
                                                    onclick="toggleDiv('myDiv2','checkboxes_Autoriz')">
                                            </div>
                                            <!-- Etiqueta derecha -->
                                            <label class="form-check-label">Documento</label>
                                        </div>
                                    </div>
                                    <br>
                                    <div id="myDiv1">
                                        <div class="d-flex align-items-center mb-3">
                                            <label for="txt_dni_jef" class="col-md-4 control-label">DNI:</label>
                                            <input type="text" class="form-control" id="txt_dni_jef" name="txt_dni_jef"
                                                placeholder="Ingresa el número de DNI">
                                        </div>
                                        <div class="d-flex align-items-center mb-3">
                                            <label for="txt_name_jef" class="col-md-4 control-label">Nombres:</label>
                                            <input type="text" class="form-control" id="txt_name_jef"
                                                name="txt_name_jef" placeholder="Ingresa tus nombres">
                                        </div>
                                        <div class="d-flex align-items-center mb-3">
                                            <label for="txt_ape_pat_jef" class="col-md-4 control-label">Apellido
                                                Paterno:</label>
                                            <input type="text" class="form-control" id="txt_ape_pat_jef"
                                                name="txt_ape_pat_jef" placeholder="Ingresa tu apellido paterno">
                                        </div>
                                        <div class="d-flex align-items-center mb-3">
                                            <label for="txt_ape_mat_jef" class="col-md-4 control-label">Apellido
                                                Materno:</label>
                                            <input type="text" class="form-control" id="txt_ape_mat_jef"
                                                name="txt_ape_mat_jef" placeholder="Ingresa tu apellido materno">
                                        </div>
                                        <!-- Lista de tipos de contrantos -->
                                        <div class="d-flex align-items-center mb-3">
                                            <label for="txt_carg_jef" class="col-md-4 control-label">Cargo Laboral:
                                            </label>
                                            <input type="text" class="form-control" id="txt_carg_jef"
                                                name="txt_carg_jef" placeholder="Cargo Laboral">
                                        </div>
                                        <div>
                                            <!-- <button type="button" class="btn btn-primary">Buscar Jefe</button> -->
                                            <button type="button" class="btn btn-primary" id="btnBuscarJefe">
                                                <i class="bi bi-search me-1"></i> Buscar jefe
                                                <span class="spinner-border spinner-border-sm search-spinner"
                                                    role="status" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- <div id="myDiv2">
                                            <p class="text-start">Se tiene que agregar el número de documento (RESOLUCION,
                                                MEMORANDUM, etc): </p>
                                            <label for="txt_document" class="col-md control-label-mb-3 mb-3 fw-bold">Número de
                                                Documento:</label>
                                            <input type="text" class="form-control" id="txt_document" name="txt_document"
                                                placeholder="Ingresa número de documento">
                                        </div> -->

                                </div>
                            </div>
                        </div>

                        <div>

                            <div class="alert alert-danger mt-3" role="alert" id="errorMessage1" style="display:none;">
                                Por favor seleccione al menos un sistema.
                            </div>
                        </div>
                        <br>
                        <center>
                            <button type="submit" class="btn btn-primary" value="GENERAR" name="submit"
                                id="submitButton">Generar
                                Formato <i class="bi bi-file-earmark-pdf"></i></button>
                        </center>
                        <br>

                    </div>
                </div>
            </div>
        </div>

    </form>
    </div>

    <!-- CODIGO DE JAVASCRIPT -->
    <script src="http://172.20.106.150:8085/Consultas/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <!-- Script para mostrar/ocultar PSI -->

    <script>
        function toggleDiv(divId, checkBoxId) {
            var checkBox = document.getElementById(checkBoxId);
            var div = document.getElementById(divId);
            // Si el checkbox está seleccionado, muestra el div, de lo contrario lo oculta
            if (checkBox.checked) {
                div.style.display = "block";
            } else {
                div.style.display = "none";
            }
        }
        function togglePSISection() {
            const checkbox = document.getElementById('checkboxes_PSI');
            const div = document.getElementById('Div_PSI');
            const correoInput = document.getElementById('inputCorreoPSI');
            const motivoInput = document.getElementById('InputMotivoSustento');

            if (checkbox.checked) {
                div.style.display = 'block';
                correoInput.setAttribute('required', 'required');
                motivoInput.setAttribute('required', 'required');
            } else {
                div.style.display = 'none';
                correoInput.removeAttribute('required');
                motivoInput.removeAttribute('required');
            }
        }


        window.onload = function () {
            // Asegura que el div esté oculto al cargar o recargar la página
            document.getElementById("myDiv").style.display = "none";
            document.getElementById("myDiv2").style.display = "none";
            document.getElementById("Div_Externo").style.display = "none";
            document.getElementById("Div_PSI").style.display = "none";
            //document.getElementById("submitButton").disabled = "false";
        };

        document.getElementById('txt_cond_lab').addEventListener('change', function () {
            const additionalInfo = document.getElementById('Div_Externo');

            // Muestra el div si se selecciona una opción diferente a la predeterminada
            if (this.value == "Externo") {
                additionalInfo.style.display = 'block'; // Muestra el div
            } else {
                additionalInfo.style.display = 'none'; // Oculta el div si no se selecciona nada
            }
        });

        //OBLIGATORIO DE LLENAR DATOS 
        const requiredFields = document.getElementById('requiredFields');
        const submitButton = document.getElementById('submitButton');
        const errorMessage = document.getElementById('errorMessage');
        const checkboxes = document.querySelectorAll('input[name="checkboxes[]"]');
        const submitButton1 = document.getElementById('submitButton');
        const errorMessage1 = document.getElementById('errorMessage1');
        const myDiv1_A = document.getElementById('myDiv1');
        const myDiv2_A = document.getElementById('myDiv2');

        // Verifica los campos al cambiar su valor
        requiredFields.addEventListener('input', function () {
            const fields = requiredFields.querySelectorAll('input[type="text"]');
            let allFilled = true;

            fields.forEach(field => {
                if (!field.value.trim()) {
                    allFilled = false;
                }
            });

            if (allFilled) {
                errorMessage.style.display = 'none'; // Oculta el mensaje de error
                //OBLIGATORIO LLENAR ALMENOS UN SISTEMA
                // Verifica los checkboxes al cambiar su estado
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function () {
                        const isChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

                        if (isChecked) {
                            submitButton1.disabled = false; // Habilita el botón
                            // ErrorJefe.style.display = 'none'; // Oculta el mensaje de error
                            errorMessage1.style.display = 'none'; // Muestra el mensaje de error

                        } else {
                            submitButton1.disabled = true; // Deshabilita el botón
                            errorMessage1.style.display = 'block'; // Muestra el mensaje de error
                            // ErrorJefe.style.display = 'block'; // Muestra el mensaje de error
                        }
                    });
                });

            } else {
                submitButton.disabled = false; // Deshabilita el botón
                errorMessage.style.display = 'block'; // Muestra el mensaje de error
            }
        });

        const checkboxes1 = document.querySelectorAll('input[name="checkboxes1[]"]');
        const check_Equip = document.getElementById('checkboxes-58');
        checkboxes1.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const isChecked = Array.from(checkboxes1).some(checkbox => checkbox.checked);

                if (isChecked) {
                    check_Equip.checked = true; // Habilita el botónr                    
                    if (isChecked) {
                        submitButton1.disabled = false; // Habilita el botón
                        // ErrorJefe.style.display = 'none'; // Oculta el mensaje de error
                        errorMessage1.style.display = 'none'; // Muestra el mensaje de error

                    } else {
                        submitButton1.disabled = true; // Deshabilita el botón
                        errorMessage1.style.display = 'block'; // Muestra el mensaje de error
                        // ErrorJefe.style.display = 'block'; // Muestra el mensaje de error
                    }
                } else {
                    check_Equip.checked = false;// Deshabilita el botón
                    if (isChecked) {
                        submitButton1.disabled = false; // Habilita el botón
                        // ErrorJefe.style.display = 'none'; // Oculta el mensaje de error
                        errorMessage1.style.display = 'none'; // Muestra el mensaje de error

                    } else {
                        submitButton1.disabled = true; // Deshabilita el botón
                        errorMessage1.style.display = 'block'; // Muestra el mensaje de error
                        // ErrorJefe.style.display = 'block'; // Muestra el mensaje de error
                    }
                }
            });
        });

        // CÓDIGO PARA LA BÚSQUEDA DE USUARIO
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const allInputs = form.querySelectorAll('input, select, textarea');
            //const submitButton = document.getElementById('submitButton');

            // Función para validar el formulario completo
            function validateForm() {
                let isValid = true;

                // Validar campos obligatorios
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                // Validar formato de DNI (8 dígitos)
                const dniField = document.getElementById('txt_DNI');
                if (dniField.value && !/^\d{8}$/.test(dniField.value)) {
                    isValid = false;
                    dniField.classList.add('is-invalid');
                    showError(dniField, 'El DNI debe tener 8 dígitos');
                }

                // Validar formato de IP
                const ipField = document.getElementById('txt_IP');
                if (ipField.value && !/^(\d{1,3}\.){3}\d{1,3}$/.test(ipField.value)) {
                    isValid = false;
                    ipField.classList.add('is-invalid');
                    showError(ipField, 'Formato de IP inválido');
                }

                // Validar que al menos un checkbox esté seleccionado
                const checkboxes = form.querySelectorAll('input[type="checkbox"]');
                const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
                if (!anyChecked) {
                    isValid = false;
                    document.getElementById('errorMessage1').style.display = 'block';
                } else {
                    document.getElementById('errorMessage1').style.display = 'none';
                }

                return isValid;
            }

            // Mostrar mensaje de error personalizado
            function showError(field, message) {
                let errorDiv = field.nextElementSibling;
                if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    field.parentNode.insertBefore(errorDiv, field.nextSibling);
                }
                errorDiv.textContent = message;
            }

            // Validar al enviar el formulario
            form.addEventListener('submit', function (event) {
                if (!validateForm()) {
                    event.preventDefault();
                    document.getElementById('errorMessage').style.display = 'block';
                } else {
                    document.getElementById('errorMessage').style.display = 'none';
                }
            });

            // Validar en tiempo real
            allInputs.forEach(input => {
                input.addEventListener('input', function () {
                    validateForm();
                });
            });

            // Mejorar la funcionalidad de búsqueda usuario
            const btnBuscar = document.getElementById('btnBuscar');
            btnBuscar.addEventListener('click', function () {
                const dniBuscar = document.getElementById('txt_DNI_Buscar').value.trim();

                if (dniBuscar === '') {
                    showError(document.getElementById('txt_DNI_Buscar'), 'Ingrese un dato para buscar');
                    return;
                }


                fetch(window.location.href + '?action=buscar&dni_buscar=' + encodeURIComponent(dniBuscar))
                    .then(response => response.json())
                    .then(data => {
                        btnBuscar.innerHTML = 'Buscar';
                        btnBuscar.disabled = false;

                        if (data.error) {
                            // Mostrar mensaje de error en un toast
                            showToast('Error', data.error, 'danger');
                            return;
                        }

                        // Llenar los campos con los datos
                        document.getElementById('txt_DNI').value = data.dni || '';
                        document.getElementById('txt_name').value = data.nombres || '';
                        document.getElementById('txt_ape_pat').value = data.apellido_paterno || '';
                        document.getElementById('txt_ape_mat').value = data.apellido_materno || '';
                        document.getElementById('txt_ofic').value = data.oficina_usuario || '';
                        //document.getElementById('txt_area').value = data.area || '';
                        let area = data.area;
                        if (data.area === "UNIDAD DE TECNOLOGIAS DE LA INFORMACION") {
                            document.getElementById('txt_area').value = "UTI";
                        }
                        else {
                            document.getElementById('txt_area').value = data.area;
                        }
                        let condicion_laboral = data.condicion_laboral;
                        if (data.condicion_laboral === "PRACTICAS PRO") {
                            document.getElementById('txt_cond_lab').value = "PRÁCTICAS PROFESIONALES";
                            if (data.condicion_laboral === "PRACTICAS PRE") {
                                document.getElementById('txt_cond_lab').value = "PRÁCTICAS PREPROFESIONALES";
                            }
                        }
                        else {
                            document.getElementById('txt_cond_lab').value = data.condicion_laboral;
                        }
                        document.getElementById('txt_carg').value = data.cargo || '';

                        // Mostrar mensaje de éxito
                        showToast('Éxito', 'Usuario encontrado', 'success');
                    })
                    .catch(error => {
                        btnBuscar.innerHTML = 'Buscar';
                        btnBuscar.disabled = false;
                        showToast('Error', 'Error en la búsqueda', 'danger');
                    });
            });

            // Mejorar la funcionalidad de búsqueda jefe
            const btnBuscarJefe = document.getElementById('btnBuscarJefe');
            const inputDniJefe = document.getElementById('txt_dni_jef');

            function buscarJefe() {
                const dniBuscarJefe = inputDniJefe.value.trim();

                if (dniBuscarJefe === '') {
                    showError(inputDniJefe, 'Ingrese un DNI para buscar');
                    return;
                }

                // Mostrar spinner de carga
                btnBuscarJefe.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Buscando...';
                btnBuscarJefe.disabled = true;

                // Cambia 'buscarJefe' por el nombre de acción que espera tu backend
                // Por ejemplo: 'buscar_jefe', 'buscarSupervisor', etc.
                fetch(window.location.href + '?action=buscar&dni_buscar=' + encodeURIComponent(dniBuscarJefe))
                    .then(response => response.json())
                    .then(data => {
                        btnBuscarJefe.innerHTML = 'Buscar';
                        btnBuscarJefe.disabled = false;

                        if (data.error) {
                            // Mostrar mensaje de error en un toast
                            showToast('Error', data.error, 'danger');
                            return;
                        }

                        // Llenar los campos con los datos del jefe
                        document.getElementById('txt_dni_jef').value = data.dni || '';
                        document.getElementById('txt_name_jef').value = data.nombres || '';
                        document.getElementById('txt_ape_pat_jef').value = data.apellido_paterno || '';
                        document.getElementById('txt_ape_mat_jef').value = data.apellido_materno || '';
                        document.getElementById('txt_carg_jef').value = data.cargo;

                        // Mostrar mensaje de éxito
                        showToast('Éxito', 'Jefe encontrado', 'success');
                    })
                    .catch(error => {
                        btnBuscarJefe.innerHTML = 'Buscar';
                        btnBuscarJefe.disabled = false;
                        showToast('Error', 'Error en la búsqueda del jefe', 'danger');
                        console.error('Error en la búsqueda:', error);
                    });
            }

            // Acción de clic en el botón "Buscar"
            btnBuscarJefe.addEventListener('click', buscarJefe);

            // Acción de presionar Enter en el campo de DNI
            inputDniJefe.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Evita el comportamiento por defecto del Enter (puede hacer submit si está en un formulario)
                    buscarJefe();
                }
            });




            // Función para mostrar mensajes toast
            function showToast(title, message, type) {
                const toastContainer = document.getElementById('toast-container');
                if (!toastContainer) {
                    const container = document.createElement('div');
                    container.id = 'toast-container';
                    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                    document.body.appendChild(container);
                }

                const toastId = 'toast-' + Date.now();
                const toastHTML = `
                <div class="toast" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-${type} text-white">
                        <strong class="me-auto">${title}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">${message}</div>
                </div>
            `;

                document.getElementById('toast-container').innerHTML += toastHTML;
                const toastElement = new bootstrap.Toast(document.getElementById(toastId));
                toastElement.show();

                // Eliminar el toast después de 5 segundos
                setTimeout(() => {
                    const element = document.getElementById(toastId);
                    if (element) element.remove();
                }, 5000);
            }

            // Función para manejar dinámicamente los cambios en la condición laboral
            document.getElementById('txt_cond_lab').addEventListener('change', function () {
                const divExterno = document.getElementById('Div_Externo');
                divExterno.style.display = this.value === 'Externo' ? 'block' : 'none';

                // Si cambia a Externo, hacer que el campo de explicación sea requerido
                const txtExterno = document.getElementById('txt_Externo');
                if (this.value === 'Externo') {
                    txtExterno.setAttribute('required', '');
                } else {
                    txtExterno.removeAttribute('required');
                }
            });
        });
        $(document).ready(function () {
            $('#txt_DNI_Buscar').select2({
                placeholder: "Escriba un nombre o DNI...",
                allowClear: true,
                ajax: {
                    url: window.location.href + '?action=buscar_usuarios',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { term: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    }
                }
            });


        });
    </script>
</body>

</html>