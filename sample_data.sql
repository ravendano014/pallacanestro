-- Archivo SQL para crear la tabla Teams y poblarla con datos de ejemplo
-- Ejecuta este script en tu base de datos MySQL

-- Crear la tabla Teams si no existe
CREATE TABLE IF NOT EXISTS Teams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50),
    coach VARCHAR(100),
    founded_year INT,
    logo_url VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar datos de ejemplo
INSERT INTO Teams (name, city, coach, founded_year, description) VALUES
('Lakers de Los Ángeles', 'Los Ángeles', 'Darvin Ham', 1947, 'Uno de los equipos más exitosos en la historia de la NBA con 17 campeonatos.'),
('Celtics de Boston', 'Boston', 'Joe Mazzulla', 1946, 'Equipo histórico con 17 títulos de la NBA, conocido por su tradición ganadora.'),
('Warriors de Golden State', 'San Francisco', 'Steve Kerr', 1946, 'Equipo conocido por revolucionar el baloncesto moderno con su estilo de juego.'),
('Bulls de Chicago', 'Chicago', 'Billy Donovan', 1966, 'Famoso por la era de Michael Jordan en los años 90.'),
('Spurs de San Antonio', 'San Antonio', 'Gregg Popovich', 1967, 'Organización modelo conocida por su cultura ganadora y desarrollo de jugadores.'),
('Heat de Miami', 'Miami', 'Erik Spoelstra', 1988, 'Equipo conocido por su cultura "Heat" y múltiples campeonatos.'),
('Nets de Brooklyn', 'Brooklyn', 'Jacque Vaughn', 1967, 'Equipo que se trasladó de Nueva Jersey a Brooklyn en 2012.'),
('Knicks de Nueva York', 'Nueva York', 'Tom Thibodeau', 1946, 'Uno de los equipos originales de la NBA con gran tradición en el Madison Square Garden.'),
('Mavericks de Dallas', 'Dallas', 'Jason Kidd', 1980, 'Campeones de la NBA en 2011, liderados por Dirk Nowitzki.'),
('Nuggets de Denver', 'Denver', 'Michael Malone', 1967, 'Actuales campeones de la NBA 2023, liderados por Nikola Jokic.'),
('Suns de Phoenix', 'Phoenix', 'Frank Vogel', 1968, 'Equipo competitivo conocido por su estilo de juego rápido y emocionante.'),
('Thunder de Oklahoma City', 'Oklahoma City', 'Mark Daigneault', 1967, 'Equipo joven con gran potencial y futuro prometedor.'),
('76ers de Filadelfia', 'Filadelfia', 'Nick Nurse', 1949, 'Equipo histórico con tradición desde los primeros días de la NBA.'),
('Kings de Sacramento', 'Sacramento', 'Mike Brown', 1945, 'Equipo con una base de fanáticos apasionada y leal.'),
('Clippers de Los Ángeles', 'Los Ángeles', 'Tyronn Lue', 1970, 'El segundo equipo de Los Ángeles, buscando su primer campeonato.');

-- Verificar que los datos se insertaron correctamente
SELECT 'Datos insertados correctamente' as status, COUNT(*) as total_teams FROM Teams;

-- Mostrar todos los equipos insertados
SELECT id, name, city, coach, founded_year FROM Teams ORDER BY name;
