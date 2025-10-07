# Colegio API - Sistema de Login y Gestión

API REST para sistema de gestión escolar con autenticación de usuarios (estudiantes, profesores, admin).

## 📁 Estructura del Proyecto

```
controladores-api-flutter/
├── config/
│   ├── database.php          # Configuración de base de datos
│   └── connection.php        # Clase de conexión PDO
├── models/
│   └── User.php             # Modelo de usuario
├── controllers/
│   └── AuthController.php   # Controlador de autenticación
├── api/
│   ├── auth.php            # Endpoint de autenticación
│   └── test.php            # Endpoint de prueba
├── colegio_login_materias.sql # Base de datos
└── README.md
```

## ⚙️ Configuración

### 1. Base de Datos
- Importar el archivo `colegio_login_materias.sql` en tu base de datos
- Configurar las credenciales en `config/database.php`

### 2. Configuración Local vs Hosting

#### Para Local (XAMPP/WAMP):
```php
// En config/database.php
$environment = 'local';
$base_path = '/controladores-api-flutter'; // Ajustar según tu carpeta
```

#### Para Hosting:
```php
// En config/database.php
$environment = 'hosting';
$base_path = ''; // Usualmente la raíz del dominio
```

### 3. Configuración de Base de Datos

#### Local:
```php
'local' => [
    'host' => 'localhost',
    'dbname' => 'colegio_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
]
```

#### Hosting:
```php
'hosting' => [
    'host' => 'localhost', // o IP del servidor
    'dbname' => 'tu_base_datos_hosting',
    'username' => 'tu_usuario_hosting',
    'password' => 'tu_password_hosting',
    'charset' => 'utf8mb4'
]
```

## 🚀 Endpoints de la API

### Base URL
- **Local:** `http://localhost/controladores-api-flutter/api/`
- **Hosting:** `https://tudominio.com/api/`

### 1. Probar API
```
GET /api/test.php
```

### 2. Login
```
POST /api/auth.php?action=login
Content-Type: application/json

{
    "email": "ana@email.com",
    "password": "password"
}
```

**Respuesta exitosa:**
```json
{
    "success": true,
    "message": "Login exitoso",
    "data": {
        "id": 4,
        "email": "ana@email.com",
        "rol": "estudiante",
        "user_data": {
            "id": 1,
            "nombre": "Ana",
            "apellido": "Martínez",
            "grado": "1°",
            "seccion": "A"
        }
    }
}
```

### 3. Registro de Estudiante
```
POST /api/auth.php?action=register
Content-Type: application/json

{
    "email": "nuevo@email.com",
    "password": "password123",
    "rol": "estudiante",
    "nombre": "Juan",
    "apellido": "Pérez",
    "grado": "2°",
    "seccion": "B",
    "telefono": "123456789",
    "direccion": "Calle 123"
}
```

### 4. Registro de Profesor
```
POST /api/auth.php?action=register
Content-Type: application/json

{
    "email": "profesor@colegio.edu",
    "password": "password123",
    "rol": "profesor",
    "nombre": "María",
    "apellido": "García",
    "telefono": "987654321",
    "direccion": "Avenida 456"
}
```

### 5. Obtener Perfil
```
GET /api/auth.php?action=profile&email=ana@email.com
```

## 🔐 Usuarios de Prueba

### Admin
- **Email:** admin@colegio.com
- **Password:** password

### Profesores
- **Email:** miguel@colegio.edu
- **Password:** password
- **Email:** laura@colegio.edu
- **Password:** password

### Estudiantes
- **Email:** ana@email.com
- **Password:** password
- **Email:** pedro@email.com
- **Password:** password

## 📱 Uso con Flutter

### Ejemplo de Login en Flutter:
```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class AuthService {
  static const String baseUrl = 'http://localhost/controladores-api-flutter/api';
  
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth.php?action=login'),
      headers: {'Content-Type': 'application/json'},
      body: json.encode({
        'email': email,
        'password': password,
      }),
    );
    
    return json.decode(response.body);
  }
}
```

## 🛠️ Características

- ✅ **Autenticación segura** con hash de contraseñas
- ✅ **Roles diferenciados** (admin, profesor, estudiante)
- ✅ **Configuración flexible** para local y hosting
- ✅ **CORS habilitado** para Flutter
- ✅ **Validaciones completas** de datos
- ✅ **Manejo de errores** robusto
- ✅ **Respuestas JSON** estandarizadas

## 🔧 Troubleshooting

### Error de conexión a base de datos:
1. Verificar credenciales en `config/database.php`
2. Asegurar que la base de datos existe
3. Verificar que el archivo SQL se importó correctamente

### Error 404 en endpoints:
1. Verificar la configuración de `BASE_PATH`
2. Asegurar que la estructura de carpetas es correcta
3. Verificar permisos de archivos en el servidor

### Error CORS en Flutter:
1. Verificar que los headers CORS están configurados
2. Asegurar que la URL base es correcta
3. Verificar que el método HTTP es correcto (POST para login/register)
