/**
 * crachas.js - Funcionalidades específicas para a gestão de crachás
 */

// URL base para as requisições da API
const API_BASE_URL = window.location.pathname.includes('Itaipu-intranet') 
    ? '/Itaipu-intranet/api/crachas' 
    : '/api/crachas';

// Variáveis globais
let currentPage = 1;
let totalPages = 1;
let crachas = [];
let motivos = [];
let selectedCrachaId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar elementos quando o documento está pronto
    initCrachasList();
    loadMotivos();
    setupEventListeners();

    // Verificar se a URL tem um parâmetro ID para mostrar um crachá específico
    const urlParams = new URLSearchParams(window.location.search);
    const crachaId = urlParams.get('id');
    
    if (crachaId) {
        showCrachaDetails(crachaId);
    }
    
    // Configurar evento para busca
    const searchInput = document.getElementById('search-crachas');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            filterCrachas(this.value);
        }, 500));
    }
});

// Inicializar a tabela de crachás
function initCrachasList() {
    // Mostrar indicador de carregamento
    const tableBody = document.getElementById('crachas-list');
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Carregando crachás...</td></tr>';
    }

    // Carregar crachás da API
    fetch(API_BASE_URL)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success' && Array.isArray(result.data)) {
                crachas = result.data;
                renderCrachasList(crachas);
            } else {
                console.error('Formato de resposta inválido:', result);
                showNotification('Erro ao carregar os dados. Formato de resposta inválido.', 'error');
                
                if (tableBody) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Erro ao carregar os dados.</td></tr>';
                }
            }
        })
        .catch(error => {
            console.error('Erro ao carregar os crachás:', error);
            showNotification('Erro ao carregar os dados. Tente novamente.', 'error');
            
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Erro ao carregar os dados.</td></tr>';
            }
        });
}

// Renderizar la lista de crachás en la tabla
// Carregar motivos de crachá
function loadMotivos() {
    fetch(`${API_BASE_URL}/motivos`)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success' && Array.isArray(result.data)) {
                motivos = result.data;
                
                // Preencher dropdowns de motivo se existirem
                const motivoSelect = document.getElementById('motivo-cracha');
                if (motivoSelect) {
                    motivoSelect.innerHTML = '<option value="">Selecione um motivo</option>';
                    motivos.forEach(motivo => {
                        const option = document.createElement('option');
                        option.value = motivo.id_motivo;
                        option.textContent = motivo.descricao_motivo;
                        motivoSelect.appendChild(option);
                    });
                }
            } else {
                console.error('Erro ao carregar motivos:', result);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar motivos de crachá:', error);
        });
}

// Filtrar crachás com base na busca
function filterCrachas(searchTerm) {
    if (!searchTerm) {
        renderCrachasList(crachas);
        return;
    }
    
    searchTerm = searchTerm.toLowerCase();
    const filtered = crachas.filter(cracha => 
        (cracha.nome && cracha.nome.toLowerCase().includes(searchTerm)) ||
        (cracha.sobrenome && cracha.sobrenome.toLowerCase().includes(searchTerm)) ||
        (cracha.descricao_motivo && cracha.descricao_motivo.toLowerCase().includes(searchTerm)) ||
        (cracha.status_cracha && cracha.status_cracha.toLowerCase().includes(searchTerm))
    );
    
    renderCrachasList(filtered);
}

// Função de debounce para pesquisa
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function renderCrachasList(crachas) {
    const tableBody = document.getElementById('crachas-list');
    if (!tableBody) return;

    tableBody.innerHTML = '';
    
    if (crachas.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum crachá encontrado</td></tr>';
        return;
    }

    crachas.forEach(cracha => {
        const row = document.createElement('tr');
        
        // Criar um indicador de estado visual
        let statusBadge = `<span class="badge badge-`;
        switch (cracha.status_cracha) {
            case 'Emitido':
                statusBadge += `success">Emitido</span>`;
                break;
            case 'Solicitado':
                statusBadge += `warning">Solicitado</span>`;
                break;
            case 'Cancelado':
                statusBadge += `danger">Cancelado</span>`;
                break;
            case 'Vencido':
                statusBadge += `secondary">Vencido</span>`;
                break;
            default:
                statusBadge += `secondary">${cracha.status_cracha}</span>`;
        }

        // Criar os botões de ação de acordo com o estado
        let actions = `
            <div class="actions">
                <button class="btn-icon view-cracha" data-id="${cracha.id_cracha}" title="Ver detalhes">
                    <i class="fas fa-eye"></i>
                </button>
        `;

        if (cracha.status_cracha === 'Emitido') {
            actions += `
                <button class="btn-icon print-cracha" data-id="${cracha.id_cracha}" title="Imprimir">
                    <i class="fas fa-print"></i>
                </button>
                <button class="btn-icon text-danger deactivate-cracha" data-id="${cracha.id_cracha}" title="Desativar">
                    <i class="fas fa-ban"></i>
                </button>
            `;
        } else if (cracha.status_cracha === 'Solicitado') {
            actions += `
                <button class="btn-icon approve-cracha" data-id="${cracha.id_cracha}" title="Aprovar">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn-icon text-danger reject-cracha" data-id="${cracha.id_cracha}" title="Rejeitar">
                    <i class="fas fa-times"></i>
                </button>
            `;
        } else {
            actions += `
                <button class="btn-icon reactivate-cracha" data-id="${cracha.id_cracha}" title="Reativar">
                    <i class="fas fa-redo"></i>
                </button>
            `;
        }        actions += `</div>`;

        // Verificar se temos foto_base64, caso contrário usar placeholder
        const fotoSrc = cracha.foto_base64 || 'assets/img/placeholder-user.jpg';

        // Formatar datas
        const dataSolicitacao = cracha.data_solicitacao ? formatDate(cracha.data_solicitacao) : 'N/A';
        const dataEmissao = cracha.data_emissao ? formatDate(cracha.data_emissao) : 'N/A';
        const dataValidade = cracha.data_validade ? formatDate(cracha.data_validade) : 'N/A';

        row.innerHTML = `
            <td>
                <div class="funcionario-info">
                    <div class="funcionario-foto">
                        <img src="${fotoSrc}" alt="Foto de ${cracha.nome} ${cracha.sobrenome}">
                    </div>
                    <div>
                        <h5>${cracha.nome} ${cracha.sobrenome}</h5>
                        <p>${cracha.nome_departamento || 'N/A'}</p>
                    </div>
                </div>
            </td>
            <td>${cracha.descricao_motivo || 'N/A'}</td>
            <td>${dataSolicitacao}</td>
            <td>${dataEmissao} - ${dataValidade}</td>
            <td>${statusBadge}</td>
            <td>${actions}</td>
        `;

        tableBody.appendChild(row);
    });

    // Após renderizar, configuramos os eventos para os botões
    setupCrachaButtonEvents();
}

// Configurar los eventos para los botones de acción
function setupCrachaButtonEvents() {
    // Ver detalle del crachá
    document.querySelectorAll('.view-cracha').forEach(button => {
        button.addEventListener('click', function() {
            const crachaId = this.getAttribute('data-id');
            showCrachaDetails(crachaId);
        });
    });

    // Imprimir crachá
    document.querySelectorAll('.print-cracha').forEach(button => {
        button.addEventListener('click', function() {
            const crachaId = this.getAttribute('data-id');
            printCracha(crachaId);
        });
    });

    // Desactivar crachá
    document.querySelectorAll('.deactivate-cracha').forEach(button => {
        button.addEventListener('click', function() {
            const crachaId = this.getAttribute('data-id');
            deactivateCracha(crachaId);
        });
    });

    // Aprovar crachá
    document.querySelectorAll('.approve-cracha').forEach(button => {
        button.addEventListener('click', function() {
            const crachaId = this.getAttribute('data-id');
            approveCracha(crachaId);
        });
    });

    // Rechazar crachá
    document.querySelectorAll('.reject-cracha').forEach(button => {
        button.addEventListener('click', function() {
            const crachaId = this.getAttribute('data-id');
            rejectCracha(crachaId);
        });
    });

    // Reactivar crachá
    document.querySelectorAll('.reactivate-cracha').forEach(button => {
        button.addEventListener('click', function() {
            const crachaId = this.getAttribute('data-id');
            reactivateCracha(crachaId);
        });
    });
}

// Adicionar formatação de data
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Mostrar os detalhes de um crachá no modal
function showCrachaDetails(crachaId) {
    // Mostrar indicador de carregamento
    const modalBody = document.querySelector('#modal-ver-cracha .modal-body');
    if (modalBody) {
        modalBody.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-3">Carregando detalhes...</p></div>';
    }
    
    // Mostrar o modal
    document.getElementById('modal-ver-cracha').classList.add('show');
    
    // Carregar detalhes do crachá via API
    fetch(`${API_BASE_URL}/${crachaId}`)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success' && result.data) {
                const cracha = result.data;
                
                // Atualizar o título do modal
                const modalTitle = document.querySelector('#modal-ver-cracha .modal-title');
                if (modalTitle) {
                    modalTitle.textContent = `Detalhes do Crachá #${cracha.id_cracha}`;
                }
                
                // Verificar se temos foto_base64, caso contrário usar placeholder
                const fotoSrc = cracha.foto_base64 || 'assets/img/placeholder-user.jpg';
                
                // Formatar datas
                const dataSolicitacao = cracha.data_solicitacao ? formatDate(cracha.data_solicitacao) : 'N/A';
                const dataEmissao = cracha.data_emissao ? formatDate(cracha.data_emissao) : 'N/A';
                const dataValidade = cracha.data_validade ? formatDate(cracha.data_validade) : 'N/A';
                
                // Criar o status badge
                let statusBadge = `<span class="badge badge-`;
                switch (cracha.status_cracha) {
                    case 'Emitido':
                        statusBadge += `success">Emitido</span>`;
                        break;
                    case 'Solicitado':
                        statusBadge += `warning">Solicitado</span>`;
                        break;
                    case 'Cancelado':
                        statusBadge += `danger">Cancelado</span>`;
                        break;
                    case 'Vencido':
                        statusBadge += `secondary">Vencido</span>`;
                        break;
                    default:
                        statusBadge += `secondary">${cracha.status_cracha}</span>`;
                }
                
                // Atualizar o conteúdo do modal
                if (modalBody) {
                    modalBody.innerHTML = `
                        <div class="cracha-details">
                            <div class="cracha-funcionario">
                                <div class="funcionario-foto">
                                    <img src="${fotoSrc}" alt="Foto de ${cracha.nome} ${cracha.sobrenome}">
                                </div>
                                <div class="funcionario-info">
                                    <h3>${cracha.nome} ${cracha.sobrenome}</h3>
                                    <p>Cargo: ${cracha.cargo || 'N/A'}</p>
                                    <p>Departamento: ${cracha.nome_departamento || 'N/A'}</p>
                                </div>
                            </div>
                            <div class="cracha-info-grid">
                                <div class="info-item">
                                    <strong>Motivo:</strong>
                                    <span>${cracha.descricao_motivo || 'N/A'}</span>
                                </div>
                                <div class="info-item">
                                    <strong>Status:</strong>
                                    <span>${statusBadge}</span>
                                </div>
                                <div class="info-item">
                                    <strong>Data de Solicitação:</strong>
                                    <span>${dataSolicitacao}</span>
                                </div>
                                <div class="info-item">
                                    <strong>Data de Emissão:</strong>
                                    <span>${dataEmissao}</span>
                                </div>
                                <div class="info-item">
                                    <strong>Validade:</strong>
                                    <span>${dataValidade}</span>
                                </div>
                                <div class="info-item full-width">
                                    <strong>Observações:</strong>
                                    <p>${cracha.observacoes || 'Nenhuma observação registrada'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Adicionar botões de ação ao modal com base no status
                    const modalFooter = document.querySelector('#modal-ver-cracha .modal-footer');
                    if (modalFooter) {
                        modalFooter.innerHTML = `
                            <button type="button" class="btn btn-secondary" onclick="closeModal('modal-ver-cracha')">Fechar</button>
                        `;
                        
                        if (cracha.status_cracha === 'Emitido') {
                            modalFooter.innerHTML += `
                                <button type="button" class="btn btn-primary" onclick="printCracha(${cracha.id_cracha})">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                                <button type="button" class="btn btn-danger" onclick="deactivateCracha(${cracha.id_cracha})">
                                    <i class="fas fa-ban"></i> Desativar
                                </button>
                            `;
                        } else if (cracha.status_cracha === 'Solicitado') {
                            modalFooter.innerHTML += `
                                <button type="button" class="btn btn-success" onclick="approveCracha(${cracha.id_cracha})">
                                    <i class="fas fa-check"></i> Aprovar
                                </button>
                                <button type="button" class="btn btn-danger" onclick="rejectCracha(${cracha.id_cracha})">
                                    <i class="fas fa-times"></i> Rejeitar
                                </button>
                            `;
                        } else {
                            modalFooter.innerHTML += `
                                <button type="button" class="btn btn-primary" onclick="reactivateCracha(${cracha.id_cracha})">
                                    <i class="fas fa-redo"></i> Reativar
                                </button>
                            `;
                        }
                    }
                }
                
                // Guardar o ID do crachá selecionado
                selectedCrachaId = cracha.id_cracha;
            } else {
                // Exibir mensagem de erro no modal
                if (modalBody) {
                    modalBody.innerHTML = `<div class="alert alert-danger">Erro ao carregar detalhes do crachá. Por favor, tente novamente.</div>`;
                }
            }
        })
        .catch(error => {
            console.error('Erro ao carregar os detalhes do crachá:', error);
            
            // Exibir mensagem de erro no modal
            if (modalBody) {
                modalBody.innerHTML = `<div class="alert alert-danger">Erro ao carregar detalhes do crachá. Por favor, tente novamente.</div>`;
            }
            
            // Mostrar notificação
            showNotification('Erro ao carregar os detalhes. Tente novamente.', 'error');
        });
}

// Aprovar crachá (mudar status para "Emitido")
function approveCracha(crachaId) {
    if (!confirm('Confirma a aprovação deste crachá? Isto irá emiti-lo.')) {
        return;
    }
    
    // Atualizar status via API
    fetch(`${API_BASE_URL}/${crachaId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status_cracha: 'Emitido'
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            showNotification('Crachá aprovado com sucesso!', 'success');
            
            // Fechar modal se estiver aberto
            closeModal('modal-ver-cracha');
            
            // Recarregar a lista de crachás
            initCrachasList();
        } else {
            showNotification(`Erro ao aprovar crachá: ${result.message}`, 'error');
        }
    })
    .catch(error => {
        console.error('Erro ao aprovar crachá:', error);
        showNotification('Erro ao aprovar crachá. Por favor, tente novamente.', 'error');
    });
}

// Rejeitar crachá (mudar status para "Cancelado")
function rejectCracha(crachaId) {
    if (!confirm('Confirma a rejeição deste crachá? Isto irá cancelá-lo.')) {
        return;
    }
    
    // Atualizar status via API
    fetch(`${API_BASE_URL}/${crachaId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status_cracha: 'Cancelado'
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            showNotification('Crachá rejeitado com sucesso.', 'success');
            
            // Fechar modal se estiver aberto
            closeModal('modal-ver-cracha');
            
            // Recarregar a lista de crachás
            initCrachasList();
        } else {
            showNotification(`Erro ao rejeitar crachá: ${result.message}`, 'error');
        }
    })
    .catch(error => {
        console.error('Erro ao rejeitar crachá:', error);
        showNotification('Erro ao rejeitar crachá. Por favor, tente novamente.', 'error');
    });
}

// Desativar crachá (mudar status para "Cancelado")
function deactivateCracha(crachaId) {
    if (!confirm('Confirma a desativação deste crachá? Isto irá cancelá-lo.')) {
        return;
    }
    
    // Atualizar status via API
    fetch(`${API_BASE_URL}/${crachaId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status_cracha: 'Cancelado'
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            showNotification('Crachá desativado com sucesso.', 'success');
            
            // Fechar modal se estiver aberto
            closeModal('modal-ver-cracha');
            
            // Recarregar a lista de crachás
            initCrachasList();
        } else {
            showNotification(`Erro ao desativar crachá: ${result.message}`, 'error');
        }
    })
    .catch(error => {
        console.error('Erro ao desativar crachá:', error);
        showNotification('Erro ao desativar crachá. Por favor, tente novamente.', 'error');
    });
}

// Reativar crachá (mudar status para "Solicitado")
function reactivateCracha(crachaId) {
    if (!confirm('Confirma a reativação deste crachá?')) {
        return;
    }
    
    // Atualizar status via API
    fetch(`${API_BASE_URL}/${crachaId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status_cracha: 'Solicitado'
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            showNotification('Crachá reativado com sucesso.', 'success');
            
            // Fechar modal se estiver aberto
            closeModal('modal-ver-cracha');
            
            // Recarregar a lista de crachás
            initCrachasList();
        } else {
            showNotification(`Erro ao reativar crachá: ${result.message}`, 'error');
        }
    })
    .catch(error => {
        console.error('Erro ao reativar crachá:', error);
        showNotification('Erro ao reativar crachá. Por favor, tente novamente.', 'error');
    });
}

// Imprimir um crachá
function printCracha(crachaId) {
    // Buscar os detalhes mais recentes do crachá
    fetch(`${API_BASE_URL}/${crachaId}`)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success' && result.data) {
                const cracha = result.data;
                
                // Verificar se o crachá está válido para impressão
                if (cracha.status_cracha !== 'Emitido') {
                    showNotification('Apenas crachás emitidos podem ser impressos.', 'warning');
                    return;
                }
                
                // Abrir uma janela de impressão com o desenho do crachá
                const printWindow = window.open('', '_blank');
                const fotoSrc = cracha.foto_base64 || 'assets/img/placeholder-user.jpg';
                const dataEmissao = cracha.data_emissao ? formatDate(cracha.data_emissao) : 'N/A';
                const dataValidade = cracha.data_validade ? formatDate(cracha.data_validade) : 'N/A';
                
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Imprimir Crachá</title>
                        <style>
                            body { font-family: Arial, sans-serif; }
                            .cracha-card {
                                width: 85mm;
                                height: 54mm;
                                border: 1px solid #000;
                                margin: 20px auto;
                                padding: 10px;
                                box-sizing: border-box;
                                position: relative;
                            }
                            .cracha-header { 
                                text-align: center; 
                                margin-bottom: 10px;
                                display: flex;
                                align-items: center;
                                justify-content: center; 
                            }
                            .cracha-header img {
                                height: 15mm;
                                max-width: 25mm;
                            }
                            .cracha-photo {
                                width: 25mm;
                                height: 30mm;
                                border: 1px solid #ccc;
                                margin: 0 auto;
                                overflow: hidden;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }
                            .cracha-photo img {
                                width: 100%;
                                height: 100%;
                                object-fit: cover;
                            }
                            .cracha-info {
                                text-align: center;
                                margin-top: 5px;
                            }
                            .cracha-info h3 { margin: 2px 0; font-size: 14px; }
                            .cracha-info p { margin: 2px 0; font-size: 11px; }
                            .cracha-id { font-weight: bold; margin-top: 3px; font-size: 12px; }
                            .cracha-dates { 
                                position: absolute;
                                bottom: 5px;
                                right: 5px;
                                font-size: 8px;
                                text-align: right;
                            }
                            .print-button { 
                                text-align: center;
                                margin-top: 20px; 
                            }
                            @media print {
                                body { margin: 0; }
                                .print-button { display: none; }
                            }
                        </style>                    </head>
                    <body>
                        <div class="cracha-card">
                            <div class="cracha-header">
                                <h2>ITAIPU INTRANET</h2>
                            </div>
                            <div class="cracha-photo">
                                <img src="${fotoSrc}" alt="Foto de ${cracha.nome} ${cracha.sobrenome}">
                            </div>
                            <div class="cracha-info">
                                <h3>${cracha.nome} ${cracha.sobrenome}</h3>
                                <p>${cracha.cargo || ''}</p>
                                <p>${cracha.nome_departamento || ''}</p>
                                <div class="cracha-id">ID: ${cracha.id_cracha}</div>
                            </div>
                            <div class="cracha-dates">
                                <div>Emissão: ${dataEmissao}</div>
                                <div>Validade: ${dataValidade}</div>
                            </div>
                        </div>
                          <div class="print-button">
                            <button onclick="window.print()">Imprimir</button>
                            <button onclick="window.close()">Fechar</button>
                        </div>
                    </body>
                    </html>
                `);
                
                printWindow.document.close();
            }
        })
        .catch(error => {
            console.error('Erro ao carregar dados para impressão:', error);
            showNotification('Erro ao preparar crachá para impressão. Tente novamente.', 'error');
        });
}

// Configurar filtros e outros eventos da interface
function setupEventListeners() {
    // Filtro por estado
    const statusFilter = document.getElementById('filter-status');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const selectedStatus = this.value;
            
            if (selectedStatus) {
                // Buscar crachás pelo status selecionado
                fetch(`${API_BASE_URL}/status/${selectedStatus}`)
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success' && Array.isArray(result.data)) {
                            crachas = result.data;
                            renderCrachasList(crachas);
                        } else {
                            showNotification('Erro ao filtrar crachás.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao filtrar crachás:', error);
                        showNotification('Erro ao filtrar crachás. Tente novamente.', 'error');
                    });
            } else {
                // Se nenhum status selecionado, mostrar todos
                initCrachasList();
            }
        });
    }
    
    // Botão de atualizar
    const refreshBtn = document.getElementById('refresh-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            initCrachasList();
            showNotification('Lista atualizada com sucesso', 'success');
        });
    }
    
    // Botão de solicitar novo crachá
    const novoCrachaBtn = document.getElementById('novo-cracha-btn');
    if (novoCrachaBtn) {
        novoCrachaBtn.addEventListener('click', function() {
            document.getElementById('modal-solicitar-cracha').classList.add('show');
        });
    }
    
    // Configurar formulário de solicitação
    const formSolicitarCracha = document.getElementById('form-solicitar-cracha');
    if (formSolicitarCracha) {
        formSolicitarCracha.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const funcionarioId = document.getElementById('funcionario-cracha').value;
            const motivoId = document.getElementById('motivo-cracha').value;
            const observacoes = document.getElementById('observacoes-cracha').value;
            
            if (!funcionarioId || !motivoId) {
                showNotification('Por favor, preencha todos os campos obrigatórios.', 'warning');
                return;
            }
            
            // Enviar solicitação para a API
            fetch(API_BASE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_funcionario: funcionarioId,
                    id_motivo: motivoId,
                    observacoes: observacoes
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    showNotification('Crachá solicitado com sucesso!', 'success');
                    closeModal('modal-solicitar-cracha');
                    formSolicitarCracha.reset();
                    initCrachasList();
                } else {
                    showNotification(`Erro ao solicitar crachá: ${result.message}`, 'error');
                }
            })
            .catch(error => {
                console.error('Erro ao solicitar crachá:', error);
                showNotification('Erro ao solicitar crachá. Tente novamente.', 'error');
            });
        });
    }
    
    // Busca
    const searchInput = document.getElementById('search-crachas');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterCrachas();
        });
    }
}

// Filtrar la lista de crachás según los criterios seleccionados
function filterCrachas() {
    const statusFilter = document.getElementById('filter-status');
    const searchInput = document.getElementById('search-crachas');
    const rows = document.querySelectorAll('#crachas-list tr');
    
    const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
    const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
    
    rows.forEach(row => {
        const statusCell = row.querySelector('td:nth-child(5)');
        const statusText = statusCell ? statusCell.textContent.toLowerCase() : '';
        
        let matchStatus = true;
        if (statusValue && !statusText.includes(statusValue)) {
            matchStatus = false;
        }
        
        let matchSearch = true;
        if (searchValue) {
            matchSearch = false;
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchValue)) {
                    matchSearch = true;
                }
            });
        }
        
        if (matchStatus && matchSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Función para cambiar el estado de un crachá (Implementación básica que se completará con llamadas a la API)
function changeCrachaStatus(crachaId, newStatus, successMessage) {
    // En una implementación real, aquí se haría una petición AJAX para cambiar el estado
    /*
    fetch(`api.php/crachas/${crachaId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ estado: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        showNotification(successMessage, 'success');
        initCrachasList(); // Recargar la lista
    })
    .catch(error => {
        console.error('Error al cambiar el estado del crachá:', error);
        showNotification('Error al procesar la solicitud. Intente nuevamente.', 'error');
    });
    */
    
    // Por ahora, simplemente mostramos una notificación
    showNotification(successMessage, 'success');
    setTimeout(() => {
        initCrachasList(); // Simulamos la recarga de la lista
    }, 1000);
}

// Funciones específicas para cada acción
function deactivateCracha(crachaId) {
    if (confirm('¿Está seguro que desea desactivar este crachá?')) {
        changeCrachaStatus(crachaId, 'inactivo', 'Crachá desactivado correctamente');
    }
}

function approveCracha(crachaId) {
    changeCrachaStatus(crachaId, 'activo', 'Crachá aprobado correctamente');
}

function rejectCracha(crachaId) {
    if (confirm('¿Está seguro que desea rechazar esta solicitud de crachá?')) {
        changeCrachaStatus(crachaId, 'rechazado', 'Solicitud rechazada correctamente');
    }
}

function reactivateCracha(crachaId) {
    changeCrachaStatus(crachaId, 'activo', 'Crachá reactivado correctamente');
}
