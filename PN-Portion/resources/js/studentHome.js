$(document).ready(function() {
    // Function to save orders to localStorage
    function saveOrdersToLocalStorage(orders) {
        localStorage.setItem('savedOrders', JSON.stringify(orders));
    }

    // Function to load orders from localStorage
    function loadOrdersFromLocalStorage() {
        const savedOrders = localStorage.getItem('savedOrders');
        if (savedOrders) {
            const orders = JSON.parse(savedOrders);
            $('#savedOrdersTable tbody').empty(); // Clear existing rows
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
                            <select class="form-control status-select">
                                <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="approved" ${order.status === 'approved' ? 'selected' : ''}>Approved</option>
                                <option value="rejected" ${order.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                            </select>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm update-status">
                                <i class="fas fa-check"></i> Update
                            </button>
                        </td>
                    </tr>
                `);
            });
        }
    }

    // Load saved orders when page loads
    loadOrdersFromLocalStorage();

    // Handle status update
    $(document).on('click', '.update-status', function() {
        const row = $(this).closest('tr');
        const status = row.find('.status-select').val();
        
        // Get all orders from localStorage
        const savedOrders = JSON.parse(localStorage.getItem('savedOrders') || '[]');
        
        // Find and update the status of the corresponding order
        const rowIndex = row.index();
        if (savedOrders[rowIndex]) {
            savedOrders[rowIndex].status = status;
            saveOrdersToLocalStorage(savedOrders);
            
            // Show success message
            alert('Status updated to: ' + status);
        }
    });
});