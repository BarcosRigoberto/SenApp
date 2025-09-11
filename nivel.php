<?php
session_start();
include 'conn.php';

// Inicializar contador de respuestas correctas si no existe
if (!isset($_SESSION['correctas'])) {
    $_SESSION['correctas'] = 0;
}

// Obtener el nivel desde la URL
$nivel = isset($_GET['nivel']) ? (int)$_GET['nivel'] : 1;

// Contar cuántos ejercicios hay en total para este nivel
$queryTotal = "SELECT COUNT(*) as total FROM ejercicio WHERE nivel = ?";
$stmtTotal = $conexion->prepare($queryTotal);
$stmtTotal->bind_param("i", $nivel);
$stmtTotal->execute();
$totalEjercicios = $stmtTotal->get_result()->fetch_assoc()['total'];

// Llevar cuenta de ejercicios completados
if (!isset($_SESSION['ejercicios_completados'])) {
    $_SESSION['ejercicios_completados'] = [];
}

// Obtener ejercicio que no se haya completado aún
$ejerciciosCompletados = isset($_SESSION['ejercicios_completados'][$nivel]) ? 
    $_SESSION['ejercicios_completados'][$nivel] : [];

$query = "SELECT * FROM ejercicio WHERE nivel = ? AND id NOT IN (" . 
    (!empty($ejerciciosCompletados) ? implode(',', $ejerciciosCompletados) : '0') . 
    ") ORDER BY RAND() LIMIT 1";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $nivel);
$stmt->execute();
$result = $stmt->get_result();
$ejercicio = $result->fetch_assoc();

// Si no hay más ejercicios, mostrar pantalla de finalización
$nivelCompletado = !$ejercicio;

// Definir la respuesta correcta (por ejemplo, la primera respuesta no vacía)
$respuestas = ['A', 'B', 'C', 'D'];
$respuestaCorrecta = '';
foreach ($respuestas as $letra) {
    if (!empty($ejercicio['rta'.$letra])) {
        $respuestaCorrecta = $letra;
        break;
    }
}

// Add this near the top where you define other PHP variables+
$ejercicioId = $ejercicio['id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeñApp - Nivel <?php echo $nivel; ?></title>
    <style>
        .oculto { display: none; }
        .correcto { background-color: #4CAF50; color: white; }
        .incorrecto { background-color: #f44336; color: white; }
        .opcion-btn { margin: 5px; padding: 10px 20px; }
        #siguiente { padding: 10px 20px; margin-top: 20px; }
        #resultado { margin-top: 20px; }
        .nivel-completado {
            text-align: center;
            padding: 20px;
            margin: 20px;
            background-color: #e8f5e9;
            border-radius: 8px;
        }
        .contador-correctas {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="contador-correctas">
        Respuestas correctas: <?php echo $_SESSION['correctas']; ?>
    </div>

    <header>
        <h1>Nivel <?php echo $nivel; ?></h1>
    </header>

    <?php if ($nivelCompletado): ?>
        <div class="nivel-completado">
            <h2>¡Nivel <?php echo $nivel; ?> completado!</h2>
            <p>Has respondido correctamente <?php echo $_SESSION['correctas']; ?> preguntas.</p>
            <button onclick="window.location.href='nivel.php?nivel=<?php echo $nivel + 1; ?>'">
                Ir al siguiente nivel
            </button>
            <button onclick="window.location.href='PagPrincipal.php'">
                Volver al menú principal
            </button>
        </div>
    <?php else: ?>
        <div class="ejercicio-container">
            <!-- Aquí puedes agregar la imagen o video de la seña -->
            
            <div class="opciones">
                <?php
                $respuestas = [
                    'A' => $ejercicio['rtaA'],
                    'B' => $ejercicio['rtaB'],
                    'C' => $ejercicio['rtaC'],
                    'D' => $ejercicio['rtaD']
                ];

                $respuestas = array_filter($respuestas, function($respuesta) {
                    return !empty($respuesta);
                });

                foreach($respuestas as $letra => $respuesta): ?>
                    <button class="opcion-btn" data-respuesta="<?php echo $letra; ?>">
                        <?php echo $respuesta; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div id="resultado" class="oculto">
                <p id="mensaje-resultado"></p>
                <button id="siguiente" class="oculto">Siguiente ejercicio</button>
            </div>
        </div>
    <?php endif; ?>

    <a href="PagPrincipal.php" class="volver-btn">Volver a niveles</a>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const respuestaCorrecta = '<?php echo $respuestaCorrecta; ?>';
            const nivelActual = <?php echo $nivel; ?>;
            const ejercicioId = <?php echo $ejercicio['id']; ?>;
            const botones = document.querySelectorAll('.opcion-btn');
            const resultado = document.getElementById('resultado');
            const mensajeResultado = document.getElementById('mensaje-resultado');
            const siguienteBtn = document.getElementById('siguiente');
            const contadorElement = document.querySelector('.contador-correctas');

            botones.forEach(boton => {
                boton.addEventListener('click', async function() {
                    const respuestaSeleccionada = this.getAttribute('data-respuesta');
                    const esCorrecta = respuestaSeleccionada === respuestaCorrecta;

                    if (esCorrecta) {
                        try {
                            const response = await fetch('actualizar_contador.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    accion: 'incrementar',
                                    nivel: nivelActual,
                                    id: ejercicioId
                                })
                            });
                            const data = await response.json();
                            contadorElement.textContent = `Respuestas correctas: ${data.correctas}`;
                        } catch (error) {
                            console.error('Error:', error);
                        }
                    }

                    // Deshabilitar todos los botones
                    botones.forEach(b => {
                        b.disabled = true;
                        if (b.getAttribute('data-respuesta') === respuestaCorrecta) {
                            b.classList.add('correcto');
                        }
                    });

                    if (!esCorrecta) {
                        this.classList.add('incorrecto');
                    }
                    
                    mensajeResultado.textContent = esCorrecta ? 
                        '¡Correcto!' : 
                        'Incorrecto. La respuesta correcta era ' + respuestaCorrecta;
                    
                    resultado.classList.remove('oculto');

                    // Mostrar opciones después de cada respuesta
                    const opcionesSiguiente = document.createElement('div');
                    opcionesSiguiente.className = 'opciones-siguiente';
                    opcionesSiguiente.innerHTML = `
                        <button onclick="window.location.href='nivel.php?nivel=${nivelActual}'">
                            Siguiente ejercicio
                        </button>
                        <button onclick="window.location.href='nivel.php?nivel=${nivelActual + 1}'">
                            Ir al siguiente nivel
                        </button>
                        <button onclick="window.location.href='PagPrincipal.php'">
                            Volver al menú
                        </button>
                    `;
                    resultado.appendChild(opcionesSiguiente);
                });
            });
        });
    </script>
</body>
</html>