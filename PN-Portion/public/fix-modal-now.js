// Emergency Modal Fix Script
// Run this in browser console if modal is not clickable

console.log('🔧 Starting emergency modal fix...');

// Find all modal backdrops
const backdrops = document.querySelectorAll('.modal-backdrop');
console.log('Found backdrops:', backdrops.length);

backdrops.forEach((backdrop, index) => {
    console.log(`Backdrop ${index}:`, {
        zIndex: window.getComputedStyle(backdrop).zIndex,
        display: window.getComputedStyle(backdrop).display,
        pointerEvents: window.getComputedStyle(backdrop).pointerEvents
    });
    
    // Fix backdrop
    backdrop.style.zIndex = '1040';
    backdrop.style.pointerEvents = 'none'; // KEY FIX: backdrop should not capture clicks
});

// Find modal
const modal = document.getElementById('dishModal');
if (modal) {
    console.log('Modal found');
    console.log('Modal z-index:', window.getComputedStyle(modal).zIndex);
    
    // Fix modal
    modal.style.zIndex = '9999';
    modal.style.pointerEvents = 'auto';
    
    // Fix all modal children
    modal.querySelectorAll('*').forEach(el => {
        el.style.pointerEvents = 'auto';
    });
    
    console.log('✅ Modal fixed!');
} else {
    console.log('❌ Modal not found');
}

// Check what's at the center of screen
const centerX = window.innerWidth / 2;
const centerY = window.innerHeight / 2;
const elementAtCenter = document.elementFromPoint(centerX, centerY);
console.log('Element at screen center:', elementAtCenter);
console.log('Element class:', elementAtCenter.className);
console.log('Element z-index:', window.getComputedStyle(elementAtCenter).zIndex);

console.log('🎉 Fix complete! Try clicking now.');
