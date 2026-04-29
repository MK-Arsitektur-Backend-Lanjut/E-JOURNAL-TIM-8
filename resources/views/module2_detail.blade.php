<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Details | Module 2</title>
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

        /* Detail Card */
        .detail-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            animation: fadeInDown 0.8s ease forwards;
        }

        .doc-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }

        .doc-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .doc-meta span {
            background: rgba(255, 255, 255, 0.05);
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .doc-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .doc-tag {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .doc-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #f4f4f5;
            margin-bottom: 1rem;
            margin-top: 2rem;
        }

        .doc-abstract {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #d4d4d8;
            text-align: justify;
        }

        /* Loader */
        .loader {
            text-align: center;
            margin: 4rem 0;
            color: var(--text-muted);
        }

        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem auto;
        }

        /* Recommendations Section */
        .recommendations-section {
            margin-top: 3rem;
            animation: fadeInUp 0.8s ease 0.3s forwards;
            opacity: 0;
        }

        .recs-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #f4f4f5;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .recs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .rec-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            flex-direction: column;
        }

        .rec-card:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            background: var(--bg-surface-hover);
        }

        .rec-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #818cf8;
            margin-bottom: 0.8rem;
            line-height: 1.4;
            flex-grow: 1;
        }

        .rec-card-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(20px); } }
    </style>
</head>
<body>

    <nav class="top-nav">
        <a href="{{ route('module2') }}" class="back-btn">
            &larr; Back to Advanced Search
        </a>
    </nav>

    <div class="container">
        
        <div id="loadingState" class="loader">
            <div class="loader-spinner"></div>
            <p>Loading document details...</p>
        </div>

        <div id="contentState" style="display: none;">
            <div class="detail-card">
                <h1 class="doc-title" id="docTitle"></h1>
                
                <div class="doc-meta">
                    <span id="docAuthor">👤 </span>
                    <span id="docYear">📅 </span>
                </div>

                <div class="doc-section-title">Abstract</div>
                <div class="doc-abstract" id="docAbstract"></div>

                <div class="doc-section-title" id="tagsTitle" style="display: none;">Tags</div>
                <div class="doc-tags" id="docTags"></div>
            </div>

            <div class="recommendations-section" id="recommendationsSection" style="display: none;">
                <h2 class="recs-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    Recommended Journals
                </h2>
                <div class="recs-grid" id="recsGrid">
                    <!-- Recommendation cards injected here -->
                </div>
            </div>
        </div>

    </div>

    <script>
        const docId = {{ $id }};
        
        document.addEventListener('DOMContentLoaded', () => {
            fetchDocumentDetail();
            fetchRecommendations();
        });

        async function fetchDocumentDetail() {
            try {
                const response = await fetch(`/api/v1/documents/${docId}`);
                if (!response.ok) throw new Error('Document not found');
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    const doc = result.data;
                    
                    document.getElementById('docTitle').innerText = doc.title || 'Untitled Document';
                    document.getElementById('docAuthor').innerHTML = `👤 ${doc.author ? (doc.author.name || doc.author) : 'Unknown Author'}`;
                    document.getElementById('docYear').innerHTML = `📅 ${doc.year || 'N/A'}`;
                    document.getElementById('docAbstract').innerText = doc.abstract || 'No abstract available for this document.';
                    
                    if (doc.tags && doc.tags.length > 0) {
                        document.getElementById('tagsTitle').style.display = 'block';
                        const tagsContainer = document.getElementById('docTags');
                        doc.tags.forEach(tag => {
                            const tagEl = document.createElement('span');
                            tagEl.className = 'doc-tag';
                            tagEl.innerText = tag.name || tag;
                            tagsContainer.appendChild(tagEl);
                        });
                    }

                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('contentState').style.display = 'block';
                }
            } catch (error) {
                console.error(error);
                document.getElementById('loadingState').innerHTML = '<p style="color:#ef4444;">Failed to load document details. It might have been removed.</p>';
            }
        }

        async function fetchRecommendations() {
            try {
                const response = await fetch(`/api/v1/documents/${docId}/recommendations`);
                const result = await response.json();
                
                if (result.success && result.data && result.data.length > 0) {
                    const recsGrid = document.getElementById('recsGrid');
                    
                    result.data.forEach(rec => {
                        const card = document.createElement('a');
                        card.className = 'rec-card';
                        card.href = `/module2/${rec.id}`;
                        
                        card.innerHTML = `
                            <div class="rec-card-title">${rec.title || 'Untitled'}</div>
                            <div class="rec-card-meta">
                                <span>👤 ${rec.author ? (rec.author.name || rec.author) : 'Unknown'}</span>
                                <span>📅 ${rec.year || '-'}</span>
                            </div>
                        `;
                        
                        recsGrid.appendChild(card);
                    });
                    
                    document.getElementById('recommendationsSection').style.display = 'block';
                }
            } catch (error) {
                console.error('Failed to fetch recommendations:', error);
            }
        }
    </script>
</body>
</html>
