<?php
/**
 * Theme Footer - Matching wireframe design
 *
 * @package Hilal
 */
?>
    </main><!-- #primary -->

    <footer id="colophon" class="site-footer">
        <div class="footer-inner">
            <!-- Brand -->
            <div class="footer-brand">
                <span class="icon">🌙</span>
                <span class="name">Hilal</span>
                <span class="tagline">Moon Sighting Platform</span>
            </div>

            <!-- Navigation -->
            <nav class="footer-nav">
                <a href="/">Home</a>
                <a href="/calendar/">Calendar</a>
                <a href="/announcements/">Announcements</a>
                <a href="/about/">About</a>
                <a href="/privacy/">Privacy</a>
            </nav>

            <!-- Copyright -->
            <div class="footer-copyright">
                &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Hilal. Based on Umm al-Qura Calendar
            </div>
        </div>
    </footer>
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
