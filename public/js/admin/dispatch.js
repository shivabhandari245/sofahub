$(document).ready(function() {
    // =========================
    // Open Distribute Batch Modal (dynamic rows)
    // =========================
    $(document).on('click', '.sendBtn', function() {
        const button = $(this);
        const dispatchId = button.data('id');
        const quantity = button.data('quantity');
        const productName = button.data('product-name') || 'Batch';
        const userName = button.data('user-name') || 'User';

        // Store data for later use
        $('#distribute_dispatch_id').val(dispatchId);
        $('#distribute_quantity').attr('max', quantity).val(quantity);
        $('#remainingQtyInfo').text('Remaining: ' + quantity);

        // Show SweetAlert confirmation first
        Swal.fire({
            title: 'Send Dispatch?',
            html: `<div style="text-align: left;">
                <p>Are you ready to send <strong>${productName}</strong> to <strong> ShowRoom </strong>?</p>
                <p class="text-info" style="font-size: 0.9em; margin-top: 10px;">
                    <i class="bi bi-info-circle"></i> Quantity: ${quantity} units
                </p>
            </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // If confirmed, show the distribute modal
                $('#distributeModal').fadeIn();
            }
        });
    });

    // =========================
    // Close Modals
    // =========================
    $(document).on('click', '.close-modal', function() {
        $(this).closest('.modal').fadeOut();
    });

    $(window).click(function(event) {
        if ($(event.target).hasClass('modal')) {
            $(event.target).fadeOut();
        }
    });

    // =========================
    // Distribute Batch via AJAX with SweetAlert
    // =========================
    $('#distributeForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Distributing...');

        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                // Show success SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Batch distributed successfully!',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                $('#distributeModal').fadeOut();
                loadDispatchTable(currentPage); // reload current AJAX page
            },
            error: function(xhr) {
                let errorMessage = 'Error distributing batch!';
                if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON?.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                
                // Show error SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // =========================
    // Send Dispatch to Transit with SweetAlert
    // =========================
    $(document).on('click', '.sendBtnTransit', function() {
        const button = $(this);
        const dispatchId = button.data('id');
        const productName = button.data('product-name') || 'Dispatch';
        const quantity = button.data('quantity') || 0;

        Swal.fire({
            title: 'Mark as In Transit?',
            html: `<div style="text-align: left;">
                <p>Are you sure you want to send <strong>${productName}</strong> (Qty: ${quantity}) to transit?</p>
                <div class="form-group mt-3">
                    <label for="swal-driver" class="form-label">Driver Name *</label>
                    <input type="text" id="swal-driver" class="form-control" placeholder="Enter driver name" required>
                </div>
            </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Send to Transit',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            preConfirm: () => {
                const driver = $('#swal-driver').val();
                if (!driver || driver.trim() === '') {
                    Swal.showValidationMessage('Please enter driver name');
                    return false;
                }
                return driver;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const driver = result.value;
                
                // Show loading
                Swal.fire({
                    title: 'Sending...',
                    text: 'Please wait while we process your request',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '/admin/sendDispatch',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        dispatch_id: dispatchId,
                        driver: driver
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sent!',
                            text: res.message || 'Dispatch sent successfully!',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                        loadDispatchTable(currentPage);
                    },
                    error: function(err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: err.responseJSON?.message || 'Error sending dispatch',
                        });
                    }
                });
            }
        });
    });

    // =========================
    // Cancel Dispatch with SweetAlert
    // =========================
    $(document).on('click', '.cancelBtn', function() {
        const button = $(this);
        const dispatchId = button.data('id');
        const productName = button.data('product-name') || 'Dispatch';
        const status = button.data('status') || 'Unknown';

        Swal.fire({
            title: 'Cancel Dispatch?',
            html: `<div style="text-align: left;">
                <p>Are you sure you want to cancel <strong>${productName}</strong>?</p>
                <p class="text-danger" style="font-size: 0.9em; margin-top: 10px;">
                    <i class="bi bi-exclamation-triangle"></i> Current Status: ${status}
                </p>
                <p class="text-danger" style="font-size: 0.9em;">
                    <i class="bi bi-exclamation-triangle"></i> This action cannot be undone.
                </p>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'Keep it',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: '/admin/cancelDispatch',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        dispatch_id: dispatchId
                    }
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to cancel dispatch');
                    }
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(
                        `Cancel failed: ${error.statusText || error.responseJSON?.message || 'Unknown error'}`
                    );
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Cancelled!',
                    text: 'Dispatch has been cancelled successfully.',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                loadDispatchTable(currentPage);
            }
        });
    });

    // =========================
    // Filter/search events
    // =========================
    $('#filterDispatchStatus').change(function() {
        loadDispatchTable(1);
    });

    $('#searchDispatches').on('input', function() {
        loadDispatchTable(1);
    });
});

let currentPage = 1;

function loadDispatchTable(page = 1) {
    currentPage = page;

    $.get('/admin/dispatchtableajax', {
        page: page,
        search: $('#searchDispatches').val(),
        status: $('#filterDispatchStatus').val()
    }, function(res) {
        let rows = '';
        let sn = (res.current_page - 1) * res.per_page + 1;

        if (res.data.length === 0) {
            rows = `<tr>
                <td colspan="11" class="text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 2rem; color: #6c757d;"></i>
                    <p class="mt-2 text-muted">No dispatches found</p>
                </td>
            </tr>`;
        } else {
            res.data.forEach(d => {
                const productName = d.batch?.product?.name || 'Unknown';
                const quantity = d.quantity;
                const userName = d.user?.name || 'Unknown';
                const status = d.status;
                
                rows += `
                <tr>
                    <td>${sn++}</td>
                    <td>${productName}</td>
                    <td>${d.batch?.product?.category?.name ?? '-'}</td>
                    <td>${d.batch?.product?.quality?.name ?? '-'}</td>
                    <td>${quantity}</td>
                    <td>${Number(d.batch?.expected_unit_cost ?? 0).toFixed(2)}</td>
                    <td>${Number(d.batch?.total_cost ?? 0).toFixed(2)}</td>
                    <td>${userName}</td>
                    <td>${d.driver ?? '-'}</td>
                    <td>
                        <span class="status-badge status-${status.toLowerCase().replace(' ', '-')}">
                            ${status}
                        </span>
                    </td>
                    <td>
                        ${status === 'Pending' 
                            ? `<button class="btn btn-sm btn-success sendBtn" 
                                 data-id="${d.id}" 
                                 data-quantity="${quantity}"
                                 data-product-name="${productName.replace(/"/g, '&quot;')}"
                                 data-user-name="${userName.replace(/"/g, '&quot;')}"
                                 title="Send Dispatch">
                                <i class="bi bi-send"></i> Send
                              </button>`
                            : status === 'In Transit' 
                                ? `<button class="btn btn-sm btn-danger cancelBtn" 
                                     data-id="${d.id}"
                                     data-product-name="${productName.replace(/"/g, '&quot;')}"
                                     data-status="${status}"
                                     title="Cancel Dispatch">
                                    <i class="bi bi-x-circle"></i> Cancel
                                  </button>`
                                : '-'}
                    </td>
                </tr>`;
            });
        }

        $('#dispatchTableBody').html(rows);
        renderPagination(res.current_page, res.last_page);
    });
}

function renderPagination(current, last) {
    let html = '';
    if (last <= 1) {
        $('#pagination').html('');
        return;
    }

    // Previous button
    html += `<button class="btn btn-sm btn-outline-secondary" ${current === 1 ? 'disabled' : ''} onclick="loadDispatchTable(${current - 1})">
                <i class="bi bi-chevron-left"></i> Prev
            </button>`;

    // Page numbers
    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - 2 && i <= current + 2)) {
            html += `<button class="btn btn-sm ${i === current ? 'btn-primary' : 'btn-outline-primary'}" onclick="loadDispatchTable(${i})">${i}</button>`;
        } else if (i === current - 3 || i === current + 3) {
            html += `<span class="mx-1">...</span>`;
        }
    }

    // Next button
    html += `<button class="btn btn-sm btn-outline-secondary" ${current === last ? 'disabled' : ''} onclick="loadDispatchTable(${current + 1})">
                Next <i class="bi bi-chevron-right"></i>
            </button>`;

    $('#pagination').html(html);
}

// Initial load
loadDispatchTable();

// Add CSS for status badges
$('head').append(`
<style>
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-block;
    }
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    .status-in-transit {
        background-color: #cce5ff;
        color: #004085;
        border: 1px solid #b8daff;
    }
    .status-delivered {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
`);