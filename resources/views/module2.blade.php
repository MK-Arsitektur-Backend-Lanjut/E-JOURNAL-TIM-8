<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced E-Journal Repository | Module 2</title>
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

        /* Top Navigation */
        .top-nav {
            width: 100%;
            padding: 1.5rem;
            display: flex;
            justify-content: flex-start;
        }

        .back-btn {
            color: var(--text-main);
            text-decoration: none;
            display: flex;
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
            max-width: 1000px;
            padding: 0 2rem 2rem 2rem;
            margin-top: 1rem;
        }

        /* Header / Hero */
        .hero {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInDown 0.8s ease forwards;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        /* Search Form Card */
        .search-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 0.8s ease 0.2s forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        label {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        input {
            background: #09090b;
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 0.8rem 1rem;
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--glow);
        }

        button.btn-search {
            grid-column: 1 / -1;
            background: var(--primary);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        button.btn-search:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px var(--glow);
        }

        /* Results Section */
        .results-container {
            margin-top: 3rem;
            width: 100%;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.5rem;
        }

        .results-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .document-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease forwards;
        }

        .document-card:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
            background: var(--bg-surface-hover);
        }

        .doc-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #818cf8;
        }

        .doc-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .doc-meta span {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }

        .doc-abstract {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #d4d4d8;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .loader {
            display: none;
            text-align: center;
            margin: 2rem 0;
            color: var(--text-muted);
        }

        .loader.active {
            display: block;
        }

        .loader-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem auto;
        }

        .btn-recommend {
            background: transparent;
            color: #818cf8;
            border: 1px solid #818cf8;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-top: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-recommend:hover {
            background: rgba(129, 140, 248, 0.1);
        }

        /* Recommendation UI */
        .recs-container {
            display: none;
            margin-top: 1.5rem;
            padding: 1.25rem;
            background: rgba(24, 24, 27, 0.7);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: var(--radius-md);
            backdrop-filter: blur(10px);
            animation: fadeInDown 0.3s ease;
        }

        .recs-header {
            font-size: 0.95rem;
            font-weight: 600;
            color: #c084fc;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 0.7rem;
        }

        .rec-item {
            padding: 0.8rem 1rem;
            background: rgba(0, 0, 0, 0.25);
            border-radius: 8px;
            margin-bottom: 0.5rem;
            border: 1px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            cursor: pointer;
        }

        .rec-item:last-child {
            margin-bottom: 0;
        }

        .rec-item:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .rec-item-title {
            font-weight: 600;
            color: #f4f4f5;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .rec-item-meta {
            font-size: 0.75rem;
            color: #a1a1aa;
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .rec-item-meta span {
            background: rgba(255,255,255,0.05);
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
        }

        /* Pagination UI */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }
        .page-btn {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 0.5rem 0.8rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .page-btn:hover:not(:disabled) {
            border-color: var(--primary);
            color: #818cf8;
        }
        .page-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <nav class="top-nav">
<<<<<<< Updated upstream
        <a href="/" class="back-btn">
            &larr; Back to Portal
=======
        <a href="/dashboard" class="back-btn">
            &larr; Back to Dashboard
>>>>>>> Stashed changes
        </a>
    </nav>

    <div class="container">
        <!-- Hero Section -->
        <div class="hero">
            <h1>Discovery Portal</h1>
            <p>Advanced digital repository search. Find exactly what you need.</p>
        </div>

        <!-- ... same search implementation ... -->
        <!-- Search Form -->
        <div class="search-card">
            <form id="searchForm">
                <div class="form-grid">
                    <div class="input-group full-width">
                        <label for="title">Document Title or Keywords</label>
                        <input type="text" id="title" placeholder="e.g. Machine Learning, Artificial Intelligence...">
                    </div>
                    <div class="input-group">
                        <label for="author">Author Name</label>
                        <input type="text" id="author" placeholder="e.g. John Doe">
                    </div>
                    <div class="input-group">
                        <label for="year">Publication Year</label>
                        <input type="number" id="year" placeholder="e.g. 2024">
                    </div>
                    <div class="input-group full-width">
                        <label for="abstract">Abstract Keywords</label>
                        <input type="text" id="abstract" placeholder="Search specific words inside abstracts...">
                    </div>
                    <button type="submit" class="btn-search">Search Repository</button>
                </div>
            </form>
        </div>

        <!-- Loader -->
        <div class="loader" id="loader">
            <div class="loader-spinner"></div>
            <p>Searching the database...</p>
        </div>

        <!-- Results Section -->
        <div class="results-container" id="resultsContainer" style="display: none;">
            <div class="results-header">
                <h2>Search Results</h2>
                <span id="resultCount" style="color: var(--text-muted);">0 found</span>
            </div>
            
            <div class="results-list" id="resultsList">
                <!-- Results will be injected here via JavaScript -->
            </div>
            
            <div class="pagination-container" id="paginationContainer" style="display: none;">
                <!-- Pagination buttons will be injected here -->
            </div>
        </div>
    </div>

    <script>
        window.currentSearchParams = '';

        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const title = document.getElementById('title').value;
            const author = document.getElementById('author').value;
            const year = document.getElementById('year').value;
            const abstract = document.getElementById('abstract').value;

            const params = new URLSearchParams();
            if (title) params.append('title', title);
            if (author) params.append('author', author);
            if (year) params.append('year', year);
            if (abstract) params.append('abstract', abstract);

            window.currentSearchParams = params.toString();
            fetchResults(1);
        });

        async function fetchResults(page) {
            const loader = document.getElementById('loader');
            const resultsContainer = document.getElementById('resultsContainer');
            const resultsList = document.getElementById('resultsList');
            const resultCount = document.getElementById('resultCount');
            const paginationContainer = document.getElementById('paginationContainer');

            try {
                loader.classList.add('active');
                resultsContainer.style.display = 'none';
                paginationContainer.style.display = 'none';
                resultsList.innerHTML = '';

                const response = await fetch(`/api/v1/documents/search?${window.currentSearchParams}&page=${page}`);
                const result = await response.json();

                loader.classList.remove('active');
                
                if (result.success && result.data) {
                    renderResults(result.data.data || result.data); 
                    const total = result.data.total || (Array.isArray(result.data) ? result.data.length : 0);
                    resultCount.innerText = `${total} documents found`;
                    
                    if (result.data.last_page && result.data.last_page > 1) {
                        renderPagination(result.data.current_page, result.data.last_page);
                        paginationContainer.style.display = 'flex';
                    }
                    
                    resultsContainer.style.display = 'block';
                }
            } catch (error) {
                loader.classList.remove('active');
                alert('An error occurred while fetching data. Check console.');
                console.error(error);
            }
        }

        function renderPagination(currentPage, lastPage) {
            const container = document.getElementById('paginationContainer');
            let html = '';
            
            html += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="fetchResults(${currentPage - 1})">← Prev</button>`;
            
            for(let i = 1; i <= lastPage; i++) {
                if (lastPage > 7) {
                    if (i === 1 || i === lastPage || (i >= currentPage - 1 && i <= currentPage + 1)) {
                        html += `<button class="page-btn ${currentPage === i ? 'active' : ''}" onclick="fetchResults(${i})">${i}</button>`;
                    } else if (i === currentPage - 2 || i === currentPage + 2) {
                        html += `<span style="color:var(--text-muted);">...</span>`;
                    }
                } else {
                    html += `<button class="page-btn ${currentPage === i ? 'active' : ''}" onclick="fetchResults(${i})">${i}</button>`;
                }
            }
            
            html += `<button class="page-btn" ${currentPage === lastPage ? 'disabled' : ''} onclick="fetchResults(${currentPage + 1})">Next →</button>`;
            container.innerHTML = html;
        }

        function renderResults(documents) {
            const resultsList = document.getElementById('resultsList');
            if (!documents || documents.length === 0) {
                resultsList.innerHTML = '<p style="text-align:center; color:#a1a1aa; padding: 2rem;">No documents match your search criteria.</p>';
                return;
            }
            documents.forEach(doc => {
                const card = document.createElement('div');
                card.className = 'document-card';
                card.innerHTML = `
                    <div class="doc-title">${doc.title || 'Untitled Document'}</div>
                    <div class="doc-meta">
                        <span>👤 ${doc.author ? (doc.author.name || doc.author) : 'Unknown'}</span>
                        <span>📅 ${doc.year || 'N/A'}</span>
                    </div>
                    <div class="doc-abstract">${doc.abstract || 'No abstract available.'}</div>
                    <button class="btn-recommend" onclick="loadRecommendations(${doc.id}, this)">View Related Journals</button>
                    <div class="recs-container"></div>
                `;
                resultsList.appendChild(card);
            });
        }

        async function loadRecommendations(docId, btn) {
            const container = btn.nextElementSibling;
            if (container.style.display === 'block') {
                container.style.display = 'none';
                btn.innerHTML = 'View Related Journals';
                return;
            }
            
            btn.innerHTML = 'Searching...';
            try {
                const response = await fetch(`/api/v1/documents/${docId}/recommendations`);
                const result = await response.json();
                
                if (result.success && result.data && result.data.length > 0) {
                    let html = `<div class="recs-header">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Related Journals by Topic
                    </div><div class="recs-list">`;
                    
                    result.data.forEach(rec => {
                        html += `
                            <div class="rec-item">
                                <div class="rec-item-title">${rec.title}</div>
                                <div class="rec-item-meta">
                                    <span>Year: ${rec.year || '-'}</span> 
                                    <span style="color:var(--text-muted);">|</span>
                                    <span>${rec.author ? (rec.author.name || rec.author) : 'Anonymous'}</span>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `
                        <div class="recs-header" style="color:var(--text-muted); border:none; margin:0;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            No related journals found.
                        </div>
                    `;
                }
                container.style.display = 'block';
                btn.innerHTML = 'Close Recommendations ▴';
            } catch (err) {
                console.error(err);
                btn.innerHTML = 'Failed to load, try again';
            }
        }
    </script>
</body>
</html>
