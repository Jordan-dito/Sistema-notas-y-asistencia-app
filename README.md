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

### 1. Instalación
```bash
# Clonar el repositorio
git clone https://github.com/Jordan-dito/Sistema-notas-y-asistencia-app.git
cd Sistema-notas-y-asistencia-app

# Copiar archivo de configuración
cp .env.example .env
```

### 2. Configuración con Variables de Entorno (.env)

#### Para Local (XAMPP/WAMP):
```env
ENVIRONMENT=local
DB_HOST_LOCAL=localhost
DB_NAME_LOCAL=colegio_db
DB_USER_LOCAL=root
DB_PASS_LOCAL=
DB_CHARSET_LOCAL=utf8mb4
BASE_PATH_LOCAL=/controladores api flutter
DOMAIN_LOCAL=localhost
```

#### Para Hosting:
```env
ENVIRONMENT=hosting
DB_HOST_HOSTING=localhost
DB_NAME_HOSTING=tu_base_datos_hosting
DB_USER_HOSTING=tu_usuario_hosting
DB_PASS_HOSTING=tu_password_hosting
DB_CHARSET_HOSTING=utf8mb4
BASE_PATH_HOSTING=
DOMAIN_HOSTING=tu-dominio.com
```

### 3. Base de Datos
- Importar el archivo `colegio_login_materias.sql` en tu base de datos
- Las credenciales se configuran automáticamente desde el archivo `.env`

### 4. Configuración Automática
El sistema detecta automáticamente el entorno basándose en:
- La variable `ENVIRONMENT` en el archivo `.env`
- O por el dominio (localhost = local, otros = hosting)

## 🚀 Endpoints de la API

### Base URL
- **Local:** `http://localhost/controladores api flutter/api/`
- **Hosting:** `https://tu-dominio.com/api/`

> **Nota:** Las URLs se configuran automáticamente desde el archivo `.env`

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

## 🔒 Seguridad

### Variables de Entorno
- ✅ **Archivo `.env`** no se sube al repositorio (está en `.gitignore`)
- ✅ **Credenciales sensibles** protegidas
- ✅ **JWT Secret** configurable
- ✅ **Detección automática** de entorno

### Configuración Segura
```env
# Cambiar estos valores en producción
JWT_SECRET=tu_clave_secreta_muy_larga_y_segura_123456789
DB_PASS_HOSTING=tu_password_super_seguro
```

## 🔧 Troubleshooting

### Error de conexión a base de datos:
1. Verificar credenciales en el archivo `.env`
2. Asegurar que la base de datos existe
3. Verificar que el archivo SQL se importó correctamente

### Error 404 en endpoints:
1. Verificar la configuración de `BASE_PATH_LOCAL` o `BASE_PATH_HOSTING` en `.env`
2. Asegurar que la estructura de carpetas es correcta
3. Verificar permisos de archivos en el servidor

### Error CORS en Flutter:
1. Verificar que los headers CORS están configurados
2. Asegurar que la URL base es correcta
3. Verificar que el método HTTP es correcto (POST para login/register)

### Error de variables de entorno:
1. Verificar que el archivo `.env` existe
2. Verificar que las variables están definidas correctamente
3. Verificar que no hay espacios extra en las variables
