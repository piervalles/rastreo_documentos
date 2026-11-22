# DocuTrace - Sistema de Rastreo y Trazabilidad Documental Física

Sistema web para el seguimiento y control de expedientes físicos en red local (LAN) o en la nube, desarrollado en **PHP Nativo Moderno (Arquitectura MVC)** y **MySQL**.

---

## 🌟 Características Principales
- **Zero-File Storage:** Diseñado exclusivamente para el control de custodia y trazabilidad de documentos físicos de papel (sin carga de archivos pesados).
- **Instalador Code-First de 1 Clic (`instalar.php`):** La base de datos, tablas, relaciones y datos iniciales se crean automáticamente desde código PHP sin necesidad de configurar phpMyAdmin a mano.
- **Control de Concurrencia Optimista (OCC):** Prevención estricta de colisiones cuando múltiples secretarias o técnicos visualizan y derivan el mismo documento al mismo tiempo.
- **Bitácora Inmutable Transaccional (ACID):** Historial permanente de movimientos (origen, destino, usuario, acciones, sellos y firmas físicas comprobadas).
- **Doble Dimensión de Estados:**
  - *Estado de Trámite (Operativo):* Situación física en la oficina (*Recibido, En traslado, En revisión, Respondido, Archivado*).
  - *Estado de Resolución (Dictamen):* Fondo del expediente (*Pendiente, Observado, Con Visto Bueno, Autorizado, Rechazado*).
- **Respaldos de 1 Clic:** Descarga directa del volcado completo `.sql` desde el panel de administración.
- **Portabilidad Total:** Funciona en XAMPP simplemente copiando la carpeta a `htdocs`, y está 100% preparado para la nube con Docker.

---

## 🚀 Puesta en Marcha Rápida (Local con XAMPP)

1. **Copiar la carpeta:**
   Pega esta carpeta en tu directorio de XAMPP:
   ```
   C:\xampp\htdocs\rastreo_doc
   ```
2. **Iniciar XAMPP:**
   Abre el panel de control de XAMPP y presiona **Start** en **Apache** y **MySQL**.
3. **Instalación Inicial (Code-First):**
   Abre tu navegador e ingresa a:
   ```
   http://localhost/rastreo_doc/instalar.php
   ```
   Presiona el botón **"Inicializar Base de Datos (1 Clic)"**.
4. **Acceso al Sistema:**
   - **Administrador:** Usuario: `admin` | Contraseña: `admin123`
   - **Operador (Mesa de Partes):** Usuario: `mesapartes` | Contraseña: `usuario123`

---

## 🌐 Conexión desde Otras Computadoras en el Edificio (Red Local)

No se requiere instalar nada en las demás computadoras:
1. Abre la consola en la PC servidora y escribe `ipconfig` para conocer su IP local (ej. `192.168.1.50`).
2. Desde cualquier otra computadora del edificio conectada a la misma red, abre el navegador e ingresa a:
   ```
   http://192.168.1.50/rastreo_doc
   ```
   *(O mediante el nombre del equipo: `http://SERVIDOR-DOCS/rastreo_doc`)*.

---

## ☁️ Preparación para la Nube (Docker)

Para desplegar en cualquier servidor en la nube con Docker Compose:
```bash
docker compose up -d --build
```
El sistema quedará disponible en el puerto `8080` conectado a su propia base de datos MySQL aislada.
