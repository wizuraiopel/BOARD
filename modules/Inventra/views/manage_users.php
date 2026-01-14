<div class="config-shell">
    <h2>Manage Users</h2>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/configuration.css">
    <div class="search-row">
        <input id="user-search" placeholder="Search username or email" style="width:300px;padding:6px;margin-right:8px">
        <button id="search-btn">Search</button>
        <button id="clear-btn">Clear</button>
    </div>

    <div style="margin-top:12px">
        <table border="1" cellpadding="6" cellspacing="0" id="users-table">
            <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>User Type</th><th>Roles</th><th>Actions</th></tr></thead>
            <tbody id="users-tbody"></tbody>
        </table>
        <div id="pagination" style="margin-top:8px"></div>
    </div>
</div>

<script>
(function(){
    const search = document.getElementById('user-search');
    const searchBtn = document.getElementById('search-btn');
    const clearBtn = document.getElementById('clear-btn');
    const perPage = 10;
    let page = 1;

    function load(pageNum=1, q=''){
        page = pageNum;
        const fd = new FormData();
        fd.append('_wpnonce','<?= htmlspecialchars($csrf_token) ?>');
        fd.append('ajax_action','list_users');
        fd.append('page', page);
        fd.append('per_page', perPage);
        fd.append('q', q);
        fetch('<?= BASE_URL ?>/index.php?action=configuration_ajax',{method:'POST', body:fd}).then(r=>r.json()).then(js=>{
            const tbody = document.getElementById('users-tbody');
            tbody.innerHTML = '';
            if (js.success && js.users) {
                js.users.forEach(u=>{
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td>'+u.id+'</td><td>'+u.username+'</td><td>'+ (u.email||'') +'</td><td>'+ (u.user_type||'') +'</td>'+
                        '<td><select multiple data-userid="'+u.id+'" style="min-width:160px">' +
                        js.roles.map(r=>'<option value="'+r.id+'" '+( (u.role_slugs || '').split(',').indexOf(r.slug) !== -1 ? 'selected' : '' )+'> '+r.name+' ('+r.slug+') </option>').join('') +
                        '</select></td>'+
                        '<td><button data-userid="'+u.id+'" class="save-roles-btn">Save Roles</button></td>';
                    tbody.appendChild(tr);
                });
                attachHandlers();
                renderPagination(js.total, js.page, js.per_page);
            }
        });
    }

    function attachHandlers(){
        document.querySelectorAll('.save-roles-btn').forEach(btn=>{
            btn.addEventListener('click', function(){
                const uid = this.getAttribute('data-userid');
                const sel = document.querySelector('select[data-userid="'+uid+'"]');
                const selected = Array.from(sel.selectedOptions).map(o=>o.value);
                // show confirmation modal
                if (!confirm('Confirm: Update roles for user ID '+uid+'?')) return;
                const fd2 = new FormData();
                fd2.append('_wpnonce','<?= htmlspecialchars($csrf_token) ?>');
                fd2.append('ajax_action','update_user_roles');
                fd2.append('user_id', uid);
                selected.forEach(v=>fd2.append('roles[]', v));
                fetch('<?= BASE_URL ?>/index.php?action=configuration_ajax',{method:'POST', body:fd2}).then(r=>r.json()).then(js2=>{
                    alert(js2.success ? 'Roles updated' : ('Failed: '+(js2.message||'')));
                    load(page, search.value.trim());
                });
            });
        });
    }

    function renderPagination(total, page, per_page){
        const pages = Math.max(1, Math.ceil(total / per_page));
        const pdiv = document.getElementById('pagination');
        pdiv.innerHTML = '';
        for (let i=1;i<=pages;i++){
            const b = document.createElement('button');
            b.innerText = i;
            b.disabled = (i===page);
            b.addEventListener('click', ()=>load(i, search.value.trim()));
            pdiv.appendChild(b);
        }
    }

    searchBtn.addEventListener('click', ()=>load(1, search.value.trim()));
    clearBtn.addEventListener('click', ()=>{search.value=''; load(1,'');});

    // initial
    load(1,'');
})();
</script>
