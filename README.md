# Sistema de Gestión de Funcionarios - Itaipu Intranet

Sistema completo para la gestión de funcionarios con backend PHP y frontend React profesional.

## 🚀 Características

- **Backend PHP RESTful**: API completa con CRUD para funcionarios
- **Frontend React Moderno**: Interfaz profesional usando Material Dashboard React
- **Gestión de Fotos**: Subida y almacenamiento de imágenes en base de datos
- **Validaciones**: Validación de datos tanto en frontend como backend
- **Interfaz Responsive**: Diseño adaptable para todos los dispositivos
- **Tabla Moderna**: Visualización profesional con avatares, estados y acciones

## 📋 Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- XAMPP/WAMP/LAMP (recomendado)
- Node.js 16 o superior
- npm o yarn

## 🛠️ Instalación

### Backend (PHP)

1. Clona el repositorio:
```bash
git clone https://github.com/Jesuselias00/Itaipu-intranet.git
cd Itaipu-intranet
```

2. Configura la base de datos:
   - Crea una base de datos MySQL
   - Ejecuta los scripts SQL en `sql/schema.sql` y `sql/dummy_data.sql`
   - Configura la conexión en `app/config/database.php`

3. Configura el servidor web:
   - Coloca el proyecto en tu directorio web (ej: `htdocs` para XAMPP)
   - Asegúrate de que el módulo de reescritura esté habilitado

### Frontend (React)

1. Navega al directorio del frontend:
```bash
cd material-dashboard-react
```

2. Instala las dependencias:
```bash
npm install
```

3. Inicia el servidor de desarrollo:
```bash
npm start
```

## 📂 Estructura del Proyecto

```
Itaipu-intranet/
├── app/
│   ├── api.php                    # Punto de entrada de la API
│   ├── config/
│   │   └── database.php           # Configuración de BD
│   ├── controllers/
│   │   └── FuncionarioController.php
│   ├── core/
│   │   ├── Database.php
│   │   └── Router.php
│   └── models/
│       └── Funcionario.php
├── material-dashboard-react/
│   ├── src/
│   │   ├── api/
│   │   │   └── funcionarios.js    # Servicios API
│   │   ├── components/
│   │   ├── pages/
│   │   │   └── FuncionariosPage.js
│   │   └── FuncionariosTable.js   # Tabla principal
│   └── package.json
├── public/
│   ├── index.php                  # Punto de entrada web
│   └── api_test.html             # Interfaz de pruebas
└── sql/
    ├── schema.sql                 # Estructura de BD
    └── dummy_data.sql            # Datos de prueba
```

## 🔧 API Endpoints

### Funcionarios

- `GET /api/funcionarios` - Listar todos los funcionarios
- `GET /api/funcionarios/{id}` - Obtener funcionario específico
- `POST /api/funcionarios` - Crear nuevo funcionario
- `PUT /api/funcionarios/{id}` - Actualizar funcionario
- `DELETE /api/funcionarios/{id}` - Eliminar funcionario

### Ejemplo de uso:

```javascript
// Listar funcionarios
fetch('http://localhost/Itaipu-intranet/app/api.php/funcionarios')
  .then(response => response.json())
  .then(data => console.log(data));
```

## 🎨 Frontend

El frontend está desarrollado con:

- **React 18**: Biblioteca principal
- **Material-UI**: Components de interfaz
- **Material Dashboard**: Template profesional
- **Responsive Design**: Adaptable a todos los dispositivos

### Características del Frontend:

- Tabla moderna con paginación
- Avatares para fotos de funcionarios
- Estados visuales (Online/Offline)
- Botones de acción (Editar/Eliminar)
- Diseño profesional y moderno

## 📝 Base de Datos

### Tabla: funcionarios

```sql
CREATE TABLE funcionarios (
    id_funcionario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    sobrenome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    cargo VARCHAR(100),
    id_departamento INT,
    data_contratacao DATE,
    salario DECIMAL(10,2),
    ativo TINYINT DEFAULT 1,
    foto LONGBLOB,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🧪 Testing

### Backend
Usa `public/api_test.html` para probar los endpoints de la API.

### Frontend
```bash
cd material-dashboard-react
npm test
```

## 🚀 Despliegue

### Producción

1. **Backend**: Sube los archivos PHP a tu servidor web
2. **Frontend**: 
   ```bash
   npm run build
   ```
   Sube el contenido de `build/` a tu servidor web

### Variables de Entorno

Configura las siguientes variables para producción:
- Base de datos (host, usuario, contraseña)
- URLs de API
- Configuraciones de seguridad

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE.md](LICENSE.md) para detalles.

## 🆘 Soporte

Si encuentras algún problema o tienes preguntas:

1. Revisa la documentación
2. Busca en los issues existentes
3. Crea un nuevo issue con detalles del problema

## 📊 Estado del Proyecto

- ✅ Backend API completa
- ✅ Frontend React integrado
- ✅ Gestión de fotos
- ✅ Validaciones
- ⏳ Funcionalidades de edición (en desarrollo)
- ⏳ Sistema de autenticación (planeado)
- ⏳ Reportes y exportación (planeado)

---

Desarrollado con ❤️ para Itaipu
