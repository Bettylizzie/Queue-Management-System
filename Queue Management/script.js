document.addEventListener("DOMContentLoaded", function () {
    // Main queue management functions
    function loadQueue() {
        fetch("php/get_queue.php")
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const queueTable = document.getElementById("queue-list")?.getElementsByTagName("tbody")[0];
                if (!queueTable) return; // Skip if not on admin page

                queueTable.innerHTML = ""; // Clear table first

                data.forEach(customer => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${escapeHtml(customer.name)}</td>
                        <td>${escapeHtml(customer.phone)}</td>
                        <td>${customer.status || 'Waiting'}</td>
                        <td>${customer.created_at}</td>
                        <td>
                            <button class="call-btn" data-id="${customer.id}">Call</button>
                            <button class="complete-btn" data-id="${customer.id}">Complete</button>
                        </td>
                    `;
                    queueTable.appendChild(row);
                });

                attachCallButtons();
                attachCompleteButtons();
            })
            .catch(error => {
                console.error("Error loading queue:", error);
                showMessage("Error loading queue. Please refresh the page.", "error");
            });
    }

    function attachCallButtons() {
        document.querySelectorAll(".call-btn").forEach(button => {
            button.addEventListener("click", function () {
                const customerId = this.getAttribute("data-id");
                updateCustomerStatus(customerId, "Called");
            });
        });
    }

    function attachCompleteButtons() {
        document.querySelectorAll(".complete-btn").forEach(button => {
            button.addEventListener("click", function () {
                const customerId = this.getAttribute("data-id");
                updateCustomerStatus(customerId, "Completed");
            });
        });
    }

    function updateCustomerStatus(customerId, status) {
        fetch("php/update_status.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `id=${customerId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(`Customer marked as ${status}: ${data.name}`, "success");
                loadQueue();
            } else {
                showMessage(data.message || "Error updating status", "error");
            }
        })
        .catch(error => {
            console.error("Error updating status:", error);
            showMessage("Network error. Please try again.", "error");
        });
    }

    // Customer form handling
    const joinForm = document.getElementById("joinQueueForm");
    if (joinForm) {
        joinForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const name = document.getElementById("name").value.trim();
            const phone = document.getElementById("phone").value.trim();

            if (!validateInput(name, phone)) {
                showMessage("Please enter valid name and phone number", "error");
                return;
            }

            fetch("php/join_queue.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage("You have successfully joined the queue!", "success");
                    joinForm.reset();
                } else {
                    showMessage(data.message || "Error joining the queue", "error");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                showMessage("Network issue or server error. Please try again.", "error");
            });
        });
    }

    // Helper functions
    function showMessage(message, type) {
        const messageElement = document.getElementById("successMessage") || createMessageElement();
        messageElement.textContent = message;
        messageElement.className = `message ${type}`;
    }

    function createMessageElement() {
        const element = document.createElement("div");
        element.id = "successMessage";
        element.className = "message";
        document.querySelector(".form-container")?.appendChild(element);
        return element;
    }

    function validateInput(name, phone) {
        return name.length > 0 && /^[\d\s\-()+]{8,}$/.test(phone);
    }

    function escapeHtml(unsafe) {
        return unsafe?.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;") || '';
    }

    // Initialize
  // In the loadQueue function in admin.html
function loadQueue() {
    fetch("php/get_queue.php")
        .then(response => {
            if (!response.ok) throw new Error("Network response was not ok");
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error("Server error:", data.error);
                return;
            }
            
            let queueTable = document.getElementById("queue-list").getElementsByTagName('tbody')[0];
            queueTable.innerHTML = "";
            
            if (data.length === 0) {
                queueTable.innerHTML = `<tr><td colspan="5" class="empty-queue">No customers currently in queue</td></tr>`;
                return;
            }
            
            // Rest of your row generation code
        })
        .catch(error => {
            console.error("Error loading queue:", error);
            document.getElementById("queue-list").getElementsByTagName('tbody')[0].innerHTML = 
                `<tr><td colspan="5" class="error-message">Error loading queue data</td></tr>`;
        });
}
    // Remove this misplaced block as it is redundant and improperly placed.

    // Add these new functions to script.js
    function updateQueueStatus() {
        fetch("php/get_queue.php?stats_only=true")
            .then(response => response.json())
            .then(data => {
                if (data.stats) {
                    document.getElementById("waiting-count").textContent = data.stats.waiting;
                    document.getElementById("called-count").textContent = data.stats.called;
                    document.getElementById("completed-count").textContent = data.stats.completed;
                }
            })
            .catch(error => console.error("Error updating status:", error));
    }

    function fetchQueuePosition(queueId) {
        fetch(`php/get_position.php?id=${queueId}`)
            .then(response => response.json())
            .then(data => {
                if (data.position) {
                    const positionMsg = document.getElementById("position-message");
                    positionMsg.textContent = `Your position in queue: ${data.position}`;
                    positionMsg.style.display = "block";
                }
            });
    }
    

    // Expose functions for admin.html
    window.queueFunctions = {
        loadQueue,
        attachCallButtons,
        updateCustomerStatus
    };
});