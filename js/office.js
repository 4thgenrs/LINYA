document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(".editBtn");
    const officeIdField = document.getElementById("office_id");
    const officeCodeField = document.getElementById("office_code");
    const officeDescField = document.getElementById("office_description");
    const submitBtn = document.getElementById("submitBtn");
    const officeForm = document.getElementById("officeForm");

    // Handle Edit Button
    editButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            const row = this.closest("tr");
            const id = row.getAttribute("data-id");
            const code = row.getAttribute("data-code");
            const description = row.getAttribute("data-description");

            officeIdField.value = id;
            officeCodeField.value = code;
            officeDescField.value = description;

            // Switch to update mode
            submitBtn.value = "Update Office";
            submitBtn.setAttribute("name", "update_office");
        });
    });

    // Optional: Reset form to Add mode when manually cleared or after submission
    officeForm.addEventListener("reset", function () {
        officeIdField.value = "";
        officeCodeField.value = "";
        officeDescField.value = "";
        submitBtn.value = "Add Office";
        submitBtn.setAttribute("name", "add_office");
    });
});
