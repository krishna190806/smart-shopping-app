function enableEdit(rowId) {
    let row = document.getElementById("row-" + rowId);
    let discountValue = row.querySelector(".discount-value");
    let discountInput = row.querySelector(".discount-input");
    let updateButton = row.querySelector(".update-btn");
    let editButton = row.querySelector(".edit-btn");
    let form = row.querySelector(".update-form");

    // Show input field and hide text
    discountValue.style.display = "none";
    discountInput.style.display = "inline";
    
    // Show Update button & hide Edit button
    editButton.style.display = "none";
    updateButton.style.display = "inline-block";

    // Move the input to the form when editing starts
    discountInput.name = "discount";
    form.appendChild(discountInput);
}