const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebarOverlay = document.getElementById('sidebar-overlay');

function setSidebar(open) {
    sidebar?.classList.toggle('-translate-x-full', !open);
    sidebar?.classList.toggle('translate-x-0', open);
    sidebarOverlay?.classList.toggle('hidden', !open);
}

sidebarToggle?.addEventListener('click', () => setSidebar(true));
sidebarOverlay?.addEventListener('click', () => setSidebar(false));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setSidebar(false);
    }
});

const importUploadForm = document.getElementById('import-upload-form');
const importLoadingOverlay = document.getElementById('import-loading-overlay');
const uploadProgressBar = document.getElementById('upload-progress-bar');
const uploadProgressLabel = document.getElementById('upload-progress-label');
const uploadProgressPercent = document.getElementById('upload-progress-percent');

importUploadForm?.addEventListener('submit', (event) => {
    const fileInput = importUploadForm.querySelector('input[type="file"]');
    if (!fileInput?.files?.length) {
        return;
    }

    const maxSizeBytes = 3584 * 1024;
    const selectedFile = fileInput.files[0];
    if (selectedFile.size > maxSizeBytes) {
        event.preventDefault();
        window.alert(`File "${selectedFile.name}" berukuran ${(selectedFile.size / 1024 / 1024).toFixed(1)} MB. Batas upload Vercel adalah 3,5 MB. Pecah file menjadi beberapa file yang lebih kecil, atau gunakan format CSV.`);
        return;
    }

    const submitButton = document.getElementById('import-submit-button');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.classList.add('opacity-60', 'cursor-not-allowed');
        submitButton.textContent = 'Mengunggah...';
    }

    importLoadingOverlay?.classList.remove('hidden');
    importLoadingOverlay?.classList.add('flex');

    let progress = 8;
    window.importUploadProgressTimer = window.setInterval(() => {
        progress = Math.min(progress + Math.max(1, Math.round((88 - progress) / 8)), 88);
        if (uploadProgressBar) {
            uploadProgressBar.style.width = `${progress}%`;
        }
        if (uploadProgressPercent) {
            uploadProgressPercent.textContent = `${progress}%`;
        }
        if (uploadProgressLabel) {
            uploadProgressLabel.textContent = progress < 30 ? 'Mengunggah file...' : progress < 70 ? 'Memvalidasi data...' : 'Menyiapkan antrean import...';
        }
    }, 180);
});

const processingImports = document.querySelectorAll('[data-import-status]');

if (processingImports.length > 0) {
    const pollImportStatus = async () => {
        let hasProcessingImport = false;

        for (const element of processingImports) {
            const statusUrl = element.dataset.statusUrl;
            if (!statusUrl) {
                continue;
            }

            try {
                const response = await fetch(statusUrl, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                });
                if (!response.ok) {
                    continue;
                }

                const data = await response.json();
                if (data.status === 'processing') {
                    hasProcessingImport = true;
                    const rowCount = element.querySelector('[data-import-row-count]');
                    const progress = element.querySelector('[data-import-progress]');
                    if (rowCount) {
                        rowCount.textContent = `${new Intl.NumberFormat('id-ID').format(data.row_count)} baris`;
                    }
                    if (progress) {
                        progress.classList.remove('w-2/5');
                        progress.classList.add('w-full');
                    }
                } else {
                    window.location.reload();
                    return;
                }
            } catch {
                hasProcessingImport = true;
            }
        }

        if (!hasProcessingImport) {
            window.location.reload();
        }
    };

    pollImportStatus();
    window.setInterval(pollImportStatus, 3000);
}

const predictionCheckboxes = document.querySelectorAll('.prediction-checkbox');
const selectAllPredictions = document.getElementById('select-all-predictions');
const selectedCount = document.getElementById('selected-count');
const bulkBroadcastButton = document.getElementById('bulk-broadcast-button');

function updateBulkBroadcastSelection() {
    const selected = document.querySelectorAll('.prediction-checkbox:checked').length;
    if (selectedCount) {
        selectedCount.textContent = `(${selected})`;
    }
    if (bulkBroadcastButton) {
        bulkBroadcastButton.disabled = selected === 0;
    }
    if (selectAllPredictions) {
        selectAllPredictions.checked = predictionCheckboxes.length > 0
            && selected === predictionCheckboxes.length;
        selectAllPredictions.indeterminate = selected > 0 && selected < predictionCheckboxes.length;
    }
}

selectAllPredictions?.addEventListener('change', () => {
    predictionCheckboxes.forEach((checkbox) => {
        checkbox.checked = selectAllPredictions.checked;
    });
    updateBulkBroadcastSelection();
});

predictionCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', updateBulkBroadcastSelection);
});

const leadCheckboxes = document.querySelectorAll('.lead-checkbox');
const selectAllLeads = document.getElementById('select-all-leads');
const leadSelectedCount = document.getElementById('lead-selected-count');
const leadBroadcastButton = document.getElementById('lead-broadcast-button');

function updateLeadSelection() {
    const enabledCheckboxes = [...document.querySelectorAll('.lead-checkbox:not(:disabled)')];
    const selected = enabledCheckboxes.filter((checkbox) => checkbox.checked).length;
    if (leadSelectedCount) {
        leadSelectedCount.textContent = `(${selected})`;
    }
    if (leadBroadcastButton) {
        leadBroadcastButton.disabled = selected === 0;
    }
    if (selectAllLeads) {
        selectAllLeads.checked = enabledCheckboxes.length > 0 && selected === enabledCheckboxes.length;
        selectAllLeads.indeterminate = selected > 0 && selected < enabledCheckboxes.length;
        selectAllLeads.disabled = enabledCheckboxes.length === 0;
    }
}

selectAllLeads?.addEventListener('change', () => {
    leadCheckboxes.forEach((checkbox) => {
        if (!checkbox.disabled) {
            checkbox.checked = selectAllLeads.checked;
        }
    });
    updateLeadSelection();
});

leadCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', updateLeadSelection);
});
