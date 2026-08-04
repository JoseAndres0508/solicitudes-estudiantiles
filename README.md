# Módulo de Solicitudes Estudiantiles

Portal de solicitudes, motor de reglas y bandeja centralizada.
Proyecto 4 — ISW-521 Programación en Ambiente Web I — UTN Sede San Carlos.

Sistema que permite a un estudiante presentar solicitudes de levantamiento de requisito y de convalidación de cursos externos, evalúa automáticamente los casos con criterios objetivos mediante un motor de reglas configurable, y centraliza en una bandeja única los casos que requieren criterio humano.

---

## Índice

1. [Antes de empezar](#1-antes-de-empezar)
2. [Requisitos del sistema](#2-requisitos-del-sistema)
3. [Instalación desde cero](#3-instalación-desde-cero)
4. [Puesta en marcha del proyecto](#4-puesta-en-marcha-del-proyecto)
5. [Comandos de uso diario](#5-comandos-de-uso-diario)
6. [Convenciones de Git](#6-convenciones-de-git)
7. [Estructura del proyecto](#7-estructura-del-proyecto)
8. [Solución de problemas](#8-solución-de-problemas)

---

## 1. Antes de empezar

Tres cosas que conviene saber antes del primer comando. Las dos primeras ya nos costaron horas.

### Laravel no se instala por separado

No busques un instalador de Laravel. El framework es una dependencia del proyecto y llega solo cuando ejecutas `composer install` en el paso 4.2. Lo que sí hay que instalar en la máquina es PHP, Composer, Node.js, MySQL y Git.

### No trabajes dentro de OneDrive, Dropbox o Google Drive

Laravel genera decenas de miles de archivos pequeños en `vendor/` y `node_modules/`. Los servicios de sincronización los suben uno por uno y bloquean archivos mientras se escriben, lo que produce errores intermitentes muy difíciles de diagnosticar.

Ubica el proyecto en una ruta local simple:

```
C:\dev\solicitudes-estudiantiles
```

El respaldo lo da Git, no OneDrive.

### Composer debe ser versión 2.8.x

Las versiones 2.9 y 2.10 tienen un bug en Windows ([composer/composer#12615](https://github.com/composer/composer/issues/12615)) que impide instalar dependencias. El error se ve así:

```
Invalid package found during dependency resolution, aborting: lib-curl-schannel
zlib version => 1.3.1
libssh version => libssh2 is invalid...
```

Ocurre porque Composer no interpreta correctamente el backend SSL de Windows (Schannel). No es un problema de tu máquina y no se arregla reinstalando PHP.

**Cualquier instalador actual te va a dar una versión afectada**, así que el paso 3.4 de este README es obligatorio, no opcional.

---

## 2. Requisitos del sistema

| Componente | Versión | Verificar con |
|---|---|---|
| Git | Cualquiera reciente | `git --version` |
| PHP | 8.3 o superior | `php -v` |
| Composer | **2.8.x** (no 2.9 ni 2.10) | `composer -V` |
| Node.js | 20 o superior | `node -v` |
| MySQL Server | 8.0 o superior | Servicio `MySQL80` en ejecución |

Extensiones de PHP necesarias: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`. Las instalaciones modernas de PHP para Windows las traen activas por defecto.

Este README asume **Windows** y usa **Git Bash** como terminal. Los comandos que requieren PowerShell están señalados de forma explícita.

---

## 3. Instalación desde cero

Sigue las secciones en orden. Cada una termina con una verificación: si falla, no avances hasta resolverla.

> **Regla general:** después de instalar cualquier herramienta, **cierra todas las terminales abiertas y abre una nueva**. Las terminales leen el PATH al arrancar y no se enteran de los cambios posteriores. La mayoría de los "no funciona, ya lo instalé" se resuelven así.

### 3.1 Git

Va primero porque sin él no puedes clonar el repositorio, y porque instala **Git Bash**, la terminal que usan todos los comandos de este documento.

Descarga desde [git-scm.com/download/win](https://git-scm.com/download/win). El instalador ofrece muchas pantallas; **acepta los valores por defecto en todas**. Los únicos que importan ya vienen bien configurados.

Verifica abriendo **Git Bash** desde el menú Inicio:

```bash
git --version
```

Debe responder algo como `git version 2.47.0`.

Configura tu identidad, que es lo que aparecerá en cada commit:

```bash
git config --global user.name "Tu Nombre"
git config --global user.email "tu@correo.com"
git config --global init.defaultBranch main
```

Usa el correo asociado a tu cuenta de GitHub para que los commits se vinculen a tu perfil.

### 3.2 PHP y Composer

La forma más rápida en Windows es **Laravel Herd**, que instala ambos y los agrega al PATH automáticamente.

Descarga desde [herd.laravel.com/windows](https://herd.laravel.com/windows), instala y **reinicia Git Bash**.

Verifica:

```bash
php -v
composer -V
```

`php -v` debe reportar 8.3 o superior. `composer -V` debe reportar un número de versión y la ruta al PHP que está usando.

<details>
<summary>Alternativa sin Herd</summary>

Descarga PHP desde [windows.php.net/download](https://windows.php.net/download/) (versión Thread Safe, x64), descomprime en `C:\php`, copia `php.ini-development` a `php.ini` y descomenta las extensiones necesarias quitando el `;` inicial. Luego agrega `C:\php` al PATH de Windows.

Composer se instala aparte desde [getcomposer.org/download](https://getcomposer.org/download/).

Este camino es más laborioso y propenso a errores de configuración. Herd es preferible salvo que tengas una razón concreta.

</details>

### 3.3 Verificar las extensiones de PHP

```bash
php -m | grep -E "pdo_mysql|mbstring|openssl|fileinfo|curl|zip"
```

Deben aparecer las seis. Si falta alguna con Herd instalado, avisa al equipo antes de continuar: sin `pdo_mysql` el proyecto no conecta a la base de datos.

### 3.4 Ajustar la versión de Composer

**Este paso es obligatorio.** Cualquier instalación reciente trae una versión con el bug descrito en la sección 1.

Comprueba:

```bash
composer -V
```

Si dice **2.9.x** o **2.10.x**, abre **PowerShell como administrador** (clic derecho en el menú Inicio → Terminal (Administrador)) y ejecuta:

```powershell
composer self-update 2.8.12
```

Se requiere administrador porque Composer suele instalarse en una carpeta protegida del sistema. Si lo ejecutas sin privilegios, Composer detecta la situación y ofrece elevarse: responde `Y`.

Verifica de nuevo:

```bash
composer -V
```

Debe decir `Composer version 2.8.12`.

> Para volver a la versión anterior en el futuro: `composer self-update --rollback`

### 3.5 Node.js

Descarga la versión **LTS** desde [nodejs.org](https://nodejs.org/). Acepta los valores por defecto del instalador.

Reinicia Git Bash y verifica:

```bash
node -v
npm -v
```

`node -v` debe reportar 20 o superior.

### 3.6 MySQL Server

Descarga el instalador desde [dev.mysql.com/downloads/installer](https://dev.mysql.com/downloads/installer/). Elige el archivo **completo** (~300 MB), no el de descarga en línea, que falla si la conexión se interrumpe.

En la página de descarga aparece "Login / Sign Up"; abajo hay un enlace pequeño que dice *"No thanks, just start my download"*. No necesitas cuenta.

Durante la instalación:

| Pantalla | Opción |
|---|---|
| Choosing a Setup Type | **Custom** |
| Select Products | Solo **MySQL Server** y **MySQL Workbench** |
| Type and Networking | Development Computer, puerto **3306** |
| Authentication Method | **Strong Password Encryption** (la primera) |
| Accounts and Roles | Define la contraseña de root — **anótala** |
| Windows Service | Nombre `MySQL80`, marcar "Start at System Startup" |

No instales el paquete completo: MySQL Shell, Router y los conectores para .NET o Python no se usan en este proyecto y solo ocupan espacio.

**Anota la contraseña de root en un lugar seguro.** La vas a necesitar en el archivo `.env` y no hay forma cómoda de recuperarla.

> Si ya tenías MySQL Workbench instalado de antes, **eso no incluye el servidor**. Workbench es solo el cliente gráfico. Abre el instalador, elige **Add** y agrega únicamente `MySQL Server`. No uses las opciones Remove ni Upgrade sobre una instalación existente.

### 3.7 Verificar que el servidor esté corriendo

En **PowerShell**:

```powershell
Get-Service *MySQL*
```

Debe aparecer `MySQL80` con estado `Running`. Si dice `Stopped`:

```powershell
Start-Service MySQL80
```

Si no aparece ningún servicio, el servidor no se instaló correctamente. Vuelve al paso 3.6.

### 3.8 Crear la base de datos

Abre **MySQL Workbench** y crea una conexión nueva con el botón `+` junto a "MySQL Connections":

| Campo | Valor |
|---|---|
| Connection Name | `Local MySQL 8.0` |
| Connection Method | Standard (TCP/IP) |
| Hostname | `127.0.0.1` |
| Port | `3306` |
| Username | `root` |

Pulsa **Test Connection** antes de guardar. Debe responder que la conexión fue exitosa.

> Si ves una conexión preexistente llamada "Docker MySQL" u otra similar, **no la uses**. Apunta a un servidor distinto, normalmente en otro puerto. Si creas la base ahí, Laravel no la encontrará y el error resultante es difícil de rastrear.

Conéctate y ejecuta en la ventana de consulta:

```sql
CREATE DATABASE utn_academic_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

El nombre no es arbitrario: es el que define el estándar institucional de base de datos del curso, común a los cinco módulos. Si más adelante los módulos se consolidan, la base ya se llama como corresponde.

La codificación `utf8mb4` es obligatoria: el esquema guarda nombres de cursos, instituciones y valores `ENUM` con tildes (`'Convalidación'`, `'Pendiente de revisión'`). Corregirla después implica migrar la base completa.

Verifica:

```sql
SHOW DATABASES;
```

`utn_academic_management` debe aparecer en la lista.

### 3.9 Editor de código

No es un requisito técnico, pero facilita bastante el trabajo. **Visual Studio Code** desde [code.visualstudio.com](https://code.visualstudio.com/).

Extensiones útiles para este proyecto: PHP Intelephense, Laravel Blade Snippets, Tailwind CSS IntelliSense.

### 3.10 Verificación final

Antes de clonar el repositorio, los cinco comandos deben responder correctamente en **una terminal recién abierta**:

```bash
git --version      # cualquier versión reciente
php -v             # 8.3 o superior
composer -V        # 2.8.x
node -v            # 20 o superior
```

Y en **PowerShell**:

```powershell
Get-Service MySQL80    # Status: Running
```

Si los cinco pasan, continúa. Si alguno falla, resuélvelo antes: los errores de la sección 4 casi siempre vienen de un requisito mal instalado aquí.

---

## 4. Puesta en marcha del proyecto

### 4.1 Clonar el repositorio

```bash
mkdir -p /c/dev && cd /c/dev
git clone https://github.com/JoseAndres0508/solicitudes-estudiantiles.git
cd solicitudes-estudiantiles
```

Si el repositorio es privado, Git pedirá autenticación. GitHub **no acepta tu contraseña de la cuenta** desde 2021:

- Si se abre una ventana del navegador pidiendo autorizar, acepta y listo.
- Si pide usuario y contraseña en la terminal, en "Password" va un **Personal Access Token**, que se genera en GitHub bajo `Settings → Developer settings → Personal access tokens → Tokens (classic)`, con el permiso `repo` marcado. Cópialo apenas se genere: GitHub no lo muestra de nuevo.

### 4.2 Instalar dependencias de PHP

```bash
composer install
```

Aquí es donde llega Laravel y todo lo demás, a la carpeta `vendor/`, que no viaja en el repositorio porque contiene decenas de miles de archivos. Tarda entre uno y tres minutos.

### 4.3 Instalar dependencias de JavaScript

```bash
npm install
```

### 4.4 Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

El archivo `.env` guarda credenciales y **nunca se sube al repositorio**. Ábrelo en el editor y ajusta el bloque de base de datos con **tu propia** contraseña de root:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=utn_academic_management
DB_USERNAME=root
DB_PASSWORD=tu_contraseña
```

Si tu contraseña contiene `#`, `$` o espacios, enciérrala en comillas dobles:

```env
DB_PASSWORD="mi#clave"
```

Confirma también el bloque de idioma, que debe quedar así:

```env
APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_ES
```

El *fallback* queda en inglés a propósito: si falta una traducción, se muestra el texto en inglés en lugar de la clave cruda. Faker no tiene locale `es_CR`; `es_ES` es el más completo de los disponibles en español.

> **Zona horaria.** No se configura en `.env`. Está fijada en `config/app.php` como `'timezone' => 'America/Costa_Rica'`, porque no cambia entre entornos: es un dato del dominio, no de la máquina. Importa más de lo que parece — el requerimiento ES-03 mide 24 horas y 5 días hábiles, y con UTC el vencimiento cae el día equivocado.

### 4.5 Crear las tablas y cargar datos de prueba

```bash
php artisan migrate --seed
```

Crea las 24 migraciones y carga los datos de demostración: el RBAC completo, las 14 carreras, dos planes de estudio con su malla y requisitos, cuatro estudiantes con expediente académico simulado, las reglas de levantamiento, el catálogo de precedentes y solicitudes de ambos tipos.

**Los datos no son de relleno.** Cada estudiante recorre un camino distinto del motor de reglas, para que cada criterio de aceptación sea demostrable:

| Cuenta | Contraseña | Situación | Qué demuestra |
|---|---|---|---|
| `ana@utn.ac.cr` | `password` | ITI-302 con nota 92 | La regla de nota mínima se cumple → procede |
| `bruno@utn.ac.cr` | `password` | ITI-302 con 71, 15 créditos | Ninguna regla concluye → no procede |
| `carla@utn.ac.cr` | `password` | Matriculada en un plan Terminal | Procede por la regla de plan terminal |
| `diego@utn.ac.cr` | `password` | Ya tiene el requisito levantado | Caso de solicitud duplicada |
| `docencia@utn.ac.cr` | `password` | Revisora | Bandeja centralizada (ES-04) |
| `comision@utn.ac.cr` | `password` | Comisión Técnica | Resolución de convalidaciones (ES-02) |

El curso `ITI-501` quedó **deliberadamente sin reglas configuradas**: es el caso que debe elevarse solo a revisión manual.

> Las reglas de levantamiento son **filas de la tabla `waiver_rules`**, no código. El seeder solo precarga algunas para que la demostración tenga contenido; si lo borras, el sistema funciona igual y toda solicitud se eleva a revisión manual.

### 4.6 Levantar la aplicación

Necesitas **dos terminales abiertas al mismo tiempo**.

Terminal 1 — servidor de PHP:

```bash
php artisan serve
```

Terminal 2 — compilador de assets:

```bash
npm run dev
```

Abre <http://127.0.0.1:8000> en el navegador.

Para detener cualquiera de los dos: `Ctrl + C`.

### 4.7 Verificar

```bash
php artisan migrate:status
```

Las 24 migraciones deben aparecer como `Ran`.

Comprueba además que los datos entraron:

```bash
php artisan tinker --execute="
  echo 'programs: '.App\Models\DegreeProgram::count().PHP_EOL;
  echo 'courses: '.App\Models\Course::count().PHP_EOL;
  echo 'students: '.App\Models\Student::count().PHP_EOL;
  echo 'rules: '.App\Models\WaiverRule::count().PHP_EOL;
  echo 'requests: '.App\Models\StudentRequest::count().PHP_EOL;
"
```

Debe reportar 14 programas, 10 cursos, 4 estudiantes, 6 reglas y 6 solicitudes. Si algún conteo da 0, el seeder correspondiente falló: revisa la salida de `php artisan db:seed`.

---

## 5. Comandos de uso diario

| Comando | Para qué |
|---|---|
| `php artisan serve` | Levanta el servidor local |
| `npm run dev` | Compila CSS y JS en modo desarrollo |
| `php artisan migrate` | Aplica migraciones nuevas |
| `php artisan migrate:fresh --seed` | Borra todo y reconstruye con datos limpios |
| `php artisan tinker` | Consola interactiva para probar modelos |
| `php artisan test` | Ejecuta las pruebas |
| `php artisan route:list` | Lista todas las rutas registradas |

### Después de cada `git pull`

Si alguien agregó dependencias o migraciones:

```bash
composer install
npm install
php artisan migrate
```

### Cuando algo se comporta raro sin explicación

```bash
php artisan optimize:clear
```

Limpia cachés de configuración, rutas y vistas. Resuelve una cantidad sorprendente de problemas aparentemente misteriosos.

---

## 6. Convenciones de Git

### Reglas obligatorias

1. **Conventional Commits** en todos los mensajes.
2. **Inglés** en mensajes de commit, nombres de ramas y todo el código.
3. **Nunca subir `.env`** — ya está en `.gitignore`, no lo saques de ahí.
4. **Un commit por cambio lógico.** No acumular una jornada entera en un solo commit.

### Formato de los mensajes

```
type(scope): description
```

Descripción en imperativo y minúscula (`add`, no `Added` ni `Adds`), sin punto final.

**Tipos:**

| Tipo | Cuándo usarlo |
|---|---|
| `feat` | Funcionalidad nueva |
| `fix` | Corrección de un error |
| `refactor` | Cambio de código sin alterar el comportamiento |
| `test` | Pruebas |
| `docs` | Documentación, README, diario |
| `chore` | Dependencias, configuración, herramientas |
| `style` | Formato, sin cambio de lógica |

**Scopes del proyecto:**

`domain`, `rules-engine`, `requests`, `validations`, `tracking`, `inbox`, `auth`, `api`, `ui`, `db`, `deps`

**Ejemplos:**

```
feat(rules-engine): add minimum grade rule evaluator
feat(requests): detect duplicate waiver requests before evaluation
fix(tracking): assign default deadline after 24h without reviewer input
test(rules-engine): cover ordered evaluation of configured rules
refactor(domain): extract rule contract into interface
chore(deps): add jwt authentication package
docs: add AI decision log entries for week 10
```

### Flujo de trabajo

```bash
# 1. Traer los cambios del equipo antes de empezar
git pull

# 2. Crear una rama para lo que vas a hacer
git checkout -b feat/rules-engine-base

# 3. Trabajar y commitear en pasos pequeños
git add app/Models/WaiverRule.php
git commit -m "feat(rules-engine): add waiver rule evaluator for minimum grade"

# 4. Subir la rama
git push -u origin feat/rules-engine-base

# 5. Abrir un Pull Request en GitHub para que el otro lo revise
```

**Nombres de rama:** `type/short-description` en inglés y con guiones.

```
feat/rules-engine-base
fix/duplicate-detection
docs/readme-setup
```

### Antes de cada commit

```bash
git status
git diff
```

Revisa qué estás subiendo. Si aparece `.env`, algo está mal configurado — detente y avisa antes de continuar.

### Verificar que `.env` está protegido

```bash
git check-ignore -v .env
```

Debe responder con la línea del `.gitignore` que lo excluye. Si no responde nada, **no hagas push** hasta resolverlo.

---

## 7. Estructura del proyecto

```
solicitudes-estudiantiles/
├── app/
│   ├── Models/              Modelos Eloquent (18 entidades, en inglés)
│   ├── Http/                Controladores y middleware
│   └── Livewire/            Componentes Livewire
├── database/
│   ├── migrations/          Esquema de la base de datos (24 archivos)
│   ├── factories/           Generadores de datos de prueba
│   └── seeders/             Carga de datos iniciales
├── docs/                    Diario de decisiones e IA + estándar SQL
├── resources/
│   ├── views/               Plantillas Blade
│   ├── css/                 Tailwind
│   └── js/                  TypeScript y Alpine
├── routes/
│   ├── web.php              Rutas de la interfaz
│   └── api.php              Rutas de la API (JWT)
├── tests/                   Pruebas unitarias y de integración
├── .env                     Configuración local (NO se sube)
└── .env.example             Plantilla de configuración (sí se sube)
```

### Modelo de datos

El esquema **no lo diseñó el equipo**: se transcribe del estándar institucional del curso, común a los cinco módulos. De sus 45 tablas se implementan las que el módulo necesita para funcionar.

| Grupo | Tablas |
|---|---|
| Autenticación y RBAC | `users`, `passkeys`, `roles`, `permissions`, `role_user`, `permission_role`, `permission_user` |
| Catálogo académico | `degree_programs`, `courses`, `academic_terms` |
| Repositorio curricular | `study_plans`, `levels`, `course_level`, `prerequisites`, `course_equivalences` |
| Estudiantes | `students`, `student_study_plan`, `academic_records` |
| **Solicitudes (módulo propio)** | `waiver_rules`, `transfer_credit_precedents`, `student_requests`, `student_request_status_history` |
| Documentos | `attachments` |

Tres decisiones del esquema que conviene entender antes de tocar código:

- **Los créditos viven en `course_level`, no en `courses`.** Un mismo curso puede valer 3 créditos en un plan y 4 en otro. La regla de "créditos acumulados ≥ K" se calcula uniendo `academic_records` con `course_level`, filtrando por el plan del estudiante.
- **`student_requests` es una sola tabla para ambos tipos.** Las columnas propias de cada tipo quedan nulables, protegidas por los CHECK `chk_student_requests_waiver_fields` y `chk_student_requests_transfer_fields`. Se prefirió eso a dividirla en subtablas porque la bandeja de ES-04 es la pantalla más usada y lista los dos tipos juntos: con una tabla es un índice, con dos sería un `UNION` en cada carga.
- **No existe una tabla de levantamientos otorgados.** Un levantamiento concedido ya queda como `student_requests` en estado `Aprobada` y como fila de `academic_records` con estado `Requisito levantado`. La detección de duplicados es una consulta previa en la capa de aplicación.

> **Idioma.** Por normativa institucional, tablas, columnas y clases van en **inglés**. Los **valores** de los `ENUM` y los nombres de rol se conservan en español porque son datos definidos por el estándar, no identificadores: `'Convalidación'`, `'Pendiente de revisión'`, `'Coordinadora de Docencia'`.

> **Nombres de tabla en los modelos.** Con los nombres en inglés el pluralizador de Laravel resuelve bien 17 de los 18 modelos. La excepción es `StudentRequestStatusHistory`, que resolvería `student_request_status_histories`, y por eso declara `#[Table('student_request_status_history')]`.

---

## 8. Solución de problemas

### `command not found` después de instalar algo

La terminal leyó el PATH al abrirse y no vio la instalación posterior. **Cierra todas las terminales y abre una nueva.** Si persiste tras reiniciar la máquina, la herramienta no se agregó al PATH durante la instalación.

### `Failed to open stream: vendor/autoload.php`

Falta la carpeta `vendor/`. No viaja en el repositorio porque contiene decenas de miles de archivos.

```bash
composer install
```

### `Invalid package found during dependency resolution: lib-curl-schannel`

Bug de Composer 2.9/2.10 en Windows. Baja a la serie 2.8 desde **PowerShell como administrador**:

```powershell
composer self-update 2.8.12
```

### `SQLSTATE[HY000] [1045] Access denied for user 'root'`

La contraseña de `DB_PASSWORD` en `.env` no coincide con la de MySQL. Revísala y, si tiene caracteres especiales, ponla entre comillas dobles.

### `SQLSTATE[HY000] [1049] Unknown database 'utn_academic_management'`

La base no existe todavía. Créala desde Workbench con el `CREATE DATABASE` de la sección 3.8. Verifica también que estés conectado a la conexión correcta y no a una de Docker.

### Las tildes se ven como `Ã³` o los `ENUM` rechazan valores

La base se creó sin `utf8mb4`. Varios `ENUM` del esquema llevan tildes (`'Convalidación'`, `'Pendiente de revisión'`) y fallan con otra codificación. Verifica:

```sql
SELECT default_character_set_name, default_collation_name
FROM information_schema.SCHEMATA
WHERE schema_name = 'utn_academic_management';
```

Debe decir `utf8mb4` y `utf8mb4_unicode_ci`. Si no, borra la base y vuelve a crearla con el `CREATE DATABASE` completo de la sección 3.8.

### Las fechas u horas no cuadran

Revisa que `config/app.php` tenga `'timezone' => 'America/Costa_Rica'`. Si estaba en `UTC`, corrígelo y reconstruye para que las marcas de tiempo sembradas queden consistentes:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
```

### `SQLSTATE[HY000] [2002] Connection refused`

El servicio de MySQL no está corriendo. En PowerShell:

```powershell
Start-Service MySQL80
```

### `could not find driver`

Falta la extensión `pdo_mysql` en PHP. Verifica con `php -m | grep pdo_mysql`. Si no aparece, la instalación de PHP está incompleta.

### `mysql: command not found`

El ejecutable no está en el PATH. **No es un problema real para este proyecto**: Laravel se conecta a MySQL a través de PHP, no del comando `mysql`. Usa Workbench para lo que necesites hacer a mano.

### Los estilos no se ven / la página aparece sin formato

Falta el compilador de assets. Abre una segunda terminal:

```bash
npm run dev
```

### `Vite manifest not found`

Los assets no se han compilado nunca:

```bash
npm install
npm run dev
```

### Cambié una migración y no pasa nada

Laravel registra en la tabla `migrations` las que ya ejecutó y las ignora en corridas posteriores. Para reconstruir todo:

```bash
php artisan migrate:fresh --seed
```

> **Importante:** esto borra todos los datos. Si la migración que quieres cambiar ya está en `main` y el otro integrante la ejecutó, **no la edites**: crea una migración nueva que altere la tabla, para que ambos entornos queden iguales.

### Algo funciona en la máquina del otro pero no en la mía

```bash
git pull
composer install
npm install
php artisan migrate
php artisan optimize:clear
```

Si persiste, comparen las versiones de las herramientas:

```bash
php -v
composer -V
node -v
```

---

## Documentación adicional

- **[`docs/Diario_Decisiones_IA.docx`](docs/Diario_Decisiones_IA.docx)** — Diario de decisiones técnicas e IA. Vale el 10% de la nota y se califica por consistencia con el historial de commits, así que **se escribe cuando se toma la decisión, no al final**. Si tomas una decisión de diseño, agrégale una entrada en el mismo commit.
- **Ficha del proyecto** — requerimientos ES-01 a ES-04 y rúbricas de evaluación.
- **Estándar institucional de base de datos** (`sistema_utn_academic_management.sql`) — las 45 tablas comunes a los cinco módulos. Es la fuente del esquema: **no lo modifiques por cuenta propia**, consúltalo con el docente.
- **Plan de trabajo** — distribución de tareas y estimaciones.