document.addEventListener("DOMContentLoaded", function () {
    loadQueue();

    // Load the queue dynamically for the admin page
    function loadQueue() {
        fetch("http://localhost/Queue%20Management%20system/php/get_queue.php")
            .then(response => response.json())
            .then(data => {
                let queueTable = document.getElementById("queue-list").getElementsByTagName("tbody")[0];
                queueTable.innerHTML = ""; // Clear table first

                data.forEach(customer => {
                    let row = `
                        <tr>
                            <td>${customer.name}</td>
                            <td>${customer.phone}</td>
                            <td>${customer.created_at}</td>
                            <td><button class="call-btn" data-id="${customer.id}">Call</button></td>
                        </tr>
                    `;
                    queueTable.innerHTML += row;
                });

                attachCallButtons();
            });
    }

    // Attach event listener to call buttons
    function attachCallButtons() {
        document.querySelectorAll(".call-btn").forEach(button => {
            button.addEventListener("click", function () {
                let customerId = this.getAttribute("data-id");
                callNextCustomer(customerId);
            });
        });
    }

    // Call next customer when button is clicked
    function callNextCustomer(customerId) {
        fetch(`http://localhost/Queue%20Management%20system/php/next_customer.php?id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message); // No customers left
                } else {
                    alert("Calling: " + data.name);
                    loadQueue(); // Refresh queue after calling
                }
            });
    }

// Form submission to join the queue
document.getElementById("joinQueueForm").addEventListener("submit", function (e) {
    e.preventDefault();

    // Get values from form
    const name = document.getElementById("name").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const successMessage = document.getElementById("successMessage");

    // Validate inputs
    if (name === "" || phone === "") {
        successMessage.innerHTML = "Please enter both name and phone number.";
        successMessage.style.color = "red";
        return;
    }

    // Send data to server (join_queue.php)
    fetch("php/join_queue.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}`
    })
    .then(response => response.json())
    .then(data => {
        console.log("Server Response:", data); // Debugging

        if (data.success) {
            successMessage.innerHTML = "You have successfully joined the queue!";
            successMessage.style.color = "green";

            // Clear input fields after successful submission
            document.getElementById("name").value = "";
            document.getElementById("phone").value = "";
        } else {
            successMessage.innerHTML = data.message || "Error joining the queue. Please try again!";
            successMessage.style.color = "red";
        }
    })
    .catch(error => {
        console.error("Fetch error:", error);
        successMessage.innerHTML = "Network error. Please check your connection.";
        successMessage.style.color = "red";
    });
});

});
