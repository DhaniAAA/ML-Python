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
                        primary: "#8B5CF6", // Violet
                        secondary: "#A78BFA", // Light Violet
                        accent: "#FBBF24", // Amber
                        danger: "#EF4444", // Red
                        success: "#10B981", // Green
                        surface: "#FFFFFF",
                        "surface-dark": "#1F2937",
                        "bg-light": "#F3F4F6",
                        "bg-dark": "#111827",
                        black: "#111827",
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"],
                        mono: ["Space Mono", "monospace"]
                    },
                    borderRadius: {
                        DEFAULT: "0px",
                        sm: "0px",
                        md: "0px",
                        lg: "0px",
                        xl: "0px",
                        full: "9999px"
                    },
                    boxShadow: {
                        neo: "5px 5px 0px 0px #000000",
                        "neo-sm": "3px 3px 0px 0px #000000",
                        "neo-lg": "8px 8px 0px 0px #000000",
                        none: "0px 0px 0px 0px #000000",
                    },
                    borderWidth: {
                        DEFAULT: "2px",
                        '3': '3px',
                        '4': '4px',
                    },
                    opacity: {
                        90: '0.9',
                        80: '0.8',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap');

        :root {
            --shadow-color: #000;
        }

        body {
            background-color: #f0f0f0;
            background-image: radial-gradient(#ddd 1px, transparent 1px);
            background-size: 20px 20px;
        }



        .container-max { max-width: 1200px; margin-inline: auto; }

        /* Neo Brutalism Base Classes */
        .surface {
            background-color: #ffffff;
            border: 3px solid #000;
            box-shadow: 5px 5px 0px 0px #000;
        }



        .card {
            background-color: white;
            border: 3px solid #000;
            padding: 1.5rem;
            box-shadow: 6px 6px 0px 0px #000;
            transition: all 0.2s ease;
        }

        .card:hover {
            transform: translate(-2px, -2px);
            box-shadow: 8px 8px 0px 0px #000;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            border: 3px solid #000;
            box-shadow: 4px 4px 0px 0px #000;
            transition: all 0.15s ease;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.05em;
        }

        .btn:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0px 0px #000;
        }

        .btn-primary {
            background-color: #8B5CF6;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #7C3AED;
        }

        .btn-outline {
            background-color: #fff;
            color: #000;
        }

        .btn-outline:hover {
            background-color: #F3F4F6;
        }

        .badge {
            border: 2px solid #000;
            padding: 0.25rem 0.5rem;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            box-shadow: 2px 2px 0px 0px #000;
            background: #FBBF24;
            color: #000;
        }

        .text-muted { opacity: 0.7; }

        .no-underline { text-decoration: none; }

        /* Input Fields */
        input[type="text"], input[type="file"], textarea, select {
            border: 3px solid #000 !important;
            padding: 0.75rem !important;
            box-shadow: 4px 4px 0px 0px #000 !important;
            outline: none !important;
        }

        input:focus, textarea:focus, select:focus {
            transform: translate(-1px, -1px);
            box-shadow: 6px 6px 0px 0px #000 !important;
        }

        /* Material Symbols overrides for boldness */
        .material-symbols-outlined {
            font-weight: 700 !important;
            font-variation-settings: 'FILL' 0, 'wght' 700, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light font-display text-text-light scroll-smooth">
    <div class="min-h-screen flex">
