/**
 * funcionarios-form.js - Gestión del formulario de funcionarios
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes del formulario
    initForm();
    
    // Verificar si estamos en modo edición (URL con parámetro de ID)
    const urlParams = new URLSearchParams(window.location.search);
    const funcionarioId = urlParams.get('id');
    
    if (funcionarioId) {
        // Modo edición
        document.getElementById('page-title').textContent = 'Editar Funcionario';
        loadFuncionarioData(funcionarioId);
    } else {
        // Modo creación - establecer fecha de contratación por defecto
        document.getElementById('data_contratacao').valueAsDate = new Date();
    }
    
    // Cargar departamentos y jefes directos para los select
    loadDepartamentos();
    loadJefesDirectos();
    
    // Configurar eventos
    setupEventListeners();
});

// Inicializar el formulario
function initForm() {
    // Configurar vista previa de la foto
    const fotoInput = document.getElementById('foto');
    const fotoPreview = document.getElementById('foto-preview');
    
    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    fotoPreview.innerHTML = `<img src="${e.target.result}" alt="Vista previa">`;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
}

// Cargar los datos de un funcionario para edición
function loadFuncionarioData(funcionarioId) {
    // En una implementación real, aquí se haría una petición AJAX para obtener los datos
    // Por ejemplo:
    /*
    fetch(`api.php/funcionarios/${funcionarioId}`)
        .then(response => response.json())
        .then(data => {
            populateFormData(data);
        })
        .catch(error => {
            console.error('Error al cargar los datos del funcionario:', error);
            showNotification('Error al cargar los datos. Intente nuevamente.', 'error');
        });
    */
    
    // Para este ejemplo, usaremos datos de prueba
    const mockFuncionario = {
        id_funcionario: funcionarioId,
        nome: 'Juan',
        sobrenome: 'Pérez',
        numero_documento: '12345678',
        email: 'juan.perez@itaipu.com',
        cargo: 'Analista de Sistemas',
        data_contratacao: '2023-01-15',
        data_nascimento: '1985-07-22',
        codigo_sistema_interno: 'JPS001',
        id_departamento: '2',
        id_chefe_direto: '1',
        foto: null
    };
    
    populateFormData(mockFuncionario);
}

// Rellenar el formulario con los datos obtenidos
function populateFormData(funcionario) {
    // Mapear cada campo del objeto funcionario a su respectivo elemento en el formulario
    document.getElementById('id_funcionario').value = funcionario.id_funcionario || '';
    document.getElementById('nome').value = funcionario.nome || '';
    document.getElementById('sobrenome').value = funcionario.sobrenome || '';
    document.getElementById('numero_documento').value = funcionario.numero_documento || '';
    document.getElementById('email').value = funcionario.email || '';
    document.getElementById('cargo').value = funcionario.cargo || '';
    document.getElementById('codigo_sistema_interno').value = funcionario.codigo_sistema_interno || '';
    
    // Manejar fechas
    if (funcionario.data_contratacao) {
        document.getElementById('data_contratacao').value = funcionario.data_contratacao;
    }
    
    if (funcionario.data_nascimento) {
        document.getElementById('data_nascimento').value = funcionario.data_nascimento;
    }
    
    // Selects
    if (funcionario.id_departamento) {
        const deptSelect = document.getElementById('id_departamento');
        if (deptSelect) {
            // Asegurarse de que las opciones estén cargadas antes de establecer el valor
            setTimeout(() => {
                deptSelect.value = funcionario.id_departamento;
            }, 500);
        }
    }
    
    if (funcionario.id_chefe_direto) {
        const jefeSelect = document.getElementById('id_chefe_direto');
        if (jefeSelect) {
            setTimeout(() => {
                jefeSelect.value = funcionario.id_chefe_direto;
            }, 500);
        }
    }
    
    // Foto
    if (funcionario.foto) {
        const fotoPreview = document.getElementById('foto-preview');
        fotoPreview.innerHTML = `<img src="data:image/jpeg;base64,${funcionario.foto}" alt="${funcionario.nome}">`;
    }
}

// Cargar la lista de departamentos para el select
function loadDepartamentos() {
    const selectDept = document.getElementById('id_departamento');
    
    if (!selectDept) return;
    
    // En una implementación real, cargar desde la API
    /*
    fetch('api.php/departamentos')
        .then(response => response.json())
        .then(data => {
            data.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id_departamento;
                option.textContent = dept.nome_departamento;
                selectDept.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar departamentos:', error);
        });
    */
    
    // Datos de prueba
    const departamentos = [
        { id_departamento: 1, nome_departamento: 'Tecnología' },
        { id_departamento: 2, nome_departamento: 'Recursos Humanos' },
        { id_departamento: 3, nome_departamento: 'Finanzas' },
        { id_departamento: 4, nome_departamento: 'Operaciones' }
    ];
    
    departamentos.forEach(dept => {
        const option = document.createElement('option');
        option.value = dept.id_departamento;
        option.textContent = dept.nome_departamento;
        selectDept.appendChild(option);
    });
}

// Cargar la lista de posibles jefes directos para el select
function loadJefesDirectos() {
    const selectJefes = document.getElementById('id_chefe_direto');
    
    if (!selectJefes) return;
    
    // En una implementación real, cargar desde la API
    /*
    fetch('api.php/funcionarios?cargo=jefe')
        .then(response => response.json())
        .then(data => {
            data.forEach(jefe => {
                const option = document.createElement('option');
                option.value = jefe.id_funcionario;
                option.textContent = `${jefe.nome} ${jefe.sobrenome} (${jefe.cargo})`;
                selectJefes.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar jefes:', error);
        });
    */
    
    // Datos de prueba
    const jefes = [
        { id_funcionario: 1, nome: 'Carlos', sobrenome: 'González', cargo: 'Director de Tecnología' },
        { id_funcionario: 2, nome: 'Ana', sobrenome: 'Martínez', cargo: 'Gerente de RRHH' },
        { id_funcionario: 3, nome: 'Roberto', sobrenome: 'Sánchez', cargo: 'Gerente de Finanzas' }
    ];
    
    jefes.forEach(jefe => {
        const option = document.createElement('option');
        option.value = jefe.id_funcionario;
        option.textContent = `${jefe.nome} ${jefe.sobrenome} (${jefe.cargo})`;
        selectJefes.appendChild(option);
    });
}

// Configurar los eventos del formulario
function setupEventListeners() {
    // Manejo del envío del formulario
    const form = document.getElementById('funcionario-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                saveForm();
            }
        });
    }
    
    // Botón de cancelar
    const cancelBtn = document.getElementById('cancel-btn');
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            if (confirm('¿Está seguro que desea cancelar? Los cambios no guardados se perderán.')) {
                window.location.href = 'funcionarios-lista.html';
            }
        });
    }
}

// Validar los campos del formulario antes de enviar
function validateForm() {
    const requiredFields = ['nome', 'sobrenome', 'numero_documento', 'email'];
    let isValid = true;
    
    // Verificar campos requeridos
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field.value.trim()) {
            field.classList.add('invalid');
            isValid = false;
        } else {
            field.classList.remove('invalid');
        }
    });
    
    // Validar formato de email
    const email = document.getElementById('email');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email.value && !emailRegex.test(email.value)) {
        email.classList.add('invalid');
        isValid = false;
    }
    
    if (!isValid) {
        showNotification('Por favor complete todos los campos requeridos correctamente.', 'error');
    }
    
    return isValid;
}

// Guardar el formulario
function saveForm() {
    // Obtener datos del formulario
    const formData = new FormData(document.getElementById('funcionario-form'));
    
    // Añadir la foto si se seleccionó una
    const fotoInput = document.getElementById('foto');
    if (fotoInput.files.length > 0) {
        formData.append('foto', fotoInput.files[0]);
    }
    
    // ID del funcionario (0 para crear nuevo, otro valor para editar)
    const funcionarioId = formData.get('id_funcionario');
    const isEditing = !!funcionarioId;
    
    // En una implementación real, enviar a la API
    /*
    const url = isEditing ? `api.php/funcionarios/${funcionarioId}` : 'api.php/funcionarios';
    const method = isEditing ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        showNotification(`Funcionario ${isEditing ? 'actualizado' : 'creado'} correctamente`, 'success');
        
        // Redirigir a la lista después de un breve tiempo
        setTimeout(() => {
            window.location.href = 'funcionarios-lista.html';
        }, 1500);
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error al guardar los datos. Intente nuevamente.', 'error');
    });
    */
    
    // Simular el guardado para el ejemplo
    console.log('Datos a guardar:', Object.fromEntries(formData));
    
    showNotification(`Funcionario ${isEditing ? 'actualizado' : 'creado'} correctamente`, 'success');
    
    // Redirigir a la lista después de un breve tiempo
    setTimeout(() => {
        window.location.href = 'funcionarios-lista.html';
    }, 1500);
}
