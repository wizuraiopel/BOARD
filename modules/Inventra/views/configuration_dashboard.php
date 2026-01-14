<div class="config-shell">
    <h2>Configuration — Roles & Permissions</h2>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/configuration.css">

    <section style="margin-top:12px;">
        <h3>Create Role</h3>
        <form id="create-role-form">
            <input type="hidden" name="_wpnonce" value="<?= htmlspecialchars($csrf_token) ?>">
            <label>Name: <input type="text" name="name" required></label>
            <label>Slug: <input type="text" name="slug" required placeholder="e.g. superadmin"></label>
            <label>Description: <input type="text" name="description"></label>
            <button type="submit">Create Role</button>
        </form>
        <div id="create-role-result"></div>
    </section>

    <hr>

    <section>
        <h3>Users</h3>
        <form id="create-user-form">
            <input type="hidden" name="_wpnonce" value="<?= htmlspecialchars($csrf_token) ?>">
            <label>Username: <input type="text" name="username" required></label>
            <label>Email: <input type="email" name="email"></label>
            <label>Password: <input type="password" name="password" required></label>
            <label>Assign Roles: <select name="roles[]" multiple style="min-width:220px;">
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?> (<?= htmlspecialchars($r['slug']) ?>)</option>
                <?php endforeach; ?>
            </select></label>
            <button type="submit">Create User</button>
        </form>
        <div id="create-user-result"></div>

        <div style="margin-top:18px">
            <h4>All Users</h4>
            <table border="1" cellpadding="6" cellspacing="0" id="users-table">
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>User Type</th><th>Roles</th><th>Actions</th></tr></thead>
                <tbody id="users-tbody"></tbody>
            </table>
        </div>
    </section>

    <hr>

    <section>
        <h3>Assign Permissions</h3>
        <label>Select Role: 
            <select id="role-select">
                <option value="0">-- Select Role --</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?> (<?= htmlspecialchars($r['slug']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>

        <div id="permissions-area" style="margin-top:12px;display:none">
            <form id="permissions-form">
                <input type="hidden" name="_wpnonce" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="role_id" id="permissions-role-id" value="0">
                <table border="1" cellpadding="6" cellspacing="0">
                    <thead>
                        <tr><th>Module</th><th>Feature</th><th>C</th><th>R</th><th>U</th><th>D</th></tr>
                    </thead>
                    <tbody id="permissions-tbody">
                        <?php foreach ($modules as $m): ?>
                            <?php foreach ($m['features'] as $f): ?>
                                <tr data-module="<?= htmlspecialchars($m['module_key']) ?>" data-feature="<?= htmlspecialchars($f['feature_key']) ?>">
                                    <td><?= htmlspecialchars($m['module_name']) ?></td>
                                    <td><?= htmlspecialchars($f['feature_name']) ?></td>
                                    <td><input type="checkbox" data-flag="c"></td>
                                    <td><input type="checkbox" data-flag="r"></td>
                                    <td><input type="checkbox" data-flag="u"></td>
                                    <td><input type="checkbox" data-flag="d"></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top:12px">
                    <button type="submit">Save Permissions</button>
                </div>
            </form>
            <div id="permissions-result"></div>
        </div>
    </section>

    <hr>

    <section>
        <h3>Recent Configuration Audit</h3>
        <div id="audit-list" style="max-height:240px;overflow:auto;background:#fafafa;padding:8px;border-radius:6px;border:1px solid #eee"></div>
    </section>

</div>

<script>
    (function(){
        const roleSelect = document.getElementById('role-select');
        const permissionsArea = document.getElementById('permissions-area');
        const permissionsTbody = document.getElementById('permissions-tbody');
        const permissionsForm = document.getElementById('permissions-form');
        const createUserForm = document.getElementById('create-user-form');

        document.getElementById('create-role-form').addEventListener('submit', function(e){
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('ajax_action', 'create_role');
            fetch('<?= BASE_URL ?>/index.php?action=configuration_ajax', {method:'POST', body:fd})
                .then(r=>r.json()).then(js=>{
                    document.getElementById('create-role-result').innerText = js.message || (js.success ? 'Role created' : 'Failed');
                    if (js.success) location.reload();
                });
        });

        roleSelect.addEventListener('change', function(){
            const id = this.value;
            if (!id || id == 0) { permissionsArea.style.display = 'none'; return; }
            document.getElementById('permissions-role-id').value = id;
            const fd = new FormData();
            fd.append('_wpnonce', '<?= htmlspecialchars($csrf_token) ?>');
            fd.append('ajax_action', 'get_permissions');
            fd.append('role_id', id);
            fetch('<?= BASE_URL ?>/index.php?action=configuration_ajax', {method:'POST', body:fd})
                .then(r=>r.json()).then(js=>{
                    // clear all
                    document.querySelectorAll('#permissions-tbody tr').forEach(tr=>{
                        tr.querySelectorAll('input[type=checkbox]').forEach(cb=>cb.checked=false);
                    });
                    if (js.success && js.permissions) {
                        for (let mk in js.permissions) {
                            for (let fk in js.permissions[mk]) {
                                const flags = js.permissions[mk][fk];
                                const tr = document.querySelector('#permissions-tbody tr[data-module="'+mk+'"][data-feature="'+fk+'"]');
                                if (!tr) continue;
                                tr.querySelector('input[data-flag="c"]').checked = !!flags.c;
                                tr.querySelector('input[data-flag="r"]').checked = !!flags.r;
                                tr.querySelector('input[data-flag="u"]').checked = !!flags.u;
                                tr.querySelector('input[data-flag="d"]').checked = !!flags.d;
                            }
                        }
                    }
                    permissionsArea.style.display = 'block';
                });
        });

        // Create user handler
        createUserForm.addEventListener('submit', function(e){
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('ajax_action', 'add_user');
            fetch('<?= BASE_URL ?>/index.php?action=configuration_ajax', {method:'POST', body:fd})
                .then(r=>r.json()).then(js=>{
                    document.getElementById('create-user-result').innerText = js.success ? ('User created (id: '+(js.user_id||'?')+')') : ('Failed: '+(js.message||''));
                    if (js.success) {
                        this.reset();
                        loadUsers();
                    }
                });
        });

        function loadUsers(){
            const fd = new FormData();
            fd.append('_wpnonce','<?= htmlspecialchars($csrf_token) ?>');
            fd.append('ajax_action','list_users');
            fetch('<?= BASE_URL ?>/index.php?action=configuration_ajax',{method:'POST', body:fd})
                .then(r=>r.json()).then(js=>{
                    const tbody = document.getElementById('users-tbody');
                    tbody.innerHTML = '';
                    if (js.success && js.users) {
                        js.users.forEach(u=>{
                            const tr = document.createElement('tr');
                            tr.innerHTML = '<td>'+u.id+'</td><td>'+u.username+'</td><td>'+ (u.email||'') +'</td><td>'+ (u.user_type||'') +'</td>'+
                                '<td><select multiple data-userid="'+u.id+'" style="min-width:160px">' +
                                js.roles.map(r=>'<option value="'+r.id+'" '+( (u.role_slugs || '').split(',').indexOf(r.slug) !== -1 ? 'selected' : '' )+'> '+r.name+' ('+r.slug+') </option>').join('') +
                                '</select></td>'+
                                '<td><button data-userid="'+u.id+'" class="save-roles-btn">Save Roles</button> <a href="<?= BASE_URL ?>/index.php?action=manage_users" style="margin-left:8px;">Manage</a></td>';
                            tbody.appendChild(tr);
                        });
                        // attach handlers
                        document.querySelectorAll('.save-roles-btn').forEach(btn=>{
                            btn.addEventListener('click', function(){
                                if (!confirm('Confirm: update roles for this user?')) return;
                                const uid = this.getAttribute('data-userid');
                                const sel = document.querySelector('select[data-userid="'+uid+'"]');
                                const selected = Array.from(sel.selectedOptions).map(o=>o.value);
                                const fd2 = new FormData();
                                fd2.append('_wpnonce','<?= htmlspecialchars($csrf_token) ?>');
                                fd2.append('ajax_action','update_user_roles');
                                fd2.append('user_id', uid);
                                selected.forEach(v=>fd2.append('roles[]', v));
                                fetch('<?= BASE_URL ?>/index.php?action=configuration_ajax',{method:'POST', body:fd2}).then(r=>r.json()).then(js2=>{
                                    alert(js2.success ? 'Roles updated' : ('Failed: '+(js2.message||'')));
                                    loadUsers();
                                });
                            });
                        });
                    }
                });
        }

        // initial load
        loadUsers();

        // Audit log section
        function loadAudit(){
            const fd = new FormData();
            fd.append('_wpnonce','<?= htmlspecialchars($csrf_token) ?>');
            fd.append('ajax_action','list_audit');
            fetch('<?= BASE_URL ?>/index.php?action=configuration_ajax',{method:'POST', body:fd}).then(r=>r.json()).then(js=>{
                const el = document.getElementById('audit-list');
                if (!el) return;
                el.innerHTML = '';
                if (js.success && js.logs) {
                    js.logs.forEach(l=>{
                        const d = document.createElement('div');
                        d.style.marginBottom = '8px';
                        d.innerText = l.created_at + ' — ' + (l.action||'') + ' — User: '+(l.user_id||'')+' by '+(l.changed_by||'')+' — '+(l.details||'');
                        el.appendChild(d);
                    });
                }
            });
        }
        loadAudit();

        permissionsForm.addEventListener('submit', function(e){
            e.preventDefault();
            const role_id = document.getElementById('permissions-role-id').value;
            const permissions = {};
            document.querySelectorAll('#permissions-tbody tr').forEach(tr=>{
                const mk = tr.getAttribute('data-module');
                const fk = tr.getAttribute('data-feature');
                const c = tr.querySelector('input[data-flag="c"]').checked ? 1 : 0;
                const r = tr.querySelector('input[data-flag="r"]').checked ? 1 : 0;
                const u = tr.querySelector('input[data-flag="u"]').checked ? 1 : 0;
                const d = tr.querySelector('input[data-flag="d"]').checked ? 1 : 0;
                if (!permissions[mk]) permissions[mk] = {};
                permissions[mk][fk] = {c:c,r:r,u:u,d:d};
            });

            const fd = new FormData();
            fd.append('_wpnonce', '<?= htmlspecialchars($csrf_token) ?>');
            fd.append('ajax_action', 'save_permissions');
            fd.append('role_id', role_id);
            fd.append('permissions', JSON.stringify(permissions));

            fetch('<?= BASE_URL ?>/index.php?action=configuration_ajax', {method:'POST', body:fd})
                .then(r=>r.json()).then(js=>{
                    document.getElementById('permissions-result').innerText = js.success ? 'Permissions saved.' : ('Failed: '+(js.message||''));
                });
        });
    })();
</script>