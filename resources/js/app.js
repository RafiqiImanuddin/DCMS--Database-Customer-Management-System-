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
const batchPanel = document.getElementById('import-batch-panel');
const batchList = document.getElementById('import-batch-list');
const batchSummary = document.getElementById('import-batch-summary');
const selectAllBatches = document.getElementById('select-all-import-batches');

const MAX_BATCH_BYTES = 3584 * 1024;
let importBatches = [];

function csvEscape(value) {
    const text = value == null ? '' : String(value);
    return /[",\r\n]/.test(text) ? `"${text.replaceAll('"', '""')}"` : text;
}

function splitCsvRecords(text) {
    const records = [];
    let record = '';
    let quoted = false;

    for (let index = 0; index < text.length; index++) {
        const character = text[index];
        record += character;

        if (character === '"') {
            if (quoted && text[index + 1] === '"') {
                record += '"';
                index++;
            } else {
                quoted = !quoted;
            }
        } else if ((character === '\n' || character === '\r') && !quoted) {
            if (record.trim() !== '') {
                records.push(record);
            }
            record = '';
        }
    }

    if (record.trim() !== '') {
        records.push(record);
    }

    return records;
}

function fileBatches(headerRecord, dataRecords, originalName) {
    const headerBytes = new Blob([headerRecord]).size;
    if (headerBytes > MAX_BATCH_BYTES) {
        throw new Error('Header file terlalu besar untuk diproses sebagai batch Vercel.');
    }

    const batches = [];
    let currentRecords = [headerRecord];
    let currentBytes = headerBytes;

    for (const record of dataRecords) {
        const recordBytes = new Blob([record]).size;
        if (recordBytes > MAX_BATCH_BYTES - headerBytes) {
            throw new Error('Ada satu baris data yang melebihi kapasitas batch 3,5 MB.');
        }
        if (currentRecords.length > 1 && currentBytes + recordBytes > MAX_BATCH_BYTES) {
            batches.push(currentRecords.join(''));
            currentRecords = [headerRecord];
            currentBytes = headerBytes;
        }
        currentRecords.push(record);
        currentBytes += recordBytes;
    }

    if (currentRecords.length > 1) {
        batches.push(currentRecords.join(''));
    }

    const stem = originalName.replace(/\.(xlsx|xls|csv)$/i, '');
    return batches.map((content, index) => new File(
        [content],
        `${stem}-batch-${index + 1}.csv`,
        { type: 'text/csv' },
    ));
}

async function prepareImportBatches(file) {
    const extension = file.name.split('.').pop()?.toLowerCase();
    if (extension === 'csv') {
        const records = splitCsvRecords(await file.text());
        if (records.length < 2) {
            throw new Error('CSV tidak memiliki header dan minimal satu baris data.');
        }
        return fileBatches(records[0].endsWith('\n') || records[0].endsWith('\r') ? records[0] : `${records[0]}\n`, records.slice(1), file.name);
    }

    if (extension === 'xlsx') {
        const { default: ExcelJS } = await import('exceljs');
        const workbook = new ExcelJS.Workbook();
        await workbook.xlsx.load(await file.arrayBuffer());
        const sheet = workbook.worksheets[0];
        if (!sheet) {
            throw new Error('File XLSX tidak memiliki worksheet.');
        }

        const csvRecords = [];
        sheet.eachRow({ includeEmpty: false }, (row) => {
            const values = [];
            for (let column = 1; column <= sheet.columnCount; column++) {
                const cell = row.getCell(column);
                values.push(csvEscape(cell.text ?? cell.value ?? ''));
            }
            csvRecords.push(`${values.join(',')}\n`);
        });

        if (csvRecords.length < 2) {
            throw new Error('XLSX tidak memiliki header dan minimal satu baris data.');
        }
        return fileBatches(csvRecords[0], csvRecords.slice(1), file.name);
    }

    if (file.size <= MAX_BATCH_BYTES) {
        return [file];
    }

    throw new Error('File XLS lebih besar dari batas Vercel. Simpan ulang sebagai XLSX atau CSV lalu pilih file tersebut.');
}

function renderImportBatches(files) {
    batchList.replaceChildren();
    importBatches = files;

    files.forEach((file, index) => {
        const label = document.createElement('label');
        label.className = 'flex items-start gap-3 rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = true;
        checkbox.dataset.batchIndex = String(index);
        checkbox.className = 'batch-checkbox mt-0.5 size-4 rounded border-slate-300 text-brand-600';

        const details = document.createElement('span');
        details.innerHTML = `<span class="block font-bold text-slate-700">${index + 1}. ${file.name}</span><span class="mt-1 block text-xs text-slate-500">${(file.size / 1024 / 1024).toFixed(2)} MB</span>`;

        label.append(checkbox, details);
        batchList.append(label);
    });

    if (selectAllBatches) {
        selectAllBatches.checked = true;
    }
    batchSummary.textContent = `${files.length} batch dibuat, masing-masing maksimal 3,5 MB`;
    batchPanel?.classList.remove('hidden');
}

selectAllBatches?.addEventListener('change', () => {
    batchList.querySelectorAll('.batch-checkbox').forEach((checkbox) => {
        checkbox.checked = selectAllBatches.checked;
    });
});

const importFileInput = document.getElementById('file');
importFileInput?.addEventListener('change', async () => {
    const file = importFileInput.files?.[0];
    if (!file) {
        batchPanel?.classList.add('hidden');
        importBatches = [];
        return;
    }

    batchPanel?.classList.remove('hidden');
    batchSummary.textContent = 'Sedang memecah file menjadi batch...';
    batchList.replaceChildren();

    try {
        renderImportBatches(await prepareImportBatches(file));
    } catch (error) {
        batchPanel?.classList.remove('hidden');
        batchSummary.textContent = error instanceof Error ? error.message : 'File tidak dapat diproses.';
        batchList.replaceChildren();
        importBatches = [];
    }
});

importUploadForm?.addEventListener('submit', (event) => {
    event.preventDefault();

    const fileInput = importUploadForm.querySelector('input[type="file"]');
    if (!fileInput?.files?.length) {
        return;
    }

    const selectedIndexes = Array.from(batchList.querySelectorAll('.batch-checkbox:checked'))
        .map((checkbox) => Number(checkbox.dataset.batchIndex));

    if (importBatches.length === 0 || selectedIndexes.length === 0) {
        batchSummary.textContent = 'Pilih minimal satu batch untuk diunggah.';
        batchPanel?.classList.remove('hidden');
        return;
    }

    const submitButton = document.getElementById('import-submit-button');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Mengunggah batch...';
    }

    importLoadingOverlay?.classList.remove('hidden');
    importLoadingOverlay?.classList.add('flex');

    const uploadToken = importUploadForm.querySelector('input[name="_token"]')?.value;
    let completed = 0;

    (async () => {
        try {
            for (const index of selectedIndexes) {
                const batch = importBatches[index];
                const formData = new FormData();
                if (uploadToken) {
                    formData.append('_token', uploadToken);
                }
                formData.append('file', batch, batch.name);

                const response = await fetch(importUploadForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    let errorMsg = `Batch ${index + 1} gagal diunggah (HTTP ${response.status}).`;
                    try {
                        const errorData = await response.json();
                        if (errorData.message) {
                            errorMsg = errorData.message;
                        } else if (errorData.errors) {
                            errorMsg = Object.values(errorData.errors).flat().join(' ');
                        }
                    } catch {}
                    throw new Error(errorMsg);
                }

                const result = await response.json().catch(() => ({}));

                completed++;
                const progress = Math.round((completed / selectedIndexes.length) * 100);
                const batchStatus = result.status === 'failed'
                    ? ` ⚠️ (${result.failed_count ?? 0} baris gagal)`
                    : result.status === 'completed'
                        ? ` ✓ (${result.success_count ?? 0} baris berhasil)`
                        : '';
                uploadProgressLabel.textContent = `Batch ${completed} dari ${selectedIndexes.length} selesai${batchStatus}`;
                uploadProgressPercent.textContent = `${progress}%`;
                uploadProgressBar.style.width = `${progress}%`;
            }

            window.location.reload();
        } catch (error) {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Coba upload lagi';
            }
            importLoadingOverlay?.classList.add('hidden');
            importLoadingOverlay?.classList.remove('flex');
            batchSummary.textContent = error instanceof Error ? error.message : 'Upload batch gagal.';
            batchPanel?.classList.remove('hidden');
        }
    })();
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
