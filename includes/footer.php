    </div>

    <script>
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
