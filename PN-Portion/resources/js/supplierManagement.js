$(document).ready(function() {
    // Function to calculate cost
    function calculateCost(row) {
        const quantity = parseFloat($(row).find('.quantity').val()) || 0;
        const unitPrice = parseFloat($(row).find('.unit-price').val()) || 0;
        const cost = quantity * unitPrice;
        $(row).find('.cost').val(cost.toFixed(2));
        updateTotalCost();
    }

    // Function to update total cost
    function updateTotalCost() {
        let total = 0;
        $('.cost').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        $('#totalCost').text(total.toFixed(2));
    }

    // Function to save orders to localStorage
    function saveOrdersToLocalStorage() {
        const orders = [];
        $('#savedOrdersTable tbody tr').each(function() {
            orders.push({
                description: $(this).find('td:eq(0)').text(),
                quantity: $(this).find('td:eq(1)').text(),
                unit: $(this).find('td:eq(2)').text(),
                unitPrice: $(this).find('td:eq(3)').text(),
                cost: $(this).find('td:eq(4)').text(),
                date: $(this).find('td:eq(5)').text()
            });
        });
        localStorage.setItem('savedOrders', JSON.stringify(orders));
    }

    // Function to load orders from localStorage
    function loadOrdersFromLocalStorage() {
        const savedOrders = localStorage.getItem('savedOrders');
        if (savedOrders) {
            const orders = JSON.parse(savedOrders);
            orders.forEach(order => {
                $('#savedOrdersTable tbody').append(`
                    <tr>
                        <td>${order.description}</td>
                        <td>${order.quantity}</td>
                        <td>${order.unit}</td>
                        <td>${order.unitPrice}</td>
                        <td>${order.cost}</td>
                        <td>${order.date}</td>
                        <td>
                            <button class="btn btn-primary btn-sm edit-order">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-order">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
        }
    }

    // Load saved orders when page loads
    loadOrdersFromLocalStorage();

    // Handle edit order
    $(document).on('click', '.edit-order', function() {
        const row = $(this).closest('tr');
        const description = row.find('td:eq(0)').text();
        const quantity = row.find('td:eq(1)').text();
        const unit = row.find('td:eq(2)').text();
        const unitPrice = row.find('td:eq(3)').text();

        // Clear existing form
        $('#purchaseOrderTable tbody tr').each(function() {
            $(this).find('input').val('');
            $(this).find('select').val('');
        });

        // Fill form with selected order data
        const firstRow = $('#purchaseOrderTable tbody tr:first');
        firstRow.find('.description').val(description);
        firstRow.find('.quantity').val(quantity);
        firstRow.find('.unit').val(unit);
        firstRow.find('.unit-price').val(unitPrice);
        calculateCost(firstRow);

        // Remove the edited row
        row.remove();
        saveOrdersToLocalStorage();
    });

    // Handle delete order
    $(document).on('click', '.delete-order', function() {
        if (confirm('Are you sure you want to delete this order?')) {
            $(this).closest('tr').remove();
            saveOrdersToLocalStorage();
        }
    });

    // Calculate cost when quantity or unit price changes
    $(document).on('input', '.quantity, .unit-price', function() {
        calculateCost($(this).closest('tr'));
    });

    // Add new row
    $('#addRow').click(function() {
        const newRow = $('#purchaseOrderTable tbody tr:first').clone();
        newRow.find('input').val('');
        $('#purchaseOrderTable tbody').append(newRow);
    });

    // Remove row
    $(document).on('click', '.remove-row', function() {
        if ($('#purchaseOrderTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
            updateTotalCost();
        }
    });

    // Handle form submission
    $('#purchaseOrderForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get current date
        const currentDate = new Date().toLocaleDateString();
        
        // Get all rows from the form
        $('#purchaseOrderTable tbody tr').each(function() {
            const description = $(this).find('.description').val();
            const quantity = $(this).find('.quantity').val();
            const unit = $(this).find('.unit').val();
            const unitPrice = $(this).find('.unit-price').val();
            const cost = $(this).find('.cost').val();

            // Only add rows that have values
            if (description && quantity && unit && unitPrice) {
                // Add new row to saved orders table
                $('#savedOrdersTable tbody').append(`
                    <tr>
                        <td>${description}</td>
                        <td>${quantity}</td>
                        <td>${unit}</td>
                        <td>${unitPrice}</td>
                        <td>${cost}</td>
                        <td>${currentDate}</td>
                        <td>
                            <button class="btn btn-primary btn-sm edit-order">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-order">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            }
        });

        // Save to localStorage
        saveOrdersToLocalStorage();

        // Clear the form
        $('#purchaseOrderTable tbody tr').each(function() {
            $(this).find('input').val('');
            $(this).find('select').val('');
        });
        
        // Reset total cost
        $('#totalCost').text('0.00');
    });
});