document.querySelectorAll('input[name="where_happen"]').forEach(function (input) {
    // Disable autocomplete for each input field
    input.setAttribute('autocomplete', 'off');
    
    input.addEventListener('input', function () {
        var inputVal = this.value.toLowerCase();
        if (inputVal.length > 0) {
            fetch('https://raw.githubusercontent.com/hasan4flf/california/main/cities.json')
                .then(response => response.json())
                .then(data => {
                    let uniqueResults = new Map(); // A map to track unique results
                    data.forEach(item => {
                        let label, value;
                        if (isNaN(inputVal)) { // Assuming a city name search
                            label = `${item.city}, ${item.state}`;
                            value = label; // For city searches, value and label are the same
                        } else { // Assuming a ZIP code search
                            label = `${item.city}, ${item.state} ${item.zip}`;
                            value = label; // Include ZIP code in value for ZIP searches
                        }
                        if (item.city.toLowerCase().startsWith(inputVal) || item.zip.startsWith(inputVal)) {
                            if (!uniqueResults.has(label)) { // Prevent duplicates
                                uniqueResults.set(label, value);
                            }
                        }
                    });
                    // Convert Map to array of objects for display
                    let matches = Array.from(uniqueResults, ([label, value]) => ({ label, value }));
                    displaySuggestions(matches, input);
                }).catch(error => console.error('Error fetching the data:', error));
        } else {
            closeAllLists();
        }
    });
});

function displaySuggestions(matches, input) {
    closeAllLists();
    let suggestionBox = document.createElement("div");
    suggestionBox.setAttribute("class", "autocomplete-items");

    if (input.nextSibling) {
        input.parentNode.insertBefore(suggestionBox, input.nextSibling);
    } else {
        input.parentNode.appendChild(suggestionBox);
    }

    matches.forEach(function (item) {
        let div = document.createElement("div");
        div.innerHTML = item.label;
        div.addEventListener("click", function () {
            input.value = item.value;
            closeAllLists();
        });
        suggestionBox.appendChild(div);
    });
}

function closeAllLists() {
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
        x[i].parentNode.removeChild(x[i]);
    }
}

// Close the list when someone clicks in the document
document.addEventListener("click", function (e) {
    closeAllLists();
});