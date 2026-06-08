# ProAssist API

[![CI](https://github.com/szwolinski/proassist-task/actions/workflows/ci.yml/badge.svg)](https://github.com/szwolinski/proassist-task/actions/workflows/ci.yml)

This repository contains a recruitment task implementation for an API system managing Tickets, Devices, and Technicians.

## Architecture & Approach
A Layered Architecture was chosen over Clean/Hexagonal Architecture. In the context of this project, strictly decoupling the business logic from the framework does not provide sufficient value and would only unnecessarily overcomplicate the development process.

The project combines two architectural approaches to optimize development speed while maintaining clean core business logic:
* **API Platform (Rapid CRUD):** Classic, basic approach for fast database operations for side domains, such as **Technician** and **Device**.
* **CQRS:** Advanced business logic for our core domain: **Tickets**. Write operations (status changes, assignments, history) and read operations are clearly decoupled.

## Prerequisites

The entire project is fully containerized. All you need on your machine is:
* **Docker**
* **Docker Compose**
* **Make** (to use commands from the `Makefile`)

## Quick Start (One-Click Setup)

Thanks to environment variables overridden directly in `docker-compose.yaml`, the application works out-of-the-box without the need to manually create `.env.local` files or configure passwords.

1. Clone the repository.
2. Initialize the project from the root directory:
   ```bash
   make init
   ```
   *(This command builds the images, starts containers in the background, and runs database migrations).*
3. Load test data (fixtures) into the database:
   ```bash
   make db-fixtures
   ```
4. Done! The application is available at: **http://localhost:8000**

## API Documentation & Bruno Collections

To facilitate working with the API, there are two tools:

1. **Swagger / API Platform UI:**
   Available in your browser immediately after starting the application at:
   -> `http://localhost:8000/api/docs`

2. **Bruno API Collection:**
   Project use **Bruno**, API client. The complete, ready-to-use API collection is located in the directory:
   -> `/api` *(simply open this folder in the Bruno app; the base file is `bruno.json`)*.

---

## Makefile Cheat Sheet

To make daily development tasks easy, I have prepared a set of `make` commands. Simply type `make help` in your terminal to see the full, self-documented list.

### Container Management
| Command        | Description                                           |
|----------------|-------------------------------------------------------|
| `make up`      | Starts the Docker environment in the background.      |
| `make down`    | Stops containers and removes orphan networks/volumes. |
| `make restart` | Performs a quick environment restart (down -> up).    |
| `make logs`    | Displays Docker logs in real-time.                    |

### Database
| Command            | Description                                                                                                                  |
|--------------------|------------------------------------------------------------------------------------------------------------------------------|
| `make db-migrate`  | Executes pending Doctrine migrations.                                                                                        |
| `make db-diff`     | Generates a new migration file based on entity mapping changes.                                                              |
| `make db-fixtures` | Loads test data (fixtures) into the database.                                                                                |
| `make db-reset`    | **Warning!** Drops the DB, recreates it, runs migrations, and loads fixtures. A lifesaver when you break the database state. |

### User Management
* `make create-user email=... password=... role=...` - Create a new user with specified role (ROLE_ADMIN, ROLE_TECHNICIAN).

### Testing & Code Quality (QA)
| Command        | Description                                                                |
|----------------|----------------------------------------------------------------------------|
| `make test`    | Runs PHPUnit tests (stops on the first failure).                           |
| `make phpstan` | Runs static code analysis (configuration loaded from `phpstan.dist.neon`). |
| `make cc`      | Clears the Symfony cache.                                                  |

---

## Production Environment

To build an optimized, secured image stripped of development tools (like Xdebug or require-dev packages) for the production environment, use:
```bash
make prod-build
``` 
