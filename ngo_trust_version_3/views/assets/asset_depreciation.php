<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<div id="wrapper">
    <div class="content">
        <div id="app">

            <!-- PAGE HEADER -->
            <div class="ad-page-header">
                <div>
                    <h2 class="ad-page-title">Asset Depreciation TEST</h2>
                    <p class="ad-page-subtitle">Track and manage asset depreciation by category</p>
                </div>
            </div>

            <!-- ALERT -->
            <transition name="fade">
                <div v-if="alert.message" :class="'ad-alert ad-alert-' + alert.type">
                    <span class="ad-alert-icon">
                        <svg v-if="alert.type === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </span>
                    {{ alert.message }}
                </div>
            </transition>

            <!-- FORM CARD -->
            <div class="ad-card">
                <div class="ad-card-header">
                    <span class="ad-card-label">
                        {{ editMode ? '✏️ Edit Record' : '➕ Add New Record' }}
                    </span>
                </div>
                <div class="ad-card-body">
                    <div class="ad-form-grid">

                        <div class="ad-field">
                            <label class="ad-label">Category</label>
                            <div class="ad-select-wrap">
                                <select v-model="form.category" class="ad-select">
                                    <option value="">— Select category —</option>
                                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <span class="ad-select-arrow">▾</span>
                            </div>
                        </div>

                        <div class="ad-field">
                            <label class="ad-label">Month &amp; Year</label>
                            <input type="month" v-model="form.month_year" class="ad-input">
                        </div>

                        <div class="ad-field">
                            <label class="ad-label">Amount (₹)</label>
                            <div class="ad-input-prefix-wrap">
                                <span class="ad-input-prefix">₹</span>
                                <input type="number" v-model="form.amount" class="ad-input ad-input-prefixed" placeholder="0.00">
                            </div>
                        </div>

                        <div class="ad-field">
                            <label class="ad-label">Depreciation Rate (%)</label>
                            <div class="ad-input-prefix-wrap">
                                <input type="number" v-model="form.rate" class="ad-input ad-input-suffixed" placeholder="0">
                                <span class="ad-input-suffix">%</span>
                            </div>
                        </div>

                    </div>

                    <div class="ad-form-actions">
                        <button v-if="editMode" class="ad-btn ad-btn-ghost" @click="resetForm">
                            Cancel
                        </button>
                        <button class="ad-btn ad-btn-primary" @click="saveData" :disabled="saving">
                            <span v-if="saving" class="ad-spinner"></span>
                            {{ saving ? 'Saving…' : (editMode ? 'Update Record' : 'Save Record') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="ad-card">
                <div class="ad-card-header">
                    <div class="ad-card-header-left">
                        <span class="ad-card-label">📋 Depreciation Records</span>
                        <span class="ad-badge">{{ list.length }} shown</span>
                    </div>
                    <div class="ad-tbl-search">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" v-model="search" placeholder="Search records…">
                    </div>
                </div>

                <div class="ad-table-wrap">
                    <table class="ad-table">
                        <thead>
                            <tr>
                                <th class="ad-th-num">#</th>
                                <th>Category</th>
                                <th>Month</th>
                                <th>Amount</th>
                                <th>Rate</th>
                                <th class="ad-th-action">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredList.length === 0">
                                <td colspan="6" class="ad-empty">
                                    <div class="ad-empty-inner">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                                        <p>No records found. Add your first entry above.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="(row, index) in filteredList" :key="row.id" class="ad-row">
                                <td class="ad-td-num">{{ ((page - 1) * limit) + index + 1 }}</td>
                                <td>
                                    <div class="ad-cell-cat">
                                        <div class="ad-cat-avatar">{{ row.category_name ? row.category_name.charAt(0).toUpperCase() : '?' }}</div>
                                        <span class="ad-category-chip">{{ row.category_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="ad-month-cell">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        {{ row.month_year }}
                                    </div>
                                </td>
                                <td class="ad-amount">₹{{ Number(row.amount).toLocaleString('en-IN') }}</td>
                                <td>
                                    <span class="ad-rate-pill">{{ row.rate }}%</span>
                                </td>
                                <td class="ad-td-action">
                                    <div class="ad-action-group">
                                        <button class="ad-btn-icon ad-btn-edit" @click="editRow(row)" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <button class="ad-btn-icon ad-btn-delete" @click="deleteRow(row.id)" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="ad-pagination">
                    <span class="ad-page-entries">
                        Showing <strong>{{ filteredList.length ? ((page-1)*limit)+1 : 0 }}</strong> – <strong>{{ ((page-1)*limit) + filteredList.length }}</strong> of <strong>{{ list.length }}</strong> entries
                    </span>
                    <div class="ad-page-controls">
                        <button class="ad-page-btn" @click="changePage(page - 1)" :disabled="page <= 1" title="Previous">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <span class="ad-page-num active">{{ page }}</span>
                        <button class="ad-page-btn" @click="changePage(page + 1)" :disabled="list.length < limit" title="Next">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- DELETE CONFIRM MODAL -->
            <transition name="modal-fade">
                <div v-if="showDeleteModal" class="ad-modal-overlay" @click.self="showDeleteModal = false">
                    <div class="ad-modal">
                        <div class="ad-modal-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </div>
                        <h4 class="ad-modal-title">Delete Record?</h4>
                        <p class="ad-modal-msg">This action cannot be undone. The depreciation record will be permanently removed.</p>
                        <div class="ad-modal-actions">
                            <button class="ad-btn ad-btn-ghost" @click="showDeleteModal = false">Cancel</button>
                            <button class="ad-btn ad-btn-danger" @click="confirmDelete" :disabled="saving">
                                <span v-if="saving" class="ad-spinner"></span>
                                {{ saving ? 'Deleting2026' : 'Yes, Delete' }}
                            </button>
                        </div>
                    </div>
                </div>
            </transition>

        </div><!-- #app -->
    </div>
</div>

<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>

<script>
const csrfName = "<?= $csrf_name ?>";
let csrfHash   = "<?= $csrf_hash ?>";
const BASE_URL = "<?php echo admin_url('ngo_trust/asset_depreciation'); ?>";

new Vue({
    el: '#app',
    data: {
        form: { id: null, category: '', month_year: '', amount: '', rate: '' },
        categories: [],
        list: [],
        search: '',
        showDeleteModal: false,
        deleteTargetId: null,
        page: 1, limit: 5, saving: false, editMode: false,
        alert: { message: '', type: 'success' }
    },
    computed: {
        filteredList() {
            if (!this.search.trim()) return this.list;
            const q = this.search.toLowerCase();
            return this.list.filter(r =>
                (r.category_name || '').toLowerCase().includes(q) ||
                (r.month_year || '').toLowerCase().includes(q) ||
                String(r.amount).includes(q) ||
                String(r.rate).includes(q)
            );
        }
    },
    mounted() { this.loadCategories(); this.loadList(); },
    methods: {
        showAlert(message, type = 'success') {
            this.alert = { message, type };
            setTimeout(() => { this.alert.message = ''; }, 3500);
        },
        buildFormData(obj) {
            const fd = new FormData();
            fd.append(csrfName, csrfHash);
            for (const key in obj) if (obj[key] !== null && obj[key] !== undefined) fd.append(key, obj[key]);
            return fd;
        },
        refreshCsrf(headers) {
            const newHash = headers.get('X-CSRF-Hash');
            if (newHash) csrfHash = newHash;
        },
        loadCategories() {
            fetch(`${BASE_URL}/get_categories`).then(r => r.json()).then(r => { this.categories = r.data || []; });
        },
        loadList() {
            fetch(`${BASE_URL}/list?page=${this.page}&limit=${this.limit}`).then(r => r.json()).then(r => { this.list = r.data || []; });
        },
        changePage(p) {
            if (p < 1) return;
            if (p > this.page && this.list.length < this.limit) return;
            this.page = p; this.loadList();
        },
        saveData() {
            if (!this.form.category)   return this.showAlert('Please select a category.', 'danger');
            if (!this.form.month_year) return this.showAlert('Please select a month-year.', 'danger');
            if (!this.form.amount)     return this.showAlert('Please enter an amount.', 'danger');
            if (!this.form.rate)       return this.showAlert('Please enter a rate.', 'danger');
            this.saving = true;
            fetch(`${BASE_URL}/save`, { method: 'POST', body: this.buildFormData(this.form) })
                .then(r => { this.refreshCsrf(r.headers); return r.json(); })
                .then(r => {
                    if (r.status) { this.showAlert(r.message); this.resetForm(); this.loadList(); }
                    else this.showAlert(r.message || 'Something went wrong.', 'danger');
                })
                .catch(() => this.showAlert('Request failed.', 'danger'))
                .finally(() => { this.saving = false; });
        },
        editRow(row) {
            this.form = { id: row.id, category: row.category, month_year: row.month_year, amount: row.amount, rate: row.rate };
            this.editMode = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        deleteRow(id) {
            this.deleteTargetId = id;
            this.showDeleteModal = true;
        },
        confirmDelete() {
            if (!this.deleteTargetId) return;
            this.saving = true;
            fetch(`${BASE_URL}/delete/${this.deleteTargetId}`, { method: 'POST', body: this.buildFormData({}) })
                .then(r => { this.refreshCsrf(r.headers); return r.json(); })
                .then(r => {
                    if (r.status) { this.showAlert(r.message); if (this.list.length === 1 && this.page > 1) this.page--; this.loadList(); }
                    else this.showAlert(r.message || 'Delete failed.', 'danger');
                })
                .finally(() => {
                    this.saving = false;
                    this.showDeleteModal = false;
                    this.deleteTargetId = null;
                });
        },
        resetForm() {
            this.form = { id: null, category: '', month_year: '', amount: '', rate: '' };
            this.editMode = false;
        }
    }
});
</script>

<style>
/* ── Token system ─────────────────────────────── */
:root {
    --ad-primary:     #4f6ef7;
    --ad-primary-dk:  #3a57e0;
    --ad-danger:      #e05252;
    --ad-success:     #27ae7a;
    --ad-bg:          #f4f6fb;
    --ad-surface:     #ffffff;
    --ad-border:      #e4e8f0;
    --ad-text:        #1e2533;
    --ad-muted:       #7a8499;
    --ad-chip-bg:     #eef1ff;
    --ad-chip-text:   #4f6ef7;
    --ad-radius:      12px;
    --ad-radius-sm:   8px;
    --ad-shadow:      0 2px 12px rgba(79,110,247,.08);
}

/* ── Layout ───────────────────────────────────── */
#app { margin: 0 auto; padding: 28px 16px; font-family: 'Poppins', sans-serif; color: var(--ad-text); min-height: 100vh; }

/* ── Page header ──────────────────────────────── */
.ad-page-header { display: flex; align-items: center; gap: 14px; margin-bottom: 24px; }
.ad-page-title { font-size: 1.45rem; font-weight: 700; margin: 0 0 2px; }
.ad-page-subtitle { font-size: 0.82rem; color: var(--ad-muted); margin: 0; }

/* ── Alert ────────────────────────────────────── */
.ad-alert { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: var(--ad-radius-sm); margin-bottom: 16px; font-size: 0.875rem; font-weight: 500; }
.ad-alert-success { background: #eafaf3; color: #1b7a56; border: 1px solid #b6e8d3; }
.ad-alert-danger  { background: #fdf1f1; color: #b33030; border: 1px solid #f2c0c0; }
.ad-alert-icon svg { width: 18px; height: 18px; flex-shrink: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity .3s; }
.fade-enter, .fade-leave-to { opacity: 0; }

/* ── Card ─────────────────────────────────────── */
.ad-card { background: var(--ad-surface); border-radius: var(--ad-radius); box-shadow: var(--ad-shadow); border: 1px solid var(--ad-border); margin-bottom: 20px; overflow: hidden; }
.ad-card-label { font-size: 0.88rem; font-weight: 600; color: var(--ad-text); }
.ad-badge { font-size: 0.75rem; background: var(--ad-chip-bg); color: var(--ad-chip-text); padding: 3px 10px; border-radius: 20px; font-weight: 600; }
.ad-card-body { padding: 22px; }

/* ── Form grid ────────────────────────────────── */
.ad-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
.ad-field { display: flex; flex-direction: column; gap: 6px; }
.ad-label { font-size: 0.8rem; font-weight: 600; color: var(--ad-muted); text-transform: uppercase; letter-spacing: .04em; }

.ad-input, .ad-select {
    width: 100%; height: 42px; padding: 0 12px;
    border: 1.5px solid var(--ad-border);
    border-radius: var(--ad-radius-sm);
    background: var(--ad-bg); color: var(--ad-text);
    font-family: inherit; font-size: 0.9rem;
    outline: none; transition: border-color .2s, box-shadow .2s; box-sizing: border-box;
}
.ad-input:focus, .ad-select:focus { border-color: var(--ad-primary); box-shadow: 0 0 0 3px rgba(79,110,247,.12); background: #fff; }

/* Select wrapper */
.ad-select-wrap { position: relative; }
.ad-select { appearance: none; padding-right: 32px; cursor: pointer; }
.ad-select-arrow { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--ad-muted); font-size: 0.75rem; }

/* Prefix / suffix inputs */
.ad-input-prefix-wrap { position: relative; display: flex; align-items: center; }
.ad-input-prefix, .ad-input-suffix {
    position: absolute; font-size: 0.85rem; font-weight: 600; color: var(--ad-muted);
}
.ad-input-prefix { left: 12px; }
.ad-input-suffix { right: 12px; }
.ad-input-prefixed { padding-left: 26px; }
.ad-input-suffixed { padding-right: 30px; }
.ad-input-prefix-wrap .ad-input { flex: 1; }

/* Form actions */
.ad-form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }

/* ── Buttons ──────────────────────────────────── */
.ad-btn { display: inline-flex; align-items: center; gap: 8px; padding: 0 22px; height: 40px; border-radius: var(--ad-radius-sm); border: none; font-family: inherit; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background .18s, transform .12s; }
.ad-btn:active { transform: scale(.97); }
.ad-btn:disabled { opacity: .6; cursor: not-allowed; }
.ad-btn-primary { background: var(--ad-primary); color: #fff; }
.ad-btn-primary:hover:not(:disabled) { background: var(--ad-primary-dk); }
.ad-btn-ghost { background: transparent; color: var(--ad-muted); border: 1.5px solid var(--ad-border); }
.ad-btn-ghost:hover { background: var(--ad-bg); }

/* Spinner */
.ad-spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Card header ──────────────────────────────── */
.ad-card-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 22px; border-bottom: 1px solid var(--ad-border); flex-wrap: wrap; gap: 10px; }
.ad-card-header-left { display: flex; align-items: center; gap: 10px; }

/* ── Table search ─────────────────────────────── */
.ad-tbl-search { display: flex; align-items: center; gap: 8px; background: var(--ad-bg); border: 1.5px solid var(--ad-border); border-radius: var(--ad-radius-sm); padding: 6px 12px; transition: border-color .2s; }
.ad-tbl-search:focus-within { border-color: var(--ad-primary); background: #fff; box-shadow: 0 0 0 3px rgba(79,110,247,.1); }
.ad-tbl-search svg { width: 14px; height: 14px; stroke: var(--ad-muted); flex-shrink: 0; }
.ad-tbl-search input { border: none; background: transparent; font-family: inherit; font-size: 0.82rem; color: var(--ad-text); outline: none; width: 170px; }
.ad-tbl-search input::placeholder { color: #adb5c7; }

/* ── Table ────────────────────────────────────── */
.ad-table-wrap { overflow-x: auto; }
.ad-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.ad-table th { padding: 12px 16px; text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--ad-muted); border-bottom: 2px solid var(--ad-border); white-space: nowrap; }
.ad-th-num, .ad-td-num { width: 54px; text-align: center; }
.ad-td-num { font-size: 0.78rem; font-weight: 700; color: #adb5c7; }
.ad-th-action, .ad-td-action { width: 110px; text-align: center; }
.ad-row { transition: background .15s; }
.ad-row:hover { background: #f8f9ff; }
.ad-table td { padding: 12px 16px; border-bottom: 1px solid #f0f2f8; vertical-align: middle; }
.ad-row:last-child td { border-bottom: none; }

/* Category cell with avatar */
.ad-cell-cat { display: flex; align-items: center; gap: 9px; }
.ad-cat-avatar { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #4f6ef7, #7b5ea7); color: #fff; font-size: 0.72rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* Month cell with icon */
.ad-month-cell { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #4a5568; font-weight: 500; }
.ad-month-cell svg { width: 13px; height: 13px; stroke: var(--ad-primary); flex-shrink: 0; }

/* Chips / pills */
.ad-category-chip { display: inline-block; background: var(--ad-chip-bg); color: var(--ad-chip-text); padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.ad-rate-pill { display: inline-block; background: #fff5e6; color: #b96b00; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; }
.ad-amount { font-weight: 700; color: var(--ad-text); font-size: 0.875rem; }

/* Icon action buttons */
.ad-action-group { display: inline-flex; align-items: center; gap: 6px; }
.ad-btn-icon { width: 32px; height: 32px; border: none; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: transform .15s, box-shadow .15s; }
.ad-btn-icon:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,.13); }
.ad-btn-icon:active { transform: scale(.9); }
.ad-btn-icon svg { width: 14px; height: 14px; }
.ad-btn-edit   { background: #f5a623; color: #fff; }
.ad-btn-delete { background: var(--ad-danger); color: #fff; }

/* Empty state */
.ad-empty { padding: 46px 16px !important; }
.ad-empty-inner { display: flex; flex-direction: column; align-items: center; gap: 10px; color: var(--ad-muted); }
.ad-empty-inner svg { width: 42px; height: 42px; opacity: .35; }
.ad-empty-inner p { margin: 0; font-size: 0.875rem; }

/* ── Pagination ───────────────────────────────── */
.ad-pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-top: 1px solid var(--ad-border); flex-wrap: wrap; gap: 10px; }
.ad-page-entries { font-size: 0.78rem; color: var(--ad-muted); }
.ad-page-entries strong { color: var(--ad-primary); }
.ad-page-controls { display: flex; align-items: center; gap: 5px; }
.ad-page-btn { width: 32px; height: 32px; border: 1.5px solid var(--ad-border); background: var(--ad-surface); border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .18s; }
.ad-page-btn svg { width: 14px; height: 14px; stroke: var(--ad-muted); }
.ad-page-btn:hover:not(:disabled) { border-color: var(--ad-primary); background: var(--ad-chip-bg); }
.ad-page-btn:hover:not(:disabled) svg { stroke: var(--ad-primary); }
.ad-page-btn:disabled { opacity: .35; cursor: not-allowed; }
.ad-page-num { min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 0.82rem; font-weight: 700; }
.ad-page-num.active { background: var(--ad-primary); color: #fff; box-shadow: 0 3px 8px rgba(79,110,247,.3); }

/* ── Responsive ───────────────────────────────── */
@media (max-width: 600px) {
    .ad-form-grid { grid-template-columns: 1fr; }
    .ad-tbl-search input { width: 110px; }
    .ad-page-entries { display: none; }
}

/* ── Delete Modal ─────────────────────────────── */
.ad-modal-overlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(15, 20, 40, 0.55);
    backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.ad-modal {
    background: #fff;
    border-radius: 18px;
    padding: 36px 32px 28px;
    max-width: 380px; width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    text-align: center;
    animation: modal-pop .22s cubic-bezier(.34,1.56,.64,1);
}
@keyframes modal-pop {
    from { transform: scale(.88); opacity: 0; }
    to   { transform: scale(1);   opacity: 1; }
}
.ad-modal-icon {
    width: 62px; height: 62px;
    background: #fdf1f1;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
}
.ad-modal-icon svg {
    width: 28px; height: 28px;
    stroke: #e05252; stroke-width: 2;
}
.ad-modal-title {
    font-size: 1.15rem; font-weight: 700;
    color: #1e2533; margin: 0 0 10px;
}
.ad-modal-msg {
    font-size: 0.84rem; color: #7a8499;
    line-height: 1.6; margin: 0 0 26px;
}
.ad-modal-actions {
    display: flex; gap: 10px; justify-content: center;
}
.ad-modal-actions .ad-btn { flex: 1; justify-content: center; }
.ad-btn-danger {
    background: #e05252; color: #fff;
}
.ad-btn-danger:hover:not(:disabled) { background: #c83c3c; }

/* Modal transition */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity .2s; }
.modal-fade-enter, .modal-fade-leave-to { opacity: 0; }
</style>