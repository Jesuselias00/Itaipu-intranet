/**
 * common.js - Funcionalidades comunes para todas las páginas
 * Gestiona el menú responsive y otras operaciones compartidas
 */

// Função para verificar o estado de autenticação
function checkAuth() {
    // Obter o token do armazenamento local
    const token = localStorage.getItem('auth_token');
    
    // Se não há token, redirecionar para o login
    if (!token) {
        window.location.href = 'login.html';
        return;
    }
    
    // Aqui poderia validar o token com o servidor
    // ou decodificar um JWT para verificar sua validade/expiração
}

// Configuración del menú responsive
function setupMenu() {
    const menuToggle = document.getElementById('menu-toggle');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-active');
        });
    }

    // Configurar menús desplegables en la sidebar
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parentLi = this.closest('li');
            
            // Si ya está activo, cerrar el menú
            if (parentLi.classList.contains('submenu-open')) {
                parentLi.classList.remove('submenu-open');
                return;
            }
            
            // Cerrar todos los otros submenús
            document.querySelectorAll('.has-submenu').forEach(item => {
                if (item !== parentLi) {
                    item.classList.remove('submenu-open');
                }
            });
            
            // Abrir el submenú actual
            parentLi.classList.add('submenu-open');
        });
    });

    // Abrir automáticamente el menú que contiene el elemento activo
    const activeSubmenuItem = document.querySelector('.submenu .active');
    if (activeSubmenuItem) {
        const parentMenu = activeSubmenuItem.closest('.has-submenu');
        if (parentMenu) {
            parentMenu.classList.add('submenu-open');
        }
    }
}

// Configurar cierre de sesión
function setupLogout() {
    const logoutBtn = document.getElementById('logout-btn');
    
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Limpiar datos de sesión
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_info');
            
            // Redireccionar a la página de login
            window.location.href = 'login.html';
        });
    }
}

// Función para cargar la información del usuario actual
function loadUserInfo() {
    const userInfoString = localStorage.getItem('user_info');
    
    if (userInfoString) {
        try {
            const userInfo = JSON.parse(userInfoString);
            const userNameElement = document.querySelector('.user-name');
            const userRoleElement = document.querySelector('.user-role');
            
            if (userNameElement && userInfo.nombre) {
                userNameElement.textContent = userInfo.nombre;
            }
            
            if (userRoleElement && userInfo.rol) {
                userRoleElement.textContent = userInfo.rol;
            }
        } catch (e) {
            console.error('Error al cargar la información del usuario:', e);
        }
    }
}

// Configuración de modales
function setupModals() {
    // Selectores para abrir y cerrar modales
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modalClosers = document.querySelectorAll('.close-modal, .cancel-modal');
    
    // Abrir modales
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            
            if (modal) {
                modal.classList.add('show');
            }
        });
    });
    
    // Cerrar modales
    modalClosers.forEach(closer => {
        closer.addEventListener('click', () => {
            const modal = closer.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
            }
        });
    });
    
    // Cerrar modal haciendo clic fuera del contenido
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal') && e.target.classList.contains('show')) {
            e.target.classList.remove('show');
        }
    });
}

// Função para fechar modais
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

// Função para mostrar notificações temporárias
function showNotification(message, type = 'info') {
    // Criar o elemento de notificação se não existir
    let notification = document.querySelector('.notification-toast');
    
    if (!notification) {
        notification = document.createElement('div');
        notification.className = 'notification-toast';
        document.body.appendChild(notification);
    }
    
    // Configurar classe de acordo com o tipo de notificação
    notification.className = 'notification-toast';
    notification.classList.add(type);
    
    // Definir a mensagem
    notification.textContent = message;
    
    // Mostrar y luego ocultar después de un tiempo
    notification.classList.add('show');
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 5000);
}

// Inicialización cuando se carga el documento
document.addEventListener('DOMContentLoaded', function() {
    // Verificar autenticación para todas las páginas excepto login
    if (!window.location.pathname.includes('login.html')) {
        checkAuth();
    }
    
    // Configurar componentes de UI comunes
    setupMenu();
    setupLogout();
    loadUserInfo();
    setupModals();
    
    // Marcar la opción activa del menú basada en la URL actual
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-links li a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (currentPath.includes(href) && href !== '#') {
            link.parentElement.classList.add('active');
        }
    });
});
