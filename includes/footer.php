    </div>
    
    <script>
        // Theme toggle
        const root = document.documentElement;
        const themeBtn = document.getElementById('themeToggle');
        const themeBtnMobile = document.getElementById('themeToggleMobile');
        const saved = localStorage.getItem('theme');
        if (saved === 'dark') root.classList.add('dark');

        function toggleTheme() {
            root.classList.toggle('dark');
            localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
        }

        if (themeBtn) themeBtn.addEventListener('click', toggleTheme);
        if (themeBtnMobile) themeBtnMobile.addEventListener('click', toggleTheme);

        // Mobile nav drawer
        const drawer = document.getElementById('navDrawer');
        const openNav = document.getElementById('openNav');
        const closeNav = document.getElementById('closeNav');
        if (openNav) openNav.addEventListener('click', () => drawer.classList.remove('hidden'));
        if (closeNav) closeNav.addEventListener('click', () => drawer.classList.add('hidden'));
        if (drawer) drawer.addEventListener('click', (e) => { if (e.target === drawer) drawer.classList.add('hidden'); });
    </script>
</body>
</html>
