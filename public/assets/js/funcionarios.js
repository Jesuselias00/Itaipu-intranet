/**
 * funcionarios.js - Gestão de funcionarios
 * Maneja el CRUD y la interfaz de gestión de funcionarios
 */

// URL base para as requisições da API
const API_BASE_URL = window.location.pathname.includes('Itaipu-intranet') 
    ? '/Itaipu-intranet/api/funcionarios' 
    : '/api/funcionarios';

// Agregar log para depuración
console.log('API_BASE_URL configurada como:', API_BASE_URL);

// Variables globales
let currentPage = 1;
let totalPages = 1;
let funcionarios = [];
let departamentos = [];
let funcionariosJefes = [];
let selectedFuncionarioId = null;

// Inicializar la página de funcionarios
document.addEventListener('DOMContentLoaded', function() {
    // Cargar los datos iniciales
    loadFuncionarios();
    loadDepartamentos();
    
    // Configurar evento para búsqueda
    const searchInput = document.getElementById('search-funcionarios');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            loadFuncionarios(1, this.value);
        }, 500));
    }
    
    // Configurar filtros
    const filterDepartamento = document.getElementById('filter-departamento');
    const filterEstado = document.getElementById('filter-estado');
    
    if (filterDepartamento) {
        filterDepartamento.addEventListener('change', function() {
            loadFuncionarios(1, searchInput ? searchInput.value : '');
        });
    }
    
    if (filterEstado) {
        filterEstado.addEventListener('change', function() {
            loadFuncionarios(1, searchInput ? searchInput.value : '');
        });
    }
    
    // Configurar paginación
    document.getElementById('prev-page').addEventListener('click', function() {
        if (currentPage > 1) {
            loadFuncionarios(currentPage - 1, searchInput ? searchInput.value : '');
        }
    });
    
    document.getElementById('next-page').addEventListener('click', function() {
        if (currentPage < totalPages) {
            loadFuncionarios(currentPage + 1, searchInput ? searchInput.value : '');
        }
    });
    
    // Configurar modal de creación de funcionario
    document.getElementById('btn-add-funcionario').addEventListener('click', function() {
        openCreateModal();
    });
    
    // Configurar formulario
    document.getElementById('funcionario-form').addEventListener('submit', function(e) {
        e.preventDefault();
        handleFuncionarioFormSubmit();
    });
    
    // Configurar a carga de foto
    document.getElementById('trigger-photo-upload').addEventListener('click', function() {
        document.getElementById('foto').click();
    });

    document.getElementById('foto').addEventListener('change', function() {
        displaySelectedImage(this);
        // If a user selects a new photo, it should cancel the removal request.
        document.getElementById('remove_foto_hidden').value = '0';
    });

    const removePhotoButton = document.getElementById('remove-photo');
    if (removePhotoButton) {
        removePhotoButton.addEventListener('click', function() {
            // Clear the file input to prevent resubmission of the old file
            const fotoInput = document.getElementById('foto');
            fotoInput.value = '';

            // Update the preview to show the placeholder
            const photoPreview = document.getElementById('photo-preview');
            photoPreview.innerHTML = '<i class="fas fa-user"></i>';

            // Set the hidden flag that the backend will check
            document.getElementById('remove_foto_hidden').value = '1';

            showNotification('Foto marcada para remoção. Salve as alterações para confirmar.', 'info');
        });
    }

    document.getElementById('fotoFuncionarioPut').addEventListener('change', function() {
        displaySelectedImage(this, 'photo-preview-put');
    });
    
    // Configurar eliminación
    document.getElementById('confirm-delete').addEventListener('click', function() {
        deleteFuncionario(selectedFuncionarioId);
    });
    
    // Configurar modal
    setupModals();
});

// Carregar funcionários da API
async function loadFuncionarios(page = 1, search = '') {
    try {
        showDebugNotification("Iniciando carregamento de funcionários", "info");
        
        // Construir a URL com os filtros
        let url = `${API_BASE_URL}`;
        
        // Adicionar parâmetros de consulta somente se necessário
        const params = new URLSearchParams();
        
        if (page > 1) {
            params.append('page', page);
        }
        
        if (search) {
            params.append('search', search);
        }
        
        const departamentoFilter = document.getElementById('filter-departamento')?.value;
        if (departamentoFilter) {
            params.append('departamento', departamentoFilter);
        }
        
        const estadoFilter = document.getElementById('filter-estado')?.value;
        if (estadoFilter) {
            params.append('estado', estadoFilter);
        }
        
        // Adicionar parâmetros à URL se existirem
        const queryString = params.toString();
        if (queryString) {
            url += `?${queryString}`;
        }
        
        console.log('Carregando funcionários da URL:', url);
        showDebugNotification(`Fazendo requisição para: ${url}`, "info");
        
        // Mostrar estado de carregamento
        const tableBody = document.getElementById('funcionarios-table-body');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr class="loading-row">
                    <td colspan="8">Carregando dados de funcionários...</td>
                </tr>
            `;
        }
        
        // Chamada à API com timeout para evitar que a página se bloqueie indefinidamente
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 segundos timeout
        
        const response = await fetch(url, { 
            signal: controller.signal,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            showDebugNotification(`Erro HTTP: ${response.status} ${response.statusText}`, "error");
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            showDebugNotification(`Resposta não é JSON: ${contentType}`, "error");
            const text = await response.text();
            console.error('Resposta não-JSON recebida:', text.substring(0, 500)); // Primeiros 500 caracteres
            throw new Error('API não retornou JSON');
        }
        
        const data = await response.json();
        console.log('Resposta da API:', data);
        showDebugNotification(`Funcionários carregados: ${data.data ? data.data.length : 0}`, "success");
        
        // Verificar estrutura da resposta
        if (!data.status) {
            console.warn('Resposta da API não tem campo status:', data);
        }
        
        if (data.status === 'error') {
            showDebugNotification(`Erro na API: ${data.message || 'Erro desconhecido'}`, "error");
            throw new Error(data.message || 'Erro desconhecido na API');
        }
        
        // Atualizar variáveis globais
        funcionarios = data.data || [];
        currentPage = data.page || 1;
        totalPages = data.total_pages || 1;
        
        console.log(`Funcionários obtidos: ${funcionarios.length}`);
        
        // Atualizar a UI
        renderFuncionariosTable();
        updatePagination();
    } catch (error) {
        console.error('Erro ao carregar funcionários:', error);
        showDebugNotification(`Erro ao carregar funcionários: ${error.message}`, "error");
        
        const tableBody = document.getElementById('funcionarios-table-body');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr class="loading-row">
                    <td colspan="8">Erro ao carregar dados: ${error.message}</td>
                </tr>
            `;
        }
    }
}

// Renderizar a tabela de funcionários
function renderFuncionariosTable() {
    const tableBody = document.getElementById('funcionarios-table-body');
    
    if (!tableBody) {
        console.error('Elemento funcionarios-table-body não encontrado!');
        return;
    }
    
    if (!funcionarios || !funcionarios.length) {
        tableBody.innerHTML = `
            <tr class="loading-row">
                <td colspan="6">Nenhum funcionário encontrado.</td>
            </tr>
        `;
        return;
    }
    
    console.log('Renderizando tabela com funcionários:', funcionarios);
    let html = '';
    
    funcionarios.forEach(func => {
        // Log detallado para cada funcionario
        console.log('Processando funcionario:', func);
        // Garantir que temos um ID válido como string
        const funcId = String(func.id_funcionario || func.id || '');
        console.log('ID usado (string):', funcId, typeof funcId);
        
        // Construir nome completo com verificação de valores nulos
        const nome = func.nome || '';
        const sobrenome = func.sobrenome || '';
        const nomeCompleto = `${nome} ${sobrenome}`.trim();
        console.log('Nome completo:', nomeCompleto);
        
        // Verificar se temos uma imagem
        let fotoHtml = '<div class="placeholder"><i class="fas fa-user"></i></div>';
        console.log('Tipo de foto:', typeof func.foto, func.foto ? 'presente' : 'ausente');
        console.log('Foto base64:', typeof func.foto_base64, func.foto_base64 ? 'presente' : 'ausente');
        
        if (func.foto_base64) {
            fotoHtml = `<img src="${func.foto_base64}" alt="${nomeCompleto}">`;
            console.log('Usando foto_base64');
        } else if (typeof func.foto === 'string' && func.foto.startsWith('data:image')) {
            fotoHtml = `<img src="${func.foto}" alt="${nomeCompleto}">`;
            console.log('Usando func.foto diretamente');
        }
        
        const dataAtualizacao = func.data_atualizacao || func.ultima_modificacao_registro;
        console.log('Data atualização:', dataAtualizacao);
        
        html += `
            <tr>
                <td>
                    <div class="employee-photo">
                        ${fotoHtml}
                    </div>
                </td>
                <td>${funcId}</td>
                <td>${nomeCompleto || '-'}</td>
                <td>${func.email || '-'}</td>
                <td>${formatDate(dataAtualizacao) || '-'}</td>
                <td>
                    <div class="row-actions">
                        <button class="action-btn view" onclick="viewFuncionario('${funcId}')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn edit" onclick="editFuncionario('${funcId}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete" onclick="confirmDeleteFuncionario('${funcId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

// Atualizar a paginação
function updatePagination() {
    document.getElementById('page-info').textContent = `Página ${currentPage} de ${totalPages}`;
    document.getElementById('prev-page').disabled = currentPage <= 1;
    document.getElementById('next-page').disabled = currentPage >= totalPages;
}

// Carregar departamentos para os seletores
async function loadDepartamentos() {
    try {
        // Em um ambiente real, isto seria uma chamada à API
        // const response = await fetch('/api/departamentos');
        // const data = await response.json();
        
        // Por enquanto usamos dados de exemplo
        departamentos = [
            { id: 1, nome: 'TI' },
            { id: 2, nome: 'Engenharia' },
            { id: 3, nome: 'Finanças' },
            { id: 4, nome: 'Recursos Humanos' },
            { id: 5, nome: 'Manutenção' }
        ];
        
        // Preencher o seletor de filtro de departamentos
        const filterSelect = document.getElementById('filter-departamento');
        let options = '<option value="">Todos</option>';
        
        departamentos.forEach(dept => {
            options += `<option value="${dept.id}">${dept.nome}</option>`;
        });
        
        filterSelect.innerHTML = options;
        
        // Também preencher os seletores dos formulários
        document.getElementById('id_departamento').innerHTML = `
            <option value="">Selecione um departamento</option>
            ${departamentos.map(dept => `<option value="${dept.id}">${dept.nome}</option>`).join('')}
        `;
    } catch (error) {
        console.error('Erro ao carregar departamentos:', error);
    }    
    // Também carregar funcionários para o seletor de chefe
    loadFuncionariosJefes();
}

// Carregar funcionários para o seletor de chefe direto
async function loadFuncionariosJefes() {
    try {
        // Em um ambiente real, isto seria uma chamada à API
        // const response = await fetch('/api/funcionarios/jefes');
        // const data = await response.json();
        
        // Por enquanto usamos dados de exemplo
        funcionariosJefes = [
            { id: 1, nome: 'Ana', sobrenome: 'Silva', cargo: 'Gerente TI' },
            { id: 2, nome: 'Carlos', sobrenome: 'Mendes', cargo: 'Diretor Engenharia' },
            { id: 5, nome: 'Laura', sobrenome: 'Martins', cargo: 'Coordenadora RH' }
        ];
          // Preencher o seletor
        const jefeSelect = document.getElementById('id_chefe_direto');
        let options = '<option value="">Nenhum</option>';
        
        funcionariosJefes.forEach(jefe => {
            options += `<option value="${jefe.id}">${jefe.nome} ${jefe.sobrenome} - ${jefe.cargo}</option>`;
        });
        
        jefeSelect.innerHTML = options;
    } catch (error) {
        console.error('Erro ao carregar funcionários chefes:', error);
    }
}

// Abrir modal para criar novo funcionário
function openCreateModal() {
    // Limpar o formulário
    document.getElementById('funcionario-form').reset();
    document.getElementById('funcionario-id').value = '';
    document.getElementById('photo-preview').innerHTML = '<i class="fas fa-user"></i>';
    document.getElementById('remove_foto_hidden').value = '0'; // Reset remove photo flag

    // Mudar o título do modal
    document.getElementById('modal-title').textContent = 'Adicionar Novo Funcionário';
    
    // Mostrar o modal
    document.getElementById('funcionario-modal').classList.add('show');
}

// Función robusta para encontrar funcionario por ID
function findFuncionarioById(id) {
    id = String(id);
    return funcionarios.find(f => String(f.id_funcionario ?? f.id) === id);
}

// Ver detalhes de um funcionário
function viewFuncionario(id) {
    const funcionario = findFuncionarioById(id);
    if (!funcionario) {
        showNotification('Funcionário não encontrado', 'error');
        return;
    }
    
    // Preencher os campos do modal com a informação do funcionário
    document.getElementById('view-funcionario-title').textContent = 'Detalhes do Funcionário';
    document.getElementById('view-funcionario-name').textContent = `${funcionario.nome} ${funcionario.sobrenome}`;
    
    // Informação pessoal
    document.getElementById('view-id').textContent = funcionario.id_funcionario || funcionario.id || '-';
    document.getElementById('view-email').textContent = funcionario.email || '-';
    document.getElementById('view-documento').textContent = funcionario.numero_documento || '-';
    document.getElementById('view-nascimento').textContent = formatDate(funcionario.data_nascimento) || '-';
    
    // Informação profissional
    document.getElementById('view-cargo').textContent = funcionario.cargo || '-';
    document.getElementById('view-departamento').textContent = funcionario.departamento || '-';
    document.getElementById('view-chefe').textContent = funcionario.chefe_direto || '-';
    document.getElementById('view-contratacao').textContent = formatDate(funcionario.data_contratacao) || '-';
    document.getElementById('view-codigo').textContent = funcionario.codigo_sistema_interno || '-';
    
    // Estado
    const statusElement = document.getElementById('view-status');
    if (funcionario.ativo === true || funcionario.ativo === 1) {
        statusElement.textContent = 'Ativo';
        statusElement.className = 'detail-value active';
    } else {
        statusElement.textContent = 'Inativo';
        statusElement.className = 'detail-value inactive';
    }
    
    // Informação do sistema
    document.getElementById('view-criacao').textContent = formatDate(funcionario.data_criacao_registro) || '-';
    document.getElementById('view-modificacao').textContent = formatDate(funcionario.ultima_modificacao_registro) || '-';
    
    // Mostrar foto se existe
    const photoContainer = document.getElementById('view-photo-container');
    if (funcionario.foto_base64) {
        photoContainer.innerHTML = `<img src="${funcionario.foto_base64}" alt="${funcionario.nome} ${funcionario.sobrenome}">`;
    } else {
        photoContainer.innerHTML = '<i class="fas fa-user-circle fa-5x"></i>';
    }
    
    // Configurar botão de editar
    const editButton = document.getElementById('edit-from-view');
    editButton.onclick = () => {
        // Fechar este modal e abrir o de edição
        document.getElementById('view-funcionario-modal').classList.remove('show');
        editFuncionario(id);
    };
    
    // Mostrar o modal
    document.getElementById('view-funcionario-modal').classList.add('show');
}

// Abrir modal para editar funcionário
async function editFuncionario(id) {
    try {
        const funcionario = findFuncionarioById(id);
        if (!funcionario) {
            showNotification('Funcionário não encontrado', 'error');
            return;
        }
        // Preencher o formulário com os dados do funcionário
        document.getElementById('funcionario-id').value = funcionario.id_funcionario || funcionario.id;
        document.getElementById('nombre').value = funcionario.nome;
        document.getElementById('sobrenome').value = funcionario.sobrenome;
        document.getElementById('email').value = funcionario.email;
        document.getElementById('numero_documento').value = funcionario.numero_documento;
        document.getElementById('cargo').value = funcionario.cargo;
        document.getElementById('data_contratacao').value = funcionario.data_contratacao;

        // Adicionar preenchimento da data de nascimento se existir
        if (document.getElementById('data_nascimento') && funcionario.data_nascimento) {
            document.getElementById('data_nascimento').value = funcionario.data_nascimento;
        }

        document.getElementById('codigo_sistema_interno').value = funcionario.codigo_sistema_interno || '';
        document.getElementById('id_departamento').value = funcionario.id_departamento || '';
        document.getElementById('id_chefe_direto').value = funcionario.id_chefe_direto || '';

        // Reset the remove photo flag every time the modal is opened
        document.getElementById('remove_foto_hidden').value = '0';

        // Atualizar a visualização prévia da foto se existir
        const photoPreview = document.getElementById('photo-preview');
        if (funcionario.foto_base64) {
            photoPreview.innerHTML = `<img src="${funcionario.foto_base64}" alt="${funcionario.nome}">`;
        } else {
            photoPreview.innerHTML = '<i class="fas fa-user"></i>';
        }
        
        // Atualizar o título do modal
        document.getElementById('modal-title').textContent = `Editar Funcionário: ${funcionario.nome} ${funcionario.sobrenome}`;
        
        // Mostrar o modal
        document.getElementById('funcionario-modal').classList.add('show');
    } catch (error) {
        console.error('Erro ao carregar dados do funcionário', error);
        showNotification('Erro ao carregar dados do funcionário', 'error');
    }
}

// Confirmar exclusão de funcionário
function confirmDeleteFuncionario(id) {
    selectedFuncionarioId = id;
    document.getElementById('confirm-delete-modal').classList.add('show');
}

// Eliminar funcionário
async function deleteFuncionario(id) {
    try {
        const response = await fetch(`${API_BASE_URL}/${id}`, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (data.status === 'success') {
            showNotification('Funcionário excluído com sucesso!', 'success');
            document.getElementById('confirm-delete-modal').classList.remove('show');
            loadFuncionarios();
        } else {
            showNotification(data.message || 'Erro ao excluir funcionário', 'error');
        }
    } catch (error) {
        showNotification('Erro ao excluir funcionário', 'error');
    }
}

// Manipular envio do formulário (criar/editar)
async function handleFuncionarioFormSubmit() {
    const id = document.getElementById('funcionario-id').value;
    const isEdit = !!id;
    const url = isEdit ? `${API_BASE_URL}/${id}` : API_BASE_URL;
    const form = document.getElementById('funcionario-form');
    const fileInput = document.getElementById('foto');
    let body, headers = {};
    let useMultipart = false;
    // Si hay foto o es edición, usar multipart siempre
    if ((fileInput && fileInput.files.length > 0) || isEdit) {
        useMultipart = true;
    }
    if (useMultipart) {
        body = new FormData(form);
        // Si hay foto nueva, agregarla; si no, no agregar campo 'foto'
        if (fileInput && fileInput.files.length > 0) {
            body.append('foto', fileInput.files[0]);
        } else {
            body.delete('foto'); // Elimina campo vazio se existe
        }
        // Para PUT, usar POST + X-HTTP-Method-Override
        if (isEdit) {
            headers['X-HTTP-Method-Override'] = 'PUT';
        }
    } else {
        // Si no hay foto e es creación, enviar JSON
        const formData = new FormData(form);
        const obj = {};
        formData.forEach((v, k) => obj[k] = v);
        body = JSON.stringify(obj);
        headers['Content-Type'] = 'application/json';
    }
    try {
        const response = await fetch(url, {
            method: useMultipart ? 'POST' : (isEdit ? 'PUT' : 'POST'),
            body,
            headers
        });
        const data = await response.json();
        if (data.status === 'success') {
            showNotification(isEdit ? 'Funcionário atualizado com sucesso!' : 'Funcionário criado com sucesso!', 'success');
            document.getElementById('funcionario-modal').classList.remove('show');
            loadFuncionarios();
        } else {
            showNotification(data.message || 'Erro ao salvar funcionário', 'error');
        }
    } catch (error) {
        showNotification('Erro ao salvar funcionário', 'error');
    }
}

// Mostrar a imagem selecionada na visualização prévia
function displaySelectedImage(input, previewId = 'photo-preview') {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Pré-visualização">`;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Função utilitária para formatar datas
function formatDate(dateString) {
    if (!dateString) return '-';
    
    try {
        console.log('Formatando data:', dateString, 'tipo:', typeof dateString);
        
        // Tentar converter diferentes formatos de data
        let date;
        
        if (typeof dateString === 'string') {
            // Verificar se o formato é "YYYY-MM-DD HH:MM:SS"
            if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(dateString)) {
                const parts = dateString.split(' ');
                const dateParts = parts[0].split('-');
                const timeParts = parts[1].split(':');
                
                date = new Date(
                    parseInt(dateParts[0]), 
                    parseInt(dateParts[1]) - 1, 
                    parseInt(dateParts[2]),
                    parseInt(timeParts[0]),
                    parseInt(timeParts[1]),
                    parseInt(timeParts[2])
                );
                console.log('Formato ISO detectado:', date);
            } else {
                // Tentar conversão padrão
                date = new Date(dateString);
                console.log('Conversão padrão:', date);
            }
        } else {
            date = new Date(dateString);
        }
        
        if (isNaN(date.getTime())) {
            console.log('Data inválida, retornando original:', dateString);
            return dateString; // Se a data for inválida, retorna a string original
        }
        
        const formatted = date.toLocaleDateString('pt-BR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        console.log('Data formatada:', formatted);
        return formatted;
    } catch (error) {
        console.error('Erro ao formatar data:', error, dateString);
        return dateString; // Em caso de erro, retorna a string original
    }
}

// Função utilitária para debounce (atraso na busca)
function debounce(func, wait) {
    let timeout;
    
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Función utilitaria para mostrar notificaciones
function showNotification(message, type = 'info') {
    // Para depuração
    console.log(`Notificação: ${message} (tipo: ${type})`);
    
    // Criar o elemento de notificação se não existir
    let notification = document.querySelector('.notification-toast');
    if (!notification) {
        notification = document.createElement('div');
        notification.className = 'notification-toast';
        document.body.appendChild(notification);
        
        // Estilos de la notificación
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.padding = '15px 20px';
        notification.style.borderRadius = '5px';
        notification.style.boxShadow = '0 3px 6px rgba(0,0,0,0.16)';
        notification.style.zIndex = '1000';
        notification.style.transition = 'all 0.3s ease';
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
    }
    
    // Configurar estilo según tipo
    switch (type) {
        case 'success':
            notification.style.backgroundColor = '#34a853';
            notification.style.color = 'white';
            break;
        case 'error':
            notification.style.backgroundColor = '#ea4335';
            notification.style.color = 'white';
            break;
        case 'warning':
            notification.style.backgroundColor = '#fbbc05';
            notification.style.color = '#333';
            break;
        default:
            notification.style.backgroundColor = '#1a73e8';
            notification.style.color = 'white';
    }
    
    // Establecer mensaje
    notification.textContent = message;
    
    // Mostrar
    notification.style.opacity = '1';
    notification.style.transform = 'translateY(0)';
    
    // Ocultar después de 5 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
    }, 5000);
}

// Agregado para depuración - mostrar notificaciones con log en consola
function showDebugNotification(message, type = 'info') {
    console.log(`[DEBUG] ${type.toUpperCase()}: ${message}`);
    
    // Si existe la función showNotification del common.js, usarla
    if (typeof showNotification === 'function') {
        showNotification(message, type);
    } else {
        // Crear notificación inline si no existe la función
        const notif = document.createElement('div');
        notif.classList.add('debug-notification', type);
        notif.textContent = message;
        notif.style.position = 'fixed';
        notif.style.top = '20px';
        notif.style.right = '20px';
        notif.style.padding = '15px';
        notif.style.background = type === 'error' ? '#f44336' : '#4CAF50';
        notif.style.color = 'white';
        notif.style.borderRadius = '4px';
        notif.style.zIndex = '9999';
        
        document.body.appendChild(notif);
        
        setTimeout(() => {
            notif.remove();
        }, 5000);
    }
}
