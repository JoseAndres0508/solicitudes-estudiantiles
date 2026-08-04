-- =====================================================================
-- Sistema Integrado de Gestión Académica y Docente — UTN, Sede San Carlos
-- MySQL 8.x | Compatible con MySQL Workbench (Reverse Engineer / EER)
-- Convenciones Laravel: nombres de tabla en plural, PK `id`, FK `table_id`.
-- Nombramientos, continuidad/estabilidad, jornada de derecho y licencias
-- docentes: diferidos a fase 2 junto con RRHH. Ver nota de alcance al final.
--
-- Este archivo es la contraparte en inglés de sistema_gestion_academica_utn.sql.
--
-- POLÍTICA DE IDIOMA
--   Inglés  : el esquema en sí — nombre de la base de datos, tablas,
--             columnas, llaves primarias y foráneas, índices y nombres de
--             restricciones — porque son identificadores de código.
--   Español : los comentarios explicativos que documentan el diseño, y
--             todo valor que la base de datos realmente almacena. Todas
--             las etiquetas ENUM y SET, todos los datos semilla (carreras,
--             recintos, modalidades, roles, descripciones de permisos,
--             equipamiento) y el texto COMMENT asociado a esas columnas,
--             porque son nombres propios o términos regulatorios
--             costarricenses / de la UTN cuyo significado legal se
--             perdería en la traducción: Diplomado, Bachillerato,
--             Licenciatura, Interino, Propiedad, Atinente, No Atinente,
--             Nota técnica, Vigente, Terminal, Levantamiento de requisito,
--             Convalidación, CONTA, RRHH, y así sucesivamente.
--             Los slugs de permisos (offering.manage, files.upload...)
--             permanecen en inglés porque son identificadores de código,
--             no texto almacenado.
--
-- Al final del archivo se incluye un glosario de traducción y la lista de
-- correcciones lógicas aplicadas.
-- =====================================================================
CREATE DATABASE IF NOT EXISTS utn_academic_management
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE utn_academic_management;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- SECCIÓN 1. NÚCLEO DE AUTENTICACIÓN
-- =====================================================================

-- 1.1 users (incluye las columnas de dos factores de la migración 2025_08_14_170933)
CREATE TABLE users (
  id                        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                      VARCHAR(255) NOT NULL,
  email                     VARCHAR(255) NOT NULL,
  email_verified_at         TIMESTAMP NULL DEFAULT NULL,
  password                  VARCHAR(255) NOT NULL,
  two_factor_secret         TEXT NULL,
  two_factor_recovery_codes TEXT NULL,
  two_factor_confirmed_at   TIMESTAMP NULL DEFAULT NULL,
  remember_token            VARCHAR(100) NULL DEFAULT NULL,
  created_at                TIMESTAMP NULL DEFAULT NULL,
  updated_at                TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY users_email_unique (email)
) ENGINE = InnoDB;

-- 1.2 password_reset_tokens
CREATE TABLE password_reset_tokens (
  email      VARCHAR(255) NOT NULL,
  token      VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (email)
) ENGINE = InnoDB;

-- 1.3 sessions
CREATE TABLE sessions (
  id            VARCHAR(255) NOT NULL,
  user_id       BIGINT UNSIGNED NULL DEFAULT NULL,
  ip_address    VARCHAR(45) NULL DEFAULT NULL,
  user_agent    TEXT NULL,
  payload       LONGTEXT NOT NULL,
  last_activity INT NOT NULL,
  PRIMARY KEY (id),
  KEY sessions_user_id_index (user_id),
  KEY sessions_last_activity_index (last_activity),
  CONSTRAINT fk_sessions_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 1.4 passkeys (WebAuthn, con relación explícita a user_id)
CREATE TABLE passkeys (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       BIGINT UNSIGNED NOT NULL,
  name          VARCHAR(255) NOT NULL,
  credential_id VARCHAR(255) NOT NULL,
  credential    JSON NOT NULL,
  last_used_at  TIMESTAMP NULL DEFAULT NULL,
  created_at    TIMESTAMP NULL DEFAULT NULL,
  updated_at    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY passkeys_credential_id_unique (credential_id),
  KEY passkeys_user_id_index (user_id),
  CONSTRAINT fk_passkeys_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 2. INFRAESTRUCTURA
-- =====================================================================

CREATE TABLE cache (
  `key`      VARCHAR(255) NOT NULL,
  value      MEDIUMTEXT NOT NULL,
  expiration BIGINT NOT NULL,
  PRIMARY KEY (`key`),
  KEY cache_expiration_index (expiration)
) ENGINE = InnoDB;

CREATE TABLE cache_locks (
  `key`      VARCHAR(255) NOT NULL,
  owner      VARCHAR(255) NOT NULL,
  expiration BIGINT NOT NULL,
  PRIMARY KEY (`key`),
  KEY cache_locks_expiration_index (expiration)
) ENGINE = InnoDB;

CREATE TABLE jobs (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  queue        VARCHAR(255) NOT NULL,
  payload      LONGTEXT NOT NULL,
  attempts     SMALLINT UNSIGNED NOT NULL,
  reserved_at  INT UNSIGNED NULL DEFAULT NULL,
  available_at INT UNSIGNED NOT NULL,
  created_at   INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY jobs_queue_index (queue)
) ENGINE = InnoDB;

CREATE TABLE job_batches (
  id             VARCHAR(255) NOT NULL,
  name           VARCHAR(255) NOT NULL,
  total_jobs     INT NOT NULL,
  pending_jobs   INT NOT NULL,
  failed_jobs    INT NOT NULL,
  failed_job_ids LONGTEXT NOT NULL,
  options        MEDIUMTEXT NULL,
  cancelled_at   INT NULL DEFAULT NULL,
  created_at     INT NOT NULL,
  finished_at    INT NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE = InnoDB;

CREATE TABLE failed_jobs (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid       VARCHAR(255) NOT NULL,
  connection VARCHAR(255) NOT NULL,
  queue      VARCHAR(255) NOT NULL,
  payload    LONGTEXT NOT NULL,
  exception  LONGTEXT NOT NULL,
  failed_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY failed_jobs_uuid_unique (uuid),
  KEY failed_jobs_connection_queue_failed_at_index (connection, queue, failed_at)
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 3. RBAC
-- =====================================================================

CREATE TABLE roles (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(60) NOT NULL,
  description VARCHAR(255) NULL DEFAULT NULL,
  created_at  TIMESTAMP NULL DEFAULT NULL,
  updated_at  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY roles_name_unique (name)
) ENGINE = InnoDB;

CREATE TABLE permissions (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(80) NOT NULL,
  description VARCHAR(255) NULL DEFAULT NULL,
  created_at  TIMESTAMP NULL DEFAULT NULL,
  updated_at  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY permissions_name_unique (name)
) ENGINE = InnoDB;

-- Pivote users <-> roles. PK compuesta con user_id primero: la consulta más
-- frecuente es "qué roles tiene este usuario", por lo que esa columna encabeza el índice.
CREATE TABLE role_user (
  user_id    BIGINT UNSIGNED NOT NULL,
  role_id    BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (user_id, role_id),
  KEY role_user_role_id_index (role_id),
  CONSTRAINT fk_role_user_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_role_user_role_id
    FOREIGN KEY (role_id) REFERENCES roles (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Pivote roles <-> permissions. PK con role_id primero: expandir un rol en
-- su conjunto de permisos es el patrón de lectura que más ejecuta la capa de autorización.
CREATE TABLE permission_role (
  role_id       BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  created_at    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (role_id, permission_id),
  KEY permission_role_permission_id_index (permission_id),
  CONSTRAINT fk_permission_role_role_id
    FOREIGN KEY (role_id) REFERENCES roles (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_permission_role_permission_id
    FOREIGN KEY (permission_id) REFERENCES permissions (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Pivote users <-> permissions (permisos DIRECTOS/EXTRA por usuario).
CREATE TABLE permission_user (
  user_id       BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  granted_by    BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Quién concedió el permiso extra',
  created_at    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (user_id, permission_id),
  KEY permission_user_permission_id_index (permission_id),
  KEY permission_user_granted_by_index (granted_by),
  CONSTRAINT fk_permission_user_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_permission_user_permission_id
    FOREIGN KEY (permission_id) REFERENCES permissions (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_permission_user_granted_by
    FOREIGN KEY (granted_by) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 4. CATÁLOGOS ACADÉMICOS
-- =====================================================================

-- 4.1 Carreras (las 14 carreras del Manual de Atinencias en alcance)
CREATE TABLE degree_programs (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(150) NOT NULL,
  is_active  TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY degree_programs_name_unique (name)
) ENGINE = InnoDB;

-- 4.2 Unidades ejecutoras (columna "# Unidad Ejecutora" de la hoja ITI)
CREATE TABLE executing_units (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code       CHAR(10) NOT NULL,
  name       VARCHAR(150) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY executing_units_code_unique (code)
) ENGINE = InnoDB;

-- 4.3 Metas presupuestarias (columnas "# Meta" / "Nombre Meta": 013001 Diplomado, 013002 Bachillerato)
CREATE TABLE budget_targets (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  executing_unit_id BIGINT UNSIGNED NOT NULL,
  code              CHAR(6) NOT NULL,
  name              VARCHAR(100) NOT NULL,
  created_at        TIMESTAMP NULL DEFAULT NULL,
  updated_at        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY budget_targets_unit_code_unique (executing_unit_id, code),
  CONSTRAINT fk_budget_targets_executing_unit_id
    FOREIGN KEY (executing_unit_id) REFERENCES executing_units (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 4.4 Períodos académicos (cuatrimestres; ej. III Cuatrimestre 2025)
CREATE TABLE academic_terms (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  year        SMALLINT UNSIGNED NOT NULL,
  term_number TINYINT UNSIGNED NOT NULL COMMENT '1, 2 o 3',
  start_date  DATE NOT NULL,
  end_date    DATE NOT NULL,
  created_at  TIMESTAMP NULL DEFAULT NULL,
  updated_at  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY academic_terms_year_term_number_unique (year, term_number),
  CONSTRAINT chk_academic_terms_term_number CHECK (term_number BETWEEN 1 AND 3),
  CONSTRAINT chk_academic_terms_date_range CHECK (end_date > start_date)
) ENGINE = InnoDB;

-- 4.5 Recintos físicos (la reasignación masiva de grupos se hace por recinto)
CREATE TABLE campuses (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(80) NOT NULL,
  is_owned   TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = alquilado/convenio (UNED, Santa Fe)',
  is_active  TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY campuses_name_unique (name)
) ENGINE = InnoDB;

-- 4.6 Aulas y espacios físicos
CREATE TABLE classrooms (
  id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  campus_id        BIGINT UNSIGNED NULL DEFAULT NULL,
  name             VARCHAR(30) NOT NULL,
  floor            VARCHAR(10) NULL DEFAULT NULL,
  type             ENUM('Aula regular','Laboratorio de cómputo','Laboratorio de ciencias',
                        'Laboratorio de idiomas','Auditorio','Otro')
                   NOT NULL DEFAULT 'Aula regular',
  capacity         SMALLINT UNSIGNED NULL DEFAULT NULL,
  unavailable_from DATE NULL DEFAULT NULL COMMENT 'No disponible a partir de esta fecha',
  created_at       TIMESTAMP NULL DEFAULT NULL,
  updated_at       TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  -- Corrección: el nombre del aula es único por recinto, no globalmente. Dos
  -- recintos pueden legítimamente tener cada uno un aula llamada "A-01".
  UNIQUE KEY classrooms_campus_name_unique (campus_id, name),
  KEY classrooms_type_capacity_index (type, capacity),
  CONSTRAINT fk_classrooms_campus_id
    FOREIGN KEY (campus_id) REFERENCES campuses (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 4.6b Equipamiento como catálogo N:M (permite filtrar aulas por equipo)
CREATE TABLE equipment_items (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(80) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY equipment_items_name_unique (name)
) ENGINE = InnoDB;

CREATE TABLE classroom_equipment_item (
  classroom_id      BIGINT UNSIGNED NOT NULL,
  equipment_item_id BIGINT UNSIGNED NOT NULL,
  quantity          SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  created_at        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (classroom_id, equipment_item_id),
  KEY classroom_equipment_item_equipment_index (equipment_item_id),
  CONSTRAINT fk_classroom_equipment_classroom_id
    FOREIGN KEY (classroom_id) REFERENCES classrooms (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_classroom_equipment_equipment_item_id
    FOREIGN KEY (equipment_item_id) REFERENCES equipment_items (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_classroom_equipment_quantity CHECK (quantity > 0)
) ENGINE = InnoDB;

-- 4.7 Modalidades: catálogo maestro; requiere_resolucion condiciona su uso
CREATE TABLE delivery_modes (
  id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                VARCHAR(40) NOT NULL,
  requires_resolution TINYINT(1) NOT NULL DEFAULT 0,
  created_at          TIMESTAMP NULL DEFAULT NULL,
  updated_at          TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY delivery_modes_name_unique (name)
) ENGINE = InnoDB;

-- 4.8 Cursos (degree_program_id NULL = curso de servicio transversal)
CREATE TABLE courses (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  degree_program_id BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'NULL cuando es un curso de servicio transversal',
  code              VARCHAR(30) NOT NULL COMMENT 'Ej.: ITI-224, ITIEL-13',
  name              VARCHAR(150) NOT NULL,
  is_service_course TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = curso transversal administrado por Docencia',
  is_bottleneck     TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = curso pinned: prioridad de horario y aula',
  requires_lab      TINYINT(1) NOT NULL DEFAULT 0,
  lab_type          ENUM('Laboratorio de cómputo','Laboratorio de ciencias','Laboratorio de idiomas')
                    NULL DEFAULT NULL COMMENT 'Tipo de laboratorio requerido',
  is_active         TINYINT(1) NOT NULL DEFAULT 1,
  created_at        TIMESTAMP NULL DEFAULT NULL,
  updated_at        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY courses_code_unique (code),
  KEY courses_degree_program_id_index (degree_program_id),
  CONSTRAINT fk_courses_degree_program_id
    FOREIGN KEY (degree_program_id) REFERENCES degree_programs (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_courses_service_program
    CHECK (is_service_course = 1 OR degree_program_id IS NOT NULL),
  -- Corrección: un curso que requiere laboratorio debe indicar qué tipo
  -- necesita, y un curso que no lo requiere no debe tener un tipo de laboratorio.
  CONSTRAINT chk_courses_lab_type
    CHECK ((requires_lab = 1 AND lab_type IS NOT NULL)
        OR (requires_lab = 0 AND lab_type IS NULL))
) ENGINE = InnoDB;

-- 4.9 Resoluciones de modalidad por curso (adjunto vía `attachments`)
CREATE TABLE delivery_mode_resolutions (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  course_id         BIGINT UNSIGNED NOT NULL,
  delivery_mode_id  BIGINT UNSIGNED NOT NULL,
  resolution_number VARCHAR(60) NOT NULL COMMENT 'Ej.: Resolución/acuerdo de vicerrectoría',
  approving_body    VARCHAR(120) NOT NULL,
  valid_from        DATE NOT NULL,
  valid_until       DATE NULL DEFAULT NULL COMMENT 'NULL = vigencia indefinida',
  created_at        TIMESTAMP NULL DEFAULT NULL,
  updated_at        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY delivery_mode_resolutions_course_mode_number_unique (course_id, delivery_mode_id, resolution_number),
  KEY delivery_mode_resolutions_course_validity_index (course_id, valid_from, valid_until),
  KEY delivery_mode_resolutions_delivery_mode_id_index (delivery_mode_id),
  CONSTRAINT fk_delivery_mode_resolutions_course_id
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_delivery_mode_resolutions_delivery_mode_id
    FOREIGN KEY (delivery_mode_id) REFERENCES delivery_modes (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_delivery_mode_resolutions_validity
    CHECK (valid_until IS NULL OR valid_until >= valid_from)
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 4C. REPOSITORIO CURRICULAR
-- =====================================================================

-- 4C.1 Planes de estudio (Vigente/Terminal; los Terminal exigen fecha de cierre)
CREATE TABLE study_plans (
  id                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  degree_program_id       BIGINT UNSIGNED NOT NULL,
  name                    VARCHAR(120) NOT NULL COMMENT 'Ej.: Plan 2023, Plan 2025',
  implementation_year     YEAR NOT NULL,
  classification          ENUM('Vigente','Terminal') NOT NULL DEFAULT 'Vigente',
  enrollment_closing_date DATE NULL DEFAULT NULL COMMENT 'Obligatoria solo para planes Terminal',
  created_at              TIMESTAMP NULL DEFAULT NULL,
  updated_at              TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY study_plans_program_name_unique (degree_program_id, name),
  KEY study_plans_classification_index (classification),
  CONSTRAINT fk_study_plans_degree_program_id
    FOREIGN KEY (degree_program_id) REFERENCES degree_programs (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_study_plans_terminal_date
    CHECK (classification = 'Vigente' OR enrollment_closing_date IS NOT NULL)
) ENGINE = InnoDB;

-- 4C.2 Niveles del plan (nivel 1, 2, 3, ... por cuatrimestre)
CREATE TABLE levels (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  study_plan_id BIGINT UNSIGNED NOT NULL,
  number        TINYINT UNSIGNED NOT NULL,
  created_at    TIMESTAMP NULL DEFAULT NULL,
  updated_at    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY levels_plan_number_unique (study_plan_id, number),
  CONSTRAINT fk_levels_study_plan_id
    FOREIGN KEY (study_plan_id) REFERENCES study_plans (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_levels_number CHECK (number > 0)
) ENGINE = InnoDB;

-- 4C.3 Pivote curso <-> nivel: estructura del plan con créditos por plan
CREATE TABLE course_level (
  level_id   BIGINT UNSIGNED NOT NULL,
  course_id  BIGINT UNSIGNED NOT NULL,
  credits    TINYINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (level_id, course_id),
  KEY course_level_course_id_index (course_id),
  CONSTRAINT fk_course_level_level_id
    FOREIGN KEY (level_id) REFERENCES levels (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_course_level_course_id
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_course_level_credits CHECK (credits > 0)
) ENGINE = InnoDB;

-- 4C.4 Requisitos entre cursos del mismo plan
CREATE TABLE prerequisites (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  study_plan_id      BIGINT UNSIGNED NOT NULL,
  required_course_id BIGINT UNSIGNED NOT NULL COMMENT 'Curso que debe aprobarse primero',
  target_course_id   BIGINT UNSIGNED NOT NULL COMMENT 'Curso que exige el requisito',
  created_at         TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY prerequisites_plan_pair_unique (study_plan_id, required_course_id, target_course_id),
  KEY prerequisites_target_course_index (target_course_id),
  KEY prerequisites_required_course_index (required_course_id),
  CONSTRAINT fk_prerequisites_study_plan_id
    FOREIGN KEY (study_plan_id) REFERENCES study_plans (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_prerequisites_required_course_id
    FOREIGN KEY (required_course_id) REFERENCES courses (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_prerequisites_target_course_id
    FOREIGN KEY (target_course_id) REFERENCES courses (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_prerequisites_distinct CHECK (required_course_id <> target_course_id)
) ENGINE = InnoDB;

-- 4C.5 Equiparaciones entre planes; anticiclos y adjunto obligatorio se
-- aplican en la capa de la aplicación.
CREATE TABLE course_equivalences (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  source_course_id  BIGINT UNSIGNED NOT NULL COMMENT 'Curso del plan anterior',
  target_course_id  BIGINT UNSIGNED NOT NULL COMMENT 'Curso equivalente del plan nuevo',
  direction         ENUM('Anterior a nuevo','Nuevo a anterior','Bidireccional') NOT NULL,
  resolution_number VARCHAR(60) NOT NULL,
  status            ENUM('Vigente','Sustituida') NOT NULL DEFAULT 'Vigente',
  superseded_by_id  BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Equiparación que prevalece (RC-02)',
  created_at        TIMESTAMP NULL DEFAULT NULL,
  updated_at        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY course_equivalences_pair_resolution_unique (source_course_id, target_course_id, resolution_number),
  KEY course_equivalences_target_course_index (target_course_id),
  KEY course_equivalences_status_index (status),
  KEY course_equivalences_superseded_by_index (superseded_by_id),
  CONSTRAINT fk_course_equivalences_source_course_id
    FOREIGN KEY (source_course_id) REFERENCES courses (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_course_equivalences_target_course_id
    FOREIGN KEY (target_course_id) REFERENCES courses (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_course_equivalences_superseded_by_id
    FOREIGN KEY (superseded_by_id) REFERENCES course_equivalences (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_course_equivalences_distinct CHECK (source_course_id <> target_course_id),
  -- Corrección: una equiparación 'Sustituida' debe referenciar a la que la
  -- reemplaza, y una equiparación 'Vigente' no debe tener ninguna.
  CONSTRAINT chk_course_equivalences_superseded
    CHECK ((status = 'Sustituida' AND superseded_by_id IS NOT NULL)
        OR (status = 'Vigente' AND superseded_by_id IS NULL))
  -- Nota: "una fila no puede sustituirse a sí misma" no puede expresarse como
  -- una restricción CHECK, porque MySQL 8 prohíbe expresiones CHECK que
  -- referencien una columna AUTO_INCREMENT. Se aplica en la capa de la
  -- aplicación, junto con la verificación más amplia de ciclos de sustitución.
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 4D. ESTUDIANTES
-- =====================================================================

CREATE TABLE students (
  id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id          BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Cuenta de acceso al portal',
  national_id      VARCHAR(12) NOT NULL,
  first_name       VARCHAR(60) NOT NULL,
  last_name        VARCHAR(60) NOT NULL,
  second_last_name VARCHAR(60) NULL DEFAULT NULL,
  is_active        TINYINT(1) NOT NULL DEFAULT 1,
  created_at       TIMESTAMP NULL DEFAULT NULL,
  updated_at       TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY students_national_id_unique (national_id),
  UNIQUE KEY students_user_id_unique (user_id),
  KEY students_last_names_index (last_name, second_last_name),
  CONSTRAINT fk_students_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Población por plan/nivel (RC-01: "cuántos estudiantes activos por plan y nivel")
CREATE TABLE student_study_plan (
  student_id    BIGINT UNSIGNED NOT NULL,
  study_plan_id BIGINT UNSIGNED NOT NULL,
  current_level TINYINT UNSIGNED NULL DEFAULT NULL,
  created_at    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (student_id, study_plan_id),
  KEY student_study_plan_plan_level_index (study_plan_id, current_level),
  CONSTRAINT fk_student_study_plan_student_id
    FOREIGN KEY (student_id) REFERENCES students (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_student_study_plan_study_plan_id
    FOREIGN KEY (study_plan_id) REFERENCES study_plans (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- Historial académico interno simplificado (RC-02b acredita aquí por
-- referencia a la resolución de equiparación que lo respalda).
CREATE TABLE academic_records (
  id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id            BIGINT UNSIGNED NOT NULL,
  course_id             BIGINT UNSIGNED NOT NULL,
  academic_term_id      BIGINT UNSIGNED NULL DEFAULT NULL,
  status                ENUM('Aprobado','Reprobado','Acreditado por equiparación',
                             'Acreditado por convalidación','Requisito levantado') NOT NULL,
  grade                 DECIMAL(5,2) NULL DEFAULT NULL,
  course_equivalence_id BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Resolución de referencia de la acreditación',
  created_at            TIMESTAMP NULL DEFAULT NULL,
  updated_at            TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY academic_records_student_course_index (student_id, course_id),
  KEY academic_records_course_status_index (course_id, status),
  KEY academic_records_term_index (academic_term_id),
  KEY academic_records_equivalence_index (course_equivalence_id),
  CONSTRAINT fk_academic_records_student_id
    FOREIGN KEY (student_id) REFERENCES students (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_academic_records_course_id
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_academic_records_academic_term_id
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_academic_records_course_equivalence_id
    FOREIGN KEY (course_equivalence_id) REFERENCES course_equivalences (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_academic_records_grade CHECK (grade IS NULL OR (grade >= 0 AND grade <= 100))
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 5. ATINENCIAS
-- =====================================================================

-- 5.1 Especialidades/grados habilitantes (listas "- ..." del Manual)
CREATE TABLE specialties (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(180) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY specialties_name_unique (name)
) ENGINE = InnoDB;

-- 5.2 Catálogo de atinencias por curso, versionado (acuerdo + Gaceta + vigencia)
CREATE TABLE suitability_catalogs (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  course_id     BIGINT UNSIGNED NOT NULL,
  version       SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  agreement      VARCHAR(120) NOT NULL COMMENT 'Acuerdo del Consejo Universitario (obligatorio)',
  gazette_number VARCHAR(60) NOT NULL COMMENT 'Número de La Gaceta (obligatorio)',
  valid_from    DATE NOT NULL,
  valid_until   DATE NULL DEFAULT NULL,
  created_at    TIMESTAMP NULL DEFAULT NULL,
  updated_at    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY suitability_catalogs_course_version_unique (course_id, version),
  KEY suitability_catalogs_course_validity_index (course_id, valid_from),
  CONSTRAINT fk_suitability_catalogs_course_id
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_suitability_catalogs_validity
    CHECK (valid_until IS NULL OR valid_until >= valid_from)
) ENGINE = InnoDB;

-- 5.3 Pivote: especialidades atinentes por versión de catálogo
CREATE TABLE suitability_catalog_specialty (
  suitability_catalog_id BIGINT UNSIGNED NOT NULL,
  specialty_id           BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (suitability_catalog_id, specialty_id),
  KEY suit_cat_spec_specialty_id_index (specialty_id),
  CONSTRAINT fk_suit_cat_spec_catalog_id
    FOREIGN KEY (suitability_catalog_id) REFERENCES suitability_catalogs (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_suit_cat_spec_specialty_id
    FOREIGN KEY (specialty_id) REFERENCES specialties (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 6. DOCENTES Y ATESTADOS
-- =====================================================================

-- 6.1 Puestos (columna "Puesto": Profesor 2/3/4, Profesor Especialista 1)
CREATE TABLE job_positions (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(60) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY job_positions_name_unique (name)
) ENGINE = InnoDB;

-- 6.2 Docentes (columnas "Cédula" y "Docente" de la hoja ITI)
CREATE TABLE instructors (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id            BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Cuenta de acceso opcional',
  job_position_id    BIGINT UNSIGNED NOT NULL,
  national_id        VARCHAR(12) NOT NULL,
  first_name         VARCHAR(60) NOT NULL,
  last_name          VARCHAR(60) NOT NULL,
  second_last_name   VARCHAR(60) NULL DEFAULT NULL,
  estimated_workload DECIMAL(3,2) NULL DEFAULT NULL COMMENT 'Indicativa; sujeta a confirmación de RRHH',
  is_active          TINYINT(1) NOT NULL DEFAULT 1,
  created_at         TIMESTAMP NULL DEFAULT NULL,
  updated_at         TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY instructors_national_id_unique (national_id),
  UNIQUE KEY instructors_user_id_unique (user_id),
  KEY instructors_job_position_id_index (job_position_id),
  KEY instructors_last_names_index (last_name, second_last_name),
  CONSTRAINT fk_instructors_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_instructors_job_position_id
    FOREIGN KEY (job_position_id) REFERENCES job_positions (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_instructors_estimated_workload
    CHECK (estimated_workload IS NULL OR (estimated_workload > 0 AND estimated_workload <= 1))
) ENGINE = InnoDB;

-- 6.3 Atestados académicos: base de la verificación de atinencia
CREATE TABLE credentials (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  instructor_id BIGINT UNSIGNED NOT NULL,
  specialty_id BIGINT UNSIGNED NOT NULL,
  degree_level ENUM('Diplomado','Bachillerato','Licenciatura','Maestría','Doctorado') NOT NULL,
  institution  VARCHAR(150) NOT NULL,
  year_awarded YEAR NOT NULL,
  created_at   TIMESTAMP NULL DEFAULT NULL,
  updated_at   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY credentials_instructor_specialty_degree_unique (instructor_id, specialty_id, degree_level),
  KEY credentials_specialty_id_index (specialty_id),
  CONSTRAINT fk_credentials_instructor_id
    FOREIGN KEY (instructor_id) REFERENCES instructors (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_credentials_specialty_id
    FOREIGN KEY (specialty_id) REFERENCES specialties (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 7. OFERTA ACADÉMICA
-- =====================================================================

-- 7.1 Grupos: instancia de un curso en un período
-- (columnas "Grupo", "Cupo", "Modalidad" y "Aula")
CREATE TABLE sections (
  id                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  course_id            BIGINT UNSIGNED NOT NULL,
  academic_term_id     BIGINT UNSIGNED NOT NULL,
  budget_target_id     BIGINT UNSIGNED NOT NULL,
  classroom_id         BIGINT UNSIGNED NULL DEFAULT NULL,
  delivery_mode_id     BIGINT UNSIGNED NOT NULL,
  number               TINYINT UNSIGNED NOT NULL COMMENT 'Número de grupo (1, 2, ...)',
  capacity             SMALLINT UNSIGNED NOT NULL,
  estimated_enrollment SMALLINT UNSIGNED NULL DEFAULT NULL COMMENT 'Demanda esperada pre-matrícula',
  actual_enrollment    SMALLINT UNSIGNED NULL DEFAULT NULL COMMENT 'Se llena tras la matrícula; NULL = aún sin datos',
  status               ENUM('Necesidad solicitada','Borrador','Enviado al CONTA','Consolidado',
                            'Enviado a RRHH','Confirmado por RRHH','Cerrado')
                       NOT NULL DEFAULT 'Borrador',
  created_at           TIMESTAMP NULL DEFAULT NULL,
  updated_at           TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY sections_course_term_number_unique (course_id, academic_term_id, number),
  KEY sections_term_status_index (academic_term_id, status),
  KEY sections_budget_target_id_index (budget_target_id),
  KEY sections_classroom_id_index (classroom_id),
  KEY sections_delivery_mode_id_index (delivery_mode_id),
  CONSTRAINT fk_sections_course_id
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_sections_academic_term_id
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_sections_budget_target_id
    FOREIGN KEY (budget_target_id) REFERENCES budget_targets (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_sections_classroom_id
    FOREIGN KEY (classroom_id) REFERENCES classrooms (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_sections_delivery_mode_id
    FOREIGN KEY (delivery_mode_id) REFERENCES delivery_modes (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_sections_capacity CHECK (capacity > 0)
) ENGINE = InnoDB;

-- 7.1b Pivote carrera <-> grupo: solo para grupos de servicio compartidos
CREATE TABLE degree_program_section (
  section_id        BIGINT UNSIGNED NOT NULL,
  degree_program_id BIGINT UNSIGNED NOT NULL,
  created_at        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (section_id, degree_program_id),
  KEY degree_program_section_program_id_index (degree_program_id),
  CONSTRAINT fk_degree_program_section_section_id
    FOREIGN KEY (section_id) REFERENCES sections (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_degree_program_section_program_id
    FOREIGN KEY (degree_program_id) REFERENCES degree_programs (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 7.1c Historial de estados del grupo
CREATE TABLE section_status_history (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  section_id      BIGINT UNSIGNED NOT NULL,
  previous_status VARCHAR(30) NULL DEFAULT NULL,
  new_status      VARCHAR(30) NOT NULL,
  user_id         BIGINT UNSIGNED NULL DEFAULT NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY section_status_history_section_date_index (section_id, created_at),
  KEY section_status_history_user_id_index (user_id),
  CONSTRAINT fk_section_status_history_section_id
    FOREIGN KEY (section_id) REFERENCES sections (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_section_status_history_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 7.2 Horarios normalizados (columna "Horario": "Lunes 08:00-11:59")
CREATE TABLE schedules (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  section_id  BIGINT UNSIGNED NOT NULL,
  day_of_week ENUM('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
  start_time  TIME NOT NULL,
  end_time    TIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY schedules_section_day_start_unique (section_id, day_of_week, start_time),
  KEY schedules_day_times_index (day_of_week, start_time, end_time),
  CONSTRAINT fk_schedules_section_id
    FOREIGN KEY (section_id) REFERENCES sections (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_schedules_time_range CHECK (end_time > start_time)
) ENGINE = InnoDB;

-- 7.3 Asignación docente por grupo (foto del cuatrimestre, no historial RRHH)
CREATE TABLE teaching_assignments (
  id                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  section_id              BIGINT UNSIGNED NOT NULL,
  instructor_id           BIGINT UNSIGNED NOT NULL,
  workload                DECIMAL(3,2) NOT NULL COMMENT 'Fracción de jornada, ej. 0.25',
  appointment_type        ENUM('Interino','Propiedad') NOT NULL DEFAULT 'Interino',
  pay_period              VARCHAR(20) NULL DEFAULT NULL,
  personnel_action_number VARCHAR(30) NULL DEFAULT NULL,
  remarks                 VARCHAR(255) NULL DEFAULT NULL,
  status                  ENUM('Propuesta','Confirmada','Rechazada') NOT NULL DEFAULT 'Propuesta',
  created_at              TIMESTAMP NULL DEFAULT NULL,
  updated_at              TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY teaching_assignments_section_instructor_unique (section_id, instructor_id),
  -- Detección de docente duplicado entre carreras en el mismo período
  KEY teaching_assignments_instructor_status_index (instructor_id, status),
  KEY teaching_assignments_status_index (status),
  CONSTRAINT fk_teaching_assignments_section_id
    FOREIGN KEY (section_id) REFERENCES sections (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_teaching_assignments_instructor_id
    FOREIGN KEY (instructor_id) REFERENCES instructors (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_teaching_assignments_workload CHECK (workload > 0 AND workload <= 1)
) ENGINE = InnoDB;

-- 7.3b Historial de cambios de asignación, respaldado por número de oficio
CREATE TABLE assignment_changes (
  id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  teaching_assignment_id BIGINT UNSIGNED NOT NULL,
  change_type            ENUM('Docente','Horario','Aula','Jornada','Estado','Otro') NOT NULL,
  previous_instructor_id BIGINT UNSIGNED NULL DEFAULT NULL,
  new_instructor_id      BIGINT UNSIGNED NULL DEFAULT NULL,
  memo_number            VARCHAR(30) NULL DEFAULT NULL COMMENT 'Oficio/acuerdo que respalda el cambio',
  description            VARCHAR(255) NOT NULL,
  user_id                BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Usuario que registró el cambio',
  created_at             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY assignment_changes_assignment_date_index (teaching_assignment_id, created_at),
  KEY assignment_changes_memo_index (memo_number),
  KEY assignment_changes_previous_instructor_index (previous_instructor_id),
  KEY assignment_changes_new_instructor_index (new_instructor_id),
  KEY assignment_changes_user_id_index (user_id),
  CONSTRAINT fk_assignment_changes_assignment_id
    FOREIGN KEY (teaching_assignment_id) REFERENCES teaching_assignments (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_assignment_changes_previous_instructor_id
    FOREIGN KEY (previous_instructor_id) REFERENCES instructors (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_assignment_changes_new_instructor_id
    FOREIGN KEY (new_instructor_id) REFERENCES instructors (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_assignment_changes_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 7.4 Verificaciones de atinencia: resultado auditable por asignación
CREATE TABLE suitability_checks (
  id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  teaching_assignment_id BIGINT UNSIGNED NOT NULL,
  suitability_catalog_id BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Versión de catálogo aplicada; NULL si el resultado es Sin catálogo',
  user_id                BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Usuario que ejecutó/aprobó la verificación',
  result                 ENUM('Atinente','No Atinente','Nota técnica','Sin catálogo') NOT NULL,
  is_provisional         TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = provisional por vigencia futura del catálogo',
  justification          TEXT NULL,
  created_at             TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY suitability_checks_assignment_date_index (teaching_assignment_id, created_at),
  KEY suitability_checks_result_index (result),
  KEY suitability_checks_catalog_id_index (suitability_catalog_id),
  KEY suitability_checks_user_id_index (user_id),
  CONSTRAINT fk_suitability_checks_assignment_id
    FOREIGN KEY (teaching_assignment_id) REFERENCES teaching_assignments (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_suitability_checks_catalog_id
    FOREIGN KEY (suitability_catalog_id) REFERENCES suitability_catalogs (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_suitability_checks_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  -- Corrección: el resultado "Sin catálogo" es el único compatible con
  -- suitability_catalog_id NULL
  CONSTRAINT chk_suitability_checks_catalog
    CHECK ((result = 'Sin catálogo' AND suitability_catalog_id IS NULL)
        OR (result <> 'Sin catálogo' AND suitability_catalog_id IS NOT NULL))
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 7B. RESERVAS Y BLOQUEOS DE AULAS
-- =====================================================================

CREATE TABLE classroom_reservations (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  classroom_id BIGINT UNSIGNED NOT NULL,
  user_id      BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Usuario que registró la reserva',
  type         ENUM('Reserva','Bloqueo administrativo') NOT NULL DEFAULT 'Reserva',
  requester    VARCHAR(120) NULL DEFAULT NULL COMMENT 'NULL en bloqueos administrativos',
  purpose      VARCHAR(255) NOT NULL,
  start_date   DATE NOT NULL,
  end_date     DATE NULL DEFAULT NULL COMMENT 'NULL = reserva de un solo día',
  days_of_week SET('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')
               NULL DEFAULT NULL COMMENT 'Días que aplica dentro del rango; NULL = todos / día único',
  start_time   TIME NOT NULL,
  end_time     TIME NOT NULL,
  status       ENUM('Solicitada','Aprobada','Rechazada','Cancelada') NOT NULL DEFAULT 'Solicitada',
  created_at   TIMESTAMP NULL DEFAULT NULL,
  updated_at   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY classroom_reservations_classroom_dates_index (classroom_id, start_date, end_date),
  KEY classroom_reservations_status_index (status),
  KEY classroom_reservations_user_id_index (user_id),
  CONSTRAINT fk_classroom_reservations_classroom_id
    FOREIGN KEY (classroom_id) REFERENCES classrooms (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_classroom_reservations_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_classroom_reservations_time_range CHECK (end_time > start_time),
  CONSTRAINT chk_classroom_reservations_date_range
    CHECK (end_date IS NULL OR end_date >= start_date),
  -- Corrección: una 'Reserva' siempre tiene solicitante; un 'Bloqueo
  -- administrativo' nunca lo tiene
  CONSTRAINT chk_classroom_reservations_requester
    CHECK ((type = 'Reserva' AND requester IS NOT NULL)
        OR (type = 'Bloqueo administrativo' AND requester IS NULL))
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 7C. SOLICITUDES ESTUDIANTILES
-- =====================================================================

-- 7C.1 Reglas de levantamiento por curso, evaluadas en orden
CREATE TABLE waiver_rules (
  id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  course_id             BIGINT UNSIGNED NOT NULL,
  evaluation_order      TINYINT UNSIGNED NOT NULL COMMENT 'Orden de evaluación del motor',
  type                  ENUM('Requisito aprobado con nota mínima','Créditos o cursos acumulados',
                             'Pertenencia a plan terminal','Siempre revisión manual') NOT NULL,
  prerequisite_course_id BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Parámetro del tipo (a)',
  minimum_grade         DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Parámetro N del tipo (a)',
  minimum_accumulated   SMALLINT UNSIGNED NULL DEFAULT NULL COMMENT 'Parámetro K del tipo (b)',
  is_active             TINYINT(1) NOT NULL DEFAULT 1,
  created_at            TIMESTAMP NULL DEFAULT NULL,
  updated_at            TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY waiver_rules_course_order_unique (course_id, evaluation_order),
  KEY waiver_rules_prerequisite_course_index (prerequisite_course_id),
  CONSTRAINT fk_waiver_rules_course_id
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_waiver_rules_prerequisite_course_id
    FOREIGN KEY (prerequisite_course_id) REFERENCES courses (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_waiver_rules_distinct_course
    CHECK (prerequisite_course_id IS NULL OR prerequisite_course_id <> course_id),
  -- Corrección: cada tipo de regla debe llevar exactamente los parámetros
  -- que necesita
  CONSTRAINT chk_waiver_rules_parameters
    CHECK (
      (type = 'Requisito aprobado con nota mínima'
        AND prerequisite_course_id IS NOT NULL AND minimum_grade IS NOT NULL
        AND minimum_accumulated IS NULL)
      OR (type = 'Créditos o cursos acumulados'
        AND minimum_accumulated IS NOT NULL
        AND prerequisite_course_id IS NULL AND minimum_grade IS NULL)
      OR (type IN ('Pertenencia a plan terminal','Siempre revisión manual')
        AND prerequisite_course_id IS NULL AND minimum_grade IS NULL
        AND minimum_accumulated IS NULL)
    )
) ENGINE = InnoDB;

-- 7C.2 Convalidaciones históricas (precedentes por institución + curso externo)
CREATE TABLE transfer_credit_precedents (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  institution       VARCHAR(150) NOT NULL,
  external_course   VARCHAR(150) NOT NULL,
  course_id         BIGINT UNSIGNED NOT NULL COMMENT 'Curso interno UTN equivalente',
  outcome           ENUM('Aprobada','Denegada') NOT NULL,
  resolution_number VARCHAR(60) NOT NULL COMMENT 'Resolución de referencia del precedente',
  created_at        TIMESTAMP NULL DEFAULT NULL,
  updated_at        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY transfer_credit_precedents_lookup_index (institution, external_course),
  KEY transfer_credit_precedents_course_id_index (course_id),
  CONSTRAINT fk_transfer_credit_precedents_course_id
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 7C.3 Solicitudes (levantamientos y convalidaciones; adjuntos vía `attachments`)
CREATE TABLE student_requests (
  id                           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id                   BIGINT UNSIGNED NOT NULL,
  type                         ENUM('Levantamiento de requisito','Convalidación') NOT NULL,
  course_id                    BIGINT UNSIGNED NOT NULL COMMENT 'Curso a matricular / curso interno al que aspira',
  prerequisite_course_id       BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Requisito no cumplido',
  source_institution           VARCHAR(150) NULL DEFAULT NULL COMMENT 'Solo en solicitudes de Convalidación',
  external_course              VARCHAR(150) NULL DEFAULT NULL COMMENT 'Solo en solicitudes de Convalidación',
  transfer_credit_precedent_id BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Precedente encontrado, si existe',
  engine_result                ENUM('Procede automáticamente','No procede','Requiere revisión manual')
                               NULL DEFAULT NULL COMMENT 'Primer resultado concluyente del motor de reglas',
  failed_rule_id               BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Regla que produjo el resultado No procede',
  status                       ENUM('Pendiente de revisión','En revisión','Aprobada','Denegada')
                               NOT NULL DEFAULT 'Pendiente de revisión',
  estimated_resolution_date    DATE NULL DEFAULT NULL COMMENT 'Si no se ingresa en 24h la app asigna 5 días hábiles',
  reviewer_id                  BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Usuario revisor (Docencia/Comisión)',
  created_at                   TIMESTAMP NULL DEFAULT NULL,
  updated_at                   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY student_requests_inbox_index (type, status, created_at),
  KEY student_requests_student_index (student_id, status),
  KEY student_requests_course_id_index (course_id),
  KEY student_requests_prerequisite_course_index (prerequisite_course_id),
  KEY student_requests_precedent_index (transfer_credit_precedent_id),
  KEY student_requests_failed_rule_index (failed_rule_id),
  KEY student_requests_reviewer_id_index (reviewer_id),
  CONSTRAINT fk_student_requests_student_id
    FOREIGN KEY (student_id) REFERENCES students (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_student_requests_course_id
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_student_requests_prerequisite_course_id
    FOREIGN KEY (prerequisite_course_id) REFERENCES courses (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_student_requests_precedent_id
    FOREIGN KEY (transfer_credit_precedent_id) REFERENCES transfer_credit_precedents (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_student_requests_failed_rule_id
    FOREIGN KEY (failed_rule_id) REFERENCES waiver_rules (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_student_requests_reviewer_id
    FOREIGN KEY (reviewer_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  -- Corrección: cada tipo de solicitud debe llevar sus propios campos obligatorios
  CONSTRAINT chk_student_requests_transfer_fields
    CHECK (type <> 'Convalidación'
        OR (source_institution IS NOT NULL AND external_course IS NOT NULL)),
  CONSTRAINT chk_student_requests_waiver_fields
    CHECK (type <> 'Levantamiento de requisito' OR prerequisite_course_id IS NOT NULL)
) ENGINE = InnoDB;

-- 7C.4 Historial de estados de la solicitud (auditoría + base de notificación)
CREATE TABLE student_request_status_history (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_request_id BIGINT UNSIGNED NOT NULL,
  previous_status    VARCHAR(30) NULL DEFAULT NULL,
  new_status         VARCHAR(30) NOT NULL,
  comment            VARCHAR(255) NULL DEFAULT NULL COMMENT 'Justificación del cambio; obligatoria en denegaciones (capa app)',
  user_id            BIGINT UNSIGNED NULL DEFAULT NULL,
  notified_at        TIMESTAMP NULL DEFAULT NULL COMMENT 'Momento del correo al estudiante',
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY student_request_status_history_request_date_index (student_request_id, created_at),
  KEY student_request_status_history_user_id_index (user_id),
  CONSTRAINT fk_student_request_status_history_request_id
    FOREIGN KEY (student_request_id) REFERENCES student_requests (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_student_request_status_history_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

-- =====================================================================
-- SECCIÓN 8. GESTIÓN DOCUMENTAL
-- =====================================================================

-- 8.1 Archivos: metadatos con relación polimórfica
CREATE TABLE attachments (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid            CHAR(36) NOT NULL COMMENT 'Identificador público para URL de descarga firmada',
  user_id         BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Usuario que subió el archivo',
  attachable_type VARCHAR(120) NOT NULL COMMENT 'Clase del modelo dueño (App\\Models\\TechnicalNote, ...)',
  attachable_id   BIGINT UNSIGNED NOT NULL,
  document_type   VARCHAR(60) NOT NULL COMMENT 'Criterio Técnico, Resolución, Certificación, Reporte, ...',
  original_name   VARCHAR(255) NOT NULL,
  disk            VARCHAR(30) NOT NULL DEFAULT 'local',
  path            VARCHAR(255) NOT NULL,
  mime_type       VARCHAR(100) NOT NULL,
  size_bytes      INT UNSIGNED NOT NULL,
  sha256_hash     CHAR(64) NOT NULL COMMENT 'Integridad y detección de duplicados',
  created_at      TIMESTAMP NULL DEFAULT NULL,
  updated_at      TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY attachments_uuid_unique (uuid),
  UNIQUE KEY attachments_disk_path_unique (disk, path),
  KEY attachments_attachable_index (attachable_type, attachable_id),
  KEY attachments_document_type_index (document_type),
  KEY attachments_user_id_index (user_id),
  CONSTRAINT fk_attachments_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_attachments_size CHECK (size_bytes > 0)
) ENGINE = InnoDB;

-- 8.2 Notas técnicas: attachment_id NOT NULL fuerza el PDF firmado como condición
CREATE TABLE technical_notes (
  id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  teaching_assignment_id BIGINT UNSIGNED NOT NULL,
  attachment_id          BIGINT UNSIGNED NOT NULL COMMENT 'PDF del criterio técnico firmado (obligatorio)',
  user_id                BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Coordinadora que registró la nota',
  ratification_deadline  DATE NOT NULL COMMENT 'SLA de ratificación',
  status                 ENUM('Ratificación pendiente','Ratificada','Vencida','Rechazada')
                         NOT NULL DEFAULT 'Ratificación pendiente',
  ratified_at            TIMESTAMP NULL DEFAULT NULL,
  created_at             TIMESTAMP NULL DEFAULT NULL,
  updated_at             TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY technical_notes_assignment_unique (teaching_assignment_id),
  KEY technical_notes_sla_index (status, ratification_deadline),
  KEY technical_notes_attachment_id_index (attachment_id),
  KEY technical_notes_user_id_index (user_id),
  CONSTRAINT fk_technical_notes_assignment_id
    FOREIGN KEY (teaching_assignment_id) REFERENCES teaching_assignments (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_technical_notes_attachment_id
    FOREIGN KEY (attachment_id) REFERENCES attachments (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_technical_notes_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  -- Corrección: solo una nota 'Ratificada' lleva marca de tiempo de ratificación
  CONSTRAINT chk_technical_notes_ratified_at
    CHECK ((status = 'Ratificada' AND ratified_at IS NOT NULL)
        OR (status <> 'Ratificada' AND ratified_at IS NULL))
) ENGINE = InnoDB;

-- 8.3 Bitácora de descargas: quién descargó qué, cuándo y desde dónde
CREATE TABLE attachment_downloads (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  attachment_id BIGINT UNSIGNED NOT NULL,
  user_id       BIGINT UNSIGNED NULL DEFAULT NULL,
  ip_address    VARCHAR(45) NULL DEFAULT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY attachment_downloads_attachment_date_index (attachment_id, created_at),
  KEY attachment_downloads_user_id_index (user_id),
  CONSTRAINT fk_attachment_downloads_attachment_id
    FOREIGN KEY (attachment_id) REFERENCES attachments (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_attachment_downloads_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 8.4 Auditoría polimórfica; `changes` guarda el diff JSON {campo:{antes,despues}}
CREATE TABLE audits (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id        BIGINT UNSIGNED NULL DEFAULT NULL,
  auditable_type VARCHAR(120) NOT NULL COMMENT 'Clase del modelo (App\\Models\\Credential, ...)',
  auditable_id   BIGINT UNSIGNED NOT NULL,
  action         ENUM('Creación','Modificación','Eliminación') NOT NULL,
  changes        JSON NULL,
  ip_address     VARCHAR(45) NULL DEFAULT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY audits_auditable_index (auditable_type, auditable_id, created_at),
  KEY audits_user_id_index (user_id),
  CONSTRAINT fk_audits_user_id
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- SECCIÓN 9. DATOS SEMILLA
-- =====================================================================

-- 9.1 Carreras en alcance
INSERT INTO degree_programs (name, created_at) VALUES
  ('Administración y Gestión de Recursos Humanos', NOW()),
  ('Administración Aduanera', NOW()),
  ('Ingeniería en Tecnologías de Información - Tecnologías de Información', NOW()),
  ('Ingeniería del Software - Tecnologías Informáticas', NOW()),
  ('Contabilidad y Finanzas - Contaduría Pública', NOW()),
  ('Asistencia Administrativa', NOW()),
  ('Inglés como Lengua Extranjera', NOW()),
  ('Administración Agroindustrial', NOW()),
  ('Gestión de Centros de Servicios Compartidos', NOW()),
  ('Ingeniería en Mantenimiento Agroindustrial Sostenible - Mantenimiento Agroindustrial Sostenible', NOW()),
  ('Ingeniería en Gestión Ambiental', NOW()),
  ('Ingeniería en Salud Ocupacional y Ambiente - Salud Ocupacional', NOW()),
  ('Ingeniería en Tecnología de Alimentos - Tecnología de Alimentos', NOW()),
  ('Administración del Comercio Exterior', NOW());

-- 9.2 Unidad ejecutora y metas presentes en la hoja "ITI"
INSERT INTO executing_units (code, name, created_at) VALUES
  ('0610207005', 'Ingeniería en Tecnologías de la Información', NOW());

INSERT INTO budget_targets (executing_unit_id, code, name, created_at) VALUES
  (1, '013001', 'Diplomado', NOW()),
  (1, '013002', 'Bachillerato', NOW());

-- 9.3 Período de la oferta analizada
INSERT INTO academic_terms (year, term_number, start_date, end_date, created_at) VALUES
  (2025, 3, '2025-09-01', '2025-12-19', NOW());

-- 9.3a Equipamientos base
INSERT INTO equipment_items (name, created_at) VALUES
  ('Proyector', NOW()),
  ('Computadoras', NOW()),
  ('Aire acondicionado', NOW()),
  ('Pizarra inteligente', NOW());

-- 9.3b Recintos (v2 — caso Santa Fe: recinto alquilado)
INSERT INTO campuses (name, is_owned, created_at) VALUES
  ('Campus Central San Carlos', 1, NOW()),
  ('Recinto Santa Fe',          0, NOW());

-- 9.3c Catálogo maestro de modalidades (v2 — RC-03: Presencial es el valor
-- por defecto y la única que no requiere resolución de respaldo).
INSERT INTO delivery_modes (name, requires_resolution, created_at) VALUES
  ('Presencial',         0, NOW()),
  ('Híbrido',            1, NOW()),
  ('Virtual',            1, NOW()),
  ('Tutoría',            1, NOW()),
  ('Aprendizaje Remoto', 1, NOW());

-- 9.4 Roles y permisos base (RBAC alineado a la lógica de negocio y a los
-- actores identificados en los requerimientos).
INSERT INTO roles (name, description, created_at) VALUES
  ('Administrador',            'Gestión total: catálogo de atinencias, usuarios y configuración', NOW()),
  ('Coordinadora de Docencia', 'Registra atestados, consolida y gestiona asignaciones docentes', NOW()),
  ('Docente',                  'Consulta su perfil, atestados y asignaciones', NOW()),
  ('Consulta',                 'Acceso de solo lectura a la oferta académica', NOW()),
  ('Director de Carrera',      'Registra la oferta, planes y resoluciones de su propia carrera', NOW()),
  ('Coordinador CONTA',        'Consolida la oferta de las carreras de su área', NOW()),
  ('Recursos Humanos',         'Lectura de la oferta consolidada; sin acceso a atinencias', NOW()),
  ('Estudiante',               'Presenta y da seguimiento a sus propias solicitudes', NOW()),
  ('Comisión Técnica',         'Revisa y resuelve solicitudes de Convalidación', NOW());

INSERT INTO permissions (name, description, created_at) VALUES
  ('credentials.manage',     'Crear y editar atestados de docentes', NOW()),
  ('catalog.manage',         'Crear versiones del catálogo de atinencias', NOW()),
  ('offering.manage',        'Crear grupos, horarios y asignaciones', NOW()),
  ('suitability.verify',     'Ejecutar verificaciones de atinencia', NOW()),
  ('technical_note.approve', 'Aprobar la vía excepcional de Nota Técnica', NOW()),
  ('offering.view',          'Consultar la oferta académica', NOW()),
  ('users.manage',           'Administrar usuarios, roles y permisos', NOW()),
  ('files.upload',           'Adjuntar documentos a los módulos', NOW()),
  ('files.download',         'Descargar documentos adjuntos y reportes', NOW()),
  ('resolutions.manage',     'Registrar resoluciones de modalidad por curso', NOW()),
  ('reservations.manage',    'Registrar y aprobar préstamos de aulas', NOW()),
  ('offering.consolidate',   'Consolidar la oferta y mover grupos de estado', NOW()),
  ('plans.manage',           'Administrar planes de estudio, niveles y requisitos', NOW()),
  ('equivalences.manage',    'Registrar equiparaciones entre planes', NOW()),
  ('requests.create',        'Presentar solicitudes estudiantiles', NOW()),
  ('requests.review',        'Revisar y resolver solicitudes estudiantiles', NOW());

INSERT INTO permission_role (role_id, permission_id, created_at) VALUES
  -- Administrador: todos los permisos
  (1, 1, NOW()), (1, 2, NOW()), (1, 3, NOW()), (1, 4, NOW()),
  (1, 5, NOW()), (1, 6, NOW()), (1, 7, NOW()), (1, 8, NOW()),
  (1, 9, NOW()), (1, 10, NOW()), (1, 11, NOW()), (1, 12, NOW()),
  (1, 13, NOW()), (1, 14, NOW()), (1, 15, NOW()), (1, 16, NOW()),
  -- Coordinadora de Docencia
  (2, 1, NOW()), (2, 3, NOW()), (2, 4, NOW()), (2, 6, NOW()),
  (2, 8, NOW()), (2, 9, NOW()), (2, 10, NOW()), (2, 11, NOW()),
  (2, 12, NOW()), (2, 13, NOW()), (2, 14, NOW()), (2, 16, NOW()),
  -- Docente
  (3, 6, NOW()), (3, 9, NOW()),
  -- Consulta
  (4, 6, NOW()),
  -- Director de Carrera: oferta, planes y resoluciones de su propia carrera
  (5, 3, NOW()), (5, 6, NOW()), (5, 8, NOW()), (5, 9, NOW()),
  (5, 10, NOW()), (5, 13, NOW()), (5, 14, NOW()),
  -- Coordinador CONTA: lectura + consolidación de su área
  (6, 6, NOW()), (6, 9, NOW()), (6, 12, NOW()),
  -- Recursos Humanos: solo lectura de la oferta consolidada
  (7, 6, NOW()), (7, 9, NOW()),
  -- Estudiante: presenta solicitudes y adjunta documentos
  (8, 8, NOW()), (8, 15, NOW()),
  -- Comisión Técnica: revisa solicitudes de Convalidación
  (9, 9, NOW()), (9, 16, NOW());

-- =====================================================================
-- NOTA DE ALCANCE
-- Fase 2 (pendiente de RRHH): nombramientos, continuidad/estabilidad,
-- jornada de derecho y licencias docentes. `estimated_workload` es indicativa.
-- Población estudiantil real diferida; el expediente simulado sí aplica.
-- En capa de aplicación por diseño: solapamiento de intervalos [inicio, fin),
-- ciclos de equiparación y adjuntos obligatorios.
-- =====================================================================

-- =====================================================================
-- GLOSARIO DE TRADUCCIÓN (fuente en español -> inglés)
-- gestion_academica_utn        -> utn_academic_management
-- carreras                     -> degree_programs
-- unidades_ejecutoras          -> executing_units
-- metas                        -> budget_targets
-- periodos_academicos          -> academic_terms
-- recintos                     -> campuses
-- aulas                        -> classrooms
-- equipamientos                -> equipment_items
-- aula_equipamiento            -> classroom_equipment_item
-- modalidades                  -> delivery_modes
-- cursos                       -> courses
-- resoluciones_modalidad       -> delivery_mode_resolutions
-- planes_estudio               -> study_plans
-- niveles                      -> levels
-- curso_nivel                  -> course_level
-- requisitos                   -> prerequisites
-- equiparaciones               -> course_equivalences
-- estudiantes                  -> students
-- estudiante_plan              -> student_study_plan
-- historial_academico          -> academic_records
-- especialidades               -> specialties
-- catalogos_atinencia          -> suitability_catalogs
-- catalogo_atinencia_especialidad -> suitability_catalog_specialty
-- puestos                      -> job_positions
-- docentes                     -> instructors
-- atestados                    -> credentials
-- grupos                       -> sections
-- carrera_grupo                -> degree_program_section
-- grupo_estados_historial      -> section_status_history
-- horarios                     -> schedules
-- asignaciones_docentes        -> teaching_assignments
-- asignacion_cambios           -> assignment_changes
-- verificaciones_atinencia     -> suitability_checks
-- reservas_aulas               -> classroom_reservations
-- reglas_levantamiento         -> waiver_rules
-- convalidaciones_historicas   -> transfer_credit_precedents
-- solicitudes                  -> student_requests
-- solicitud_estados_historial  -> student_request_status_history
-- archivos                     -> attachments
-- notas_tecnicas                -> technical_notes
-- descargas_archivos           -> attachment_downloads
-- auditorias                   -> audits
--
-- Términos de dominio usados en identificadores y comentarios:
--   "atinencia" -> suitability (si el atestado de un docente lo habilita
--   para impartir un curso); "levantamiento de requisito" -> prerequisite
--   waiver; "convalidación" -> transfer credit; "grupo" -> section;
--   "meta" -> budget target; "recinto" -> campus; "atestado" -> credential.
-- =====================================================================

-- =====================================================================
-- DÓNDE SE USA CADA IDIOMA
--
-- Español (valores almacenados y los comentarios que los describen):
--   classrooms.type                   Aula regular, Laboratorio de cómputo,
--                                     Laboratorio de ciencias, Laboratorio de
--                                     idiomas, Auditorio, Otro
--   courses.lab_type                  las mismas tres etiquetas de laboratorio
--   study_plans.classification        Vigente, Terminal
--   course_equivalences.direction     Anterior a nuevo, Nuevo a anterior,
--                                     Bidireccional
--   course_equivalences.status        Vigente, Sustituida
--   academic_records.status           Aprobado, Reprobado, Acreditado por
--                                     equiparación, Acreditado por
--                                     convalidación, Requisito levantado
--   credentials.degree_level          Diplomado, Bachillerato, Licenciatura,
--                                     Maestría, Doctorado
--   sections.status                   Necesidad solicitada, Borrador, Enviado
--                                     al CONTA, Consolidado, Enviado a RRHH,
--                                     Confirmado por RRHH, Cerrado
--   schedules.day_of_week             Lunes ... Domingo
--   teaching_assignments.appointment_type   Interino, Propiedad
--   teaching_assignments.status       Propuesta, Confirmada, Rechazada
--   assignment_changes.change_type    Docente, Horario, Aula, Jornada,
--                                     Estado, Otro
--   suitability_checks.result         Atinente, No Atinente, Nota técnica,
--                                     Sin catálogo
--   classroom_reservations.type       Reserva, Bloqueo administrativo
--   classroom_reservations.days_of_week     Lunes ... Domingo
--   classroom_reservations.status     Solicitada, Aprobada, Rechazada,
--                                     Cancelada
--   waiver_rules.type                 Requisito aprobado con nota mínima,
--                                     Créditos o cursos acumulados,
--                                     Pertenencia a plan terminal,
--                                     Siempre revisión manual
--   transfer_credit_precedents.outcome      Aprobada, Denegada
--   student_requests.type             Levantamiento de requisito, Convalidación
--   student_requests.engine_result    Procede automáticamente, No procede,
--                                     Requiere revisión manual
--   student_requests.status           Pendiente de revisión, En revisión,
--                                     Aprobada, Denegada
--   technical_notes.status            Ratificación pendiente, Ratificada,
--                                     Vencida, Rechazada
--   audits.action                     Creación, Modificación, Eliminación
--   Datos semilla                     degree_programs, executing_units,
--                                     budget_targets, campuses,
--                                     delivery_modes, equipment_items,
--                                     roles (nombres y descripciones),
--                                     descripciones de permisos
--
-- Inglés: nombres de la base de datos, tablas, columnas, llaves, índices y
-- restricciones; y los slugs de permisos como offering.manage.
-- =====================================================================

-- =====================================================================
-- CORRECCIONES LÓGICAS APLICADAS AL ORIGINAL EN ESPAÑOL
-- 1. classrooms: el nombre ahora es único por recinto en vez de global.
--    Dos recintos pueden tener cada uno un aula llamada "A-01".
-- 2. courses: se agregó chk_courses_lab_type para que requires_lab y
--    lab_type sean siempre consistentes (un curso de laboratorio debe
--    indicar su tipo, y solo un curso de laboratorio puede tener uno).
-- 3. course_equivalences: una fila 'Sustituida' debe referenciar a la
--    equiparación que la reemplaza y una fila 'Vigente' no debe hacerlo.
--    (La auto-sustitución queda a cargo de la capa de aplicación: MySQL 8
--    no permite expresiones CHECK que referencien una columna AUTO_INCREMENT.)
-- 4. suitability_checks: el resultado 'Sin catálogo' es el único compatible
--    con suitability_catalog_id NULL.
-- 5. classroom_reservations: 'Reserva' exige solicitante,
--    'Bloqueo administrativo' no debe tenerlo.
-- 6. waiver_rules: cada tipo de regla debe llevar exactamente los
--    parámetros que necesita, y una regla no puede tener como requisito
--    al propio curso.
-- 7. student_requests: las solicitudes de convalidación exigen
--    source_institution y external_course; las de levantamiento exigen
--    prerequisite_course_id.
-- 8. technical_notes: solo una nota 'Ratificada' lleva un valor en ratified_at.
-- 9. Rangos de vigencia: se agregaron validaciones fin >= inicio en
--    academic_terms, delivery_mode_resolutions y suitability_catalogs.
-- 10. Rangos de valor: se agregaron validaciones positivas en levels.number,
--    course_level.credits, sections.capacity, classroom_equipment_item.
--    quantity e instructors.estimated_workload, además de un límite 0-100
--    en academic_records.grade.
-- 11. Las acciones ON UPDATE se normalizaron a CASCADE en todas las llaves
--    foráneas; el original mezclaba RESTRICT y CASCADE para llaves
--    sustitutas equivalentes.
-- 12. Se renombró `grupos` como `sections` en vez de `groups`: GROUPS es
--    palabra reservada en MySQL 8 y exigiría comillas invertidas en todas partes.
-- 13. Todas las restricciones CHECK se reescribieron para comparar contra
--    las etiquetas ENUM en español que realmente se almacenan (por ejemplo
--    'Sin catálogo', 'Convalidación', 'Vigente'), de modo que las
--    restricciones sigan vigentes.
-- =====================================================================
