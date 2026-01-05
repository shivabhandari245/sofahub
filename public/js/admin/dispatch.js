$(document).ready(function() {

    // =========================
    // Open Distribute Batch Modal
    // =========================
    $('.sendBtn').click(function() {
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
    $('.close-modal').click(function() {
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
                location.reload(); // reload page to show updated dispatches
            },
            error: function(xhr) {
                let errorMessage = 'Error distributing batch!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
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
    $('.sendBtnTransit').click(function() {
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
                location.reload();
            },
            error: function(err) {
                alert(err.responseJSON.message || 'Error sending dispatch');
            }
        });
    });

    // =========================
    // Cancel Dispatch via AJAX
    // =========================
    $('.cancelBtn').click(function() {
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
                location.reload();
            },
            error: function(err) {
                alert(err.responseJSON.message || 'Error canceling dispatch');
            }
        });
    });

    // =========================
    // Filter/Search
    // =========================
    $('#filterDispatchStatus').change(function() {
        const status = $(this).val().toLowerCase();
        $('#dispatchesTable tbody tr').each(function() {
            const rowStatus = $(this).find('td:nth-child(10)').text().toLowerCase(); // Status column
            if (status === 'all' || rowStatus === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#searchDispatches').on('input', function() {
        const search = $(this).val().toLowerCase();
        $('#dispatchesTable tbody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.includes(search));
        });
    });

});
