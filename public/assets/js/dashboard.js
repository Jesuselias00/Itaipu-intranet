/**
 * dashboard.js - Funcionalidades específicas del dashboard
 * Gestiona estadísticas y gráficos
 */

// URL base para las solicitudes API
const API_BASE_URL = window.location.pathname.includes('Itaipu-intranet') 
    ? '/Itaipu-intranet/api' 
    : '/api';

// Cargar datos de estadísticas
async function loadStats() {
    try {
        // En un entorno real, estas serían llamadas a API
        // const response = await fetch(`${API_BASE_URL}/dashboard/stats`);
        // const stats = await response.json();
        
        // Por ahora, usamos datos de ejemplo
        const stats = {
            funcionarios: 124,
            crachasActivos: 118,
            departamentos: 12,
            crachasPendientes: 6
        };
        
        // Actualizar los valores en el DOM
        document.getElementById('total-funcionarios').textContent = stats.funcionarios;
        document.getElementById('total-crachas').textContent = stats.crachasActivos;
        document.getElementById('total-departamentos').textContent = stats.departamentos;
        document.getElementById('crachas-pendientes').textContent = stats.crachasPendientes;
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

// Cargar los últimos funcionarios agregados
async function loadRecentEmployees() {
    try {
        // En un entorno real, esta sería una llamada a la API
        // const response = await fetch(`${API_BASE_URL}/funcionarios/recientes`);
        // const data = await response.json();
        
        // Datos de ejemplo
        const recentEmployees = [
            { id: 1, nombre: 'Ana Silva', cargo: 'Analista de Sistemas', departamento: 'TI', fecha: '2024-05-15', foto: null },
            { id: 2, nombre: 'Carlos Mendez', cargo: 'Ingeniero Civil', departamento: 'Ingeniería', fecha: '2024-05-10', foto: null },
            { id: 3, nombre: 'Maria González', cargo: 'Contadora', departamento: 'Finanzas', fecha: '2024-04-28', foto: null },
            { id: 4, nombre: 'Juan Perez', cargo: 'Técnico Electromecánico', departamento: 'Mantenimiento', fecha: '2024-04-22', foto: null },
            { id: 5, nombre: 'Laura Martínez', cargo: 'Recepcionista', departamento: 'Administración', fecha: '2024-04-15', foto: null }
        ];
        
        const recentUsersList = document.getElementById('recent-users-list');
        
        if (recentUsersList) {
            // Limpiar contenido previo
            recentUsersList.innerHTML = '';
            
            // Agregar cada empleado a la lista
            recentEmployees.forEach(emp => {
                const listItem = document.createElement('li');
                
                const avatar = document.createElement('div');
                avatar.className = 'user-avatar';
                
                if (emp.foto) {
                    const img = document.createElement('img');
                    img.src = emp.foto;
                    img.alt = emp.nombre;
                    avatar.appendChild(img);
                } else {
                    // Placeholder para usuarios sin foto
                    avatar.innerHTML = `<i class="fas fa-user"></i>`;
                    avatar.style.backgroundColor = '#f1f3f4';
                    avatar.style.display = 'flex';
                    avatar.style.alignItems = 'center';
                    avatar.style.justifyContent = 'center';
                    avatar.style.color = '#5f6368';
                }
                
                const userInfo = document.createElement('div');
                userInfo.className = 'user-info';
                
                const name = document.createElement('div');
                name.className = 'name';
                name.textContent = emp.nombre;
                
                const details = document.createElement('div');
                details.className = 'details';
                details.textContent = `${emp.cargo} - ${emp.departamento}`;
                
                userInfo.appendChild(name);
                userInfo.appendChild(details);
                
                const date = document.createElement('div');
                date.className = 'user-date';
                
                // Formatear fecha
                const empDate = new Date(emp.fecha);
                date.textContent = empDate.toLocaleDateString();
                
                listItem.appendChild(avatar);
                listItem.appendChild(userInfo);
                listItem.appendChild(date);
                
                recentUsersList.appendChild(listItem);
            });
        }
    } catch (error) {
        console.error('Error al cargar funcionarios recientes:', error);
    }
}

// Crear un gráfico de departamentos
function createDepartmentChart() {
    const ctx = document.getElementById('dept-chart');
    
    if (ctx) {
        // Datos de ejemplo para el gráfico
        const deptData = {
            labels: ['TI', 'Ingeniería', 'Finanzas', 'RR.HH.', 'Mantenimiento', 'Otros'],
            datasets: [{
                label: 'Número de funcionarios',
                data: [25, 32, 18, 14, 29, 6],
                backgroundColor: [
                    'rgba(26, 115, 232, 0.7)',
                    'rgba(52, 168, 83, 0.7)',
                    'rgba(251, 188, 5, 0.7)',
                    'rgba(234, 67, 53, 0.7)',
                    'rgba(66, 133, 244, 0.7)',
                    'rgba(150, 150, 150, 0.7)'
                ],
                borderColor: [
                    'rgba(26, 115, 232, 1)',
                    'rgba(52, 168, 83, 1)',
                    'rgba(251, 188, 5, 1)',
                    'rgba(234, 67, 53, 1)',
                    'rgba(66, 133, 244, 1)',
                    'rgba(150, 150, 150, 1)'
                ],
                borderWidth: 1
            }]
        };
        
        new Chart(ctx, {
            type: 'doughnut',
            data: deptData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
}

// Inicializar el dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Cargar datos y estadísticas
    loadStats();
    loadRecentEmployees();
    
    // Crear gráficos después de un pequeño delay para asegurar que Chart.js está cargado
    setTimeout(() => {
        createDepartmentChart();
    }, 100);
});
