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
        <a href="/" class="back-btn">
            &larr; Back to Portal
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
        </div>
    </div>

    <script>
        document.getElementById('searchForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const title = document.getElementById('title').value;
            const author = document.getElementById('author').value;
            const year = document.getElementById('year').value;
            const abstract = document.getElementById('abstract').value;

            const loader = document.getElementById('loader');
            const resultsContainer = document.getElementById('resultsContainer');
            const resultsList = document.getElementById('resultsList');
            const resultCount = document.getElementById('resultCount');

            const params = new URLSearchParams();
            if (title) params.append('title', title);
            if (author) params.append('author', author);
            if (year) params.append('year', year);
            if (abstract) params.append('abstract', abstract);

            try {
                loader.classList.add('active');
                resultsContainer.style.display = 'none';
                resultsList.innerHTML = '';

                const response = await fetch('/api/v1/documents/search?' + params.toString());
                const result = await response.json();

                loader.classList.remove('active');
                
                if (result.success && result.data) {
                    renderResults(result.data.data || result.data); 
                    const total = result.data.total || (Array.isArray(result.data) ? result.data.length : 0);
                    resultCount.innerText = `${total} documents found`;
                    resultsContainer.style.display = 'block';
                }
            } catch (error) {
                loader.classList.remove('active');
                alert('An error occurred while fetching data. Check console.');
                console.error(error);
            }
        });

        function renderResults(documents) {
            const resultsList = document.getElementById('resultsList');
            if (!documents || documents.length === 0) {
                resultsList.innerHTML = '<p style="text-align:center; color:#a1a1aa; padding: 2rem;">No documents match your search criteria.</p>';
                return;
            }
            documents.forEach(doc => {
                const card = document.createElement('div');
                card.className = 'document-card';
                card.innerHTML = \`
                    <div class="doc-title">\${doc.title || 'Untitled Document'}</div>
                    <div class="doc-meta">
                        <span>👤 \${doc.author || 'Unknown'}</span>
                        <span>📅 \${doc.year || 'N/A'}</span>
                    </div>
                    <div class="doc-abstract">\${doc.abstract || 'No abstract available.'}</div>
                \`;
                resultsList.appendChild(card);
            });
        }
    </script>
</body>
</html>
