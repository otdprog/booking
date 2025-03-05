document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("body").addEventListener("click", function (event) {
        if (event.target.classList.contains("confirm-btn")) {
            event.preventDefault();
            
            let buttonElement = event.target;
            let bookingId = buttonElement.closest("form").querySelector('input[name="confirm_booking_id"]').value;
            let csrfToken = buttonElement.closest("form").querySelector('input[name="csrf_token"]').value;

            buttonElement.disabled = true;

            fetch("confirm-booking.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `booking_id=${bookingId}&csrf_token=${csrfToken}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Failed to confirm booking.");
                    buttonElement.disabled = false;
                }
            })
            .catch(error => {
                console.error("Error:", error);
                buttonElement.disabled = false;
            });
        }
    });
});
