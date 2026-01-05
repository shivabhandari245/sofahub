/**
 * User Dashboard - Performance Analytics
 * Production-ready dashboard with real-time data visualization
 */

class Dashboard {
    constructor() {
        this.data = null;
        this.charts = {};
        this.init();
    }

    init() {
        this.loadData();
        this.initCharts();
        this.setupEventListeners();
        this.updateLiveData();
    }

    loadData() {
        try {
            const dataElement = document.getElementById('dashboardData');
            if (dataElement) {
                this.data = JSON.parse(dataElement.textContent);
                console.log('Dashboard data loaded:', this.data);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.showError('Failed to load dashboard data');
        }
    }

    initCharts() {
        if (!this.data) {
            console.warn('No data available for charts');
            return;
        }

        this.initSalesChart();
        this.initProductChart();
        this.initStatusChart();
        this.updateMetrics();
    }

    initSalesChart() {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        const monthlySales = this.data.monthlySales;
        const months = this.getMonthNames();
        const salesData = Object.values(monthlySales);

        // Create gradient
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(102, 126, 234, 0.2)');
        gradient.addColorStop(1, 'rgba(102, 126, 234, 0.05)');

        this.charts.sales = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Revenue (NPR)',
                    data: salesData,
                    borderColor: '#667eea',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        padding: 12,
                        callbacks: {
                            label: (context) => {
                                return `NPR ${this.formatCurrency(context.parsed.y)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            callback: (value) => `NPR ${this.formatCurrency(value)}`
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    initProductChart() {
        const ctx = document.getElementById('productChart');
        if (!ctx) return;

        const productStatus = this.data.productStatus;
        const labels = Object.keys(productStatus);
        const data = Object.values(productStatus);

        this.charts.product = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 15,
                    hoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        callbacks: {
                            label: (context) => {
                                const total = data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${context.parsed} items (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    initStatusChart() {
        const ctx = document.getElementById('statusChart');
        if (!ctx) return;

        const saleStatus = this.data.saleStatus;
        const labels = Object.keys(saleStatus);
        const data = Object.values(saleStatus);

        this.charts.status = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 15,
                    hoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        callbacks: {
                            label: (context) => {
                                const total = this.data.totalInvoices;
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    updateMetrics() {
        if (!this.data) return;

        const { monthlySales, productStatus, saleStatus, totalInvoices, totalSales, growth } = this.data;
        
        // Update current month sales
        const currentMonth = new Date().getMonth() + 1;
        const currentMonthSales = monthlySales[currentMonth] || 0;
        const currentMonthElement = document.getElementById('currentMonthSales');
        if (currentMonthElement) {
            currentMonthElement.innerHTML = `
                <i class="fas fa-arrow-up"></i>
                Current Month: NPR ${this.formatCurrency(currentMonthSales)}
            `;
        }

        // Update low stock badge
        const lowStockBadge = document.getElementById('lowStockBadge');
        if (lowStockBadge) {
            const lowStockCount = productStatus['Low Stock'] || 0;
            const isLowStock = lowStockCount > 0;
            lowStockBadge.className = `stats-badge ${isLowStock ? 'badge-danger' : 'badge-success'}`;
            lowStockBadge.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                ${lowStockCount} Low Stock Items
            `;
        }

        // Update success rate badge
        const successRateBadge = document.getElementById('successRateBadge');
        if (successRateBadge && totalInvoices > 0) {
            const successRate = ((saleStatus['Completed'] / totalInvoices) * 100).toFixed(1);
            successRateBadge.innerHTML = `
                <i class="fas fa-percentage"></i>
                ${successRate}% Success Rate
            `;
        }

        // Update inventory counts
        this.updateElementText('availableCount', productStatus['Available'] || 0);
        this.updateElementText('lowStockCount', productStatus['Low Stock'] || 0);
        this.updateElementText('outOfStockCount', productStatus['Out of Stock'] || 0);
        
        // Update sale status counts
        this.updateElementText('completedCount', saleStatus['Completed'] || 0);
        this.updateElementText('returnedCount', saleStatus['Returned'] || 0);

        // Update calculated metrics
        const avgOrderValue = totalInvoices > 0 ? totalSales / totalInvoices : 0;
        this.updateElementText('avgOrderValue', `NPR ${this.formatCurrency(avgOrderValue)}`);
        this.updateElementText('thisMonthRevenue', `NPR ${this.formatCurrency(currentMonthSales)}`);
        
        const totalItems = Object.values(productStatus).reduce((a, b) => a + b, 0);
        this.updateElementText('totalItems', totalItems);
        
        const conversionRate = totalInvoices > 0 ? ((saleStatus['Completed'] / totalInvoices) * 100).toFixed(1) : 0;
        this.updateElementText('conversionRate', `${conversionRate}%`);
    }

    updateLiveData() {
        // Simulate live data updates (replace with actual API calls in production)
        setInterval(() => {
            this.updateTimestamp();
        }, 60000); // Update every minute

        // You can add WebSocket or API polling here for real-time updates
        // this.setupWebSocket();
    }

    updateTimestamp() {
        const timestamp = new Date().toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const timeElements = document.querySelectorAll('.stats-badge i.fa-calendar-alt');
        timeElements.forEach(element => {
            const badge = element.closest('.stats-badge');
            if (badge) {
                badge.innerHTML = `<i class="fas fa-calendar-alt"></i> Last Updated: ${timestamp}`;
            }
        });
    }

    setupEventListeners() {
        // Refresh button functionality
        const refreshButtons = document.querySelectorAll('[data-refresh]');
        refreshButtons.forEach(button => {
            button.addEventListener('click', () => this.refreshData());
        });

        // Chart export functionality
        const exportButtons = document.querySelectorAll('[data-export]');
        exportButtons.forEach(button => {
            button.addEventListener('click', (e) => this.exportChart(e.target.dataset.export));
        });

        // Window resize handler
        window.addEventListener('resize', this.debounce(() => {
            Object.values(this.charts).forEach(chart => {
                if (chart && typeof chart.resize === 'function') {
                    chart.resize();
                }
            });
        }, 250));
    }

    refreshData() {
        // Show loading state
        document.body.classList.add('loading');
        
        // Simulate API call (replace with actual API call)
        setTimeout(() => {
            // In production, make an API call here
            // fetch('/api/dashboard-data')
            //     .then(response => response.json())
            //     .then(data => {
            //         this.data = data;
            //         this.updateCharts();
            //         this.updateMetrics();
            //         document.body.classList.remove('loading');
            //     })
            //     .catch(error => {
            //         console.error('Error refreshing data:', error);
            //         this.showError('Failed to refresh data');
            //         document.body.classList.remove('loading');
            //     });
            
            // For demo, just remove loading class
            document.body.classList.remove('loading');
            this.updateTimestamp();
        }, 1500);
    }

    updateCharts() {
        if (!this.data) return;
        
        // Update sales chart
        if (this.charts.sales) {
            const salesData = Object.values(this.data.monthlySales);
            this.charts.sales.data.datasets[0].data = salesData;
            this.charts.sales.update('none');
        }

        // Update product chart
        if (this.charts.product) {
            const productData = Object.values(this.data.productStatus);
            this.charts.product.data.datasets[0].data = productData;
            this.charts.product.update('none');
        }

        // Update status chart
        if (this.charts.status) {
            const statusData = Object.values(this.data.saleStatus);
            this.charts.status.data.datasets[0].data = statusData;
            this.charts.status.update('none');
        }
    }

    exportChart(chartName) {
        if (!this.charts[chartName]) {
            console.warn(`Chart ${chartName} not found`);
            return;
        }

        const link = document.createElement('a');
        link.download = `${chartName}-chart-${new Date().toISOString().split('T')[0]}.png`;
        link.href = this.charts[chartName].toBase64Image();
        link.click();
    }

    // Utility Methods
    getMonthNames() {
        return Array.from({ length: 12 }, (_, i) => {
            return new Date(0, i).toLocaleString('en-US', { month: 'short' });
        });
    }

    formatCurrency(value) {
        return new Intl.NumberFormat('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }

    updateElementText(elementId, text) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = text;
        }
    }

    showError(message) {
        // Create error toast
        const toast = document.createElement('div');
        toast.className = 'error-toast';
        toast.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>${message}</span>
            <button class="toast-close"><i class="fas fa-times"></i></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
        
        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.remove();
        });
    }

    debounce(func, wait) {
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
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new Dashboard();
    
    // Make dashboard available globally for debugging (optional)
    window.dashboard = dashboard;
});

// Add error toast styles dynamically
const errorToastStyles = `
.error-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #fee2e2;
    color: #991b1b;
    padding: 12px 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 9999;
    animation: slideInRight 0.3s ease;
    max-width: 400px;
}

.error-toast i {
    font-size: 1.2rem;
}

.error-toast span {
    flex: 1;
    font-size: 0.9rem;
}

.error-toast .toast-close {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    padding: 0;
    font-size: 0.9rem;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.error-toast .toast-close:hover {
    opacity: 1;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = errorToastStyles;
document.head.appendChild(styleSheet);