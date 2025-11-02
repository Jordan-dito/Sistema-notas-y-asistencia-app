# Pruebas cURL - Módulo de Reforzamiento

## Base URL
- **Localhost**: `http://localhost/controladores%20api%20flutter/api/reforzamiento.php`
- **Hosting**: `https://hermanosfrios.alwaysdata.net/api/reforzamiento.php`

---

## 1. SUBIR MATERIAL DE REFORZAMIENTO (Profesor)
### 1.1 Material de Texto (General para todos los reprobados)

```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=subir" \
  -H "Content-Type: multipart/form-data" \
  -F "materia_id=5" \
  -F "estudiante_id=" \
  -F "profesor_id=5" \
  -F "año_academico=2025" \
  -F "titulo=Guía de Estudio - Ciencias Naturales" \
  -F "descripcion=Material de reforzamiento general para todos los estudiantes reprobados en Ciencias Naturales" \
  -F "tipo_contenido=texto" \
  -F "contenido=Este es el contenido del material de reforzamiento. Incluye ejercicios y explicaciones detalladas sobre los temas evaluados."
```

### 1.2 Material de Texto (Específico para un estudiante)

```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=subir" \
  -H "Content-Type: multipart/form-data" \
  -F "materia_id=5" \
  -F "estudiante_id=14" \
  -F "profesor_id=5" \
  -F "año_academico=2025" \
  -F "titulo=Ejercicios Personalizados - Fernando" \
  -F "descripcion=Ejercicios específicos basados en tus debilidades" \
  -F "tipo_contenido=texto" \
  -F "contenido=1. Ejercicio sobre fotosíntesis\\n2. Problemas de química básica\\n3. Análisis de ecosistemas"
```

### 1.3 Material con Imagen (PDF o Imagen)

```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=subir" \
  -H "Content-Type: multipart/form-data" \
  -F "materia_id=5" \
  -F "estudiante_id=" \
  -F "profesor_id=5" \
  -F "año_academico=2025" \
  -F "titulo=Guía Visual - Ecosistemas" \
  -F "descripcion=Diagramas y esquemas de ecosistemas" \
  -F "tipo_contenido=imagen" \
  -F "archivo=@/ruta/a/tu/imagen.jpg"
```

**Windows (PowerShell):**
```powershell
$filePath = "C:\ruta\a\tu\imagen.jpg"
curl.exe -X POST "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=subir" `
  -F "materia_id=5" `
  -F "estudiante_id=" `
  -F "profesor_id=5" `
  -F "año_academico=2025" `
  -F "titulo=Guía Visual - Ecosistemas" `
  -F "tipo_contenido=imagen" `
  -F "archivo=@$filePath"
```

### 1.4 Material PDF

```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=subir" \
  -H "Content-Type: multipart/form-data" \
  -F "materia_id=5" \
  -F "estudiante_id=" \
  -F "profesor_id=5" \
  -F "año_academico=2025" \
  -F "titulo=Manual Completo de Refuerzo" \
  -F "descripcion=PDF con ejercicios y teoría" \
  -F "tipo_contenido=pdf" \
  -F "archivo=@/ruta/a/tu/documento.pdf"
```

### 1.5 Material con Link Externo

```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=subir" \
  -H "Content-Type: multipart/form-data" \
  -F "materia_id=5" \
  -F "estudiante_id=" \
  -F "profesor_id=5" \
  -F "año_academico=2025" \
  -F "titulo=Video Tutorial - YouTube" \
  -F "descripcion=Video explicativo sobre fotosíntesis" \
  -F "tipo_contenido=link" \
  -F "url_externa=https://www.youtube.com/watch?v=ejemplo"
```

### 1.6 Material con Video

```bash
curl -X POST "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=subir" \
  -H "Content-Type: multipart/form-data" \
  -F "materia_id=5" \
  -F "estudiante_id=" \
  -F "profesor_id=5" \
  -F "año_academico=2025" \
  -F "titulo=Lección en Video" \
  -F "descripcion=Clase grabada sobre el tema" \
  -F "tipo_contenido=video" \
  -F "url_externa=https://drive.google.com/file/d/ejemplo/view"
```

---

## 2. OBTENER MATERIAL PARA ESTUDIANTE REPROBADO

```bash
curl -X GET "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=obtener_estudiante&estudiante_id=14&materia_id=5&año_academico=2025"
```

**Respuesta esperada:**
- Si el estudiante está reprobado: lista de materiales
- Si el estudiante NO está reprobado: `reprobado: false`

---

## 3. OBTENER ESTUDIANTES REPROBADOS (Profesor)

```bash
curl -X GET "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=estudiantes_reprobados&materia_id=5&profesor_id=5&año_academico=2025"
```

**Respuesta:** Lista de estudiantes con promedio < 60 y cantidad de materiales disponibles

---

## 4. OBTENER MATERIAL POR ESTUDIANTE (Profesor)

```bash
curl -X GET "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=material_por_estudiante&estudiante_id=14&materia_id=5&año_academico=2025"
```

**Respuesta:** Todos los materiales (específicos y generales) que puede ver ese estudiante

---

## 5. ELIMINAR MATERIAL DE REFORZAMIENTO

```bash
curl -X DELETE "https://hermanosfrios.alwaysdata.net/api/reforzamiento.php?action=eliminar&material_id=1&profesor_id=5"
```

---

## Ejemplos de Respuestas

### Respuesta exitosa al subir material:
```json
{
  "success": true,
  "message": "Material de reforzamiento subido correctamente",
  "data": {
    "id": 1,
    "materia_id": 5,
    "estudiante_id": null,
    "titulo": "Guía de Estudio - Ciencias Naturales",
    "tipo_contenido": "texto"
  }
}
```

### Respuesta al obtener material (estudiante reprobado):
```json
{
  "success": true,
  "message": "Material de reforzamiento obtenido correctamente",
  "reprobado": true,
  "promedio": 57.50,
  "data": [
    {
      "id": 1,
      "titulo": "Guía de Estudio - Ciencias Naturales",
      "descripcion": "Material de reforzamiento general...",
      "tipo_contenido": "texto",
      "contenido": "Este es el contenido...",
      "nombre_profesor": "Brandon Mendez",
      "fecha_publicacion": "2025-11-01"
    }
  ]
}
```

### Respuesta si estudiante NO está reprobado:
```json
{
  "success": true,
  "message": "No estás reprobado en esta materia",
  "data": [],
  "reprobado": false
}
```

### Respuesta de error (estudiante no reprobado):
```json
{
  "success": false,
  "message": "Este estudiante no está reprobado. El material de reforzamiento es solo para estudiantes reprobados."
}
```

---

## Notas Importantes

1. **Material General vs Específico:**
   - `estudiante_id=""` o `estudiante_id=null` → Material para TODOS los reprobados
   - `estudiante_id=14` → Material solo para ese estudiante

2. **Validación de Reprobados:**
   - Solo estudiantes con `promedio < 60` pueden ver/recibir material
   - Si intentas subir material para estudiante aprobado, recibirás error

3. **Tipos de Archivo Permitidos:**
   - Imágenes: JPG, PNG, GIF
   - Documentos: PDF
   - Videos: MP4, MPEG
   - Tamaño máximo: 10MB

4. **URLs de Archivos:**
   - Los archivos se guardan en `uploads/reforzamiento/`
   - La URL se genera automáticamente en la respuesta

---

## Pruebas Recomendadas

1. ✅ Subir material general de texto
2. ✅ Subir material específico para estudiante reprobado
3. ✅ Intentar subir material para estudiante aprobado (debe fallar)
4. ✅ Obtener material como estudiante reprobado
5. ✅ Obtener material como estudiante aprobado (debe devolver reprobado: false)
6. ✅ Ver lista de estudiantes reprobados (profesor)
7. ✅ Eliminar material

