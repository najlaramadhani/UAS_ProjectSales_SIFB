/**
 * Sales Dashboard - Vanilla JavaScript
 * Modal management, navigation, and interactive features
 * Last updated: 2025-12-27
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize application
 */
function initializeApp() {
    attachModalListeners();
    attachPageNavigationListeners();
    initializeResponsiveMenu();
}

/**
 * ==================== MODAL FUNCTIONS ====================
 */

/**
 * Open modal with form
 * @param {string} title - Modal title
 * @param {string} formType - Type of form to display
 * @param {string} id - Optional ID for edit mode
 */
function openModal(title, formType, id = null) {
    const modal = document.getElementById('modalForm');
    const modalTitle = document.getElementById('modalTitle');
    const formContent = document.getElementById('formContent');
    
    modalTitle.textContent = title;
    
    // Load appropriate form based on type
    loadFormContent(formContent, formType, id);
    // initialize form-specific controls (e.g., products list)
    if (formType === 'orderForm') {
        setTimeout(() => { initializeProductControls(); }, 20);
    }
    
    // Show modal with animation
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // If detail view, fetch data after modal visible
    if (formType === 'orderDetail' && id) {
        setTimeout(() => { 
            console.log('About to fetch detail for:', id);
            const detailNoEl = document.getElementById('detailNo');
            console.log('detailNo element found:', !!detailNoEl);
            fetchAndRenderOrderDetail(id); 
        }, 300);
    }
}

/**
 * Wrapper function for opening order detail modal
 * @param {string} noPesanan - Order number
 */
function openOrderDetailModal(noPesanan) {
    openModal('Detail Order', 'orderDetail', noPesanan);
}

/**
 * Close modal
 */
function closeModal() {
    const modal = document.getElementById('modalForm');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

/**
 * Load form content dynamically
 * @param {HTMLElement} container - Container for form
 * @param {string} formType - Type of form
 * @param {string} id - Item ID (optional)
 */
function loadFormContent(container, formType, id) {
    let formHTML = '';
    
    switch(formType) {
        case 'orderForm':
            formHTML = `
                <div class="form-group">
                    <label for="noOrder">No Order</label>
                    <input type="text" id="noOrder" name="noOrder" placeholder="Auto generated" readonly>
                </div>
                <div class="form-group">
                    <label for="tanggalOrder">Tanggal Order</label>
                    <input type="date" id="tanggalOrder" name="tanggalOrder" required>
                </div>
                <div class="form-group">
                    <label for="distributor">Distributor</label>
                    <select id="distributor" name="distributor" required>
                        <option value="">Pilih Distributor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Produk</label>
                    <div id="productList">
                        <!-- product rows will be appended here -->
                    </div>
                    <div style="margin-top:0.75rem; display:flex; gap:0.5rem;">
                        <button type="button" class="btn btn-outline" id="addProductBtn">+ Tambah Produk</button>
                        <div style="margin-left:auto; align-self:center; font-weight:700;">Total: <span id="orderTotal">Rp 0</span></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="">Pilih Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Dikirim">Dikirim</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            `;
            break;
            
        case 'distributorForm':
            formHTML = `
                <div class="form-group">
                    <label for="noDistributor">No Distributor</label>
                    <input type="text" id="noDistributor" name="noDistributor" placeholder="Auto generated" readonly>
                </div>
                <div class="form-group">
                    <label for="namaDistributor">Nama Distributor</label>
                    <input type="text" id="namaDistributor" name="namaDistributor" placeholder="Nama lengkap distributor" required>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" placeholder="Jalan, No., Kota, Provinsi" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="kontak">Kontak/No. Telepon</label>
                    <input type="text" id="kontak" name="kontak" placeholder="08xxxxxxxxxx" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="email@distributor.com" required>
                </div>
            `;
            break;
            
        case 'pengirimanForm':
            formHTML = `
                <div class="form-group">
                    <label for="noPengiriman">No Pengiriman</label>
                    <input type="text" id="noPengiriman" name="noPengiriman" placeholder="Auto generated" readonly>
                </div>
                <div class="form-group">
                    <label for="noSuratJalan">No Surat Jalan</label>
                    <input type="text" id="noSuratJalan" name="noSuratJalan" placeholder="Nomor surat jalan" required />
                </div>
                <div class="form-group">
                    <label for="tanggalKirim">Tanggal Kirim</label>
                    <input type="date" id="tanggalKirim" name="tanggalKirim" required>
                </div>
                <div class="form-group">
                    <label for="alamatPengiriman">Alamat Pengiriman</label>
                    <textarea id="alamatPengiriman" name="alamatPengiriman" placeholder="Jalan, No., Kota, Provinsi" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="idDriver">Driver</label>
                    <select id="idDriver" name="idDriver" required>
                        <option value="">Pilih Driver</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="noPesanan">No Order</label>
                    <select id="noPesanan" name="noPesanan" required>
                        <option value="">Pilih Order</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="statusPengiriman">Status Pengiriman</label>
                    <select id="statusPengiriman" name="statusPengiriman" required>
                        <option value="">Pilih Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Dikirim">Dikirim</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            `;
            break;
        
        case 'orderDetail':
            // Read-only order detail view (sample/placeholder)
            formHTML = `
                <div class="form-group">
                    <label>No Order</label>
                    <div><strong id="detailNo">-</strong></div>
                </div>
                <div class="form-group">
                    <label>Tanggal Order</label>
                    <div id="detailTanggal">-</div>
                </div>
                <div class="form-group">
                    <label>Distributor</label>
                    <div id="detailDistributor">-</div>
                </div>
                <div class="form-group">
                    <label>Produk</label>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Total</th></tr>
                            </thead>
                            <tbody id="detailProducts">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-group" style="text-align:right; font-weight:700;">
                    <label>Total</label>
                    <div id="detailTotal">Rp 0</div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <div id="detailStatus">-</div>
                </div>
            `;
            break;
        
        case 'pengirimanDetail':
            formHTML = `
                <div class="form-group">
                    <label>No Pengiriman</label>
                    <div><strong>${id || 'PGR-XXXX-0001'}</strong></div>
                </div>
                <div class="form-group">
                    <label>Tanggal Kirim</label>
                    <div>24 Des 2025</div>
                </div>
                <div class="form-group">
                    <label>Alamat Pengiriman</label>
                    <div>Jl. Ahmad Yani No. 123, Medan</div>
                </div>
                <div class="form-group">
                    <label>Nama Driver</label>
                    <div>Andi Supir</div>
                </div>
                <div class="form-group">
                    <label>Kontak Driver</label>
                    <div>081234567890</div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <div><span class="badge badge-info">Dikirim</span></div>
                </div>
            `;
            break;
            
        case 'detailPesananForm':
            formHTML = `
                <div class="form-group">
                    <label for="produk">Produk</label>
                    <select id="produk" name="produk" required>
                        <option value="">Pilih Produk</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jumlah">Jumlah</label>
                    <input type="number" id="jumlah" name="jumlah" placeholder="Jumlah produk" min="1" required>
                </div>
            `;
            break;
            
        default:
            formHTML = '<p>Form tidak ditemukan</p>';
    }
    
    container.innerHTML = formHTML;
}

/**
 * Initialize product controls inside order form (add/remove rows, totals)
 */
function initializeProductControls() {
    const addBtn = document.getElementById('addProductBtn');
    const list = document.getElementById('productList');
    if (!list) return;
    list.innerHTML = '';
    if (addBtn) {
        addBtn.onclick = function() { addProductRow(); };
    }
    // start with one empty row
    addProductRow();
    updateOrderTotal();
}

function addProductRow(productId = '', qty = 1) {
    const list = document.getElementById('productList');
    if (!list) return;
    const row = document.createElement('div');
    row.className = 'product-row';
    row.style.cssText = 'display:flex; gap:0.5rem; align-items:center; margin-bottom:0.5rem;';

    let options = '<option value="">Pilih Produk</option>';
    if (window.productsData && window.productsData.length) {
        window.productsData.forEach(p => {
            options += `<option value="${p.idProduk}" data-harga="${p.harga}">${p.namaProduk} (Rp ${parseFloat(p.harga).toLocaleString('id-ID')})</option>`;
        });
    }

    row.innerHTML = `
        <select class="prod-select" style="flex:1;">${options}</select>
        <input type="number" class="prod-qty" min="1" value="${qty}" style="width:80px;" />
        <div class="prod-total" style="width:120px; text-align:right;">Rp 0</div>
        <button type="button" class="btn btn-outline btn-remove" style="margin-left:6px;">×</button>
    `;

    list.appendChild(row);

    const sel = row.querySelector('.prod-select');
    const qtyInput = row.querySelector('.prod-qty');
    const totalDiv = row.querySelector('.prod-total');

    sel.addEventListener('change', () => { updateRowTotal(row); updateOrderTotal(); });
    qtyInput.addEventListener('input', () => { updateRowTotal(row); updateOrderTotal(); });
    row.querySelector('.btn-remove').addEventListener('click', () => { row.remove(); updateOrderTotal(); });

    if (productId) sel.value = productId;
    updateRowTotal(row);
}

function updateRowTotal(row) {
    const sel = row.querySelector('.prod-select');
    const qty = parseInt(row.querySelector('.prod-qty').value || 0, 10);
    const harga = sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].dataset ? parseFloat(sel.options[sel.selectedIndex].dataset.harga || 0) : 0;
    const total = harga * qty;
    row.querySelector('.prod-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

function updateOrderTotal() {
    const rows = document.querySelectorAll('.product-row');
    let sum = 0;
    rows.forEach(r => {
        const sel = r.querySelector('.prod-select');
        const qty = parseInt(r.querySelector('.prod-qty').value || 0, 10);
        const harga = sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].dataset ? parseFloat(sel.options[sel.selectedIndex].dataset.harga || 0) : 0;
        sum += harga * qty;
    });
    const el = document.getElementById('orderTotal');
    if (el) el.textContent = 'Rp ' + sum.toLocaleString('id-ID');
}

/**
 * Populate product rows from server-provided items for an order
 * @param {string} noPesanan
 */
function populateOrderItems(noPesanan) {
    if (!noPesanan) return;
    const url = `index.php?page=detail_pesanan&fetch=json&pesanan=${encodeURIComponent(noPesanan)}`;
    fetch(url, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (!Array.isArray(data)) return;
            const productList = document.getElementById('productList');
            if (!productList) return;
            productList.innerHTML = '';
            data.forEach(item => {
                // item: idDetail, idProduk, jumlah, hargaSatuan, totalHarga
                addProductRow(item.idProduk, item.jumlah);
            });
            updateOrderTotal();
        })
        .catch(err => console.error('Failed to fetch order items:', err));
}

/**
 * Fetch order detail items and render read-only detail table in modal
 * @param {string} noPesanan
 */
function fetchAndRenderOrderDetail(noPesanan) {
    if (!noPesanan) {
        console.error('noPesanan is empty');
        return;
    }
    
    const url = `index.php?page=detail_pesanan&fetch=json&pesanan=${encodeURIComponent(noPesanan)}`;
    console.log('fetchAndRenderOrderDetail called with:', noPesanan, 'URL:', url);
    
    // Verify elements exist before fetch
    const detailNo = document.getElementById('detailNo');
    const detailTanggal = document.getElementById('detailTanggal');
    const detailDistributor = document.getElementById('detailDistributor');
    const detailStatus = document.getElementById('detailStatus');
    const tbody = document.getElementById('detailProducts');
    const detailTotal = document.getElementById('detailTotal');
    
    console.log('Elements check:', {
        detailNo: !!detailNo,
        detailTanggal: !!detailTanggal,
        detailDistributor: !!detailDistributor,
        detailStatus: !!detailStatus,
        tbody: !!tbody,
        detailTotal: !!detailTotal
    });
    
    fetch(url, { credentials: 'same-origin' })
        .then(r => {
            console.log('Fetch response status:', r.status);
            if (!r.ok) {
                throw new Error(`HTTP ${r.status}: ${r.statusText}`);
            }
            return r.text(); // Get as text first to debug
        })
        .then(text => {
            console.log('Raw response text:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed JSON:', data);
                
                // Handle error response
                if (data.error) {
                    console.error('Server error:', data.error);
                    if (data.debug) console.log('Debug info:', data.debug);
                    return;
                }
                
                // Extract header and items from response
                const header = data.header || {};
                const items = Array.isArray(data.items) ? data.items : [];
                
                console.log('Processing header:', header);
                console.log('Processing items:', items);
                
                // Update header fields
                if (detailNo) {
                    detailNo.textContent = header.noPesanan || '-';
                    console.log('Updated detailNo to:', header.noPesanan);
                } else {
                    console.warn('detailNo element not found');
                }
                
                if (detailTanggal) {
                    const tanggal = header.tanggalOrder ? new Date(header.tanggalOrder).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-';
                    detailTanggal.textContent = tanggal;
                    console.log('Updated detailTanggal to:', tanggal);
                } else {
                    console.warn('detailTanggal element not found');
                }
                
                if (detailDistributor) {
                    detailDistributor.textContent = header.namaDistributor || '-';
                    console.log('Updated detailDistributor to:', header.namaDistributor);
                } else {
                    console.warn('detailDistributor element not found');
                }
                
                if (detailStatus) {
                    const statusClass = header.status === 'Selesai' ? 'badge-success' : (header.status === 'Dikirim' ? 'badge-warning' : 'badge-info');
                    detailStatus.innerHTML = `<span class="badge ${statusClass}">${header.status || '-'}</span>`;
                    console.log('Updated detailStatus to:', header.status);
                } else {
                    console.warn('detailStatus element not found');
                }
                
                // Update detail items table
                if (tbody) {
                    tbody.innerHTML = '';
                    console.log('Cleared tbody, now populating with', items.length, 'items');
                } else {
                    console.warn('tbody#detailProducts not found');
                    return;
                }

                let sum = 0;
                items.forEach((it, idx) => {
                    const tr = document.createElement('tr');
                    // Prefer server-provided name, then client-side lookup, then legacy parse
                    let nama = it.namaProduk || '';
                    if (!nama && window.productsData && Array.isArray(window.productsData)) {
                        const p = window.productsData.find(x => String(x.idProduk) === String(it.idProduk));
                        if (p) nama = p.namaProduk;
                    }
                    if (!nama) {
                        if (it.idProduk && it.idProduk.indexOf('|') !== -1) nama = it.idProduk.split('|')[0];
                        else nama = it.idProduk || '-';
                    }

                    const harga = parseFloat(it.hargaSatuan) || 0;
                    const jumlah = parseInt(it.jumlah) || 0;
                    const total = parseFloat(it.totalHarga) || (harga * jumlah);
                    sum += total;

                    tr.innerHTML = `<td>${nama}</td><td>${formatCurrency(harga)}</td><td>${jumlah}</td><td>${formatCurrency(total)}</td>`;
                    tbody.appendChild(tr);
                    console.log(`Row ${idx + 1}: ${nama} x ${jumlah} = Rp ${formatCurrency(total)}`);
                });

                if (detailTotal) {
                    detailTotal.textContent = formatCurrency(sum);
                    console.log('Updated detailTotal to:', formatCurrency(sum));
                } else {
                    console.warn('detailTotal element not found');
                }
                
                console.log('Detail view updated with', items.length, 'items, total:', sum);
            } catch (parseErr) {
                console.error('Failed to parse JSON:', parseErr);
                console.error('Response was:', text);
            }
        })
        .catch(err => {
            console.error('Failed to load order detail:', err);
            console.error('Error stack:', err.stack);
        });
}

/**
 * Attach modal listeners
 */
function attachModalListeners() {
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('modalForm');
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
    
    // Form submission
    const form = document.getElementById('dynamicForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            handleFormSubmit();
        });
    }
}

/**
 * Handle form submission
 */
function handleFormSubmit() {
    const formData = new FormData(document.getElementById('dynamicForm'));
    const data = Object.fromEntries(formData);
    
    // Simple feedback (in real app, would send to server)
    console.log('Form Data:', data);
    showSuccessMessage('Data berhasil disimpan!');
    closeModal();
}

/**
 * Show success message
 * @param {string} message - Success message
 */
function showSuccessMessage(message) {
    const alert = document.createElement('div');
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #27ae60, #229954);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 9999;
        animation: slideInRight 0.3s ease;
    `;
    alert.textContent = message;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

/**
 * ==================== NAVIGATION & SIDEBAR ====================
 */

/**
 * Attach page navigation listeners
 */
function attachPageNavigationListeners() {
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Remove active class from all items
            navItems.forEach(nav => nav.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
        });
    });
}

/**
 * Initialize responsive menu for mobile
 */
function initializeResponsiveMenu() {
    // Check if we need mobile menu (can be extended with hamburger menu)
    const sidebar = document.querySelector('.sidebar');
    
    if (window.innerWidth <= 768) {
        // Mobile-specific initialization
        optimizeForMobile();
    }
    
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            optimizeForMobile();
        } else {
            optimizeForDesktop();
        }
    });
}

/**
 * Mobile optimization
 */
function optimizeForMobile() {
    const aside = document.querySelector('.aside-panel');
    if (aside) {
        aside.style.display = 'block'; // Ensure visibility
    }
}

/**
 * Desktop optimization
 */
function optimizeForDesktop() {
    const aside = document.querySelector('.aside-panel');
    if (aside) {
        aside.style.display = 'block';
    }
}

/**
 * ==================== TABLE INTERACTIONS ====================
 */

/**
 * Delete item with confirmation
 * @param {string} itemName - Name of item to delete
 * @param {Function} callback - Callback after deletion
 */
function deleteItem(itemName, callback) {
    if (confirm(`Apakah Anda yakin ingin menghapus ${itemName}?`)) {
        if (callback) callback();
        showSuccessMessage(`${itemName} berhasil dihapus!`);
        return true;
    }
    return false;
}

/**
 * Filter table rows
 * @param {string} inputId - ID of input element
 * @param {number} columnIndex - Index of column to filter
 */
function filterTable(inputId, columnIndex) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells[columnIndex]) {
                const text = cells[columnIndex].textContent.toUpperCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            }
        });
    });
}

/**
 * Sort table by column
 * @param {number} columnIndex - Index of column to sort
 */
function sortTableByColumn(columnIndex) {
    const table = document.querySelector('.table');
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    
    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].textContent.trim();
        const bText = b.cells[columnIndex].textContent.trim();
        
        // Try numeric sort first
        const aNum = parseFloat(aText);
        const bNum = parseFloat(bText);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return aNum - bNum;
        }
        
        // Fall back to string sort
        return aText.localeCompare(bText);
    });
    
    // Re-append sorted rows
    const tbody = table.querySelector('tbody');
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * ==================== UTILITY FUNCTIONS ====================
 */

/**
 * Format currency
 * @param {number} value - Value to format
 * @returns {string} Formatted currency
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(value);
}

/**
 * Format date
 * @param {string} dateString - Date string
 * @returns {string} Formatted date
 */
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

/**
 * Get current date in YYYY-MM-DD format
 * @returns {string} Current date
 */
function getTodayDate() {
    const today = new Date();
    return today.toISOString().split('T')[0];
}

/**
 * ==================== ORDER PRODUCTS HELPERS ====================
 */

function initializeProductControls() {
    const productList = document.getElementById('productList');
    const addBtn = document.getElementById('addProductBtn');
    if (!productList) return;

    // Clear and add initial row
    productList.innerHTML = '';
    addProductRow();

    if (addBtn) {
        addBtn.addEventListener('click', function() {
            addProductRow();
        });
    }
    updateOrderTotal();
}

function addProductRow(data = {}) {
    const productList = document.getElementById('productList');
    if (!productList) return;

    const row = document.createElement('div');
    row.className = 'product-row';
    row.style.cssText = 'display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem;';
    row.innerHTML = `
        <select name="produk[]" class="form-control prod-select" style="flex:2;padding:0.6rem;border:1px solid var(--border-color);border-radius:var(--radius);">
            <option value="">Pilih Produk</option>
        </select>
        <input type="number" name="harga[]" class="prod-harga" placeholder="Harga" value="" style="flex:1;padding:0.6rem;border:1px solid var(--border-color);border-radius:var(--radius);" />
        <input type="number" name="jumlah[]" class="prod-qty" placeholder="Jumlah" value="1" style="width:80px;padding:0.6rem;border:1px solid var(--border-color);border-radius:var(--radius);" />
        <div style="min-width:100px;text-align:right;"> <strong class="row-total">Rp 0</strong> </div>
        <button type="button" class="btn btn-outline btn-remove">-</button>
    `;

    productList.appendChild(row);

    // Wire events
    const select = row.querySelector('select');
    const harga = row.querySelector('input[name="harga[]"]');
    const jumlah = row.querySelector('input[name="jumlah[]"]');
    const btnRemove = row.querySelector('.btn-remove');

    // populate options from window.productsData if available
    if (window.productsData && window.productsData.length) {
        select.innerHTML = '<option value="">Pilih Produk</option>';
        window.productsData.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.idProduk;
            opt.textContent = p.namaProduk + ' - Rp ' + parseFloat(p.harga).toLocaleString('id-ID');
            opt.dataset.harga = p.harga;
            select.appendChild(opt);
        });
    }

    // If data provided, prefill selection and qty
    if (data && data.idProduk) {
        select.value = data.idProduk;
        // if the option did not exist, create temporary option (handles legacy stored values like "Name|price")
        if (select.value !== data.idProduk) {
            let text = data.idProduk;
            let hval = data.hargaSatuan || 0;
            if (data.idProduk.indexOf('|') !== -1) {
                const parts = data.idProduk.split('|');
                text = parts[0].trim();
                if (parts[1]) hval = parseFloat(parts[1]) || hval;
            } else {
                // try to find name from productsData by id (if id stored as name)
                if (window.productsData) {
                    const found = window.productsData.find(p => p.idProduk == data.idProduk || p.namaProduk == data.idProduk);
                    if (found) {
                        text = found.namaProduk;
                        hval = found.harga;
                    }
                }
            }
            const opt = document.createElement('option');
            opt.value = data.idProduk;
            opt.textContent = text + (hval ? ' - Rp ' + parseFloat(hval).toLocaleString('id-ID') : '');
            opt.dataset.harga = hval;
            select.appendChild(opt);
            select.value = data.idProduk;
            harga.value = hval;
        } else {
            const chosen = select.options[select.selectedIndex];
            const h = chosen && chosen.dataset ? parseFloat(chosen.dataset.harga || 0) : (data.hargaSatuan || 0);
            harga.value = h;
        }
    }
    if (data && data.jumlah) {
        jumlah.value = data.jumlah;
    }

    select.addEventListener('change', function() {
        const chosen = this.options[this.selectedIndex];
        const h = chosen && chosen.dataset ? parseFloat(chosen.dataset.harga || 0) : 0;
        harga.value = h;
        updateRowTotal(row);
    });

    harga.addEventListener('input', function() { updateRowTotal(row); });
    jumlah.addEventListener('input', function() { updateRowTotal(row); });

    btnRemove.addEventListener('click', function() {
        row.remove();
        updateOrderTotal();
    });

    updateRowTotal(row);
}

function updateRowTotal(row) {
    if (!row) return;
    const harga = parseFloat(row.querySelector('input[name="harga[]"]').value) || 0;
    const jumlah = parseFloat(row.querySelector('input[name="jumlah[]"]').value) || 0;
    const total = harga * jumlah;
    const el = row.querySelector('.row-total');
    if (el) el.textContent = formatCurrency(total);
    updateOrderTotal();
}

function updateOrderTotal() {
    const productRows = document.querySelectorAll('#productList .product-row');
    let sum = 0;
    productRows.forEach(row => {
        const harga = parseFloat(row.querySelector('input[name="harga[]"]').value) || 0;
        const jumlah = parseFloat(row.querySelector('input[name="jumlah[]"]').value) || 0;
        sum += harga * jumlah;
    });
    const totalEl = document.getElementById('orderTotal');
    if (totalEl) totalEl.textContent = formatCurrency(sum);
}

/**
 * Generate unique ID
 * @returns {string} Unique ID
 */
function generateUniqueId() {
    return 'ID-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
}

/**
 * Debounce function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * ==================== EXPORT & PRINT ====================
 */

/**
 * Export table to CSV
 * @param {string} tableId - ID of table to export
 * @param {string} filename - Name of file
 */
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId) || document.querySelector('.table');
    let csv = [];
    
    // Get headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push('"' + th.textContent.trim() + '"');
    });
    csv.push(headers.join(','));
    
    // Get rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push('"' + td.textContent.trim() + '"');
        });
        csv.push(row.join(','));
    });
    
    // Create download link
    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const link = document.createElement('a');
    link.setAttribute('href', encodeURI(csvContent));
    link.setAttribute('download', filename + '.csv');
    link.click();
}

/**
 * Print current page
 */
function printPage() {
    window.print();
}

/**
 * ==================== ANIMATIONS & TRANSITIONS ====================
 */

/**
 * Add fade-in animation to elements
 */
function animateFadeIn(elements) {
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.animation = `fadeIn 0.5s ease forwards ${index * 0.1}s`;
    });
}

/**
 * Smooth scroll to element
 * @param {string} elementId - ID of element to scroll to
 */
function smoothScrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}

/**
 * ==================== LOCAL STORAGE HELPERS ====================
 */

/**
 * Save to localStorage
 * @param {string} key - Storage key
 * @param {any} value - Value to store
 */
function saveToLocalStorage(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
}

/**
 * Get from localStorage
 * @param {string} key - Storage key
 * @returns {any} Stored value
 */
function getFromLocalStorage(key) {
    const item = localStorage.getItem(key);
    return item ? JSON.parse(item) : null;
}

/**
 * Remove from localStorage
 * @param {string} key - Storage key
 */
function removeFromLocalStorage(key) {
    localStorage.removeItem(key);
}

/**
 * ==================== FORM HELPERS ====================
 */

/**
 * Validate email
 * @param {string} email - Email to validate
 * @returns {boolean} Is valid email
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validate phone number
 * @param {string} phone - Phone number to validate
 * @returns {boolean} Is valid phone
 */
function isValidPhone(phone) {
    const regex = /^(\+62|0)[0-9]{9,12}$/;
    return regex.test(phone);
}

/**
 * Clear form
 * @param {string} formId - ID of form to clear
 */
function clearForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
    }
}
