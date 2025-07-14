document.addEventListener('DOMContentLoaded', function() {
    const optionTexts = [
        "Placeholder for Option 1",
        "Placeholder for Option 2",
        "Placeholder for Option 3",
        "Placeholder for Option 4",
        "Placeholder for Option 5",
        "Placeholder for Option 6",
        "Placeholder for Option 7",
        "Placeholder for Option 8",
        "Placeholder for Option 9",
        "Placeholder for Option 10"
    ];
    document.querySelectorAll('.section-dropdown').forEach(function(dropdown) {
        dropdown.addEventListener('change', function() {
            const section = this.getAttribute('data-section');
            const selectedIndex = this.selectedIndex;
            document.getElementById('section-content-' + section).textContent = optionTexts[selectedIndex];
        });
    });
});
