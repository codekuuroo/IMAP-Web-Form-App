function addMember() {
    const container = document.getElementById('members-container');
    
    const newEntry = document.createElement('div');
    newEntry.className = 'member-entry';
    
    newEntry.innerHTML = `
        <input type="text" name="relativeName[]" placeholder="relative Name" required>

        <input type="number" name="relativeAge[]" placeholder="Age" required>

        <select name="relCivStats[]" required>
            <option value="" disabled selected hidden>
                Civil Status
            </option>
            <option value="single">
                Single
            </option>
            <option value="widow">
                Widow
            </option>
            <option value="married">
                Married
            </option>
            <option value="partner">
                Common-Law Partner
            </option>
        </select>

        <input type="text" name="relPatient[]" placeholder="Relation to Patient" required>

        <input type="text" name="relJob[]" placeholder="Job" required>

        <input type="number" name="relIncome[]" min="0" placeholder="Monthly Income" required>

        <button type="button" class="remove-btn" onclick="removeMember(this)">Delete</button>
    `;
    
    container.appendChild(newEntry);
}

function removeMember(button) {
    button.parentElement.remove();
}

function toggleRequestDetails() {
    const requestType = document.getElementById('request').value;
    const detailsDiv = document.getElementById('requestDetails');

    if (['others', 'medicine', 'laboratory', 'transplant'].includes(requestType)) {
        detailsDiv.style.display = 'block';
    } else {
        detailsDiv.style.display = 'none';
    }
}