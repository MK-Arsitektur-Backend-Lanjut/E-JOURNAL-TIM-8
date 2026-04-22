<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Journal System Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #10b981;
            --accent: #f43f5e;
            --bg: #09090b;
            --bg-surface: #18181b;
            --bg-surface-hover: #27272a;
            --text-main: #f4f4f5;
            --text-muted: #a1a1aa;
            --border: #3f3f46;
            --glow: rgba(99, 102, 241, 0.3);
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

        .container {
            width: 100%;
            max-width: 1200px;
            padding: 2rem;
            margin-top: 4rem;
        }

        .hero {
            text-align: center;
            margin-bottom: 4rem;
            animation: fadeInDown 0.8s ease forwards;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #818cf8, #c084fc, #f43f5e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            color: var(--text-muted);
            font-size: 1.25rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .module-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.1), transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .module-card.active:hover {
            border-color: var(--primary);
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.5), 0 0 20px var(--glow);
        }

        .module-card.active:hover::before {
            opacity: 1;
        }

        .module-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .module-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .module-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            flex-grow: 1;
        }

        .module-btn {
            background: #27272a;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 30px;
            font-weight: 600;
            border: 1px solid var(--border);
            transition: all 0.3s;
            width: 100%;
        }

        .module-card.active .module-btn {
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 4px 15px var(--glow);
        }

        .module-card.inactive {
            opacity: 0.6;
            cursor: not-allowed;
            filter: grayscale(100%);
        }

        /* Animations */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .module-card:nth-child(1) { animation: fadeInUp 0.8s ease 0.1s forwards; opacity: 0; transform: translateY(30px); }
        .module-card:nth-child(2) { animation: fadeInUp 0.8s ease 0.2s forwards; opacity: 0; transform: translateY(30px); }
        .module-card:nth-child(3) { animation: fadeInUp 0.8s ease 0.3s forwards; opacity: 0; transform: translateY(30px); }
        .module-card:nth-child(4) { animation: fadeInUp 0.8s ease 0.4s forwards; opacity: 0; transform: translateY(30px); }

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-badge.ready {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid #10b981;
        }

        .status-badge.wip {
            background: rgba(161, 161, 170, 0.2);
            color: #a1a1aa;
            border: 1px solid #71717a;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="hero">
            <h1>E-Journal Master Portal</h1>
            <p>Select a module to access specific system capabilities and management tools.</p>
        </div>

        <div class="modules-grid">
            <!-- Module 1 -->
            <a href="/module1" class="module-card active">
                <div class="status-badge ready">Online</div>
                <div class="module-icon">📚</div>
                <h2 class="module-title">Module 1 : Collection & Metadata</h2>
                <p class="module-desc">Document catalog CRUD, simulated file upload management, and subject/tag grouping.</p>
                <div class="module-btn">Launch Module</div>
            </a>

            <!-- Module 2 -->
            <a href="/module2" class="module-card active">
                <div class="status-badge ready">Online</div>
                <div class="module-icon">🔭</div>
                <h2 class="module-title">Module 2 : Discovery</h2>
                <p class="module-desc">Advanced search algorithms, smart filtering, and document repository recommendations.</p>
                <div class="module-btn">Launch Module</div>
            </a>

            <!-- Module 3 -->
            <div class="module-card inactive">
                <div class="status-badge wip">Locked</div>
                <div class="module-icon">📝</div>
                <h2 class="module-title">Module 3 : Submissions</h2>
                <p class="module-desc">Submission pipeline, peer review workflow, and editor decision tracking.</p>
                <div class="module-btn">Coming Soon</div>
            </div>

            <!-- Module 4 -->
            <div class="module-card inactive">
                <div class="status-badge wip">Locked</div>
                <div class="module-icon">📊</div>
                <h2 class="module-title">Module 4 : Analytics</h2>
                <p class="module-desc">Dashboard metrics, read/download statistics, and system administration logs.</p>
                <div class="module-btn">Coming Soon</div>
            </div>
        </div>
    </div>

</body>
</html>
