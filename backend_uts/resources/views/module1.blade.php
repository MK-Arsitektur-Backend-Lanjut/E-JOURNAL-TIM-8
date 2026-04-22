<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection & Metadata | Module 1</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --bg: #09090b;
            --bg-surface: #18181b;
            --bg-surface-hover: #27272a;
            --text-main: #f4f4f5;
            --text-muted: #a1a1aa;
            --border: #3f3f46;
            --glow: rgba(99, 102, 241, 0.3);
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .top-nav {
            width: 100%;
            padding: 1.5rem;
            display: flex;
            justify-content: flex-start;
        }

        .back-btn {
            color: var(--text-main);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
            transition: all 0.3s;
            border: 1px solid var(--border);
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.1);
            border-color: var(--text-muted);
        }

        .container {
            width: 100%;
            max-width: 1200px;
            padding: 0 2rem 2rem 2rem;
            margin-top: 1rem;
        }

        .hero {
            text-align: center;
            margin-bottom: 2rem;
        }

        .hero h1 {
            font-size: 2.75rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            color: var(--text-muted);
            font-size: 1.05rem;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .card h2 {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            display: block;
            margin-bottom: 0.4rem;
        }

        input, textarea, select {
            width: 100%;
            background: #09090b;
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 0.75rem 0.9rem;
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            outline: none;
        }

        textarea { min-height: 110px; resize: vertical; }

        input:focus, textarea:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--glow);
        }

        .actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn {
            border: 1px solid var(--border);
            background: rgba(255,255,255,0.06);
            color: var(--text-main);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn.primary {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 8px 20px var(--glow);
        }

        .btn.primary:hover { background: var(--primary-hover); }
        .btn:hover { transform: translateY(-1px); }

        .table-wrap { overflow: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.75rem; border-bottom: 1px solid var(--border); }
        th { font-size: 0.85rem; color: var(--text-muted); }
        td { font-size: 0.95rem; vertical-align: top; }

        .muted { color: var(--text-muted); font-size: 0.85rem; }
        .pill {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            margin-right: 0.35rem;
            margin-top: 0.25rem;
            font-size: 0.8rem;
            background: rgba(255,255,255,0.05);
        }

        .toolbar {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: end;
            margin-bottom: 1rem;
        }

        .toolbar .field { flex: 1; min-width: 160px; }
        .pager { display: flex; gap: 0.5rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }

        @media (max-width: 980px) {
            .grid { grid-template-columns: 1fr; }
            .row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<nav class="top-nav">
    <a href="/" class="back-btn">&larr; Back to Portal</a>
</nav>

<div class="container">
    <div class="hero">
        <h1>Collection & Metadata</h1>
        <p>Manage document catalog, metadata, simulated file uploads, and subject tagging.</p>
    </div>

    <div class="grid">
        <div class="card">
            <h2>Catalog (API)</h2>

            <div class="toolbar">
                <div class="field">
                    <label>Title</label>
                    <input id="fTitle" placeholder="Filter title...">
                </div>
                <div class="field">
                    <label>Author</label>
                    <input id="fAuthor" placeholder="Filter author name...">
                </div>
                <div class="field">
                    <label>Year</label>
                    <input id="fYear" type="number" placeholder="e.g. 2024">
                </div>
                <div class="field">
                    <label>Tag</label>
                    <input id="fTag" placeholder="Filter tag...">
                </div>
                <div class="field">
                    <label>Per page</label>
                    <select id="perPage">
                        <option value="10">10</option>
                        <option value="15" selected>15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <button class="btn primary" id="btnReload">Reload</button>
            </div>

            <div class="muted" id="catalogMeta">Loading...</div>

            <div class="table-wrap" style="margin-top: 0.75rem;">
                <table>
                    <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Title</th>
                        <th style="width: 160px;">Author</th>
                        <th style="width: 90px;">Year</th>
                        <th>Tags</th>
                        <th style="width: 190px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody id="catalogBody"></tbody>
                </table>
            </div>

            <div class="pager">
                <div class="muted" id="pageInfo">-</div>
                <div style="display:flex; gap:0.5rem;">
                    <button class="btn" id="btnPrev">Prev</button>
                    <button class="btn" id="btnNext">Next</button>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Create / Update Document</h2>
            <div class="muted">Upload file is simulated by saving into storage and writing <code>file_path</code> to DB.</div>

            <div style="margin-top: 1rem;" class="row">
                <div>
                    <label>Document ID (for update)</label>
                    <input id="docId" type="number" placeholder="Leave empty for create">
                </div>
                <div>
                    <label>Year</label>
                    <input id="year" type="number" placeholder="e.g. 2024">
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <label>Title</label>
                <input id="title" placeholder="Document title">
            </div>

            <div style="margin-top: 1rem;">
                <label>Abstract</label>
                <textarea id="abstract" placeholder="Abstract..."></textarea>
            </div>

            <div style="margin-top: 1rem;" class="row">
                <div>
                    <label>Author</label>
                    <select id="authorId"></select>
                </div>
                <div>
                    <label>Tags (multi-select)</label>
                    <select id="tagIds" multiple size="5"></select>
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <label>File (optional)</label>
                <input id="file" type="file" />
            </div>

            <div class="actions">
                <button class="btn primary" id="btnCreate">Create</button>
                <button class="btn" id="btnUpdate">Update</button>
                <button class="btn" id="btnClear">Clear</button>
            </div>

            <div style="margin-top: 1rem;" class="muted" id="formMsg"></div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let lastPage = 1;

    function qs(id) { return document.getElementById(id); }

    function selectedMulti(selectEl) {
        return Array.from(selectEl.selectedOptions).map(o => parseInt(o.value, 10));
    }

    async function loadLookups() {
        const [authorsRes, tagsRes] = await Promise.all([
            fetch('/api/v1/authors'),
            fetch('/api/v1/tags')
        ]);
        const authorsJson = await authorsRes.json();
        const tagsJson = await tagsRes.json();

        const authorSelect = qs('authorId');
        authorSelect.innerHTML = '<option value="">(none)</option>';
        (authorsJson.data || []).forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id;
            opt.textContent = a.name;
            authorSelect.appendChild(opt);
        });

        const tagSelect = qs('tagIds');
        tagSelect.innerHTML = '';
        (tagsJson.data || []).forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name;
            tagSelect.appendChild(opt);
        });
    }

    function buildCatalogParams(page) {
        const params = new URLSearchParams();
        const perPage = qs('perPage').value;
        params.set('per_page', perPage);
        params.set('page', String(page));

        const title = qs('fTitle').value.trim();
        const author = qs('fAuthor').value.trim();
        const year = qs('fYear').value.trim();
        const tag = qs('fTag').value.trim();

        if (title) params.set('title', title);
        if (author) params.set('author', author);
        if (year) params.set('year', year);
        if (tag) params.set('tag', tag);

        return params;
    }

    function renderCatalog(paginated) {
        const body = qs('catalogBody');
        body.innerHTML = '';

        const rows = (paginated && paginated.data) ? paginated.data : [];
        rows.forEach(doc => {
            const tr = document.createElement('tr');
            const authorName = doc.author && doc.author.name ? doc.author.name : '—';
            const tags = (doc.tags || []).map(t => `<span class="pill">${t.name}</span>`).join(' ');
            tr.innerHTML = `
                <td>${doc.id}</td>
                <td>
                    <div style="font-weight:800">${doc.title ?? 'Untitled'}</div>
                    <div class="muted">${doc.file_path ? ('file: ' + doc.file_path) : 'no file'}</div>
                </td>
                <td>${authorName}</td>
                <td>${doc.year ?? '—'}</td>
                <td>${tags || '<span class="muted">—</span>'}</td>
                <td>
                    <button class="btn" data-action="fill" data-id="${doc.id}">Fill</button>
                    <button class="btn" data-action="delete" data-id="${doc.id}">Delete</button>
                </td>
            `;
            body.appendChild(tr);
        });

        qs('catalogMeta').textContent = `Total: ${paginated.total} | Showing page ${paginated.current_page} of ${paginated.last_page}`;
        qs('pageInfo').textContent = `Page ${paginated.current_page} / ${paginated.last_page}`;
    }

    async function loadCatalog(page = 1) {
        const params = buildCatalogParams(page);
        const res = await fetch('/api/v1/documents?' + params.toString());
        const json = await res.json();
        const paginated = json.data;
        currentPage = paginated.current_page;
        lastPage = paginated.last_page;
        renderCatalog(paginated);
    }

    async function fillFormFromId(id) {
        const res = await fetch('/api/v1/documents/' + id);
        const json = await res.json();
        if (!json.success) throw new Error(json.message || 'Not found');
        const doc = json.data;

        qs('docId').value = doc.id;
        qs('title').value = doc.title ?? '';
        qs('year').value = doc.year ?? '';
        qs('abstract').value = doc.abstract ?? '';
        qs('authorId').value = doc.author_id ?? (doc.author ? doc.author.id : '');

        const tagSelect = qs('tagIds');
        const tagIds = (doc.tags || []).map(t => t.id);
        Array.from(tagSelect.options).forEach(opt => {
            opt.selected = tagIds.includes(parseInt(opt.value, 10));
        });
    }

    async function createDocument() {
        const fd = new FormData();
        fd.append('title', qs('title').value);
        fd.append('year', qs('year').value);
        fd.append('abstract', qs('abstract').value);

        const authorId = qs('authorId').value;
        if (authorId) fd.append('author_id', authorId);

        selectedMulti(qs('tagIds')).forEach(id => fd.append('tag_ids[]', String(id)));

        const file = qs('file').files[0];
        if (file) fd.append('file', file);

        const res = await fetch('/api/v1/documents', { method: 'POST', body: fd });
        return await res.json();
    }

    async function updateDocument(id) {
        const fd = new FormData();
        if (qs('title').value.trim()) fd.append('title', qs('title').value);
        if (qs('year').value.trim()) fd.append('year', qs('year').value);
        fd.append('abstract', qs('abstract').value);

        const authorId = qs('authorId').value;
        fd.append('author_id', authorId); // allow null/empty to clear

        selectedMulti(qs('tagIds')).forEach(tid => fd.append('tag_ids[]', String(tid)));

        const file = qs('file').files[0];
        if (file) fd.append('file', file);

        const res = await fetch('/api/v1/documents/' + id, { method: 'PUT', body: fd });
        return await res.json();
    }

    async function deleteDocument(id) {
        const res = await fetch('/api/v1/documents/' + id, { method: 'DELETE' });
        return await res.json();
    }

    function clearForm() {
        qs('docId').value = '';
        qs('title').value = '';
        qs('year').value = '';
        qs('abstract').value = '';
        qs('authorId').value = '';
        Array.from(qs('tagIds').options).forEach(opt => opt.selected = false);
        qs('file').value = '';
        qs('formMsg').textContent = '';
    }

    qs('btnReload').addEventListener('click', () => loadCatalog(1));
    qs('btnPrev').addEventListener('click', () => loadCatalog(Math.max(1, currentPage - 1)));
    qs('btnNext').addEventListener('click', () => loadCatalog(Math.min(lastPage, currentPage + 1)));

    qs('btnCreate').addEventListener('click', async () => {
        try {
            qs('formMsg').textContent = 'Creating...';
            const json = await createDocument();
            qs('formMsg').textContent = json.success ? 'Created.' : ('Error: ' + (json.message || 'failed'));
            await loadCatalog(currentPage);
        } catch (e) {
            qs('formMsg').textContent = 'Error: ' + e.message;
        }
    });

    qs('btnUpdate').addEventListener('click', async () => {
        const id = qs('docId').value.trim();
        if (!id) { qs('formMsg').textContent = 'Fill Document ID first.'; return; }
        try {
            qs('formMsg').textContent = 'Updating...';
            const json = await updateDocument(id);
            qs('formMsg').textContent = json.success ? 'Updated.' : ('Error: ' + (json.message || 'failed'));
            await loadCatalog(currentPage);
        } catch (e) {
            qs('formMsg').textContent = 'Error: ' + e.message;
        }
    });

    qs('btnClear').addEventListener('click', clearForm);

    qs('catalogBody').addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.dataset.action;
        const id = btn.dataset.id;

        if (action === 'fill') {
            await fillFormFromId(id);
        }
        if (action === 'delete') {
            if (!confirm('Delete document #' + id + '?')) return;
            await deleteDocument(id);
            await loadCatalog(currentPage);
        }
    });

    (async function init() {
        await loadLookups();
        await loadCatalog(1);
    })();
</script>

</body>
</html>

