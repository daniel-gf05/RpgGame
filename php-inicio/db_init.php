<?php
/**
 * Script de inicialización de la base de datos para el juego RPG
 * Este script crea las tablas necesarias y las carga con datos iniciales
 */

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rpg_game";
$port = "3307";

// Crear conexión
$conn = new mysqli($servername, $username, $password, NULL, $port);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Crear la base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === FALSE) {
    die("Error al crear la base de datos: " . $conn->error);
}

echo "Base de datos creada correctamente o ya existente.<br>";

// Seleccionar la base de datos
$conn->select_db($dbname);

// Crear tablas

// Tabla de jugadores
$sql = "CREATE TABLE IF NOT EXISTS players (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    level INT DEFAULT 1,
    health INT DEFAULT 100,
    max_health INT DEFAULT 100,
    experience INT DEFAULT 0,
    strength INT DEFAULT 10,
    position_x INT DEFAULT 0,
    position_y INT DEFAULT 0
)";

if ($conn->query($sql) === FALSE) {
    die("Error al crear tabla players: " . $conn->error);
}

echo "Tabla players creada correctamente.<br>";

// Tabla de items
$sql = "CREATE TABLE IF NOT EXISTS items (
    id_item INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    tipo VARCHAR(20) NOT NULL
)";

if ($conn->query($sql) === FALSE) {
    die("Error al crear tabla items: " . $conn->error);
}

echo "Tabla items creada correctamente.<br>";

// Tabla de inventario
$sql = "CREATE TABLE IF NOT EXISTS inventario (
    id_player INT,
    item_id INT NOT NULL,
    PRIMARY KEY (id_player, item_id),
    FOREIGN KEY (id_player) REFERENCES players(id),
    FOREIGN KEY (item_id) REFERENCES items(id_item)
)";

if ($conn->query($sql) === FALSE) {
    die("Error al crear tabla inventario: " . $conn->error);
}

echo "Tabla inventario creada correctamente.<br>";

// Tabla de casillas del mapa
$sql = "CREATE TABLE IF NOT EXISTS map_tiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    x INT NOT NULL,
    y INT NOT NULL,
    type VARCHAR(20) NOT NULL,
    enemy_chance DECIMAL(3,2) DEFAULT 0.0,
    item_chance DECIMAL(3,2) DEFAULT 0.0,
    UNIQUE KEY unique_position (x, y)
)";

if ($conn->query($sql) === FALSE) {
    die("Error al crear tabla map_tiles: " . $conn->error);
}

echo "Tabla map_tiles creada correctamente.<br>";

// Tabla de enemigos
$sql = "CREATE TABLE IF NOT EXISTS enemies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    level INT NOT NULL,
    health INT NOT NULL,
    strength INT NOT NULL,
    experience_reward INT NOT NULL
)";

if ($conn->query($sql) === FALSE) {
    die("Error al crear tabla enemies: " . $conn->error);
}

echo "Tabla enemies creada correctamente.<br>";

// Tabla de estadísticas del jugador
$sql = "CREATE TABLE IF NOT EXISTS player_stats (
    player_id INT PRIMARY KEY,
    battles_won INT DEFAULT 0,
    battles_lost INT DEFAULT 0,
    steps_taken INT DEFAULT 0,
    FOREIGN KEY (player_id) REFERENCES players(id)
)";

if ($conn->query($sql) === FALSE) {
    die("Error al crear tabla player_stats: " . $conn->error);
}

echo "Tabla player_stats creada correctamente.<br>";

// Insertar datos iniciales

// Verificar si ya hay datos en las tablas
$result = $conn->query("SELECT COUNT(*) as count FROM players");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insertar jugador inicial
    $sql = "INSERT INTO players (name, level, health, max_health, experience, strength, position_x, position_y) 
            VALUES ('Aventurero', 1, 100, 100, 0, 10, 1, 1)";
    
    if ($conn->query($sql) === FALSE) {
        die("Error al insertar jugador inicial: " . $conn->error);
    }
    
    echo "Jugador inicial creado.<br>";
    
    // Insertar estadísticas iniciales para el jugador
    $sql = "INSERT INTO player_stats (player_id, battles_won, battles_lost, steps_taken) 
            VALUES (1, 0, 0, 0)";
    
    if ($conn->query($sql) === FALSE) {
        die("Error al insertar estadísticas iniciales: " . $conn->error);
    }
    
    echo "Estadísticas iniciales creadas.<br>";
}

// Verificar si ya hay objetos
$result = $conn->query("SELECT COUNT(*) as count FROM items");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insertar items básicos (inspirados en D&D)
    $items = [
        ['Espada corta', 'Una espada básica para principiantes.', 'arma'],
        ['Escudo de madera', 'Un escudo simple hecho de madera.', 'escudo'],
        ['Poción de curación', 'Restaura 50 puntos de salud.', 'consumible'],
        ['Armadura de cuero', 'Proporciona una ligera protección.', 'armadura'],
        ['Antorcha', 'Útil para iluminar áreas oscuras.', 'herramienta'],
        ['Arco corto', 'Un arco pequeño para ataques a distancia.', 'arma'],
        ['Flecha', 'Munición para el arco.', 'munición'],
        ['Libro de hechizos', 'Contiene un hechizo básico.', 'magia'],
        ['Botas de viajero', 'Aumenta la velocidad de movimiento.', 'ropa'],
        ['Amuleto de la suerte', 'Incrementa ligeramente la probabilidad de golpe crítico.', 'accesorio']
    ];
    
    $stmt = $conn->prepare("INSERT INTO items (name, description, tipo) VALUES (?, ?, ?)");
    $stmt->bind_param($name, $description, $tipo);
    
    foreach ($items as $item) {
        $name = $item[0];
        $description = $item[1];
        $tipo = $item[2];
        
        $stmt->execute();
    }
    
    echo "Items iniciales creados.<br>";
}

// Verificar si ya hay enemigos
$result = $conn->query("SELECT COUNT(*) as count FROM enemies");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insertar enemigos básicos (inspirados en D&D)
    $enemies = [
        ['Goblin', 1, 15, 8, 20],
        ['Kobold', 1, 12, 7, 15],
        ['Rata gigante', 1, 10, 5, 10],
        ['Esqueleto', 2, 20, 10, 25],
        ['Orco', 2, 25, 12, 30],
        ['Lobo', 2, 18, 9, 20],
        ['Troll', 3, 40, 15, 50],
        ['Ogro', 3, 35, 13, 45],
        ['Mantícora', 4, 50, 18, 70],
        ['Dragón joven', 5, 80, 25, 100]
    ];
    
    $stmt = $conn->prepare("INSERT INTO enemies (name, level, health, strength, experience_reward) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siiii", $name, $level, $health, $strength, $exp_reward);
    
    foreach ($enemies as $enemy) {
        $name = $enemy[0];
        $level = $enemy[1];
        $health = $enemy[2];
        $strength = $enemy[3];
        $exp_reward = $enemy[4];
        
        $stmt->execute();
    }
    
    echo "Enemigos iniciales creados.<br>";
}

// Verificar si ya hay mapa
$result = $conn->query("SELECT COUNT(*) as count FROM map_tiles");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo "No hay mapa creado. El mapa se generará automáticamente cuando se solicite por primera vez.<br>";
}

echo "<br>¡Inicialización de la base de datos completada con éxito!";

// Cerrar la conexión
$conn->close();
?>