<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?php echo isset($page_title) ? $page_title : 'Sentiment AI'; ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#4F46E5",
                        secondary: "#0EA5E9",
                        accent: "#22C55E",
                        surface: "#ffffff",
                        surfaceDark: "#111827",
                        "background-light": "#f5f7fb",
                        "background-dark": "#0f172a",
                        "text-light": "#1F2937",
                        "text-dark": "#E5E7EB",
                        positive: "#10B981",
                        negative: "#EF4444",
                        neutral: "#F59E0B"
                    },
                    fontFamily: { display: ["Inter", "ui-sans-serif", "system-ui"] },
                    borderRadius: { DEFAULT: "0.75rem", lg: "1.25rem", xl: "1.75rem", full: "9999px" },
                    boxShadow: {
                        soft: "9px 9px 16px #d1d9e6, -9px -9px 16px #ffffff",
                        softDark: "9px 9px 16px #0c141c, -9px -9px 16px #141e28",
                        card: "0 10px 20px rgba(0,0,0,.06)",
                        btn: "0 8px 16px rgba(79,70,229,.25)"
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --ring: rgba(79,70,229,.7);
        }
        .scroll-smooth { scroll-behavior: smooth; }
        .focus-ring:focus-visible { outline: 2px solid var(--ring); outline-offset: 2px; }
        .no-underline { text-decoration: none; }
        .container-max { max-width: 1200px; margin-inline: auto; }
        .surface { background-color: rgba(255,255,255,.7); backdrop-filter: blur(8px); }
        .surface-dark { background-color: rgba(17,24,39,.6); backdrop-filter: blur(8px); }
        .transition-base { transition: all .2s ease; }
        .hover-elevate:hover { box-shadow: 0 12px 24px rgba(0,0,0,.08); transform: translateY(-1px); }
        .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.625rem 1rem; border-radius:.75rem; font-weight:600; }
        .btn-primary { background-color:#4F46E5; color:white; }
        .btn-primary:hover { background-color:#4338CA; }
        .btn-outline { border:1px solid rgba(79,70,229,.35); color:#4F46E5; }
        .btn-outline:hover { background-color:rgba(79,70,229,.08); }
        .card { border-radius:1rem; padding:1.25rem; box-shadow: 0 10px 20px rgba(0,0,0,.06); }
        .badge { border-radius:.5rem; padding:.25rem .5rem; font-weight:600; font-size:.75rem; }
        .text-muted { opacity:.7; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-text-light dark:text-text-dark scroll-smooth">
    <div class="min-h-screen flex">
