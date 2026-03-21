/**
 * COMPREHENSIVE MODAL FIXES
 * Fixes all modal backdrop, z-index, and display issues
 */

(function() {
    'use strict';
    
    // Prevent multiple initializations
    if (window.ModalFixesInitialized) {
        return;
    }
    window.ModalFixesInitialized = true;

    console.log('🔧 Initializing comprehensive modal fixes...');

    // Global modal cleanup function
    function cleanupAllModalStates() {
        console.log('🧹 Cleaning up all modal states...');
        
        // Remove all modal backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
            console.log('Removing backdrop:', backdrop);
            backdrop.remove();
        });
        
        // Reset body classes and styles
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
        // Hide all modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
            modal.removeAttribute('role');
        });
        
        console.log('✅ Modal states cleaned up');
    }

    // Safe modal show function with validation
    function showModalSafely(modalId) {
        console.log('🔄 Showing modal safely:', modalId);

        // Validate modal exists
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error('❌ Modal not found:', modalId);
            console.error('Available modals:', Array.from(document.querySelectorAll('.modal')).map(m => m.id));

            // Show user-friendly error
            if (typeof showToast === 'function') {
                showToast('Modal not found. Please refresh the page.', 'error');
            } else {
                alert('Modal not found. Please refresh the page.');
            }
            return false;
        }

        // Check if Bootstrap is available
        if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            console.error('❌ Bootstrap Modal not available');

            // Fallback to simple modal display
            return showModalSimple(modalId);
        }

        // First cleanup any existing modal states
        cleanupAllModalStates();

        // Wait a moment for cleanup to complete
        setTimeout(() => {
            try {
                // Ensure proper z-index
                modalElement.style.zIndex = '1060';

                // Show modal
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: true
                });

                modal.show();

                // Force proper z-index after show
                setTimeout(() => {
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.style.zIndex = '1055';
                    }
                    modalElement.style.zIndex = '1060';
                }, 100);

                console.log('✅ Modal shown successfully:', modalId);
            } catch (error) {
                console.error('❌ Error showing modal:', error);

                // Fallback to simple modal display
                showModalSimple(modalId);
            }
        }, 50);

        return true;
    }

    // Safe modal hide function
    function hideModalSafely(modalId) {
        console.log('🔄 Hiding modal safely:', modalId);
        
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error('❌ Modal not found:', modalId);
            return;
        }
        
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            modalInstance.hide();
        }
        
        // Force cleanup after hide
        setTimeout(() => {
            cleanupAllModalStates();
        }, 300);
        
        console.log('✅ Modal hidden successfully:', modalId);
    }

    // Override Bootstrap modal events to ensure proper cleanup
    document.addEventListener('hidden.bs.modal', function(event) {
        console.log('🔄 Modal hidden event triggered:', event.target.id);
        setTimeout(() => {
            cleanupAllModalStates();
        }, 100);
    });

    // Fix for stuck modals on page load + validation
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔄 DOM loaded - cleaning up any stuck modals...');
        cleanupAllModalStates();

        // Run modal validation after a short delay
        setTimeout(() => {
            const validation = validateSystemModals();
            if (!validation.valid) {
                console.warn('⚠️ Modal issues detected on page load:', validation.issues);
            }
        }, 1000);
    });

    // Emergency cleanup on window focus (in case user switches tabs)
    window.addEventListener('focus', function() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 0) {
            console.log('🔄 Window focus - cleaning up stuck backdrops...');
            cleanupAllModalStates();
        }
    });

    // Keyboard shortcut for emergency cleanup (Ctrl+Alt+M)
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.altKey && event.key === 'm') {
            console.log('🚨 Emergency modal cleanup triggered by keyboard shortcut');
            cleanupAllModalStates();
            event.preventDefault();
        }
    });

    // Simple modal fallback (no Bootstrap dependency)
    function showModalSimple(modalId) {
        console.log('🔄 Using simple modal fallback for:', modalId);

        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error('❌ Modal not found for simple display:', modalId);
            return false;
        }

        // Clean up any existing stuff
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.style.overflow = 'hidden';

        // Show modal manually
        modalElement.style.cssText = `
            display: block !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 999999 !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            pointer-events: auto !important;
        `;

        modalElement.classList.add('show');

        // Style the dialog
        const modalDialog = modalElement.querySelector('.modal-dialog');
        if (modalDialog) {
            modalDialog.style.cssText = `
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                z-index: 1000000 !important;
                pointer-events: auto !important;
                margin: 0 !important;
            `;
        }

        // Ensure content is clickable
        const modalContent = modalElement.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.cssText = `
                pointer-events: auto !important;
                z-index: 1000001 !important;
                background: white !important;
                border-radius: 12px !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
            `;
        }

        // Make all inputs clickable
        modalElement.querySelectorAll('input, textarea, button, select').forEach(el => {
            el.style.pointerEvents = 'auto';
        });

        // Close on backdrop click
        modalElement.onclick = function(e) {
            if (e.target === modalElement) {
                hideModalSimple(modalId);
            }
        };

        // Close button functionality
        modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close').forEach(btn => {
            btn.onclick = function() {
                hideModalSimple(modalId);
            };
        });

        console.log('✅ Simple modal shown successfully:', modalId);
        return true;
    }

    function hideModalSimple(modalId) {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // System-wide modal validation
    function validateSystemModals() {
        console.log('🔍 Validating system modals...');

        const issues = [];
        const modals = document.querySelectorAll('.modal');
        const modalIds = [];

        modals.forEach(modal => {
            const id = modal.id;
            if (!id) {
                issues.push('Modal without ID found');
                return;
            }

            // Check for duplicate IDs
            if (modalIds.includes(id)) {
                issues.push(`Duplicate modal ID: ${id}`);
            } else {
                modalIds.push(id);
            }

            // Check for missing modal-dialog
            if (!modal.querySelector('.modal-dialog')) {
                issues.push(`Modal ${id} missing .modal-dialog`);
            }

            // Check for missing modal-content
            if (!modal.querySelector('.modal-content')) {
                issues.push(`Modal ${id} missing .modal-content`);
            }
        });

        // Check for orphaned modal references
        const scripts = document.querySelectorAll('script');
        scripts.forEach(script => {
            const content = script.textContent || script.innerText;

            // Look for modal references that don't exist
            const modalRefs = content.match(/getElementById\(['"`]([^'"`]*Modal[^'"`]*)['"]\)/g);
            if (modalRefs) {
                modalRefs.forEach(ref => {
                    const modalId = ref.match(/getElementById\(['"`]([^'"`]*)['"]\)/)[1];
                    if (!document.getElementById(modalId)) {
                        issues.push(`Reference to non-existent modal: ${modalId}`);
                    }
                });
            }
        });

        if (issues.length > 0) {
            console.warn('⚠️ Modal validation issues found:', issues);
        } else {
            console.log('✅ All modals validated successfully');
        }

        return {
            valid: issues.length === 0,
            issues: issues,
            modalCount: modals.length,
            modalIds: modalIds
        };
    }

    // Make functions available globally
    window.ModalFixes = {
        cleanup: cleanupAllModalStates,
        cleanupModalStates: cleanupAllModalStates,
        showModal: showModalSafely,
        hideModal: hideModalSafely,
        showModalSimple: showModalSimple,
        hideModalSimple: hideModalSimple,
        validate: validateSystemModals
    };

    // Also make individual functions available for backward compatibility
    window.cleanupModalStates = cleanupAllModalStates;
    window.showModalSafely = showModalSafely;
    window.hideModalSafely = hideModalSafely;
    window.showModalSimple = showModalSimple;
    window.hideModalSimple = hideModalSimple;

    console.log('✅ Comprehensive modal fixes initialized successfully');
    console.log('💡 Available functions:');
    console.log('   - window.ModalFixes.cleanup()');
    console.log('   - window.ModalFixes.showModal(modalId)');
    console.log('   - window.ModalFixes.hideModal(modalId)');
    console.log('   - window.ModalFixes.showModalSimple(modalId)');
    console.log('   - window.ModalFixes.validate()');
    console.log('   - Emergency cleanup: Ctrl+Alt+M');

})();
