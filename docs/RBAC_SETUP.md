# RBAC Setup & Usage

This document explains how to enable the new Roles & Permissions functionality added to the project.

1) Apply DB changes

- Run the SQL statements included in `CONFIGURATION.php` under the "RBAC / ACCESS CONTROL" section. Those statements will:
  - Create `roles`, `role_user`, `modules`, `module_features`, and `role_permissions` tables (and a `users` statement if needed)
  - Seed the default roles: `superadmin`, `sysadmin`, `operation`, `compliance`, `manager`, `monitor`
  - Seed example modules and features used by the UI (Inventra, CashOps, KPI, Configuration)

Run the SQL in your MySQL client or via your migration tooling. Example:

  mysql -u <user> -p <database> < rbac_seed.sql

2) Grant roles to users

- Assign an existing user a role (example):

  INSERT INTO role_user (role_id, user_id)
  VALUES ((SELECT id FROM roles WHERE slug = 'superadmin'), (SELECT id FROM users WHERE username = 'admin'));

3) Access the Configuration UI

- Log in as a user assigned to the **superadmin** or **sysadmin** roles. The Configuration UI is restricted to those roles only.
- Open: `/index.php?action=configuration`

You will be able to:
- Create new roles (name + slug + description) — requires the role permission `configuration:roles:create` (or `user_type = 'admin'`).
- Select a role and set CRUD flags per module feature (including the new `configuration` features below)
- Add new users (requires the role permission `configuration:users:create`)

Granting Manager the ability to use Configuration and assign permissions

If you want members of the **Manager** role to access parts of the Configuration UI and manage permissions, do the following (example SQL):

1. Ensure the configuration features exist (seed SQL in `CONFIGURATION.php` inserts `roles`, `permissions`, and `users`).

2. Grant Manager `read` and `update` on the `permissions` feature and `create` on `users` as needed:

```sql
-- Find Manager role id
SELECT id FROM roles WHERE slug = 'manager';

-- Replace <manager_id> with the returned id
INSERT INTO role_permissions (role_id, module_key, feature_key, can_create, can_read, can_update, can_delete)
VALUES (<manager_id>, 'configuration', 'permissions', 0, 1, 1, 0),
       (<manager_id>, 'configuration', 'users', 1, 1, 1, 0);
```

Note: The Configuration UI overall remains restricted to users assigned to the **superadmin** or **sysadmin** roles per your request. However, within that UI, you can grant Managers access to specific features (like creating users or editing permissions) so they can perform those actions without making them full Configuration admins.
After this, users assigned the Manager role will be able to open `/index.php?action=configuration`, view and update permissions, and add users (depending on the flags you set).

Audit logging & Manage Users

- All role changes are recorded in the `role_change_audit` table. Each audit row contains: `id`, `role_id`, `user_id`, `changed_by`, `action` (assign|remove|create), `details` (JSON string), and `created_at`.
- You can view recent role-change activity from the Configuration dashboard's **Audit** tab or by querying the table directly:

  SELECT * FROM role_change_audit ORDER BY created_at DESC LIMIT 100;

- The Configuration module also includes a **Manage Users** page at `/index.php?action=manage_users` (requires `superadmin` or `sysadmin`) that provides search and pagination for users and bulk role assignment.

If you want, I can add a one-click SQL migration file to apply these sample grants automatically.

I added a ready-to-run seed file at `db/seed/sample_seed_data.sql` with sample users (superadmin, sysadmin, manager, operation, compliance, monitor, branch), roles, modules/features, example role_permissions, and demo inventory data. Run it on your development DB with:

mysql -u <user> -p <database> < db/seed/sample_seed_data.sql

4) Verify / Troubleshooting

- If the Configuration menu does not appear in the header, make sure you have updated `views/layout/main.php` (it should contain a `Configuration` link). The code changes were applied and the link points to `?action=configuration`.
- If modules/features are empty, verify the `modules` and `module_features` tables contain rows (seed SQL inserts these).
- Check `modules/Inventra/controllers/ConfigurationController.php` and the `modules/Inventra/models/{Role,Permission}.php` for server-side behavior (AJAX endpoints at `?action=configuration_ajax`).

5) Security notes

- The controller enforces a **role-based** check: the logged-in user must belong to either the `superadmin` or `sysadmin` role to access the Configuration UI and most AJAX actions. For backward compatibility, users with `user_type = 'admin'` are also allowed access. Individual features (create/read/update/delete per module feature) are gated by entries in `role_permissions` and helper methods in `modules/Inventra/models/Permission.php` (e.g., `userHasPermission`, `userHasAnyPermission`).
- Always run SQL in a safe environment and back up your database before applying schema changes.

---

If you want, I can:
- Add a migration script file and a CLI helper to run the SQL automatically
- Extend the controller to check role membership rather than `user_type === 'admin'`
- Add a small unit/integration test for the CRUD permission endpoints

Which of these would you like next?