# GESTAD - Sistema de Control de Asistencia RFID

## Descripción del proyecto

GESTAD es un sistema web desarrollado para la gestión y control de asistencia mediante tecnología RFID. El proyecto permite administrar usuarios, horarios, reportes y registros de asistencia de manera automatizada.

El sistema cuenta con autenticación de usuarios, panel administrativo, gestión de docentes y administradores, control de horarios y generación de reportes. Además, integra módulos para recepción y procesamiento de tarjetas RFID.

---

# Integrantes del grupo

| Nombre         | GitHub       |
| -------------- | -------------|
| Juan Sánchez   | @JuanchoSA   |
| Camilo Cañas   | @Cammy06     |
| Wilmer Beltrán | @Alex020825  |


---

# Tecnologías usadas (Stack)

## Backend

* PHP
* Arquitectura MVC
* PDO para conexión a base de datos

## Base de datos

* MySQL

## Frontend

* HTML5
* CSS3
* JavaScript

## Librerías y dependencias

* TCPDF (Generación de reportes PDF)
* Composer

## Otros

* Integración con RFID
* Variables de entorno mediante archivo `.env`

---

# Instrucciones para correr el proyecto localmente

## 1. Clonar el repositorio

```bash
git clone https://github.com/GESTAD-SA/gestad.git
```

## 2. Entrar al proyecto

```bash
cd gestad
```

## 3. Instalar dependencias

```bash
composer install
```

## 4. Configurar variables de entorno

Crear o editar el archivo `.env` con los datos de la base de datos:

```env
DB_HOST=127.0.0.1
DB_NAME=rfid_system
DB_USER=root
DB_PASS=
```

> Importante: No subir credenciales reales al repositorio.

## 5. Importar la base de datos

* Crear una base de datos en MySQL llamada `rfid_system`
* Importar el archivo `.sql` correspondiente si está disponible.

## 6. Ejecutar el proyecto

Colocar el proyecto en el servidor local:

### XAMPP

Mover la carpeta al directorio:

```bash
htdocs/
```

Luego iniciar:

* Apache
* MySQL

Abrir en el navegador:

```text
http://localhost/gestad/public/
```

---

# Estado actual del MVP

## Funcionalidades implementadas

* Inicio de sesión de usuarios
* Gestión de administradores y docentes
* Registro de asistencia
* Integración RFID
* Gestión de horarios
* Generación de reportes
* Notificaciones
* Panel administrativo

## Estado del proyecto

El MVP se encuentra funcional en su estructura principal. Actualmente permite gestionar usuarios, horarios y asistencias mediante RFID. Algunas funcionalidades pueden seguir en proceso de mejora, optimización y pruebas.

---

# Estructura general del proyecto

```text
app/
 ├── controllers/
 ├── models/
 ├── views/
 └── utils/

public/
 ├── api/
 ├── assets/
 ├── dev/
 └── index.php

rfid/
vendor/
```

---

# Recomendaciones

* Usar PHP 8 o superior.
* Configurar correctamente Apache y MySQL.
* Mantener el archivo `.env` fuera del repositorio público.
* Ejecutar `composer install` después de clonar el proyecto.
