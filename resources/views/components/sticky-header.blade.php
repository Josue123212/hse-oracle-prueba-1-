<style>
    .fi-header {
        position: sticky;
        top: 0;
        z-index: 20;
        background-color: rgb(249, 250, 251); /* Light mode bg-gray-50 */
        padding-top: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgb(229, 231, 235); /* border-gray-200 */
        transition: all 0.3s ease;
    }
    
    .dark .fi-header {
        background-color: rgb(3, 7, 18); /* Dark mode bg-gray-950 */
        border-bottom-color: rgb(31, 41, 55); /* border-gray-800 */
    }

    /* Optional: Add a shadow when scrolling (requires JS, but base style is here) */
    .fi-header.is-stuck {
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }

    /* 
       FIX: Mobile Sidebar Trigger (Hamburger) & Sticky Header Conflict
       The user reported that the hamburger button overlaps the title and disappears on scroll.
       We fix this by:
       1. Making the hamburger button fixed (sticky) so it doesn't scroll away.
       2. Adding left padding to the header so the title doesn't sit under the button.
    */
    @media (max-width: 1024px) {
        /* Target common Filament sidebar trigger classes */
        .fi-topbar-open-sidebar-btn,
        .fi-sidebar-trigger,
        button[aria-label="Open sidebar"],
        button[aria-label="Abrir barra lateral"] {
            position: fixed !important;
            top: 0.75rem; /* Align with header padding */
            left: 1rem;
            z-index: 30 !important; /* Higher than header's 20 */
            background-color: rgba(255, 255, 255, 0.9); /* Ensure visibility */
            backdrop-filter: blur(4px);
            border-radius: 0.5rem;
            padding: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            width: auto !important;
            height: auto !important;
        }

        /* Dark mode support for the button */
        .dark .fi-topbar-open-sidebar-btn,
        .dark .fi-sidebar-trigger,
        .dark button[aria-label="Open sidebar"],
        .dark button[aria-label="Abrir barra lateral"] {
            background-color: rgba(24, 24, 27, 0.9); /* zinc-950 */
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Push the header content to the right to avoid overlap */
        .fi-header {
            padding-left: 4rem !important; 
        }
    }
</style>
