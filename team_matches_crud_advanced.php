                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="notes">Notas:</label>
                            <textarea name="notes" id="notesTextarea" data-role="textarea" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="button primary" id="submitBtn">Crear Partido</button>
                        <button type="button" class="button secondary" onclick="closeModal()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de Calendario -->
        <div id="calendarModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeCalendarModal()">&times;</span>
                <h3>📅 Vista de Calendario</h3>
                <div id="calendarContainer">
                    <div class="calendar-view">
                        <div id="calendarContent">
                            <!-- El calendario se generará dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="metro.js"></script>
    <script>
        // Variables globales
        let matchModal = null;
        let calendarModal = null;
        
        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            Metro.init();
            
            matchModal = document.getElementById('matchModal');
            calendarModal = document.getElementById('calendarModal');
            
            // Auto-ocultar mensajes toast
            setTimeout(function() {
                const toasts = document.querySelectorAll('.toast');
                toasts.forEach(function(toast) {
                    if (toast) {
                        toast.style.display = 'none';
                    }
                });
            }, 5000);
            
            // Configurar eventos
            setupEventListeners();
        });
        
        function setupEventListeners() {
            // Select all checkbox
            document.getElementById('selectAll').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.match-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleBulkActions();
            });
            
            // Individual checkboxes
            document.querySelectorAll('.match-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', toggleBulkActions);
            });
            
            // Validación del formulario
            document.getElementById('matchForm').addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
            
            // Validación de equipos diferentes
            document.getElementById('homeTeamSelect').addEventListener('change', validateTeams);
            document.getElementById('awayTeamSelect').addEventListener('change', validateTeams);
            
            // Validación de disponibilidad de cancha
            document.getElementById('courtSelect').addEventListener('change', validateCourt);
            document.getElementById('datetimeInput').addEventListener('change', validateCourt);
        }
        
        function toggleBulkActions() {
            const checkedBoxes = document.querySelectorAll('.match-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            
            if (checkedBoxes.length > 0) {
                bulkActions.classList.add('show');
            } else {
                bulkActions.classList.remove('show');
            }
        }
        
        // Funciones del Modal
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Nuevo Partido';
            document.getElementById('formAction').value = 'create';
            document.getElementById('submitBtn').textContent = 'Crear Partido';
            document.getElementById('matchForm').reset();
            clearValidationErrors();
            matchModal.style.display = 'block';
        }
        
        function closeModal() {
            matchModal.style.display = 'none';
        }
        
        function editMatch(matchId) {
            // Hacer petición AJAX para obtener los datos del partido
            fetch(`get_match.php?id=${matchId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateForm(data.match);
                        document.getElementById('modalTitle').textContent = 'Editar Partido';
                        document.getElementById('formAction').value = 'update';
                        document.getElementById('submitBtn').textContent = 'Actualizar Partido';
                        matchModal.style.display = 'block';
                    } else {
                        alert('Error al cargar los datos del partido');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del partido');
                });
        }
        
        function populateForm(match) {
            document.getElementById('matchId').value = match.match_id;
            document.getElementById('tournamentSelect').value = match.tournament_id;
            document.getElementById('jornadaInput').value = match.jornada;
            document.getElementById('juegoInput').value = match.juego;
            document.getElementById('phaseSelect').value = match.phase;
            document.getElementById('datetimeInput').value = match.start_datetime ? match.start_datetime.replace(' ', 'T') : '';
            document.getElementById('homeTeamSelect').value = match.home_team_id;
            document.getElementById('awayTeamSelect').value = match.away_team_id;
            document.getElementById('homeScoreInput').value = match.home_score || '';
            document.getElementById('awayScoreInput').value = match.away_score || '';
            document.getElementById('isByeSelect').value = match.is_bye;
            document.getElementById('byeTeamSelect').value = match.bye_team_id || '';
            document.getElementById('courtSelect').value = match.court_id;
            document.getElementById('statusSelect').value = match.status;
            document.getElementById('walkoverSelect').value = match.walkover_winner || '';
            document.getElementById('notesTextarea').value = match.notes || '';
        }
        
        function deleteMatch(matchId) {
            if (confirm('¿Está seguro de eliminar este partido?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="match_id" value="${matchId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function bulkDelete() {
            const checkedBoxes = document.querySelectorAll('.match-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Seleccione al menos un partido para eliminar');
                return;
            }
            
            if (confirm(`¿Está seguro de eliminar ${checkedBoxes.length} partidos seleccionados?`)) {
                document.getElementById('bulkForm').submit();
            }
        }
        
        // Funciones de Validación
        function validateForm() {
            clearValidationErrors();
            let isValid = true;
            
            // Validar equipos diferentes
            if (!validateTeams()) {
                isValid = false;
            }
            
            // Validar campos requeridos
            const requiredFields = [
                { id: 'tournamentSelect', error: 'tournamentError', message: 'Seleccione un torneo' },
                { id: 'jornadaInput', error: 'jornadaError', message: 'Ingrese la jornada' },
                { id: 'juegoInput', error: 'juegoError', message: 'Ingrese el número de juego' },
                { id: 'phaseSelect', error: 'phaseError', message: 'Seleccione la fase' },
                { id: 'homeTeamSelect', error: 'homeTeamError', message: 'Seleccione el equipo local' },
                { id: 'awayTeamSelect', error: 'awayTeamError', message: 'Seleccione el equipo visitante' },
                { id: 'courtSelect', error: 'courtError', message: 'Seleccione una cancha' },
                { id: 'statusSelect', error: 'statusError', message: 'Seleccione el estado' }
            ];
            
            requiredFields.forEach(field => {
                const element = document.getElementById(field.id);
                if (!element.value.trim()) {
                    showValidationError(field.error, field.message);
                    isValid = false;
                }
            });
            
            return isValid;
        }
        
        function validateTeams() {
            const homeTeam = document.getElementById('homeTeamSelect').value;
            const awayTeam = document.getElementById('awayTeamSelect').value;
            
            if (homeTeam && awayTeam && homeTeam === awayTeam) {
                showValidationError('homeTeamError', 'Los equipos deben ser diferentes');
                showValidationError('awayTeamError', 'Los equipos deben ser diferentes');
                return false;
            }
            
            return true;
        }
        
        function validateCourt() {
            const courtId = document.getElementById('courtSelect').value;
            const datetime = document.getElementById('datetimeInput').value;
            
            if (courtId && datetime) {
                // Aquí podrías hacer una validación AJAX para verificar disponibilidad
                // Por ahora solo mostramos una validación básica
                const now = new Date();
                const selectedDate = new Date(datetime);
                
                if (selectedDate < now) {
                    showValidationError('datetimeError', 'La fecha debe ser futura');
                    return false;
                }
            }
            
            return true;
        }
        
        function showValidationError(errorId, message) {
            document.getElementById(errorId).textContent = message;
        }
        
        function clearValidationErrors() {
            const errorElements = document.querySelectorAll('.validation-error');
            errorElements.forEach(element => {
                element.textContent = '';
            });
        }
        
        // Funciones de Exportación
        function exportData() {
            const format = prompt('Formato de exportación:\n1 - CSV\n2 - Excel\n3 - PDF\n\nIngrese el número:', '1');
            
            if (format) {
                let exportUrl = 'export_matches.php?format=';
                switch(format) {
                    case '1':
                        exportUrl += 'csv';
                        break;
                    case '2':
                        exportUrl += 'excel';
                        break;
                    case '3':
                        exportUrl += 'pdf';
                        break;
                    default:
                        alert('Formato no válido');
                        return;
                }
                
                // Agregar filtros actuales a la URL
                const urlParams = new URLSearchParams(window.location.search);
                exportUrl += '&' + urlParams.toString();
                
                window.open(exportUrl, '_blank');
            }
        }
        
        // Función de Calendario
        function showCalendar() {
            generateCalendar();
            calendarModal.style.display = 'block';
        }
        
        function closeCalendarModal() {
            calendarModal.style.display = 'none';
        }
        
        function generateCalendar() {
            // Aquí generarías un calendario dinámico con los partidos
            const calendarContent = document.getElementById('calendarContent');
            calendarContent.innerHTML = `
                <div style="text-align: center; padding: 20px;">
                    <h4>Calendario de Partidos - ${new Date().toLocaleDateString('es-ES', { month: 'long', year: 'numeric' })}</h4>
                    <p>🚧 Vista de calendario en desarrollo...</p>
                    <p>Esta función mostrará un calendario visual con todos los partidos programados.</p>
                </div>
            `;
        }
        
        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            if (event.target === matchModal) {
                closeModal();
            }
            if (event.target === calendarModal) {
                closeCalendarModal();
            }
        }
        
        // Búsqueda en tiempo real
        let searchTimeout;
        document.querySelector('input[name="search"]').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filtersForm').submit();
            }, 500);
        });
    </script>
</body>
</html>
