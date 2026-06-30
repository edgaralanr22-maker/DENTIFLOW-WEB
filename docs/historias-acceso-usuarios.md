# Historias De Usuario: Acceso, Sesion Y Usuarios

Documento de referencia para identificar que existe en el backend/base de datos, que relaciones usa y que queda pendiente.

## HU-001 Login

**Historia:** Como usuario quiero iniciar sesion para acceder de forma segura al sistema.

**Estado actual:** Implementado en backend.

**Capas usadas:**
- Ruta GET `/login`: muestra el formulario segun rol (`admin`, `doctor`, `asistente`).
- Ruta POST `/login`: valida `role`, `email` y `password`, busca el usuario por correo y crea datos de sesion.
- Modelo `User`: consulta la tabla `users`.
- Tabla `sessions`: Laravel la usa para guardar la sesion cuando el driver es `database`.

**Datos de sesion creados:**
- `access_role`
- `access_user_id`
- `access_name`
- `access_email`

**Relaciones usadas:**
- `User` puede tener un `Dentist` mediante `User::dentist()`.
- El rol esta guardado en `users.role` y se compara contra el tipo de acceso seleccionado.

**Validacion importante:**
- La contrasena se valida con hash.
- El rol de la cuenta debe coincidir con el rol elegido en el login.

## HU-002 Cerrar Sesion

**Historia:** Como usuario quiero cerrar sesion para proteger mi cuenta.

**Estado actual:** Implementado en backend.

**Capas usadas:**
- Ruta POST `/logout`.
- Sesion Laravel.

**Funcionamiento actual:**
- Elimina de sesion `access_role`, `access_user_id`, `access_name` y `access_email`.
- Invalida la sesion y regenera el token CSRF.
- Redirige al selector de rol.

## HU-003 Recuperacion De Contrasena

**Historia:** Como usuario quiero recuperar mi contrasena si la olvido.

**Estado actual:** Implementado en backend.

**Base disponible:**
- Tabla `password_reset_tokens` existe en la migracion inicial de usuarios.
- Configuracion de correo existe en `.env`, actualmente con `MAIL_MAILER=log`.

**Rutas backend:**
- POST `/forgot-password`: envia/genera token de recuperacion.
- GET `/reset-password/{token}`: expone endpoint de token para integracion con vista.
- POST `/reset-password`: valida token y guarda nueva contrasena.

**Pendiente frontend:**
- Conectar el enlace "Olvidaste?" a una pantalla/formulario real.

## HU-004 Cambio De Contrasena

**Historia:** Como usuario quiero cambiar mi contrasena desde mi perfil.

**Estado actual:** Implementado en backend.

**Capas existentes relacionadas:**
- Ruta GET `/perfil/editar`.
- Ruta POST `/perfil`.
- Ruta POST `/perfil/password`.
- `ProfileController::update()` permite cambiar nombre, email, telefono y especialidad/puesto.
- `ProfileController::updatePassword()` valida contrasena actual y guarda la nueva.
- Modelo `User` ya tiene cast `password => hashed`.

**Pendiente frontend:**
- Agregar formulario visual para capturar contrasena actual, nueva contrasena y confirmacion.

## HU-005 Dashboard / Sesiones Activas Por Rol

**Historia:** Como sistema quiero validar sesiones activas para controlar el acceso por rol.

**Estado actual:** Implementado en backend.

**Capas usadas:**
- Middleware `EnsureRoleAccess`.
- Registro del middleware en `bootstrap/app.php`.
- `DashboardController::index()` muestra dashboard distinto para `admin` y usuarios operativos.

**Funcionamiento actual:**
- Si no existe `access_role`, redirige al selector de rol.
- Si existe rol, compara el nombre de la ruta contra permisos por rol.
- Admin accede a `admin.*`, `inicio`, `perfil*`, `logout`.
- Doctor accede a citas, pacientes, reportes, calendario, perfil.
- Asistente accede a citas, pacientes limitados, inventario, calendario, perfil.

**Mejora aplicada:**
- `users.role` guarda el rol real y el login solo crea sesion si el rol coincide.

## HU-006 Consulta De Usuarios Desde Admin

**Historia:** Como administrador quiero ver los usuarios registrados sin poder crear cuentas nuevas por rol.

**Estado actual:** Listado implementado; creacion desde admin desactivada.

**Capas actuales:**
- `GET /administracion/usuarios` lista usuarios registrados.
- `GET /administracion/usuarios/{user}/editar` permite editar nombre/correo.
- `PUT /administracion/usuarios/{user}` actualiza datos basicos sin cambiar rol.
- No existe `POST /administracion/usuarios`; el admin no puede crear usuarios por rol desde esta pantalla.
- El menu de admin muestra `Usuarios`.
- Los usuarios base se crean por migracion/seeder o por el flujo publico de registro.
- Tabla `users` guarda nombre, email, password y rol.
- Relacion `User::dentist()` vincula usuario doctor con dentista cuando corresponde.

## Tablas Y Relaciones Involucradas

**users**
- Guarda cuentas base.
- Campos principales: `name`, `email`, `password`, `telefono`, `puesto`.
- Campo de acceso: `role`.
- Relacion: `hasOne(Dentist::class)`.

**dentists**
- Guarda perfil clinico del doctor.
- Campo `user_id` vincula un dentista con un usuario.
- Relacion: `belongsTo(User::class)`.

**sessions**
- Guarda sesiones activas cuando Laravel usa driver de sesion en base de datos.

**password_reset_tokens**
- Base preparada para recuperacion de contrasena, pero aun sin flujo implementado.

## Prioridad Recomendada

1. Conectar formularios frontend para recuperacion/cambio de contrasena.
2. Revisar si el registro publico debe permanecer activo para todos los roles o limitarse.
3. Revisar permisos finos por rol en cada modulo.
