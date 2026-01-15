$(document).ready(function() {

    // =========================
    // Open Distribute Batch Modal (dynamic rows)
    // =========================
    $(document).on('click', '.sendBtn', function() {
        const button = $(this);
        const dispatchId = button.data('id');
        const quantity = button.data('quantity');

        $('#distribute_dispatch_id').val(dispatchId);
        $('#distribute_quantity').attr('max', quantity).val(quantity);
        $('#remainingQtyInfo').text('Remaining: ' + quantity);

        $('#distributeModal').fadeIn();
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
    // Distribute Batch via AJAX
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
                alert(response.message);
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
                alert(errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // =========================
    // Send Dispatch via AJAX
    // =========================
    $(document).on('click', '.sendBtnTransit', function() {
        const dispatchId = $(this).data('id');
        const driver = prompt("Enter driver name:");

        if (!driver) return alert("Driver name is required!");

        $.ajax({
            url: '/admin/sendDispatch',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                dispatch_id: dispatchId,
                driver: driver
            },
            success: function(res) {
                alert(res.message || 'Dispatch sent successfully!');
                loadDispatchTable(currentPage);
            },
            error: function(err) {
                alert(err.responseJSON?.message || 'Error sending dispatch');
            }
        });
    });

    // =========================
    // Cancel Dispatch via AJAX
    // =========================
    $(document).on('click', '.cancelBtn', function() {
        if (!confirm('Are you sure you want to cancel this dispatch?')) return;

        const dispatchId = $(this).data('id');

        $.ajax({
            url: '/admin/cancelDispatch',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                dispatch_id: dispatchId
            },
            success: function(res) {
                alert(res.message);
                loadDispatchTable(currentPage);
            },
            error: function(err) {
                alert(err.responseJSON?.message || 'Error canceling dispatch');
            }
        });
    });

    // =========================
    // Remove client-side filter/search
    // =========================
    // With AJAX backend, the filtering is done server-side
    // So just reload table when user changes input
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
            rows = `<tr><td colspan="11" class="text-center">No dispatches found</td></tr>`;
        } else {
            res.data.forEach(d => {
                rows += `
                <tr>
                    <td>${sn++}</td>
                    <td>${d.batch?.product?.name ?? '-'}</td>
                    <td>${d.batch?.product?.category?.name ?? '-'}</td>
                    <td>${d.batch?.product?.quality?.name ?? '-'}</td>
                    <td>${d.quantity}</td>
                    <td>${Number(d.batch?.expected_unit_cost ?? 0).toFixed(2)}</td>
                    <td>${Number(d.batch?.total_cost ?? 0).toFixed(2)}</td>
                    <td>${d.user?.name ?? '-'}</td>
                    <td>${d.driver ?? '-'}</td>
                    <td>${d.status}</td>
                    <td>
                        ${d.status === 'Pending' 
                            ? `<button class="btn btn-sm btn-success sendBtn" data-id="${d.id}">Send</button>`
                            : d.status === 'In Transit' 
                                ? `<button class="btn btn-sm btn-danger cancelBtn" data-id="${d.id}">Cancel</button>`
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

    html += `<button class="btn btn-sm" ${current === 1 ? 'disabled' : ''} onclick="loadDispatchTable(${current - 1})">Prev</button>`;

    for (let i = 1; i <= last; i++) {
        html += `<button class="btn btn-sm ${i === current ? 'btn-primary' : ''}" onclick="loadDispatchTable(${i})">${i}</button>`;
    }

    html += `<button class="btn btn-sm" ${current === last ? 'disabled' : ''} onclick="loadDispatchTable(${current + 1})">Next</button>`;

    $('#pagination').html(html);
}

// Initial load
loadDispatchTable();

// Search & filter events
$('#searchDispatches').on('keyup', () => loadDispatchTable(1));
$('#filterDispatchStatus').on('change', () => loadDispatchTable(1));
