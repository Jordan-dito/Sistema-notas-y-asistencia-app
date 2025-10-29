# 📚 Documentación Completa de Endpoints API

**Base URL**: `https://hermanosfrios.alwaysdata.net/api`

---

## 🔐 1. AUTENTICACIÓN (`auth.php`)

### 1.1. Login
- **Método**: `POST`
- **URL**: `/api/auth.php?action=login`
- **Body**:
```json
{
  "email": "profesor@colegio.edu",
  "password": "password"
}
```
- **Respuesta**:
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {...},
    "token": "..."
  }
}
```

### 1.2. Register (Registro)
- **Método**: `POST`
- **URL**: `/api/auth.php?action=register`
- **Body**: Datos del usuario a registrar

### 1.3. Profile (Perfil)
- **Método**: `GET`
- **URL**: `/api/auth.php?action=profile&usuario_id={id}`

### 1.4. Students (Listar Estudiantes)
- **Método**: `GET`
- **URL**: `/api/auth.php?action=students`

### 1.5. Teachers (Listar Profesores)
- **Método**: `GET`
- **URL**: `/api/auth.php?action=teachers`

### 1.6. Edit Student (Editar Estudiante)
- **Método**: `PUT`
- **URL**: `/api/auth.php?action=edit-student`

### 1.7. Edit Teacher (Editar Profesor)
- **Método**: `PUT`
- **URL**: `/api/auth.php?action=edit-teacher`

### 1.8. Delete Student (Eliminar Estudiante)
- **Método**: `DELETE`
- **URL**: `/api/auth.php?action=delete-student&id={id}`

### 1.9. Delete Teacher (Eliminar Profesor)
- **Método**: `DELETE`
- **URL**: `/api/auth.php?action=delete-teacher&id={id}`

---

## 📚 2. MATERIAS (`materias.php`)

### 2.1. Create (Crear Materia)
- **Método**: `POST`
- **URL**: `/api/materias.php?action=create`
- **Body**:
```json
{
  "nombre": "Matemáticas",
  "grado": "10",
  "seccion": "A",
  "profesor_id": 2,
  "año_academico": "2025"
}
```

### 2.2. All (Obtener Todas las Materias)
- **Método**: `GET`
- **URL**: `/api/materias.php?action=all`

### 2.3. By Profesor (Materias de un Profesor)
- **Método**: `GET`
- **URL**: `/api/materias.php?action=by-profesor&profesor_id={id}`

### 2.4. Edit (Editar Materia)
- **Método**: `PUT`
- **URL**: `/api/materias.php?action=edit`

### 2.5. Delete (Eliminar Materia)
- **Método**: `DELETE`
- **URL**: `/api/materias.php?action=delete&id={id}`

---

## 📝 3. MATERIAS DEL ESTUDIANTE (`materias_estudiante.php`)

### 3.1. Obtener Materias de un Estudiante
- **Método**: `GET`
- **URL**: `/api/materias_estudiante.php?usuario_id={id}`
- **Parámetros**:
  - `usuario_id` (requerido): ID del usuario estudiante
- **Respuesta**:
```json
{
  "success": true,
  "message": "Materias obtenidas correctamente",
  "data": {
    "usuario_id": 10,
    "total_materias": 5,
    "materias": [...]
  }
}
```

---

## 👥 4. VISTA ESTUDIANTES Y MATERIAS (`vista_estudiantes_materias.php`)

### 4.1. Obtener Vista Completa
- **Método**: `GET`
- **URL**: `/api/vista_estudiantes_materias.php`
- **Parámetros opcionales**:
  - `materia_id`: Filtrar por materia específica
  - `profesor_id`: Filtrar por profesor específico
- **Ejemplo**: `/api/vista_estudiantes_materias.php?materia_id=5`
- **Respuesta**:
```json
{
  "success": true,
  "message": "Vista de estudiantes y materias obtenida correctamente",
  "data": {
    "filtros_aplicados": {...},
    "total_materias": 2,
    "total_estudiantes": 15,
    "materias": [
      {
        "materia_id": 1,
        "nombre_materia": "Matemáticas",
        "grado": "10",
        "seccion": "A",
        "nombre_profesor": "Juan Pérez",
        "año_academico": "2024",
        "total_estudiantes": 8,
        "estudiantes": [...]
      }
    ]
  }
}
```

---

## 📋 5. INSCRIPCIONES (`inscripciones.php`)

### 5.1. Create (Crear Inscripción)
- **Método**: `POST`
- **URL**: `/api/inscripciones.php?action=create`
- **Body**:
```json
{
  "estudiante_id": 10,
  "materia_id": 5
}
```

### 5.2. All (Obtener Todas las Inscripciones)
- **Método**: `GET`
- **URL**: `/api/inscripciones.php?action=all`

### 5.3. By Estudiante (Inscripciones de un Estudiante)
- **Método**: `GET`
- **URL**: `/api/inscripciones.php?action=by-estudiante&estudiante_id={id}`

### 5.4. By Profesor (Inscripciones de Materias de un Profesor)
- **Método**: `GET`
- **URL**: `/api/inscripciones.php?action=by-profesor&profesor_id={id}`

### 5.5. Delete (Eliminar Inscripción)
- **Método**: `DELETE`
- **URL**: `/api/inscripciones.php?action=delete&id={id}`

### 5.6. Update (Actualizar Inscripción)
- **Método**: `PUT`
- **URL**: `/api/inscripciones.php?action=update`

---

## ✅ 6. ASISTENCIA (`asistencia.php`)

### 6.1. Tomar Asistencia (`action=tomar`)
- **Método**: `POST`
- **URL**: `/api/asistencia.php?action=tomar`
- **Body**:
```json
{
  "materia_id": 5,
  "fecha_clase": "2025-10-29",
  "profesor_id": 5,
  "asistencias": [
    {
      "estudiante_id": 16,
      "estado": "presente"
    },
    {
      "estudiante_id": 19,
      "estado": "ausente"
    },
    {
      "estudiante_id": 20,
      "estado": "tardanza"
    }
  ]
}
```
- **Estados válidos**: `presente`, `ausente`, `tardanza`
- **Respuesta**:
```json
{
  "success": true,
  "message": "Asistencia registrada correctamente para 3 estudiantes",
  "data": {
    "fecha_clase": "2025-10-29",
    "materia_id": 5,
    "registros_insertados": 3
  }
}
```

### 6.2. Obtener Asistencia de Clase (`action=obtener_clase`)
- **Método**: `GET`
- **URL**: `/api/asistencia.php?action=obtener_clase&materia_id={id}&fecha_clase={fecha}`
- **Parámetros**:
  - `materia_id` (requerido)
  - `fecha_clase` (requerido): Formato `YYYY-MM-DD`

### 6.3. Estadísticas Estudiante (`action=estadisticas_estudiante`)
- **Método**: `GET`
- **URL**: `/api/asistencia.php?action=estadisticas_estudiante&estudiante_id={id}&materia_id={id}`

### 6.4. Estudiantes Inscritos (`action=estudiantes_inscritos`)
- **Método**: `GET`
- **URL**: `/api/asistencia.php?action=estudiantes_inscritos&materia_id={id}`

### 6.5. Actualizar Asistencia (`action=actualizar`)
- **Método**: `PUT`
- **URL**: `/api/asistencia.php?action=actualizar`
- **Body**:
```json
{
  "asistencia_id": 1,
  "estado": "presente"
}
```

### 6.6. Resumen Clase (`action=resumen_clase`)
- **Método**: `GET`
- **URL**: `/api/asistencia.php?action=resumen_clase&materia_id={id}&fecha_clase={fecha}`

---

## ⚙️ 7. CONFIGURACIÓN (`configuracion.php`)

### 7.1. Guardar Configuración (`action=guardar`)
- **Método**: `POST`
- **URL**: `/api/configuracion.php?action=guardar`
- **Body**:
```json
{
  "materia_id": 5,
  "año_academico": "2025",
  "fecha_inicio": "2025-10-27",
  "fecha_fin": "2026-02-24",
  "dias_clase": "lunes,martes,miercoles,jueves,viernes",
  "hora_clase": "08:00:00",
  "meta_asistencia": 80.00
}
```

### 7.2. Obtener Configuración (`action=obtener`)
- **Método**: `GET`
- **URL**: `/api/configuracion.php?action=obtener&materia_id={id}&año_academico={año}`

### 7.3. Configuraciones de Profesor (`action=profesor`)
- **Método**: `GET`
- **URL**: `/api/configuracion.php?action=profesor&profesor_id={id}`

### 7.4. Verificar Día de Clase (`action=verificar_dia`)
- **Método**: `GET`
- **URL**: `/api/configuracion.php?action=verificar_dia&materia_id={id}&fecha={fecha}`

### 7.5. Eliminar Configuración (`action=eliminar`)
- **Método**: `DELETE`
- **URL**: `/api/configuracion.php?action=eliminar&id={id}`

---

## 📝 Ejemplos de cURL

### Ejemplo 1: Login
```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/auth.php?action=login" \
  -H "Content-Type: application/json" \
  -d '{"email":"profesor@colegio.edu","password":"password"}'
```

### Ejemplo 2: Tomar Asistencia
```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/asistencia.php?action=tomar" \
  -H "Content-Type: application/json" \
  -d '{"materia_id":5,"fecha_clase":"2025-10-29","profesor_id":5,"asistencias":[{"estudiante_id":16,"estado":"presente"}]}'
```

### Ejemplo 3: Obtener Materias de Estudiante
```bash
curl -X GET "https://hermanosfrios.alwaysdata.net/api/materias_estudiante.php?usuario_id=10" \
  -H "Content-Type: application/json"
```

### Ejemplo 4: Vista de Estudiantes y Materias
```bash
curl -X GET "https://hermanosfrios.alwaysdata.net/api/vista_estudiantes_materias.php?materia_id=5" \
  -H "Content-Type: application/json"
```

---

## 📊 Resumen de Endpoints por Categoría

| Categoría | Total Endpoints | Archivo |
|----------|----------------|---------|
| Autenticación | 9 | `auth.php` |
| Materias | 5 | `materias.php` |
| Materias Estudiante | 1 | `materias_estudiante.php` |
| Vista Estudiantes | 1 | `vista_estudiantes_materias.php` |
| Inscripciones | 6 | `inscripciones.php` |
| Asistencia | 6 | `asistencia.php` |
| Configuración | 5 | `configuracion.php` |
| **TOTAL** | **33** | |

---

## 🔒 Headers Comunes

Todos los endpoints requieren:
```
Content-Type: application/json
Access-Control-Allow-Origin: *
```

---

## ✅ Códigos de Estado HTTP

- `200`: Operación exitosa
- `400`: Error de validación (datos incorrectos)
- `404`: Recurso no encontrado
- `405`: Método HTTP no permitido
- `500`: Error interno del servidor

---

**Última actualización**: Octubre 2025

