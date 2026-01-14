$(function () {

    let table = $('#productTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: {
            url: "/admin/products", // route('admin.products.index')
            data: function (d) {
                d.search_value = $('#searchProducts').val();
                d.status = $('#filterStatus').val();
                d.source = $('#filterSource').val();
            }
        },
        columns: [
            { data: 'name' },
            { data: 'category' },
            { data: 'quality' },
            { data: 'quantity' },
            {
                data: 'cost_per_product',
                render: d => 'Rs. ' + parseFloat(d).toFixed(2)
            },
            {
                data: 'total_cost',
                render: d => 'Rs. ' + parseFloat(d).toFixed(2)
            },
            { data: 'showroom' },
            { data: 'source' },
            { data: 'status' }
        ],
        drawCallback: function (settings) {
            if (!settings.json) return;

            $('#totalProducts').text(settings.json.stats.total);
            $('#availableProducts').text(settings.json.stats.available);
            $('#lowStockProducts').text(settings.json.stats.low);
            $('#outOfStockProducts').text(settings.json.stats.out);
        }
    });

    $('#searchProducts').on('keyup', () => table.draw());
    $('#filterStatus, #filterSource').on('change', () => table.draw());

});
