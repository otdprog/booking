document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".confirm-btn").forEach(button => {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            let bookingId = this.dataset.id;
            let buttonElement = this;

 //           console.log("Sending booking_id:", bookingId); // Лог у консоль

            buttonElement.disabled = true;

            fetch("confirm-booking.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `booking_id=${bookingId}&csrf_token=${document.querySelector('input[name="csrf_token"]').value}`
            })
            .then(response => response.json())
.then(data => {
                if (data.success) {
                    // Перезавантажуємо сторінку для відображення оновленого статусу
                    location.reload();
                } else {
                    alert("Failed to confirm booking.");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                buttonElement.disabled = false;
            });
        });
    });
});