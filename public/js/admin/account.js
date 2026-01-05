$(document).ready(function() {
    // Get the form card and toggle button
    const $formCard = $('#userFormCard');
    const $toggleBtn = $('#toggleFormBtn');
    
    // Check if we're in edit mode (form should be visible)
    const isEditMode = $formCard.css('display') === 'block';
    
    // Set initial button text
    updateButtonText();
    
    // Toggle form visibility when button is clicked
    $toggleBtn.on('click', function() {
        // Toggle with smooth animation
        $formCard.slideToggle(300, function() {
            // Update button text after animation
            updateButtonText();
        });
    });
    
    // Function to update button text based on form visibility
    function updateButtonText() {
        const isVisible = $formCard.is(':visible');
        const isEditing = "{{ isset($editUser) ? 'true' : 'false' }}" === 'true';
        
        if (isEditing) {
            $toggleBtn.text(isVisible ? 'Hide Edit Form' : 'Show Edit Form');
        } else {
            $toggleBtn.text(isVisible ? 'Hide Add User Form' : 'Show Add User Form');
        }
    }
});