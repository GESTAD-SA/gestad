# Implementación de Suite de Pruebas Automatizadas con PHPUnit

## Proyecto: GESTAD - Sistema de Gestión de Asistencia RFID

---

## Estudio Previo Requerido

### ¿Qué funcionalidad crítica existe en su proyecto?

1. **Validación de Identidad**: Validación de cédulas colombianas es crítica para la seguridad del sistema
2. **Registro de Asistencia**: Funcionalidad principal del sistema - marcar asistencia con RFID
3. **Gestión de Usuarios**: Creación, autenticación y gestión de roles (admin, docente, superadmin)
4. **Control de Horarios**: Verificación de horarios activos para determinar estado de asistencia
5. **API RFID**: Endpoint que recibe UIDs y procesa asistencia en tiempo real

### ¿Qué puede fallar con más impacto?

1. **Validación de Cédulas**: Si falla, se pueden registrar usuarios con identidades inválidas
2. **Endpoint RFID**: Si falla, el sistema no registra asistencia en tiempo real
3. **Lógica de Estados de Asistencia**: Si falla, se pueden marcar asistencias incorrectamente (Presente vs Tarde vs Ausente)
4. **Autenticación**: Si falla, usuarios no autorizados pueden acceder al sistema
5. **Duplicación de Registros**: Si falla, se pueden crear múltiples registros de asistencia en un mismo día

### ¿Qué flujo de usuario es esencial?

1. **Flujo de Login**: Usuario ingresa credenciales → Sistema valida → Redirige a dashboard según rol
2. **Flujo de Asistencia RFID**: Docente pasa tarjeta → RFID lee UID → API recibe UID → Sistema valida y registra → Retorna estado
3. **Flujo de Gestión de Docentes**: Admin crea usuario → Asigna tarjeta RFID → Configura horarios → Sistema valida y guarda

### ¿Dónde está el mayor riesgo de regresión?

1. **AttendanceModel::marcarAsistencia()**: Función central que maneja lógica compleja (validación de día, horario, estado, duplicados)
2. **ColombiaValidator::validarCedula()**: Validación crítica de identidad usada en múltiples puntos
3. **Endpoint /api/rfid_receiver.php**: Punto de entrada externo que debe manejar errores correctamente
4. **Sistema de Login**: Cambios en autenticación pueden afectar acceso de todos los usuarios
5. **Cálculo de Estados de Asistencia**: Lógica de tiempo que puede fallar con cambios de horario

---

## Tecnologías Utilizadas

- **PHP 8.2**
- **PHPUnit 10.5.63**
- **Guzzle HTTP Client** (para pruebas E2E)
- **MySQL** (base de datos de pruebas)
- **Composer** (gestión de dependencias)

---

## Estructura de Pruebas

```
tests/
├── Unit/                    # Pruebas unitarias
│   ├── ColombiaValidatorTest.php
│   └── AttendanceStatusTest.php
├── Integration/             # Pruebas de integración
│   ├── UserModelTest.php
│   └── AttendanceModelTest.php
├── E2E/                     # Pruebas end-to-end
│   ├── RfidReceiverTest.php
│   └── LoginFlowTest.php
└── fixtures/                # Datos de prueba
    └── schema.sql
```

---

## Configuración

### phpunit.xml
- Configuración de suites separadas (Unit, Integration, E2E)
- Variables de entorno para base de datos de pruebas
- Bootstrap con autoload de Composer

### Base de Datos de Pruebas
- Nombre: `rfid_system_test`
- Schema en `tests/fixtures/schema.sql`
- Limpieza automática de datos después de cada test

---

## Pruebas Unitarias (14 tests, 21 assertions)

### ColombiaValidatorTest
Validación de cédulas colombianas:
- ✅ Cédulas válidas de 10 dígitos
- ✅ Cédulas inválidas (menos de 10 dígitos)
- ✅ Cédulas con letras
- ✅ Cédulas con caracteres especiales

### AttendanceStatusTest
Lógica de estados de asistencia:
- ✅ Estado "Presente" (dentro del horario)
- ✅ Estado "Tarde" (fuera del horario pero dentro del margen)
- ✅ Estado "Ausente" (fuera del margen permitido)

---

## Pruebas de Integración (11 tests, 20 assertions)

### UserModelTest
Operaciones CRUD de usuarios:
- ✅ Creación de usuarios
- ✅ Búsqueda por username
- ✅ Asignación de tarjeta RFID
- ✅ Búsqueda por UID
- ✅ Búsqueda por cédula
- ✅ Actualización de usuarios
- ✅ Desactivación de usuarios

### AttendanceModelTest
Registro de asistencia:
- ✅ Marcar asistencia con UID válido
- ✅ Manejo de UID inválido
- ✅ Prevención de duplicados
- ✅ Comportamiento en fines de semana
- ✅ Consulta por rango de fechas

---

## Pruebas E2E (9 tests, 20 assertions)

### RfidReceiverTest
Endpoint API de RFID:
- ✅ POST con UID válido → "OK"
- ✅ POST con UID inválido → mensaje de error
- ✅ POST sin UID → "UID requerido"
- ✅ Verificación de registro en base de datos

### LoginFlowTest
Flujo de autenticación:
- ✅ Login con credenciales correctas → redirect a dashboard
- ✅ Login con credenciales incorrectas → redirect a login
- ✅ Login con credenciales vacías → redirect a login
- ✅ Acceso a página de login → 200 OK
- ✅ Acceso a dashboard sin login → redirect a login

---

## Resultados Finales

```
Unitarias:       14/14 pasadas ✓ (21 assertions)
Integración:     11/11 pasadas ✓ (20 assertions)
E2E:             9/9 pasadas   ✓ (20 assertions)
─────────────────────────────────────────────
Total:           34/34 pasadas ✓ (70 assertions)
```

---

## Comandos de Ejecución

### Pruebas Unitarias
```bash
vendor/bin/phpunit --testsuite Unit
```

### Pruebas de Integración
```bash
vendor/bin/phpunit --testsuite Integration
```

### Pruebas E2E
```bash
# Terminal 1: Iniciar servidor
php -S localhost:8000 -t public

# Terminal 2: Ejecutar pruebas
vendor/bin/phpunit --testsuite E2E
```

### Todas las Pruebas
```bash
vendor/bin/phpunit
```

---

## Mejoras Implementadas

1. **Corrección de rutas E2E**: Eliminado prefijo `/public/` de las URLs
2. **Manejo de errores en API**: Endpoint RFID devuelve mensajes de error específicos
3. **Eliminación de saltos por día de semana**: Tests usan lunes como día fijo
4. **Corrección de warnings PHP**: Validación de `$_SESSION['user']` en vistas
5. **Limpieza de archivos basura**: Eliminados logs y caché innecesarios

---

## Justificación de Pruebas

### ¿Por qué elegimos estas pruebas y no otras?

**Pruebas Unitarias:**
- **ColombiaValidatorTest**: Validación de identidad es crítica y aislada, ideal para unit testing
- **AttendanceStatusTest**: Lógica de cálculo de estados es pura lógica sin dependencias externas

**Pruebas de Integración:**
- **UserModelTest**: Operaciones CRUD de usuarios interactúan con base de datos, requieren integración
- **AttendanceModelTest**: Registro de asistencia depende de múltiples modelos (User, Schedule, Notification)

**Pruebas E2E:**
- **RfidReceiverTest**: Endpoint API es punto de entrada externo, requiere testing completo del flujo HTTP
- **LoginFlowTest**: Flujo de autenticación es esencial para el sistema, requiere testing de navegador

### ¿Qué riesgo cubre cada prueba?

**Unitarias:**
- `testValidCedulaWith10Digits`: Riesgo de aceptar cédulas inválidas → seguridad comprometida
- `testInvalidCedulaTooShort`: Riesgo de rechazar cédulas válidas → usuarios bloqueados
- `testInvalidCedulaWithLetters`: Riesgo de inyección de caracteres no numéricos
- `testStatusPresente`: Riesgo de marcar incorrectamente asistencia dentro de horario
- `testStatusTarde`: Riesgo de no detectar llegadas tardías
- `testStatusAusente`: Riesgo de no marcar ausencias cuando corresponde

**Integración:**
- `testCreateUser`: Riesgo de fallo en creación de usuarios → sistema inoperativo
- `testFindByUsername`: Riesgo de no encontrar usuarios existentes → login falla
- `testAssignCard`: Riesgo de no asignar tarjeta RFID → asistencia no funciona
- `testMarcarAsistencia`: Riesgo de fallo en registro de asistencia → función principal falla
- `testPreventsDuplicate`: Riesgo de registros duplicados → datos inconsistentes
- `testWeekendAttendance`: Riesgo de registrar asistencia en días no laborales

**E2E:**
- `testPostValidUIDReturnsOK`: Riesgo de endpoint no procesar UIDs válidos → sistema no funciona
- `testPostInvalidUIDReturnsError`: Riesgo de aceptar UIDs inválidos → seguridad comprometida
- `testLoginWithCorrectCredentials`: Riesgo de fallo en autenticación → nadie puede acceder
- `testLoginWithIncorrectCredentials`: Riesgo de permitir acceso no autorizado → seguridad comprometida

### ¿Por qué es unitaria y no de integración, o viceversa?

**ColombiaValidatorTest es Unitaria porque:**
- No depende de base de datos
- No depende de otros modelos
- Lógica pura de validación de strings
- Rápida de ejecutar
- Fácil de aislar

**AttendanceStatusTest es Unitaria porque:**
- Aísla la lógica de cálculo de estados
- No requiere conexión a base de datos
- Prueba algoritmo de tiempo de forma pura
- Independiente de horarios reales

**UserModelTest es de Integración porque:**
- Requiere conexión a base de datos real
- Prueba interacción con PDO
- Verifica persistencia de datos
- Depende de esquema de base de datos

**AttendanceModelTest es de Integración porque:**
- Interactúa con múltiples modelos (User, Schedule, Notification)
- Requiere base de datos con relaciones
- Prueba transacciones y consistencia
- Depende de datos de horarios y usuarios

**RfidReceiverTest es E2E porque:**
- Prueba endpoint HTTP completo
- Requiere servidor PHP corriendo
- Verifica flujo completo desde HTTP hasta base de datos
- Simula interacción real con sistema externo

**LoginFlowTest es E2E porque:**
- Prueba flujo de autenticación completo
- Verifica redirecciones HTTP
- Requiere sesión y cookies
- Simula interacción de usuario real

### ¿Qué descubrimos al escribirlas? ¿Encontramos bugs?

**Bugs Encontrados y Corregidos:**

1. **Endpoint RFID no validaba resultado de marcarAsistencia()**
   - **Bug**: Siempre devolvía "OK" incluso cuando fallaba
   - **Impacto**: No se informaban errores de UID inválido o sin horario
   - **Solución**: Agregar verificación de resultado y devolver mensaje de error específico

2. **Warnings PHP en header.php y dashboard.php**
   - **Bug**: Acceso a `$_SESSION['user']` sin verificar si existe
   - **Impacto**: Warnings en logs y posibles errores en producción
   - **Solución**: Agregar `isset()` antes de acceder a variables de sesión

3. **Tests se saltaban en fines de semana**
   - **Bug**: Validación de día de la semana impedía ejecución de tests
   - **Impacto**: Tests no ejecutables 2 días a la semana
   - **Solución**: Usar día fijo (lunes) para pruebas, insertar registros manualmente

4. **Rutas E2E incorrectas**
   - **Bug**: Tests usaban `/public/` como prefijo cuando servidor ya servía desde `public`
   - **Impacto**: Tests E2E fallaban con 404
   - **Solución**: Eliminar prefijo `/public/` de las rutas

### ¿Cómo las ejecutan? ¿Están en el README?

**Comandos de Ejecución (documentados en README.md):**

```bash
# Pruebas Unitarias
vendor/bin/phpunit --testsuite Unit

# Pruebas de Integración
vendor/bin/phpunit --testsuite Integration

# Pruebas E2E (requiere servidor)
# Terminal 1:
php -S localhost:8000 -t public
# Terminal 2:
vendor/bin/phpunit --testsuite E2E

# Todas las pruebas
vendor/bin/phpunit
```

**Documentación en README.md incluye:**
- Configuración de base de datos de pruebas
- Instalación de dependencias con Composer
- Comandos específicos para Windows/XAMPP
- Instrucciones para importar schema SQL
- Explicación de suites de pruebas
- Manejo de errores comunes

---

## Conclusiones

- ✅ Suite de pruebas completa y funcional
- ✅ Cobertura de funcionalidades críticas
- ✅ Tests independientes del día de la semana
- ✅ Limpieza automática de datos de prueba
- ✅ Documentación completa en README
- ✅ Código de producción mejorado (corrección de warnings)
- ✅ 4 bugs encontrados y corregidos durante el proceso

---

## Próximos Pasos Sugeridos

1. Aumentar cobertura de código con más tests unitarios
2. Implementar tests de rendimiento
3. Agregar tests de seguridad
4. Configurar CI/CD para ejecución automática de tests
5. Implementar mocking de dependencias externas

---

**Fecha de implementación:** Mayo 24, 2026
**Desarrollador:** JuanchoSA
**Estado:** ✅ Completado
