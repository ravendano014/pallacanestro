<?php
// Configuración del sistema CRUD
class CRUDConfig {
    
    // Configuración de tablas
    public static $tables = [
        'players' => [
            'name' => 'Jugadores',
            'table' => 'Players',
            'primary_key' => 'player_id',
            'fields' => [
                'first_name' => ['label' => 'Nombre', 'required' => true, 'type' => 'text'],
                'last_name' => ['label' => 'Apellido', 'required' => true, 'type' => 'text'],
                'gender' => ['label' => 'Género', 'required' => false, 'type' => 'select', 'options' => ['M' => 'Masculino', 'F' => 'Femenino']],
                'address' => ['label' => 'Dirección', 'required' => false, 'type' => 'text'],
                'other_details' => ['label' => 'Otros Detalles', 'required' => false, 'type' => 'textarea']
            ]
        ],
        'leagues' => [
            'name' => 'Ligas',
            'table' => 'Leagues',
            'primary_key' => 'league_id',
            'fields' => [
                'league_name' => ['label' => 'Nombre de la Liga', 'required' => true, 'type' => 'text'],
                'league_details' => ['label' => 'Detalles de la Liga', 'required' => false, 'type' => 'textarea']
            ]
        ],
        'matches' => [
            'name' => 'Partidos',
            'table' => 'Matches',
            'primary_key' => 'match_id',
            'fields' => [
                'game_code' => ['label' => 'Código del Juego', 'required' => true, 'type' => 'text'],
                'player_1_id' => ['label' => 'Jugador 1', 'required' => true, 'type' => 'select'],
                'player_2_id' => ['label' => 'Jugador 2', 'required' => true, 'type' => 'select'],
                'court_id' => ['label' => 'Cancha', 'required' => true, 'type' => 'select'],
                'match_date' => ['label' => 'Fecha y Hora del Partido', 'required' => true, 'type' => 'datetime'],
                'result' => ['label' => 'Resultado', 'required' => false, 'type' => 'text'],
                'other_details' => ['label' => 'Otros Detalles', 'required' => false, 'type' => 'textarea']
            ]
        ]
    ];
    
    // Mensajes del sistema
    public static $messages = [
        'success' => [
            'create' => 'Registro creado exitosamente',
            'update' => 'Registro actualizado exitosamente',
            'delete' => 'Registro eliminado exitosamente'
        ],
        'error' => [
            'create' => 'Error al crear el registro',
            'update' => 'Error al actualizar el registro',
            'delete' => 'Error al eliminar el registro',
            'not_found' => 'Registro no encontrado'
        ]
    ];
    
    // Configuración de paginación
    public static $pagination = [
        'records_per_page' => 10
    ];
}
?>