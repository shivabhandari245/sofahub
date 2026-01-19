$(document).ready(function () {

    /* ================= CSRF ================= */
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* ================= TOOLTIP (BOOTSTRAP 5) ================= */
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

    /* ================= FILTER ================= */
    $('#filterAll').on('click', function () {
        $('.history-item').show();
        $(this).addClass('active').siblings().removeClass('active');
    });

    $('#filterRestocked').on('click', function () {
        $('.history-item').hide();
        $('.history-item.restocked').show();
        $(this).addClass('active').siblings().removeClass('active');
    });

    $('#filterUsed').on('click', function () {
        $('.history-item').hide();
        $('.history-item.used').show();
        $(this).addClass('active').siblings().removeClass('active');
    });



    /* ================= RESTOCK MODAL ================= */
    let restockModal = new bootstrap.Modal(
        document.getElementById('restockModal')
    );

    $('.restock-btn').on('click', function () {
        const materialId = $(this).data('id');
        $('#restockMaterialId').val(materialId);
        restockModal.show();
    });

    /* ================= USE STOCK MODAL ================= */
    let useStockModal = new bootstrap.Modal(
        document.getElementById('useStockModal')
    );

    $('.use-btn').on('click', function () {
        const materialId = $(this).data('id');
        const currentStock = $(this).data('stock');

        $('#useMaterialId').val(materialId);
        $('#currentStock').text(currentStock);
        $('#useStockForm input[name="use_quantity"]').attr('max', currentStock);

        useStockModal.show();
    });

    /* ================= TOAST ================= */
    function showToast(type, message) {

        if ($('#toastContainer').length === 0) {
            $('body').append(`
                <div id="toastContainer"
                     class="toast-container position-fixed bottom-0 end-0 p-3">
                </div>
            `);
        }

        const toast = $(`
            <div class="toast text-bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        $('#toastContainer').append(toast);

        const bsToast = new bootstrap.Toast(toast[0], { delay: 3000 });
        bsToast.show();

        toast.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }

});
