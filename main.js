function addMember() {
    const container = document.getElementById('members-container');
    
    const newEntry = document.createElement('div');
    newEntry.className = 'member-entry';
    
    newEntry.innerHTML = `
        <input type="text" name="memberName" placeholder="relative Name" required>
        <input type="text" name="memberAge" placeholder="Age">
        <button type="button" class="remove-btn" onclick="removeMember(this)">Delete</button>
    `;
    
    container.appendChild(newEntry);
}

function removeMember(button) {
    button.parentElement.remove();
}