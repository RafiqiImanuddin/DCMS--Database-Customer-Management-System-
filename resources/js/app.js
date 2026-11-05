function setSidebar(open) {
    if (window.toggleDcmsSidebar) {
        window.toggleDcmsSidebar(open);
    } else {
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        sidebar?.classList.toggle('-translate-x-full', !open);
        sidebar?.classList.toggle('translate-x-0', open);
        sidebarOverlay?.classList.toggle('hidden', !open);
    }
}

document.querySelectorAll('#sidebar-toggle, [data-sidebar-toggle]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (window.toggleDcmsSidebar) {
            window.toggleDcmsSidebar();
        } else {
            setSidebar(true);
        }
    });
});

document.getElementById('sidebar-overlay')?.addEventListener('click', () => setSidebar(false));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && window.innerWidth < 768) {
        setSidebar(false);
    }
    if ((event.ctrlKey && event.key.toLowerCase() === 'b') || (event.altKey && event.key.toLowerCase() === 's')) {
        event.preventDefault();
        if (window.toggleDcmsSidebar) {
            window.toggleDcmsSidebar();
        }
    }
});

const importUploadForm = document.getElementById('import-upload-form');
const importLoadingOverlay = document.getElementById('import-loading-overlay');
const uploadProgressBar = document.getElementById('upload-progress-bar');
const uploadProgressLabel = document.getElementById('upload-progress-label');
const uploadProgressPercent = document.getElementById('upload-progress-percent');
const importFileInput = document.getElementById('file');
const turnoverFileInput = document.getElementById('turnover_file');
const importSubmitButton = document.getElementById('import-submit-button');

function updateImportSubmitButton() {
    if (!importSubmitButton) return;
    const hasCross = importFileInput?.files?.length > 0;
    const hasTurnover = turnoverFileInput?.files?.length > 0;

    if (hasCross && hasTurnover) {
        importSubmitButton.textContent = '🚀 Unggah & Sinkronkan Semua (Dual Engine)';
    } else if (hasTurnover) {
        importSubmitButton.textContent = '📊 Unggah File Turnover';
    } else if (hasCross) {
        importSubmitButton.textContent = '🎯 Unggah File Cross-Selling';
    } else {
        importSubmitButton.textContent = 'Mulai Import';
    }
}

importFileInput?.addEventListener('change', updateImportSubmitButton);
turnoverFileInput?.addEventListener('change', updateImportSubmitButton);

importUploadForm?.addEventListener('submit', (event) => {
    event.preventDefault();

    const hasCross = importFileInput?.files?.length > 0;
    const hasTurnover = turnoverFileInput?.files?.length > 0;

    if (!hasCross && !hasTurnover) {
        alert('Silakan pilih minimal satu file (Cross-Selling atau Turnover) untuk diunggah.');
        return;
    }

    if (importSubmitButton) {
        importSubmitButton.disabled = true;
        importSubmitButton.textContent = 'Mengunggah data...';
    }

    importLoadingOverlay?.classList.remove('hidden');
    importLoadingOverlay?.classList.add('flex');
    if (uploadProgressBar) uploadProgressBar.style.width = '10%';
    if (uploadProgressPercent) uploadProgressPercent.textContent = '10%';
    if (uploadProgressLabel) uploadProgressLabel.textContent = 'Menyiapkan berkas...';

    const formData = new FormData(importUploadForm);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', importUploadForm.action, true);
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const percent = Math.min(95, Math.round((e.loaded / e.total) * 100));
            if (uploadProgressBar) uploadProgressBar.style.width = `${percent}%`;
            if (uploadProgressPercent) uploadProgressPercent.textContent = `${percent}%`;
            if (uploadProgressLabel) {
                if (percent >= 90) {
                    uploadProgressLabel.textContent = 'Menyimpan & menyinkronkan data ke sistem...';
                } else {
                    uploadProgressLabel.textContent = `Mengunggah berkas (${percent}%)...`;
                }
            }
        }
    });

    xhr.addEventListener('load', () => {
        if (xhr.status >= 200 && xhr.status < 300) {
            if (uploadProgressBar) uploadProgressBar.style.width = '100%';
            if (uploadProgressPercent) uploadProgressPercent.textContent = '100%';
            if (uploadProgressLabel) uploadProgressLabel.textContent = 'Sinkronisasi berhasil! Memuat ulang...';
            setTimeout(() => window.location.reload(), 600);
        } else {
            let errorMsg = 'Gagal mengunggah data.';
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.message) {
                    errorMsg = res.message;
                } else if (res.errors) {
                    errorMsg = Object.values(res.errors).flat().join(' ');
                }
            } catch {}
            alert(errorMsg);
            resetUploadState();
        }
    });

    xhr.addEventListener('error', () => {
        alert('Terjadi kendala jaringan saat mengunggah file.');
        resetUploadState();
    });

    function resetUploadState() {
        if (importSubmitButton) {
            importSubmitButton.disabled = false;
            updateImportSubmitButton();
        }
        importLoadingOverlay?.classList.add('hidden');
        importLoadingOverlay?.classList.remove('flex');
    }

    xhr.send(formData);
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
const leadBulkDeleteButton = document.getElementById('lead-bulk-delete-button');
const leadBulkDeleteCount = document.getElementById('lead-bulk-delete-count');

function updateLeadSelection() {
    const enabledCheckboxes = [...document.querySelectorAll('.lead-checkbox:not(:disabled)')];
    const selected = enabledCheckboxes.filter((checkbox) => checkbox.checked).length;
    if (leadSelectedCount) {
        leadSelectedCount.textContent = `(${selected})`;
    }
    if (leadBulkDeleteCount) {
        leadBulkDeleteCount.textContent = `(${selected})`;
    }
    if (leadBroadcastButton) {
        leadBroadcastButton.disabled = selected === 0;
    }
    if (leadBulkDeleteButton) {
        leadBulkDeleteButton.disabled = selected === 0;
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

// =========================================================================
// UNIVERSAL CUSTOM DROPDOWN ENHANCER (MODERN UX ACROSS ALL MENUS)
// =========================================================================
function formatOptionBadge(text) {
    const trimmed = text.trim();
    const lower = trimmed.toLowerCase();
    let badge = '';

    if (lower === 'whatsapp valid') {
        badge = '<span class="inline-flex size-2 rounded-full bg-emerald-500 shrink-0"></span>';
    } else if (lower.includes('tidak valid')) {
        badge = '<span class="inline-flex size-2 rounded-full bg-amber-500 shrink-0"></span>';
    } else if (lower.includes('tanpa nomor')) {
        badge = '<span class="inline-flex size-2 rounded-full bg-slate-400 shrink-0"></span>';
    } else if (lower === 'tinggi') {
        badge = '<span class="inline-flex size-2 rounded-full bg-emerald-500 shrink-0"></span>';
    } else if (lower === 'sedang') {
        badge = '<span class="inline-flex size-2 rounded-full bg-amber-500 shrink-0"></span>';
    } else if (lower === 'rendah') {
        badge = '<span class="inline-flex size-2 rounded-full bg-rose-500 shrink-0"></span>';
    } else if (lower === 'baru') {
        badge = '<span class="inline-flex size-2 rounded-full bg-sky-500 shrink-0"></span>';
    } else if (lower.includes('overdue') || lower.includes('terlewat')) {
        badge = '<span class="inline-flex size-2 rounded-full bg-rose-500 shrink-0"></span>';
    } else if (lower.includes('completed') || lower.includes('sent') || lower.includes('terkirim')) {
        badge = '<span class="inline-flex size-2 rounded-full bg-emerald-500 shrink-0"></span>';
    } else if (lower.includes('failed') || lower.includes('gagal')) {
        badge = '<span class="inline-flex size-2 rounded-full bg-rose-500 shrink-0"></span>';
    }

    return `${badge}<span class="truncate">${trimmed}</span>`;
}

function initCustomDropdowns(root = document) {
    const selects = root.querySelectorAll('select.dcms-input, select.dcms-select, select[data-dcms-select]');
    selects.forEach((select) => {
        if (select.dataset.dcmsEnhanced === 'true') {
            return;
        }
        select.dataset.dcmsEnhanced = 'true';

        const isXs = select.className.includes('text-xs');
        const isSm = select.className.includes('py-1.5') || select.className.includes('py-2');
        const paddingClass = isSm ? 'py-1.5 px-3' : 'py-2.5 px-3.5';
        const textClass = isXs ? 'text-xs' : 'text-sm';

        const wrapper = document.createElement('div');
        wrapper.className = 'dcms-custom-dropdown relative w-full inline-block text-left';

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = `dcms-dropdown-trigger w-full rounded-xl border border-slate-200 bg-white ${paddingClass} ${textClass} font-semibold text-slate-800 shadow-2xs flex items-center justify-between text-left cursor-pointer hover:border-slate-300 focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 transition`;
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const labelSpan = document.createElement('span');
        labelSpan.className = 'dcms-dropdown-label truncate flex items-center gap-2 flex-1 min-w-0';

        const chevron = document.createElement('span');
        chevron.className = 'dcms-dropdown-chevron shrink-0 ml-2 transition-transform duration-200 text-slate-400';
        chevron.innerHTML = `
            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        `;

        trigger.appendChild(labelSpan);
        trigger.appendChild(chevron);

        const menu = document.createElement('div');
        menu.className = 'dcms-dropdown-menu absolute left-0 top-[calc(100%+6px)] z-[80] hidden w-full min-w-[200px] max-h-60 overflow-y-auto rounded-2xl border border-slate-200/90 bg-white p-1.5 shadow-2xl backdrop-blur-md transition-all duration-150';
        menu.setAttribute('role', 'listbox');

        function buildOptions() {
            menu.innerHTML = '';
            const options = Array.from(select.options);
            const selectedIdx = select.selectedIndex >= 0 ? select.selectedIndex : 0;
            const currentOption = options[selectedIdx];

            if (currentOption) {
                labelSpan.innerHTML = formatOptionBadge(currentOption.text);
            }

            options.forEach((opt, idx) => {
                const isSelected = idx === selectedIdx;
                const item = document.createElement('div');
                item.className = `dcms-dropdown-item flex items-center justify-between rounded-xl px-3 py-2 ${textClass} font-semibold cursor-pointer transition select-none ${
                    isSelected ? 'bg-brand-50 text-brand-900 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900'
                }`;
                item.setAttribute('role', 'option');
                item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                item.setAttribute('data-value', opt.value);

                const itemContent = document.createElement('div');
                itemContent.className = 'flex items-center gap-2 truncate min-w-0';
                itemContent.innerHTML = formatOptionBadge(opt.text);

                const check = document.createElement('span');
                check.className = `dcms-dropdown-check shrink-0 ml-2 text-brand-600 ${isSelected ? '' : 'hidden'}`;
                check.innerHTML = `
                    <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                `;

                item.appendChild(itemContent);
                item.appendChild(check);

                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    select.selectedIndex = idx;
                    select.value = opt.value;
                    labelSpan.innerHTML = formatOptionBadge(opt.text);

                    menu.querySelectorAll('.dcms-dropdown-item').forEach((it, i) => {
                        const sel = i === idx;
                        it.className = `dcms-dropdown-item flex items-center justify-between rounded-xl px-3 py-2 ${textClass} font-semibold cursor-pointer transition select-none ${
                            sel ? 'bg-brand-50 text-brand-900 font-bold' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900'
                        }`;
                        it.setAttribute('aria-selected', sel ? 'true' : 'false');
                        const itCheck = it.querySelector('.dcms-dropdown-check');
                        if (itCheck) {
                            itCheck.classList.toggle('hidden', !sel);
                        }
                    });

                    closeMenu();

                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                });

                menu.appendChild(item);
            });
        }

        buildOptions();

        function openMenu() {
            document.querySelectorAll('.dcms-dropdown-menu:not(.hidden)').forEach((m) => {
                if (m !== menu) {
                    m.classList.add('hidden');
                    const trig = m.closest('.dcms-custom-dropdown')?.querySelector('.dcms-dropdown-trigger');
                    if (trig) {
                        trig.setAttribute('aria-expanded', 'false');
                        trig.querySelector('.dcms-dropdown-chevron')?.classList.remove('rotate-180');
                    }
                }
            });

            const rect = wrapper.getBoundingClientRect();
            if (rect.right > window.innerWidth - 30) {
                menu.classList.remove('left-0');
                menu.classList.add('right-0');
            } else {
                menu.classList.add('left-0');
                menu.classList.remove('right-0');
            }

            menu.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
            chevron.classList.add('rotate-180');

            const activeItem = menu.querySelector('.dcms-dropdown-item[aria-selected="true"]');
            if (activeItem) {
                activeItem.scrollIntoView({ block: 'nearest' });
            }
        }

        function closeMenu() {
            menu.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
            chevron.classList.remove('rotate-180');
        }

        function toggleMenu() {
            if (menu.classList.contains('hidden')) {
                openMenu();
            } else {
                closeMenu();
            }
        }

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);
        wrapper.appendChild(select);

        select.style.position = 'absolute';
        select.style.opacity = '0';
        select.style.pointerEvents = 'none';
        select.style.width = '0';
        select.style.height = '0';
        select.style.margin = '0';
        select.style.padding = '0';
        select.style.border = 'none';
        select.tabIndex = -1;

        const observer = new MutationObserver(() => {
            buildOptions();
        });
        observer.observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['selected'] });

        select.addEventListener('change', () => {
            const currentOption = select.options[select.selectedIndex];
            if (currentOption) {
                labelSpan.innerHTML = formatOptionBadge(currentOption.text);
            }
        });
    });
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.dcms-custom-dropdown')) {
        document.querySelectorAll('.dcms-dropdown-menu:not(.hidden)').forEach((menu) => {
            menu.classList.add('hidden');
            const trigger = menu.closest('.dcms-custom-dropdown')?.querySelector('.dcms-dropdown-trigger');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
                trigger.querySelector('.dcms-dropdown-chevron')?.classList.remove('rotate-180');
            }
        });
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.dcms-dropdown-menu:not(.hidden)').forEach((menu) => {
            menu.classList.add('hidden');
            const trigger = menu.closest('.dcms-custom-dropdown')?.querySelector('.dcms-dropdown-trigger');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
                trigger.querySelector('.dcms-dropdown-chevron')?.classList.remove('rotate-180');
            }
        });
    }
});

document.addEventListener('reset', (e) => {
    setTimeout(() => {
        document.querySelectorAll('.dcms-custom-dropdown').forEach((wrapper) => {
            const select = wrapper.querySelector('select');
            const labelSpan = wrapper.querySelector('.dcms-dropdown-label');
            if (select && labelSpan && select.options[select.selectedIndex]) {
                labelSpan.innerHTML = formatOptionBadge(select.options[select.selectedIndex].text);
            }
        });
    }, 50);
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initCustomDropdowns());
} else {
    initCustomDropdowns();
}

window.initCustomDropdowns = initCustomDropdowns;
