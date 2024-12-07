<?php
require('./class/Libros.php');
session_start();

if (!isset($_SESSION['Libros'])) {
    $_SESSION['Libros'] = [];
}

$Libros =  $_SESSION['Libros'];

// CREAR LIBRO
if (isset($_POST['createForm'])) {
    if (isset($_POST['tituloFrm'], $_POST['autorFrm'], $_POST['categoriaFrm'])) {


        $id = count($Libros) + 1;
        $titulo = $_POST['tituloFrm'];
        $autor = $_POST['autorFrm'];
        $categoria = $_POST['categoriaFrm'];

        $libro = new Libro($id, $titulo, $autor, $categoria);
        array_push($Libros, $libro);
        $_SESSION['Libros'] = $Libros;

        header('Location: /actividad_bibliotech/');
    }
}

// ACTUALIZAR LIBRO
if (isset($_POST['updateForm'])) {
    foreach ($Libros as $libro) {
        if ($libro->getId() == $_POST['id']) {
            $libro->setTitulo($_POST['tituloFrm']);
            $libro->setAutor($_POST['autorFrm']);
            $libro->setCategoria($_POST['categoriaFrm']);
        }
    }
    header('Location: /actividad_bibliotech/');
}

// ELIMINAR LIBRO
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    foreach ($Libros as $key => $libro) {
        if ($libro->getId() == $id) {
            unset($Libros[$key]);
            break;
        }
    }
    $_SESSION['Libros'] = $Libros;
    header('Location: /actividad_bibliotech/');
}

function getLibroPorID($id, $Libros)
{
    foreach ($Libros as $libro) {
        if ($libro->getId() == $id) {
            return $libro;
        }
    }
}

// SOLICITAR PRESTAMO
if (isset($_GET['prestamo'])) {
    $id = $_GET['prestamo'];
    foreach ($Libros as $libro) {
        if ($libro->getId() == $id && $libro->getDisponible()) {
            // (no disponible)
            $libro->setDisponible(false);
            $_SESSION['Libros'] = $Libros;
            break;
        }
    }
    header('Location: /actividad_bibliotech/');
}

// DEVOLVER LIBRO
if (isset($_GET['devolver'])) {
    $id = $_GET['devolver'];
    foreach ($Libros as $libro) {
        if ($libro->getId() == $id && !$libro->getDisponible()) {
            // (disponible)
            $libro->setDisponible(true);
            $_SESSION['Libros'] = $Libros;
            break;
        }
    }
    header('Location: /actividad_bibliotech/');
}

// IMPLEMENTACION DE BUSQUEDA
if (isset($_GET['buscarPor']) && isset($_GET['terminoBusqueda'])) {
    $criterioBusqueda = $_GET['buscarPor'];
    $terminoBusqueda = $_GET['terminoBusqueda'];

    $LibrosFiltrados = [];

    foreach ($Libros as $libro) {
        // Filtrar por título
        if ($criterioBusqueda == 'titulo' && stripos($libro->getTitulo(), $terminoBusqueda) !== false) {
            $LibrosFiltrados[] = $libro;
        }
        // Filtrar por autor
        elseif ($criterioBusqueda == 'autor' && stripos($libro->getAutor(), $terminoBusqueda) !== false) {
            $LibrosFiltrados[] = $libro;
        }
        // Filtrar por categoría
        elseif ($criterioBusqueda == 'categoria' && stripos($libro->getCategoria(), $terminoBusqueda) !== false) {
            $LibrosFiltrados[] = $libro;
        }
    }
} else {
    $LibrosFiltrados = $Libros; // Si no hay búsqueda, mostrar todos los libros
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD BiblioTech</title>
</head>

<body style="background-color: #414B56; color: white;">
    <h1>Bibliotech</h1>

    <form style="margin-bottom: 10px" method="GET" action="">
        <label for="buscarPor">Buscar por:</label>
        <select name="buscarPor" id="buscarPor">
            <option value="titulo" <?php echo isset($_GET['buscarPor']) && $_GET['buscarPor'] == 'titulo' ? 'selected' : ''; ?>>Título</option>
            <option value="autor" <?php echo isset($_GET['buscarPor']) && $_GET['buscarPor'] == 'autor' ? 'selected' : ''; ?>>Autor</option>
            <option value="categoria" <?php echo isset($_GET['buscarPor']) && $_GET['buscarPor'] == 'categoria' ? 'selected' : ''; ?>>Categoría</option>
        </select>

        <input type="text" name="terminoBusqueda" value="<?php echo isset($_GET['terminoBusqueda']) ? $_GET['terminoBusqueda'] : ''; ?>" placeholder="Ingrese el término de búsqueda">
        <button type="submit">Buscar</button>
    </form>

    <?php if (isset($_GET['edit'])) {
        $libroEditable = getLibroPorID($_GET['edit'], $Libros);
    ?>

        <form method="POST" action="">
            <input type="hidden" name="updateForm">
            <input type="hidden" name="id" value="<?php echo $libroEditable->getId() ?>">

            <label>Titulo del libro</label>
            <input type="text" name="tituloFrm" value=<?php echo $libroEditable->getTitulo() ?>>

            <label>Autor del libro</label>
            <input type="text" name="autorFrm" value=<?php echo $libroEditable->getAutor() ?>>

            <label>Categoria</label>
            <input type="text" name="categoriaFrm" value=<?php echo $libroEditable->getCategoria() ?>>

            <button type="submit">Editar Libro</button>
        </form>
    <?php } else { ?>

        <form method="POST" action="" name="createForm">
            <input type="hidden" name="createForm" value="soy el create">

            <label>Titulo del Libro</label>
            <input type="text" name="tituloFrm">

            <label>Autor del Libro</label>
            <input type="text" name="autorFrm">

            <label>Categoria</label>
            <select name="categoriaFrm">
                <option value="Ficcion">Ficcion</option>
                <option value="MisterioySuspenso">Misterio y Suspenso</option>
                <option value="CienciaFiccion">Ciencia Ficcion</option>
                <option value="Romantico">Romantico</option>
                <option value="Autoayuda">Autoayuda</option>
                <option value="BiografiayMemorias">Biografia y Memorias</option>
                <option value="Historia">Historia</option>
                <option value="Otra">Otra</option>
            </select>

            <button type="submit">Registrar libro</button>
        </form>
    <?php } ?>
    <main>
        <table border="1" cellpadding="5">
            <thead>
                <th>ID</th>
                <th>Titulo</th>
                <th>Autor</th>
                <th>Categoria</th>
                <th>Disponible</th>
                <th>Acciones</th>
            </thead>
            <tbody>
                <?php foreach ($LibrosFiltrados as $libro): ?>
                    <tr>
                        <td><?php echo $libro->getId(); ?></td>
                        <td><?php echo $libro->getTitulo(); ?></td>
                        <td><?php echo $libro->getAutor(); ?></td>
                        <td><?php echo $libro->getCategoria(); ?></td>
                        <td><?php echo $libro->getDisponible() ? 'Sí' : 'No'; ?></td>
                        <td>
                            <a style="text-decoration: underline; color: white;" href='?edit=<?php echo $libro->getId(); ?>'>Editar</a>
                            <a style="text-decoration: underline; color: white;" href='?delete=<?php echo $libro->getId(); ?>'>Eliminar</a>
                            <a style="text-decoration: underline; color: white;" href='?prestamo=<?php echo $libro->getId(); ?>'>Solicitar Prestamo</a>

                            <?php if (!$libro->getDisponible()): ?>
                                <a style="text-decoration: underline; color: white;" href='?devolver=<?php echo $libro->getId(); ?>'>Devolver Libro</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>

</html>