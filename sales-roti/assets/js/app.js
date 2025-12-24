/**
 * Sales Dashboard - Vanilla JavaScript
 * Modal management, navigation, and interactive features
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
                        <option value="1">PT Abadi Jaya</option>
                        <option value="2">CV Maju Bersama</option>
                        <option value="3">PT Bakery Nusantara</option>
                        <option value="4">PT Toko Segar</option>
                        <option value="5">CV Berkah Makmur</option>
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
                        <option value="pending">Pending</option>
                        <option value="dikirim">Dikirim</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
            `;
            break;
            
        case 'distributorForm':
            formHTML = `
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
                    <label for="tanggalKirim">Tanggal Kirim</label>
                    <input type="date" id="tanggalKirim" name="tanggalKirim" required>
                </div>
                <div class="form-group">
                    <label for="alamatPengiriman">Alamat Pengiriman</label>
                    <textarea id="alamatPengiriman" name="alamatPengiriman" placeholder="Jalan, No., Kota, Provinsi" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="namaDriver">Nama Driver</label>
                    <input type="text" id="namaDriver" name="namaDriver" placeholder="Nama driver" required />
                </div>
                <div class="form-group">
                    <label for="kontakDriver">Kontak Driver</label>
                    <input type="text" id="kontakDriver" name="kontakDriver" placeholder="08xxxxxxxxxx" required />
                </div>
                <div class="form-group">
                    <label for="statusPengiriman">Status Pengiriman</label>
                    <select id="statusPengiriman" name="statusPengiriman" required>
                        <option value="">Pilih Status</option>
                        <option value="pending">Pending</option>
                        <option value="dikirim">Dikirim</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="noOrder">No Order Terkait</label>
                    <select id="noOrder" name="noOrder" required>
                        <option value="">Pilih Order</option>
                        <option value="ORD-2025-0156">ORD-2025-0156 - PT Abadi Jaya</option>
                        <option value="ORD-2025-0155">ORD-2025-0155 - CV Maju Bersama</option>
                        <option value="ORD-2025-0154">ORD-2025-0154 - PT Bakery Nusantara</option>
                    </select>
                </div>
            `;
            break;
        
        case 'orderDetail':
            // Read-only order detail view (sample/placeholder)
            formHTML = `
                <div class="form-group">
                    <label>No Order</label>
                    <div><strong id="detailNo">${id || 'ORD-XXXX-0001'}</strong></div>
                </div>
                <div class="form-group">
                    <label>Tanggal Order</label>
                    <div id="detailTanggal">24 Des 2025</div>
                </div>
                <div class="form-group">
                    <label>Distributor</label>
                    <div id="detailDistributor">PT Abadi Jaya</div>
                </div>
                <div class="form-group">
                    <label>Produk</label>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Total</th></tr>
                            </thead>
                            <tbody id="detailProducts">
                                <tr><td>Roti Tawar</td><td>Rp 12.000</td><td>10</td><td>Rp 120.000</td></tr>
                                <tr><td>Roti Coklat</td><td>Rp 18.000</td><td>5</td><td>Rp 90.000</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-group" style="text-align:right; font-weight:700;">
                    <label>Total</label>
                    <div id="detailTotal">Rp 210.000</div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <div id="detailStatus"><span class="badge badge-success">Selesai</span></div>
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
            
        default:
            formHTML = '<p>Form tidak ditemukan</p>';
    }
    
    container.innerHTML = formHTML;
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
        <select name="produk[]" class="form-control" style="flex:2;padding:0.6rem;border:1px solid var(--border-color);border-radius:var(--radius);">
            <option value="">Pilih Produk</option>
            <option value="Roti Tawar|12000">Roti Tawar - Rp 12.000</option>
            <option value="Roti Manis|15000">Roti Manis - Rp 15.000</option>
            <option value="Roti Coklat|18000">Roti Coklat - Rp 18.000</option>
        </select>
        <input type="number" name="harga[]" placeholder="Harga" value="" style="flex:1;padding:0.6rem;border:1px solid var(--border-color);border-radius:var(--radius);" />
        <input type="number" name="jumlah[]" placeholder="Jumlah" value="1" style="width:80px;padding:0.6rem;border:1px solid var(--border-color);border-radius:var(--radius);" />
        <div style="min-width:100px;text-align:right;"> <strong class="row-total">Rp 0</strong> </div>
        <button type="button" class="btn btn-outline btn-remove">-</button>
    `;

    productList.appendChild(row);

    // Wire events
    const select = row.querySelector('select');
    const harga = row.querySelector('input[name="harga[]"]');
    const jumlah = row.querySelector('input[name="jumlah[]"]');
    const btnRemove = row.querySelector('.btn-remove');

    select.addEventListener('change', function() {
        const parts = this.value.split('|');
        if (parts.length === 2) {
            harga.value = parts[1];
        }
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
