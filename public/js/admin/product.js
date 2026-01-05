document.addEventListener("DOMContentLoaded", function () {
    const apiURL = "/admin/productlist";
    const tableBody = document.getElementById("productBody");

    const searchInput = document.getElementById("searchProducts");
    const filterStatus = document.getElementById("filterStatus");
    const filterSource = document.getElementById("filterSource");

    // Initial load
    loadProducts();

    // Fetch products from API
    function loadProducts() {
        fetch(apiURL)
            .then(res => res.json())
            .then(data => {
                renderProducts(data);
                updateStats(data);
            })
            .catch(err => console.error("Error loading products:", err));
    }

    // Render products in table
    function renderProducts(products) {
        tableBody.innerHTML = "";

        const searchText = searchInput.value.toLowerCase();
        const statusFilter = filterStatus.value;
        const sourceFilter = filterSource.value;

        products
            .filter(p =>
                p.product_name.toLowerCase().includes(searchText) &&
                (sourceFilter === "all" || p.source === sourceFilter)
            )
            .filter(p => matchStatus(p, statusFilter))
            .forEach(p => {
                const row = `
                    <tr>
                        <td>${p.product_name}</td>
                        <td>${p.category_name}</td>
                        <td>${p.quality_name}</td>
                        <td>${p.quantity}</td>
                        <td>Rs. ${p.cost_per_product}</td>
                        <td>Rs. ${p.total_cost}</td>
                        <td>${p.showroom_name}</td>
                        <td>${p.source}</td>
                        <td>${getStatusBadge(p.quantity)}</td>
                    </tr>
                `;
                tableBody.innerHTML += row;
            });
    }

    // Determine badge based on quantity
    function getStatusBadge(qty) {
        if (qty == 0) return `<span class="badge bg-danger">Out of Stock</span>`;
        if (qty < 5) return `<span class="badge bg-warning text-dark">Low Stock</span>`;
        return `<span class="badge bg-success">Available</span>`;
    }

    // Status filter logic
    function matchStatus(product, filter) {
        if (filter === "all") return true;
        if (filter === "Available" && product.quantity >= 5) return true;
        if (filter === "Low" && product.quantity > 0 && product.quantity < 5) return true;
        if (filter === "Out of Stock" && product.quantity == 0) return true;
        return false;
    }

    // Update dashboard stats
    function updateStats(products) {
        const total = products.length;
        const available = products.filter(p => p.quantity >= 5).length;
        const low = products.filter(p => p.quantity > 0 && p.quantity < 5).length;
        const out = products.filter(p => p.quantity == 0).length;

        document.getElementById("totalProducts").innerText = total;
        document.getElementById("availableProducts").innerText = available;
        document.getElementById("lowStockProducts").innerText = low;
        document.getElementById("outOfStockProducts").innerText = out;
    }

    // Event listeners
    searchInput.addEventListener("input", loadProducts);
    filterStatus.addEventListener("change", loadProducts);
    filterSource.addEventListener("change", loadProducts);
});