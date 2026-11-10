/**
 * Quotation Letter Real-Time Editor & Print Synchronizer
 * DCMS Gadgetmart
 */

(function() {
    'use strict';

    const idMonths = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    function formatIndonesianDate(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        const year = parts[0];
        const month = idMonths[parseInt(parts[1], 10) - 1] || parts[1];
        const day = parts[2];
        return day + ' ' + month + ' ' + year;
    }

    function formatRupiah(num) {
        return 'Rp. ' + new Intl.NumberFormat('id-ID').format(num || 0);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Main Real-Time Sync Function
    function syncPreview() {
        // 1. Recipient
        const custNameInput = document.getElementById('inputCustomerName');
        const custDestInput = document.getElementById('inputCustomerDestination');
        const prevCustName = document.getElementById('prevCustomerName');
        const prevCustDest = document.getElementById('prevCustomerDest');

        if (prevCustName) {
            prevCustName.innerText = (custNameInput && custNameInput.value.trim() !== '') ? custNameInput.value : 'PT. ';
        }
        if (prevCustDest) {
            prevCustDest.innerText = (custDestInput && custDestInput.value.trim() !== '') ? custDestInput.value : 'di-tempat';
        }

        // 2. Greeting & Texts
        const greetingInput = document.getElementById('inputGreetingText');
        const prevGreeting = document.getElementById('prevGreetingText');
        if (prevGreeting && greetingInput) {
            prevGreeting.innerText = greetingInput.value;
        }

        const notesInput = document.getElementById('inputNotes');
        const prevNotes = document.getElementById('prevNotes');
        if (prevNotes && notesInput) {
            prevNotes.innerText = notesInput.value;
        }

        const closingInput = document.getElementById('inputClosingText');
        const prevClosing = document.getElementById('prevClosingText');
        if (prevClosing && closingInput) {
            prevClosing.innerText = closingInput.value;
        }

        // 3. Date & City
        const cityInput = document.getElementById('inputLetterCity');
        const dateInput = document.getElementById('inputLetterDate');
        const prevDateCity = document.getElementById('prevDateCity');
        if (prevDateCity) {
            const city = (cityInput && cityInput.value.trim() !== '') ? cityInput.value : 'Banjarmasin';
            const dateVal = dateInput ? dateInput.value : '';
            prevDateCity.innerText = city + ', ' + formatIndonesianDate(dateVal);
        }

        // 4. Signer & Title
        const signerNameInput = document.getElementById('inputSignerName');
        const signerTitleInput = document.getElementById('inputSignerTitle');
        const prevSignerName = document.getElementById('prevSignerName');
        const prevSignerTitle = document.getElementById('prevSignerTitle');

        if (prevSignerName) {
            prevSignerName.innerText = (signerNameInput && signerNameInput.value.trim() !== '') ? signerNameInput.value : 'RAFIQI IMANUDDIN';
        }
        if (prevSignerTitle) {
            prevSignerTitle.innerText = (signerTitleInput && signerTitleInput.value.trim() !== '') ? signerTitleInput.value : 'Marketing Gadget Mart';
        }

        // 5. Table Items & Calculations
        const rows = document.querySelectorAll('.item-row');
        const tbody = document.getElementById('prevTableBody');
        let tbodyHtml = '';
        let subtotal = 0;

        rows.forEach(function(row) {
            const descInput = row.querySelector('.item-description');
            const priceInput = row.querySelector('.item-price');
            const qtyInput = row.querySelector('.item-qty');
            const totalDisplay = row.querySelector('.item-total-display');

            const desc = descInput ? descInput.value : '';
            const price = priceInput ? (parseInt(priceInput.value, 10) || 0) : 0;
            const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;
            const total = price * qty;
            subtotal += total;

            if (totalDisplay) {
                totalDisplay.innerText = formatRupiah(total);
            }

            tbodyHtml += '<tr class="border border-black">' +
                '<td class="border border-black px-3 py-2 text-left font-normal text-black leading-snug">' + escapeHtml(desc) + '</td>' +
                '<td class="border border-black px-3 py-2 text-center font-normal text-black whitespace-nowrap">' + formatRupiah(price) + '</td>' +
                '<td class="border border-black px-3 py-2 text-center font-normal text-black">' + qty + '</td>' +
                '<td class="border border-black px-3 py-2 text-center font-normal text-black whitespace-nowrap">' + formatRupiah(total) + '</td>' +
                '</tr>';
        });

        if (rows.length === 0) {
            tbodyHtml = '<tr class="border border-black">' +
                '<td class="border border-black px-3 py-2 text-left text-slate-400 italic" colspan="4">Belum ada barang dimasukkan</td>' +
                '</tr>';
        }

        // 6. Diskon & Footer Table Row Logic
        const discountInput = document.getElementById('inputDiscount');
        const discount = discountInput ? Math.max(0, parseInt(discountInput.value, 10) || 0) : 0;
        const finalTotal = Math.max(0, subtotal - discount);

        if (discount > 0) {
            tbodyHtml += '<tr class="border border-black font-semibold bg-slate-50">' +
                '<td class="border border-black px-3 py-1.5 text-right uppercase text-[11px]" colspan="3">SUBTOTAL:</td>' +
                '<td class="border border-black px-3 py-1.5 text-center whitespace-nowrap text-[11px] font-mono">' + formatRupiah(subtotal) + '</td>' +
                '</tr>' +
                '<tr class="border border-black font-semibold text-rose-600 bg-rose-50/50">' +
                '<td class="border border-black px-3 py-1.5 text-right uppercase text-[11px]" colspan="3">DISKON:</td>' +
                '<td class="border border-black px-3 py-1.5 text-center whitespace-nowrap text-[11px] font-mono">- ' + formatRupiah(discount) + '</td>' +
                '</tr>' +
                '<tr class="border border-black font-bold bg-slate-100 text-black">' +
                '<td class="border border-black px-3 py-2 text-right uppercase text-xs" colspan="3">TOTAL AKHIR:</td>' +
                '<td class="border border-black px-3 py-2 text-center whitespace-nowrap text-xs font-mono font-black">' + formatRupiah(finalTotal) + '</td>' +
                '</tr>';
        } else if (rows.length > 1) {
            tbodyHtml += '<tr class="border border-black font-bold bg-slate-50 text-black">' +
                '<td class="border border-black px-3 py-2 text-right uppercase text-xs" colspan="3">TOTAL:</td>' +
                '<td class="border border-black px-3 py-2 text-center whitespace-nowrap text-xs font-mono font-black">' + formatRupiah(subtotal) + '</td>' +
                '</tr>';
        }

        if (tbody) {
            tbody.innerHTML = tbodyHtml;
        }

        const subtotalEl = document.getElementById('subtotalDisplay');
        if (subtotalEl) {
            subtotalEl.innerText = formatRupiah(subtotal);
        }

        const grandTotalEl = document.getElementById('grandTotalDisplay');
        if (grandTotalEl) {
            grandTotalEl.innerText = formatRupiah(finalTotal);
        }
    }

    // Calculation helper for single row
    window.calcRow = function(el) {
        syncPreview();
    };

    // Add new product row
    window.addNewItemRow = function(desc, price, qty) {
        desc = desc || '';
        price = price || 0;
        qty = qty || 1;

        const container = document.getElementById('itemsContainer');
        if (!container) return;

        const rows = container.querySelectorAll('.item-row');
        const nextIdx = rows.length;

        const newRow = document.createElement('div');
        newRow.className = 'item-row rounded-xl border border-slate-200 bg-slate-50/60 p-3 space-y-2 relative';
        newRow.dataset.index = nextIdx;

        newRow.innerHTML = '<div class="flex items-center justify-between">' +
            '<span class="text-[11px] font-bold text-slate-600">Barang #<span class="row-number">' + (nextIdx + 1) + '</span></span>' +
            '<button type="button" onclick="removeItemRow(this)" class="text-slate-400 hover:text-rose-600 transition" title="Hapus baris ini">' +
            '<svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>' +
            '</button>' +
            '</div>' +
            '<div>' +
            '<input type="text" name="items[' + nextIdx + '][description]" value="' + escapeHtml(desc) + '" placeholder="Deskripsi barang / tipe gadget / spesifikasi..." required class="item-description w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 font-medium focus:border-indigo-500 focus:outline-none">' +
            '</div>' +
            '<div class="grid grid-cols-12 gap-2">' +
            '<div class="col-span-6">' +
            '<label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Harga Satuan (Rp)</label>' +
            '<input type="number" name="items[' + nextIdx + '][price]" value="' + price + '" min="0" step="1000" required class="item-price w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-800 font-mono font-bold focus:border-indigo-500 focus:outline-none">' +
            '</div>' +
            '<div class="col-span-2">' +
            '<label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Qty</label>' +
            '<input type="number" name="items[' + nextIdx + '][qty]" value="' + qty + '" min="1" required class="item-qty w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-center font-bold text-slate-800 focus:border-indigo-500 focus:outline-none">' +
            '</div>' +
            '<div class="col-span-4">' +
            '<label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Total (Rp)</label>' +
            '<div class="item-total-display rounded-lg bg-slate-200/70 px-2 py-1.5 text-xs font-mono font-black text-slate-800 text-right">' +
            formatRupiah(price * qty) +
            '</div>' +
            '</div>' +
            '</div>';

        container.appendChild(newRow);
        reindexRows();
        syncPreview();
    };

    // Remove product row
    window.removeItemRow = function(btn) {
        const row = btn.closest('.item-row');
        if (!row) return;

        const container = document.getElementById('itemsContainer');
        if (container && container.querySelectorAll('.item-row').length <= 1) {
            alert('Minimal harus ada 1 baris barang dalam penawaran.');
            return;
        }

        row.remove();
        reindexRows();
        syncPreview();
    };

    function reindexRows() {
        const container = document.getElementById('itemsContainer');
        if (!container) return;
        const rows = container.querySelectorAll('.item-row');
        rows.forEach(function(row, i) {
            row.dataset.index = i;
            const numSpan = row.querySelector('.row-number');
            if (numSpan) numSpan.innerText = (i + 1);

            const descInput = row.querySelector('.item-description');
            if (descInput) descInput.name = 'items[' + i + '][description]';

            const priceInput = row.querySelector('.item-price');
            if (priceInput) priceInput.name = 'items[' + i + '][price]';

            const qtyInput = row.querySelector('.item-qty');
            if (qtyInput) qtyInput.name = 'items[' + i + '][qty]';
        });
    }

    // Select from pricelist helper
    window.applyPricelistSelection = function() {
        const searchInput = document.getElementById('pricelistQuickSearch');
        const selectedVal = searchInput ? searchInput.value.trim() : '';
        if (!selectedVal) return;

        const datalist = document.getElementById('pricelistDatalist');
        const opt = datalist ? datalist.querySelector('option[value="' + CSS.escape(selectedVal) + '"]') : null;
        let price = 0;
        if (opt && opt.dataset.price) {
            price = parseInt(opt.dataset.price, 10) || 0;
        }

        const rows = document.querySelectorAll('.item-row');
        const lastRow = rows.length > 0 ? rows[rows.length - 1] : null;
        const lastDesc = lastRow ? (lastRow.querySelector('.item-description') ? lastRow.querySelector('.item-description').value.trim() : 'not-empty') : 'not-empty';

        if (lastRow && lastDesc === '') {
            const descField = lastRow.querySelector('.item-description');
            const priceField = lastRow.querySelector('.item-price');
            if (descField) descField.value = selectedVal;
            if (priceField) priceField.value = price;
        } else {
            window.addNewItemRow(selectedVal, price, 1);
        }

        if (searchInput) searchInput.value = '';
        syncPreview();
    };

    // Signature Editor State
    let rawSignatureImage = null; // HTMLImageElement
    let currentRotation = 0; // 0, 90, 180, 270 degrees
    let isTransparentEnabled = false;
    let signatureTolerance = 75; // 30% to 95%
    let signatureScale = 100; // 40% to 200%

    function applySignatureScaleToPreview() {
        const sigImg = document.getElementById('prevSignatureImg');
        if (sigImg) {
            const h = Math.round(80 * (signatureScale / 100));
            sigImg.style.height = h + 'px';
        }

        const scaleBadge = document.getElementById('scaleIndicatorBadge');
        if (scaleBadge) {
            scaleBadge.innerText = signatureScale + '%';
        }

        const scaleDisplay = document.getElementById('scaleDisplay');
        if (scaleDisplay) {
            scaleDisplay.innerText = signatureScale + '%';
        }

        const scaleRange = document.getElementById('signatureScaleRange');
        if (scaleRange && parseInt(scaleRange.value, 10) !== signatureScale) {
            scaleRange.value = signatureScale;
        }

        const scaleInput = document.getElementById('inputSignatureScale');
        if (scaleInput) {
            scaleInput.value = signatureScale;
        }
    }

    function updateSignatureUI() {
        const toolsPanel = document.getElementById('signatureToolsPanel');
        const rotIndicator = document.getElementById('rotationIndicator');
        const btnTrans = document.getElementById('btnToggleTransparent');
        const labelTrans = document.getElementById('labelTransparent');
        const sliderWrapper = document.getElementById('transparentSliderWrapper');
        const toleranceDisplay = document.getElementById('toleranceDisplay');

        if (toolsPanel) {
            if (rawSignatureImage) {
                toolsPanel.classList.remove('hidden');
            } else {
                toolsPanel.classList.add('hidden');
            }
        }

        if (rotIndicator) {
            rotIndicator.innerText = currentRotation + '°';
        }

        if (btnTrans && labelTrans) {
            if (isTransparentEnabled) {
                btnTrans.className = 'inline-flex items-center gap-1.5 rounded-lg border border-emerald-600 bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white shadow-2xs cursor-pointer transition';
                labelTrans.innerText = '✅ Transparan Aktif';
                if (sliderWrapper) sliderWrapper.classList.remove('hidden');
            } else {
                btnTrans.className = 'inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition cursor-pointer shadow-2xs';
                labelTrans.innerText = '✨ Hapus Background';
                if (sliderWrapper) sliderWrapper.classList.add('hidden');
            }
        }

        if (toleranceDisplay) {
            toleranceDisplay.innerText = signatureTolerance + '%';
        }

        applySignatureScaleToPreview();
    }

    function processSignatureImage() {
        if (!rawSignatureImage) return;

        const origW = rawSignatureImage.naturalWidth || rawSignatureImage.width;
        const origH = rawSignatureImage.naturalHeight || rawSignatureImage.height;
        if (!origW || !origH) return;

        const isSideways = (currentRotation === 90 || currentRotation === 270);
        const canvas = document.createElement('canvas');
        canvas.width = isSideways ? origH : origW;
        canvas.height = isSideways ? origW : origH;

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        // Apply rotation
        ctx.save();
        ctx.translate(canvas.width / 2, canvas.height / 2);
        ctx.rotate((currentRotation * Math.PI) / 180);
        ctx.drawImage(rawSignatureImage, -origW / 2, -origH / 2);
        ctx.restore();

        // Background transparency removal
        if (isTransparentEnabled) {
            const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imgData.data;

            // Threshold calculation based on percentage (30-95%)
            const threshold = (signatureTolerance / 100) * 255;
            const softRange = 40;
            const lowerLimit = Math.max(0, threshold - softRange);

            for (let i = 0; i < data.length; i += 4) {
                const r = data[i];
                const g = data[i + 1];
                const b = data[i + 2];
                // Luminance formula
                const lum = 0.299 * r + 0.587 * g + 0.114 * b;

                if (lum >= threshold) {
                    data[i + 3] = 0; // Pure transparent paper background
                } else if (lum > lowerLimit) {
                    // Smooth antialiased falloff
                    const factor = 1 - ((lum - lowerLimit) / (threshold - lowerLimit));
                    data[i + 3] = Math.round(data[i + 3] * factor);
                }
            }

            ctx.putImageData(imgData, 0, 0);
        }

        const dataUrl = canvas.toDataURL('image/png');

        const sigImg = document.getElementById('prevSignatureImg');
        if (sigImg) {
            sigImg.src = dataUrl;
            sigImg.style.display = 'block';
        }

        const base64Input = document.getElementById('inputSignatureBase64');
        if (base64Input) {
            base64Input.value = dataUrl;
        }

        updateSignatureUI();
    }

    // Rotate signature clockwise by deg (default 90)
    window.rotateSignature = function(deg) {
        deg = deg || 90;
        currentRotation = (currentRotation + deg) % 360;
        processSignatureImage();
    };

    // Toggle transparency on/off
    window.toggleSignatureTransparency = function() {
        isTransparentEnabled = !isTransparentEnabled;
        processSignatureImage();
    };

    // Tolerance change slider
    window.onSignatureToleranceChange = function(val) {
        signatureTolerance = parseInt(val, 10) || 75;
        processSignatureImage();
    };

    // Step signature scale by delta (+10 or -10)
    window.stepSignatureScale = function(delta) {
        signatureScale = Math.max(40, Math.min(200, signatureScale + delta));
        applySignatureScaleToPreview();
    };

    // Scale range slider change
    window.onSignatureScaleChange = function(val) {
        signatureScale = Math.max(40, Math.min(200, parseInt(val, 10) || 100));
        applySignatureScaleToPreview();
    };

    // Reset signature edits
    window.resetSignatureEdits = function() {
        currentRotation = 0;
        isTransparentEnabled = false;
        signatureTolerance = 75;
        signatureScale = 100;
        const tolSlider = document.getElementById('signatureTolerance');
        if (tolSlider) tolSlider.value = '75';
        applySignatureScaleToPreview();
        processSignatureImage();
    };

    // Reset signature image to plain
    window.clearSignatureToPlain = function() {
        rawSignatureImage = null;
        currentRotation = 0;
        isTransparentEnabled = false;
        signatureScale = 100;

        const fileInput = document.getElementById('inputSignatureFile');
        if (fileInput) fileInput.value = '';

        const base64Input = document.getElementById('inputSignatureBase64');
        if (base64Input) base64Input.value = '';

        const resetFlag = document.getElementById('inputResetSignature');
        if (resetFlag) resetFlag.value = '1';

        const sigImg = document.getElementById('prevSignatureImg');
        if (sigImg) {
            sigImg.src = '';
            sigImg.style.display = 'none';
        }

        applySignatureScaleToPreview();
        updateSignatureUI();
    };

    // Print Handler (Direct window.print() with @media print CSS)
    window.printQuotationLetter = function() {
        window.print();
    };

    // Download Image Handler (html2canvas)
    window.downloadQuotationImage = function() {
        const paper = document.getElementById('printableLetterPaper');
        if (!paper || typeof html2canvas === 'undefined') {
            alert('Fitur download gambar sedang dimuat, silakan coba lagi.');
            return;
        }

        const numInput = document.getElementById('inputQuotationNumber');
        const rawNum = numInput ? numInput.value : 'Surat_Penawaran';
        const filename = rawNum.replace(/[\/\\:]/g, '_') + '.png';

        html2canvas(paper, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff'
        }).then(function(canvas) {
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }).catch(function(err) {
            console.error('Error generating canvas:', err);
            alert('Gagal membuat gambar surat penawaran.');
        });
    };

    // Expose syncPreview to global scope if needed
    window.syncPreview = syncPreview;

    // Initialize all listeners when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('quotationForm');
        if (form) {
            form.addEventListener('input', syncPreview);
            form.addEventListener('change', syncPreview);
            form.addEventListener('keyup', syncPreview);
        }

        const sigFileInput = document.getElementById('inputSignatureFile');
        if (sigFileInput) {
            sigFileInput.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0];
                if (file) {
                    const resetFlag = document.getElementById('inputResetSignature');
                    if (resetFlag) resetFlag.value = '0';

                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        const img = new Image();
                        img.onload = function() {
                            rawSignatureImage = img;
                            currentRotation = 0;
                            // Otomatis aktifkan transparan latar belakang untuk file foto tanda tangan
                            isTransparentEnabled = true;
                            processSignatureImage();
                        };
                        img.src = evt.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Cek jika sudah ada gambar ttd tersimpan sebelumnya (misal halaman edit)
        const prevSigImg = document.getElementById('prevSignatureImg');
        if (prevSigImg && prevSigImg.getAttribute('src')) {
            const src = prevSigImg.getAttribute('src');
            if (src && src.trim() !== '') {
                const existingImg = new Image();
                existingImg.crossOrigin = 'anonymous';
                existingImg.onload = function() {
                    rawSignatureImage = existingImg;
                    updateSignatureUI();
                };
                existingImg.src = src;
            }
        }

        // Inisialisasi skala tanda tangan jika tersimpan di input form
        const scaleInput = document.getElementById('inputSignatureScale');
        if (scaleInput && scaleInput.value) {
            signatureScale = Math.max(40, Math.min(200, parseInt(scaleInput.value, 10) || 100));
            applySignatureScaleToPreview();
        }

        // Run immediately
        syncPreview();
    });
})();
