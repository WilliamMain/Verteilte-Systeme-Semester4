
//Pop-up öffnen und schließen
function openPopup(popupID) {
    var popup = document.getElementById(popupID);
    if (popup) {
        popup.style.display = 'flex';
    } else {
        console.error('Popup with ID ' + popupID + ' not found.');
    }
}

function closePopup(popupID) {
    var popup = document.getElementById(popupID);
    if (popup) {
        popup.style.display = 'none';
    }
}



