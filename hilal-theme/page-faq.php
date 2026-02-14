<?php
/**
 * Template Name: FAQ
 *
 * Frequently Asked Questions page with accordion design.
 *
 * @package Hilal
 */

get_header();

// Get filter from query string
$filter_category = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
$search_query    = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';

// Get FAQs
$faqs         = array();
$grouped_faqs = array();

if ( class_exists( 'Hilal_FAQ' ) ) {
    if ( ! empty( $search_query ) ) {
        $faqs = Hilal_FAQ::search( $search_query, 'en' );
    } elseif ( ! empty( $filter_category ) ) {
        $faqs = Hilal_FAQ::get_faqs( array( 'category' => $filter_category ) );
    } else {
        $grouped_faqs = Hilal_FAQ::get_grouped_faqs();
    }
}

$categories = Hilal_FAQ::$categories ?? array();
?>

<section class="section">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Frequently Asked Questions</h1>
            <p class="subtitle">Find answers to common questions about moon sighting, Islamic calendar, and prayer times</p>
        </div>

        <!-- Search Box -->
        <div class="faq-search" style="max-width: 600px; margin: 0 auto 32px;">
            <form method="get" action="">
                <div style="display: flex; gap: 8px;">
                    <input type="text"
                           name="q"
                           value="<?php echo esc_attr( $search_query ); ?>"
                           placeholder="Search questions..."
                           style="flex: 1; padding: 12px 16px; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>
                </div>
            </form>
        </div>

        <!-- Category Filter Tabs -->
        <div style="display: flex; gap: 8px; margin-bottom: 32px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo esc_url( remove_query_arg( array( 'category', 'q' ) ) ); ?>"
               class="filter-btn <?php echo empty( $filter_category ) && empty( $search_query ) ? 'active' : ''; ?>">
                All
            </a>
            <?php foreach ( $categories as $key => $label ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'category', $key, remove_query_arg( 'q' ) ) ); ?>"
                   class="filter-btn <?php echo $filter_category === $key ? 'active' : ''; ?>">
                    <?php echo esc_html( $label ); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ( ! empty( $search_query ) ) : ?>
            <!-- Search Results -->
            <div class="search-results" style="margin-bottom: 24px;">
                <p class="text-muted">
                    Found <?php echo count( $faqs ); ?> result(s) for "<?php echo esc_html( $search_query ); ?>"
                    <a href="<?php echo esc_url( remove_query_arg( 'q' ) ); ?>" style="margin-left: 8px;">Clear search</a>
                </p>
            </div>

            <?php if ( ! empty( $faqs ) ) : ?>
                <div class="faq-list">
                    <?php foreach ( $faqs as $faq ) : ?>
                        <div class="faq-item card" data-faq-id="<?php echo esc_attr( $faq['id'] ); ?>">
                            <button class="faq-question" onclick="toggleFaq(this)">
                                <span class="faq-category-badge"><?php echo esc_html( $faq['category_label'] ); ?></span>
                                <span class="faq-question-text"><?php echo esc_html( $faq['question_en'] ); ?></span>
                                <span class="faq-toggle-icon">+</span>
                            </button>
                            <div class="faq-answer">
                                <?php echo wp_kses_post( $faq['answer_en'] ); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="text-center" style="padding: 3rem;">
                    <p class="text-muted">No FAQs found matching your search.</p>
                </div>
            <?php endif; ?>

        <?php elseif ( ! empty( $filter_category ) ) : ?>
            <!-- Filtered FAQs -->
            <?php if ( ! empty( $faqs ) ) : ?>
                <div class="faq-list">
                    <?php foreach ( $faqs as $faq ) : ?>
                        <div class="faq-item card" data-faq-id="<?php echo esc_attr( $faq['id'] ); ?>">
                            <button class="faq-question" onclick="toggleFaq(this)">
                                <span class="faq-question-text"><?php echo esc_html( $faq['question_en'] ); ?></span>
                                <span class="faq-toggle-icon">+</span>
                            </button>
                            <div class="faq-answer">
                                <?php echo wp_kses_post( $faq['answer_en'] ); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="text-center" style="padding: 3rem;">
                    <p class="text-muted">No FAQs in this category yet.</p>
                </div>
            <?php endif; ?>

        <?php else : ?>
            <!-- Grouped FAQs by Category -->
            <?php if ( ! empty( $grouped_faqs ) ) : ?>
                <?php foreach ( $grouped_faqs as $group ) : ?>
                    <div class="faq-category-section" style="margin-bottom: 40px;">
                        <h2 class="faq-category-title" style="font-size: 1.25rem; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid var(--gold);">
                            <?php echo esc_html( $group['label'] ); ?>
                        </h2>
                        <div class="faq-list">
                            <?php foreach ( $group['faqs'] as $faq ) : ?>
                                <div class="faq-item card" data-faq-id="<?php echo esc_attr( $faq['id'] ); ?>">
                                    <button class="faq-question" onclick="toggleFaq(this)">
                                        <span class="faq-question-text"><?php echo esc_html( $faq['question_en'] ); ?></span>
                                        <span class="faq-toggle-icon">+</span>
                                    </button>
                                    <div class="faq-answer">
                                        <?php echo wp_kses_post( $faq['answer_en'] ); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="text-center" style="padding: 3rem;">
                    <p class="text-muted">No FAQs available yet.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Contact Section -->
        <div class="faq-contact card" style="margin-top: 48px; text-align: center; padding: 32px;">
            <h3 style="margin-bottom: 8px;">Still have questions?</h3>
            <p class="text-muted" style="margin-bottom: 16px;">
                If you couldn't find the answer you were looking for, feel free to contact us.
            </p>
            <a href="mailto:info@hilal.nz" class="btn btn-primary">Contact Us</a>
        </div>
    </div>
</section>

<style>
.faq-item {
    margin-bottom: 8px;
    overflow: hidden;
}

.faq-question {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    font-size: 1rem;
    font-weight: 500;
    color: var(--text);
    transition: background-color 0.2s;
}

.faq-question:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.faq-question-text {
    flex: 1;
}

.faq-category-badge {
    font-size: 0.7rem;
    padding: 4px 8px;
    background: var(--gold-light);
    color: var(--gold-dark);
    border-radius: 4px;
    text-transform: uppercase;
    font-weight: 600;
    white-space: nowrap;
}

.faq-toggle-icon {
    font-size: 1.5rem;
    color: var(--gold);
    font-weight: 300;
    transition: transform 0.2s;
    min-width: 24px;
    text-align: center;
}

.faq-item.open .faq-toggle-icon {
    transform: rotate(45deg);
}

.faq-answer {
    display: none;
    padding: 0 20px 20px;
    color: var(--text-muted);
    line-height: 1.7;
}

.faq-item.open .faq-answer {
    display: block;
}

.faq-answer p {
    margin-bottom: 1rem;
}

.faq-answer p:last-child {
    margin-bottom: 0;
}

.faq-answer ul,
.faq-answer ol {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.faq-answer li {
    margin-bottom: 0.5rem;
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
    .faq-question:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
}
</style>

<script>
function toggleFaq(button) {
    const faqItem = button.closest('.faq-item');
    const wasOpen = faqItem.classList.contains('open');

    // Close all other FAQs in the same list (optional: for accordion behavior)
    // const faqList = faqItem.closest('.faq-list');
    // faqList.querySelectorAll('.faq-item.open').forEach(item => {
    //     if (item !== faqItem) {
    //         item.classList.remove('open');
    //     }
    // });

    // Toggle current FAQ
    faqItem.classList.toggle('open');

    // Update URL hash for deep linking
    if (!wasOpen) {
        const faqId = faqItem.dataset.faqId;
        history.replaceState(null, null, '#faq-' + faqId);
    }
}

// Open FAQ from URL hash on page load
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#faq-')) {
        const faqId = hash.replace('#faq-', '');
        const faqItem = document.querySelector('[data-faq-id="' + faqId + '"]');
        if (faqItem) {
            faqItem.classList.add('open');
            faqItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
</script>

<?php
get_footer();
