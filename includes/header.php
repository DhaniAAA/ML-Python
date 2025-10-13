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
                        primary: "#111827",
                        "background-light": "#f5f7fb",
                        "background-dark": "#0f172a",
                        "text-light": "#34495E",
                        "text-dark": "#EAECEE",
                        positive: "#A3E4D7",
                        negative: "#F5B7B1",
                        neutral: "#FAD7A0"
                    },
                    fontFamily: { display: ["Inter", "ui-sans-serif", "system-ui"] },
                    borderRadius: { DEFAULT: "0.75rem", lg: "1.25rem", xl: "1.75rem", full: "9999px" }
                }
            }
        }
    </script>
    <style>
        .scroll-smooth { scroll-behavior: smooth; }
        .focus-ring:focus-visible { outline: 2px solid rgba(99,102,241,.9); outline-offset: 2px; }
        .no-underline { text-decoration: none; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-text-light dark:text-text-dark scroll-smooth">
    <div class="min-h-screen flex">
