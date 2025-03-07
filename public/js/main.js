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
            .then(response => response.json()) // Парсимо JSON
            .then(data => {
                alert(data.message); // Виводимо будь-яке повідомлення

                if (data.success) {
                    location.reload(); // Перезавантажуємо сторінку тільки при успіху
                } else {
                    buttonElement.disabled = false; // Вмикаємо кнопку при помилці
                }
            })
            .catch(error => {
                console.error("", error);
                alert("Помилка серверу. Спробуйте ще раз.");
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



document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        let alertBox = document.querySelector(".alert");
        if (alertBox) {
            alertBox.style.transition = "opacity 0.5s";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500); // Видаляємо з DOM
        }
    }, 5000); // Час у мілісекундах (5 секунд)
});