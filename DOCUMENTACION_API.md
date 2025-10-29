# 📚 DOCUMENTACIÓN COMPLETA DE APIs

## 🌐 **URLs Base**

### **Local (XAMPP):**
```
http://localhost/controladores api flutter/api/
```

### **Servidor (Hosting):**
```
https://hermanosfrios.alwaysdata.net/api/
```

---

## 🔐 **API DE AUTENTICACIÓN**
**Archivo:** `api/auth.php`

### **Endpoints Disponibles:**

| Método | Endpoint | Descripción | Parámetros |
|--------|----------|-------------|------------|
| POST | `auth.php?action=login` | Iniciar sesión | `email`, `password` |
| POST | `auth.php?action=register` | Registrar usuario | `email`, `password`, `rol`, datos específicos |
| GET | `auth.php?action=profile` | Obtener perfil | `email` |
| GET | `auth.php?action=students` | Listar estudiantes | - |
| GET | `auth.php?action=teachers` | Listar profesores | - |
| PUT | `auth.php?action=edit-student` | Editar estudiante | `estudiante_id`, datos |
| PUT | `auth.php?action=edit-teacher` | Editar profesor | `profesor_id`, datos |
| DELETE | `auth.php?action=delete-student` | Eliminar estudiante | `estudiante_id` |
| DELETE | `auth.php?action=delete-teacher` | Eliminar profesor | `profesor_id` |

### **Ejemplos de Uso:**

#### **Login:**
```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/auth.php?action=login" \
-H "Content-Type: application/json" \
-d '{
    "email": "profesor@colegio.com",
    "password": "123456"
}'
```

#### **Registrar Estudiante:**
```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/auth.php?action=register" \
-H "Content-Type: application/json" \
-d '{
    "email": "estudiante@colegio.com",
    "password": "123456",
    "rol": "estudiante",
    "nombre": "Ana",
    "apellido": "Martínez",
    "grado": "1°",
    "seccion": "A"
}'
```

---

## 📚 **API DE MATERIAS**
**Archivo:** `api/materias.php`

### **Endpoints Disponibles:**

| Método | Endpoint | Descripción | Parámetros |
|--------|----------|-------------|------------|
| POST | `materias.php?action=create` | Crear materia | `nombre`, `grado`, `seccion`, `profesor_id`, `año_academico` |
| GET | `materias.php?action=all` | Listar todas las materias | - |
| GET | `materias.php?action=by-profesor` | Materias de un profesor | `profesor_id` |
| PUT | `materias.php?action=edit` | Editar materia | `materia_id`, datos |
| DELETE | `materias.php?action=delete` | Eliminar materia | `materia_id` |

### **Ejemplos de Uso:**

#### **Crear Materia:**
```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/materias.php?action=create" \
-H "Content-Type: application/json" \
-d '{
    "nombre": "Matemáticas",
    "grado": "1°",
    "seccion": "A",
    "profesor_id": 1,
    "año_academico": 2024
}'
```

#### **Materias de un Profesor:**
```bash
curl -X GET "https://hermanosfrios.alwaysdata.net/api/materias.php?action=by-profesor&profesor_id=1"
```

---

## 📝 **API DE INSCRIPCIONES**
**Archivo:** `api/inscripciones.php`

### **Endpoints Disponibles:**

| Método | Endpoint | Descripción | Parámetros |
|--------|----------|-------------|------------|
| POST | `inscripciones.php?action=create` | Inscribir estudiante | `estudiante_id`, `materia_id` |
| GET | `inscripciones.php?action=by-estudiante` | Inscripciones de estudiante | `estudiante_id` |
| GET | `inscripciones.php?action=by-materia` | Estudiantes de una materia | `materia_id` |
| DELETE | `inscripciones.php?action=delete` | Eliminar inscripción | `inscripcion_id` |

---

## ⚙️ **API DE CONFIGURACIÓN DE ASISTENCIA**
**Archivo:** `api/configuracion.php`

### **Endpoints Disponibles:**

| Método | Endpoint | Descripción | Parámetros |
|--------|----------|-------------|------------|
| POST | `configuracion.php?action=guardar` | Guardar configuración | `materia_id`, `año_academico`, `fecha_inicio`, `fecha_fin`, `dias_clase`, `hora_clase`, `meta_asistencia` |
| GET | `configuracion.php?action=obtener` | Obtener configuración | `materia_id`, `año_academico` |
| GET | `configuracion.php?action=profesor` | Configuraciones de profesor | `profesor_id`, `año_academico` |
| GET | `configuracion.php?action=verificar_dia` | Verificar día de clase | `materia_id`, `fecha` |
| DELETE | `configuracion.php?action=eliminar` | Eliminar configuración | `materia_id`, `año_academico` |

### **Ejemplos de Uso:**

#### **Guardar Configuración:**
```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/configuracion.php?action=guardar" \
-H "Content-Type: application/json" \
-d '{
    "materia_id": 1,
    "año_academico": 2024,
    "fecha_inicio": "2024-08-15",
    "fecha_fin": "2024-12-15",
    "dias_clase": "lunes,miercoles,viernes",
    "hora_clase": "08:00",
    "meta_asistencia": 80.00
}'
```

#### **Verificar Día de Clase:**
```bash
curl -X GET "https://hermanosfrios.alwaysdata.net/api/configuracion.php?action=verificar_dia&materia_id=1&fecha=2024-01-15"
```

---

## 📊 **API DE ASISTENCIA**
**Archivo:** `api/asistencia.php`

### **Endpoints Disponibles:**

| Método | Endpoint | Descripción | Parámetros |
|--------|----------|-------------|------------|
| POST | `asistencia.php?action=tomar` | Tomar asistencia | `materia_id`, `fecha_clase`, `profesor_id`, `asistencias[]` |
| GET | `asistencia.php?action=obtener_clase` | Ver asistencia de clase | `materia_id`, `fecha_clase` |
| GET | `asistencia.php?action=estadisticas_estudiante` | Estadísticas de estudiante | `estudiante_id`, `materia_id` |
| GET | `asistencia.php?action=estudiantes_inscritos` | Estudiantes inscritos | `materia_id` |
| PUT | `asistencia.php?action=actualizar` | Actualizar asistencia | `asistencia_id`, `estado` |
| GET | `asistencia.php?action=resumen_clase` | Resumen de clase | `materia_id`, `fecha_clase` |

### **Ejemplos de Uso:**

#### **Tomar Asistencia:**
```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/asistencia.php?action=tomar" \
-H "Content-Type: application/json" \
-d '{
    "materia_id": 1,
    "fecha_clase": "2024-01-15",
    "profesor_id": 1,
    "asistencias": [
        {
            "estudiante_id": 1,
            "estado": "presente"
        },
        {
            "estudiante_id": 2,
            "estado": "ausente"
        },
        {
            "estudiante_id": 3,
            "estado": "tardanza"
        }
    ]
}'
```

#### **Estadísticas del Estudiante:**
```bash
curl -X GET "https://hermanosfrios.alwaysdata.net/api/asistencia.php?action=estadisticas_estudiante&estudiante_id=1&materia_id=1"
```

#### **Resumen de Clase:**
```bash
curl -X GET "https://hermanosfrios.alwaysdata.net/api/asistencia.php?action=resumen_clase&materia_id=1&fecha_clase=2024-01-15"
```

---

## 🧪 **API DE PRUEBA**
**Archivo:** `api/test.php`

### **Endpoint:**

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `test.php` | Probar conexión y configuración |

### **Ejemplo:**
```bash
curl -X GET "https://hermanosfrios.alwaysdata.net/api/test.php"
```

---

## 📋 **ESTRUCTURA DE ARCHIVOS**

```
controladores api flutter/
├── api/
│   ├── auth.php              # Autenticación
│   ├── materias.php          # Gestión de materias
│   ├── inscripciones.php     # Inscripciones
│   ├── configuracion.php     # Configuración de asistencia
│   ├── asistencia.php        # Sistema de asistencia
│   └── test.php              # Pruebas
├── controllers/
│   ├── AuthController.php
│   ├── MateriaController.php
│   ├── InscripcionController.php
│   ├── ConfiguracionController.php
│   └── AsistenciaController.php
├── models/
│   ├── User.php
│   ├── Materia.php
│   ├── Inscripcion.php
│   ├── ConfiguracionMateria.php
│   └── Asistencia.php
├── config/
│   ├── database.php
│   ├── connection.php
│   └── env_loader.php
└── colegio_login_materias.sql
```

---

## 🔧 **CÓDIGOS DE RESPUESTA**

| Código | Significado |
|--------|-------------|
| 200 | Éxito |
| 201 | Creado exitosamente |
| 400 | Error en los datos enviados |
| 401 | No autorizado |
| 404 | No encontrado |
| 405 | Método no permitido |
| 409 | Conflicto (ej: email duplicado) |
| 500 | Error interno del servidor |

---

## 📝 **FORMATOS DE DATOS**

### **Estados de Asistencia:**
- `"presente"`
- `"ausente"`
- `"tardanza"`

### **Roles de Usuario:**
- `"estudiante"`
- `"profesor"`

### **Días de Clase:**
- `"lunes,martes,miercoles,jueves,viernes"`

### **Formato de Fecha:**
- `YYYY-MM-DD` (ej: `2024-01-15`)

### **Formato de Hora:**
- `HH:MM` (ej: `08:00`)

---

## 🚀 **FLUJO DE TRABAJO RECOMENDADO**

1. **Configurar materia** → `POST configuracion.php?action=guardar`
2. **Inscribir estudiantes** → `POST inscripciones.php?action=create`
3. **Tomar asistencia** → `POST asistencia.php?action=tomar`
4. **Ver estadísticas** → `GET asistencia.php?action=estadisticas_estudiante`

---

## ⚠️ **NOTAS IMPORTANTES**

- Todas las respuestas son en formato JSON
- Los headers CORS están configurados para Flutter
- Las fechas deben estar en formato `YYYY-MM-DD`
- Los IDs deben ser números enteros
- La meta de asistencia debe estar entre 0 y 100
