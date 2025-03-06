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

document.addEventListener("DOMContentLoaded", function () {
    const commentModal = new bootstrap.Modal(document.getElementById("commentModal"));
    
    document.querySelectorAll(".edit-comment").forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("bookingId").value = this.dataset.id;
            document.getElementById("admin_comment").value = this.dataset.comment;
            commentModal.show();
        });
    });

    document.getElementById("commentForm").addEventListener("submit", function (e) {
        e.preventDefault();

        fetch("update-comment.php", {
            method: "POST",
            body: new FormData(this),
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload();
        })
        .catch(error => console.error("Error:", error));
    });
});