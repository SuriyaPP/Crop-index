<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div id="wrapper">
    <div class="content">
        <div id="app">
            <div class="panel_s custom-panel">
                <div class="panel-body">

                    <div class="page-header-section">
                        <div>
                            <h2 class="page-title">Donors & Vendors</h2>
                            <p class="page-subtitle">Manage donors and vendors information</p>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs custom-tabs">
                        <li :class="{active: activeTab === 'donors'}">
                            <a href="#" @click.prevent="switchTab('donors')">
                                <i class="fa fa-users"></i> Donors
                                <span class="tab-badge">{{ donorTotal }}</span>
                            </a>
                        </li>
                        <li :class="{active: activeTab === 'vendors'}">
                            <a href="#" @click.prevent="switchTab('vendors')">
                                <i class="fa fa-building"></i> Vendors
                                <span class="tab-badge">{{ vendorTotal }}</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Top action bar -->
                    <div class="top-action-bar">
                        <div class="left-actions">
                            <button class="btn btn-primary custom-btn" @click="openModal('add')">
                                <i class="fa fa-plus"></i>
                                Add {{ activeTab === 'donors' ? 'Donor' : 'Vendor' }}
                            </button>
                        </div>
                        <div class="right-actions">
                            <div class="limit-select-wrap">
                                <label>Show</label>
                                <select class="form-control limit-select" v-model="currentLimit"
                                    @change="onLimitChange">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label>entries</label>
                            </div>
                            <div class="search-wrap">
                                <input type="text" class="form-control search-input" placeholder="Search..."
                                    v-model="searchQuery" @input="onSearch">
                                <i class="fa fa-search search-icon"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Donors Table -->
                    <div v-show="activeTab === 'donors'">
                        <div class="pagination-info-top" v-if="donorTotal > 0">
                            Showing {{ (donorPage - 1) * currentLimit + 1 }} to {{ Math.min(donorPage * currentLimit,
                            donorTotal) }} of {{ donorTotal }} entries
                        </div>
                        <table class="table table-bordered custom-table text-center">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Mobile</th>
                                    <th class="text-center">Balance</th>
                                    <th class="text-center">Address</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="donorLoading">
                                    <td colspan="6" class="text-center empty-msg">
                                        <i class="fa fa-spinner fa-spin"></i> Loading...
                                    </td>
                                </tr>
                                <tr v-else-if="donors.length === 0">
                                    <td colspan="6" class="text-center empty-msg">
                                        <i class="fa fa-inbox"></i>
                                        No donor records found
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in donors" :key="item.donorid">
                                    <td>{{ (donorPage - 1) * currentLimit + index + 1 }}</td>
                                    <td>{{ item.name }}</td>
                                    <td>{{ item.phonenumber }}</td>
                                    <td>{{ item.balance }}</td>
                                    <td>{{ item.address || '—' }}</td>
                                    <td class="text-center action-btns">
                                        <button class="btn btn-xs btn-info" title="View" @click="viewRecord(item)">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button class="btn btn-xs btn-warning" title="Edit"
                                            @click="openModal('edit', item, 'donor')">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button class="btn btn-xs btn-danger" title="Delete"
                                            @click="confirmDelete(item.donorid, 'donor')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Donor Pagination -->
                        <div class="pagination-bar" v-if="donorTotal > 0">
                            <ul class="pagination custom-pagination">
                                <li :class="{disabled: donorPage === 1}">
                                    <a href="#" @click.prevent="changePage('donor', donorPage - 1)">
                                        <i class="fa fa-chevron-left"></i>
                                    </a>
                                </li>
                                <li v-for="p in donorPages" :key="p"
                                    :class="{active: p === donorPage, 'page-ellipsis': p === '...'}">
                                    <a href="#" @click.prevent="p !== '...' && changePage('donor', p)">{{ p }}</a>
                                </li>
                                <li :class="{disabled: donorPage === donorTotalPages}">
                                    <a href="#" @click.prevent="changePage('donor', donorPage + 1)">
                                        <i class="fa fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Vendors Table -->
                    <div v-show="activeTab === 'vendors'">
                        <div class="pagination-info-top" v-if="vendorTotal > 0">
                            Showing {{ (vendorPage - 1) * currentLimit + 1 }} to {{ Math.min(vendorPage * currentLimit,
                            vendorTotal) }} of {{ vendorTotal }} entries
                        </div>
                        <table class="table table-bordered custom-table text-center">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Mobile</th>
                                    <th class="text-center">Balance</th>
                                    <th class="text-center">Address</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="vendorLoading">
                                    <td colspan="6" class="text-center empty-msg">
                                        <i class="fa fa-spinner fa-spin"></i> Loading...
                                    </td>
                                </tr>
                                <tr v-else-if="vendors.length === 0">
                                    <td colspan="6" class="text-center empty-msg">
                                        <i class="fa fa-inbox"></i>
                                        No vendor records found
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in vendors" :key="item.vendorid">
                                    <td>{{ (vendorPage - 1) * currentLimit + index + 1 }}</td>
                                    <td>{{ item.name }}</td>
                                    <td>{{ item.phonenumber }}</td>
                                    <td>{{ item.balance }}</td>
                                    <td>{{ item.address || '—' }}</td>
                                    <td class="text-center action-btns">
                                        <button class="btn btn-xs btn-info" title="View" @click="viewRecord(item)">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button class="btn btn-xs btn-warning" title="Edit"
                                            @click="openModal('edit', item, 'vendor')">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button class="btn btn-xs btn-danger" title="Delete"
                                            @click="confirmDelete(item.vendorid, 'vendor')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Vendor Pagination -->
                        <div class="pagination-bar" v-if="vendorTotal > 0">
                            <ul class="pagination custom-pagination">
                                <li :class="{disabled: vendorPage === 1}">
                                    <a href="#" @click.prevent="changePage('vendor', vendorPage - 1)">
                                        <i class="fa fa-chevron-left"></i>
                                    </a>
                                </li>
                                <li v-for="p in vendorPages" :key="p"
                                    :class="{active: p === vendorPage, 'page-ellipsis': p === '...'}">
                                    <a href="#" @click.prevent="p !== '...' && changePage('vendor', p)">{{ p }}</a>
                                </li>
                                <li :class="{disabled: vendorPage === vendorTotalPages}">
                                    <a href="#" @click.prevent="changePage('vendor', vendorPage + 1)">
                                        <i class="fa fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Add / Edit Modal -->
            <div class="modal fade" id="addModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" @click="closeModal">&times;</button>
                            <h4 class="modal-title">
                                {{ modalMode === 'add' ? 'Add' : 'Edit' }}
                                {{ activeTab === 'donors' ? 'Donor' : 'Vendor' }}
                            </h4>
                        </div>
                        <form @submit.prevent="saveData">
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" v-model="form.name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Mobile Number <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" v-model="form.phonenumber"
                                                pattern="[0-9]{10}" maxlength="10" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" v-model="form.email" required>
                                </div>
                                <div class="form-group">
                                    <label>Opening Balance</label>
                                    <input type="number" step="0.01" min="0" class="form-control" v-model="form.balance"
                                        placeholder="Enter Opening Balance">
                                </div>
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea class="form-control" rows="3" v-model="form.address"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>PAN / GST Number</label>
                                    <input type="text" class="form-control" v-model="form.gst_number">
                                </div>
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea class="form-control" rows="3" v-model="form.notes"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success" :disabled="loading">
                                    <span v-if="loading">
                                        <i class="fa fa-spinner fa-spin"></i> Saving...
                                    </span>
                                    <span v-else>
                                        <i class="fa fa-save"></i> {{ modalMode === 'add' ? 'Save' : 'Update' }}
                                    </span>
                                </button>
                                <button type="button" class="btn btn-default" @click="closeModal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- View Modal -->
            <div class="modal fade" id="viewModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" @click="closeViewModal">&times;</button>
                            <h4 class="modal-title">
                                <i class="fa fa-user"></i> {{ viewData.name }}
                            </h4>
                        </div>
                        <div class="modal-body">
                            <table class="table view-detail-table">
                                <tr>
                                    <th>Name</th>
                                    <td>{{ viewData.name }}</td>
                                </tr>
                                <tr>
                                    <th>Mobile</th>
                                    <td>{{ viewData.phonenumber }}</td>
                                </tr>
                                <tr>
                                    <th>Balance</th>
                                    <td>{{ viewData.balance }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ viewData.email }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ viewData.address || '—' }}</td>
                                </tr>
                                <tr>
                                    <th>PAN / GST</th>
                                    <td>{{ viewData.gst_number || '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Notes</th>
                                    <td>{{ viewData.notes || '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Created</th>
                                    <td>{{ viewData.datecreated || '—' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" @click="closeViewModal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirm Modal -->
            <div class="modal fade" id="deleteModal">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header modal-header-danger">
                            <button type="button" class="close" @click="closeDeleteModal">&times;</button>
                            <h4 class="modal-title">
                                <i class="fa fa-exclamation-triangle"></i> Confirm Delete
                            </h4>
                        </div>
                        <div class="modal-body text-center">
                            <p>Are you sure you want to delete this record?</p>
                            <p class="text-danger"><small>This action cannot be undone.</small></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" @click="deleteRecord" :disabled="loading">
                                <span v-if="loading">
                                    <i class="fa fa-spinner fa-spin"></i> Deleting...
                                </span>
                                <span v-else>
                                    <i class="fa fa-trash"></i> Delete
                                </span>
                            </button>
                            <button type="button" class="btn btn-default" @click="closeDeleteModal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>

<script>
    new Vue({
        el: '#app',

        data: {
            activeTab: 'donors',
            loading: false,
            donorLoading: false,
            vendorLoading: false,

            donors: [],
            donorPage: 1,
            donorTotal: 0,
            donorTotalPages: 1,

            vendors: [],
            vendorPage: 1,
            vendorTotal: 0,
            vendorTotalPages: 1,

            currentLimit: 10,
            searchQuery: '',
            searchTimer: null,

            modalMode: 'add',
            editId: null,
            editType: null,

            deleteId: null,
            deleteType: null,

            viewData: {},

            form: {
                name: '',
                phonenumber: '',
                balance: '0.00',
                email: '',
                address: '',
                gst_number: '',
                notes: ''
            }
        },

        computed: {
            donorPages() { return this.buildPages(this.donorPage, this.donorTotalPages); },
            vendorPages() { return this.buildPages(this.vendorPage, this.vendorTotalPages); }
        },

        mounted() {
            this.getDonors();
            this.getVendors();
        },

        methods: {

            switchTab(tab) {
                this.activeTab = tab;
                this.searchQuery = '';
            },

            getDonors() {
                this.donorLoading = true;
                $.ajax({
                    url: admin_url + 'ngo_trust/parties/get_donors',
                    type: 'GET',
                    data: {
                        page: this.donorPage,
                        limit: this.currentLimit,
                        search: this.searchQuery
                    },
                    success: (response) => {
                        try {
                            var res = JSON.parse(response);
                            this.donors = res.data || [];
                            this.donorTotal = res.total || 0;
                            this.donorTotalPages = Math.ceil((res.total || 0) / this.currentLimit) || 1;
                        } catch (e) {
                            this.donors = [];
                            this.donorTotal = 0;
                        }
                        this.donorLoading = false;
                    },
                    error: () => {
                        this.donorLoading = false;
                        alert_float('danger', 'Failed to load donors');
                    }
                });
            },

            getVendors() {
                this.vendorLoading = true;
                $.ajax({
                    url: admin_url + 'ngo_trust/parties/get_vendors',
                    type: 'GET',
                    data: {
                        page: this.vendorPage,
                        limit: this.currentLimit,
                        search: this.searchQuery
                    },
                    success: (response) => {
                        try {
                            var res = JSON.parse(response);
                            this.vendors = res.data || [];
                            this.vendorTotal = res.total || 0;
                            this.vendorTotalPages = Math.ceil((res.total || 0) / this.currentLimit) || 1;
                        } catch (e) {
                            this.vendors = [];
                            this.vendorTotal = 0;
                        }
                        this.vendorLoading = false;
                    },
                    error: () => {
                        this.vendorLoading = false;
                        alert_float('danger', 'Failed to load vendors');
                    }
                });
            },

            reloadActive() {
                if (this.activeTab === 'donors') {
                    this.getDonors();
                } else {
                    this.getVendors();
                }
            },

            changePage(type, page) {
                if (type === 'donor') {
                    if (page < 1 || page > this.donorTotalPages) return;
                    this.donorPage = page;
                    this.getDonors();
                } else {
                    if (page < 1 || page > this.vendorTotalPages) return;
                    this.vendorPage = page;
                    this.getVendors();
                }
            },

            onLimitChange() {
                this.donorPage = 1;
                this.vendorPage = 1;
                this.getDonors();
                this.getVendors();
            },

            onSearch() {
                clearTimeout(this.searchTimer);
                this.searchTimer = setTimeout(() => {
                    this.donorPage = 1;
                    this.vendorPage = 1;
                    this.reloadActive();
                }, 400);
            },

            buildPages(current, total) {
                var pages = [];
                if (total <= 7) {
                    for (var i = 1; i <= total; i++) pages.push(i);
                    return pages;
                }
                pages.push(1);
                if (current > 3) pages.push('...');
                var start = Math.max(2, current - 1);
                var end = Math.min(total - 1, current + 1);
                for (var j = start; j <= end; j++) pages.push(j);
                if (current < total - 2) pages.push('...');
                pages.push(total);
                return pages;
            },

            openModal(mode, item, type) {
                this.modalMode = mode;
                if (mode === 'edit') {
                    this.editId = type === 'donor' ? item.donorid : item.vendorid;
                    this.editType = type;
                    this.form = {
                        name: item.name || '',
                        phonenumber: item.phonenumber || '',
                        balance: item.balance || '0.00',
                        email: item.email || '',
                        address: item.address || '',
                        gst_number: item.gst_number || '',
                        notes: item.notes || ''
                    };
                }
                $('#addModal').modal('show');
            },

            closeModal() {
                $('#addModal').modal('hide');
                this.resetForm();
            },

            resetForm() {
                this.modalMode = 'add';
                this.editId = null;
                this.editType = null;
                this.form = {
                    name: '', phonenumber: '', email: '',
                    address: '', gst_number: '', notes: ''
                };
            },

            saveData() {
                this.loading = true;

                var isEdit = this.modalMode === 'edit';
                var type = isEdit ? this.editType : (this.activeTab === 'donors' ? 'donor' : 'vendor');
                var url = isEdit
                    ? admin_url + 'ngo_trust/parties/' + (type === 'donor' ? 'update_donor' : 'update_vendor') + '/' + this.editId
                    : admin_url + 'ngo_trust/parties/' + (type === 'donor' ? 'create_donor' : 'create_vendor');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: this.form,
                    success: (response) => {
                        response = JSON.parse(response);
                        if (response.success) {
                            alert_float('success', response.message);
                            this.closeModal();
                            if (type === 'donor') { this.getDonors(); } else { this.getVendors(); }
                        } else {
                            alert_float('danger', response.message || 'Failed to save');
                        }
                        this.loading = false;
                    },
                    error: () => {
                        this.loading = false;
                        alert_float('danger', 'Something went wrong');
                    }
                });
            },

            viewRecord(item) {
                this.viewData = item;
                $('#viewModal').modal('show');
            },

            closeViewModal() {
                $('#viewModal').modal('hide');
                this.viewData = {};
            },

            confirmDelete(id, type) {
                this.deleteId = id;
                this.deleteType = type;
                $('#deleteModal').modal('show');
            },

            closeDeleteModal() {
                $('#deleteModal').modal('hide');
                this.deleteId = null;
                this.deleteType = null;
            },

            deleteRecord() {
                console.log("this.deleteType",this.deleteType)
                this.loading = true;
                var url = this.deleteType === 'donor'
                    ? admin_url + 'ngo_trust/parties/delete_donor/' + this.deleteId
                    : admin_url + 'ngo_trust/parties/delete_vendor/' + this.deleteId;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { _method: 'DELETE' },
                    success: (response) => {
                        response = JSON.parse(response);
                        if (response.success) {
                            alert_float('success', response.message);
                            this.closeDeleteModal();
                                this.getDonors();
                                this.getVendors();
                        } else {
                            alert_float('danger', response.message || 'Failed to delete');
                        }
                        this.loading = false;
                    },
                    error: () => {
                        this.loading = false;
                        alert_float('danger', 'Something went wrong');
                    }
                });
            }
        }
    });
</script>

<style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .custom-panel {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 25px rgba(0, 0, 0, .08);
    }

    .panel-body {
        padding: 25px;
    }

    .page-header-section {
        margin-bottom: 25px;
    }

    .page-title {
        margin: 0;
        font-weight: 700;
    }

    .page-subtitle {
        color: #777;
        margin-top: 5px;
    }

    /* Tabs */
    .custom-tabs {
        border-bottom: none;
        margin-bottom: 0;
    }

    .custom-tabs li a {
        border: none !important;
        font-weight: 600;
        color: #666 !important;
        transition: .3s;
    }

    .custom-tabs li.active a {
        background: #4f46e5 !important;
        color: #fff !important;
        border-radius: 8px;
    }

    .tab-badge {
        display: inline-block;
        border-radius: 20px;
        padding: 1px 8px;
        font-size: 11px;
        margin-left: 5px;
    }

    .custom-tabs li.active .tab-badge {
        background: rgba(255, 255, 255, 0.25);
        color: #fff;
    }

    .custom-tabs li:not(.active) .tab-badge {
        background: #e8e8f0;
        color: #4f46e5;
    }

    /* Action bar */
    .top-action-bar {
        margin-top: 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .right-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .limit-select-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #555;
    }

    .limit-select {
        width: 70px !important;
        min-height: 36px !important;
        padding: 4px 8px;
        display: inline-block;
    }

    .search-wrap {
        position: relative;
    }

    .search-input {
        padding-left: 32px !important;
        width: 220px;
        min-height: 36px !important;
        border-radius: 8px !important;
    }

    .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
        pointer-events: none;
    }

    .custom-btn {
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
    }

    .custom-btn:hover {
        transform: translateY(-2px);
    }

    /* Showing info above table */
    .pagination-info-top {
        font-size: 13px;
        color: #666;
        margin-bottom: 8px;
    }

    /* Table */
    .custom-table {
        background: #fff;
        margin-bottom: 0;
    }

    .custom-table thead {
        background: #f5f6fa;
    }

    .custom-table th {
        font-weight: 600;
        vertical-align: middle;
    }

    .custom-table td {
        vertical-align: middle;
    }

    .empty-msg {
        color: #aaa;
        padding: 30px !important;
        font-size: 14px;
    }

    .empty-msg i {
        display: block;
        font-size: 28px;
        margin-bottom: 8px;
    }

    /* Action buttons */
    .action-btns {
        white-space: nowrap;
    }

    .action-btns .btn {
        margin: 0 2px;
        border-radius: 6px;
        padding: 8px;
    }

    /* Pagination */
    .pagination-bar {
        display: flex;
        justify-content: flex-end;
        padding: 10px 0 4px;
    }

    .custom-pagination {
        margin: 0;
    }

    .custom-pagination li a {
        border-radius: 6px !important;
        padding: 5px 10px;
        color: #4f46e5;
        border-color: #e0e0f0;
    }

    .custom-pagination li.active a {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
    }

    .custom-pagination li.disabled a {
        color: #ccc;
        pointer-events: none;
    }

    .custom-pagination li.page-ellipsis a {
        cursor: default;
        color: #999;
    }

    /* View table */
    .view-detail-table th {
        width: 130px;
        color: #666;
        font-weight: 600;
    }

    .view-detail-table td {
        color: #333;
    }

    /* Modals */
    .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }

    .modal-header-danger {
        background: #fff5f5;
    }

    .modal-header-danger .modal-title {
        color: #dc3545;
    }

    .form-control {
        border-radius: 8px;
        min-height: 42px;
    }

    textarea.form-control {
        min-height: auto;
    }

    .btn-success,
    .btn-default,
    .btn-danger {
        border-radius: 8px;
    }

    .modal.fade .modal-dialog {
        transform: translateY(-50px);
        transition: .3s ease;
    }

    .modal.in .modal-dialog {
        transform: translateY(0);
    }
</style>
</body>

</html>