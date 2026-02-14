<?php
/**
 * Template Name: Prayer Times
 *
 * @package Hilal
 */

get_header();

// Get mosques from API class
$mosques = array();
$regions = array();
if ( class_exists( 'Hilal_Prayer_Times_API' ) ) {
    $api     = new Hilal_Prayer_Times_API();
    $mosques = $api->get_nz_mosques();

    // Get unique regions
    $regions = array_unique( array_column( $mosques, 'region' ) );
    sort( $regions );
}

$default_mosque = 'masjid-e-umar';
?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h1>Prayer Times</h1>
            <p class="text-muted">Daily prayer times for mosques in New Zealand</p>
        </div>

        <!-- Mosque Selector -->
        <div class="mosque-selector" style="max-width: 500px; margin: 0 auto 2rem;">
            <label for="mosque-select" class="form-label">Select Mosque</label>
            <select id="mosque-select" class="form-control form-select">
                <?php foreach ( $regions as $region ) : ?>
                    <optgroup label="<?php echo esc_attr( $region ); ?>">
                        <?php
                        foreach ( $mosques as $mosque ) :
                            if ( $mosque['region'] !== $region ) {
                                continue;
                            }
                            $has_iqama   = ! empty( $mosque['my_masjid_id'] );
                            $iqama_badge = $has_iqama ? ' ✓' : '';
                            ?>
                            <option
                                value="<?php echo esc_attr( $mosque['id'] ); ?>"
                                data-lat="<?php echo esc_attr( $mosque['lat'] ); ?>"
                                data-lng="<?php echo esc_attr( $mosque['lng'] ); ?>"
                                data-name="<?php echo esc_attr( $mosque['name'] ); ?>"
                                data-address="<?php echo esc_attr( $mosque['address'] ); ?>"
                                data-city="<?php echo esc_attr( $mosque['city'] ); ?>"
                                data-mymasjid="<?php echo esc_attr( $mosque['my_masjid_id'] ?? '' ); ?>"
                                <?php selected( $default_mosque, $mosque['id'] ); ?>
                            >
                                <?php echo esc_html( $mosque['name'] . $iqama_badge ); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>

            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <button type="button" id="use-location" class="btn btn-outline" style="flex: 1;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 0.25rem;">
                        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                    </svg>
                    Use My Location
                </button>
            </div>

            <!-- Source indicator -->
            <div id="source-indicator" style="margin-top: 0.75rem; font-size: 0.75rem; color: var(--hilal-gray-500);">
                <span id="source-text"></span>
            </div>
        </div>

        <!-- Prayer Times Card -->
        <div class="prayer-times-card card" style="max-width: 500px; margin: 0 auto;">
            <div class="card-header" style="text-align: center; padding: 1.25rem;">
                <h3 id="mosque-name" style="margin: 0 0 0.25rem; font-size: 1.25rem;"></h3>
                <p id="mosque-address" class="text-muted" style="margin: 0 0 0.5rem; font-size: 0.875rem;"></p>
                <p class="text-muted" id="prayer-date" style="margin: 0; font-size: 0.8125rem;"><?php echo esc_html( gmdate( 'l, j F Y' ) ); ?></p>
            </div>
            <div class="card-body">
                <!-- Header for Iqama -->
                <div id="iqama-header" class="prayer-header" style="display: none; padding: 0.5rem 1rem; border-bottom: 1px solid var(--hilal-gray-200); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <span style="flex: 1;">Prayer</span>
                    <span style="flex: 1; text-align: center;">Adhan</span>
                    <span style="flex: 1; text-align: right; color: var(--hilal-gold);">Iqama</span>
                </div>

                <div class="prayer-list" id="prayer-list">
                    <div class="prayer-item">
                        <span class="prayer-name">Fajr</span>
                        <span class="prayer-time" data-prayer="fajr">--:--</span>
                        <span class="iqama-time" data-iqama="fajr" style="display: none;">--:--</span>
                    </div>
                    <div class="prayer-item">
                        <span class="prayer-name">Sunrise</span>
                        <span class="prayer-time" data-prayer="sunrise">--:--</span>
                        <span class="iqama-time" data-iqama="sunrise" style="display: none;">-</span>
                    </div>
                    <div class="prayer-item">
                        <span class="prayer-name">Dhuhr</span>
                        <span class="prayer-time" data-prayer="dhuhr">--:--</span>
                        <span class="iqama-time" data-iqama="dhuhr" style="display: none;">--:--</span>
                    </div>
                    <div class="prayer-item">
                        <span class="prayer-name">Asr</span>
                        <span class="prayer-time" data-prayer="asr">--:--</span>
                        <span class="iqama-time" data-iqama="asr" style="display: none;">--:--</span>
                    </div>
                    <div class="prayer-item">
                        <span class="prayer-name">Maghrib</span>
                        <span class="prayer-time" data-prayer="maghrib">--:--</span>
                        <span class="iqama-time" data-iqama="maghrib" style="display: none;">--:--</span>
                    </div>
                    <div class="prayer-item">
                        <span class="prayer-name">Isha</span>
                        <span class="prayer-time" data-prayer="isha">--:--</span>
                        <span class="iqama-time" data-iqama="isha" style="display: none;">--:--</span>
                    </div>
                </div>

                <!-- Next Prayer -->
                <div id="next-prayer-info" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--hilal-gray-200); text-align: center;">
                    <p class="text-muted" style="margin: 0; font-size: 0.875rem;">Next Prayer</p>
                    <p id="next-prayer-name" style="font-size: 1.25rem; font-weight: 600; margin: 0.5rem 0; color: var(--hilal-gold);"></p>
                    <p id="next-prayer-countdown" class="text-muted" style="margin: 0;"></p>
                </div>
            </div>
        </div>

        <!-- Mosque Info Card -->
        <div id="mosque-info-card" class="card" style="max-width: 500px; margin: 1.5rem auto 0; display: none;">
            <div class="card-body" style="padding: 1rem;">
                <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="var(--hilal-gold)" viewBox="0 0 16 16" style="flex-shrink: 0; margin-top: 2px;">
                        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                    </svg>
                    <div>
                        <p id="mosque-full-address" style="margin: 0; font-size: 0.875rem;"></p>
                        <p id="mosque-phone" style="margin: 0.25rem 0 0; font-size: 0.875rem; display: none;"></p>
                        <a id="mosque-website" href="#" target="_blank" style="display: none; font-size: 0.875rem; color: var(--hilal-gold);">
                            Visit Website →
                        </a>
                    </div>
                </div>
                <div id="mymasjid-badge" style="display: none; margin-top: 0.75rem; padding: 0.5rem 0.75rem; background: var(--hilal-gold-light); border-radius: 6px; font-size: 0.75rem; color: var(--hilal-gold);">
                    Iqama times available
                </div>
            </div>
        </div>

        <!-- Calculation Method Info -->
        <div style="max-width: 500px; margin: 2rem auto 0; text-align: center;">
            <p id="method-info" class="text-muted" style="font-size: 0.875rem;">
                Calculation Method: Muslim World League (MWL)
            </p>
        </div>
    </div>
</section>

<style>
.prayer-header {
    display: flex;
    color: var(--hilal-gray-500);
    font-weight: 600;
}
.prayer-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--hilal-gray-100);
}
.prayer-item:last-child {
    border-bottom: none;
}
.prayer-item.next {
    background: var(--hilal-gold-light);
    border-left: 3px solid var(--hilal-gold);
}
.prayer-item.next .prayer-name,
.prayer-item.next .prayer-time {
    color: var(--hilal-gold);
}
.prayer-name {
    flex: 1;
    font-weight: 600;
}
.prayer-time {
    font-weight: 700;
    font-size: 1.125rem;
}
.prayer-item.has-iqama .prayer-time {
    flex: 1;
    text-align: center;
}
.iqama-time {
    flex: 1;
    text-align: right;
    font-weight: 700;
    font-size: 1.125rem;
    color: var(--hilal-gold);
}
#source-indicator svg {
    width: 14px;
    height: 14px;
    vertical-align: middle;
    margin-right: 4px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mosqueSelect = document.getElementById('mosque-select');
    const useLocationBtn = document.getElementById('use-location');
    const mosqueName = document.getElementById('mosque-name');
    const mosqueAddress = document.getElementById('mosque-address');
    const sourceText = document.getElementById('source-text');
    const iqamaHeader = document.getElementById('iqama-header');
    const mosqueInfoCard = document.getElementById('mosque-info-card');
    const mymasjidBadge = document.getElementById('mymasjid-badge');
    const methodInfo = document.getElementById('method-info');

    const prayerNames = { fajr: 'Fajr', sunrise: 'Sunrise', dhuhr: 'Dhuhr', asr: 'Asr', maghrib: 'Maghrib', isha: 'Isha' };

    function showIqamaColumns(show) {
        iqamaHeader.style.display = show ? 'flex' : 'none';
        document.querySelectorAll('.iqama-time').forEach(el => {
            el.style.display = show ? 'block' : 'none';
        });
        document.querySelectorAll('.prayer-item').forEach(el => {
            el.classList.toggle('has-iqama', show);
        });
    }

    function updateSourceIndicator(source) {
        if (source === 'aladhan') {
            sourceText.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="var(--hilal-success)" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg> Times from AlAdhan.com`;
            methodInfo.textContent = 'Calculation Method: Muslim World League (MWL)';
        } else {
            sourceText.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M2 10h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1zm9-9h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-3a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zm0 9a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-3zm-8.5-6a.5.5 0 0 1 .5.5v.5a.5.5 0 0 1-1 0V4a.5.5 0 0 1 .5-.5zM3 6.5a.5.5 0 0 0-.5.5v.5a.5.5 0 0 0 1 0V7a.5.5 0 0 0-.5-.5z"/></svg> Calculated times (MWL)`;
            methodInfo.textContent = 'Calculation Method: Muslim World League (MWL)';
        }
    }

    async function loadPrayerTimes(mosqueOption) {
        const mosqueId = mosqueOption.value;
        const lat = mosqueOption.dataset.lat;
        const lng = mosqueOption.dataset.lng;
        const name = mosqueOption.dataset.name;
        const address = mosqueOption.dataset.address;
        const city = mosqueOption.dataset.city;

        mosqueName.textContent = name;
        mosqueAddress.textContent = `${address}, ${city}`;

        document.getElementById('mosque-full-address').textContent = `${address}, ${city}, New Zealand`;
        mosqueInfoCard.style.display = 'block';

        try {
            const response = await hilalAPI.get(`prayer-times/mosque/${mosqueId}?lang=en`);

            if (response.success && response.data) {
                const data = response.data;
                const times = data.times;
                const iqamah = data.iqamah || {};
                const hasIqamah = data.has_iqamah;

                showIqamaColumns(hasIqamah);
                mymasjidBadge.style.display = hasIqamah ? 'block' : 'none';

                updateSourceIndicator(data.source || 'aladhan');

                ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'].forEach(prayer => {
                    const el = document.querySelector(`[data-prayer="${prayer}"]`);
                    if (el && times[prayer]) {
                        el.textContent = times[prayer];
                    }

                    const iqamaEl = document.querySelector(`[data-iqama="${prayer}"]`);
                    if (iqamaEl) {
                        if (iqamah[prayer]) {
                            iqamaEl.textContent = iqamah[prayer];
                        } else {
                            iqamaEl.textContent = '-';
                        }
                    }
                });

                if (data.next_prayer) {
                    updateNextPrayerDisplay(data.next_prayer);
                }
            }
        } catch (error) {
            console.error('Error loading prayer times:', error);
            showIqamaColumns(false);
            try {
                const fallbackResponse = await hilalAPI.get(`prayer-times?lat=${lat}&lng=${lng}&lang=en`);
                if (fallbackResponse.success && fallbackResponse.data) {
                    const data = fallbackResponse.data;
                    updateSourceIndicator(data.source || 'aladhan');
                    Object.keys(data.times).forEach(prayer => {
                        const el = document.querySelector(`[data-prayer="${prayer}"]`);
                        if (el) {
                            el.textContent = data.times[prayer];
                        }
                    });
                    if (data.next_prayer) {
                        updateNextPrayerDisplay(data.next_prayer);
                    }
                }
            } catch (fallbackError) {
                console.error('Fallback also failed:', fallbackError);
            }
        }
    }

    function updateNextPrayerDisplay(nextPrayer) {
        document.getElementById('next-prayer-name').textContent = prayerNames[nextPrayer.name];

        const mins = nextPrayer.minutes_until;
        const hours = Math.floor(mins / 60);
        const minutes = mins % 60;
        document.getElementById('next-prayer-countdown').textContent =
            `in ${hours > 0 ? hours + 'h ' : ''}${minutes}m`;

        document.querySelectorAll('.prayer-item').forEach(item => item.classList.remove('next'));
        const nextPrayerEl = document.querySelector(`[data-prayer="${nextPrayer.name}"]`);
        if (nextPrayerEl) {
            nextPrayerEl.closest('.prayer-item').classList.add('next');
        }
    }

    mosqueSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        loadPrayerTimes(option);
    });

    useLocationBtn.addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = 'Getting location...';

        try {
            const location = await hilalGetLocation();
            showIqamaColumns(false);

            mosqueName.textContent = 'Your Location';
            mosqueAddress.textContent = `${location.lat.toFixed(4)}, ${location.lng.toFixed(4)}`;
            mosqueInfoCard.style.display = 'none';

            const response = await hilalAPI.get(`prayer-times?lat=${location.lat}&lng=${location.lng}&lang=en`);

            if (response.success && response.data) {
                const data = response.data;
                const times = data.times;

                updateSourceIndicator(data.source || 'aladhan');

                Object.keys(times).forEach(prayer => {
                    const el = document.querySelector(`[data-prayer="${prayer}"]`);
                    if (el) {
                        el.textContent = times[prayer];
                    }
                });

                if (data.next_prayer) {
                    updateNextPrayerDisplay(data.next_prayer);
                }
            }
        } catch (error) {
            hilalShowNotification(error.message || hilalData.strings.error, 'error');
        }

        this.disabled = false;
        this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 0.25rem;"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg> Use My Location`;
    });

    const initialOption = mosqueSelect.options[mosqueSelect.selectedIndex];
    loadPrayerTimes(initialOption);
});
</script>

<?php
get_footer();
