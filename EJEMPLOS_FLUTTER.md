<?php
/**
 * EJEMPLOS DE USO DE LOS ENDPOINTS PARA FLUTTER
 * 
 * Este archivo contiene ejemplos de cómo usar los endpoints
 * desde tu aplicación Flutter
 */

// ==============================================
// CONFIGURACIÓN DE MATERIA
// ==============================================

/*
1. GUARDAR CONFIGURACIÓN DE MATERIA
POST: http://tu-servidor/api/configuracion.php?action=guardar

Body JSON:
{
    "materia_id": 1,
    "año_academico": 2024,
    "fecha_inicio": "2024-08-15",
    "fecha_fin": "2024-12-15",
    "dias_clase": "lunes,miercoles,viernes",
    "hora_clase": "08:00",
    "meta_asistencia": 80.00
}

Respuesta:
{
    "success": true,
    "message": "Configuración creada correctamente",
    "data": {
        "id": 1,
        "materia_id": 1,
        "año_academico": 2024,
        "fecha_inicio": "2024-08-15",
        "fecha_fin": "2024-12-15",
        "dias_clase": "lunes,miercoles,viernes",
        "hora_clase": "08:00",
        "meta_asistencia": 80.00
    }
}
*/

/*
2. OBTENER CONFIGURACIÓN DE MATERIA
GET: http://tu-servidor/api/configuracion.php?action=obtener&materia_id=1&año_academico=2024

Respuesta:
{
    "success": true,
    "message": "Configuración obtenida correctamente",
    "data": {
        "id": 1,
        "materia_id": 1,
        "nombre_materia": "Matemáticas",
        "grado": "1°",
        "seccion": "A",
        "nombre_profesor": "Juan Pérez",
        "año_academico": 2024,
        "fecha_inicio": "2024-08-15",
        "fecha_fin": "2024-12-15",
        "dias_clase": "lunes,miercoles,viernes",
        "hora_clase": "08:00",
        "meta_asistencia": 80.00
    }
}
*/

/*
3. VERIFICAR SI ES DÍA DE CLASE
GET: http://tu-servidor/api/configuracion.php?action=verificar_dia&materia_id=1&fecha=2024-01-15

Respuesta:
{
    "success": true,
    "message": "Verificación completada",
    "data": {
        "es_dia_clase": true,
        "dia_semana": "lunes",
        "dias_configurados": ["lunes", "miercoles", "viernes"]
    }
}
*/

// ==============================================
// ASISTENCIA
// ==============================================

/*
4. OBTENER ESTUDIANTES INSCRITOS
GET: http://tu-servidor/api/asistencia.php?action=estudiantes_inscritos&materia_id=1

Respuesta:
{
    "success": true,
    "message": "Estudiantes obtenidos correctamente",
    "data": [
        {
            "estudiante_id": 1,
            "nombre_estudiante": "Ana Martínez",
            "grado": "1°",
            "seccion": "A"
        },
        {
            "estudiante_id": 2,
            "nombre_estudiante": "Pedro Rodríguez",
            "grado": "1°",
            "seccion": "A"
        }
    ]
}
*/

/*
5. TOMAR ASISTENCIA DE TODA LA CLASE
POST: http://tu-servidor/api/asistencia.php?action=tomar

Body JSON:
{
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
}

Respuesta:
{
    "success": true,
    "message": "Asistencia registrada correctamente para 3 estudiantes",
    "data": {
        "fecha_clase": "2024-01-15",
        "materia_id": 1,
        "registros_insertados": 3
    }
}
*/

/*
6. OBTENER ASISTENCIA DE UNA CLASE
GET: http://tu-servidor/api/asistencia.php?action=obtener_clase&materia_id=1&fecha_clase=2024-01-15

Respuesta:
{
    "success": true,
    "message": "Asistencia obtenida correctamente",
    "data": [
        {
            "id": 1,
            "estudiante_id": 1,
            "nombre_estudiante": "Ana Martínez",
            "estado": "presente",
            "fecha_registro": "2024-01-15 08:30:00"
        },
        {
            "id": 2,
            "estudiante_id": 2,
            "nombre_estudiante": "Pedro Rodríguez",
            "estado": "ausente",
            "fecha_registro": "2024-01-15 08:30:00"
        }
    ]
}
*/

/*
7. OBTENER ESTADÍSTICAS DE ESTUDIANTE
GET: http://tu-servidor/api/asistencia.php?action=estadisticas_estudiante&estudiante_id=1&materia_id=1

Respuesta:
{
    "success": true,
    "message": "Estadísticas obtenidas correctamente",
    "data": {
        "estadisticas": {
            "total_clases": 25,
            "presentes": 20,
            "ausentes": 3,
            "tardanzas": 2,
            "porcentaje_asistencia": 80.00
        },
        "historial": [
            {
                "fecha_clase": "2024-01-15",
                "estado": "presente",
                "fecha_registro": "2024-01-15 08:30:00"
            },
            {
                "fecha_clase": "2024-01-12",
                "estado": "ausente",
                "fecha_registro": "2024-01-12 08:30:00"
            }
        ]
    }
}
*/

/*
8. OBTENER RESUMEN DE CLASE
GET: http://tu-servidor/api/asistencia.php?action=resumen_clase&materia_id=1&fecha_clase=2024-01-15

Respuesta:
{
    "success": true,
    "message": "Resumen obtenido correctamente",
    "data": {
        "total_estudiantes": 25,
        "presentes": 20,
        "ausentes": 3,
        "tardanzas": 2,
        "porcentaje_asistencia": 80.00
    }
}
*/

/*
9. ACTUALIZAR ASISTENCIA INDIVIDUAL
PUT: http://tu-servidor/api/asistencia.php?action=actualizar

Body JSON:
{
    "asistencia_id": 1,
    "estado": "presente"
}

Respuesta:
{
    "success": true,
    "message": "Asistencia actualizada correctamente"
}
*/

// ==============================================
// CÓDIGO FLUTTER DE EJEMPLO
// ==============================================

/*
// Clase para manejar las llamadas HTTP
class AsistenciaService {
  static const String baseUrl = 'http://tu-servidor/api';
  
  // Tomar asistencia
  static Future<Map<String, dynamic>> tomarAsistencia({
    required int materiaId,
    required String fechaClase,
    required int profesorId,
    required List<Map<String, dynamic>> asistencias,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/asistencia.php?action=tomar'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'materia_id': materiaId,
        'fecha_clase': fechaClase,
        'profesor_id': profesorId,
        'asistencias': asistencias,
      }),
    );
    
    return jsonDecode(response.body);
  }
  
  // Obtener estadísticas del estudiante
  static Future<Map<String, dynamic>> obtenerEstadisticasEstudiante({
    required int estudianteId,
    required int materiaId,
  }) async {
    final response = await http.get(
      Uri.parse('$baseUrl/asistencia.php?action=estadisticas_estudiante&estudiante_id=$estudianteId&materia_id=$materiaId'),
    );
    
    return jsonDecode(response.body);
  }
  
  // Guardar configuración
  static Future<Map<String, dynamic>> guardarConfiguracion({
    required int materiaId,
    required int añoAcademico,
    required String fechaInicio,
    required String fechaFin,
    required String diasClase,
    String? horaClase,
    double metaAsistencia = 80.0,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/configuracion.php?action=guardar'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'materia_id': materiaId,
        'año_academico': añoAcademico,
        'fecha_inicio': fechaInicio,
        'fecha_fin': fechaFin,
        'dias_clase': diasClase,
        'hora_clase': horaClase,
        'meta_asistencia': metaAsistencia,
      }),
    );
    
    return jsonDecode(response.body);
  }
}

// Ejemplo de uso en un widget
class TomarAsistenciaWidget extends StatefulWidget {
  @override
  _TomarAsistenciaWidgetState createState() => _TomarAsistenciaWidgetState();
}

class _TomarAsistenciaWidgetState extends State<TomarAsistenciaWidget> {
  List<Map<String, dynamic>> estudiantes = [];
  Map<String, String> asistencias = {};
  
  @override
  void initState() {
    super.initState();
    _cargarEstudiantes();
  }
  
  Future<void> _cargarEstudiantes() async {
    try {
      final response = await AsistenciaService.obtenerEstudiantesInscritos(
        materiaId: 1,
      );
      
      if (response['success']) {
        setState(() {
          estudiantes = List<Map<String, dynamic>>.from(response['data']);
        });
      }
    } catch (e) {
      print('Error: $e');
    }
  }
  
  Future<void> _guardarAsistencia() async {
    try {
      final asistenciasList = estudiantes.map((estudiante) => {
        'estudiante_id': estudiante['estudiante_id'],
        'estado': asistencias[estudiante['estudiante_id'].toString()] ?? 'ausente',
      }).toList();
      
      final response = await AsistenciaService.tomarAsistencia(
        materiaId: 1,
        fechaClase: '2024-01-15',
        profesorId: 1,
        asistencias: asistenciasList,
      );
      
      if (response['success']) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Asistencia guardada correctamente')),
        );
      }
    } catch (e) {
      print('Error: $e');
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Tomar Asistencia')),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              itemCount: estudiantes.length,
              itemBuilder: (context, index) {
                final estudiante = estudiantes[index];
                final estudianteId = estudiante['estudiante_id'].toString();
                
                return ListTile(
                  title: Text(estudiante['nombre_estudiante']),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        icon: Icon(Icons.check, color: Colors.green),
                        onPressed: () {
                          setState(() {
                            asistencias[estudianteId] = 'presente';
                          });
                        },
                      ),
                      IconButton(
                        icon: Icon(Icons.close, color: Colors.red),
                        onPressed: () {
                          setState(() {
                            asistencias[estudianteId] = 'ausente';
                          });
                        },
                      ),
                      IconButton(
                        icon: Icon(Icons.schedule, color: Colors.orange),
                        onPressed: () {
                          setState(() {
                            asistencias[estudianteId] = 'tardanza';
                          });
                        },
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
          ElevatedButton(
            onPressed: _guardarAsistencia,
            child: Text('Guardar Asistencia'),
          ),
        ],
      ),
    );
  }
}
*/
?>

