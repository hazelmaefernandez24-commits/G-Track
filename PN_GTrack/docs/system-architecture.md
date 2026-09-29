# GTrack System Architecture Diagram

GTrack is a Laravel 13 application with a web dashboard for administrators and API endpoints for the student mobile client. The architecture below reflects the current project configuration and route structure.

## Runtime Architecture

```mermaid
flowchart TB
    subgraph Clients[Client Layer]
        BROWSER[Admin browser]
        MOBILE[Student mobile app]
    end

    subgraph Web[HTTP and Presentation Layer]
        SERVER[Laravel application server\nPHP 8.3]
        WEBROUTES[Web routes\nroutes/web.php]
        APIROUTES[API routes\nroutes/api.php]
        VIEWS[Blade views\nresources/views]
        ASSETS[Compiled frontend assets\nVite + Tailwind + Axios]
    end

    subgraph Application[Application Layer]
        AUTH[Authentication\nAuthController + guards]
        MIDDLEWARE[Middleware\nauth:admin + admin.role]
        CONTROLLERS[Controllers\nstudent, device, location, notification, management]
        MODELS[Eloquent models and relationships]
        VALIDATION[Request validation and business rules]
    end

    subgraph Persistence[Persistence Layer]
        DB[(Configured database\nSQLite or MySQL/MariaDB/PostgreSQL/SQL Server)]
        FILES[(File storage\nlocal/public or S3)]
        MIGRATIONS[Database migrations]
    end

    BROWSER -->|HTTPS web requests| SERVER
    MOBILE -->|HTTPS JSON API requests| SERVER
    SERVER --> WEBROUTES
    SERVER --> APIROUTES
    WEBROUTES --> MIDDLEWARE
    APIROUTES --> MIDDLEWARE
    MIDDLEWARE --> AUTH
    MIDDLEWARE --> CONTROLLERS
    CONTROLLERS --> VALIDATION
    VALIDATION --> MODELS
    AUTH --> MODELS
    MODELS --> DB
    CONTROLLERS --> FILES
    CONTROLLERS --> VIEWS
    VIEWS --> BROWSER
    ASSETS --> BROWSER
    MIGRATIONS --> DB
```

## Main Runtime Components

| Layer | Components | Responsibility |
| --- | --- | --- |
| Client | Admin browser, student mobile app | Presents the dashboard/mobile experience and sends web or JSON requests. |
| HTTP entry points | `routes/web.php`, `routes/api.php` | Maps browser and mobile requests to application actions. |
| Access control | Admin auth guard, `auth:admin`, `admin.role` | Authenticates users and restricts management actions to permitted admin roles. |
| Application services | Controllers, validation, Eloquent models | Processes authentication, student management, classes, tracking, notifications, SOS, and media uploads. |
| Presentation | Blade views, Vite, Tailwind, Axios | Renders the admin UI and builds/loads frontend assets. |
| Database | Laravel-configured relational database | Stores admins, students, classes, locations, notifications, student auth records, and framework tables. |
| File storage | Local/public disk or S3 disk | Stores uploaded profile pictures and notification media references/files. |

## Request Flows

### Admin dashboard request

```mermaid
sequenceDiagram
    participant A as Admin browser
    participant R as Web routes
    participant M as Admin middleware
    participant C as Device/Notification controller
    participant E as Eloquent models
    participant D as Database
    participant V as Blade view

    A->>R: Request dashboard, tracking, activity, or notifications
    R->>M: Apply auth:admin and role checks
    M->>C: Dispatch authorized request
    C->>E: Query students, locations, and notifications
    E->>D: Execute database queries
    D-->>E: Return records and aggregates
    E-->>C: Return domain data
    C->>V: Render view with data
    V-->>A: HTML dashboard response
```

### Student mobile status or SOS request

```mermaid
sequenceDiagram
    participant S as Student mobile app
    participant R as API routes
    participant C as Student/Location controller
    participant E as Eloquent models
    participant D as Database
    participant F as File storage

    S->>R: Login, heartbeat, location, SOS, or media request
    R->>C: Dispatch JSON request
    C->>C: Validate payload and student identity
    C->>E: Update student status or create records
    E->>D: Persist student, location, or notification data
    opt Media upload
        C->>F: Store profile picture or media
        F-->>C: Return stored path/URL
        C->>D: Persist media reference
    end
    D-->>E: Confirm persistence
    E-->>C: Return result
    C-->>S: JSON response
```

## Build and Deployment View

```mermaid
flowchart LR
    SOURCE[Application source\nPHP, Blade, CSS, JavaScript]
    COMPOSER[Composer install\nPHP dependencies]
    NPM[npm run build\nVite production assets]
    APP[Laravel application server]
    DB[(Configured relational database)]
    STORAGE[(Configured filesystem)]

    SOURCE --> COMPOSER
    SOURCE --> NPM
    COMPOSER --> APP
    NPM --> APP
    APP --> DB
    APP --> STORAGE
```

The development script can run the Laravel server, queue listener, log viewer, and Vite development server together. Production database and storage selection are controlled through environment variables such as `DB_CONNECTION`, `DB_DATABASE`, `FILESYSTEM_DISK`, and the related connection/storage settings.

## Source References

- `composer.json`: PHP and Laravel versions, Composer scripts, and development processes
- `package.json`: Vite, Tailwind, Axios, and frontend commands
- `vite.config.js`: frontend entry points and Laravel Vite integration
- `bootstrap/app.php`: web/API route registration and middleware aliases
- `config/database.php`: supported database connections and default selection
- `config/filesystems.php`: local, public, and S3 filesystem disks
- `routes/web.php` and `routes/api.php`: user-facing and mobile/API request boundaries
