<?php
/**
 * Template Name: Qibla Direction
 * Google-style Qibla Finder with compass
 *
 * @package Hilal
 */

get_header();
?>

<style>
/* Qibla Finder Styles - Google-like design */
.qibla-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    padding: 2rem 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.qibla-header {
    text-align: center;
    margin-bottom: 2rem;
    color: #fff;
}

.qibla-header h1 {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #fff;
}

.qibla-header p {
    color: rgba(255,255,255,0.7);
    font-size: 0.95rem;
}

/* Compass Container */
.qibla-compass-container {
    position: relative;
    width: 320px;
    height: 320px;
    margin: 0 auto;
}

/* Outer glow ring */
.compass-glow {
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, rgba(212,168,67,0.3), rgba(212,168,67,0.1), rgba(212,168,67,0.3));
    animation: rotateGlow 10s linear infinite;
}

@keyframes rotateGlow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Main compass */
.qibla-compass {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(145deg, #2d2d44, #1a1a2e);
    box-shadow:
        0 0 60px rgba(212,168,67,0.2),
        inset 0 0 30px rgba(0,0,0,0.5),
        0 10px 40px rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Compass face */
.compass-face {
    position: relative;
    width: 90%;
    height: 90%;
    border-radius: 50%;
    background: linear-gradient(145deg, #252538, #1e1e30);
    border: 2px solid rgba(212,168,67,0.3);
    transition: transform 0.3s ease-out;
}

/* Degree marks - positioned around the compass face edge */
.compass-marks {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
}

.compass-mark {
    position: absolute;
    width: 2px;
    height: 10px;
    background: rgba(255,255,255,0.35);
    /* Position mark at top, then rotate around center (0,0) */
    top: -132px; /* Slightly inside the compass face edge (144px radius - 12px) */
    left: -1px;
    transform-origin: 1px 132px; /* Rotate around center */
}

.compass-mark.major {
    height: 16px;
    width: 3px;
    background: rgba(255,255,255,0.75);
    top: -135px;
    left: -1.5px;
    transform-origin: 1.5px 135px;
}

/* Cardinal directions */
.compass-cardinal {
    position: absolute;
    font-size: 1.1rem;
    font-weight: 700;
    color: rgba(255,255,255,0.9);
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

.compass-cardinal.north {
    top: 25px;
    left: 50%;
    transform: translateX(-50%);
    color: #e74c3c;
    font-size: 1.3rem;
}

.compass-cardinal.east {
    right: 25px;
    top: 50%;
    transform: translateY(-50%);
}

.compass-cardinal.south {
    bottom: 25px;
    left: 50%;
    transform: translateX(-50%);
}

.compass-cardinal.west {
    left: 25px;
    top: 50%;
    transform: translateY(-50%);
}

/* Qibla direction indicator (the arc/wedge) */
.qibla-indicator {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 100%;
    transform: translate(-50%, -50%);
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.5s ease;
}

.qibla-indicator.active {
    opacity: 1;
}

.qibla-arc {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Qibla needle/arrow */
.qibla-needle {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 8px;
    height: 45%;
    transform-origin: center bottom;
    transform: translate(-50%, -100%) rotate(0deg);
    transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.qibla-needle::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 15px solid transparent;
    border-right: 15px solid transparent;
    border-bottom: 30px solid #D4A843;
    filter: drop-shadow(0 0 10px rgba(212,168,67,0.5));
}

.qibla-needle::after {
    content: '';
    position: absolute;
    top: 25px;
    left: 50%;
    transform: translateX(-50%);
    width: 6px;
    height: calc(100% - 25px);
    background: linear-gradient(to bottom, #D4A843, #b8942e);
    border-radius: 3px;
}

/* Kaaba icon in center */
.compass-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50px;
    height: 50px;
    background: linear-gradient(145deg, #2a2a3e, #1a1a28);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    z-index: 10;
}

.compass-center svg {
    width: 28px;
    height: 28px;
    fill: #D4A843;
}

/* Info panel */
.qibla-info {
    text-align: center;
    margin-top: 2rem;
    color: #fff;
}

.qibla-bearing {
    font-size: 3.5rem;
    font-weight: 300;
    color: #D4A843;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.qibla-bearing sup {
    font-size: 1.5rem;
    vertical-align: super;
}

.qibla-direction-text {
    font-size: 1.1rem;
    color: rgba(255,255,255,0.8);
    margin-bottom: 0.5rem;
}

.qibla-distance {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.5);
}

/* Action button */
.qibla-action {
    margin-top: 2rem;
    text-align: center;
}

.qibla-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, #D4A843, #b8942e);
    color: #1a1a2e;
    font-size: 1rem;
    font-weight: 600;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(212,168,67,0.3);
}

.qibla-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 30px rgba(212,168,67,0.4);
}

.qibla-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.qibla-btn svg {
    width: 20px;
    height: 20px;
}

/* Status messages */
.qibla-status {
    margin-top: 1rem;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-size: 0.9rem;
    display: none;
}

.qibla-status.loading {
    display: inline-block;
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.8);
}

.qibla-status.error {
    display: inline-block;
    background: rgba(231,76,60,0.2);
    color: #e74c3c;
}

/* Compass mode toggle */
.compass-mode-toggle {
    margin-top: 1.5rem;
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.mode-btn {
    padding: 0.6rem 1.25rem;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.5);
    border-radius: 25px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.mode-btn:hover {
    background: rgba(255,255,255,0.12);
    color: rgba(255,255,255,0.7);
}

.mode-btn.active {
    background: linear-gradient(135deg, rgba(212,168,67,0.25), rgba(212,168,67,0.15));
    border-color: #D4A843;
    color: #D4A843;
    box-shadow: 0 0 15px rgba(212,168,67,0.2);
}

.mode-btn .mode-icon {
    width: 16px;
    height: 16px;
    opacity: 0.7;
}

.mode-btn.active .mode-icon {
    opacity: 1;
}

/* Live mode active state for compass */
.compass-face.live-mode {
    border-color: rgba(46, 204, 113, 0.5);
    box-shadow: 0 0 20px rgba(46, 204, 113, 0.2);
}

/* Instructions */
.qibla-instructions {
    max-width: 400px;
    margin: 2rem auto 0;
    padding: 1rem 1.5rem;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.1);
}

.qibla-instructions p {
    color: rgba(255,255,255,0.6);
    font-size: 0.85rem;
    line-height: 1.6;
    margin: 0;
}

.qibla-instructions strong {
    color: #D4A843;
}

/* Live compass indicator */
.compass-live-indicator {
    position: absolute;
    top: -30px;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.25rem 0.75rem;
    background: rgba(46, 204, 113, 0.2);
    border: 1px solid rgba(46, 204, 113, 0.5);
    border-radius: 15px;
    font-size: 0.75rem;
    color: #2ecc71;
    display: none;
}

.compass-live-indicator.active {
    display: block;
}

/* Pulse animation for finding */
@keyframes pulse {
    0% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
    100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
}

.compass-center.finding::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 100%;
    border: 2px solid #D4A843;
    border-radius: 50%;
    animation: pulse 1.5s infinite;
}
</style>

<div class="qibla-page">
    <div class="qibla-header">
        <h1>Qibla Finder</h1>
        <p>Find the direction of Qibla from your location</p>
    </div>

    <div class="qibla-compass-container">
        <div class="compass-glow"></div>
        <div class="compass-live-indicator" id="liveIndicator">LIVE</div>

        <div class="qibla-compass">
            <div class="compass-face" id="compassFace">
                <!-- Degree marks -->
                <div class="compass-marks" id="compassMarks"></div>

                <!-- Cardinal directions -->
                <span class="compass-cardinal north">N</span>
                <span class="compass-cardinal east">E</span>
                <span class="compass-cardinal south">S</span>
                <span class="compass-cardinal west">W</span>

                <!-- Qibla indicator -->
                <div class="qibla-indicator" id="qiblaIndicator">
                    <div class="qibla-needle" id="qiblaNeedle"></div>
                </div>

                <!-- Center Kaaba icon -->
                <div class="compass-center" id="compassCenter">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L3 7v10l9 5 9-5V7l-9-5zm0 2.18l6.5 3.64v7.36L12 18.82l-6.5-3.64V7.82L12 4.18z"/>
                        <rect x="9" y="9" width="6" height="6" rx="0.5"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="qibla-info">
        <div class="qibla-bearing" id="qiblaBearing">
            --<sup>°</sup>
        </div>
        <div class="qibla-direction-text" id="qiblaDirectionText">
            Tap to find Qibla
        </div>
        <div class="qibla-distance" id="qiblaDistance"></div>
    </div>

    <div class="qibla-action">
        <button type="button" class="qibla-btn" id="findQiblaBtn">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3c-.46-4.17-3.77-7.48-7.94-7.94V1h-2v2.06C6.83 3.52 3.52 6.83 3.06 11H1v2h2.06c.46 4.17 3.77 7.48 7.94 7.94V23h2v-2.06c4.17-.46 7.48-3.77 7.94-7.94H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
            </svg>
            <span id="btnText">Find Qibla</span>
        </button>

        <div class="qibla-status" id="qiblaStatus"></div>
    </div>

    <div class="compass-mode-toggle" id="modeToggle" style="display: none;">
        <button class="mode-btn active" data-mode="static">
            <svg class="mode-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
            </svg>
            Static
        </button>
        <button class="mode-btn" data-mode="live">
            <svg class="mode-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3c-.46-4.17-3.77-7.48-7.94-7.94V1h-2v2.06C6.83 3.52 3.52 6.83 3.06 11H1v2h2.06c.46 4.17 3.77 7.48 7.94 7.94V23h2v-2.06c4.17-.46 7.48-3.77 7.94-7.94H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
            </svg>
            Live Compass
        </button>
    </div>

    <div class="qibla-instructions">
        <p>
            <strong>How to use:</strong> Tap "Find Qibla" to locate the direction of the Holy Kaaba from your current position. The golden arrow points towards Qibla.
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const findBtn = document.getElementById('findQiblaBtn');
    const btnText = document.getElementById('btnText');
    const compassFace = document.getElementById('compassFace');
    const compassCenter = document.getElementById('compassCenter');
    const qiblaIndicator = document.getElementById('qiblaIndicator');
    const qiblaNeedle = document.getElementById('qiblaNeedle');
    const bearingEl = document.getElementById('qiblaBearing');
    const directionTextEl = document.getElementById('qiblaDirectionText');
    const distanceEl = document.getElementById('qiblaDistance');
    const statusEl = document.getElementById('qiblaStatus');
    const liveIndicator = document.getElementById('liveIndicator');
    const modeToggle = document.getElementById('modeToggle');

    let qiblaBearing = null;
    let deviceHeading = 0;
    let isLiveMode = false;
    let compassSupported = false;

    // Generate degree marks
    const marksContainer = document.getElementById('compassMarks');
    for (let i = 0; i < 360; i += 10) {
        const mark = document.createElement('div');
        mark.className = 'compass-mark' + (i % 30 === 0 ? ' major' : '');
        mark.style.transform = `rotate(${i}deg)`;
        marksContainer.appendChild(mark);
    }

    // Check for device orientation support
    if (window.DeviceOrientationEvent) {
        if (typeof DeviceOrientationEvent.requestPermission === 'function') {
            compassSupported = true;
        } else {
            compassSupported = true;
        }
    }

    // Find Qibla button click
    findBtn.addEventListener('click', async function() {
        findBtn.disabled = true;
        btnText.textContent = 'Finding...';
        compassCenter.classList.add('finding');
        statusEl.className = 'qibla-status loading';
        statusEl.textContent = 'Getting your location...';

        try {
            // Get location
            const position = await new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation not supported'));
                    return;
                }

                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            });

            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            statusEl.textContent = 'Calculating Qibla direction...';

            // Calculate Qibla (Kaaba coordinates)
            const kaabaLat = 21.4225;
            const kaabaLng = 39.8262;

            // Calculate bearing
            const bearing = calculateBearing(lat, lng, kaabaLat, kaabaLng);
            const distance = calculateDistance(lat, lng, kaabaLat, kaabaLng);

            qiblaBearing = bearing;

            // Update UI
            qiblaNeedle.style.transform = `translate(-50%, -100%) rotate(${bearing}deg)`;
            qiblaIndicator.classList.add('active');

            bearingEl.innerHTML = `${Math.round(bearing)}<sup>°</sup>`;

            const cardinalDir = getCardinalDirection(bearing);
            directionTextEl.textContent = `${cardinalDir} - ${bearing.toFixed(1)}° from North`;
            distanceEl.textContent = `Distance to Kaaba: ${distance.toFixed(0)} km`;

            statusEl.style.display = 'none';

            // Show mode toggle if compass supported
            if (compassSupported) {
                modeToggle.style.display = 'flex';
            }

            // Try to enable live compass
            if (compassSupported) {
                requestCompassPermission();
            }

        } catch (error) {
            console.error('Qibla finder error:', error);
            statusEl.className = 'qibla-status error';
            statusEl.textContent = error.message || 'An error occurred';
        }

        compassCenter.classList.remove('finding');
        findBtn.disabled = false;
        btnText.textContent = 'Update Qibla';
    });

    // Calculate bearing between two points
    function calculateBearing(lat1, lng1, lat2, lng2) {
        const toRad = deg => deg * Math.PI / 180;
        const toDeg = rad => rad * 180 / Math.PI;

        lat1 = toRad(lat1);
        lat2 = toRad(lat2);
        const dLng = toRad(lng2 - lng1);

        const x = Math.cos(lat2) * Math.sin(dLng);
        const y = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLng);

        let bearing = toDeg(Math.atan2(x, y));
        return (bearing + 360) % 360;
    }

    // Calculate distance using Haversine formula
    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth's radius in km
        const toRad = deg => deg * Math.PI / 180;

        const dLat = toRad(lat2 - lat1);
        const dLng = toRad(lng2 - lng1);

        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Get cardinal direction
    function getCardinalDirection(bearing) {
        const directions = ['North', 'Northeast', 'East', 'Southeast', 'South', 'Southwest', 'West', 'Northwest'];
        const index = Math.round(bearing / 45) % 8;
        return directions[index];
    }

    // Request compass permission (iOS 13+)
    async function requestCompassPermission() {
        if (typeof DeviceOrientationEvent.requestPermission === 'function') {
            try {
                const permission = await DeviceOrientationEvent.requestPermission();
                if (permission === 'granted') {
                    enableLiveCompass();
                }
            } catch (e) {
                console.log('Compass permission denied');
            }
        } else {
            enableLiveCompass();
        }
    }

    // Enable live compass mode
    function enableLiveCompass() {
        window.addEventListener('deviceorientationabsolute', handleOrientation, true);
        window.addEventListener('deviceorientation', handleOrientation, true);
    }

    // Handle device orientation
    function handleOrientation(event) {
        let heading = event.alpha;

        // For iOS, we need to adjust
        if (event.webkitCompassHeading) {
            heading = event.webkitCompassHeading;
        }

        if (heading !== null && isLiveMode && qiblaBearing !== null) {
            deviceHeading = heading;

            // Rotate compass face opposite to device heading
            // so Qibla needle always points correctly
            const adjustedRotation = qiblaBearing - heading;
            compassFace.style.transform = `rotate(${-heading}deg)`;

            liveIndicator.classList.add('active');
        }
    }

    // Mode toggle buttons
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            isLiveMode = this.dataset.mode === 'live';

            if (isLiveMode) {
                compassFace.classList.add('live-mode');
                liveIndicator.classList.add('active');
                directionTextEl.textContent = 'Point your phone to find Qibla';
            } else {
                compassFace.style.transform = 'rotate(0deg)';
                compassFace.classList.remove('live-mode');
                liveIndicator.classList.remove('active');
                if (qiblaBearing !== null) {
                    const cardinalDir = getCardinalDirection(qiblaBearing);
                    directionTextEl.textContent = `${cardinalDir} - ${qiblaBearing.toFixed(1)}° from North`;
                }
            }
        });
    });
});
</script>

<?php
get_footer();
