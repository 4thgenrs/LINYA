document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(".editBtn");
    const officeIdField = document.getElementById("office_id");
    const officeDescField = document.getElementById("office_description");
    const submitBtn = document.getElementById("submitBtn");

    editButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            const row = this.closest("tr");
            const id = row.getAttribute("data-id");
            const description = row.getAttribute("data-description");

            officeIdField.value = id;
            officeDescField.value = description;
            submitBtn.value = "Update Office";

            // Change name so backend knows it's an update
            submitBtn.setAttribute("name", "update_office");
        });
    });
});
