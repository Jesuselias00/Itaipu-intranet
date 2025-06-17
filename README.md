# Sistema de Gestión de Funcionarios - Backend PHP

API RESTful completa para la gestión de funcionarios desarrollada en PHP.

## 🚀 Características

- **API RESTful Completa**: CRUD completo para funcionarios
- **Gestión de Fotos**: Subida y almacenamiento de imágenes en base de datos
- **Validaciones**: Validación de datos robusta
- **Estructura MVC**: Arquitectura organizada y escalable
- **Base de Datos MySQL**: Esquema optimizado
- **Interfaz de Pruebas**: HTML simple para testing de la API

## 📋 Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- XAMPP/WAMP/LAMP (recomendado)

## 🛠️ Instalación

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

## 📂 Estructura del Proyecto

```
Itaipu-intranet/
├── app/
│   ├── api.php                    # Punto de entrada de la API
│   ├── config/
│   │   └── database.php           # Configuración de BD
│   ├── controllers/
│   │   ├── FuncionarioController.php
│   │   └── CrachaController.php
│   ├── core/
│   │   ├── Database.php           # Clase de conexión a BD
│   │   └── Router.php             # Enrutador de la API
│   └── models/
│       ├── Funcionario.php
│       ├── Departamento.php
│       ├── Cracha.php
│       └── MotivoCracha.php
├── public/
│   ├── index.php                  # Punto de entrada web
│   ├── api_test.html             # Interfaz de pruebas
│   └── assets/
│       └── img/
│           └── funcionarios/      # Imágenes de funcionarios
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

```bash
# Listar funcionarios
curl -X GET http://localhost/Itaipu-intranet/app/api.php/funcionarios

# Crear funcionario
curl -X POST http://localhost/Itaipu-intranet/app/api.php/funcionarios \
  -H "Content-Type: application/json" \
  -d '{"nome":"Juan","sobrenome":"Pérez","email":"juan@email.com"}'

# Actualizar funcionario
curl -X PUT http://localhost/Itaipu-intranet/app/api.php/funcionarios/1 \
  -H "Content-Type: application/json" \
  -d '{"nome":"Juan Carlos","cargo":"Desarrollador"}'

# Eliminar funcionario
curl -X DELETE http://localhost/Itaipu-intranet/app/api.php/funcionarios/1
```

## 🧪 Testing

Usa `public/api_test.html` para probar los endpoints de la API desde una interfaz web simple.

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

## 🚀 Despliegue

### Producción

1. Sube los archivos PHP a tu servidor web
2. Configura la base de datos en el servidor
3. Asegúrate de que los permisos de escritura estén configurados para la carpeta de imágenes

### Variables de Entorno

Configura las siguientes variables para producción:
- Base de datos (host, usuario, contraseña)
- Configuraciones de seguridad
- Permisos de carpetas

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
- ✅ Sistema CRUD de funcionarios
- ✅ Gestión de fotos
- ✅ Validaciones de datos
- ✅ Interfaz de pruebas
- ⏳ Sistema de autenticación (planeado)
- ⏳ Roles y permisos (planeado)
- ⏳ Reportes y exportación (planeado)

---

Desarrollado con ❤️ para Itaipu
