<?php
/**
 * Template Name: Crescent Sighting
 *
 * Simplified crescent sighting submission form - PDF and details only.
 *
 * @package Hilal
 */

get_header();
?>

<section class="section">
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div style="font-size: 2.5rem; margin-bottom: 8px;">🌙</div>
            <h1>Crescent Sighting</h1>
            <p class="subtitle">Share your moon sighting with the community</p>
        </div>

        <!-- Submission Form Card -->
        <div class="card" style="max-width: 640px; margin: 0 auto;">
            <div class="card-body">
                <form id="crescent-sighting-form" enctype="multipart/form-data">
                    <!-- PDF Upload -->
                    <div class="form-group">
                        <label class="form-label">PDF Document</label>
                        <div class="file-upload-area" id="pdf-upload-area">
                            <div class="icon">📄</div>
                            <div class="text">Click to upload or drag & drop</div>
                            <div class="hint">PDF files only, up to 20MB</div>
                        </div>
                        <input type="file" name="attachment" id="pdf-input" accept=".pdf,application/pdf" style="display: none;">
                        <div id="pdf-preview" style="display: none; margin-top: 10px; padding: 12px; background: var(--hilal-gray-100); border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span style="font-size: 2rem;">📄</span>
                                <div style="flex: 1;">
                                    <div id="pdf-filename" style="font-weight: 500;"></div>
                                    <div id="pdf-filesize" style="font-size: 0.875rem; color: var(--hilal-gray-500);"></div>
                                </div>
                                <button type="button" id="remove-pdf" class="btn btn-sm btn-secondary">Remove</button>
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="form-group">
                        <label class="form-label">Details <span class="text-danger">*</span></label>
                        <textarea name="details" id="details-input" class="form-control" rows="6" required
                                  placeholder="Describe the sighting including:&#10;- Location (city, country)&#10;- Date and time of observation&#10;- Weather conditions&#10;- Any additional observations..."></textarea>
                    </div>

                    <!-- Confirmation Checkbox -->
                    <div class="form-checkbox" style="margin-bottom: 22px;">
                        <input type="checkbox" name="confirm" id="confirm-checkbox" required>
                        <label for="confirm-checkbox">
                            I confirm this is an honest observation
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary btn-lg w-full" id="submit-btn">
                        Submit Crescent Sighting
                    </button>

                    <p class="text-muted text-center" style="font-size: 0.6875rem; margin-top: 12px;">
                        Your submission will be reviewed before being published
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('crescent-sighting-form');
    if (!form) return;

    const pdfUploadArea = document.getElementById('pdf-upload-area');
    const pdfInput = document.getElementById('pdf-input');
    const pdfPreview = document.getElementById('pdf-preview');
    const pdfFilename = document.getElementById('pdf-filename');
    const pdfFilesize = document.getElementById('pdf-filesize');
    const removePdfBtn = document.getElementById('remove-pdf');
    const submitBtn = document.getElementById('submit-btn');

    let selectedFile = null;

    // PDF upload area click
    pdfUploadArea.addEventListener('click', () => pdfInput.click());

    // PDF drag & drop
    pdfUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        pdfUploadArea.style.borderColor = 'var(--hilal-gold)';
    });

    pdfUploadArea.addEventListener('dragleave', () => {
        pdfUploadArea.style.borderColor = '';
    });

    pdfUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        pdfUploadArea.style.borderColor = '';
        if (e.dataTransfer.files.length) {
            handleFile(e.dataTransfer.files[0]);
        }
    });

    // PDF input change
    pdfInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            handleFile(e.target.files[0]);
        }
    });

    function handleFile(file) {
        // Validate file type
        if (file.type !== 'application/pdf') {
            hilalShowNotification('Please select a PDF file', 'error');
            return;
        }

        // Validate file size (20MB max)
        if (file.size > 20 * 1024 * 1024) {
            hilalShowNotification('File is too large. Maximum size is 20MB', 'error');
            return;
        }

        selectedFile = file;
        showPreview(file);
    }

    function showPreview(file) {
        pdfFilename.textContent = file.name;
        pdfFilesize.textContent = formatFileSize(file.size);
        pdfPreview.style.display = 'block';
        pdfUploadArea.style.display = 'none';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Remove PDF
    removePdfBtn.addEventListener('click', () => {
        pdfInput.value = '';
        selectedFile = null;
        pdfPreview.style.display = 'none';
        pdfUploadArea.style.display = 'block';
    });

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const details = document.getElementById('details-input').value.trim();
        if (!details) {
            hilalShowNotification('Please provide details about the sighting', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        try {
            // Upload PDF first if present
            let attachmentId = null;

            if (selectedFile) {
                const pdfData = new FormData();
                pdfData.append('file', selectedFile);

                const uploadResponse = await fetch(hilalData.apiUrl + 'sighting/upload-attachment', {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': hilalData.nonce
                    },
                    body: pdfData
                });

                const uploadResult = await uploadResponse.json();
                if (uploadResult.success && uploadResult.data.id) {
                    attachmentId = uploadResult.data.id;
                } else {
                    throw new Error(uploadResult.message || 'Failed to upload PDF');
                }
            }

            // Submit the sighting
            const sightingData = {
                details: details,
                attachment_id: attachmentId
            };

            const response = await hilalAPI.post('sighting-report', sightingData);

            if (response.success) {
                hilalShowNotification(
                    'Your sighting has been submitted! It will be reviewed soon.',
                    'success'
                );
                form.reset();
                selectedFile = null;
                pdfPreview.style.display = 'none';
                pdfUploadArea.style.display = 'block';
            }
        } catch (error) {
            hilalShowNotification(error.message || 'Error submitting sighting', 'error');
        }

        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Crescent Sighting';
    });
});
</script>

<?php
get_footer();
